<?php
declare(strict_types=1);

class UsuarioModel implements IRepositorio
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::obtenerInstancia()->getConexion();
    }

    public function obtenerTodos(int $pagina = 1, string $busqueda = ''): array
    {
        $offset = ($pagina - 1) * POR_PAGINA;
        $like   = "%$busqueda%";
        $stmt   = $this->pdo->prepare(
            'SELECT id_usuario, nombre, apellido, email, username, rol, activo, bloqueado, ultimo_login
             FROM   usuarios
             WHERE  nombre LIKE ? OR apellido LIKE ? OR email LIKE ? OR username LIKE ?
             ORDER  BY nombre ASC
             LIMIT  ? OFFSET ?'
        );
        $stmt->execute([$like, $like, $like, $like, POR_PAGINA, $offset]);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_usuario, nombre, apellido, email, username, rol, activo FROM usuarios WHERE id_usuario = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function insertar(array $d): int|false
    {
        $hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nombre, apellido, email, username, password_hash, rol)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $ok = $stmt->execute([$d['nombre'], $d['apellido'], $d['email'], $d['username'], $hash, $d['rol']]);
        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    public function actualizar(int $id, array $d): bool
    {
        // Si viene contraseña nueva, se actualiza; si no, se deja igual
        if (!empty($d['password'])) {
            $hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $this->pdo->prepare(
                'UPDATE usuarios SET nombre=?, apellido=?, email=?, username=?, password_hash=?, rol=?, activo=?
                 WHERE id_usuario=?'
            );
            return $stmt->execute([$d['nombre'], $d['apellido'], $d['email'], $d['username'], $hash, $d['rol'], $d['activo'], $id]);
        }
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET nombre=?, apellido=?, email=?, username=?, rol=?, activo=? WHERE id_usuario=?'
        );
        return $stmt->execute([$d['nombre'], $d['apellido'], $d['email'], $d['username'], $d['rol'], $d['activo'], $id]);
    }

    public function eliminar(int $id): bool
    {
        // Baja lógica — nunca borramos usuarios (auditoría)
        $stmt = $this->pdo->prepare('UPDATE usuarios SET activo = 0 WHERE id_usuario = ?');
        return $stmt->execute([$id]);
    }

    public function contarTotal(string $busqueda = ''): int
    {
        $like = "%$busqueda%";
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM usuarios WHERE nombre LIKE ? OR apellido LIKE ? OR email LIKE ? OR username LIKE ?'
        );
        $stmt->execute([$like, $like, $like, $like]);
        return (int)$stmt->fetchColumn();
    }

    public function desbloquear(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET bloqueado=0, intentos_fallidos=0 WHERE id_usuario=?');
        return $stmt->execute([$id]);
    }

    public function existeEmail(string $email, int $excluirId = 0): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE email=? AND id_usuario != ?');
        $stmt->execute([$email, $excluirId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function existeUsername(string $username, int $excluirId = 0): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE username=? AND id_usuario != ?');
        $stmt->execute([$username, $excluirId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}