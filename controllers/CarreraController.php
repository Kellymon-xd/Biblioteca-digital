<?php
declare(strict_types=1);

class CarreraController
{
    private CarreraModel $modelo;
    private Validador    $val;

    public function __construct() { $this->modelo = new CarreraModel(); $this->val = new Validador(); }

    public function index(): void
    {
        $busqueda = Sanitizador::texto($_GET['q'] ?? '');
        $pagina   = max(1, Sanitizador::entero($_GET['pag'] ?? 1));
        $carreras = $this->modelo->obtenerTodos($pagina, $busqueda);
        $total    = $this->modelo->contarTotal($busqueda);
        $paginas  = (int)ceil($total / POR_PAGINA);
        require_once SRC_PATH . '/views/carreras/index.php';
    }

    public function form(): void
    {
        $id     = Sanitizador::entero($_GET['id'] ?? 0);
        $carrera = $id ? $this->modelo->obtenerPorId($id) : [];
        require_once SRC_PATH . '/views/carreras/form.php';
    }

    public function guardar(): void
    {
        CsrfToken::verificarPost();
        $id = Sanitizador::entero($_POST['id'] ?? 0);
        $d  = Sanitizador::sanitizarPost(['nombre' => 'nombre', 'codigo' => 'texto', 'descripcion' => 'texto', 'activo' => 'int']);

        $this->val->limpiar();
        $this->val->requerido('nombre', $d['nombre'])->requerido('codigo', $d['codigo']);

        if ($this->val->tieneErrores()) {
            foreach ($this->val->errores() as $msg) ErrorHandler::agregarMensaje('danger', $msg);
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::redirigir('carreras', 'form', $id ? ['id' => $id] : []);
        }

        try {
            $ok = $id ? $this->modelo->actualizar($id, $d) : $this->modelo->insertar($d);
        } catch (Throwable $e) {
            error_log('[CarreraController::guardar] ' . $e->getMessage());
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::agregarMensaje('danger', 'No se pudo guardar la carrera. Verifica que los datos no estén duplicados.');
            ErrorHandler::redirigir('carreras', 'form', $id ? ['id' => $id] : []);
        }
        if (!$ok) {
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::agregarMensaje('danger', 'Error al guardar.');
            ErrorHandler::redirigir('carreras', 'form', $id ? ['id' => $id] : []);
        }
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Carrera guardada.' : 'Error al guardar.');
        ErrorHandler::redirigir('carreras');
    }

    public function eliminar(): void
    {
        CsrfToken::verificarPost();
        $ok = $this->modelo->eliminar(Sanitizador::entero($_POST['id'] ?? 0));
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Carrera desactivada.' : 'Error.');
        ErrorHandler::redirigir('carreras');
    }
}
