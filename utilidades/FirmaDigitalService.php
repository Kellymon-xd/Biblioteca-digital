<?php

declare(strict_types=1);

class FirmaDigitalService implements TransformadorDatosInterface
{
    public function generar(array|string $datos): string
    {
        $payload = is_array($datos) ? $datos : ['valor' => (string)$datos];
        return FirmaDigital::generar($payload);
    }

    public function verificar(array|string $datos, string $valorGuardado): bool
    {
        $payload = is_array($datos) ? $datos : ['valor' => (string)$datos];
        return FirmaDigital::verificar($payload, $valorGuardado);
    }
}
