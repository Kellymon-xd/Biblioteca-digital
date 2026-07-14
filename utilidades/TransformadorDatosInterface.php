<?php

declare(strict_types=1);

interface TransformadorDatosInterface
{
    public function generar(array|string $datos): string;

    public function verificar(array|string $datos, string $valorGuardado): bool;
}
