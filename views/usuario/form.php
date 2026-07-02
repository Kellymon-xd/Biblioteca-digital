<?php
$tituloPagina = empty($usuario) ? 'Nuevo Usuario' : 'Editar Usuario';
require_once SRC_PATH.'/views/layout/header.php';
$id = $usuario['id_usuario'] ?? 0;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i><?= $tituloPagina ?></h5>
  <a href="<?=BASE_URL?>?mod=usuarios" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Volver</a>
</div>
<div class="card border-0 shadow-sm" style="max-width:600px">
  <div class="card-body p-4">
    <form method="POST" action="<?=BASE_URL?>?mod=usuarios&accion=guardar" novalidate>
      <?=CsrfToken::campoOculto()?>
      <input type="hidden" name="id" value="<?=$id?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
          <input type="text" name="nombre" class="form-control" required
                 value="<?=Sanitizador::html($usuario['nombre']??'')?>">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Apellido <span class="text-danger">*</span></label>
          <input type="text" name="apellido" class="form-control" required
                 value="<?=Sanitizador::html($usuario['apellido']??'')?>">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control" required
                 value="<?=Sanitizador::html($usuario['email']??'')?>">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Usuario <span class="text-danger">*</span></label>
          <input type="text" name="username" class="form-control" required
                 value="<?=Sanitizador::html($usuario['username']??'')?>">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">
            Contraseña <?= $id ? '' : '<span class="text-danger">*</span>' ?>
          </label>
          <input type="password" name="password" class="form-control"
                 placeholder="<?=$id?'Dejar vacío para mantener':'8–12 caracteres'?>"
                 <?=$id?'':'required'?> minlength="8" maxlength="12">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Rol</label>
          <select name="rol" class="form-select">
            <?php foreach (['administrador','operador'] as $r): ?>
            <option value="<?=$r?>" <?=($usuario['rol']??'operador')===$r?'selected':''?>><?=ucfirst($r)?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php if ($id): ?>
        <div class="col-12">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="activo" value="1" id="chkActivo"
                   <?=($usuario['activo']??1)?'checked':'?>>
            <label class="form-check-label" for="chkActivo">Usuario activo</label>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-floppy me-1"></i>Guardar</button>
        <a href="<?=BASE_URL?>?mod=usuarios" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php require_once SRC_PATH.'/views/layout/footer.php'; ?>
