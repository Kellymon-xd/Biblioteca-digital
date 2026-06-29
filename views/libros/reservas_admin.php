<?php
$tituloPagina = 'Reservas Administrativas';
require_once SRC_PATH . '/views/layout/header.php';
?>
<div class="container py-4">
  <?php require_once SRC_PATH . '/views/layout/flash.php'; ?>
  <h1 class="h3 mb-4">Reservas</h1>
  <?php if (!empty($reservas)): ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Libro</th>
            <th>Estudiante</th>
            <th>Estado</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reservas as $reserva): ?>
            <tr>
              <td><?= Sanitizador::html($reserva['id_reserva'] ?? '') ?></td>
              <td><?= Sanitizador::html($reserva['titulo'] ?? '') ?></td>
              <td><?= Sanitizador::html($reserva['estudiante'] ?? '') ?></td>
              <td><?= Sanitizador::html($reserva['estado'] ?? '') ?></td>
              <td><?= Sanitizador::html($reserva['fecha_reserva'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="alert alert-info">No hay reservas registradas.</div>
  <?php endif; ?>
</div>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>