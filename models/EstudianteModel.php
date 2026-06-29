<?php
declare(strict_types=1);

class EstudianteModel implements IRepositorio
{
    private PDO $pdo;
    public function __construct() { $this->pdo = Conexion::obtenerInstancia()->getConexion(); }

    public function obtenerTodos(int $pagina = 1, string $busqueda = ''): array
    {
        $offset = ($pagina - 1) * POR_PAGINA;
        $like   = "%$busqueda%";
        $stmt   = $this->pdo->prepare(
            'SELECT e.*, c.nombre AS carrera
             FROM   estudiantes e
             JOIN   carreras c ON c.id_carrera = e.id_carrera
             WHERE  e.cip LIKE ? OR e.primer_nombre LIKE ? OR e.primer_apellido LIKE ? OR e.email LIKE ?
             ORDER  BY e.primer_apellido, e.primer_nombre
             LIMIT  ? OFFSET ?'
        );
        $stmt->execute([$like, $like, $like, $like, POR_PAGINA, $offset]);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.*, c.nombre AS carrera FROM estudiantes e JOIN carreras c ON c.id_carrera=e.id_carrera WHERE e.id_estudiante=?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function insertar(array $d): int|false
    {
        $hash = !empty($d['password']) ? password_hash($d['password'], PASSWORD_BCRYPT, ['cost'=>12]) : null;
        $stmt = $this->pdo->prepare(
            'INSERT INTO estudiantes (cip, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido,
             fecha_nacimiento, id_carrera, email, password_hash)
             VALUES (?,?,?,?,?,?,?,?,?)'
        );
        $ok = $stmt->execute([
            $d['cip'], $d['primer_nombre'], $d['segundo_nombre'] ?: null,
            $d['primer_apellido'], $d['segundo_apellido'] ?: null,
            $d['fecha_nacimiento'], $d['id_carrera'], $d['email'] ?: null, $hash,
        ]);
        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    public function actualizar(int $id, array $d): bool
    {
        if (!empty($d['password'])) {
            $hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost'=>12]);
            $stmt = $this->pdo->prepare(
                'UPDATE estudiantes SET cip=?, primer_nombre=?, segundo_nombre=?, primer_apellido=?,
                 segundo_apellido=?, fecha_nacimiento=?, id_carrera=?, email=?, password_hash=?, activo=?
                 WHERE id_estudiante=?'
            );
            return $stmt->execute([
                $d['cip'], $d['primer_nombre'], $d['segundo_nombre'] ?: null,
                $d['primer_apellido'], $d['segundo_apellido'] ?: null,
                $d['fecha_nacimiento'], $d['id_carrera'], $d['email'] ?: null,
                $hash, $d['activo'], $id,
            ]);
        }

        $stmt = $this->pdo->prepare(
            'UPDATE estudiantes SET cip=?, primer_nombre=?, segundo_nombre=?, primer_apellido=?,
             segundo_apellido=?, fecha_nacimiento=?, id_carrera=?, email=?, activo=? WHERE id_estudiante=?'
        );
        return $stmt->execute([
            $d['cip'], $d['primer_nombre'], $d['segundo_nombre'] ?: null,
            $d['primer_apellido'], $d['segundo_apellido'] ?: null,
            $d['fecha_nacimiento'], $d['id_carrera'], $d['email'] ?: null, $d['activo'], $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE estudiantes SET activo=0 WHERE id_estudiante=?');
        return $stmt->execute([$id]);
    }

    public function contarTotal(string $busqueda = ''): int
    {
        $like = "%$busqueda%";
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM estudiantes WHERE cip LIKE ? OR primer_nombre LIKE ? OR primer_apellido LIKE ?');
        $stmt->execute([$like, $like, $like]);
        return (int)$stmt->fetchColumn();
    }

    public function existeCIP(string $cip, int $excluirId = 0): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM estudiantes WHERE cip=? AND id_estudiante!=?');
        $stmt->execute([$cip, $excluirId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function desbloquear(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE estudiantes SET bloqueado=0, intentos_fallidos=0 WHERE id_estudiante=?');
        return $stmt->execute([$id]);
    }
}
