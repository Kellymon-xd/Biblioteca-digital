<?php

declare(strict_types=1);

class PortalController
{
    private LibroModel $libros;
    private AuthModel $authModel;
    private ParametroModel $parametros;

    public function __construct()
    {
        $this->libros = new LibroModel();
        $this->authModel = new AuthModel();
        $this->parametros = new ParametroModel();
    }

    public function login(): void
    {
        if (!empty($_SESSION['lector']['id'])) {
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
        $tipo = strtoupper(Sanitizador::texto($_POST['tipo'] ?? 'ESTUDIANTE'));
        if (!in_array($tipo, ['ESTUDIANTE', 'PROFESOR'], true)) {
            $tipo = 'ESTUDIANTE';
        }

        if ($identificador === '' || $password === '') {
            ErrorHandler::agregarMensaje('danger', 'Debes ingresar CIP o email y contraseña.');
            require_once SRC_PATH . '/views/portal/login.php';
            return;
        }

        $persona = $tipo === 'PROFESOR'
            ? $this->authModel->buscarProfesorPorIdentificador($identificador)
            : $this->authModel->buscarEstudiantePorIdentificador($identificador);

        if (!$persona) {
            $this->authModel->registrarLog('LOGIN_FALLIDO', $identificador, $tipo === 'PROFESOR' ? 'Profesor no encontrado' : 'Estudiante no encontrado', null, null, strtolower($tipo));
            ErrorHandler::agregarMensaje('danger', 'Credenciales incorrectas.');
            require_once SRC_PATH . '/views/portal/login.php';
            return;
        }

        if ((int)$persona['bloqueado'] === 1) {
            ErrorHandler::agregarMensaje('danger', 'Tu cuenta está bloqueada. Contacta a la biblioteca.');
            require_once SRC_PATH . '/views/portal/login.php';
            return;
        }

        if (empty($persona['password_hash']) || !password_verify($password, $persona['password_hash'])) {
            if ($tipo === 'PROFESOR') {
                $this->authModel->registrarIntentoFallido((int)$persona['id_profesor'], 'profesores');
            } else {
                $this->authModel->registrarIntentoFallido((int)$persona['id_estudiante'], 'estudiantes');
            }
            $restantes = MAX_LOGIN_INTENTOS - ((int)$persona['intentos_fallidos'] + 1);
            $this->authModel->registrarLog('LOGIN_FALLIDO', $identificador, 'Contraseña incorrecta', null, $tipo === 'ESTUDIANTE' ? (int)$persona['id_estudiante'] : null, strtolower($tipo), $tipo === 'PROFESOR' ? (int)$persona['id_profesor'] : null);
            ErrorHandler::agregarMensaje('danger', $restantes > 0 ? "Contraseña incorrecta. Intentos restantes: $restantes." : 'Cuenta bloqueada.');
            require_once SRC_PATH . '/views/portal/login.php';
            return;
        }

        session_regenerate_id(true);
        $id = $tipo === 'PROFESOR' ? (int)$persona['id_profesor'] : (int)$persona['id_estudiante'];
        $nombre = trim(($persona['primer_nombre'] ?? '') . ' ' . ($persona['primer_apellido'] ?? ''));
        $_SESSION['lector'] = [
            'tipo' => $tipo,
            'id' => $id,
            'nombre' => $nombre,
            'cip' => $persona['cip'],
            'label' => $tipo === 'PROFESOR' ? 'Docente' : 'Estudiante',
        ];

        if ($tipo === 'PROFESOR') {
            $_SESSION['profesor'] = [
                'id_profesor' => $id,
                'nombre' => $nombre,
                'cip' => $persona['cip'],
                'departamento' => $persona['departamento'] ?? '',
            ];
            unset($_SESSION['estudiante']);
            $this->authModel->registrarLoginExitoso($id, 'profesores');
            $this->authModel->registrarLog('LOGIN_EXITOSO', $identificador, 'Acceso portal docente', null, null, 'profesor', $id);
        } else {
            $_SESSION['estudiante'] = [
                'id_estudiante' => $id,
                'nombre' => $nombre,
                'cip' => $persona['cip'],
                'carrera' => $persona['carrera'],
            ];
            unset($_SESSION['profesor']);
            $this->authModel->registrarLoginExitoso($id, 'estudiantes');
            $this->authModel->registrarLog('LOGIN_EXITOSO', $identificador, 'Acceso portal estudiantil', null, $id, 'estudiante');
        }

        ErrorHandler::redirigir('portal', 'index');
    }

    public function index(): void
    {
        $busqueda = Sanitizador::texto($_GET['q'] ?? '');
        $idCategoria = Sanitizador::entero($_GET['cat'] ?? 0);

        $libros = $this->libros->buscarParaPortal($busqueda, $idCategoria);
        $categorias = (new CategoriaModel())->obtenerTodosActivos();
        $diasPrestamo = !empty($_SESSION['lector']) ? $this->parametros->diasPrestamo($_SESSION['lector']['tipo']) : 0;

        require_once SRC_PATH . '/views/portal/index.php';
    }

    public function logout(): void
    {
        if (!empty($_SESSION['lector'])) {
            $this->authModel->registrarLog('CIERRE_SESION', $_SESSION['lector']['cip'] ?? '', 'Cierre portal', null, $_SESSION['lector']['tipo'] === 'ESTUDIANTE' ? (int)$_SESSION['lector']['id'] : null, strtolower($_SESSION['lector']['tipo']), $_SESSION['lector']['tipo'] === 'PROFESOR' ? (int)$_SESSION['lector']['id'] : null);
        }
        unset($_SESSION['lector'], $_SESSION['estudiante'], $_SESSION['profesor']);
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
