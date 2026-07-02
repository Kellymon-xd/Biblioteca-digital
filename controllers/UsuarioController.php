<?php
declare(strict_types=1);

class UsuarioController
{
    private UsuarioModel $modelo;
    private Validador $val;

    public function __construct()
    {
        $this->modelo = new UsuarioModel();
        $this->val = new Validador();
    }

    public function index(): void
    {
        $busqueda = Sanitizador::texto($_GET['q'] ?? '');
        $pagina = max(1, Sanitizador::entero($_GET['pag'] ?? 1));
        $usuarios = $this->modelo->obtenerTodos($pagina, $busqueda);
        $total = $this->modelo->contarTotal($busqueda);
        $paginas = (int) ceil($total / POR_PAGINA);
        require_once SRC_PATH . '/views/usuarios/index.php';
    }

    public function form(): void
    {
        $id = Sanitizador::entero($_GET['id'] ?? 0);
        $usuario = $id ? $this->modelo->obtenerPorId($id) : [];
        require_once SRC_PATH . '/views/usuarios/form.php';
    }

    public function guardar(): void
    {
        CsrfToken::verificarPost();
        $id = Sanitizador::entero($_POST['id'] ?? 0);
        $d = Sanitizador::sanitizarPost([
            'nombre' => 'nombre',
            'apellido' => 'nombre',
            'email' => 'email',
            'username' => 'texto',
            'rol' => 'texto',
            'activo' => 'int',
            'password' => 'texto',
        ]);

        // Roles permitidos en la aplicación
        $rolesPermitidos = ['administrador', 'operador'];

        // Validar que el rol enviado sea uno permitido
        if (!in_array($d['rol'], $rolesPermitidos, true)) {
            ErrorHandler::agregarMensaje('danger', 'Rol inválido.');
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::redirigir('usuarios', 'form', $id ? ['id' => $id] : []);
        }

        // Solo administradores pueden crear nuevos usuarios
        if (!$id && (!isset($_SESSION['usuario']['rol']) || $_SESSION['usuario']['rol'] !== 'administrador')) {
            ErrorHandler::agregarMensaje('danger', 'No tienes permiso para crear usuarios.');
            ErrorHandler::redirigir('usuarios');
        }

        $this->val->limpiar();
        $this->val->requerido('nombre', $d['nombre'])
            ->requerido('apellido', $d['apellido'])
            ->requerido('email', $d['email'])
            ->email('email', $d['email'])
            ->requerido('username', $d['username']);

        if (!$id) {
            $this->val->requerido('password', $d['password']);

            if (!empty($d['password'])) {
                $this->val->contrasena('password', $d['password']);
            }
        } elseif (!empty($d['password'])) {
            $this->val->contrasena('password', $d['password']);
        }

        if ($this->val->tieneErrores()) {
            foreach ($this->val->errores() as $msg) {
                ErrorHandler::agregarMensaje('danger', $msg);
            }
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::redirigir('usuarios', 'form', $id ? ['id' => $id] : []);
        }

        if ($this->modelo->existeEmail($d['email'], $id)) {
            ErrorHandler::agregarMensaje('danger', 'El correo ya está registrado.');
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::redirigir('usuarios', 'form', $id ? ['id' => $id] : []);
        }
        if ($this->modelo->existeUsername($d['username'], $id)) {
            ErrorHandler::agregarMensaje('danger', 'El nombre de usuario ya existe.');
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::redirigir('usuarios', 'form', $id ? ['id' => $id] : []);
        }

        if ($id) {
            try {
                $ok = $this->modelo->actualizar($id, $d);
                $msg = $ok ? 'Usuario actualizado correctamente.' : 'Error al actualizar.';
            } catch (Throwable $e) {
                error_log('[UsuarioController::guardar] ' . $e->getMessage());
                Sanitizador::guardarViejosDatos(['id' => $id] + $d);
                ErrorHandler::agregarMensaje('danger', 'No se pudo actualizar el usuario. Verifica que los datos no estén duplicados.');
                ErrorHandler::redirigir('usuarios', 'form', ['id' => $id]);
            }
        } else {
            try {
                $ok = $this->modelo->insertar($d);
                $msg = $ok ? 'Usuario creado correctamente.' : 'Error al crear.';
            } catch (Throwable $e) {
                error_log('[UsuarioController::guardar] ' . $e->getMessage());
                Sanitizador::guardarViejosDatos(['id' => $id] + $d);
                ErrorHandler::agregarMensaje('danger', 'No se pudo crear el usuario. Verifica que los datos no estén duplicados.');
                ErrorHandler::redirigir('usuarios', 'form');
            }
        }
        if (!$ok) {
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::agregarMensaje('danger', $msg);
            ErrorHandler::redirigir('usuarios', 'form', $id ? ['id' => $id] : []);
        }
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $msg);
        ErrorHandler::redirigir('usuarios');
    }

    public function eliminar(): void
    {
        CsrfToken::verificarPost();
        $id = Sanitizador::entero($_POST['id'] ?? 0);
        if ((int) $_SESSION['usuario']['id_usuario'] === $id) {
            ErrorHandler::agregarMensaje('danger', 'No puedes desactivar tu propia cuenta.');
        } else {
            $ok = $this->modelo->eliminar($id);
            ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Usuario desactivado.' : 'Error.');
        }
        ErrorHandler::redirigir('usuarios');
    }

    public function desbloquear(): void
    {
        CsrfToken::verificarPost();
        $id = Sanitizador::entero($_POST['id'] ?? 0);
        $ok = $this->modelo->desbloquear($id);
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Cuenta desbloqueada.' : 'Error.');
        ErrorHandler::redirigir('usuarios');
    }
}
