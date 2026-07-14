<?php
$tituloPagina = 'Estadísticas';
$activePortal = 'estadisticas';
require_once SRC_PATH . '/views/layout/header.php';
require_once SRC_PATH . '/views/portal/_navbar.php';
?>

<div class="container py-4 portal-page">
  <?php require_once SRC_PATH . '/views/layout/flash.php'; ?>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <h1 class="h4 mb-3">Estadísticas de uso de libros</h1>
      <p class="text-muted mb-3">Consulta los libros más utilizados por período.</p>

      <form method="GET" action="<?= BASE_URL ?>/index.php" class="row g-3 align-items-end">
        <input type="hidden" name="mod" value="portal">
        <input type="hidden" name="accion" value="estadisticas">

        <div class="col-md-4">
          <label class="form-label">Desde</label>
          <input type="date" name="desde" class="form-control" value="<?= Sanitizador::html($desde ?? '') ?>" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Hasta</label>
          <input type="date" name="hasta" class="form-control" value="<?= Sanitizador::html($hasta ?? '') ?>" required>
        </div>

        <div class="col-md-4">
          <button type="submit" class="btn btn-primary w-100">
            Ver estadísticas
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h2 class="h5 mb-3">Libros más usados</h2>

      <?php if (!empty($datos)): ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Título</th>
                <th>Autor</th>
                <th>Categoría</th>
                <th class="text-center">Total de reservas</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($datos as $i => $item): ?>
                <tr>
                  <td><?= $i + 1 ?></td>
                  <td class="fw-semibold"><?= Sanitizador::html($item['titulo'] ?? '') ?></td>
                  <td><?= Sanitizador::html($item['autor'] ?? '') ?></td>
                  <td><?= Sanitizador::html($item['categoria'] ?? '') ?></td>
                  <td class="text-center">
                    <span class="badge bg-primary"><?= (int)($item['total_reservas'] ?? 0) ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="alert alert-info mb-0">
          No hay reservas registradas en el período seleccionado.
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>