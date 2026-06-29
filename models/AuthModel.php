<?php
declare(strict_types=1);

/**
 * AuthModel — autenticación de usuarios y bitácora de accesos.
 * Implementa bloqueo automático tras MAX_LOGIN_INTENTOS intentos fallidos.
 */
class AuthModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::obtenerInstancia()->getConexion();
    }

    /** Busca usuario por username */
    public function buscarPorUsername(string $username): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_usuario, nombre, apellido, email, username,
                    password_hash, rol, activo, intentos_fallidos, bloqueado
             FROM   usuarios WHERE username = ? LIMIT 1'
        );
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    /** Busca estudiante por CIP o email para el portal */
    public function buscarEstudiantePorIdentificador(string $identificador): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.id_estudiante, e.primer_nombre, e.primer_apellido, e.email,
                    e.cip, e.password_hash, e.activo, e.intentos_fallidos, e.bloqueado,
                    c.nombre AS carrera
             FROM   estudiantes e
             JOIN   carreras c ON c.id_carrera = e.id_carrera
             WHERE  (e.cip = ? OR e.email = ?) AND e.activo = 1 LIMIT 1'
        );
        $stmt->execute([$identificador, $identificador]);
        return $stmt->fetch();
    }

    /** Incrementa intentos fallidos; bloquea si llega al límite */
    public function registrarIntentoFallido(int $idUsuario, string $tabla = 'usuarios'): void
    {
        $col = ($tabla === 'usuarios') ? 'id_usuario' : 'id_estudiante';
        $stmt = $this->pdo->prepare(
            "UPDATE $tabla
             SET intentos_fallidos = intentos_fallidos + 1,
                 bloqueado = IF(intentos_fallidos + 1 >= ?, 1, 0)
             WHERE $col = ?"
        );
        $stmt->execute([MAX_LOGIN_INTENTOS, $idUsuario]);
    }

    /** Restablece intentos y actualiza último login */
    public function registrarLoginExitoso(int $idUsuario, string $tabla = 'usuarios'): void
    {
        $col = ($tabla === 'usuarios') ? 'id_usuario' : 'id_estudiante';
        $stmt = $this->pdo->prepare(
            "UPDATE $tabla
             SET intentos_fallidos = 0, bloqueado = 0, ultimo_login = NOW()
             WHERE $col = ?"
        );
        $stmt->execute([$idUsuario]);
    }

    /** Guarda un registro en login_logs */
    public function registrarLog(
        string $accion,
        string $identificador,
        string $descripcion,
        ?int $idUsuario = null,
        ?int $idEstudiante = null,
        string $tipoActor = 'usuario'
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO login_logs (
            username,
            ip_address,
            user_agent,
            accion,
            descripcion,
            id_usuario,
            id_estudiante,
            tipo_actor
         ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $identificador,
            $_SERVER['REMOTE_ADDR'] ?? 'desconocida',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $accion,
            $descripcion,
            $idUsuario,
            $idEstudiante,
            $tipoActor,
        ]);
    }

    /** Obtiene los últimos logs para el dashboard */
    public function obtenerLogs(int $limite = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM login_logs ORDER BY fecha DESC LIMIT ?'
        );
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    }
}
