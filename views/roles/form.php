<?php
$tituloPagina = empty($rol) ? 'Nuevo Rol' : 'Editar Rol';
require_once SRC_PATH . '/views/layout/header.php';
$old = Sanitizador::obtenerViejosDatos();
$id = (int)($rol['id_rol'] ?? 0);
$seleccionados = $old['modulos'] ?? array_filter(explode(',', (string)($rol['modulos'] ?? '')));
if (($rol['modulos'] ?? '') === '*') {
    $seleccionados = ['*'];
}
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= Sanitizador::html($tituloPagina) ?></h1>
    <a href="<?= BASE_URL ?>/index.php?mod=roles" class="btn btn-secondary">Volver</a>
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form method="POST" action="<?= BASE_URL ?>/index.php?mod=roles&accion=guardar">
        <?= CsrfToken::campoOculto() ?>
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nombre del rol</label>
            <input type="text" name="nombre" class="form-control" required value="<?= Sanitizador::html((string)($old['nombre'] ?? $rol['nombre'] ?? '')) ?>">
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" <?= (int)($old['activo'] ?? $rol['activo'] ?? 1) === 1 ? 'checked' : '' ?>>
              <label class="form-check-label" for="activo">Rol activo</label>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="2"><?= Sanitizador::html((string)($old['descripcion'] ?? $rol['descripcion'] ?? '')) ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Permisos por módulo</label>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="modulos[]" value="*" id="mod_total" <?= in_array('*', $seleccionados, true) ? 'checked' : '' ?>>
              <label class="form-check-label fw-semibold" for="mod_total">Control total</label>
            </div>
            <div class="row g-2">
              <?php foreach ($modulos as $clave => $nombre): ?>
                <div class="col-md-4">
                  <div class="form-check border rounded p-2 ps-4 bg-light">
                    <input class="form-check-input" type="checkbox" name="modulos[]" value="<?= Sanitizador::html($clave) ?>" id="mod_<?= Sanitizador::html($clave) ?>" <?= in_array($clave, $seleccionados, true) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="mod_<?= Sanitizador::html($clave) ?>"><?= Sanitizador::html($nombre) ?></label>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="mt-4">
          <button class="btn btn-primary">Guardar</button>
          <a href="<?= BASE_URL ?>/index.php?mod=roles" class="btn btn-secondary">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>
