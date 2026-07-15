<?php

declare(strict_types=1);

class DashboardController
{
    public function index(): void
    {
        $pdo = Conexion::obtenerInstancia()->getConexion();
        $totales = [];
        $tablas = ['libros' => 'libros', 'estudiantes' => 'estudiantes', 'profesores' => 'profesores', 'usuarios' => 'usuarios', 'reservas' => 'reservas'];
        foreach ($tablas as $k => $t) {
            try {
                $totales[$k] = (int)$pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
            } catch (Throwable $e) {
                $totales[$k] = 0;
            }
        }
        $disponibles = (int)$pdo->query("SELECT COALESCE(SUM(unidades_disponibles),0) FROM libros WHERE activo=1")->fetchColumn();
        $ultimasReservas = $pdo->query("SELECT r.id_reserva, l.titulo,
                    COALESCE(CONCAT(e.primer_nombre,' ',e.primer_apellido), CONCAT(p.primer_nombre,' ',p.primer_apellido), CONCAT(u.nombre,' ',u.apellido)) AS lector,
                    r.tipo_actor, r.estado, r.fecha_reserva
             FROM reservas r
             JOIN libros l ON l.id_libro=r.id_libro
             LEFT JOIN estudiantes e ON e.id_estudiante=r.id_estudiante
             LEFT JOIN profesores p ON p.id_profesor=r.id_profesor
             LEFT JOIN usuarios u ON u.id_usuario=r.id_usuario
             ORDER BY r.fecha_reserva DESC LIMIT 5")->fetchAll();
        $ultimosLogs = (new AuthModel())->obtenerLogs(8);
        $tituloPagina = 'Dashboard';
        require_once SRC_PATH . '/views/layout/header.php';
        require_once SRC_PATH . '/views/dashboard/index.php';
        require_once SRC_PATH . '/views/layout/footer.php';
    }
}
