<?php
$tituloPagina = empty($estudiante) ? 'Nuevo Estudiante' : 'Editar Estudiante';
require_once SRC_PATH . '/views/layout/header.php';
?>
<div class="container py-4">
  <?php require_once SRC_PATH . '/views/layout/flash.php'; ?>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= $tituloPagina ?></h1>
    <a href="<?= BASE_URL ?>/index.php?mod=estudiantes" class="btn btn-secondary">Volver</a>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <?php $old = Sanitizador::obtenerViejosDatos(); ?>

      <form method="POST" action="<?= BASE_URL ?>/index.php?mod=estudiantes&accion=guardar" novalidate>
        <?= CsrfToken::campoOculto() ?>
        <input type="hidden" name="id" value="<?= Sanitizador::html((string)($estudiante['id_estudiante'] ?? $old['id'] ?? '')) ?>">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">CIP</label>
            <input type="text" name="cip" class="form-control"
                   value="<?= Sanitizador::html($old['cip'] ?? $estudiante['cip'] ?? '') ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= Sanitizador::html($old['email'] ?? $estudiante['email'] ?? '') ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Primer nombre</label>
            <input type="text" name="primer_nombre" class="form-control text-capitalize"
                   value="<?= Sanitizador::html($old['primer_nombre'] ?? $estudiante['primer_nombre'] ?? '') ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Segundo nombre</label>
            <input type="text" name="segundo_nombre" class="form-control text-capitalize"
                   value="<?= Sanitizador::html($old['segundo_nombre'] ?? $estudiante['segundo_nombre'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Primer apellido</label>
            <input type="text" name="primer_apellido" class="form-control text-capitalize"
                   value="<?= Sanitizador::html($old['primer_apellido'] ?? $estudiante['primer_apellido'] ?? '') ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Segundo apellido</label>
            <input type="text" name="segundo_apellido" class="form-control text-capitalize"
                   value="<?= Sanitizador::html($old['segundo_apellido'] ?? $estudiante['segundo_apellido'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Carrera</label>
            <select name="id_carrera" class="form-select" required>
              <option value="">Seleccione</option>
              <?php foreach ($carreras as $carrera): ?>
                <?php
                  $selectedCarrera =
                    (isset($old['id_carrera']) && $old['id_carrera'] == $carrera['id_carrera']) ||
                    (!isset($old['id_carrera']) && isset($estudiante['id_carrera']) && $estudiante['id_carrera'] == $carrera['id_carrera']);
                ?>
                <option value="<?= Sanitizador::html((string)$carrera['id_carrera']) ?>" <?= $selectedCarrera ? 'selected' : '' ?>>
                  <?= Sanitizador::html($carrera['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Fecha de nacimiento</label>
            <input type="date" name="fecha_nacimiento" class="form-control"
                   value="<?= Sanitizador::html($old['fecha_nacimiento'] ?? $estudiante['fecha_nacimiento'] ?? '') ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control"
                   placeholder="<?= !empty($estudiante) ? 'Dejar vacía para mantener la actual' : '8–12 caracteres' ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Activo</label>
            <select name="activo" class="form-select">
              <?php $activo = (string)($old['activo'] ?? $estudiante['activo'] ?? '1'); ?>
              <option value="1" <?= $activo === '1' ? 'selected' : '' ?>>Sí</option>
              <option value="0" <?= $activo === '0' ? 'selected' : '' ?>>No</option>
            </select>
          </div>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <a href="<?= BASE_URL ?>/index.php?mod=estudiantes" class="btn btn-secondary">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>