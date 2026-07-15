<?php

declare(strict_types=1);

class ParametroModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::obtenerInstancia()->getConexion();
    }

    public function obtener(string $clave, string $default = ''): string
    {
        try {
            $stmt = $this->pdo->prepare('SELECT valor FROM parametros WHERE clave=? LIMIT 1');
            $stmt->execute([$clave]);
            $valor = $stmt->fetchColumn();
            return $valor !== false ? (string)$valor : $default;
        } catch (Throwable $e) {
            error_log('[ParametroModel::obtener] ' . $e->getMessage());
            return $default;
        }
    }

    public function obtenerInt(string $clave, int $default): int
    {
        return max(1, (int)$this->obtener($clave, (string)$default));
    }

    public function guardar(string $clave, string $valor): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO parametros (clave, valor) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE valor=VALUES(valor), updated_at=NOW()'
        );
        return $stmt->execute([$clave, $valor]);
    }

    public function diasPrestamo(string $tipoActor): int
    {
        return match (strtoupper($tipoActor)) {
            'PROFESOR' => $this->obtenerInt('dias_prestamo_profesor', DIAS_PRESTAMO_PROFESOR),
            default => $this->obtenerInt('dias_prestamo_estudiante', DIAS_PRESTAMO_ESTUDIANTE),
        };
    }
}
