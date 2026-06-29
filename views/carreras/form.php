<?php
$tituloPagina = empty($carrera) ? 'Nueva Carrera' : 'Editar Carrera';
require_once SRC_PATH . '/views/layout/header.php';
?>
<div class="container py-4">
  <?php require_once SRC_PATH . '/views/layout/flash.php'; ?>
  <?php $old = Sanitizador::obtenerViejosDatos(); ?>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= $tituloPagina ?></h1>
    <a href="<?= BASE_URL ?>/index.php?mod=carreras" class="btn btn-secondary">Volver</a>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form method="POST" action="<?= BASE_URL ?>/index.php?mod=carreras&accion=guardar" novalidate>
        <?= CsrfToken::campoOculto() ?>

        <input type="hidden" name="id" value="<?= Sanitizador::html((string)($carrera['id_carrera'] ?? $old['id'] ?? '')) ?>">

        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input
            type="text"
            name="nombre"
            class="form-control text-capitalize"
            value="<?= Sanitizador::html((string)($old['nombre'] ?? $carrera['nombre'] ?? '')) ?>"
            required
          >
        </div>

        <div class="mb-3">
          <label class="form-label">Código</label>
          <input
            type="text"
            name="codigo"
            class="form-control"
            value="<?= Sanitizador::html((string)($old['codigo'] ?? $carrera['codigo'] ?? '')) ?>"
            required
          >
        </div>

        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control" rows="3"><?= Sanitizador::html((string)($old['descripcion'] ?? $carrera['descripcion'] ?? '')) ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Activo</label>
          <select name="activo" class="form-select">
            <?php $activo = (string)($old['activo'] ?? $carrera['activo'] ?? '1'); ?>
            <option value="1" <?= $activo === '1' ? 'selected' : '' ?>>Sí</option>
            <option value="0" <?= $activo === '0' ? 'selected' : '' ?>>No</option>
          </select>
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="<?= BASE_URL ?>/index.php?mod=carreras" class="btn btn-secondary">Cancelar</a>
      </form>
    </div>
  </div>
</div>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>