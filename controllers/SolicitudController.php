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
        exigirPermiso('solicitudes');

        $busqueda = Sanitizador::texto($_GET['q'] ?? '');
        $pagina = max(1, Sanitizador::entero($_GET['pag'] ?? 1));

        $solicitudes = $this->modelo->obtenerTodos($pagina, $busqueda);
        $total = $this->modelo->contarTotal($busqueda);
        $paginas = (int)ceil($total / POR_PAGINA);

        require_once SRC_PATH . '/views/solicitudes/index.php';
    }

    public function mis(): void
    {
        if (empty($_SESSION['lector']['id']) || empty($_SESSION['lector']['tipo'])) {
            ErrorHandler::redirigir('portal', 'login');
        }

        $tipo = $_SESSION['lector']['tipo'];
        $id = (int)$_SESSION['lector']['id'];

        $misSolicitudes = $this->modelo->obtenerPorLector($tipo, $id);

        require_once SRC_PATH . '/views/portal/solicitar.php';
    }

    public function crear(): void
    {
        if (empty($_SESSION['lector']['id']) || empty($_SESSION['lector']['tipo'])) {
            ErrorHandler::redirigir('portal', 'login');
        }

        CsrfToken::verificarPost();

        $d = Sanitizador::sanitizarPost([
            'titulo' => 'nombre',
            'autor' => 'nombre',
            'area' => 'texto',
            'materia' => 'texto',
            'motivo' => 'texto',
            'tipo_solicitud' => 'texto',
            'institucion_sugerida' => 'texto',
            'descripcion' => 'texto',
        ]);

        $areas = [
            'Matemáticas',
            'Ciencias',
            'Tecnologías',
            'Deporte',
            'Salud',
            'Revistas Científicas',
            'Sistemas',
            'Lógica',
            'Química',
            'Estadística'
        ];

        if (!in_array($d['area'], $areas, true)) {
            $d['area'] = 'Tecnologías';
        }

        if (!in_array($d['tipo_solicitud'], ['COMPRA', 'INTERBIBLIOTECARIO'], true)) {
            $d['tipo_solicitud'] = 'COMPRA';
        }

        $tipo = $_SESSION['lector']['tipo'];
        $id = (int)$_SESSION['lector']['id'];

        $d['id_estudiante'] = null;
        $d['id_profesor'] = null;

        if ($tipo === 'PROFESOR') {
            $d['id_profesor'] = $id;
        } else {
            $d['id_estudiante'] = $id;
        }

        $ok = $this->modelo->insertar($d);

        ErrorHandler::agregarMensaje(
            $ok ? 'success' : 'danger',
            $ok ? 'Solicitud enviada a la administración.' : 'Error al enviar la solicitud.'
        );

        ErrorHandler::redirigir('solicitudes', 'mis');
    }

    public function responder(): void
    {
        exigirPermiso('solicitudes');

        CsrfToken::verificarPost();

        $id = Sanitizador::entero($_POST['id'] ?? 0);

        $d = Sanitizador::sanitizarPost([
            'estado' => 'texto',
            'observaciones' => 'texto'
        ]);

        if (!in_array($d['estado'], ['PENDIENTE', 'REVISADA', 'APROBADA', 'RECHAZADA'], true)) {
            $d['estado'] = 'PENDIENTE';
        }

        $ok = $this->modelo->actualizar($id, $d);

        ErrorHandler::agregarMensaje(
            $ok ? 'success' : 'danger',
            $ok ? 'Solicitud actualizada.' : 'Error al actualizar la solicitud.'
        );

        ErrorHandler::redirigir('solicitudes');
    }
}