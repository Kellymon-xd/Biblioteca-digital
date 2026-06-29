<?php
$tituloPagina = empty($categoria) ? 'Nueva Categoría' : 'Editar Categoría';
require_once SRC_PATH . '/views/layout/header.php';
?>
<div class="container py-4">
  <?php require_once SRC_PATH . '/views/layout/flash.php'; ?>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= $tituloPagina ?></h1>
    <a href="<?= BASE_URL ?>/index.php?mod=categorias" class="btn btn-secondary">Volver</a>
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form method="POST" action="<?= BASE_URL ?>/index.php?mod=categorias&accion=guardar" novalidate>
        <?= CsrfToken::campoOculto() ?>
        <input type="hidden" name="id" value="<?= Sanitizador::html($categoria['id_categoria'] ?? '') ?>">

        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input type="text" name="nombre" class="form-control text-capitalize" value="<?= Sanitizador::html($categoria['nombre'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control" rows="3"><?= Sanitizador::html($categoria['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Activo</label>
          <select name="activo" class="form-select">
            <option value="1" <?= (!isset($categoria['activo']) || $categoria['activo']) ? 'selected' : '' ?>>Sí</option>
            <option value="0" <?= isset($categoria['activo']) && !$categoria['activo'] ? 'selected' : '' ?>>No</option>
          </select>
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="<?= BASE_URL ?>/index.php?mod=categorias" class="btn btn-secondary">Cancelar</a>
      </form>
    </div>
  </div>
</div>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>