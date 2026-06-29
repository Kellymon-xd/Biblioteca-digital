<?php
$tituloPagina = 'Categorías';
require_once SRC_PATH . '/views/layout/header.php';
?>

<div class="container py-4">
  <?php require_once SRC_PATH . '/views/layout/flash.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-0">Categorías</h1>
      <p class="text-muted mb-0">Listado de categorías registradas.</p>
    </div>

    <a href="<?= BASE_URL ?>/index.php?mod=categorias&accion=form" class="btn btn-primary">
      <i class="bi bi-plus-circle me-1"></i>Nueva categoría
    </a>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" action="<?= BASE_URL ?>/index.php" class="row g-2">
        <input type="hidden" name="mod" value="categorias">

        <div class="col-md-10">
          <input
            type="search"
            name="q"
            class="form-control"
            placeholder="Buscar por nombre"
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

  <?php if (!empty($categorias)): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th class="text-center">Activa</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <?php foreach ($categorias as $categoria): ?>
                <tr>
                  <td><?= (int)($categoria['id_categoria'] ?? 0) ?></td>

                  <td class="fw-semibold">
                    <?= Sanitizador::html($categoria['nombre'] ?? '') ?>
                  </td>

                  <td>
                    <?= Sanitizador::html($categoria['descripcion'] ?? '') ?>
                  </td>

                  <td class="text-center">
                    <?php if (!empty($categoria['activo'])): ?>
                      <span class="badge text-bg-success">Sí</span>
                    <?php else: ?>
                      <span class="badge text-bg-secondary">No</span>
                    <?php endif; ?>
                  </td>

                  <td class="text-end">
                    <div class="d-flex justify-content-end align-items-center gap-2 flex-nowrap">
                      <a
                        href="<?= BASE_URL ?>/index.php?mod=categorias&accion=form&id=<?= (int)$categoria['id_categoria'] ?>"
                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                        <i class="bi bi-pencil-square"></i>
                        <span>Editar</span>
                      </a>

                      <?php if (!empty($categoria['activo'])): ?>
                        <form
                          method="POST"
                          action="<?= BASE_URL ?>/index.php?mod=categorias&accion=eliminar"
                          class="m-0"
                          onsubmit="return confirm('¿Seguro que deseas desactivar esta categoría?');">
                          <?= CsrfToken::campoOculto() ?>
                          <input type="hidden" name="id" value="<?= (int)$categoria['id_categoria'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                            <i class="bi bi-trash"></i>
                            <span>Desactivar</span>
                          </button>
                        </form>
                      <?php else: ?>
                        <span class="text-muted small">Desactivada</span>
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
                  <a class="page-link" href="<?= BASE_URL ?>/index.php?mod=categorias&pag=<?= $i ?>&q=<?= urlencode($busqueda ?? '') ?>">
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
    <div class="alert alert-info">No hay categorías registradas.</div>
  <?php endif; ?>
</div>

<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>