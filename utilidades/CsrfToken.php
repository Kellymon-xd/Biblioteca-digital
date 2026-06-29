<?php

declare(strict_types=1);

class CsrfToken
{
    public static function generar(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
        }
        return $_SESSION['csrf_token'];
    }

    public static function campoOculto(): string
    {
        return sprintf('<input type="hidden" name="csrf_token" value="%s">', self::generar());
    }

    public static function verificarPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !hash_equals((string)($_SESSION['csrf_token'] ?? ''), $token)) {
            http_response_code(403);
            die('Token CSRF inválido.');
        }
    }
}
