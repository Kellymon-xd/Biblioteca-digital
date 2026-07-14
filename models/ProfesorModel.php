<?php

declare(strict_types=1);

class ProfesorModel implements IRepositorio
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
            'SELECT * FROM profesores
             WHERE cip LIKE ? OR primer_nombre LIKE ? OR primer_apellido LIKE ? OR email LIKE ? OR departamento LIKE ?
             ORDER BY primer_apellido, primer_nombre
             LIMIT ? OFFSET ?'
        );
        $stmt->execute([$like, $like, $like, $like, $like, POR_PAGINA, $offset]);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM profesores WHERE id_profesor=?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function insertar(array $d): int|false
    {
        $hash = !empty($d['password']) ? $this->passwords->generar($d['password']) : null;
        $stmt = $this->pdo->prepare(
            'INSERT INTO profesores (cip, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido,
             fecha_nacimiento, email, departamento, especialidad, password_hash, activo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $ok = $stmt->execute([
            $d['cip'], $d['primer_nombre'], $d['segundo_nombre'] ?: null,
            $d['primer_apellido'], $d['segundo_apellido'] ?: null,
            $d['fecha_nacimiento'] ?: null, $d['email'] ?: null,
            $d['departamento'] ?: null, $d['especialidad'] ?: null,
            $hash, (int)($d['activo'] ?? 1),
        ]);
        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    public function actualizar(int $id, array $d): bool
    {
        if (!empty($d['password'])) {
            $hash = $this->passwords->generar($d['password']);
            $stmt = $this->pdo->prepare(
                'UPDATE profesores SET cip=?, primer_nombre=?, segundo_nombre=?, primer_apellido=?, segundo_apellido=?,
                 fecha_nacimiento=?, email=?, departamento=?, especialidad=?, password_hash=?, activo=? WHERE id_profesor=?'
            );
            return $stmt->execute([
                $d['cip'], $d['primer_nombre'], $d['segundo_nombre'] ?: null,
                $d['primer_apellido'], $d['segundo_apellido'] ?: null,
                $d['fecha_nacimiento'] ?: null, $d['email'] ?: null,
                $d['departamento'] ?: null, $d['especialidad'] ?: null,
                $hash, (int)$d['activo'], $id,
            ]);
        }

        $stmt = $this->pdo->prepare(
            'UPDATE profesores SET cip=?, primer_nombre=?, segundo_nombre=?, primer_apellido=?, segundo_apellido=?,
             fecha_nacimiento=?, email=?, departamento=?, especialidad=?, activo=? WHERE id_profesor=?'
        );
        return $stmt->execute([
            $d['cip'], $d['primer_nombre'], $d['segundo_nombre'] ?: null,
            $d['primer_apellido'], $d['segundo_apellido'] ?: null,
            $d['fecha_nacimiento'] ?: null, $d['email'] ?: null,
            $d['departamento'] ?: null, $d['especialidad'] ?: null,
            (int)$d['activo'], $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE profesores SET activo=0 WHERE id_profesor=?');
        return $stmt->execute([$id]);
    }

    public function contarTotal(string $busqueda = ''): int
    {
        $like = "%$busqueda%";
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM profesores WHERE cip LIKE ? OR primer_nombre LIKE ? OR primer_apellido LIKE ? OR email LIKE ? OR departamento LIKE ?');
        $stmt->execute([$like, $like, $like, $like, $like]);
        return (int)$stmt->fetchColumn();
    }

    public function existeCIP(string $cip, int $excluirId = 0): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM profesores WHERE cip=? AND id_profesor!=?');
        $stmt->execute([$cip, $excluirId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function existeEmail(string $email, int $excluirId = 0): bool
    {
        if ($email === '') {
            return false;
        }
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM profesores WHERE email=? AND id_profesor!=?');
        $stmt->execute([$email, $excluirId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function desbloquear(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE profesores SET bloqueado=0, intentos_fallidos=0 WHERE id_profesor=?');
        return $stmt->execute([$id]);
    }
}
