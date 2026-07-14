<?php

declare(strict_types=1);

class ConfiguracionController
{
    private ParametroModel $modelo;

    public function __construct()
    {
        $this->modelo = new ParametroModel();
    }

    public function index(): void
    {
        $diasEstudiante = $this->modelo->diasPrestamo('ESTUDIANTE');
        $diasProfesor = $this->modelo->diasPrestamo('PROFESOR');
        $diasAdministrativo = $this->modelo->diasPrestamo('ADMINISTRATIVO');
        require_once SRC_PATH . '/views/configuracion/index.php';
    }

    public function guardar(): void
    {
        CsrfToken::verificarPost();
        $diasEstudiante = max(1, Sanitizador::entero($_POST['dias_prestamo_estudiante'] ?? DIAS_PRESTAMO_ESTUDIANTE));
        $diasProfesor = max(1, Sanitizador::entero($_POST['dias_prestamo_profesor'] ?? DIAS_PRESTAMO_PROFESOR));
        $diasAdministrativo = max(1, Sanitizador::entero($_POST['dias_prestamo_administrativo'] ?? DIAS_PRESTAMO_PROFESOR));

        $ok = $this->modelo->guardar('dias_prestamo_estudiante', (string)$diasEstudiante)
            && $this->modelo->guardar('dias_prestamo_profesor', (string)$diasProfesor)
            && $this->modelo->guardar('dias_prestamo_administrativo', (string)$diasAdministrativo);

        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Configuración actualizada.' : 'No se pudo actualizar la configuración.');
        ErrorHandler::redirigir('configuracion');
    }
}
