<?php

declare(strict_types=1);

class LogController
{
    private AuthModel $modelo;

    public function __construct()
    {
        $this->modelo = new AuthModel();
    }

    public function index(): void
    {
        $busqueda = Sanitizador::texto($_GET['q'] ?? '');
        $logs = $this->modelo->obtenerLogs(200, $busqueda);
        require_once SRC_PATH . '/views/logs/index.php';
    }
}
