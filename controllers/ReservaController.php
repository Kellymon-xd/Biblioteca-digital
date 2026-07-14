<?php

declare(strict_types=1);

class ReservaController
{
    private ReservaModel $modelo;
    private SolicitudModel $solicitudes;
    private ParametroModel $parametros;

    public function __construct()
    {
        $this->modelo = new ReservaModel();
        $this->solicitudes = new SolicitudModel();
        $this->parametros = new ParametroModel();
    }

    public function index(): void
    {
        if (empty($_SESSION['lector']['id'])) {
            ErrorHandler::redirigir('portal', 'login');
        }
        $tipo = (string)$_SESSION['lector']['tipo'];
        $id = (int)$_SESSION['lector']['id'];
        $reservas = $this->modelo->obtenerPorActor($tipo, $id);
        $misSolicitudes = $tipo === 'ESTUDIANTE' ? $this->solicitudes->obtenerPorEstudiante($id) : [];
        require_once SRC_PATH . '/views/portal/reservas.php';
    }

    public function reservar(): void
    {
        if (empty($_SESSION['lector']['id'])) {
            ErrorHandler::redirigir('portal', 'login');
        }

        CsrfToken::verificarPost();

        $idLibro = Sanitizador::entero($_POST['id_libro'] ?? 0);
        $tipoActor = (string)$_SESSION['lector']['tipo'];
        $idActor = (int)$_SESSION['lector']['id'];
        $dias = $this->parametros->diasPrestamo($tipoActor);
        $fechaDev = date('Y-m-d', strtotime('+' . $dias . ' days'));

        $nuevo = $this->modelo->insertar([
            'tipo_actor' => $tipoActor,
            'id_actor' => $idActor,
            'id_libro' => $idLibro,
            'fecha_devolucion_esperada' => $fechaDev,
        ]);

        $msg = $nuevo
            ? "¡Reserva realizada! El préstamo es por {$dias} días, hasta el " . date('d/m/Y', strtotime($fechaDev)) . '.'
            : 'No se pudo realizar la reserva. Verifica que el libro tenga unidades disponibles.';

        ErrorHandler::agregarMensaje($nuevo ? 'success' : 'warning', $msg);
        ErrorHandler::redirigir('portal', 'index');
    }

    public function devolver(): void
    {
        if (empty($_SESSION['lector']['id'])) {
            ErrorHandler::redirigir('portal', 'login');
        }
        CsrfToken::verificarPost();
        $id = Sanitizador::entero($_POST['id_reserva'] ?? 0);
        $ok = $this->modelo->devolver($id);
        ErrorHandler::agregarMensaje($ok ? 'success' : 'danger', $ok ? 'Libro devuelto. ¡Gracias!' : 'Error al registrar devolución.');
        ErrorHandler::redirigir('reservas');
    }

    // Admin: reporte con filtros y exportación
    public function admin(): void
    {
        exigirPermiso('reservas');
        $busqueda = Sanitizador::texto($_GET['q'] ?? '');
        $desde = Sanitizador::fecha($_GET['desde'] ?? date('Y-m-01'));
        $hasta = Sanitizador::fecha($_GET['hasta'] ?? date('Y-m-d'));
        $pagina = max(1, Sanitizador::entero($_GET['pag'] ?? 1));
        $reservas = $this->modelo->obtenerReporte($desde, $hasta, $busqueda, $pagina, POR_PAGINA);
        $total = $this->modelo->contarReporte($desde, $hasta, $busqueda);
        $paginas = (int)ceil($total / POR_PAGINA);
        require_once SRC_PATH . '/views/libros/reservas_admin.php';
    }

    public function exportar(): void
    {
        exigirPermiso('reservas');
        $busqueda = Sanitizador::texto($_GET['q'] ?? '');
        $desde = Sanitizador::fecha($_GET['desde'] ?? '');
        $hasta = Sanitizador::fecha($_GET['hasta'] ?? '');
        $reservas = $this->modelo->obtenerReporte($desde, $hasta, $busqueda, 1, 0);

        $filename = 'reporte_reservas_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['ID', 'Libro', 'Autor', 'Reservado por', 'Tipo', 'Identificación', 'Estado', 'Fecha reserva', 'Fecha esperada', 'Fecha devolución', 'Días reservados']);
        foreach ($reservas as $r) {
            fputcsv($out, [
                $r['id_reserva'], $r['titulo'], $r['autor'], $r['lector'], $r['tipo_lector'], $r['identificacion'],
                $r['estado'], $r['fecha_reserva'], $r['fecha_devolucion_esperada'], $r['fecha_devolucion_real'], $r['dias_reservados'],
            ]);
        }
        fclose($out);
        exit;
    }
}
