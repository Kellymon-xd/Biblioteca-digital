<?php

declare(strict_types=1);

require_once __DIR__ . '/utilidades/Env.php';

Env::load(__DIR__ . '/.env');

$env = strtolower((string) Env::get('APP_ENV', 'local'));
$debug = Env::bool('APP_DEBUG', $env !== 'production');
$isProduction = $env === 'production';

date_default_timezone_set((string) Env::get('APP_TIMEZONE', 'America/Panama'));

if ($debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
}

if (!function_exists('appEsHttps')) {
    function appEsHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
            return true;
        }
        if (($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on') {
            return true;
        }
        return false;
    }
}

if (!defined('BASE_URL')) {
    $urlConfigurada = trim((string) Env::get('APP_URL', ''));

    if ($urlConfigurada !== '' && strtolower($urlConfigurada) !== 'auto') {
        define('BASE_URL', rtrim($urlConfigurada, '/'));
    } else {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $root = rtrim(str_replace('\\', '/', dirname($script)), '/');

        define('BASE_URL', ($root === '' || $root === '.' || $root === '/') ? '' : $root);
    }
}

if (!defined('SRC_PATH')) {
    define('SRC_PATH', __DIR__);
}

if (!defined('MAX_LOGIN_INTENTOS')) {
    define('MAX_LOGIN_INTENTOS', Env::int('MAX_LOGIN_INTENTOS', 3));
}

if (!defined('POR_PAGINA')) {
    define('POR_PAGINA', Env::int('POR_PAGINA', 10));
}

$secret = (string) Env::get('APP_SECRET', 'biblioteca-digital-cambiar-en-produccion-2026');
$secretCheck = strtolower($secret);
if ($isProduction && ($secret === '' || str_contains($secretCheck, 'cambiar') || str_contains($secretCheck, 'cambia_') || strlen($secret) < 32)) {
    throw new RuntimeException('APP_SECRET no es válido para producción. Define una clave larga y única en .env.');
}
if (!defined('APP_SECRET')) {
    define('APP_SECRET', $secret);
}

if (!defined('DIAS_PRESTAMO_ESTUDIANTE')) {
    define('DIAS_PRESTAMO_ESTUDIANTE', Env::int('DIAS_PRESTAMO_ESTUDIANTE', 3));
}

if (!defined('DIAS_PRESTAMO_PROFESOR')) {
    define('DIAS_PRESTAMO_PROFESOR', Env::int('DIAS_PRESTAMO_PROFESOR', 3));
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

if (!function_exists('modulosPermisos')) {
    function modulosPermisos(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'usuarios' => 'Usuarios',
            'roles' => 'Roles y permisos',
            'estudiantes' => 'Estudiantes',
            'profesores' => 'Profesores',
            'carreras' => 'Carreras',
            'categorias' => 'Categorías',
            'libros' => 'Libros',
            'reservas' => 'Reservas y reportes',
            'solicitudes' => 'Solicitudes de libros',
            'logs' => 'Bitácora de acceso',
            'configuracion' => 'Configuración de préstamos',
        ];
    }
}

if (!function_exists('usuarioPuede')) {
    function usuarioPuede(string $modulo): bool
    {
        $usuario = $_SESSION['usuario'] ?? null;
        if (empty($usuario)) {
            return false;
        }

        $rol = normalizarRol((string)($usuario['rol'] ?? ''), (string)($usuario['username'] ?? ''));
        if ($rol === 'administrador') {
            return true;
        }

        $permisos = $usuario['permisos'] ?? [];
        if (is_string($permisos)) {
            $permisos = array_filter(array_map('trim', explode(',', $permisos)));
        }

        return in_array('*', $permisos, true) || in_array($modulo, $permisos, true);
    }
}

if (!function_exists('exigirPermiso')) {
    function exigirPermiso(string $modulo): void
    {
        if (empty($_SESSION['usuario'])) {
            ErrorHandler::redirigir('auth', 'login');
        }

        if (!usuarioPuede($modulo)) {
            http_response_code(403);
            ErrorHandler::agregarMensaje('danger', 'No tienes permiso para acceder a este módulo.');
            ErrorHandler::redirigir('dashboard', 'index');
        }
    }
}

if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', (string) Env::get('UPLOADS_PATH', __DIR__ . '/public/uploads'));
}

if (!defined('UPLOADS_URL')) {
    define('UPLOADS_URL', rtrim(BASE_URL, '/') . '/uploads');
}

if (!defined('THUMB_WIDTH')) {
    define('THUMB_WIDTH', 220);
}

if (!defined('THUMB_HEIGHT')) {
    define('THUMB_HEIGHT', 300);
}

// Cookies de sesión endurecidas. En producción deben viajar por HTTPS.
if (session_status() === PHP_SESSION_NONE) {
    $secureCookie = Env::bool('SESSION_SECURE', $isProduction || appEsHttps());
    session_name((string) Env::get('SESSION_NAME', 'BIBLIOTECA_SESION'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

if (Env::bool('SECURE_HEADERS', true) && !headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
    if (appEsHttps()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    if (Env::bool('CSP_ENABLED', true)) {
        $csp = (string) Env::get(
            'CSP_VALUE',
            "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'"
        );
        header('Content-Security-Policy: ' . $csp);
    }
}

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
require_once SRC_PATH . '/utilidades/TransformadorDatosInterface.php';
require_once SRC_PATH . '/utilidades/HashPasswordService.php';
require_once SRC_PATH . '/utilidades/FirmaDigitalService.php';
require_once SRC_PATH . '/utilidades/ErrorHandler.php';
require_once SRC_PATH . '/utilidades/Sanitizador.php';
require_once SRC_PATH . '/utilidades/CsrfToken.php';
require_once SRC_PATH . '/utilidades/Validador.php';
require_once SRC_PATH . '/utilidades/FirmaDigital.php';

require_once SRC_PATH . '/conexion/Conexion.php';

require_once SRC_PATH . '/models/AuthModel.php';
require_once SRC_PATH . '/models/RolModel.php';
require_once SRC_PATH . '/models/UsuarioModel.php';
require_once SRC_PATH . '/models/EstudianteModel.php';
require_once SRC_PATH . '/models/ProfesorModel.php';
require_once SRC_PATH . '/models/CarreraModel.php';
require_once SRC_PATH . '/models/CategoriaModel.php';
require_once SRC_PATH . '/models/LibroModel.php';
require_once SRC_PATH . '/models/ReservaModel.php';
require_once SRC_PATH . '/models/SolicitudModel.php';
require_once SRC_PATH . '/models/ParametroModel.php';

require_once SRC_PATH . '/controllers/AuthController.php';
require_once SRC_PATH . '/controllers/RolController.php';
require_once SRC_PATH . '/controllers/CarreraController.php';
require_once SRC_PATH . '/controllers/CategoriaController.php';
require_once SRC_PATH . '/controllers/EstudianteController.php';
require_once SRC_PATH . '/controllers/ProfesorController.php';
require_once SRC_PATH . '/controllers/LibroController.php';
require_once SRC_PATH . '/controllers/PortalController.php';
require_once SRC_PATH . '/controllers/ReservaController.php';
require_once SRC_PATH . '/controllers/SolicitudController.php';
require_once SRC_PATH . '/controllers/UsuarioController.php';
require_once SRC_PATH . '/controllers/LogController.php';
require_once SRC_PATH . '/controllers/ConfiguracionController.php';
require_once SRC_PATH . '/controllers/ApiController.php';
require_once SRC_PATH . '/controllers/DashboardController.php';
