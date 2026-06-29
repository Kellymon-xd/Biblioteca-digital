<?php

declare(strict_types=1);

interface IRepositorio
{
    public function obtenerTodos(int $pagina = 1, string $busqueda = ''): array;
    public function obtenerPorId(int $id): array|false;
    public function insertar(array $datos): int|false;
    public function actualizar(int $id, array $datos): bool;
    public function eliminar(int $id): bool;
}
