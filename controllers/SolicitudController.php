<?php
declare(strict_types=1);

class SolicitudController
{
    private SolicitudModel $modelo;

    public function __construct()
    {
        $this->modelo = new SolicitudModel();
    }

    public function index(): void
    {
        if (empty($_SESSION['usuario'])) {
            ErrorHandler::redirigir('auth', 'login');
        }

        $busqueda    = Sanitizador::texto($_GET['q'] ?? '');
        $pagina      = max(1, Sanitizador::entero($_GET['pag'] ?? 1));
        $solicitudes = $this->modelo->obtenerTodos($pagina, $busqueda);
        $total       = $this->modelo->contarTotal($busqueda);
        $paginas     = (int) ceil($total / POR_PAGINA);

        require_once SRC_PATH . '/views/solicitudes/index.php';
    }

    public function mis(): void
    {
        if (empty($_SESSION['estudiante']['id_estudiante'])) {
            ErrorHandler::redirigir('portal', 'login');
        }

        $idEstudiante   = (int) $_SESSION['estudiante']['id_estudiante'];
        $misSolicitudes = $this->modelo->obtenerPorEstudiante($idEstudiante);

        require_once SRC_PATH . '/views/portal/solicitar.php';
    }

    public function crear(): void
    {
        if (empty($_SESSION['estudiante']['id_estudiante'])) {
            ErrorHandler::redirigir('portal', 'login');
        }

        CsrfToken::verificarPost();

        $d = Sanitizador::sanitizarPost([
            'titulo'      => 'nombre',
            'autor'       => 'nombre',
            'area'        => 'texto',
            'descripcion' => 'texto',
        ]);

        $d['id_estudiante'] = (int) $_SESSION['estudiante']['id_estudiante'];

        $ok = $this->modelo->insertar($d);

        ErrorHandler::agregarMensaje(
            $ok ? 'success' : 'danger',
            $ok ? 'Solicitud enviada a la administración.' : 'Error al enviar.'
        );

        ErrorHandler::redirigir('solicitudes', 'mis');
    }

    public function responder(): void
    {
        if (empty($_SESSION['usuario'])) {
            ErrorHandler::redirigir('auth', 'login');
        }

        CsrfToken::verificarPost();

        $id = Sanitizador::entero($_POST['id'] ?? 0);
        $d  = Sanitizador::sanitizarPost([
            'estado'        => 'texto',
            'observaciones' => 'texto',
        ]);

        $ok = $this->modelo->actualizar($id, $d);

        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Solicitud actualizada.' : 'Error.');
        ErrorHandler::redirigir('solicitudes');
    }
}