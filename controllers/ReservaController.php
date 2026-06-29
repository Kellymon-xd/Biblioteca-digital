<?php
declare(strict_types=1);

class ReservaController
{
    private ReservaModel $modelo;
    private SolicitudModel $solicitudes;

    public function __construct()
    {
        $this->modelo = new ReservaModel();
        $this->solicitudes = new SolicitudModel();
    }

    public function index(): void
    {
        if (empty($_SESSION['estudiante']['id_estudiante'])) {
            ErrorHandler::redirigir('portal', 'login');
        }
        $idEstudiante = (int) $_SESSION['estudiante']['id_estudiante'];
        $reservas = $this->modelo->obtenerPorEstudiante($idEstudiante);
        $misSolicitudes = $this->solicitudes->obtenerPorEstudiante($idEstudiante);
        require_once SRC_PATH . '/views/portal/reservas.php';
    }
    

    public function reservar(): void
    {
        if (empty($_SESSION['estudiante']['id_estudiante'])) {
            ErrorHandler::redirigir('portal', 'login');
        }

        CsrfToken::verificarPost();

        $idLibro = Sanitizador::entero($_POST['id_libro'] ?? 0);
        $idEstudiante = (int) $_SESSION['estudiante']['id_estudiante'];
        $fechaDev = Sanitizador::fecha($_POST['fecha_devolucion'] ?? date('Y-m-d', strtotime('+15 days')));

        error_log('[ReservaController] idLibro=' . $idLibro);
        error_log('[ReservaController] idEstudiante=' . $idEstudiante);
        error_log('[ReservaController] fechaDev=' . $fechaDev);

        $nuevo = $this->modelo->insertar([
            'id_estudiante' => $idEstudiante,
            'id_libro' => $idLibro,
            'fecha_devolucion_esperada' => $fechaDev,
        ]);

        $msg = $nuevo
            ? '¡Reserva realizada! Recoge tu libro en biblioteca.'
            : 'No se pudo realizar la reserva. Verifica que el libro tenga unidades disponibles.';

        ErrorHandler::agregarMensaje($nuevo ? 'success' : 'warning', $msg);
        ErrorHandler::redirigir('portal', 'index');
    }

    public function devolver(): void
    {
        if (empty($_SESSION['estudiante']['id_estudiante'])) {
            ErrorHandler::redirigir('portal', 'login');
        }
        CsrfToken::verificarPost();
        $id = Sanitizador::entero($_POST['id_reserva'] ?? 0);
        $ok = $this->modelo->devolver($id);
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Libro devuelto. ¡Gracias!' : 'Error al registrar devolución.');
        ErrorHandler::redirigir('reservas');
    }

    public function solicitarLibro(): void
    {
        if (empty($_SESSION['estudiante']['id_estudiante'])) {
            ErrorHandler::redirigir('portal', 'login');
        }
        CsrfToken::verificarPost();
        $d = Sanitizador::sanitizarPost([
            'titulo' => 'nombre',
            'autor' => 'nombre',
            'area' => 'texto',
            'descripcion' => 'texto',
        ]);
        $d['id_estudiante'] = (int) $_SESSION['estudiante']['id_estudiante'];
        $ok = $this->solicitudes->insertar($d);
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Solicitud enviada a la administración.' : 'Error al enviar.');
        ErrorHandler::redirigir('reservas');
    }

    // Admin: ver todas las reservas
    public function admin(): void
    {
        if (empty($_SESSION['usuario'])) {
            ErrorHandler::redirigir('auth', 'login');
        }
        $busqueda = Sanitizador::texto($_GET['q'] ?? '');
        $pagina = max(1, Sanitizador::entero($_GET['pag'] ?? 1));
        $reservas = $this->modelo->obtenerTodos($pagina, $busqueda);
        $total = $this->modelo->contarTotal($busqueda);
        $paginas = (int) ceil($total / POR_PAGINA);
        require_once SRC_PATH . '/views/libros/reservas_admin.php';
    }
}
