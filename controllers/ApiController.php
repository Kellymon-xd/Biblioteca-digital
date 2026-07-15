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
    private ProfesorModel $profesorModel;
    private RolModel $rolModel;
    private ParametroModel $parametroModel;

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
        $this->profesorModel = new ProfesorModel();
        $this->rolModel = new RolModel();
        $this->parametroModel = new ParametroModel();
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

    private function text(array $data, string $key, string $default = ''): string
    {
        return Sanitizador::texto((string)($data[$key] ?? $default));
    }

    private function name(array $data, string $key, string $default = ''): string
    {
        return Sanitizador::nombrePropio((string)($data[$key] ?? $default));
    }

    private function email(array $data, string $key, string $default = ''): string
    {
        return filter_var((string)($data[$key] ?? $default), FILTER_SANITIZE_EMAIL) ?: '';
    }

    private function boolInt(array $data, string $key, int $default = 1): int
    {
        return Sanitizador::entero($data[$key] ?? $default) === 0 ? 0 : 1;
    }

    private function pdo(): PDO
    {
        return Conexion::obtenerInstancia()->getConexion();
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

    public function dashboard(): void
    {
        $this->requireAuth();

        $pdo = $this->pdo();
        $totales = [];
        foreach (['libros', 'estudiantes', 'profesores', 'usuarios', 'reservas'] as $tabla) {
            try {
                $totales[$tabla] = (int)$pdo->query("SELECT COUNT(*) FROM {$tabla}")->fetchColumn();
            } catch (Throwable $e) {
                $totales[$tabla] = 0;
            }
        }

        $disponibles = (int)$pdo->query('SELECT COALESCE(SUM(unidades_disponibles),0) FROM libros WHERE activo=1')->fetchColumn();
        $ultimasReservas = $pdo->query(
            "SELECT r.id_reserva, l.titulo,
                    COALESCE(CONCAT(e.primer_nombre,' ',e.primer_apellido), CONCAT(p.primer_nombre,' ',p.primer_apellido), CONCAT(u.nombre,' ',u.apellido)) AS lector,
                    r.tipo_actor, r.estado, r.fecha_reserva
             FROM reservas r
             JOIN libros l ON l.id_libro=r.id_libro
             LEFT JOIN estudiantes e ON e.id_estudiante=r.id_estudiante
             LEFT JOIN profesores p ON p.id_profesor=r.id_profesor
             LEFT JOIN usuarios u ON u.id_usuario=r.id_usuario
             ORDER BY r.fecha_reserva DESC LIMIT 5"
        )->fetchAll();

        $this->jsonResponse([
            'totales' => $totales,
            'libros_disponibles' => $disponibles,
            'ultimas_reservas' => $ultimasReservas,
            'ultimos_logs' => $this->authModel->obtenerLogs(8),
        ]);
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

            try {
                $ok = $this->libroModel->insertar($payload);
            } catch (Throwable $e) {
                error_log('[ApiController::libros POST] ' . $e->getMessage());
                $this->jsonResponse(['error' => 'No se pudo crear el libro. Verifica datos duplicados o inválidos.'], 409);
            }

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

            try {
                $ok = $this->libroModel->actualizar($id, $payload);
            } catch (Throwable $e) {
                error_log('[ApiController::libros UPDATE] ' . $e->getMessage());
                $this->jsonResponse(['error' => 'No se pudo actualizar el libro. Verifica datos duplicados o inválidos.'], 409);
            }

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

            try {
                $ok = $this->usuarioModel->insertar($payload);
            } catch (Throwable $e) {
                error_log('[ApiController::usuarios POST] ' . $e->getMessage());
                $this->jsonResponse(['error' => 'No se pudo crear el usuario. Verifica email o username duplicado.'], 409);
            }

            if (!$ok) {
                $this->jsonResponse(['error' => 'No se pudo crear el usuario'], 500);
            }

            $this->jsonResponse(['success' => true, 'id' => $ok, 'mensaje' => 'Usuario creado correctamente'], 201);
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }

            $actual = $this->usuarioModel->obtenerPorId($id);
            if (!$actual) {
                $this->jsonResponse(['error' => 'Usuario no encontrado'], 404);
            }

            $payload = array_merge($actual, $data);
            $payload['nombre'] = Sanitizador::texto((string) ($payload['nombre'] ?? ''));
            $payload['apellido'] = Sanitizador::texto((string) ($payload['apellido'] ?? ''));
            $payload['email'] = filter_var((string) ($payload['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $payload['username'] = Sanitizador::texto((string) ($payload['username'] ?? ''));
            $payload['rol'] = Sanitizador::texto((string) ($payload['rol'] ?? ''));
            $payload['activo'] = Sanitizador::entero($payload['activo'] ?? 1);

            if ($payload['nombre'] === '' || $payload['apellido'] === '' || $payload['email'] === '' || $payload['username'] === '' || !in_array($payload['rol'], ['administrador', 'operador'], true)) {
                $this->jsonResponse(['error' => 'Nombre, apellido, email, username y rol válido son obligatorios'], 400);
            }

            try {
                $ok = $this->usuarioModel->actualizar($id, $payload);
            } catch (Throwable $e) {
                error_log('[ApiController::usuarios UPDATE] ' . $e->getMessage());
                $this->jsonResponse(['error' => 'No se pudo actualizar el usuario. Verifica email o username duplicado.'], 409);
            }

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
        $data = $this->readBody();
        $id = $this->getIdFromRequest($data);
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if ($id) {
                $categoria = $this->categoriaModel->obtenerPorId($id);
                if (!$categoria) {
                    $this->jsonResponse(['error' => 'Categoría no encontrada'], 404);
                }
                $this->jsonResponse($categoria);
            }

            [$pagina, $busqueda] = $this->getPagination($data);
            if (($_GET['activos'] ?? '') === '1') {
                $this->jsonResponse(['datos' => $this->categoriaModel->obtenerTodosActivos()]);
            }
            $this->jsonResponse(['pagina' => $pagina, 'q' => $busqueda, 'datos' => $this->categoriaModel->obtenerTodos($pagina, $busqueda)]);
        }

        $this->requireRole('administrador');

        if ($method === 'POST') {
            $payload = [
                'nombre' => $this->name($data, 'nombre'),
                'descripcion' => $this->text($data, 'descripcion'),
            ];

            if ($payload['nombre'] === '') {
                $this->jsonResponse(['error' => 'El nombre de la categoría es obligatorio'], 400);
            }

            try {
                $nuevoId = $this->categoriaModel->insertar($payload);
            } catch (Throwable $e) {
                error_log('[ApiController::categorias POST] ' . $e->getMessage());
                $this->jsonResponse(['error' => 'No se pudo crear la categoría. Verifica que no esté duplicada.'], 409);
            }
            $this->jsonResponse(['success' => true, 'id' => $nuevoId, 'mensaje' => 'Categoría creada correctamente'], 201);
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }
            $actual = $this->categoriaModel->obtenerPorId($id);
            if (!$actual) {
                $this->jsonResponse(['error' => 'Categoría no encontrada'], 404);
            }

            $payload = array_merge($actual, $data);
            $payload = [
                'nombre' => $this->name($payload, 'nombre'),
                'descripcion' => $this->text($payload, 'descripcion'),
                'activo' => $this->boolInt($payload, 'activo', (int)$actual['activo']),
            ];

            if ($payload['nombre'] === '') {
                $this->jsonResponse(['error' => 'El nombre de la categoría es obligatorio'], 400);
            }

            try {
                $ok = $this->categoriaModel->actualizar($id, $payload);
            } catch (Throwable $e) {
                error_log('[ApiController::categorias UPDATE] ' . $e->getMessage());
                $this->jsonResponse(['error' => 'No se pudo actualizar la categoría. Verifica que no esté duplicada.'], 409);
            }
            $this->jsonResponse(['success' => $ok, 'mensaje' => 'Categoría actualizada correctamente']);
        }

        if ($method === 'DELETE') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }
            $ok = $this->categoriaModel->eliminar($id);
            $this->jsonResponse(['success' => $ok, 'mensaje' => 'Categoría desactivada correctamente']);
        }

        $this->jsonResponse(['error' => 'Método no permitido'], 405);
    }

    public function carreras(): void
    {
        $this->requireAuth();
        $data = $this->readBody();
        $id = $this->getIdFromRequest($data);
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if ($id) {
                $carrera = $this->carreraModel->obtenerPorId($id);
                if (!$carrera) {
                    $this->jsonResponse(['error' => 'Carrera no encontrada'], 404);
                }
                $this->jsonResponse($carrera);
            }

            [$pagina, $busqueda] = $this->getPagination($data);
            if (($_GET['activos'] ?? '') === '1') {
                $this->jsonResponse(['datos' => $this->carreraModel->obtenerTodosActivos()]);
            }
            $this->jsonResponse(['pagina' => $pagina, 'q' => $busqueda, 'datos' => $this->carreraModel->obtenerTodos($pagina, $busqueda)]);
        }

        $this->requireRole('administrador');

        if ($method === 'POST') {
            $payload = [
                'nombre' => $this->name($data, 'nombre'),
                'codigo' => $this->text($data, 'codigo'),
                'descripcion' => $this->text($data, 'descripcion'),
            ];

            if ($payload['nombre'] === '' || $payload['codigo'] === '') {
                $this->jsonResponse(['error' => 'Nombre y código son obligatorios'], 400);
            }

            try {
                $nuevoId = $this->carreraModel->insertar($payload);
            } catch (Throwable $e) {
                error_log('[ApiController::carreras POST] ' . $e->getMessage());
                $this->jsonResponse(['error' => 'No se pudo crear la carrera. Verifica que el código no esté duplicado.'], 409);
            }
            $this->jsonResponse(['success' => true, 'id' => $nuevoId, 'mensaje' => 'Carrera creada correctamente'], 201);
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }
            $actual = $this->carreraModel->obtenerPorId($id);
            if (!$actual) {
                $this->jsonResponse(['error' => 'Carrera no encontrada'], 404);
            }

            $payload = array_merge($actual, $data);
            $payload = [
                'nombre' => $this->name($payload, 'nombre'),
                'codigo' => $this->text($payload, 'codigo'),
                'descripcion' => $this->text($payload, 'descripcion'),
                'activo' => $this->boolInt($payload, 'activo', (int)$actual['activo']),
            ];

            if ($payload['nombre'] === '' || $payload['codigo'] === '') {
                $this->jsonResponse(['error' => 'Nombre y código son obligatorios'], 400);
            }

            try {
                $ok = $this->carreraModel->actualizar($id, $payload);
            } catch (Throwable $e) {
                error_log('[ApiController::carreras UPDATE] ' . $e->getMessage());
                $this->jsonResponse(['error' => 'No se pudo actualizar la carrera. Verifica que el código no esté duplicado.'], 409);
            }
            $this->jsonResponse(['success' => $ok, 'mensaje' => 'Carrera actualizada correctamente']);
        }

        if ($method === 'DELETE') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }
            $ok = $this->carreraModel->eliminar($id);
            $this->jsonResponse(['success' => $ok, 'mensaje' => 'Carrera desactivada correctamente']);
        }

        $this->jsonResponse(['error' => 'Método no permitido'], 405);
    }

    public function estudiantes(): void
    {
        $this->requireAuth();
        $data = $this->readBody();
        $id = $this->getIdFromRequest($data);
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if ($id) {
                $estudiante = $this->estudianteModel->obtenerPorId($id);
                if (!$estudiante) {
                    $this->jsonResponse(['error' => 'Estudiante no encontrado'], 404);
                }
                $this->jsonResponse($estudiante);
            }
            [$pagina, $busqueda] = $this->getPagination($data);
            $estudiantes = $this->estudianteModel->obtenerTodos($pagina, $busqueda);
            $this->jsonResponse(['pagina' => $pagina, 'q' => $busqueda, 'datos' => $estudiantes]);
        }

        $this->requireRole('administrador');

        if ($method === 'POST') {
            $payload = $this->normalizarEstudiante($data);
            if ($payload['cip'] === '' || $payload['primer_nombre'] === '' || $payload['primer_apellido'] === '' || $payload['fecha_nacimiento'] === '' || $payload['id_carrera'] <= 0) {
                $this->jsonResponse(['error' => 'CIP, primer nombre, primer apellido, fecha de nacimiento y carrera son obligatorios'], 400);
            }
            if ($this->estudianteModel->existeCIP($payload['cip'])) {
                $this->jsonResponse(['error' => 'El CIP/Cédula ya está registrado'], 409);
            }

            try {
                $nuevoId = $this->estudianteModel->insertar($payload);
            } catch (Throwable $e) {
                error_log('[ApiController::estudiantes POST] ' . $e->getMessage());
                $this->jsonResponse(['error' => 'No se pudo crear el estudiante. Verifica datos duplicados o inválidos.'], 409);
            }
            $this->jsonResponse(['success' => true, 'id' => $nuevoId, 'mensaje' => 'Estudiante creado correctamente'], 201);
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }
            $actual = $this->estudianteModel->obtenerPorId($id);
            if (!$actual) {
                $this->jsonResponse(['error' => 'Estudiante no encontrado'], 404);
            }
            $payload = $this->normalizarEstudiante(array_merge($actual, $data));
            $payload['activo'] = $this->boolInt($payload, 'activo', (int)$actual['activo']);

            if ($payload['cip'] === '' || $payload['primer_nombre'] === '' || $payload['primer_apellido'] === '' || $payload['fecha_nacimiento'] === '' || $payload['id_carrera'] <= 0) {
                $this->jsonResponse(['error' => 'CIP, primer nombre, primer apellido, fecha de nacimiento y carrera son obligatorios'], 400);
            }
            if ($this->estudianteModel->existeCIP($payload['cip'], $id)) {
                $this->jsonResponse(['error' => 'El CIP/Cédula ya está registrado'], 409);
            }

            try {
                $ok = $this->estudianteModel->actualizar($id, $payload);
            } catch (Throwable $e) {
                error_log('[ApiController::estudiantes UPDATE] ' . $e->getMessage());
                $this->jsonResponse(['error' => 'No se pudo actualizar el estudiante. Verifica datos duplicados o inválidos.'], 409);
            }
            $this->jsonResponse(['success' => $ok, 'mensaje' => 'Estudiante actualizado correctamente']);
        }

        if ($method === 'DELETE') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }
            $ok = $this->estudianteModel->eliminar($id);
            $this->jsonResponse(['success' => $ok, 'mensaje' => 'Estudiante desactivado correctamente']);
        }

        $this->jsonResponse(['error' => 'Método no permitido'], 405);
    }

    private function normalizarEstudiante(array $data): array
    {
        return [
            'cip' => $this->text($data, 'cip'),
            'primer_nombre' => $this->name($data, 'primer_nombre'),
            'segundo_nombre' => $this->name($data, 'segundo_nombre'),
            'primer_apellido' => $this->name($data, 'primer_apellido'),
            'segundo_apellido' => $this->name($data, 'segundo_apellido'),
            'fecha_nacimiento' => Sanitizador::fecha((string)($data['fecha_nacimiento'] ?? '')),
            'id_carrera' => Sanitizador::entero($data['id_carrera'] ?? 0),
            'email' => $this->email($data, 'email'),
            'password' => (string)($data['password'] ?? ''),
            'activo' => $this->boolInt($data, 'activo', 1),
        ];
    }

    public function profesores(): void
    {
        $this->requireAuth();
        $data = $this->readBody();
        $id = $this->getIdFromRequest($data);
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if ($id) {
                $profesor = $this->profesorModel->obtenerPorId($id);
                if (!$profesor) {
                    $this->jsonResponse(['error' => 'Profesor no encontrado'], 404);
                }
                $this->jsonResponse($profesor);
            }
            [$pagina, $busqueda] = $this->getPagination($data);
            $this->jsonResponse(['pagina' => $pagina, 'q' => $busqueda, 'datos' => $this->profesorModel->obtenerTodos($pagina, $busqueda)]);
        }

        $this->requireRole('administrador');

        if ($method === 'POST') {
            $payload = $this->normalizarProfesor($data);
            if ($payload['cip'] === '' || $payload['primer_nombre'] === '' || $payload['primer_apellido'] === '') {
                $this->jsonResponse(['error' => 'CIP, primer nombre y primer apellido son obligatorios'], 400);
            }
            if ($this->profesorModel->existeCIP($payload['cip'])) {
                $this->jsonResponse(['error' => 'El CIP/Cédula ya está registrado'], 409);
            }
            if ($this->profesorModel->existeEmail($payload['email'])) {
                $this->jsonResponse(['error' => 'El email ya está registrado'], 409);
            }

            try {
                $nuevoId = $this->profesorModel->insertar($payload);
            } catch (Throwable $e) {
                error_log('[ApiController::profesores POST] ' . $e->getMessage());
                $this->jsonResponse(['error' => 'No se pudo crear el profesor. Verifica datos duplicados o inválidos.'], 409);
            }
            $this->jsonResponse(['success' => true, 'id' => $nuevoId, 'mensaje' => 'Profesor creado correctamente'], 201);
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }
            $actual = $this->profesorModel->obtenerPorId($id);
            if (!$actual) {
                $this->jsonResponse(['error' => 'Profesor no encontrado'], 404);
            }
            $payload = $this->normalizarProfesor(array_merge($actual, $data));
            $payload['activo'] = $this->boolInt($payload, 'activo', (int)$actual['activo']);

            if ($payload['cip'] === '' || $payload['primer_nombre'] === '' || $payload['primer_apellido'] === '') {
                $this->jsonResponse(['error' => 'CIP, primer nombre y primer apellido son obligatorios'], 400);
            }
            if ($this->profesorModel->existeCIP($payload['cip'], $id)) {
                $this->jsonResponse(['error' => 'El CIP/Cédula ya está registrado'], 409);
            }
            if ($this->profesorModel->existeEmail($payload['email'], $id)) {
                $this->jsonResponse(['error' => 'El email ya está registrado'], 409);
            }

            try {
                $ok = $this->profesorModel->actualizar($id, $payload);
            } catch (Throwable $e) {
                error_log('[ApiController::profesores UPDATE] ' . $e->getMessage());
                $this->jsonResponse(['error' => 'No se pudo actualizar el profesor. Verifica datos duplicados o inválidos.'], 409);
            }
            $this->jsonResponse(['success' => $ok, 'mensaje' => 'Profesor actualizado correctamente']);
        }

        if ($method === 'DELETE') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }
            $ok = $this->profesorModel->eliminar($id);
            $this->jsonResponse(['success' => $ok, 'mensaje' => 'Profesor desactivado correctamente']);
        }

        $this->jsonResponse(['error' => 'Método no permitido'], 405);
    }

    private function normalizarProfesor(array $data): array
    {
        return [
            'cip' => $this->text($data, 'cip'),
            'primer_nombre' => $this->name($data, 'primer_nombre'),
            'segundo_nombre' => $this->name($data, 'segundo_nombre'),
            'primer_apellido' => $this->name($data, 'primer_apellido'),
            'segundo_apellido' => $this->name($data, 'segundo_apellido'),
            'fecha_nacimiento' => Sanitizador::fecha((string)($data['fecha_nacimiento'] ?? '')),
            'email' => $this->email($data, 'email'),
            'departamento' => $this->text($data, 'departamento'),
            'especialidad' => $this->text($data, 'especialidad'),
            'password' => (string)($data['password'] ?? ''),
            'activo' => $this->boolInt($data, 'activo', 1),
        ];
    }

    public function roles(): void
    {
        $this->requireRole('administrador');
        $data = $this->readBody();
        $id = $this->getIdFromRequest($data);
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if ($id) {
                $rol = $this->rolModel->obtenerPorId($id);
                if (!$rol) {
                    $this->jsonResponse(['error' => 'Rol no encontrado'], 404);
                }
                $this->jsonResponse($rol);
            }
            [$pagina, $busqueda] = $this->getPagination($data);
            if (($_GET['activos'] ?? '') === '1') {
                $this->jsonResponse(['datos' => $this->rolModel->obtenerTodosActivos()]);
            }
            $this->jsonResponse(['pagina' => $pagina, 'q' => $busqueda, 'datos' => $this->rolModel->obtenerTodos($pagina, $busqueda)]);
        }

        if ($method === 'POST') {
            $payload = $this->normalizarRolPayload($data);
            if ($payload['nombre'] === '') {
                $this->jsonResponse(['error' => 'El nombre del rol es obligatorio'], 400);
            }
            if ($this->rolModel->existeNombre($payload['nombre'])) {
                $this->jsonResponse(['error' => 'El nombre del rol ya existe'], 409);
            }
            $nuevoId = $this->rolModel->insertar($payload);
            $this->jsonResponse(['success' => true, 'id' => $nuevoId, 'mensaje' => 'Rol creado correctamente'], 201);
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }
            $actual = $this->rolModel->obtenerPorId($id);
            if (!$actual) {
                $this->jsonResponse(['error' => 'Rol no encontrado'], 404);
            }
            $payload = $this->normalizarRolPayload(array_merge($actual, $data));
            if ($payload['nombre'] === '') {
                $this->jsonResponse(['error' => 'El nombre del rol es obligatorio'], 400);
            }
            if ($this->rolModel->existeNombre($payload['nombre'], $id)) {
                $this->jsonResponse(['error' => 'El nombre del rol ya existe'], 409);
            }
            $ok = $this->rolModel->actualizar($id, $payload);
            $this->jsonResponse(['success' => $ok, 'mensaje' => 'Rol actualizado correctamente']);
        }

        if ($method === 'DELETE') {
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }
            $ok = $this->rolModel->eliminar($id);
            if (!$ok) {
                $this->jsonResponse(['error' => 'No se pudo desactivar el rol. El rol administrador principal no puede eliminarse.'], 400);
            }
            $this->jsonResponse(['success' => true, 'mensaje' => 'Rol desactivado correctamente']);
        }

        $this->jsonResponse(['error' => 'Método no permitido'], 405);
    }

    private function normalizarRolPayload(array $data): array
    {
        $modulos = $data['modulos'] ?? '';
        if (is_array($modulos)) {
            $modulos = implode(',', array_map('trim', $modulos));
        }

        return [
            'nombre' => $this->name($data, 'nombre'),
            'descripcion' => $this->text($data, 'descripcion'),
            'modulos' => Sanitizador::texto((string)$modulos),
            'activo' => $this->boolInt($data, 'activo', 1),
        ];
    }

    public function logs(): void
    {
        $this->requireRole('administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(['error' => 'Método no permitido'], 405);
        }

        $q = Sanitizador::texto((string)($_GET['q'] ?? ''));
        $limite = Sanitizador::entero($_GET['limite'] ?? 100);
        $limite = max(1, min(300, $limite));
        $this->jsonResponse(['q' => $q, 'limite' => $limite, 'datos' => $this->authModel->obtenerLogs($limite, $q)]);
    }

    public function configuracion(): void
    {
        $this->requireRole('administrador');
        $data = $this->readBody();
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $this->jsonResponse([
                'dias_prestamo_estudiante' => $this->parametroModel->diasPrestamo('ESTUDIANTE'),
                'dias_prestamo_profesor' => $this->parametroModel->diasPrestamo('PROFESOR'),
                'api_enabled' => Env::bool('API_ENABLED', false),
                'por_pagina' => POR_PAGINA,
            ]);
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            $diasEstudiante = max(1, Sanitizador::entero($data['dias_prestamo_estudiante'] ?? $this->parametroModel->diasPrestamo('ESTUDIANTE')));
            $diasProfesor = max(1, Sanitizador::entero($data['dias_prestamo_profesor'] ?? $this->parametroModel->diasPrestamo('PROFESOR')));

            $ok = $this->parametroModel->guardar('dias_prestamo_estudiante', (string)$diasEstudiante)
                && $this->parametroModel->guardar('dias_prestamo_profesor', (string)$diasProfesor);

            if (!$ok) {
                $this->jsonResponse(['error' => 'No se pudo actualizar la configuración'], 500);
            }

            $this->jsonResponse([
                'success' => true,
                'mensaje' => 'Configuración actualizada correctamente',
                'datos' => [
                    'dias_prestamo_estudiante' => $diasEstudiante,
                    'dias_prestamo_profesor' => $diasProfesor,
                ],
            ]);
        }

        $this->jsonResponse(['error' => 'Método no permitido'], 405);
    }

    public function solicitudes(): void
    {
        $this->requireAuth();
        $data = $this->readBody();
        $id = $this->getIdFromRequest($data);
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if ($id) {
                $solicitud = $this->solicitudModel->obtenerPorId($id);
                if (!$solicitud) {
                    $this->jsonResponse(['error' => 'Solicitud no encontrada'], 404);
                }
                $this->jsonResponse($solicitud);
            }
            [$pagina, $busqueda] = $this->getPagination($data);
            $solicitudes = $this->solicitudModel->obtenerTodos($pagina, $busqueda);
            $this->jsonResponse(['pagina' => $pagina, 'q' => $busqueda, 'datos' => $solicitudes]);
        }

        if ($method === 'POST') {
            $payload = $this->normalizarSolicitudCreacion($data);
            if (($payload['id_estudiante'] ?? 0) <= 0 && ($payload['id_profesor'] ?? 0) <= 0) {
                $this->jsonResponse(['error' => 'Debe indicar id_estudiante o id_profesor'], 400);
            }
            if ($payload['titulo'] === '' || $payload['area'] === '') {
                $this->jsonResponse(['error' => 'Título y área son obligatorios'], 400);
            }
            $nuevoId = $this->solicitudModel->insertar($payload);
            if (!$nuevoId) {
                $this->jsonResponse(['error' => 'No se pudo crear la solicitud'], 500);
            }
            $this->jsonResponse(['success' => true, 'id' => $nuevoId, 'mensaje' => 'Solicitud creada correctamente'], 201);
        }

        if ($method === 'PUT' || $method === 'PATCH') {
            $this->requireRole('administrador');
            if (!$id) {
                $this->jsonResponse(['error' => 'El parámetro id es obligatorio'], 400);
            }
            $actual = $this->solicitudModel->obtenerPorId($id);
            if (!$actual) {
                $this->jsonResponse(['error' => 'Solicitud no encontrada'], 404);
            }
            $payload = [
                'estado' => Sanitizador::texto((string)($data['estado'] ?? $actual['estado'] ?? 'PENDIENTE')),
                'observaciones' => Sanitizador::texto((string)($data['observaciones'] ?? $actual['observaciones'] ?? '')),
            ];
            if (!in_array($payload['estado'], ['PENDIENTE', 'REVISADA', 'APROBADA', 'RECHAZADA'], true)) {
                $this->jsonResponse(['error' => 'Estado inválido'], 400);
            }
            $ok = $this->solicitudModel->actualizar($id, $payload);
            $this->jsonResponse(['success' => $ok, 'mensaje' => 'Solicitud actualizada correctamente']);
        }

        $this->jsonResponse(['error' => 'Método no permitido'], 405);
    }

    private function normalizarSolicitudCreacion(array $data): array
    {
        $areas = ['Matemáticas', 'Ciencias', 'Tecnologías', 'Deporte', 'Salud', 'Revistas Científicas', 'Sistemas', 'Lógica', 'Química', 'Estadística'];
        $tipos = ['COMPRA', 'INTERBIBLIOTECARIO'];
        $area = $this->text($data, 'area');
        $tipo = $this->text($data, 'tipo_solicitud', 'COMPRA');

        return [
            'id_estudiante' => Sanitizador::entero($data['id_estudiante'] ?? 0) ?: null,
            'id_profesor' => Sanitizador::entero($data['id_profesor'] ?? 0) ?: null,
            'titulo' => $this->text($data, 'titulo'),
            'autor' => $this->text($data, 'autor'),
            'area' => in_array($area, $areas, true) ? $area : 'Tecnologías',
            'materia' => $this->text($data, 'materia'),
            'motivo' => $this->text($data, 'motivo'),
            'tipo_solicitud' => in_array($tipo, $tipos, true) ? $tipo : 'COMPRA',
            'institucion_sugerida' => $this->text($data, 'institucion_sugerida'),
            'descripcion' => $this->text($data, 'descripcion'),
        ];
    }
}
