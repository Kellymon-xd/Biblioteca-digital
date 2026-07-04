<?php

require_once __DIR__ . '/config.php';

$mod    = Sanitizador::alfanumerico($_GET['mod'] ?? 'auth');
$accion = Sanitizador::alfanumerico($_GET['accion'] ?? 'index');

switch ($mod) {
    case 'auth':
        $controller = new AuthController();
        break;
    case 'dashboard':
        $controller = new DashboardController();
        break;
    case 'usuarios':
        $controller = new UsuarioController();
        break;
    case 'estudiantes':
        $controller = new EstudianteController();
        break;
    case 'carreras':
        $controller = new CarreraController();
        break;
    case 'categorias':
        $controller = new CategoriaController();
        break;
    case 'libros':
        $controller = new LibroController();
        break;
    case 'reservas':
        $controller = new ReservaController();
        break;
    case 'solicitudes':
        $controller = new SolicitudController();
        break;
    case 'portal':
        $controller = new PortalController();
        break;
    case 'api':
        $controller = new ApiController();
        break;
    default:
        http_response_code(404);
        require_once SRC_PATH . '/views/error404.php';
        exit;
}

if (!method_exists($controller, $accion)) {
    http_response_code(404);
    require_once SRC_PATH . '/views/error404.php';
    exit;
}

$modulosPrivados = ['dashboard', 'usuarios', 'estudiantes', 'carreras', 'categorias', 'libros'];
if (in_array($mod, $modulosPrivados, true) && empty($_SESSION['usuario'])) {
    ErrorHandler::redirigir('auth', 'login');
}

$controller->{$accion}();
