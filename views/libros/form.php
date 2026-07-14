<?php
$tituloPagina = empty($libro) ? 'Nuevo Libro' : 'Editar Libro';
require_once SRC_PATH . '/views/layout/header.php';
$old = Sanitizador::obtenerViejosDatos();
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= Sanitizador::html($tituloPagina) ?></h1>
    <a href="<?= BASE_URL ?>/index.php?mod=libros" class="btn btn-secondary">Volver</a>
  </div>
  <div class="card border-0 shadow-sm"><div class="card-body">
    <form method="POST" action="<?= BASE_URL ?>/index.php?mod=libros&accion=guardar" enctype="multipart/form-data" novalidate>
      <?= CsrfToken::campoOculto() ?>
      <input type="hidden" name="id" value="<?= Sanitizador::html((string)($libro['id_libro'] ?? $old['id'] ?? '')) ?>">
      <div class="row g-3">
        <div class="col-md-4"><label class="form-label">ISBN</label><input type="text" name="isbn" class="form-control" value="<?= Sanitizador::html((string)($old['isbn'] ?? $libro['isbn'] ?? '')) ?>"></div>
        <div class="col-md-8"><label class="form-label">Título</label><input type="text" name="titulo" class="form-control text-capitalize" required value="<?= Sanitizador::html((string)($old['titulo'] ?? $libro['titulo'] ?? '')) ?>"></div>
        <div class="col-md-6"><label class="form-label">Autor</label><input type="text" name="autor" class="form-control text-capitalize" required value="<?= Sanitizador::html((string)($old['autor'] ?? $libro['autor'] ?? '')) ?>"></div>
        <div class="col-md-6"><label class="form-label">Editorial</label><input type="text" name="editorial" class="form-control text-capitalize" value="<?= Sanitizador::html((string)($old['editorial'] ?? $libro['editorial'] ?? '')) ?>"></div>
        <div class="col-md-3"><label class="form-label">Año</label><input type="number" name="anio_publicacion" class="form-control" value="<?= Sanitizador::html((string)($old['anio_publicacion'] ?? $libro['anio_publicacion'] ?? '')) ?>"></div>
        <div class="col-md-3"><label class="form-label">Costo</label><div class="input-group"><span class="input-group-text">B/.</span><input type="number" step="0.01" min="0" name="costo" class="form-control" value="<?= Sanitizador::html((string)($old['costo'] ?? $libro['costo'] ?? '0.00')) ?>"></div></div>
        <div class="col-md-3"><label class="form-label">Categoría</label><select name="id_categoria" class="form-select" required><option value="">Seleccione</option><?php foreach ($categorias as $cat): ?><?php $sel = (isset($old['id_categoria']) && $old['id_categoria'] == $cat['id_categoria']) || (!isset($old['id_categoria']) && isset($libro['id_categoria']) && $libro['id_categoria'] == $cat['id_categoria']); ?><option value="<?= (int)$cat['id_categoria'] ?>" <?= $sel ? 'selected' : '' ?>><?= Sanitizador::html($cat['nombre']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><label class="form-label">Unidades</label><input type="number" min="1" name="unidades_totales" class="form-control" required value="<?= Sanitizador::html((string)($old['unidades_totales'] ?? $libro['unidades_totales'] ?? '')) ?>"></div>
        <div class="col-12"><label class="form-label">Descripción</label><textarea name="descripcion" class="form-control" rows="4"><?= Sanitizador::html((string)($old['descripcion'] ?? $libro['descripcion'] ?? '')) ?></textarea></div>
        <div class="col-md-6">
          <label class="form-label">Imagen</label>
          <?php if (!empty($libro['imagen_thumb'])): ?>
            <div class="mb-2"><img src="<?= BASE_URL . '/' . Sanitizador::html($libro['imagen_thumb']) ?>" alt="Portada actual" style="width:90px;height:120px;object-fit:cover;border-radius:6px;border:1px solid #ddd;"><div class="form-text">Portada actual. Si subes otra imagen, se reemplazará.</div></div>
          <?php endif; ?>
          <input type="file" name="imagen" class="form-control" accept="image/jpeg,image/png,image/webp">
          <div class="form-text">Se guarda la original y se genera un thumbnail automáticamente.</div>
        </div>
        <div class="col-md-6"><label class="form-label">Activo</label><select name="activo" class="form-select"><?php $activo = (string)($old['activo'] ?? $libro['activo'] ?? '1'); ?><option value="1" <?= $activo === '1' ? 'selected' : '' ?>>Sí</option><option value="0" <?= $activo === '0' ? 'selected' : '' ?>>No</option></select></div>
      </div>
      <div class="mt-4"><button type="submit" class="btn btn-primary">Guardar</button><a href="<?= BASE_URL ?>/index.php?mod=libros" class="btn btn-outline-secondary">Cancelar</a></div>
    </form>
  </div></div>
</div>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>
