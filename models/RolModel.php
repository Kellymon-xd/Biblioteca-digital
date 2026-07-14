<?php

declare(strict_types=1);

class RolModel implements IRepositorio
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
            'SELECT * FROM roles
             WHERE nombre LIKE ? OR descripcion LIKE ?
             ORDER BY nombre ASC LIMIT ? OFFSET ?'
        );
        $stmt->execute([$like, $like, POR_PAGINA, $offset]);
        return $stmt->fetchAll();
    }

    public function obtenerTodosActivos(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM roles WHERE activo=1 ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM roles WHERE id_rol=?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function insertar(array $d): int|false
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO roles (nombre, descripcion, modulos, activo) VALUES (?, ?, ?, ?)'
        );
        $ok = $stmt->execute([
            $d['nombre'],
            $d['descripcion'] ?: null,
            $d['modulos'] ?: '',
            (int)$d['activo'],
        ]);
        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    public function actualizar(int $id, array $d): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE roles SET nombre=?, descripcion=?, modulos=?, activo=? WHERE id_rol=?'
        );
        return $stmt->execute([
            $d['nombre'],
            $d['descripcion'] ?: null,
            $d['modulos'] ?: '',
            (int)$d['activo'],
            $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        if ($id === 1) {
            return false;
        }
        $stmt = $this->pdo->prepare('UPDATE roles SET activo=0 WHERE id_rol=?');
        return $stmt->execute([$id]);
    }

    public function contarTotal(string $busqueda = ''): int
    {
        $like = "%$busqueda%";
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM roles WHERE nombre LIKE ? OR descripcion LIKE ?');
        $stmt->execute([$like, $like]);
        return (int)$stmt->fetchColumn();
    }

    public function existeNombre(string $nombre, int $excluirId = 0): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM roles WHERE nombre=? AND id_rol != ?');
        $stmt->execute([$nombre, $excluirId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
