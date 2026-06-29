<?php

declare(strict_types=1);

class FirmaDigital
{
    public static function generar(array $datos): string
    {
        ksort($datos);
        return hash_hmac('sha256', json_encode($datos, JSON_UNESCAPED_UNICODE), APP_SECRET);
    }

    public static function verificar(array $datos, ?string $firmaGuardada): bool
    {
        if (empty($firmaGuardada)) {
            return false;
        }

        $firmaCalculada = self::generar($datos);

        return hash_equals($firmaGuardada, $firmaCalculada);
    }

    public static function libro(array $libro): string
    {
        return self::generar([
            'id_libro' => (int) ($libro['id_libro'] ?? 0),
            'unidades_totales' => (int) ($libro['unidades_totales'] ?? 0),
            'unidades_disponibles' => (int) ($libro['unidades_disponibles'] ?? 0),
        ]);
    }

    public static function reserva(array $reserva): string
    {
        return self::generar([
            'id_reserva' => (int) ($reserva['id_reserva'] ?? 0),
            'id_estudiante' => (int) ($reserva['id_estudiante'] ?? 0),
            'id_libro' => (int) ($reserva['id_libro'] ?? 0),
            'estado' => (string) ($reserva['estado'] ?? ''),
        ]);
    }

    public static function registrarAuditoria(
        string $tabla,
        int $idRegistro,
        string $accion,
        ?string $firmaAnterior,
        ?string $firmaNueva,
        ?int $idUsuario
    ): void {
        try {
            $pdo = Conexion::obtenerInstancia()->getConexion();
            $stmt = $pdo->prepare(
                'INSERT INTO auditoria_firmas
                 (tabla_afectada, id_registro, accion, firma_anterior, firma_nueva, id_usuario, ip_address)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $tabla,
                $idRegistro,
                $accion,
                $firmaAnterior ?: null,
                $firmaNueva ?: null,
                $idUsuario,
                $_SERVER['REMOTE_ADDR'] ?? 'desconocida',
            ]);
        } catch (Throwable $e) {
            error_log('[FirmaDigital] No se pudo registrar auditoria: ' . $e->getMessage());
        }
    }
}
