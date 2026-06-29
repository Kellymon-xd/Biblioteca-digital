<?php
declare(strict_types=1);

class DashboardController
{
    public function index(): void
    {
        $pdo     = Conexion::obtenerInstancia()->getConexion();
        $totales = [];
        $tablas  = ['libros'=>'libros','estudiantes'=>'estudiantes','usuarios'=>'usuarios','reservas'=>'reservas'];
        foreach ($tablas as $k => $t) {
            $totales[$k] = (int)$pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        }
        $disponibles    = (int)$pdo->query("SELECT SUM(unidades_disponibles) FROM libros WHERE activo=1")->fetchColumn();
        $ultimasReservas= $pdo->query("SELECT r.id_reserva, l.titulo, CONCAT(e.primer_nombre,' ',e.primer_apellido) AS estudiante, r.estado, r.fecha_reserva FROM reservas r JOIN libros l ON l.id_libro=r.id_libro JOIN estudiantes e ON e.id_estudiante=r.id_estudiante ORDER BY r.fecha_reserva DESC LIMIT 5")->fetchAll();
        $ultimosLogs    = (new AuthModel())->obtenerLogs(8);
        $tituloPagina   = 'Dashboard';
        require_once SRC_PATH . '/views/layout/header.php';
        require_once SRC_PATH . '/views/dashboard/index.php';
        require_once SRC_PATH . '/views/layout/footer.php';
    }
}
