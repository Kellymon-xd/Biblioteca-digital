<?php
/**
 * Conexion.php
 * 
 * Clase de conexión a la base de datos mediante PDO.
 * Principios aplicados: Singleton (una sola instancia), SRP (única responsabilidad),
 * DRY (configuración centralizada), OWASP (modo excepción activado).
 * 
 * USUARIO DE BD: bib_app — solo tiene SELECT, INSERT, UPDATE, DELETE
 *                No usa root en producción.
 */

declare(strict_types=1);

class Conexion
{
    // ── Configuración de la base de datos ─────────────────────────────────────
    private const DB_HOST    = 'localhost';
    private const DB_NAME    = 'biblioteca_digital';
    private const DB_USER    = 'bib_app';               // Usuario dedicado (no root)
    private const DB_PASS    = 'B!bl10t3c@_S3cur3#2025';
    private const DB_CHARSET = 'utf8mb4';

    // ── Singleton ─────────────────────────────────────────────────────────────
    private static ?Conexion $instancia = null;
    private PDO $conexion;

    // ── Constructor privado (patrón Singleton) ────────────────────────────────
    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            self::DB_HOST,
            self::DB_NAME,
            self::DB_CHARSET
        );

        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Lanza excepciones
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Retorna arrays asociativos
            PDO::ATTR_EMULATE_PREPARES   => false,                    // Prepared statements reales
            PDO::MYSQL_ATTR_FOUND_ROWS   => true,                     // UPDATE devuelve filas encontradas
        ];

        try {
            $this->conexion = new PDO($dsn, self::DB_USER, self::DB_PASS, $opciones);
        } catch (PDOException $e) {
            // No exponer detalles internos al usuario (OWASP A05)
            error_log('[Conexion] Error de BD: ' . $e->getMessage());
            throw new RuntimeException('No se pudo establecer la conexión con la base de datos.');
        }
    }

    // Evitar clonación y deserialización (Singleton seguro)
    private function __clone() {}
    public function __wakeup(): void
    {
        throw new RuntimeException('No se puede deserializar un Singleton.');
    }

    // ── Obtener instancia única ───────────────────────────────────────────────
    public static function obtenerInstancia(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    // ── Obtener objeto PDO ────────────────────────────────────────────────────
    public function getConexion(): PDO
    {
        return $this->conexion;
    }

    // ── Método de conveniencia: preparar sentencia ────────────────────────────
    public function preparar(string $sql): \PDOStatement
    {
        return $this->conexion->prepare($sql);
    }

    // ── Transacciones ─────────────────────────────────────────────────────────
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

    // ── Último ID insertado ───────────────────────────────────────────────────
    public function ultimoId(): string
    {
        return $this->conexion->lastInsertId();
    }
}
