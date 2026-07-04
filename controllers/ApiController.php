<?php
declare(strict_types=1);

class ApiController
{
    private AuthModel $authModel;
    private LibroModel $libroModel;
    private ReservaModel $reservaModel;
    private UsuarioModel $usuarioModel;
    private CategoriaModel $categoriaModel;
    private CarreraModel $carreraModel;
    private EstudianteModel $estudianteModel;
    private SolicitudModel $solicitudModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
        $this->libroModel = new LibroModel();
        $this->reservaModel = new ReservaModel();
        $this->usuarioModel = new UsuarioModel();
        $this->categoriaModel = new CategoriaModel();
        $this->carreraModel = new CarreraModel();
        $this->estudianteModel = new EstudianteModel();
        $this->solicitudModel = new SolicitudModel();
    }

    private function jsonResponse(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function readBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            if ($raw !== false) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return $_POST;
    }

    private function requireAuth(): void
    {
        if (!empty($_SESSION['usuario'])) {
            return;
        }

        $token = '';
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
        }

        if ($token === '') {
            $token = (string) ($_SERVER['HTTP_X_API_TOKEN'] ?? '');
        }

        if ($token === '') {
            $token = (string) ($_COOKIE['api_token'] ?? '');
        }

        $expectedToken = (string) ($_SESSION['api_token'] ?? '');
        if ($expectedToken !== '' && hash_equals($expectedToken, $token)) {
            return;
        }

        $this->jsonResponse(['error' => 'Autenticación requerida'], 401);
    }

    private function requireRole(string $role): void
    {
        $this->requireAuth();
        if (($_SESSION['usuario']['rol'] ?? '') !== $role) {
            $this->jsonResponse(['error' => 'Permiso denegado'], 403);
        }
    }

    private function normalizeRole(string $rol): string
    {
        if ($rol === 'admin') {
            return 'administrador';
        }
        if ($rol === 'bibliotecario') {
            return 'operador';
        }
        return $rol;
    }

    private function getIdFromRequest(array $data = []): int
    {
        return Sanitizador::entero($data['id'] ?? $_GET['id'] ?? 0);
    }

    private function getPagination(array $data = []): array
    {
        $pagina = max(1, Sanitizador::entero($data['pag'] ?? $_GET['pag'] ?? 1));
        $busqueda = Sanitizador::texto((string) ($data['q'] ?? $_GET['q'] ?? ''));
        return [$pagina, $busqueda];
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Método no permitido'], 405);
        }

        $data = $this->readBody();
        $username = Sanitizador::texto((string) ($data['username'] ?? $_POST['username'] ?? ''));
        $password = (string) ($data['password'] ?? $_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $this->jsonResponse(['error' => 'Usuario y contraseña son obligatorios'], 400);
        }

        $usuario = $this->authModel->buscarPorUsername($username);
        if (!$usuario) {
            $this->jsonResponse(['error' => 'Credenciales incorrectas'], 401);
        }

        if ((int) $usuario['bloqueado'] === 1) {
            $this->jsonResponse(['error' => 'Cuenta bloqueada. Contacta al administrador.'], 403);
        }

        if (!password_verify($password, $usuario['password_hash'])) {
            $this->authModel->registrarIntentoFallido((int) $usuario['id_usuario']);
            $restantes = MAX_LOGIN_INTENTOS - ((int) $usuario['intentos_fallidos'] + 1);
            $this->authModel->registrarLog('LOGIN_FALLIDO', $username, 'Contraseña incorrecta', (int) $usuario['id_usuario']);
            $msg = $restantes <= 0 ? 'Cuenta bloqueada tras demasiados intentos.' : "Credenciales incorrectas. Intentos restantes: {$restantes}";
            $this->jsonResponse(['error' => $msg], 401);
        }

        session_regenerate_id(true);
        $token = bin2hex(random_bytes(16));
        $_SESSION['usuario'] = [
            'id_usuario' => (int) $usuario['id_usuario'],
            'nombre' => $usuario['nombre'] . ' ' . $usuario['apellido'],
            'username' => $usuario['username'],
            'rol' => $this->normalizeRole($usuario['rol']),
        ];
        $_SESSION['api_token'] = $token;

        $this->authModel->registrarLoginExitoso((int) $usuario['id_usuario']);
        $this->authModel->registrarLog('LOGIN_EXITOSO', $username, 'Ingreso API', (int) $usuario['id_usuario']);

        setcookie('api_token', $token, time() + 3600, '/', '', false, true);

        $this->jsonResponse([
            'success' => true,
            'usuario' => $_SESSION['usuario'],
            'token' => $token,
            'token_type' => 'Bearer',
            'cookie' => 'api_token',
        ]);
    }

    public function logout(): void
    {
        if (!empty($_SESSION['usuario'])) {
            $usuario = $_SESSION['usuario']['username'] ?? '';
            $this->authModel->registrarLog('CIERRE_SESION', $usuario, 'Salida API', $_SESSION['usuario']['id_usuario'] ?? null);
        }

        unset($_SESSION['usuario'], $_SESSION['api_token']);
        setcookie('api_token', '', time() - 3600, '/', '', false, true);
        $this->jsonResponse(['success' => true, 'mensaje' => 'Sesión cerrada']);
    }

    public function perfil(): void
    {
        $this->requireAuth();
        $this->jsonResponse($_SESSION['usuario']);
    }

    public function libros(): void
    {
        $this->requireAuth();
        $data = $this->readBody();
        $id = $this->getIdFromRequest($data);
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if ($id) {
                $libro = $this->libroModel->obtenerPorId($id);
                if (!$libro) {
                    $this->jsonResponse(['error' => 'Libro no encontrado'], 404);
                }
                $this->jsonResponse($libro);
            }

            [$pagina, $busqueda] = $this->getPagination($data);
            $libros = $this->libroModel->obtenerTodos($pagina, $busqueda);
            $this->jsonResponse(['pagina' => $pagina, 'q' => $busqueda, 'datos' => $libros]);
        }

        if ($method === 'POST') {
            $payload = $data;
            $payload['titulo'] = Sanitizador::texto((string) ($payload['titulo'] ?? ''));
            $payload['autor'] = Sanitizador::texto((string) ($payload['autor'] ?? ''));
            $payload['id_categoria'] = Sanitizador::entero($payload['id_categoria'] ?? 0);
            $payload['unidades_totales'] = Sanitizador::entero($payload['unidades_totales'] ?? 1);
            $payload['activo'] = Sanitizador::entero($payload['activo'] ?? 1);

            if ($payload['titulo'] === '' || $payload['autor'] === '' || $payload['id_categoria'] <= 0 || $payload['unidades_totales'] <= 0) {
                $this->jsonResponse(['error' => 'Título, autor, categoría y unidades son obligatorios'], 400);
            }

            $ok = $this->libroModel->insertar($payload);
            if (!$ok) {
                $this->jsonResponse(['error' => 'No se pudo crear el libro'], 500);
            }

            $this->jsonResponse(['success' => true, 'id' => $ok, 'mensaje' => 'Libro creado correctamente'], 201);
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }

            $actual = $this->libroModel->obtenerPorId($id);
            if (!$actual) {
                $this->jsonResponse(['error' => 'Libro no encontrado'], 404);
            }

            $payload = array_merge($actual, $data, ['id_libro' => $id]);
            $payload['titulo'] = Sanitizador::texto((string) ($payload['titulo'] ?? ''));
            $payload['autor'] = Sanitizador::texto((string) ($payload['autor'] ?? ''));
            $payload['id_categoria'] = Sanitizador::entero($payload['id_categoria'] ?? $actual['id_categoria'] ?? 0);
            $payload['unidades_totales'] = Sanitizador::entero($payload['unidades_totales'] ?? $actual['unidades_totales'] ?? 1);
            $payload['activo'] = Sanitizador::entero($payload['activo'] ?? $actual['activo'] ?? 1);

            if ($payload['titulo'] === '' || $payload['autor'] === '' || $payload['id_categoria'] <= 0 || $payload['unidades_totales'] <= 0) {
                $this->jsonResponse(['error' => 'Título, autor, categoría y unidades son obligatorios'], 400);
            }

            $ok = $this->libroModel->actualizar($id, $payload);
            if (!$ok) {
                $this->jsonResponse(['error' => 'No se pudo actualizar el libro'], 500);
            }

            $this->jsonResponse(['success' => true, 'mensaje' => 'Libro actualizado correctamente']);
        }

        if ($method === 'DELETE') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }

            $ok = $this->libroModel->eliminar($id);
            if (!$ok) {
                $this->jsonResponse(['error' => 'No se pudo desactivar el libro'], 500);
            }

            $this->jsonResponse(['success' => true, 'mensaje' => 'Libro desactivado correctamente']);
        }

        $this->jsonResponse(['error' => 'Método no permitido'], 405);
    }

    public function reservas(): void
    {
        $this->requireAuth();
        $data = $this->readBody();
        $id = $this->getIdFromRequest($data);
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if ($id) {
                $reserva = $this->reservaModel->obtenerPorId($id);
                if (!$reserva) {
                    $this->jsonResponse(['error' => 'Reserva no encontrada'], 404);
                }
                $this->jsonResponse($reserva);
            }

            [$pagina, $busqueda] = $this->getPagination($data);
            $rol = $_SESSION['usuario']['rol'] ?? '';
            if ($rol === 'administrador' || $rol === 'operador') {
                $reservas = $this->reservaModel->obtenerTodos($pagina, $busqueda);
                $this->jsonResponse(['pagina' => $pagina, 'q' => $busqueda, 'datos' => $reservas]);
            }

            $idEstudiante = Sanitizador::entero($data['id_estudiante'] ?? 0);
            if ($idEstudiante <= 0) {
                $this->jsonResponse(['error' => 'Debe indicar id_estudiante para listar reservas'], 400);
            }

            $reservas = $this->reservaModel->obtenerPorEstudiante($idEstudiante);
            $this->jsonResponse(['id_estudiante' => $idEstudiante, 'datos' => $reservas]);
        }

        if ($method === 'POST') {
            $payload = $data;
            $payload['id_estudiante'] = Sanitizador::entero($payload['id_estudiante'] ?? 0);
            $payload['id_libro'] = Sanitizador::entero($payload['id_libro'] ?? 0);
            $payload['fecha_devolucion_esperada'] = Sanitizador::fecha((string) ($payload['fecha_devolucion_esperada'] ?? date('Y-m-d', strtotime('+15 days'))));

            if ($payload['id_estudiante'] <= 0 || $payload['id_libro'] <= 0 || $payload['fecha_devolucion_esperada'] === '') {
                $this->jsonResponse(['error' => 'id_estudiante, id_libro y fecha_devolucion_esperada son obligatorios'], 400);
            }

            $nuevoId = $this->reservaModel->insertar($payload);
            if (!$nuevoId) {
                $this->jsonResponse(['error' => 'No se pudo crear la reserva'], 500);
            }

            $this->jsonResponse(['success' => true, 'id' => $nuevoId, 'mensaje' => 'Reserva creada correctamente'], 201);
        }

        if ($method === 'DELETE') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }

            $ok = $this->reservaModel->devolver($id);
            if (!$ok) {
                $this->jsonResponse(['error' => 'No se pudo registrar la devolución'], 500);
            }

            $this->jsonResponse(['success' => true, 'mensaje' => 'Devolución registrada correctamente']);
        }

        $this->jsonResponse(['error' => 'Método no permitido'], 405);
    }

    public function usuarios(): void
    {
        $this->requireRole('administrador');
        $data = $this->readBody();
        $id = $this->getIdFromRequest($data);
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if ($id) {
                $usuario = $this->usuarioModel->obtenerPorId($id);
                if (!$usuario) {
                    $this->jsonResponse(['error' => 'Usuario no encontrado'], 404);
                }
                $this->jsonResponse($usuario);
            }

            [$pagina, $busqueda] = $this->getPagination($data);
            $usuarios = $this->usuarioModel->obtenerTodos($pagina, $busqueda);
            $this->jsonResponse(['pagina' => $pagina, 'q' => $busqueda, 'datos' => $usuarios]);
        }

        if ($method === 'POST') {
            $payload = $data;
            $payload['nombre'] = Sanitizador::texto((string) ($payload['nombre'] ?? ''));
            $payload['apellido'] = Sanitizador::texto((string) ($payload['apellido'] ?? ''));
            $payload['email'] = filter_var((string) ($payload['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $payload['username'] = Sanitizador::texto((string) ($payload['username'] ?? ''));
            $payload['rol'] = Sanitizador::texto((string) ($payload['rol'] ?? ''));
            $payload['password'] = (string) ($payload['password'] ?? '');
            $payload['activo'] = Sanitizador::entero($payload['activo'] ?? 1);

            if ($payload['nombre'] === '' || $payload['apellido'] === '' || $payload['email'] === '' || $payload['username'] === '' || $payload['password'] === '' || !in_array($payload['rol'], ['administrador', 'operador'], true)) {
                $this->jsonResponse(['error' => 'Nombre, apellido, email, username, password y rol válido son obligatorios'], 400);
            }

            $ok = $this->usuarioModel->insertar($payload);
            if (!$ok) {
                $this->jsonResponse(['error' => 'No se pudo crear el usuario'], 500);
            }

            $this->jsonResponse(['success' => true, 'id' => $ok, 'mensaje' => 'Usuario creado correctamente'], 201);
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }

            $payload = $data;
            $payload['nombre'] = Sanitizador::texto((string) ($payload['nombre'] ?? ''));
            $payload['apellido'] = Sanitizador::texto((string) ($payload['apellido'] ?? ''));
            $payload['email'] = filter_var((string) ($payload['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $payload['username'] = Sanitizador::texto((string) ($payload['username'] ?? ''));
            $payload['rol'] = Sanitizador::texto((string) ($payload['rol'] ?? ''));
            $payload['activo'] = Sanitizador::entero($payload['activo'] ?? 1);

            if ($payload['nombre'] === '' || $payload['apellido'] === '' || $payload['email'] === '' || $payload['username'] === '' || !in_array($payload['rol'], ['administrador', 'operador'], true)) {
                $this->jsonResponse(['error' => 'Nombre, apellido, email, username y rol válido son obligatorios'], 400);
            }

            $ok = $this->usuarioModel->actualizar($id, $payload);
            if (!$ok) {
                $this->jsonResponse(['error' => 'No se pudo actualizar el usuario'], 500);
            }

            $this->jsonResponse(['success' => true, 'mensaje' => 'Usuario actualizado correctamente']);
        }

        if ($method === 'DELETE') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }

            $ok = $this->usuarioModel->eliminar($id);
            if (!$ok) {
                $this->jsonResponse(['error' => 'No se pudo desactivar el usuario'], 500);
            }

            $this->jsonResponse(['success' => true, 'mensaje' => 'Usuario desactivado correctamente']);
        }

        $this->jsonResponse(['error' => 'Método no permitido'], 405);
    }

    public function categorias(): void
    {
        $this->requireAuth();
        $categorias = $this->categoriaModel->obtenerTodosActivos();
        $this->jsonResponse(['datos' => $categorias]);
    }

    public function carreras(): void
    {
        $this->requireAuth();
        $carreras = $this->carreraModel->obtenerTodosActivos();
        $this->jsonResponse(['datos' => $carreras]);
    }

    public function estudiantes(): void
    {
        $this->requireAuth();
        [$pagina, $busqueda] = $this->getPagination();
        $estudiantes = $this->estudianteModel->obtenerTodos($pagina, $busqueda);
        $this->jsonResponse(['pagina' => $pagina, 'q' => $busqueda, 'datos' => $estudiantes]);
    }

    public function solicitudes(): void
    {
        $this->requireAuth();
        [$pagina, $busqueda] = $this->getPagination();
        $solicitudes = $this->solicitudModel->obtenerTodos($pagina, $busqueda);
        $this->jsonResponse(['pagina' => $pagina, 'q' => $busqueda, 'datos' => $solicitudes]);
    }
}
