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
        return $this->obtenerReporte('', '', $busqueda, $pagina, POR_PAGINA);
    }

    public function obtenerReporte(string $desde = '', string $hasta = '', string $busqueda = '', int $pagina = 1, int $limite = 0): array
    {
        $where = [];
        $params = [];

        if ($desde !== '') {
            $where[] = 'DATE(r.fecha_reserva) >= ?';
            $params[] = $desde;
        }
        if ($hasta !== '') {
            $where[] = 'DATE(r.fecha_reserva) <= ?';
            $params[] = $hasta;
        }
        if ($busqueda !== '') {
            $where[] = '(l.titulo LIKE ? OR l.autor LIKE ? OR e.cip LIKE ? OR p.cip LIKE ? OR u.username LIKE ? OR e.primer_apellido LIKE ? OR p.primer_apellido LIKE ?)';
            $like = "%$busqueda%";
            array_push($params, $like, $like, $like, $like, $like, $like, $like);
        }

        $sqlWhere = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $sqlLimit = '';
        if ($limite > 0) {
            $offset = ($pagina - 1) * $limite;
            $sqlLimit = ' LIMIT ' . (int)$limite . ' OFFSET ' . (int)$offset;
        }

        $sql = "SELECT r.*, l.titulo, l.autor, l.imagen_thumb,
                       COALESCE(
                         CONCAT(e.primer_nombre, ' ', e.primer_apellido),
                         CONCAT(p.primer_nombre, ' ', p.primer_apellido),
                         CONCAT(u.nombre, ' ', u.apellido)
                       ) AS lector,
                       COALESCE(e.cip, p.cip, u.username) AS identificacion,
                       CASE
                         WHEN r.tipo_actor='PROFESOR' THEN 'Docente'
                         WHEN r.tipo_actor='ADMINISTRATIVO' THEN 'Administrativo'
                         ELSE 'Estudiante'
                       END AS tipo_lector,
                       DATEDIFF(r.fecha_devolucion_esperada, DATE(r.fecha_reserva)) AS dias_reservados
                FROM reservas r
                JOIN libros l ON l.id_libro = r.id_libro
                LEFT JOIN estudiantes e ON e.id_estudiante = r.id_estudiante
                LEFT JOIN profesores p ON p.id_profesor = r.id_profesor
                LEFT JOIN usuarios u ON u.id_usuario = r.id_usuario
                $sqlWhere
                ORDER BY r.fecha_reserva DESC$sqlLimit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function contarReporte(string $desde = '', string $hasta = '', string $busqueda = ''): int
    {
        $where = [];
        $params = [];
        if ($desde !== '') { $where[] = 'DATE(r.fecha_reserva) >= ?'; $params[] = $desde; }
        if ($hasta !== '') { $where[] = 'DATE(r.fecha_reserva) <= ?'; $params[] = $hasta; }
        if ($busqueda !== '') {
            $where[] = '(l.titulo LIKE ? OR l.autor LIKE ? OR e.cip LIKE ? OR p.cip LIKE ? OR u.username LIKE ?)';
            $like = "%$busqueda%";
            array_push($params, $like, $like, $like, $like, $like);
        }
        $sqlWhere = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reservas r JOIN libros l ON l.id_libro=r.id_libro LEFT JOIN estudiantes e ON e.id_estudiante=r.id_estudiante LEFT JOIN profesores p ON p.id_profesor=r.id_profesor LEFT JOIN usuarios u ON u.id_usuario=r.id_usuario $sqlWhere");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function obtenerPorActor(string $tipoActor, int $idActor): array
    {
        $col = match (strtoupper($tipoActor)) {
            'PROFESOR' => 'id_profesor',
            'ADMINISTRATIVO' => 'id_usuario',
            default => 'id_estudiante',
        };
        $stmt = $this->pdo->prepare(
            "SELECT r.*, l.titulo, l.imagen_thumb, l.autor,
                    DATEDIFF(r.fecha_devolucion_esperada, DATE(r.fecha_reserva)) AS dias_reservados
             FROM reservas r JOIN libros l ON l.id_libro=r.id_libro
             WHERE r.tipo_actor=? AND r.$col=? ORDER BY r.fecha_reserva DESC"
        );
        $stmt->execute([strtoupper($tipoActor), $idActor]);
        return $stmt->fetchAll();
    }

    public function obtenerPorEstudiante(int $idEstudiante): array
    {
        return $this->obtenerPorActor('ESTUDIANTE', $idEstudiante);
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reservas WHERE id_reserva=?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function insertar(array $d): int|false
    {
        $idLibro = (int)($d['id_libro'] ?? 0);
        $tipoActor = strtoupper((string)($d['tipo_actor'] ?? 'ESTUDIANTE'));
        $idActor = (int)($d['id_actor'] ?? $d['id_estudiante'] ?? 0);
        $fechaDev = $d['fecha_devolucion_esperada'] ?? '';

        if ($idLibro <= 0 || $idActor <= 0 || $fechaDev === '') {
            error_log('[ReservaModel::insertar] Datos inválidos: ' . print_r($d, true));
            return false;
        }

        $idEstudiante = $tipoActor === 'ESTUDIANTE' ? $idActor : null;
        $idProfesor = $tipoActor === 'PROFESOR' ? $idActor : null;
        $idUsuario = $tipoActor === 'ADMINISTRATIVO' ? $idActor : null;

        Conexion::obtenerInstancia()->iniciarTransaccion();
        try {
            $stmtLibro = $this->pdo->prepare(
                'SELECT id_libro, unidades_totales, unidades_disponibles, firma_datos
                 FROM libros WHERE id_libro=? AND activo=1 FOR UPDATE'
            );
            $stmtLibro->execute([$idLibro]);
            $libro = $stmtLibro->fetch(PDO::FETCH_ASSOC);
            if (!$libro || (int)$libro['unidades_disponibles'] <= 0) {
                Conexion::obtenerInstancia()->revertir();
                return false;
            }

            $nuevosDisponibles = (int)$libro['unidades_disponibles'] - 1;
            $firmaLibroNueva = FirmaDigital::libro([
                'id_libro' => (int)$libro['id_libro'],
                'unidades_totales' => (int)$libro['unidades_totales'],
                'unidades_disponibles' => $nuevosDisponibles,
            ]);

            $this->pdo->prepare('UPDATE libros SET unidades_disponibles=?, firma_datos=? WHERE id_libro=?')
                ->execute([$nuevosDisponibles, $firmaLibroNueva, $idLibro]);

            $firmaTemporal = FirmaDigital::generar([
                'id_reserva' => 0,
                'tipo_actor' => $tipoActor,
                'id_actor' => $idActor,
                'id_libro' => $idLibro,
                'estado' => 'ACTIVA',
            ]);

            $stmtReserva = $this->pdo->prepare(
                'INSERT INTO reservas
                 (tipo_actor, id_estudiante, id_profesor, id_usuario, id_libro, fecha_devolucion_esperada, estado, firma_datos)
                 VALUES (?, ?, ?, ?, ?, ?, "ACTIVA", ?)'
            );
            $stmtReserva->execute([$tipoActor, $idEstudiante, $idProfesor, $idUsuario, $idLibro, $fechaDev, $firmaTemporal]);
            $nuevoId = (int)$this->pdo->lastInsertId();

            $firmaReserva = FirmaDigital::reserva([
                'id_reserva' => $nuevoId,
                'id_estudiante' => $idEstudiante ?? 0,
                'id_libro' => $idLibro,
                'estado' => 'ACTIVA',
            ]);
            $this->pdo->prepare('UPDATE reservas SET firma_datos=? WHERE id_reserva=?')->execute([$firmaReserva, $nuevoId]);

            FirmaDigital::registrarAuditoria('libros', $idLibro, 'UPDATE', $libro['firma_datos'] ?? null, $firmaLibroNueva, $_SESSION['usuario']['id_usuario'] ?? null);
            FirmaDigital::registrarAuditoria('reservas', $nuevoId, 'INSERT', null, $firmaReserva, $_SESSION['usuario']['id_usuario'] ?? null);

            Conexion::obtenerInstancia()->confirmar();
            return $nuevoId;
        } catch (Throwable $e) {
            Conexion::obtenerInstancia()->revertir();
            error_log('[ReservaModel::insertar] ' . $e->getMessage());
            return false;
        }
    }

    public function devolver(int $id): bool
    {
        $reserva = $this->obtenerPorId($id);
        if (!$reserva || $reserva['estado'] !== 'ACTIVA') {
            return false;
        }

        Conexion::obtenerInstancia()->iniciarTransaccion();
        try {
            $firma = FirmaDigital::reserva(array_merge((array)$reserva, ['estado' => 'DEVUELTA']));
            $this->pdo->prepare('UPDATE reservas SET estado="DEVUELTA", fecha_devolucion_real=CURDATE(), firma_datos=? WHERE id_reserva=?')
                ->execute([$firma, $id]);
            (new LibroModel())->actualizarDisponibles((int)$reserva['id_libro'], 1);
            Conexion::obtenerInstancia()->confirmar();
            FirmaDigital::registrarAuditoria('reservas', $id, 'UPDATE', $reserva['firma_datos'], $firma, $_SESSION['usuario']['id_usuario'] ?? null);
            return true;
        } catch (Throwable $e) {
            Conexion::obtenerInstancia()->revertir();
            error_log('[ReservaModel::devolver] ' . $e->getMessage());
            return false;
        }
    }

    public function actualizar(int $id, array $d): bool { return false; }
    public function eliminar(int $id): bool { return false; }

    public function contarTotal(string $busqueda = ''): int
    {
        return $this->contarReporte('', '', $busqueda);
    }
}
