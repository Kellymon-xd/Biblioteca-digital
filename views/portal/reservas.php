<?php
$tituloPagina = 'Mis Reservas';
$activePortal = 'reservas';
require_once SRC_PATH . '/views/layout/header.php';
require_once SRC_PATH . '/views/portal/_navbar.php';
?>
<div class="container py-4">
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h1 class="h4 mb-3">Mis Reservas</h1>
      <?php if (!empty($reservas)): ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr><th>#</th><th>Libro</th><th>Estado</th><th>Fecha reserva</th><th>Fecha esperada</th><th>Días</th><th>Acción</th></tr>
            </thead>
            <tbody>
              <?php foreach ($reservas as $reserva): ?>
                <tr>
                  <td><?= (int)$reserva['id_reserva'] ?></td>
                  <td><?= Sanitizador::html($reserva['titulo'] ?? '') ?><div class="small text-muted"><?= Sanitizador::html($reserva['autor'] ?? '') ?></div></td>
                  <td><span class="badge bg-light text-dark border"><?= Sanitizador::html($reserva['estado'] ?? '') ?></span></td>
                  <td><?= !empty($reserva['fecha_reserva']) ? date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])) : '' ?></td>
                  <td><?= !empty($reserva['fecha_devolucion_esperada']) ? date('d/m/Y', strtotime($reserva['fecha_devolucion_esperada'])) : '' ?></td>
                  <td><?= (int)($reserva['dias_reservados'] ?? 0) ?></td>
                  <td>
                    <?php if (($reserva['estado'] ?? '') === 'ACTIVA'): ?>
                      <form method="POST" action="<?= BASE_URL ?>/index.php?mod=reservas&accion=devolver" onsubmit="return confirm('¿Confirmas la devolución de este libro?');">
                        <?= CsrfToken::campoOculto() ?><input type="hidden" name="id_reserva" value="<?= (int)$reserva['id_reserva'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-success">Devolver</button>
                      </form>
                    <?php else: ?><span class="text-muted small">Sin acción</span><?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?><div class="alert alert-info">No tienes reservas registradas.</div><?php endif; ?>
    </div>
  </div>
</div>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>
