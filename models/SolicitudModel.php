<?php
declare(strict_types=1);

class SolicitudModel implements IRepositorio
{
    private PDO $pdo;
    public function __construct() { $this->pdo = Conexion::obtenerInstancia()->getConexion(); }

    public function obtenerTodos(int $pagina = 1, string $busqueda = ''): array
    {
        $offset = ($pagina - 1) * POR_PAGINA;
        $like   = "%$busqueda%";
        $stmt   = $this->pdo->prepare(
            'SELECT s.*, CONCAT(e.primer_nombre," ",e.primer_apellido) AS estudiante, e.cip
             FROM   solicitudes_libros s JOIN estudiantes e ON e.id_estudiante=s.id_estudiante
             WHERE  s.titulo LIKE ? OR s.autor LIKE ? OR e.primer_apellido LIKE ?
             ORDER  BY s.created_at DESC LIMIT ? OFFSET ?'
        );
        $stmt->execute([$like, $like, $like, POR_PAGINA, $offset]);
        return $stmt->fetchAll();
    }

    public function obtenerPorEstudiante(int $idEstudiante): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM solicitudes_libros WHERE id_estudiante=? ORDER BY created_at DESC');
        $stmt->execute([$idEstudiante]);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM solicitudes_libros WHERE id_solicitud=?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function insertar(array $d): int|false
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO solicitudes_libros (id_estudiante, titulo, autor, area, descripcion) VALUES (?,?,?,?,?)'
        );
        $ok = $stmt->execute([$d['id_estudiante'], $d['titulo'], $d['autor'] ?: null, $d['area'], $d['descripcion'] ?: null]);
        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    public function actualizar(int $id, array $d): bool
    {
        $stmt = $this->pdo->prepare('UPDATE solicitudes_libros SET estado=?, observaciones=? WHERE id_solicitud=?');
        return $stmt->execute([$d['estado'], $d['observaciones'] ?: null, $id]);
    }

    public function eliminar(int $id): bool { return false; }

    public function contarTotal(string $busqueda = ''): int
    {
        $like = "%$busqueda%";
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM solicitudes_libros WHERE titulo LIKE ?');
        $stmt->execute([$like]);
        return (int)$stmt->fetchColumn();
    }
}
