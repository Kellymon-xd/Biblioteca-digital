<?php

declare(strict_types=1);

/**
 * AuthModel — autenticación y bitácora de accesos.
 * Bloquea automáticamente tras MAX_LOGIN_INTENTOS intentos fallidos.
 */
class AuthModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::obtenerInstancia()->getConexion();
    }

    public function buscarPorUsername(string $username): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.username,
                    u.password_hash, u.rol, u.id_rol, u.activo, u.intentos_fallidos, u.bloqueado,
                    r.nombre AS rol_nombre, r.modulos AS permisos
             FROM usuarios u
             LEFT JOIN roles r ON r.id_rol = u.id_rol
             WHERE u.username = ? AND u.activo = 1
             LIMIT 1'
        );
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function buscarEstudiantePorIdentificador(string $identificador): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.id_estudiante, e.primer_nombre, e.primer_apellido, e.email,
                    e.cip, e.password_hash, e.activo, e.intentos_fallidos, e.bloqueado,
                    c.nombre AS carrera
             FROM estudiantes e
             JOIN carreras c ON c.id_carrera = e.id_carrera
             WHERE (e.cip = ? OR e.email = ?) AND e.activo = 1 LIMIT 1'
        );
        $stmt->execute([$identificador, $identificador]);
        return $stmt->fetch();
    }

    public function buscarProfesorPorIdentificador(string $identificador): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_profesor, cip, primer_nombre, primer_apellido, email, departamento,
                    password_hash, activo, intentos_fallidos, bloqueado
             FROM profesores
             WHERE (cip = ? OR email = ?) AND activo = 1 LIMIT 1'
        );
        $stmt->execute([$identificador, $identificador]);
        return $stmt->fetch();
    }

    public function registrarIntentoFallido(int $id, string $tabla = 'usuarios'): void
    {
        $col = match ($tabla) {
            'estudiantes' => 'id_estudiante',
            'profesores' => 'id_profesor',
            default => 'id_usuario',
        };
        $stmt = $this->pdo->prepare(
            "UPDATE $tabla
             SET intentos_fallidos = intentos_fallidos + 1,
                 bloqueado = IF(intentos_fallidos + 1 >= ?, 1, 0)
             WHERE $col = ?"
        );
        $stmt->execute([MAX_LOGIN_INTENTOS, $id]);
    }

    public function registrarLoginExitoso(int $id, string $tabla = 'usuarios'): void
    {
        $col = match ($tabla) {
            'estudiantes' => 'id_estudiante',
            'profesores' => 'id_profesor',
            default => 'id_usuario',
        };
        $stmt = $this->pdo->prepare(
            "UPDATE $tabla
             SET intentos_fallidos = 0, bloqueado = 0, ultimo_login = NOW()
             WHERE $col = ?"
        );
        $stmt->execute([$id]);
    }

    public function registrarLog(
        string $accion,
        string $identificador,
        string $descripcion,
        ?int $idUsuario = null,
        ?int $idEstudiante = null,
        string $tipoActor = 'usuario',
        ?int $idProfesor = null
    ): void {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
            if ($ip === '::1') {
                $ip = '127.0.0.1';
            }

            $stmt = $this->pdo->prepare(
                'INSERT INTO login_logs (
                    username, ip_address, user_agent, accion, descripcion,
                    id_usuario, id_estudiante, id_profesor, tipo_actor, identificador
                 ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $identificador,
                $ip,
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $accion,
                $descripcion,
                $idUsuario,
                $idEstudiante,
                $idProfesor,
                $tipoActor,
                $identificador,
            ]);
        } catch (Throwable $e) {
            error_log('[AuthModel::registrarLog] ' . $e->getMessage());
        }
    }

    public function obtenerLogs(int $limite = 50, string $busqueda = ''): array
    {
        $limite = max(1, min(300, $limite));
        $like = "%$busqueda%";
        $stmt = $this->pdo->prepare(
            'SELECT * FROM login_logs
             WHERE username LIKE ? OR identificador LIKE ? OR ip_address LIKE ? OR tipo_actor LIKE ? OR accion LIKE ?
             ORDER BY fecha DESC LIMIT ?'
        );
        $stmt->execute([$like, $like, $like, $like, $like, $limite]);
        return $stmt->fetchAll();
    }
}
