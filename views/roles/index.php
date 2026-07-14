<?php
$tituloPagina = 'Roles y Permisos';
require_once SRC_PATH . '/views/layout/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-0">Roles y permisos</h1>
      <p class="text-muted mb-0">Controla el alcance de cada usuario por módulo.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?mod=roles&accion=form" class="btn btn-primary">
      <i class="bi bi-plus-circle me-1"></i>Nuevo rol
    </a>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" action="<?= BASE_URL ?>/index.php" class="row g-2">
        <input type="hidden" name="mod" value="roles">
        <div class="col-md-10">
          <input type="search" name="q" class="form-control" placeholder="Buscar rol" value="<?= Sanitizador::html($busqueda ?? '') ?>">
        </div>
        <div class="col-md-2">
          <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Buscar</button>
        </div>
      </form>
    </div>
  </div>

  <?php if (!empty($roles)): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body table-responsive">
        <table class="table table-hover align-middle mb-0 admin-table admin-table-roles">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Rol</th>
              <th>Permisos</th>
              <th class="text-center">Activo</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($roles as $rol): ?>
              <tr>
                <td><?= (int)$rol['id_rol'] ?></td>
                <td>
                  <strong><?= Sanitizador::html($rol['nombre']) ?></strong>
                  <div class="small text-muted"><?= Sanitizador::html($rol['descripcion'] ?? '') ?></div>
                </td>
                <td>
                  <?php if (($rol['modulos'] ?? '') === '*'): ?>
                    <span class="badge badge-nowrap text-bg-danger">Control total</span>
                  <?php else: ?>
                    <?php foreach (array_filter(explode(',', (string)($rol['modulos'] ?? ''))) as $modulo): ?>
                      <span class="badge bg-light text-dark border me-1 mb-1"><?= Sanitizador::html(modulosPermisos()[$modulo] ?? $modulo) ?></span>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </td>
                <td class="text-center"><?= !empty($rol['activo']) ? '<span class="badge text-bg-success">Sí</span>' : '<span class="badge text-bg-secondary">No</span>' ?></td>
                <td class="text-end table-actions-cell">
                  <div class="table-actions">
                    <a href="<?= BASE_URL ?>/index.php?mod=roles&accion=form&id=<?= (int)$rol['id_rol'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i> Editar</a>
                    <?php if ((int)$rol['id_rol'] !== 1 && !empty($rol['activo'])): ?>
                      <form method="POST" action="<?= BASE_URL ?>/index.php?mod=roles&accion=eliminar" class="m-0" onsubmit="return confirm('¿Desactivar este rol?');">
                        <?= CsrfToken::campoOculto() ?>
                        <input type="hidden" name="id" value="<?= (int)$rol['id_rol'] ?>">
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Desactivar</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-info">No hay roles registrados.</div>
  <?php endif; ?>
</div>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>
