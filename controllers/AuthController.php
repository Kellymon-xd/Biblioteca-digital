<?php
declare(strict_types=1);

class AuthController
{
    private AuthModel $modelo;

    public function __construct()
    {
        $this->modelo = new AuthModel();
    }

    public function index(): void
    {
        $this->login();
    }

    public function login(): void
    {
        if (!empty($_SESSION['usuario'])) {
            ErrorHandler::redirigir('dashboard', 'index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfToken::verificarPost();
            $this->procesarLogin();
            return;
        }

        require_once SRC_PATH . '/views/login.php';
    }

    private function procesarLogin(): void
    {
        $username = Sanitizador::texto($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            ErrorHandler::agregarMensaje('danger', 'Usuario y contraseña son obligatorios.');
            ErrorHandler::redirigir('auth', 'login');
        }

        $usuario = $this->modelo->buscarPorUsername($username);
        if (!$usuario) {
            ErrorHandler::agregarMensaje('danger', 'Credenciales incorrectas.');
            ErrorHandler::redirigir('auth', 'login');
        }

        if ((int)$usuario['bloqueado'] === 1) {
            ErrorHandler::agregarMensaje('danger', 'Cuenta bloqueada. Contacta al administrador.');
            ErrorHandler::redirigir('auth', 'login');
        }

        if (!password_verify($password, $usuario['password_hash'])) {
            $this->modelo->registrarIntentoFallido((int)$usuario['id_usuario']);
            $restantes = MAX_LOGIN_INTENTOS - ((int)$usuario['intentos_fallidos'] + 1);
            $this->modelo->registrarLog('LOGIN_FALLIDO', $username, 'Contraseña incorrecta', (int)$usuario['id_usuario']);
            if ($restantes <= 0) {
                ErrorHandler::agregarMensaje('danger', 'Cuenta bloqueada tras demasiados intentos.');
            } else {
                ErrorHandler::agregarMensaje('danger', "Credenciales incorrectas. Intentos restantes: {$restantes}.");
            }
            ErrorHandler::redirigir('auth', 'login');
        }

        session_regenerate_id(true);
        // Normalizar roles históricos a los permitidos: 'administrador' y 'operador'
        $rol = $usuario['rol'];
        if ($rol === 'admin') {
            $rol = 'administrador';
        } elseif ($rol === 'bibliotecario') {
            // Mapear 'bibliotecario' a 'operador' para mantener compatibilidad
            $rol = 'operador';
        }

        $_SESSION['usuario'] = [
            'id_usuario' => (int)$usuario['id_usuario'],
            'nombre'     => $usuario['nombre'] . ' ' . $usuario['apellido'],
            'username'   => $usuario['username'],
            'rol'        => $rol,
        ];

        $this->modelo->registrarLoginExitoso((int)$usuario['id_usuario']);
        $this->modelo->registrarLog('LOGIN_EXITOSO', $username, 'Ingreso administrativo', (int)$usuario['id_usuario']);

        ErrorHandler::redirigir('dashboard', 'index');
    }

    public function logout(): void
    {
        if (!empty($_SESSION['usuario'])) {
            $usuario = $_SESSION['usuario']['username'] ?? '';
            $this->modelo->registrarLog('CIERRE_SESION', $usuario, 'Salida de sesión', $_SESSION['usuario']['id_usuario'] ?? null);
        }

        unset($_SESSION['usuario']);
        ErrorHandler::redirigir('auth', 'login');
    }
}
