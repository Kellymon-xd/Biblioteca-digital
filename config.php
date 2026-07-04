<?php

declare(strict_types=1);

// Ajusta esta constante según el nombre de carpeta que uses en tu servidor local.
// Ejemplo: http://localhost/biblioteca_digital
if (!defined('BASE_URL')) {
    $root = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    define('BASE_URL', $root === '' ? '/' : $root);
}

if (!defined('SRC_PATH')) {
    define('SRC_PATH', __DIR__);
}

if (!defined('MAX_LOGIN_INTENTOS')) {
    define('MAX_LOGIN_INTENTOS', 3);
}

if (!defined('POR_PAGINA')) {
    define('POR_PAGINA', 10);
}

if (!defined('APP_SECRET')) {
    define('APP_SECRET', 'biblioteca-digital-cambiar-en-produccion-2026');
}

if (!function_exists('normalizarRol')) {
    function normalizarRol(string $rol, string $username = ''): string
    {
        $rol = strtolower(trim($rol));
        $username = strtolower(trim($username));

        if ($rol === '') {
            return $username === 'admin' ? 'administrador' : 'operador';
        }

        return match ($rol) {
            'admin', 'administrador' => 'administrador',
            'bibliotecario', 'operador' => 'operador',
            default => $rol,
        };
    }
}

if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', __DIR__ . '/uploads');
}

if (!defined('UPLOADS_URL')) {
    define('UPLOADS_URL', BASE_URL . '/uploads');
}

if (!defined('THUMB_WIDTH')) {
    define('THUMB_WIDTH', 220);
}

if (!defined('THUMB_HEIGHT')) {
    define('THUMB_HEIGHT', 300);
}

session_start();

if (!empty($_SESSION['usuario'])) {
    $_SESSION['usuario']['rol'] = normalizarRol(
        (string) ($_SESSION['usuario']['rol'] ?? ''),
        (string) ($_SESSION['usuario']['username'] ?? '')
    );
}

foreach ([UPLOADS_PATH, UPLOADS_PATH . '/libros', UPLOADS_PATH . '/libros/orig', UPLOADS_PATH . '/libros/thumb'] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

// Cargar clases utilidades.
require_once SRC_PATH . '/utilidades/IRepositorio.php';
require_once SRC_PATH . '/utilidades/ErrorHandler.php';
require_once SRC_PATH . '/utilidades/Sanitizador.php';
require_once SRC_PATH . '/utilidades/CsrfToken.php';
require_once SRC_PATH . '/utilidades/Validador.php';
require_once SRC_PATH . '/utilidades/FirmaDigital.php';

require_once SRC_PATH . '/conexion/Conexion.php';

require_once SRC_PATH . '/models/AuthModel.php';
require_once SRC_PATH . '/models/UsuarioModel.php';
require_once SRC_PATH . '/models/EstudianteModel.php';
require_once SRC_PATH . '/models/CarreraModel.php';
require_once SRC_PATH . '/models/CategoriaModel.php';
require_once SRC_PATH . '/models/LibroModel.php';
require_once SRC_PATH . '/models/ReservaModel.php';
require_once SRC_PATH . '/models/SolicitudModel.php';
require_once SRC_PATH . '/controllers/AuthController.php';
require_once SRC_PATH . '/controllers/CarreraController.php';
require_once SRC_PATH . '/controllers/CategoriaController.php';
require_once SRC_PATH . '/controllers/EstudianteController.php';
require_once SRC_PATH . '/controllers/LibroController.php';
require_once SRC_PATH . '/controllers/PortalController.php';
require_once SRC_PATH . '/controllers/ReservaController.php';
require_once SRC_PATH . '/controllers/SolicitudController.php';
require_once SRC_PATH . '/controllers/UsuarioController.php';
require_once SRC_PATH . '/controllers/ApiController.php';
require_once SRC_PATH . '/views/DashboardController.php';
