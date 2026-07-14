<?php
$tituloPagina = 'Libros';
require_once SRC_PATH . '/views/layout/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="h3 mb-0">Libros</h1><p class="text-muted mb-0">Altas, bajas, consultas y reporte Excel del catálogo.</p></div>
    <div class="d-flex gap-2">
      <a href="<?= BASE_URL ?>/index.php?mod=libros&accion=exportar&q=<?= urlencode($busqueda ?? '') ?>&cat=<?= (int)($idCategoria ?? 0) ?>&disp=<?= urlencode($disponibilidad ?? '') ?>" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Exportar</a>
      <a href="<?= BASE_URL ?>/index.php?mod=libros&accion=form" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Nuevo libro</a>
    </div>
  </div>
  <div class="card border-0 shadow-sm mb-4"><div class="card-body">
    <form method="GET" action="<?= BASE_URL ?>/index.php" class="row g-2">
      <input type="hidden" name="mod" value="libros">
      <div class="col-md-5"><input type="search" name="q" class="form-control" placeholder="Buscar por título, autor, ISBN o categoría" value="<?= Sanitizador::html($busqueda ?? '') ?>"></div>
      <div class="col-md-3"><select name="cat" class="form-select"><option value="0">Todas las categorías</option><?php foreach ($categorias as $cat): ?><option value="<?= (int)$cat['id_categoria'] ?>" <?= (int)($idCategoria ?? 0) === (int)$cat['id_categoria'] ? 'selected' : '' ?>><?= Sanitizador::html($cat['nombre']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-2"><select name="disp" class="form-select"><option value="" <?= ($disponibilidad ?? '') === '' ? 'selected' : '' ?>>Todos</option><option value="disponible" <?= ($disponibilidad ?? '') === 'disponible' ? 'selected' : '' ?>>Disponibles</option><option value="no_disponible" <?= ($disponibilidad ?? '') === 'no_disponible' ? 'selected' : '' ?>>No disponibles</option></select></div>
      <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Buscar</button></div>
    </form>
  </div></div>
  <?php if (!empty($libros)): ?>
    <div class="card border-0 shadow-sm"><div class="card-body"><div class="table-responsive admin-books-responsive">
      <table class="table table-hover align-middle mb-0 admin-table admin-table-books">
        <colgroup>
          <col class="col-id">
          <col class="col-cover">
          <col class="col-title">
          <col class="col-author">
          <col class="col-category">
          <col class="col-cost">
          <col class="col-total">
          <col class="col-available">
          <col class="col-active">
          <col class="col-actions">
        </colgroup>
        <thead class="table-light"><tr><th>#</th><th class="text-center">Portada</th><th>Título</th><th>Autor</th><th class="text-center">Categoría</th><th class="text-end">Costo</th><th class="text-center">Total</th><th class="text-center">Disponibles</th><th class="text-center">Activo</th><th class="text-end">Acciones</th></tr></thead>
        <tbody>
          <?php foreach ($libros as $libro): ?>
            <tr>
              <td><?= (int)($libro['id_libro'] ?? 0) ?></td>
              <td class="text-center"><?php if (!empty($libro['imagen_thumb'])): ?><img src="<?= BASE_URL . '/' . Sanitizador::html($libro['imagen_thumb']) ?>" alt="<?= Sanitizador::html($libro['titulo'] ?? 'Portada') ?>" class="img-fluid rounded border" style="width:55px;height:75px;object-fit:cover;"><?php else: ?><div class="bg-light border rounded d-inline-flex align-items-center justify-content-center" style="width:55px;height:75px;"><i class="bi bi-image text-muted"></i></div><?php endif; ?></td>
              <td class="book-title-cell">
                <div class="fw-semibold lh-sm book-title-wrap"><?= Sanitizador::html($libro['titulo'] ?? '') ?></div>
                <small class="text-muted d-block book-isbn-wrap">ISBN: <?= Sanitizador::html($libro['isbn'] ?? 'N/A') ?></small>
              </td>
              <td class="book-author-cell"><div class="book-author-wrap"><?= Sanitizador::html($libro['autor'] ?? '') ?></div></td>
              <td class="text-center"><span class="badge badge-nowrap bg-light text-dark border"><?= Sanitizador::html($libro['categoria'] ?? '') ?></span></td>
              <td class="text-end money-cell"><span class="money-nowrap">B/. <?= number_format((float)($libro['costo'] ?? 0), 2) ?></span></td>
              <td class="text-center"><?= (int)($libro['unidades_totales'] ?? 0) ?></td>
              <td class="text-center"><span class="<?= (int)($libro['unidades_disponibles'] ?? 0) > 0 ? 'text-success' : 'text-danger' ?> fw-semibold"><?= (int)($libro['unidades_disponibles'] ?? 0) ?></span></td>
              <td class="text-center"><?= !empty($libro['activo']) ? '<span class="badge text-bg-success">Sí</span>' : '<span class="badge text-bg-secondary">No</span>' ?></td>
              <td class="text-end table-actions-cell"><div class="table-actions"><a href="<?= BASE_URL ?>/index.php?mod=libros&accion=form&id=<?= (int)$libro['id_libro'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i> Editar</a><?php if (!empty($libro['activo'])): ?><form method="POST" action="<?= BASE_URL ?>/index.php?mod=libros&accion=eliminar" class="m-0" onsubmit="return confirm('¿Desactivar este libro?');"><?= CsrfToken::campoOculto() ?><input type="hidden" name="id" value="<?= (int)$libro['id_libro'] ?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Desactivar</button></form><?php else: ?><span class="text-muted small">Desactivado</span><?php endif; ?></div></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div></div></div>
  <?php else: ?><div class="alert alert-info">No hay libros con esos filtros.</div><?php endif; ?>
</div>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>
