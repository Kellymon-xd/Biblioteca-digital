<?php
declare(strict_types=1);

class ReservaModel implements IRepositorio
{
    private PDO $pdo;
    public function __construct()
    {
        $this->pdo = Conexion::obtenerInstancia()->getConexion();
    }

    public function obtenerTodos(int $pagina = 1, string $busqueda = ''): array
    {
        $offset = ($pagina - 1) * POR_PAGINA;
        $like = "%$busqueda%";
        $stmt = $this->pdo->prepare(
            'SELECT r.*, l.titulo, l.imagen_thumb,
                    CONCAT(e.primer_nombre," ",e.primer_apellido) AS estudiante, e.cip
             FROM   reservas r
             JOIN   libros l ON l.id_libro = r.id_libro
             JOIN   estudiantes e ON e.id_estudiante = r.id_estudiante
             WHERE  l.titulo LIKE ? OR e.primer_apellido LIKE ? OR e.cip LIKE ?
             ORDER  BY r.fecha_reserva DESC LIMIT ? OFFSET ?'
        );
        $stmt->execute([$like, $like, $like, POR_PAGINA, $offset]);
        return $stmt->fetchAll();
    }

    public function obtenerPorEstudiante(int $idEstudiante): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, l.titulo, l.imagen_thumb, l.autor
             FROM   reservas r JOIN libros l ON l.id_libro=r.id_libro
             WHERE  r.id_estudiante=? ORDER BY r.fecha_reserva DESC'
        );
        $stmt->execute([$idEstudiante]);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reservas WHERE id_reserva=?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function insertar(array $d): int|false
    {
        $idLibro = (int) ($d['id_libro'] ?? 0);
        $idEstudiante = (int) ($d['id_estudiante'] ?? 0);
        $fechaDev = $d['fecha_devolucion_esperada'] ?? date('Y-m-d', strtotime('+15 days'));

        if ($idLibro <= 0 || $idEstudiante <= 0 || empty($fechaDev)) {
            error_log('[ReservaModel::insertar] Datos inválidos: ' . print_r($d, true));
            return false;
        }

        Conexion::obtenerInstancia()->iniciarTransaccion();

        try {
            // 1. Buscar el libro y bloquearlo durante la transacción
            $stmtLibro = $this->pdo->prepare(
                'SELECT id_libro, unidades_totales, unidades_disponibles, firma_datos
             FROM libros
             WHERE id_libro = ?
               AND activo = 1
             FOR UPDATE'
            );

            $stmtLibro->execute([$idLibro]);
            $libro = $stmtLibro->fetch(PDO::FETCH_ASSOC);

            if (!$libro) {
                Conexion::obtenerInstancia()->revertir();
                error_log('[ReservaModel::insertar] Libro no encontrado o inactivo. ID: ' . $idLibro);
                return false;
            }

            $disponibles = (int) $libro['unidades_disponibles'];

            if ($disponibles <= 0) {
                Conexion::obtenerInstancia()->revertir();
                error_log('[ReservaModel::insertar] Libro sin unidades disponibles. ID: ' . $idLibro);
                return false;
            }

            // 2. Calcular nuevo inventario
            $nuevosDisponibles = $disponibles - 1;

            $firmaLibroNueva = FirmaDigital::libro([
                'id_libro' => (int) $libro['id_libro'],
                'unidades_totales' => (int) $libro['unidades_totales'],
                'unidades_disponibles' => $nuevosDisponibles,
            ]);

            // 3. Actualizar inventario y firma del libro
            $stmtUpdateLibro = $this->pdo->prepare(
                'UPDATE libros
             SET unidades_disponibles = ?,
                 firma_datos = ?
             WHERE id_libro = ?'
            );

            $okLibro = $stmtUpdateLibro->execute([
                $nuevosDisponibles,
                $firmaLibroNueva,
                $idLibro
            ]);

            if (!$okLibro) {
                Conexion::obtenerInstancia()->revertir();
                error_log('[ReservaModel::insertar] No se pudo actualizar inventario.');
                return false;
            }

            // 4. Insertar reserva con firma temporal
            $firmaTemporal = FirmaDigital::generar([
                'id_reserva' => 0,
                'id_estudiante' => $idEstudiante,
                'id_libro' => $idLibro,
                'estado' => 'ACTIVA'
            ]);

            $stmtReserva = $this->pdo->prepare(
                'INSERT INTO reservas
                (id_estudiante, id_libro, fecha_devolucion_esperada, estado, firma_datos)
             VALUES
                (?, ?, ?, "ACTIVA", ?)'
            );

            $okReserva = $stmtReserva->execute([
                $idEstudiante,
                $idLibro,
                $fechaDev,
                $firmaTemporal
            ]);

            if (!$okReserva) {
                Conexion::obtenerInstancia()->revertir();
                error_log('[ReservaModel::insertar] No se pudo insertar la reserva.');
                return false;
            }

            $nuevoId = (int) $this->pdo->lastInsertId();

            // 5. Firma final de la reserva con el ID real
            $firmaReserva = FirmaDigital::reserva([
                'id_reserva' => $nuevoId,
                'id_estudiante' => $idEstudiante,
                'id_libro' => $idLibro,
                'estado' => 'ACTIVA'
            ]);

            $this->pdo
                ->prepare('UPDATE reservas SET firma_datos = ? WHERE id_reserva = ?')
                ->execute([$firmaReserva, $nuevoId]);

            // 6. Auditoría
            FirmaDigital::registrarAuditoria(
                'libros',
                $idLibro,
                'UPDATE',
                $libro['firma_datos'] ?? null,
                $firmaLibroNueva,
                null
            );

            FirmaDigital::registrarAuditoria(
                'reservas',
                $nuevoId,
                'INSERT',
                null,
                $firmaReserva,
                null
            );

            Conexion::obtenerInstancia()->confirmar();

            return $nuevoId;

        } catch (\Throwable $e) {
            Conexion::obtenerInstancia()->revertir();
            error_log('[ReservaModel::insertar] ERROR REAL: ' . $e->getMessage());
            return false;
        }
    }

    public function devolver(int $id): bool
    {
        $reserva = $this->obtenerPorId($id);
        if (!$reserva || $reserva['estado'] !== 'ACTIVA')
            return false;

        Conexion::obtenerInstancia()->iniciarTransaccion();
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE reservas SET estado="DEVUELTA", fecha_devolucion_real=NOW(), firma_datos=? WHERE id_reserva=?'
            );
            $firma = FirmaDigital::reserva(array_merge((array) $reserva, ['estado' => 'DEVUELTA']));
            $stmt->execute([$firma, $id]);
            (new LibroModel())->actualizarDisponibles((int) $reserva['id_libro'], 1);
            Conexion::obtenerInstancia()->confirmar();
            FirmaDigital::registrarAuditoria('reservas', $id, 'UPDATE', $reserva['firma_datos'], $firma, null);
            return true;
        } catch (\Throwable $e) {
            Conexion::obtenerInstancia()->revertir();
            error_log('[ReservaModel::devolver] ' . $e->getMessage());
            return false;
        }
    }

    public function actualizar(int $id, array $d): bool
    {
        return false;
    } // No aplica
    public function eliminar(int $id): bool
    {
        return false;
    }            // Reservas no se eliminan
    public function contarTotal(string $busqueda = ''): int
    {
        $like = "%$busqueda%";
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM reservas r JOIN libros l ON l.id_libro=r.id_libro WHERE l.titulo LIKE ?');
        $stmt->execute([$like]);
        return (int) $stmt->fetchColumn();
    }
}
