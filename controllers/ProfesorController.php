<?php

declare(strict_types=1);

class ProfesorController
{
    private ProfesorModel $modelo;
    private Validador $val;

    public function __construct()
    {
        $this->modelo = new ProfesorModel();
        $this->val = new Validador();
    }

    public function index(): void
    {
        $busqueda = Sanitizador::texto($_GET['q'] ?? '');
        $pagina = max(1, Sanitizador::entero($_GET['pag'] ?? 1));
        $profesores = $this->modelo->obtenerTodos($pagina, $busqueda);
        $total = $this->modelo->contarTotal($busqueda);
        $paginas = (int)ceil($total / POR_PAGINA);
        require_once SRC_PATH . '/views/profesores/index.php';
    }

    public function form(): void
    {
        $id = Sanitizador::entero($_GET['id'] ?? 0);
        $profesor = $id ? $this->modelo->obtenerPorId($id) : [];
        require_once SRC_PATH . '/views/profesores/form.php';
    }

    public function guardar(): void
    {
        CsrfToken::verificarPost();
        $id = Sanitizador::entero($_POST['id'] ?? 0);
        $d = Sanitizador::sanitizarPost([
            'cip' => 'texto',
            'primer_nombre' => 'nombre',
            'segundo_nombre' => 'nombre',
            'primer_apellido' => 'nombre',
            'segundo_apellido' => 'nombre',
            'fecha_nacimiento' => 'fecha',
            'email' => 'email',
            'departamento' => 'texto',
            'especialidad' => 'texto',
            'password' => 'texto',
            'activo' => 'int',
        ]);
        $d['activo'] = isset($_POST['activo']) || !$id ? 1 : 0;

        $this->val->limpiar();
        $this->val->requerido('cip', $d['cip'])
            ->requerido('primer_nombre', $d['primer_nombre'])
            ->soloLetras('primer_nombre', $d['primer_nombre'])
            ->requerido('primer_apellido', $d['primer_apellido'])
            ->soloLetras('primer_apellido', $d['primer_apellido']);

        if (!empty($d['email'])) {
            $this->val->email('email', $d['email']);
        }
        if (!$id) {
            $this->val->requerido('password', $d['password']);
        }
        if (!empty($d['password'])) {
            $this->val->contrasena('password', $d['password']);
        }

        if ($this->val->tieneErrores()) {
            foreach ($this->val->errores() as $msg) {
                ErrorHandler::agregarMensaje('danger', $msg);
            }
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::redirigir('profesores', 'form', $id ? ['id' => $id] : []);
        }

        if ($this->modelo->existeCIP($d['cip'], $id)) {
            ErrorHandler::agregarMensaje('danger', 'El CIP/Cédula ya está registrado para otro profesor.');
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::redirigir('profesores', 'form', $id ? ['id' => $id] : []);
        }
        if ($this->modelo->existeEmail($d['email'], $id)) {
            ErrorHandler::agregarMensaje('danger', 'El correo ya está registrado para otro profesor.');
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::redirigir('profesores', 'form', $id ? ['id' => $id] : []);
        }

        try {
            $ok = $id ? $this->modelo->actualizar($id, $d) : $this->modelo->insertar($d);
        } catch (Throwable $e) {
            error_log('[ProfesorController::guardar] ' . $e->getMessage());
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::agregarMensaje('danger', 'No se pudo guardar el profesor. Verifica duplicados.');
            ErrorHandler::redirigir('profesores', 'form', $id ? ['id' => $id] : []);
        }

        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Profesor guardado.' : 'Error al guardar.');
        ErrorHandler::redirigir('profesores');
    }

    public function eliminar(): void
    {
        CsrfToken::verificarPost();
        $id = Sanitizador::entero($_POST['id'] ?? 0);
        $ok = $this->modelo->eliminar($id);
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Profesor desactivado.' : 'Error.');
        ErrorHandler::redirigir('profesores');
    }

    public function desbloquear(): void
    {
        CsrfToken::verificarPost();
        $id = Sanitizador::entero($_POST['id'] ?? 0);
        $ok = $this->modelo->desbloquear($id);
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Profesor desbloqueado.' : 'Error.');
        ErrorHandler::redirigir('profesores');
    }
}
