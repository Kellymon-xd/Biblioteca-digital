<?php
$tituloPagina = 'Profesores';
require_once SRC_PATH . '/views/layout/header.php';
?>

<div class="container py-4">
  <?php require_once SRC_PATH . '/views/layout/flash.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-0">Profesores</h1>
      <p class="text-muted mb-0">Docentes autorizados para usar la biblioteca digital.</p>
    </div>

    <a href="<?= BASE_URL ?>/index.php?mod=profesores&accion=form" class="btn btn-primary">
      <i class="bi bi-plus-circle me-1"></i>Nuevo profesor
    </a>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" action="<?= BASE_URL ?>/index.php" class="row g-2">
        <input type="hidden" name="mod" value="profesores">

        <div class="col-md-10">
          <input
            type="search"
            name="q"
            class="form-control"
            placeholder="Buscar por CIP, nombre, correo o departamento"
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

  <?php if (!empty($profesores)): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 admin-table admin-table-professors">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>CIP</th>
                <th>Departamento</th>
                <th class="text-center">Estado</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <?php foreach ($profesores as $profesor): ?>
                <?php
                  $nombreCompleto = trim(
                    ($profesor['primer_nombre'] ?? '') . ' ' .
                    ($profesor['segundo_nombre'] ?? '') . ' ' .
                    ($profesor['primer_apellido'] ?? '') . ' ' .
                    ($profesor['segundo_apellido'] ?? '')
                  );
                ?>

                <tr>
                  <td><?= (int)($profesor['id_profesor'] ?? 0) ?></td>

                  <td>
                    <div class="fw-semibold">
                      <?= Sanitizador::html($nombreCompleto) ?>
                    </div>

                    <?php if (!empty($profesor['email'])): ?>
                      <small class="text-muted d-block">
                        <?= Sanitizador::html($profesor['email']) ?>
                      </small>
                    <?php endif; ?>
                  </td>

                  <td>
                    <span class="badge bg-light text-dark border">
                      <?= Sanitizador::html($profesor['cip'] ?? '') ?>
                    </span>
                  </td>

                  <td>
                    <?= Sanitizador::html($profesor['departamento'] ?? '') ?>
                  </td>

                  <td class="text-center professor-status-cell">
                    <?php if (!empty($profesor['activo'])): ?>
                      <span class="badge text-bg-success">Activo</span>
                    <?php else: ?>
                      <span class="badge text-bg-secondary">Inactivo</span>
                    <?php endif; ?>

                    <?php if (!empty($profesor['bloqueado'])): ?>
                      <span class="badge text-bg-danger ms-1">Bloqueado</span>
                    <?php endif; ?>
                  </td>

                  <td class="text-end table-actions-cell">
                    <div class="table-actions">
                      <a
                        href="<?= BASE_URL ?>/index.php?mod=profesores&accion=form&id=<?= (int)$profesor['id_profesor'] ?>"
                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                        <i class="bi bi-pencil-square"></i>
                        <span>Editar</span>
                      </a>

                      <?php if (!empty($profesor['bloqueado'])): ?>
                        <form
                          method="POST"
                          action="<?= BASE_URL ?>/index.php?mod=profesores&accion=desbloquear"
                          class="m-0"
                          onsubmit="return confirm('¿Desbloquear profesor?');">
                          <?= CsrfToken::campoOculto() ?>
                          <input type="hidden" name="id" value="<?= (int)$profesor['id_profesor'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1">
                            <i class="bi bi-unlock"></i>
                            <span>Desbloquear</span>
                          </button>
                        </form>
                      <?php endif; ?>

                      <?php if (!empty($profesor['activo'])): ?>
                        <form
                          method="POST"
                          action="<?= BASE_URL ?>/index.php?mod=profesores&accion=eliminar"
                          class="m-0"
                          onsubmit="return confirm('¿Seguro que deseas desactivar este profesor?');">
                          <?= CsrfToken::campoOculto() ?>
                          <input type="hidden" name="id" value="<?= (int)$profesor['id_profesor'] ?>">
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
                  <a class="page-link" href="<?= BASE_URL ?>/index.php?mod=profesores&pag=<?= $i ?>&q=<?= urlencode($busqueda ?? '') ?>">
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
    <div class="alert alert-info">No hay profesores registrados.</div>
  <?php endif; ?>
</div>

<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>
