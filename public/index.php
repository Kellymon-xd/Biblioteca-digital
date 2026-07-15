<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';

$modDefault = isset($_SESSION['usuario']) ? 'dashboard' : 'portal';
$mod = Sanitizador::alfanumerico($_GET['mod'] ?? $modDefault);
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
    case 'roles':
        $controller = new RolController();
        break;
    case 'estudiantes':
        $controller = new EstudianteController();
        break;
    case 'profesores':
        $controller = new ProfesorController();
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
    case 'logs':
        $controller = new LogController();
        break;
    case 'configuracion':
        $controller = new ConfiguracionController();
        break;
    case 'api':
        if (!Env::bool('API_ENABLED', false)) {
            http_response_code(404);
            require_once SRC_PATH . '/views/error404.php';
            exit;
        }
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

$modulosPrivados = ['dashboard', 'usuarios', 'roles', 'estudiantes', 'profesores', 'carreras', 'categorias', 'libros', 'logs', 'configuracion'];
if (in_array($mod, $modulosPrivados, true)) {
    exigirPermiso($mod);
}

$controller->{$accion}();
