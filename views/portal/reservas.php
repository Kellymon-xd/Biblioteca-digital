<?php
$tituloPagina = 'Mis Reservas';
$activePortal = 'reservas';
require_once SRC_PATH . '/views/layout/header.php';
require_once SRC_PATH . '/views/portal/_navbar.php';
?>

<div class="container py-4">
  <?php require_once SRC_PATH . '/views/layout/flash.php'; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h1 class="h4 mb-3">Mis Reservas</h1>

      <?php if (!empty($reservas)): ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Libro</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Acción</th>
              </tr>
            </thead>

            <tbody>
              <?php foreach ($reservas as $reserva): ?>
                <tr>
                  <td><?= Sanitizador::html($reserva['id_reserva'] ?? '') ?></td>
                  <td><?= Sanitizador::html($reserva['titulo'] ?? '') ?></td>
                  <td><?= Sanitizador::html($reserva['estado'] ?? '') ?></td>
                  <td><?= Sanitizador::html($reserva['fecha_reserva'] ?? '') ?></td>
                  <td>
                    <?php if (($reserva['estado'] ?? '') === 'ACTIVA'): ?>
                      <form method="POST" action="<?= BASE_URL ?>/index.php?mod=reservas&accion=devolver"
                        onsubmit="return confirm('¿Confirmas la devolución de este libro?');">
                        <?= CsrfToken::campoOculto() ?>
                        <input type="hidden" name="id_reserva" value="<?= (int)$reserva['id_reserva'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-success">
                          Devolver
                        </button>
                      </form>
                    <?php else: ?>
                      <span class="text-muted small">Sin acción</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="alert alert-info">No tienes reservas activas.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>