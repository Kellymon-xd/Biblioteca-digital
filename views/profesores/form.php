<?php
$tituloPagina = empty($profesor) ? 'Nuevo Profesor' : 'Editar Profesor';
require_once SRC_PATH . '/views/layout/header.php';
$old = Sanitizador::obtenerViejosDatos();
$id = (int)($profesor['id_profesor'] ?? 0);
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= Sanitizador::html($tituloPagina) ?></h1>
    <a href="<?= BASE_URL ?>/index.php?mod=profesores" class="btn btn-secondary">Volver</a>
  </div>
  <div class="card border-0 shadow-sm"><div class="card-body">
    <form method="POST" action="<?= BASE_URL ?>/index.php?mod=profesores&accion=guardar" novalidate>
      <?= CsrfToken::campoOculto() ?><input type="hidden" name="id" value="<?= $id ?>">
      <div class="row g-3">
        <div class="col-md-4"><label class="form-label">CIP / Identificación</label><input name="cip" class="form-control" required value="<?= Sanitizador::html((string)($old['cip'] ?? $profesor['cip'] ?? '')) ?>"></div>
        <div class="col-md-4"><label class="form-label">Primer Nombre</label><input name="primer_nombre" class="form-control text-capitalize" required value="<?= Sanitizador::html((string)($old['primer_nombre'] ?? $profesor['primer_nombre'] ?? '')) ?>"></div>
        <div class="col-md-4"><label class="form-label">Segundo Nombre</label><input name="segundo_nombre" class="form-control text-capitalize" value="<?= Sanitizador::html((string)($old['segundo_nombre'] ?? $profesor['segundo_nombre'] ?? '')) ?>"></div>
        <div class="col-md-4"><label class="form-label">Primer Apellido</label><input name="primer_apellido" class="form-control text-capitalize" required value="<?= Sanitizador::html((string)($old['primer_apellido'] ?? $profesor['primer_apellido'] ?? '')) ?>"></div>
        <div class="col-md-4"><label class="form-label">Segundo Apellido</label><input name="segundo_apellido" class="form-control text-capitalize" value="<?= Sanitizador::html((string)($old['segundo_apellido'] ?? $profesor['segundo_apellido'] ?? '')) ?>"></div>
        <div class="col-md-4"><label class="form-label">Fecha de nacimiento</label><input type="date" name="fecha_nacimiento" class="form-control" value="<?= Sanitizador::html((string)($old['fecha_nacimiento'] ?? $profesor['fecha_nacimiento'] ?? '')) ?>"></div>
        <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= Sanitizador::html((string)($old['email'] ?? $profesor['email'] ?? '')) ?>"></div>
        <div class="col-md-4"><label class="form-label">Departamento</label><input name="departamento" class="form-control" value="<?= Sanitizador::html((string)($old['departamento'] ?? $profesor['departamento'] ?? '')) ?>"></div>
        <div class="col-md-4"><label class="form-label">Especialidad</label><input name="especialidad" class="form-control" value="<?= Sanitizador::html((string)($old['especialidad'] ?? $profesor['especialidad'] ?? '')) ?>"></div>
        <div class="col-md-6"><label class="form-label">Contraseña portal docente</label><input type="password" name="password" class="form-control" placeholder="<?= $id ? 'Dejar vacío para mantener' : '8–12 caracteres' ?>" <?= $id ? '' : 'required' ?> minlength="8" maxlength="12"></div>
        <?php if ($id): ?><div class="col-md-6 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="activo" value="1" id="activo" <?= (int)($old['activo'] ?? $profesor['activo'] ?? 1) === 1 ? 'checked' : '' ?>><label class="form-check-label" for="activo">Profesor activo</label></div></div><?php endif; ?>
      </div>
      <div class="mt-4"><button class="btn btn-primary">Guardar</button><a href="<?= BASE_URL ?>/index.php?mod=profesores" class="btn btn-secondary">Cancelar</a></div>
    </form>
  </div></div>
</div>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>
