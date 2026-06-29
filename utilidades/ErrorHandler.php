<?php

declare(strict_types=1);

class ErrorHandler
{
    private const SESSION_KEY = 'flash_messages';

    public static function agregarMensaje(string $tipo, string $mensaje): void
    {
        $mensajes = $_SESSION[self::SESSION_KEY] ?? [];
        if (!isset($mensajes[$tipo])) {
            $mensajes[$tipo] = [];
        }
        $mensajes[$tipo][] = $mensaje;
        $_SESSION[self::SESSION_KEY] = $mensajes;
    }

    public static function obtenerMensajes(): array
    {
        $mensajes = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]);
        return $mensajes;
    }

    public static function redirigir(string $mod = 'auth', string $accion = 'index', array $params = []): void
    {
        $url = BASE_URL . '/index.php?mod=' . urlencode($mod) . '&accion=' . urlencode($accion);
        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }
        header('Location: ' . $url);
        exit;
    }
}
