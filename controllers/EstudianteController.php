<?php
declare(strict_types=1);

class EstudianteController
{
    private EstudianteModel $modelo;
    private CarreraModel $carreras;
    private Validador $val;

    public function __construct()
    {
        $this->modelo = new EstudianteModel();
        $this->carreras = new CarreraModel();
        $this->val = new Validador();
    }

    public function index(): void
    {
        $busqueda = Sanitizador::texto($_GET['q'] ?? '');
        $pagina = max(1, Sanitizador::entero($_GET['pag'] ?? 1));
        $estudiantes = $this->modelo->obtenerTodos($pagina, $busqueda);
        $total = $this->modelo->contarTotal($busqueda);
        $paginas = (int) ceil($total / POR_PAGINA);
        require_once SRC_PATH . '/views/estudiantes/index.php';
    }

    public function form(): void
    {
        $id = Sanitizador::entero($_GET['id'] ?? 0);
        $estudiante = $id ? $this->modelo->obtenerPorId($id) : [];
        $carreras = $this->carreras->obtenerTodosActivos();
        require_once SRC_PATH . '/views/estudiantes/form.php';
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
            'id_carrera' => 'int',
            'email' => 'email',
            'password' => 'texto',
            'activo' => 'int',
        ]);

        $this->val->limpiar();
        $this->val->requerido('cip', $d['cip'])
            ->requerido('primer_nombre', $d['primer_nombre'])
            ->soloLetras('primer_nombre', $d['primer_nombre'])
            ->requerido('primer_apellido', $d['primer_apellido'])
            ->soloLetras('primer_apellido', $d['primer_apellido'])
            ->requerido('fecha_nacimiento', $d['fecha_nacimiento'])
            ->fecha('fecha_nacimiento', $d['fecha_nacimiento'])
            ->enteroPositivo('id_carrera', $d['id_carrera']);

        if (!empty($d['password'])) {
            $this->val->contrasena('password', $d['password']);
        }

        if ($this->val->tieneErrores()) {
            foreach ($this->val->errores() as $msg)
                ErrorHandler::agregarMensaje('danger', $msg);
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::redirigir('estudiantes', 'form', $id ? ['id' => $id] : []);
        }

        // RF: No duplicar cédula
        if ($this->modelo->existeCIP($d['cip'], $id)) {
            ErrorHandler::agregarMensaje('danger', 'El CIP/Cédula ya está registrado.');
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::redirigir('estudiantes', 'form', $id ? ['id' => $id] : []);
        }

        try {
            $ok = $id ? $this->modelo->actualizar($id, $d) : $this->modelo->insertar($d);
        } catch (Throwable $e) {
            error_log('[EstudianteController::guardar] ' . $e->getMessage());
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::agregarMensaje('danger', 'No se pudo guardar el estudiante. Verifica que los datos no estén duplicados.');
            ErrorHandler::redirigir('estudiantes', 'form', $id ? ['id' => $id] : []);
        }
        if (!$ok) {
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::agregarMensaje('danger', 'Error al guardar.');
            ErrorHandler::redirigir('estudiantes', 'form', $id ? ['id' => $id] : []);
        }
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Estudiante guardado.' : 'Error al guardar.');
        ErrorHandler::redirigir('estudiantes');
    }

    public function eliminar(): void
    {
        CsrfToken::verificarPost();
        $ok = $this->modelo->eliminar(Sanitizador::entero($_POST['id'] ?? 0));
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Estudiante desactivado.' : 'Error.');
        ErrorHandler::redirigir('estudiantes');
    }
}
