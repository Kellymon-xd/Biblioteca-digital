<?php
/**
 * Conexion.php
 *
 * Clase de conexión a la base de datos mediante PDO.
 * La configuración se toma desde .env cuando existe y mantiene valores por defecto
 * para que el proyecto también funcione en XAMPP sin configuración adicional.
 */

declare(strict_types=1);

if (!class_exists('Env')) {
    require_once dirname(__DIR__) . '/utilidades/Env.php';
}

Env::load(dirname(__DIR__) . '/.env');

class Conexion
{
    private static ?Conexion $instancia = null;
    private PDO $conexion;

    private function __construct()
    {
        $host = (string) Env::get('DB_HOST', 'localhost');
        $port = Env::int('DB_PORT', 3306);
        $name = (string) Env::get('DB_NAME', 'biblioteca_digital');
        $user = (string) Env::get('DB_USER', 'bib_app');
        $pass = (string) Env::get('DB_PASS', 'B!bl10t3c@_S3cur3#2025');
        $charset = (string) Env::get('DB_CHARSET', 'utf8mb4');

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $host,
            $port,
            $name,
            $charset
        );

        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_FOUND_ROWS   => true,
        ];

        try {
            $this->conexion = new PDO($dsn, $user, $pass, $opciones);
        } catch (PDOException $e) {
            error_log('[Conexion] Error de BD: ' . $e->getMessage());

            if (Env::bool('APP_DEBUG', true)) {
                throw new RuntimeException('No se pudo establecer la conexión con la base de datos: ' . $e->getMessage());
            }

            throw new RuntimeException('No se pudo establecer la conexión con la base de datos.');
        }
    }

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new RuntimeException('No se puede deserializar un Singleton.');
    }

    public static function obtenerInstancia(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }

        return self::$instancia;
    }

    public function getConexion(): PDO
    {
        return $this->conexion;
    }

    public function preparar(string $sql): PDOStatement
    {
        return $this->conexion->prepare($sql);
    }

    public function iniciarTransaccion(): void
    {
        $this->conexion->beginTransaction();
    }

    public function confirmar(): void
    {
        $this->conexion->commit();
    }

    public function revertir(): void
    {
        $this->conexion->rollBack();
    }

    public function ultimoId(): string
    {
        return $this->conexion->lastInsertId();
    }
}
