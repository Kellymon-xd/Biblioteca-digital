<?php
declare(strict_types=1);

class PortalController
{
    private LibroModel $libros;
    private AuthModel $authModel;

    public function __construct()
    {
        $this->libros = new LibroModel();
        $this->authModel = new AuthModel();
    }

    public function login(): void
    {
        if (!empty($_SESSION['estudiante']['id_estudiante'])) {
            ErrorHandler::redirigir('portal', 'index');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CsrfToken::verificarPost();
            $this->procesarLogin();
            return;
        }
        require_once SRC_PATH . '/views/portal/login.php';
    }

    private function procesarLogin(): void
    {
        $identificador = Sanitizador::texto($_POST['identificador'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($identificador === '' || $password === '') {
            ErrorHandler::agregarMensaje('danger', 'Debes ingresar CIP o email y contraseña.');
            require_once SRC_PATH . '/views/portal/login.php';
            return;
        }

        $estudiante = $this->authModel->buscarEstudiantePorIdentificador($identificador);

        if (!$estudiante) {
            $this->authModel->registrarLog('LOGIN_FALLIDO', $identificador, 'Estudiante no encontrado');
            ErrorHandler::agregarMensaje('danger', 'Credenciales incorrectas.');
            require_once SRC_PATH . '/views/portal/login.php';
            return;
        }

        if ($estudiante['bloqueado']) {
            ErrorHandler::agregarMensaje('danger', 'Tu cuenta está bloqueada. Contacta a la biblioteca.');
            require_once SRC_PATH . '/views/portal/login.php';
            return;
        }

        if (empty($estudiante['password_hash']) || !password_verify($password, $estudiante['password_hash'])) {
            $this->authModel->registrarIntentoFallido((int) $estudiante['id_estudiante'], 'estudiantes');
            $restantes = MAX_LOGIN_INTENTOS - ((int) $estudiante['intentos_fallidos'] + 1);
            $this->authModel->registrarLog('LOGIN_FALLIDO', $identificador, 'Contraseña incorrecta');
            $msg = $restantes > 0 ? "Contraseña incorrecta. Intentos restantes: $restantes." : 'Cuenta bloqueada.';
            ErrorHandler::agregarMensaje('danger', $msg);
            require_once SRC_PATH . '/views/portal/login.php';
            return;
        }

        session_regenerate_id(true);
        $_SESSION['estudiante'] = [
            'id_estudiante' => (int) $estudiante['id_estudiante'],
            'nombre' => $estudiante['primer_nombre'] . ' ' . $estudiante['primer_apellido'],
            'cip' => $estudiante['cip'],
            'carrera' => $estudiante['carrera'],
        ];
        $this->authModel->registrarLoginExitoso((int) $estudiante['id_estudiante'], 'estudiantes');
        $this->authModel->registrarLog('LOGIN_EXITOSO', $identificador, 'Acceso portal');
        ErrorHandler::redirigir('portal', 'index');
    }

    public function index(): void
    {
        $busqueda = Sanitizador::texto($_GET['q'] ?? '');
        $idCategoria = Sanitizador::entero($_GET['cat'] ?? 0);

        $libros = $this->libros->buscarParaPortal($busqueda, $idCategoria);
        $categorias = (new CategoriaModel())->obtenerTodosActivos();

        $misReservas = [];

        if (!empty($_SESSION['estudiante']['id_estudiante'])) {
            $idEstudiante = (int) $_SESSION['estudiante']['id_estudiante'];
            $misReservas = (new ReservaModel())->obtenerPorEstudiante($idEstudiante);
        }

        require_once SRC_PATH . '/views/portal/index.php';
    }

    public function logout(): void
    {
        $this->authModel->registrarLog('CIERRE_SESION', $_SESSION['estudiante']['cip'] ?? '', 'Cierre portal');
        unset($_SESSION['estudiante']);
        ErrorHandler::redirigir('portal', 'login');
    }

    public function estadisticas(): void
    {
        $desde = Sanitizador::fecha($_GET['desde'] ?? date('Y-01-01'));
        $hasta = Sanitizador::fecha($_GET['hasta'] ?? date('Y-12-31'));
        $datos = $this->libros->estadisticasPorPeriodo($desde, $hasta);
        require_once SRC_PATH . '/views/portal/estadisticas.php';
    }
}
