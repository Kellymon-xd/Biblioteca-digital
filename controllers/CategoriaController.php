<?php
declare(strict_types=1);

class CategoriaController
{
    private CategoriaModel $modelo;
    private Validador $val;
    public function __construct() { $this->modelo = new CategoriaModel(); $this->val = new Validador(); }

    public function index(): void
    {
        $busqueda   = Sanitizador::texto($_GET['q'] ?? '');
        $pagina     = max(1, Sanitizador::entero($_GET['pag'] ?? 1));
        $categorias = $this->modelo->obtenerTodos($pagina, $busqueda);
        $total      = $this->modelo->contarTotal($busqueda);
        $paginas    = (int)ceil($total / POR_PAGINA);
        require_once SRC_PATH . '/views/categorias/index.php';
    }

    public function form(): void
    {
        $id        = Sanitizador::entero($_GET['id'] ?? 0);
        $categoria = $id ? $this->modelo->obtenerPorId($id) : [];
        require_once SRC_PATH . '/views/categorias/form.php';
    }

    public function guardar(): void
    {
        CsrfToken::verificarPost();
        $id = Sanitizador::entero($_POST['id'] ?? 0);
        $d  = Sanitizador::sanitizarPost(['nombre' => 'nombre', 'descripcion' => 'texto', 'activo' => 'int']);

        $this->val->limpiar()->requerido('nombre', $d['nombre']);
        if ($this->val->tieneErrores()) {
            ErrorHandler::agregarMensaje('danger', 'El nombre de la categoría es obligatorio.');
            Sanitizador::guardarViejosDatos($d);
            ErrorHandler::redirigir('categorias', 'form', $id ? ['id' => $id] : []);
       
        }

        $ok = $id ? $this->modelo->actualizar($id, $d) : $this->modelo->insertar($d);
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Categoría guardada.' : 'Error al guardar.');
        ErrorHandler::redirigir('categorias');
    }

    public function eliminar(): void
    {
        CsrfToken::verificarPost();
        $ok = $this->modelo->eliminar(Sanitizador::entero($_POST['id'] ?? 0));
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Categoría desactivada.' : 'Error.');
        ErrorHandler::redirigir('categorias');
    }
}