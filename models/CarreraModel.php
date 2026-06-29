<?php
declare(strict_types=1);

class CarreraModel implements IRepositorio
{
    private PDO $pdo;
    public function __construct() { $this->pdo = Conexion::obtenerInstancia()->getConexion(); }

    public function obtenerTodos(int $pagina = 1, string $busqueda = ''): array
    {
        $offset = ($pagina - 1) * POR_PAGINA;
        $like   = "%$busqueda%";
        $stmt   = $this->pdo->prepare(
            'SELECT * FROM carreras WHERE nombre LIKE ? OR codigo LIKE ? ORDER BY nombre ASC LIMIT ? OFFSET ?'
        );
        $stmt->execute([$like, $like, POR_PAGINA, $offset]);
        return $stmt->fetchAll();
    }

    public function obtenerTodosActivos(): array
    {
        return $this->pdo->query('SELECT id_carrera, nombre FROM carreras WHERE activo=1 ORDER BY nombre')->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM carreras WHERE id_carrera = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function insertar(array $d): int|false
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO carreras (nombre, codigo, descripcion) VALUES (?, ?, ?)'
        );
        $ok = $stmt->execute([$d['nombre'], $d['codigo'], $d['descripcion']]);
        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    public function actualizar(int $id, array $d): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE carreras SET nombre=?, codigo=?, descripcion=?, activo=? WHERE id_carrera=?'
        );
        return $stmt->execute([$d['nombre'], $d['codigo'], $d['descripcion'], $d['activo'], $id]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE carreras SET activo=0 WHERE id_carrera=?');
        return $stmt->execute([$id]);
    }

    public function contarTotal(string $busqueda = ''): int
    {
        $like = "%$busqueda%";
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM carreras WHERE nombre LIKE ? OR codigo LIKE ?');
        $stmt->execute([$like, $like]);
        return (int)$stmt->fetchColumn();
    }
}