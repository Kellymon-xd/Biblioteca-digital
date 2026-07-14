<?php
$tituloPagina = 'Reporte de Reservas';
require_once SRC_PATH . '/views/layout/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-0">Reporte de reservas</h1>
      <p class="text-muted mb-0">Filtra por fecha y exporta a Excel/CSV.</p>
    </div>
    <a class="btn btn-success" href="<?= BASE_URL ?>/index.php?mod=reservas&accion=exportar&desde=<?= urlencode($desde ?? '') ?>&hasta=<?= urlencode($hasta ?? '') ?>&q=<?= urlencode($busqueda ?? '') ?>"><i class="bi bi-file-earmark-excel me-1"></i>Exportar Excel</a>
  </div>
  <div class="card border-0 shadow-sm mb-4"><div class="card-body">
    <form method="GET" action="<?= BASE_URL ?>/index.php" class="row g-2">
      <input type="hidden" name="mod" value="reservas"><input type="hidden" name="accion" value="admin">
      <div class="col-md-3"><label class="form-label small">Desde</label><input type="date" name="desde" class="form-control" value="<?= Sanitizador::html($desde ?? '') ?>"></div>
      <div class="col-md-3"><label class="form-label small">Hasta</label><input type="date" name="hasta" class="form-control" value="<?= Sanitizador::html($hasta ?? '') ?>"></div>
      <div class="col-md-4"><label class="form-label small">Buscar</label><input type="search" name="q" class="form-control" placeholder="Libro, autor, CIP, docente, estudiante" value="<?= Sanitizador::html($busqueda ?? '') ?>"></div>
      <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filtrar</button></div>
    </form>
  </div></div>
  <?php if (!empty($reservas)): ?>
    <div class="card border-0 shadow-sm"><div class="card-body table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>#</th><th>Libro</th><th>Reservado por</th><th>Tipo</th><th>Estado</th><th>Fecha</th><th>Esperada</th><th>Días</th></tr></thead>
        <tbody>
          <?php foreach ($reservas as $reserva): ?>
            <tr>
              <td><?= (int)$reserva['id_reserva'] ?></td>
              <td><?= Sanitizador::html($reserva['titulo'] ?? '') ?><div class="small text-muted"><?= Sanitizador::html($reserva['autor'] ?? '') ?></div></td>
              <td><?= Sanitizador::html($reserva['lector'] ?? '') ?><div class="small text-muted"><?= Sanitizador::html($reserva['identificacion'] ?? '') ?></div></td>
              <td><span class="badge bg-light text-dark border"><?= Sanitizador::html($reserva['tipo_lector'] ?? '') ?></span></td>
              <td><?= Sanitizador::html($reserva['estado'] ?? '') ?></td>
              <td><?= !empty($reserva['fecha_reserva']) ? date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])) : '' ?></td>
              <td><?= !empty($reserva['fecha_devolucion_esperada']) ? date('d/m/Y', strtotime($reserva['fecha_devolucion_esperada'])) : '' ?></td>
              <td><?= (int)($reserva['dias_reservados'] ?? 0) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div></div>
  <?php else: ?><div class="alert alert-info">No hay reservas con esos filtros.</div><?php endif; ?>
</div>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>
