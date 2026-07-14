<?php
$tituloPagina = 'Catálogo Público';
$activePortal = 'catalogo';
require_once SRC_PATH . '/views/layout/header.php';
require_once SRC_PATH . '/views/portal/_navbar.php';
?>

<main class="container py-4 portal-page">
  <section class="row align-items-center g-4 mb-4 portal-hero">
    <div class="col-lg-7">
      <span class="badge text-bg-primary mb-2">PHP MVC + MySQL + PDO</span>
      <h1 class="h2 mb-2">Biblioteca Digital</h1>
      <p class="text-muted mb-0">
        Consulta libros disponibles, reserva ejemplares y solicita nuevos títulos. Las bibliotecas digitales permiten acceso rápido,
        mejor control del inventario, reportes por período y apoyo académico para estudiantes y docentes.
      </p>
    </div>
    <div class="col-lg-5">
      <form class="row g-2 portal-search-card" method="GET" action="<?= BASE_URL ?>/index.php">
        <input type="hidden" name="mod" value="portal">
        <div class="col-12 col-md-7">
          <input class="form-control" type="search" name="q" value="<?= Sanitizador::html($busqueda ?? '') ?>" placeholder="Buscar por título, autor o tema">
        </div>
        <div class="col-8 col-md-3">
          <select class="form-select" name="cat">
            <option value="0">Todas</option>
            <?php foreach ($categorias as $categoria): ?>
              <option value="<?= (int)$categoria['id_categoria'] ?>" <?= $idCategoria === (int)$categoria['id_categoria'] ? 'selected' : '' ?>><?= Sanitizador::html($categoria['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-4 col-md-2"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i></button></div>
      </form>
    </div>
  </section>

  <?php if (!empty($_SESSION['lector'])): ?>
    <div class="alert alert-primary d-flex align-items-center justify-content-between portal-loan-alert">
      <span><i class="bi bi-clock-history me-1"></i>Tu préstamo actual está configurado para <strong><?= (int)$diasPrestamo ?> días</strong>.</span>
      <span class="small">Tipo: <?= Sanitizador::html($_SESSION['lector']['label'] ?? '') ?></span>
    </div>
  <?php endif; ?>

  <section class="row g-3 book-grid">
    <?php if (!empty($libros)): ?>
      <?php foreach ($libros as $libro): ?>
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <article class="card h-100 shadow-sm book-card">
            <?php if (!empty($libro['imagen_thumb'])): ?>
              <img class="book-thumb" src="<?= BASE_URL . '/' . Sanitizador::html($libro['imagen_thumb']) ?>" alt="<?= Sanitizador::html($libro['titulo']) ?>">
            <?php else: ?>
              <div class="book-thumb book-thumb-empty"><i class="bi bi-journal-bookmark"></i></div>
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
              <span class="badge text-bg-light border align-self-start mb-2"><?= Sanitizador::html($libro['categoria']) ?></span>
              <h2 class="h6 mb-1"><?= Sanitizador::html($libro['titulo']) ?></h2>
              <p class="small text-muted mb-1"><?= Sanitizador::html($libro['autor']) ?></p>
              <?php if (isset($libro['costo'])): ?><p class="small mb-2">Costo ref.: B/. <?= number_format((float)$libro['costo'], 2) ?></p><?php endif; ?>
              <p class="small flex-grow-1"><?= Sanitizador::html(mb_strimwidth((string)($libro['descripcion'] ?? ''), 0, 110, '...')) ?></p>
              <div class="book-card-actions">
                <span class="small fw-semibold <?= (int)$libro['unidades_disponibles'] > 0 ? 'text-success' : 'text-danger' ?>">
                  <?= (int)$libro['unidades_disponibles'] ?> de <?= (int)$libro['unidades_totales'] ?> disponibles
                </span>
                <?php if (!empty($_SESSION['lector']) && (int)$libro['unidades_disponibles'] > 0): ?>
                  <form method="POST" action="<?= BASE_URL ?>/index.php?mod=reservas&accion=reservar">
                    <?= CsrfToken::campoOculto() ?>
                    <input type="hidden" name="id_libro" value="<?= (int)$libro['id_libro'] ?>">
                    <button class="btn btn-sm btn-primary" type="submit">Reservar</button>
                  </form>
                <?php elseif (!empty($_SESSION['lector']) && (int)$libro['unidades_disponibles'] <= 0): ?>
                  <a class="btn btn-sm btn-outline-warning" href="<?= BASE_URL ?>/index.php?mod=solicitudes&accion=mis">Solicitar</a>
                <?php else: ?>
                  <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/index.php?mod=portal&accion=login">Ingresar</a>
                <?php endif; ?>
              </div>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12"><div class="alert alert-info">No encontramos libros con esa búsqueda.</div></div>
    <?php endif; ?>
  </section>

  <section class="row g-3 mt-4">
    <div class="col-md-4"><div class="p-3 bg-white border rounded-2 h-100"><h2 class="h6">Stack del sistema</h2><p class="small text-muted mb-0">PHP 8, MVC, PDO, MySQL/MariaDB, Bootstrap, CSRF, HMAC, validación y sanitización centralizada.</p></div></div>
    <div class="col-md-4"><div class="p-3 bg-white border rounded-2 h-100"><h2 class="h6">Importancia</h2><p class="small text-muted mb-0">Facilita reservas, control de stock, estadísticas de uso y solicitudes de adquisición o préstamo interbibliotecario.</p></div></div>
    <div class="col-md-4"><div class="p-3 bg-white border rounded-2 h-100"><h2 class="h6">Contáctenos</h2><p class="small text-muted mb-0">biblioteca@institucion.edu.pa<br>Tel. 0000-0000<br>Horario: lunes a viernes.</p></div></div>
  </section>
</main>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>
