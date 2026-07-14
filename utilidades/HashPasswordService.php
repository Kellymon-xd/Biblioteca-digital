<?php

declare(strict_types=1);

class HashPasswordService implements TransformadorDatosInterface
{
    public function generar(array|string $datos): string
    {
        $password = is_array($datos) ? (string)($datos['password'] ?? '') : (string)$datos;
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function verificar(array|string $datos, string $valorGuardado): bool
    {
        $password = is_array($datos) ? (string)($datos['password'] ?? '') : (string)$datos;
        return password_verify($password, $valorGuardado);
    }
}
