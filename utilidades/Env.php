<?php

/**
 * Env.php
 *
 * Cargador liviano de variables .env sin depender de Composer.
 * El archivo .env es opcional: si no existe, la aplicación usa valores por defecto.
 */

declare(strict_types=1);

final class Env
{
    private static bool $cargado = false;

    public static function load(?string $ruta = null): void
    {
        if (self::$cargado) {
            return;
        }

        $ruta = $ruta ?? dirname(__DIR__) . '/.env';

        if (!is_file($ruta) || !is_readable($ruta)) {
            self::$cargado = true;
            return;
        }

        $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lineas === false) {
            self::$cargado = true;
            return;
        }

        foreach ($lineas as $linea) {
            $linea = trim($linea);

            if ($linea === '' || str_starts_with($linea, '#')) {
                continue;
            }

            if (!str_contains($linea, '=')) {
                continue;
            }

            [$clave, $valor] = explode('=', $linea, 2);
            $clave = trim($clave);
            $valor = trim($valor);

            if ($clave === '') {
                continue;
            }

            $valor = self::limpiarValor($valor);

            // No sobrescribir variables reales del entorno.
            // Esto permite que Docker inyecte DB_HOST=db aunque exista un .env local de XAMPP.
            if (getenv($clave) === false && !array_key_exists($clave, $_ENV) && !array_key_exists($clave, $_SERVER)) {
                $_ENV[$clave] = $valor;
                $_SERVER[$clave] = $valor;
                putenv($clave . '=' . $valor);
            }
        }

        self::$cargado = true;
    }

    public static function get(string $clave, mixed $default = null): mixed
    {
        $valor = getenv($clave);

        if ($valor !== false) {
            return $valor;
        }

        if (array_key_exists($clave, $_ENV)) {
            return $_ENV[$clave];
        }

        if (array_key_exists($clave, $_SERVER)) {
            return $_SERVER[$clave];
        }

        return $default;
    }

    public static function int(string $clave, int $default = 0): int
    {
        return (int) self::get($clave, $default);
    }

    public static function bool(string $clave, bool $default = false): bool
    {
        $valor = strtolower((string) self::get($clave, $default ? 'true' : 'false'));

        return in_array($valor, ['1', 'true', 'yes', 'on', 'si', 'sí'], true);
    }

    private static function limpiarValor(string $valor): string
    {
        if ($valor === '') {
            return '';
        }

        $primer = $valor[0];
        $ultimo = $valor[strlen($valor) - 1];

        if (($primer === '"' && $ultimo === '"') || ($primer === "'" && $ultimo === "'")) {
            return substr($valor, 1, -1);
        }

        $posComentario = strpos($valor, ' #');
        if ($posComentario !== false) {
            $valor = substr($valor, 0, $posComentario);
        }

        return trim($valor);
    }
}
