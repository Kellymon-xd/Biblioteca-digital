<?php

declare(strict_types=1);

class LibroModel implements IRepositorio
{
    private PDO $pdo;
    public function __construct() { $this->pdo = Conexion::obtenerInstancia()->getConexion(); }

    public function obtenerTodos(int $pagina = 1, string $busqueda = ''): array
    {
        return $this->obtenerFiltrado($pagina, $busqueda, 0, '');
    }

    public function obtenerFiltrado(int $pagina = 1, string $busqueda = '', int $idCategoria = 0, string $disponibilidad = ''): array
    {
        $offset = ($pagina - 1) * POR_PAGINA;
        [$where, $params] = $this->filtros($busqueda, $idCategoria, $disponibilidad);
        $stmt = $this->pdo->prepare(
            "SELECT l.*, c.nombre AS categoria
             FROM libros l JOIN categorias_libros c ON c.id_categoria = l.id_categoria
             WHERE $where
             ORDER BY l.titulo ASC LIMIT ? OFFSET ?"
        );
        $stmt->execute([...$params, POR_PAGINA, $offset]);
        return $stmt->fetchAll();
    }

    private function filtros(string $busqueda, int $idCategoria, string $disponibilidad): array
    {
        $where = ['(l.titulo LIKE ? OR l.autor LIKE ? OR l.isbn LIKE ? OR c.nombre LIKE ?)'];
        $like = "%$busqueda%";
        $params = [$like, $like, $like, $like];
        if ($idCategoria > 0) {
            $where[] = 'l.id_categoria = ?';
            $params[] = $idCategoria;
        }
        if ($disponibilidad === 'disponible') {
            $where[] = 'l.unidades_disponibles > 0';
        } elseif ($disponibilidad === 'no_disponible') {
            $where[] = 'l.unidades_disponibles <= 0';
        }
        return [implode(' AND ', $where), $params];
    }

    public function buscarParaPortal(string $busqueda, int $idCategoria = 0): array
    {
        $like = "%$busqueda%";
        $where = 'l.activo=1 AND (l.titulo LIKE ? OR l.autor LIKE ? OR l.isbn LIKE ? OR c.nombre LIKE ?)';
        $params = [$like, $like, $like, $like];
        if ($idCategoria > 0) { $where .= ' AND l.id_categoria=?'; $params[] = $idCategoria; }
        $stmt = $this->pdo->prepare(
            "SELECT l.*, c.nombre AS categoria
             FROM libros l JOIN categorias_libros c ON c.id_categoria=l.id_categoria
             WHERE $where ORDER BY l.titulo"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT l.*, c.nombre AS categoria
             FROM libros l JOIN categorias_libros c ON c.id_categoria=l.id_categoria
             WHERE l.id_libro=?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function insertar(array $d): int|false
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO libros (isbn, titulo, autor, editorial, anio_publicacion, id_categoria,
             descripcion, costo, unidades_totales, unidades_disponibles, imagen_original, imagen_thumb, firma_datos)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $ok = $stmt->execute([
            $d['isbn'] ?: null, $d['titulo'], $d['autor'], $d['editorial'] ?: null,
            $d['anio_publicacion'] ?: null, $d['id_categoria'], $d['descripcion'] ?: null,
            $d['costo'] ?? 0, $d['unidades_totales'], $d['unidades_totales'],
            $d['imagen_original'] ?? null, $d['imagen_thumb'] ?? null,
            FirmaDigital::generar(['id_libro' => 0, 'unidades_totales' => $d['unidades_totales'], 'unidades_disponibles' => $d['unidades_totales']]),
        ]);
        if (!$ok) return false;
        $nuevoId = (int)$this->pdo->lastInsertId();
        $firma = FirmaDigital::libro(['id_libro'=>$nuevoId,'unidades_totales'=>$d['unidades_totales'],'unidades_disponibles'=>$d['unidades_totales']]);
        $this->pdo->prepare('UPDATE libros SET firma_datos=? WHERE id_libro=?')->execute([$firma, $nuevoId]);
        FirmaDigital::registrarAuditoria('libros', $nuevoId, 'INSERT', '', $firma, $_SESSION['usuario']['id_usuario'] ?? null);
        return $nuevoId;
    }

    public function actualizar(int $id, array $d): bool
    {
        $actual = $this->obtenerPorId($id);
        if (!$actual) return false;
        $imagen = $d['imagen_original'] ?? $actual['imagen_original'];
        $thumb = $d['imagen_thumb'] ?? $actual['imagen_thumb'];
        $firma = FirmaDigital::libro(['id_libro'=>$id,'unidades_totales'=>$d['unidades_totales'],'unidades_disponibles'=>$actual['unidades_disponibles']]);
        $stmt = $this->pdo->prepare(
            'UPDATE libros SET isbn=?, titulo=?, autor=?, editorial=?, anio_publicacion=?,
             id_categoria=?, descripcion=?, costo=?, unidades_totales=?, imagen_original=?, imagen_thumb=?, activo=?, firma_datos=?
             WHERE id_libro=?'
        );
        $ok = $stmt->execute([
            $d['isbn'] ?: null, $d['titulo'], $d['autor'], $d['editorial'] ?: null,
            $d['anio_publicacion'] ?: null, $d['id_categoria'], $d['descripcion'] ?: null,
            $d['costo'] ?? 0, $d['unidades_totales'], $imagen, $thumb, $d['activo'], $firma, $id,
        ]);
        if ($ok) FirmaDigital::registrarAuditoria('libros', $id, 'UPDATE', $actual['firma_datos'] ?? '', $firma, $_SESSION['usuario']['id_usuario'] ?? null);
        return $ok;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE libros SET activo=0 WHERE id_libro=?');
        return $stmt->execute([$id]);
    }

    public function contarTotal(string $busqueda = ''): int
    {
        return $this->contarFiltrado($busqueda, 0, '');
    }

    public function contarFiltrado(string $busqueda = '', int $idCategoria = 0, string $disponibilidad = ''): int
    {
        [$where, $params] = $this->filtros($busqueda, $idCategoria, $disponibilidad);
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM libros l JOIN categorias_libros c ON c.id_categoria=l.id_categoria WHERE $where");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function actualizarDisponibles(int $idLibro, int $delta): bool
    {
        $stmt = $this->pdo->prepare('SELECT id_libro, unidades_totales, unidades_disponibles, firma_datos FROM libros WHERE id_libro=? FOR UPDATE');
        $stmt->execute([$idLibro]);
        $libro = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$libro) return false;
        $actuales = (int)$libro['unidades_disponibles'];
        $totales = (int)$libro['unidades_totales'];
        $nuevos = $actuales + $delta;
        if ($nuevos < 0 || $nuevos > $totales) return false;
        if (!empty($libro['firma_datos'])) {
            $firmaValida = FirmaDigital::verificar(['id_libro'=>(int)$libro['id_libro'], 'unidades_totales'=>$totales, 'unidades_disponibles'=>$actuales], $libro['firma_datos']);
            if (!$firmaValida) {
                error_log('[LibroModel::actualizarDisponibles] Firma inválida en libro ID: ' . $idLibro);
                return false;
            }
        }
        $firmaNueva = FirmaDigital::libro(['id_libro'=>(int)$libro['id_libro'], 'unidades_totales'=>$totales, 'unidades_disponibles'=>$nuevos]);
        $ok = $this->pdo->prepare('UPDATE libros SET unidades_disponibles=?, firma_datos=? WHERE id_libro=?')->execute([$nuevos, $firmaNueva, $idLibro]);
        if ($ok) FirmaDigital::registrarAuditoria('libros', $idLibro, 'UPDATE', $libro['firma_datos'] ?? null, $firmaNueva, $_SESSION['usuario']['id_usuario'] ?? null);
        return $ok;
    }

    public function obtenerTodosParaExcel(string $busqueda = '', int $idCategoria = 0, string $disponibilidad = ''): array
    {
        [$where, $params] = $this->filtros($busqueda, $idCategoria, $disponibilidad);
        $stmt = $this->pdo->prepare(
            "SELECT l.isbn, l.titulo, l.autor, l.editorial, l.anio_publicacion,
                    c.nombre AS categoria, l.costo, l.unidades_totales, l.unidades_disponibles, l.activo
             FROM libros l JOIN categorias_libros c ON c.id_categoria=l.id_categoria
             WHERE $where ORDER BY l.titulo"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function estadisticasPorPeriodo(string $desde, string $hasta): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT l.titulo, l.autor, c.nombre AS categoria, COUNT(r.id_reserva) AS total_reservas
             FROM reservas r
             JOIN libros l ON l.id_libro = r.id_libro
             JOIN categorias_libros c ON c.id_categoria = l.id_categoria
             WHERE r.fecha_reserva BETWEEN ? AND ?
             GROUP BY r.id_libro ORDER BY total_reservas DESC LIMIT 20'
        );
        $stmt->execute([$desde, $hasta . ' 23:59:59']);
        return $stmt->fetchAll();
    }
}
