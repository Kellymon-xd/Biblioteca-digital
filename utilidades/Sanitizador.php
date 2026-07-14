<?php

declare(strict_types=1);

class Sanitizador
{
    public static function texto(string $valor): string
    {
        $valor = trim(strip_tags($valor));
        return preg_replace('/\s+/', ' ', $valor) ?? '';
    }

    public static function alfanumerico(string $valor): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $valor) ?? '';
    }

    public static function entero($valor): int
    {
        return filter_var($valor, FILTER_VALIDATE_INT) ?: 0;
    }

    public static function decimal($valor): float
    {
        $valor = str_replace(',', '.', (string)$valor);
        return is_numeric($valor) ? (float)$valor : 0.0;
    }

    public static function fecha(string $valor): string
    {
        $valor = trim($valor);
        if ($valor === '') {
            return '';
        }
        $time = strtotime($valor);
        return $time ? date('Y-m-d', $time) : '';
    }

    public static function html(string $valor): string
    {
        return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function sanitizarPost(array $reglas): array
    {
        $datos = [];
        foreach ($reglas as $campo => $tipo) {
            $valor = $_POST[$campo] ?? '';
            if ($tipo === 'texto') {
                $datos[$campo] = self::texto((string)$valor);
            } elseif ($tipo === 'nombre') {
                $datos[$campo] = self::nombrePropio((string)$valor);
            } elseif ($tipo === 'email') {
                $datos[$campo] = filter_var((string)$valor, FILTER_SANITIZE_EMAIL);
            } elseif ($tipo === 'int') {
                $datos[$campo] = self::entero($valor);
            } elseif ($tipo === 'decimal') {
                $datos[$campo] = self::decimal($valor);
            } elseif ($tipo === 'fecha') {
                $datos[$campo] = self::fecha((string)$valor);
            } else {
                $datos[$campo] = self::texto((string)$valor);
            }
        }
        return $datos;
    }

    public static function guardarViejosDatos(array $datos): void
    {
        foreach (['csrf_token', 'password', 'password_confirm', 'confirm_password'] as $campo) {
            unset($datos[$campo]);
        }
        $_SESSION['old_input'] = $datos;
    }

    public static function guardarPostComoViejosDatos(): void
    {
        self::guardarViejosDatos($_POST);
    }

    public static function obtenerViejosDatos(): array
    {
        $datos = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);
        return $datos;
    }

    public static function nombrePropio(string $valor): string
    {
        $valor = self::texto($valor);
        return mb_convert_case($valor, MB_CASE_TITLE, 'UTF-8');
    }
}
