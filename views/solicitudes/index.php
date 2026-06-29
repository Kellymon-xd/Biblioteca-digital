<?php
$tituloPagina = 'Solicitudes';
require_once SRC_PATH . '/views/layout/header.php';
?>

<div class="container py-4">
  <?php require_once SRC_PATH . '/views/layout/flash.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-0">Solicitudes</h1>
      <p class="text-muted mb-0">Solicitudes de libros enviadas por estudiantes.</p>
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" action="<?= BASE_URL ?>/index.php" class="row g-2">
        <input type="hidden" name="mod" value="solicitudes">

        <div class="col-md-10">
          <input
            type="search"
            name="q"
            class="form-control"
            placeholder="Buscar por título, autor o estudiante"
            value="<?= Sanitizador::html($busqueda ?? '') ?>"
          >
        </div>

        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-search me-1"></i>Buscar
          </button>
        </div>
      </form>
    </div>
  </div>

  <?php if (!empty($solicitudes)): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Título</th>
                <th>Autor</th>
                <th>Área</th>
                <th>Estudiante</th>
                <th class="text-center">Estado</th>
                <th>Observaciones</th>
                <th class="text-end">Responder</th>
              </tr>
            </thead>

            <tbody>
              <?php foreach ($solicitudes as $solicitud): ?>
                <tr>
                  <td><?= (int)($solicitud['id_solicitud'] ?? 0) ?></td>

                  <td class="fw-semibold">
                    <?= Sanitizador::html($solicitud['titulo'] ?? '') ?>
                  </td>

                  <td>
                    <?= Sanitizador::html($solicitud['autor'] ?? '') ?>
                  </td>

                  <td>
                    <?= Sanitizador::html($solicitud['area'] ?? '') ?>
                  </td>

                  <td>
                    <?= Sanitizador::html($solicitud['estudiante'] ?? '') ?>
                    <div class="small text-muted">
                      <?= Sanitizador::html($solicitud['cip'] ?? '') ?>
                    </div>
                  </td>

                  <td class="text-center">
                    <span class="badge bg-light text-dark border">
                      <?= Sanitizador::html($solicitud['estado'] ?? '') ?>
                    </span>
                  </td>

                  <td>
                    <?= Sanitizador::html($solicitud['observaciones'] ?? '') ?>
                  </td>

                  <td class="text-end">
                    <form method="POST"
                          action="<?= BASE_URL ?>/index.php?mod=solicitudes&accion=responder"
                          class="d-grid gap-2"
                          style="min-width: 240px;">
                      <?= CsrfToken::campoOculto() ?>
                      <input type="hidden" name="id" value="<?= (int)($solicitud['id_solicitud'] ?? 0) ?>">

                      <?php $estadoActual = $solicitud['estado'] ?? 'PENDIENTE'; ?>
                      <select name="estado" class="form-select form-select-sm">
                        <option value="PENDIENTE" <?= $estadoActual === 'PENDIENTE' ? 'selected' : '' ?>>PENDIENTE</option>
                        <option value="REVISADA" <?= $estadoActual === 'REVISADA' ? 'selected' : '' ?>>REVISADA</option>
                        <option value="APROBADA" <?= $estadoActual === 'APROBADA' ? 'selected' : '' ?>>APROBADA</option>
                        <option value="RECHAZADA" <?= $estadoActual === 'RECHAZADA' ? 'selected' : '' ?>>RECHAZADA</option>
                      </select>

                      <textarea
                        name="observaciones"
                        class="form-control form-control-sm"
                        rows="2"
                        placeholder="Observaciones"><?= Sanitizador::html($solicitud['observaciones'] ?? '') ?></textarea>

                      <button type="submit" class="btn btn-sm btn-outline-primary">
                        Guardar respuesta
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <?php if (($paginas ?? 1) > 1): ?>
          <nav class="mt-3">
            <ul class="pagination justify-content-center mb-0">
              <?php for ($i = 1; $i <= $paginas; $i++): ?>
                <li class="page-item <?= $i === ($pagina ?? 1) ? 'active' : '' ?>">
                  <a class="page-link" href="<?= BASE_URL ?>/index.php?mod=solicitudes&pag=<?= $i ?>&q=<?= urlencode($busqueda ?? '') ?>">
                    <?= $i ?>
                  </a>
                </li>
              <?php endfor; ?>
            </ul>
          </nav>
        <?php endif; ?>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-info">No hay solicitudes.</div>
  <?php endif; ?>
</div>

<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>