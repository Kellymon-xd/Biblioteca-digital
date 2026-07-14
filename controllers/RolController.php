<?php

declare(strict_types=1);

class RolController
{
    private RolModel $modelo;
    private Validador $val;

    public function __construct()
    {
        $this->modelo = new RolModel();
        $this->val = new Validador();
    }

    public function index(): void
    {
        $busqueda = Sanitizador::texto($_GET['q'] ?? '');
        $pagina = max(1, Sanitizador::entero($_GET['pag'] ?? 1));
        $roles = $this->modelo->obtenerTodos($pagina, $busqueda);
        $total = $this->modelo->contarTotal($busqueda);
        $paginas = (int)ceil($total / POR_PAGINA);
        require_once SRC_PATH . '/views/roles/index.php';
    }

    public function form(): void
    {
        $id = Sanitizador::entero($_GET['id'] ?? 0);
        $rol = $id ? $this->modelo->obtenerPorId($id) : [];
        $modulos = modulosPermisos();
        require_once SRC_PATH . '/views/roles/form.php';
    }

    public function guardar(): void
    {
        CsrfToken::verificarPost();
        $id = Sanitizador::entero($_POST['id'] ?? 0);
        $nombre = Sanitizador::texto($_POST['nombre'] ?? '');
        $descripcion = Sanitizador::texto($_POST['descripcion'] ?? '');
        $activo = isset($_POST['activo']) ? 1 : 0;
        $seleccionados = $_POST['modulos'] ?? [];

        if (!is_array($seleccionados)) {
            $seleccionados = [];
        }
        $seleccionados = array_values(array_intersect(array_map('strval', $seleccionados), array_keys(modulosPermisos())));
        $modulos = in_array('*', $_POST['modulos'] ?? [], true) ? '*' : implode(',', $seleccionados);

        $this->val->limpiar()->requerido('nombre', $nombre);
        if ($this->val->tieneErrores()) {
            foreach ($this->val->errores() as $msg) {
                ErrorHandler::agregarMensaje('danger', $msg);
            }
            Sanitizador::guardarViejosDatos(['id' => $id, 'nombre' => $nombre, 'descripcion' => $descripcion, 'modulos' => $seleccionados, 'activo' => $activo]);
            ErrorHandler::redirigir('roles', 'form', $id ? ['id' => $id] : []);
        }

        if ($this->modelo->existeNombre($nombre, $id)) {
            ErrorHandler::agregarMensaje('danger', 'Ya existe un rol con ese nombre.');
            Sanitizador::guardarViejosDatos(['id' => $id, 'nombre' => $nombre, 'descripcion' => $descripcion, 'modulos' => $seleccionados, 'activo' => $activo]);
            ErrorHandler::redirigir('roles', 'form', $id ? ['id' => $id] : []);
        }

        $d = compact('nombre', 'descripcion', 'modulos', 'activo');
        $ok = $id ? $this->modelo->actualizar($id, $d) : $this->modelo->insertar($d);
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Rol guardado correctamente.' : 'No se pudo guardar el rol.');
        ErrorHandler::redirigir('roles');
    }

    public function eliminar(): void
    {
        CsrfToken::verificarPost();
        $id = Sanitizador::entero($_POST['id'] ?? 0);
        $ok = $this->modelo->eliminar($id);
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Rol desactivado.' : 'No se pudo desactivar el rol administrador.');
        ErrorHandler::redirigir('roles');
    }
}
