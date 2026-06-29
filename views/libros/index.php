<?php
$tituloPagina = 'Libros';
require_once SRC_PATH . '/views/layout/header.php';
?>

<div class="container py-4">
  <?php require_once SRC_PATH . '/views/layout/flash.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-0">Libros</h1>
      <p class="text-muted mb-0">Listado de libros registrados en la biblioteca.</p>
    </div>

    <div class="d-flex gap-2">
      <a href="<?= BASE_URL ?>/index.php?mod=libros&accion=exportar&q=<?= urlencode($busqueda ?? '') ?>"
         class="btn btn-outline-success">
        <i class="bi bi-file-earmark-excel me-1"></i>Exportar
      </a>

      <a href="<?= BASE_URL ?>/index.php?mod=libros&accion=form"
         class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nuevo libro
      </a>
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" action="<?= BASE_URL ?>/index.php" class="row g-2">
        <input type="hidden" name="mod" value="libros">

        <div class="col-md-10">
          <input
            type="search"
            name="q"
            class="form-control"
            placeholder="Buscar por título, autor, ISBN o categoría"
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

  <?php if (!empty($libros)): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="align-middle">#</th>
                <th class="align-middle text-center">Portada</th>
                <th class="align-middle">Título</th>
                <th class="align-middle">Autor</th>
                <th class="align-middle text-center">Categoría</th>
                <th class="align-middle text-center">Total</th>
                <th class="align-middle text-center">Disponibles</th>
                <th class="align-middle text-center">Activo</th>
                <th class="align-middle text-end">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <?php foreach ($libros as $libro): ?>
                <tr>
                  <td class="align-middle"><?= (int)($libro['id_libro'] ?? 0) ?></td>

                  <td class="align-middle text-center">
                    <?php if (!empty($libro['imagen_thumb'])): ?>
                      <img
                        src="<?= BASE_URL . '/' . Sanitizador::html($libro['imagen_thumb']) ?>"
                        alt="<?= Sanitizador::html($libro['titulo'] ?? 'Portada') ?>"
                        class="img-fluid rounded border"
                        style="width:55px;height:75px;object-fit:cover;"
                      >
                    <?php else: ?>
                      <div class="bg-light border rounded d-inline-flex align-items-center justify-content-center"
                           style="width:55px;height:75px;">
                        <i class="bi bi-image text-muted"></i>
                      </div>
                    <?php endif; ?>
                  </td>

                  <td class="align-middle">
                    <div class="fw-semibold lh-sm">
                      <?= html_entity_decode(Sanitizador::html($libro['titulo'] ?? '')) ?>
                    </div>
                    <small class="text-muted d-block">
                      ISBN: <?= Sanitizador::html($libro['isbn'] ?? 'N/A') ?>
                    </small>
                  </td>

                  <td class="align-middle">
                    <?= Sanitizador::html($libro['autor'] ?? '') ?>
                  </td>

                  <td class="align-middle text-center">
                    <span class="badge bg-light text-dark border">
                      <?= Sanitizador::html($libro['categoria'] ?? '') ?>
                    </span>
                  </td>

                  <td class="align-middle text-center">
                    <?= (int)($libro['unidades_totales'] ?? 0) ?>
                  </td>

                  <td class="align-middle text-center">
                    <span class="<?= (int)($libro['unidades_disponibles'] ?? 0) > 0 ? 'text-success' : 'text-danger' ?> fw-semibold">
                      <?= (int)($libro['unidades_disponibles'] ?? 0) ?>
                    </span>
                  </td>

                  <td class="align-middle text-center">
                    <?php if (!empty($libro['activo'])): ?>
                      <span class="badge text-bg-success">Sí</span>
                    <?php else: ?>
                      <span class="badge text-bg-secondary">No</span>
                    <?php endif; ?>
                  </td>

                  <td class="align-middle text-end">
                    <div class="d-flex justify-content-end align-items-center gap-2 flex-nowrap">
                      <a
                        href="<?= BASE_URL ?>/index.php?mod=libros&accion=form&id=<?= (int)$libro['id_libro'] ?>"
                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                        <i class="bi bi-pencil-square"></i>
                        <span>Editar</span>
                      </a>

                      <?php if (!empty($libro['activo'])): ?>
                        <form
                          method="POST"
                          action="<?= BASE_URL ?>/index.php?mod=libros&accion=eliminar"
                          class="m-0"
                          onsubmit="return confirm('¿Seguro que deseas desactivar este libro?');">
                          <?= CsrfToken::campoOculto() ?>
                          <input type="hidden" name="id" value="<?= (int)$libro['id_libro'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                            <i class="bi bi-trash"></i>
                            <span>Desactivar</span>
                          </button>
                        </form>
                      <?php else: ?>
                        <span class="text-muted small">Desactivado</span>
                      <?php endif; ?>
                    </div>
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
                  <a class="page-link" href="<?= BASE_URL ?>/index.php?mod=libros&pag=<?= $i ?>&q=<?= urlencode($busqueda ?? '') ?>">
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
    <div class="alert alert-info">No hay libros registrados aún.</div>
  <?php endif; ?>
</div>

<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>