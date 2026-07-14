<?php

declare(strict_types=1);

class UsuarioModel implements IRepositorio
{
    private PDO $pdo;
    private HashPasswordService $passwords;

    public function __construct()
    {
        $this->pdo = Conexion::obtenerInstancia()->getConexion();
        $this->passwords = new HashPasswordService();
    }

    public function obtenerTodos(int $pagina = 1, string $busqueda = ''): array
    {
        $offset = ($pagina - 1) * POR_PAGINA;
        $like = "%$busqueda%";
        $stmt = $this->pdo->prepare(
            'SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.username, u.rol, u.id_rol,
                    COALESCE(r.nombre, u.rol) AS rol_nombre, u.activo, u.bloqueado, u.ultimo_login
             FROM usuarios u
             LEFT JOIN roles r ON r.id_rol = u.id_rol
             WHERE u.nombre LIKE ? OR u.apellido LIKE ? OR u.email LIKE ? OR u.username LIKE ? OR COALESCE(r.nombre,u.rol) LIKE ?
             ORDER BY u.nombre ASC
             LIMIT ? OFFSET ?'
        );
        $stmt->execute([$like, $like, $like, $like, $like, POR_PAGINA, $offset]);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.username, u.rol, u.id_rol, u.activo
             FROM usuarios u WHERE u.id_usuario = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function insertar(array $d): int|false
    {
        $hash = $this->passwords->generar($d['password']);
        $rolTexto = $this->rolTextoDesdeId((int)($d['id_rol'] ?? 0));
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nombre, apellido, email, username, password_hash, rol, id_rol, activo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $ok = $stmt->execute([
            $d['nombre'],
            $d['apellido'],
            $d['email'],
            $d['username'],
            $hash,
            $rolTexto,
            $d['id_rol'] ?: null,
            (int)($d['activo'] ?? 1),
        ]);
        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    public function actualizar(int $id, array $d): bool
    {
        $rolTexto = $this->rolTextoDesdeId((int)($d['id_rol'] ?? 0));
        if (!empty($d['password'])) {
            $hash = $this->passwords->generar($d['password']);
            $stmt = $this->pdo->prepare(
                'UPDATE usuarios SET nombre=?, apellido=?, email=?, username=?, password_hash=?, rol=?, id_rol=?, activo=?
                 WHERE id_usuario=?'
            );
            return $stmt->execute([$d['nombre'], $d['apellido'], $d['email'], $d['username'], $hash, $rolTexto, $d['id_rol'] ?: null, $d['activo'], $id]);
        }
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET nombre=?, apellido=?, email=?, username=?, rol=?, id_rol=?, activo=? WHERE id_usuario=?'
        );
        return $stmt->execute([$d['nombre'], $d['apellido'], $d['email'], $d['username'], $rolTexto, $d['id_rol'] ?: null, $d['activo'], $id]);
    }

    private function rolTextoDesdeId(int $idRol): string
    {
        if ($idRol <= 0) {
            return 'operador';
        }
        $stmt = $this->pdo->prepare('SELECT nombre, modulos FROM roles WHERE id_rol=? LIMIT 1');
        $stmt->execute([$idRol]);
        $rol = $stmt->fetch();
        if (!$rol) {
            return 'operador';
        }
        return ((string)($rol['modulos'] ?? '') === '*') ? 'administrador' : 'operador';
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET activo = 0 WHERE id_usuario = ?');
        return $stmt->execute([$id]);
    }

    public function contarTotal(string $busqueda = ''): int
    {
        $like = "%$busqueda%";
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM usuarios u LEFT JOIN roles r ON r.id_rol=u.id_rol
             WHERE u.nombre LIKE ? OR u.apellido LIKE ? OR u.email LIKE ? OR u.username LIKE ? OR COALESCE(r.nombre,u.rol) LIKE ?'
        );
        $stmt->execute([$like, $like, $like, $like, $like]);
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
