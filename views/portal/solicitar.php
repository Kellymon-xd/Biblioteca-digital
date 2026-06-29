<?php
$tituloPagina = 'Solicitar Libro';
$activePortal = 'solicitar';
require_once SRC_PATH . '/views/layout/header.php';
require_once SRC_PATH . '/views/portal/_navbar.php';
?>

<div class="container py-4">
  <?php require_once SRC_PATH . '/views/layout/flash.php'; ?>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <h1 class="h4 mb-3">Solicitar libro</h1>
      <p class="text-muted small">
        Si el libro no existe en el catálogo o no está disponible, puedes enviarlo para revisión.
      </p>

      <form method="POST" action="<?= BASE_URL ?>/index.php?mod=solicitudes&accion=crear">
        <?= CsrfToken::campoOculto() ?>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Título</label>
            <input type="text" name="titulo" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Autor</label>
            <input type="text" name="autor" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Área</label>
            <select name="area" class="form-select" required>
              <option value="">Seleccione</option>
              <option value="Matemáticas">Matemáticas</option>
              <option value="Ciencias">Ciencias</option>
              <option value="Tecnologías">Tecnologías</option>
              <option value="Deporte">Deporte</option>
              <option value="Salud">Salud</option>
              <option value="Revistas Científicas">Revistas Científicas</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"></textarea>
          </div>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Enviar solicitud</button>
          <a href="<?= BASE_URL ?>/index.php?mod=portal" class="btn btn-secondary">Cancelar</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h2 class="h5 mb-3">Mis solicitudes</h2>

      <?php if (!empty($misSolicitudes)): ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Título</th>
                <th>Área</th>
                <th>Estado</th>
                <th>Observaciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($misSolicitudes as $solicitud): ?>
                <tr>
                  <td><?= (int)($solicitud['id_solicitud'] ?? 0) ?></td>
                  <td><?= Sanitizador::html($solicitud['titulo'] ?? '') ?></td>
                  <td><?= Sanitizador::html($solicitud['area'] ?? '') ?></td>
                  <td><?= Sanitizador::html($solicitud['estado'] ?? '') ?></td>
                  <td><?= Sanitizador::html($solicitud['observaciones'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="alert alert-info mb-0">Aún no has enviado solicitudes.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>