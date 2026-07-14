<?php
$tituloPagina = 'Usuarios';
require_once SRC_PATH . '/views/layout/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-0">Usuarios</h1>
      <p class="text-muted mb-0">Usuarios administrativos con roles y permisos por módulo.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?mod=usuarios&accion=form" class="btn btn-primary">
      <i class="bi bi-plus-circle me-1"></i>Nuevo usuario
    </a>
  </div>
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <form method="GET" action="<?= BASE_URL ?>/index.php" class="row g-2">
        <input type="hidden" name="mod" value="usuarios">
        <div class="col-md-10">
          <input type="search" name="q" class="form-control" placeholder="Buscar por nombre, correo, usuario o rol" value="<?= Sanitizador::html($busqueda ?? '') ?>">
        </div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Buscar</button></div>
      </form>
    </div>
  </div>
  <?php if (!empty($usuarios)): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 admin-table admin-table-users">
            <thead class="table-light">
              <tr>
                <th>#</th><th>Nombre</th><th>Email</th><th>Usuario</th><th>Rol</th><th class="text-center">Activo</th><th class="text-center">Bloqueado</th><th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($usuarios as $usuario): ?>
                <tr>
                  <td><?= (int)($usuario['id_usuario'] ?? 0) ?></td>
                  <td class="fw-semibold"><?= Sanitizador::html(trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''))) ?></td>
                  <td><?= Sanitizador::html($usuario['email'] ?? '') ?></td>
                  <td><span class="badge badge-nowrap bg-light text-dark border"><?= Sanitizador::html($usuario['username'] ?? '') ?></span></td>
                  <td><span class="badge badge-nowrap bg-light text-dark border"><?= Sanitizador::html($usuario['rol_nombre'] ?? $usuario['rol'] ?? '') ?></span></td>
                  <td class="text-center"><?= !empty($usuario['activo']) ? '<span class="badge text-bg-success">Sí</span>' : '<span class="badge text-bg-secondary">No</span>' ?></td>
                  <td class="text-center"><?= !empty($usuario['bloqueado']) ? '<span class="badge text-bg-danger">Sí</span>' : '<span class="badge text-bg-secondary">No</span>' ?></td>
                  <td class="text-end table-actions-cell">
                    <div class="table-actions">
                      <a href="<?= BASE_URL ?>/index.php?mod=usuarios&accion=form&id=<?= (int)$usuario['id_usuario'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i> Editar</a>
                      <?php if (!empty($usuario['bloqueado'])): ?>
                        <form method="POST" action="<?= BASE_URL ?>/index.php?mod=usuarios&accion=desbloquear" class="m-0" onsubmit="return confirm('¿Desbloquear este usuario?');">
                          <?= CsrfToken::campoOculto() ?><input type="hidden" name="id" value="<?= (int)$usuario['id_usuario'] ?>">
                          <button class="btn btn-sm btn-outline-warning"><i class="bi bi-unlock"></i> Desbloquear</button>
                        </form>
                      <?php endif; ?>
                      <?php if (!empty($usuario['activo'])): ?>
                        <form method="POST" action="<?= BASE_URL ?>/index.php?mod=usuarios&accion=eliminar" class="m-0" onsubmit="return confirm('¿Desactivar este usuario?');">
                          <?= CsrfToken::campoOculto() ?><input type="hidden" name="id" value="<?= (int)$usuario['id_usuario'] ?>">
                          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Desactivar</button>
                        </form>
                      <?php else: ?><span class="text-muted small">Desactivado</span><?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php else: ?><div class="alert alert-info">No hay usuarios registrados.</div><?php endif; ?>
</div>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>
