<?php
$tituloPagina = empty($usuario) ? 'Nuevo Usuario' : 'Editar Usuario';
require_once SRC_PATH . '/views/layout/header.php';
$id = $usuario['id_usuario'] ?? 0;
$old = Sanitizador::obtenerViejosDatos();
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= Sanitizador::html($tituloPagina) ?></h1>
    <a href="<?= BASE_URL ?>/index.php?mod=usuarios" class="btn btn-secondary">Volver</a>
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form method="POST" action="<?= BASE_URL ?>/index.php?mod=usuarios&accion=guardar" novalidate>
        <?= CsrfToken::campoOculto() ?>
        <input type="hidden" name="id" value="<?= Sanitizador::html((string)($id ?: ($old['id'] ?? ''))) ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control text-capitalize" required value="<?= Sanitizador::html((string)($old['nombre'] ?? $usuario['nombre'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Apellido</label>
            <input type="text" name="apellido" class="form-control text-capitalize" required value="<?= Sanitizador::html((string)($old['apellido'] ?? $usuario['apellido'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?= Sanitizador::html((string)($old['email'] ?? $usuario['email'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Usuario</label>
            <input type="text" name="username" class="form-control" required value="<?= Sanitizador::html((string)($old['username'] ?? $usuario['username'] ?? '')) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" placeholder="<?= $id ? 'Dejar vacío para mantener' : '8–12 caracteres' ?>" <?= $id ? '' : 'required' ?> minlength="8" maxlength="12">
          </div>
          <div class="col-md-6">
            <label class="form-label">Rol / alcance</label>
            <select name="id_rol" class="form-select" required>
              <option value="">Seleccione...</option>
              <?php foreach ($roles as $rol): ?>
                <option value="<?= (int)$rol['id_rol'] ?>" <?= (int)($old['id_rol'] ?? $usuario['id_rol'] ?? 0) === (int)$rol['id_rol'] ? 'selected' : '' ?>>
                  <?= Sanitizador::html($rol['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">El rol define a qué módulos puede entrar este usuario.</div>
          </div>
          <?php if ($id): ?>
            <div class="col-12">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="activo" value="1" id="chkActivo" <?= (int)($old['activo'] ?? $usuario['activo'] ?? 1) === 1 ? 'checked' : '' ?> >
                <label class="form-check-label" for="chkActivo">Usuario activo</label>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <a href="<?= BASE_URL ?>/index.php?mod=usuarios" class="btn btn-secondary">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>
