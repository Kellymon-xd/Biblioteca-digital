<?php
declare(strict_types=1);

class LibroController
{
    private LibroModel $modelo;
    private CategoriaModel $categorias;
    private Validador $val;

    public function __construct()
    {
        $this->modelo = new LibroModel();
        $this->categorias = new CategoriaModel();
        $this->val = new Validador();
    }

    public function index(): void
    {
        $busqueda = Sanitizador::texto($_GET['q'] ?? '');
        $pagina = max(1, Sanitizador::entero($_GET['pag'] ?? 1));
        $libros = $this->modelo->obtenerTodos($pagina, $busqueda);
        $total = $this->modelo->contarTotal($busqueda);
        $paginas = (int) ceil($total / POR_PAGINA);
        require_once SRC_PATH . '/views/libros/index.php';
    }

    public function form(): void
    {
        $id = Sanitizador::entero($_GET['id'] ?? 0);
        $libro = $id ? $this->modelo->obtenerPorId($id) : [];
        $categorias = $this->categorias->obtenerTodosActivos();
        require_once SRC_PATH . '/views/libros/form.php';
    }

    public function guardar(): void
    {
        CsrfToken::verificarPost();
        $id = Sanitizador::entero($_POST['id'] ?? 0);
        $d = Sanitizador::sanitizarPost([
            'isbn' => 'texto',
            'titulo' => 'nombre',
            'autor' => 'nombre',
            'editorial' => 'nombre',
            'anio_publicacion' => 'int',
            'id_categoria' => 'int',
            'descripcion' => 'texto',
            'unidades_totales' => 'int',
            'activo' => 'int',
        ]);

        $this->val->limpiar();
        $this->val->requerido('titulo', $d['titulo'])
            ->requerido('autor', $d['autor'])
            ->enteroPositivo('id_categoria', $d['id_categoria'])
            ->enteroPositivo('unidades_totales', $d['unidades_totales']);

        // Imagen (opcional en edición, obligatoria al crear)
        $imagenData = [];
        if (!empty($_FILES['imagen']['name'])) {
            $this->val->imagen('imagen', $_FILES['imagen']);
            if (!$this->val->tieneErrores()) {
                try {
                    $imagenData = $this->procesarImagen($_FILES['imagen'], $d['titulo']);
                } catch (RuntimeException $e) {
                    ErrorHandler::agregarMensaje('danger', $e->getMessage());
                    Sanitizador::guardarViejosDatos(['id' => $id] + $d);
                    ErrorHandler::redirigir('libros', 'form', $id ? ['id' => $id] : []);
                }
            }
        } elseif (!$id) {
            // Al crear, la imagen no es obligatoria, solo sugerida
        }

        if ($this->val->tieneErrores()) {
            foreach ($this->val->errores() as $msg)
                ErrorHandler::agregarMensaje('danger', $msg);
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::redirigir('libros', 'form', $id ? ['id' => $id] : []);
        }

        $d = array_merge($d, $imagenData);
        try {
            $ok = $id ? $this->modelo->actualizar($id, $d) : $this->modelo->insertar($d);
        } catch (Throwable $e) {
            error_log('[LibroController::guardar] ' . $e->getMessage());
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::agregarMensaje('danger', 'No se pudo guardar el libro. Verifica que los datos no estén duplicados.');
            ErrorHandler::redirigir('libros', 'form', $id ? ['id' => $id] : []);
        }
        if (!$ok) {
            Sanitizador::guardarViejosDatos(['id' => $id] + $d);
            ErrorHandler::agregarMensaje('danger', 'Error al guardar.');
            ErrorHandler::redirigir('libros', 'form', $id ? ['id' => $id] : []);
        }
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Libro guardado correctamente.' : 'Error al guardar.');
        ErrorHandler::redirigir('libros');
    }

    private function procesarImagen(array $archivo, string $titulo): array
    {
        if (!isset($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
            throw new RuntimeException('Archivo de imagen inválido.');
        }

        $info = getimagesize($archivo['tmp_name']);

        if (!$info) {
            throw new RuntimeException('El archivo subido no es una imagen válida.');
        }

        $tipoImagen = $info[2];

        $ext = match ($tipoImagen) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
            default => throw new RuntimeException('Formato de imagen no permitido. Use JPG, PNG o WEBP.'),
        };

        $slug = $this->crearSlug($titulo);
        $fecha = date('Ymd_His');
        $random = bin2hex(random_bytes(4));

        $nombre = 'libro_' . $slug . '_' . $fecha . '_' . $random . '.' . $ext;

        $carpetaOrig = UPLOADS_PATH . '/libros/orig/';
        $carpetaThumb = UPLOADS_PATH . '/libros/thumb/';

        if (!is_dir($carpetaOrig)) {
            mkdir($carpetaOrig, 0777, true);
        }

        if (!is_dir($carpetaThumb)) {
            mkdir($carpetaThumb, 0777, true);
        }

        $rutaOrig = $carpetaOrig . $nombre;
        $rutaThumb = $carpetaThumb . $nombre;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaOrig)) {
            throw new RuntimeException('No se pudo guardar la imagen del libro.');
        }

        $this->generarThumbnail($rutaOrig, $rutaThumb, $ext);

        return [
            'imagen_original' => 'uploads/libros/orig/' . $nombre,
            'imagen_thumb' => 'uploads/libros/thumb/' . $nombre,
        ];
    }

    private function crearSlug(string $texto): string
    {
        $texto = trim($texto);
        $texto = mb_strtolower($texto, 'UTF-8');

        $buscar = ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u', 'n', 'u'];
        $texto = str_replace($buscar, $reemplazar, $texto);

        $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
        $texto = trim($texto, '-');

        if ($texto === '') {
            return 'sin-titulo';
        }

        return mb_substr($texto, 0, 60);
    }

    private function generarThumbnail(string $origen, string $destino, string $ext): void
    {
        if (!extension_loaded('gd')) {
            throw new RuntimeException('La extensión GD no está habilitada en PHP. Actívala en php.ini.');
        }

        $src = match ($ext) {
            'jpg', 'jpeg' => function_exists('imagecreatefromjpeg') ? imagecreatefromjpeg($origen) : null,
            'png' => function_exists('imagecreatefrompng') ? imagecreatefrompng($origen) : null,
            'webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($origen) : null,
            default => null,
        };

        if (!$src) {
            throw new RuntimeException('PHP no pudo procesar la imagen. Verifica que GD esté habilitada y soporte este formato.');
        }

        $size = getimagesize($origen);
        if (!$size) {
            imagedestroy($src);
            throw new RuntimeException('No se pudo obtener el tamaño de la imagen.');
        }

        [$w, $h] = $size;

        $ratio = min(THUMB_WIDTH / $w, THUMB_HEIGHT / $h);
        $nw = (int) ($w * $ratio);
        $nh = (int) ($h * $ratio);

        $thumb = imagecreatetruecolor(THUMB_WIDTH, THUMB_HEIGHT);
        $blanco = imagecolorallocate($thumb, 255, 255, 255);
        imagefill($thumb, 0, 0, $blanco);

        $ox = (int) ((THUMB_WIDTH - $nw) / 2);
        $oy = (int) ((THUMB_HEIGHT - $nh) / 2);

        imagecopyresampled($thumb, $src, $ox, $oy, 0, 0, $nw, $nh, $w, $h);

        $guardado = match ($ext) {
            'jpg', 'jpeg' => function_exists('imagejpeg') ? imagejpeg($thumb, $destino, 85) : false,
            'png' => function_exists('imagepng') ? imagepng($thumb, $destino) : false,
            'webp' => function_exists('imagewebp') ? imagewebp($thumb, $destino, 85) : false,
            default => false,
        };

        imagedestroy($src);
        imagedestroy($thumb);

        if (!$guardado) {
            throw new RuntimeException('No se pudo generar el thumbnail.');
        }
    }

    public function eliminar(): void
    {
        CsrfToken::verificarPost();
        $ok = $this->modelo->eliminar(Sanitizador::entero($_POST['id'] ?? 0));
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Libro desactivado.' : 'Error.');
        ErrorHandler::redirigir('libros');
    }

    /** Genera y descarga reporte CSV compatible con Excel */
    public function exportar(): void
    {
        $busqueda = Sanitizador::texto($_GET['q'] ?? '');
        $libros = $this->modelo->obtenerTodosParaExcel($busqueda);

        $filename = 'reporte_libros_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM para Excel
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, ['ISBN', 'Título', 'Autor', 'Editorial', 'Año', 'Categoría', 'Total', 'Disponibles', 'Activo']);
        foreach ($libros as $l) {
            fputcsv($out, [
                $l['isbn'],
                $l['titulo'],
                $l['autor'],
                $l['editorial'],
                $l['anio_publicacion'],
                $l['categoria'],
                $l['unidades_totales'],
                $l['unidades_disponibles'],
                $l['activo'] ? 'Sí' : 'No',
            ]);
        }
        fclose($out);
        exit;
    }

}
