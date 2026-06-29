<?php
declare(strict_types=1);

class CategoriaModel implements IRepositorio
{
    private PDO $pdo;
    public function __construct() { $this->pdo = Conexion::obtenerInstancia()->getConexion(); }

    public function obtenerTodos(int $pagina = 1, string $busqueda = ''): array
    {
        $offset = ($pagina - 1) * POR_PAGINA;
        $like   = "%$busqueda%";
        $stmt   = $this->pdo->prepare(
            'SELECT * FROM categorias_libros WHERE nombre LIKE ? ORDER BY nombre ASC LIMIT ? OFFSET ?'
        );
        $stmt->execute([$like, POR_PAGINA, $offset]);
        return $stmt->fetchAll();
    }

    public function obtenerTodosActivos(): array
    {
        return $this->pdo->query(
            'SELECT id_categoria, nombre FROM categorias_libros WHERE activo=1 ORDER BY nombre'
        )->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categorias_libros WHERE id_categoria = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function insertar(array $d): int|false
    {
        $stmt = $this->pdo->prepare('INSERT INTO categorias_libros (nombre, descripcion) VALUES (?, ?)');
        $ok = $stmt->execute([$d['nombre'], $d['descripcion']]);
        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    public function actualizar(int $id, array $d): bool
    {
        $stmt = $this->pdo->prepare('UPDATE categorias_libros SET nombre=?, descripcion=?, activo=? WHERE id_categoria=?');
        return $stmt->execute([$d['nombre'], $d['descripcion'], $d['activo'], $id]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE categorias_libros SET activo=0 WHERE id_categoria=?');
        return $stmt->execute([$id]);
    }

    public function contarTotal(string $busqueda = ''): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM categorias_libros WHERE nombre LIKE ?');
        $stmt->execute(["%$busqueda%"]);
        return (int)$stmt->fetchColumn();
    }
}