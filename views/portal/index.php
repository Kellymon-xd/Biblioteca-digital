<?php
$tituloPagina = 'Catalogo Publico';
$activePortal = 'catalogo';
require_once SRC_PATH . '/views/layout/header.php';
require_once SRC_PATH . '/views/portal/_navbar.php';
?>

<main class="container py-4">
  <?php require_once SRC_PATH . '/views/layout/flash.php'; ?>

  <section class="row align-items-center g-4 mb-4">
    <div class="col-lg-7">
      <h1 class="h2 mb-2">Biblioteca Digital</h1>
      <p class="text-muted mb-0">
        Consulta libros disponibles, reserva ejemplares y solicita nuevos titulos para fortalecer el catalogo academico.
      </p>
    </div>
    <div class="col-lg-5">
      <form class="row g-2" method="GET" action="<?= BASE_URL ?>/index.php">
        <input type="hidden" name="mod" value="portal">

        <div class="col-12 col-md-7">
          <input class="form-control" type="search" name="q" value="<?= Sanitizador::html($busqueda ?? '') ?>"
            placeholder="Buscar por titulo, autor o ISBN">
        </div>

        <div class="col-8 col-md-3">
          <select class="form-select" name="cat">
            <option value="0">Todas</option>
            <?php foreach ($categorias as $categoria): ?>
              <option value="<?= (int) $categoria['id_categoria'] ?>" <?= $idCategoria === (int) $categoria['id_categoria'] ? 'selected' : '' ?>>
                <?= Sanitizador::html($categoria['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-4 col-md-2">
          <button class="btn btn-primary w-100" type="submit">
            <i class="bi bi-search"></i>
          </button>
        </div>
      </form>
    </div>
  </section>

  <section class="row g-3">
    <?php if (!empty($libros)): ?>
      <?php foreach ($libros as $libro): ?>
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <article class="card h-100 shadow-sm book-card">
            <?php if (!empty($libro['imagen_thumb'])): ?>
              <img class="book-thumb" src="<?= BASE_URL . '/' . Sanitizador::html($libro['imagen_thumb']) ?>"
                alt="<?= Sanitizador::html($libro['titulo']) ?>">
            <?php else: ?>
              <div class="book-thumb book-thumb-empty">
                <i class="bi bi-journal-bookmark"></i>
              </div>
            <?php endif; ?>

            <div class="card-body d-flex flex-column">
              <span class="badge text-bg-light border align-self-start mb-2">
                <?= Sanitizador::html($libro['categoria']) ?>
              </span>

              <h2 class="h6 mb-1"><?= Sanitizador::html($libro['titulo']) ?></h2>
              <p class="small text-muted mb-2"><?= Sanitizador::html($libro['autor']) ?></p>
              <p class="small flex-grow-1">
                <?= Sanitizador::html(mb_strimwidth((string) ($libro['descripcion'] ?? ''), 0, 110, '...')) ?>
              </p>

              <div class="d-flex justify-content-between align-items-center gap-2">
                <span
                  class="small fw-semibold <?= (int) $libro['unidades_disponibles'] > 0 ? 'text-success' : 'text-danger' ?>">
                  <?= (int) $libro['unidades_disponibles'] ?> disponibles
                </span>

                <?php if (!empty($_SESSION['estudiante']) && (int) $libro['unidades_disponibles'] > 0): ?>
                  <form method="POST" action="<?= BASE_URL ?>/index.php?mod=reservas&accion=reservar">
                    <?= CsrfToken::campoOculto() ?>
                    <input type="hidden" name="id_libro" value="<?= (int) $libro['id_libro'] ?>">
                    <input type="hidden" name="fecha_devolucion" value="<?= date('Y-m-d', strtotime('+15 days')) ?>">
                    <button class="btn btn-sm btn-primary" type="submit">Reservar</button>
                  </form>

                <?php elseif (!empty($_SESSION['estudiante']) && (int) $libro['unidades_disponibles'] <= 0): ?>
                  <a class="btn btn-sm btn-outline-warning" href="<?= BASE_URL ?>/index.php?mod=solicitudes&accion=mis">
                    Solicitar
                  </a>

                <?php else: ?>
                  <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/index.php?mod=portal&accion=login">
                    Ingresar
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12">
        <div class="alert alert-info">
          No encontramos libros con esa busqueda. Los estudiantes pueden iniciar sesion y solicitar un titulo nuevo.
        </div>
      </div>
    <?php endif; ?>
  </section>

  <section class="row g-3 mt-4">
    <div class="col-md-4">
      <div class="p-3 bg-white border rounded-2 h-100">
        <h2 class="h6">Reservas en linea</h2>
        <p class="small text-muted mb-0">
          El inventario se descuenta automaticamente al reservar y aumenta al devolver.
        </p>
      </div>
    </div>

    <div class="col-md-4">
      <div class="p-3 bg-white border rounded-2 h-100">
        <h2 class="h6">Solicitudes academicas</h2>
        <p class="small text-muted mb-0">
          Los estudiantes pueden pedir libros por area cuando no estan disponibles o cuando no existen en el catalogo.
        </p>
      </div>
    </div>

    <div class="col-md-4">
      <div class="p-3 bg-white border rounded-2 h-100">
        <h2 class="h6">Desarrolladores</h2>
        <p class="small text-muted mb-0">
          Proyecto PHP MVC con MySQL, CSRF, auditoria y control de acceso.
        </p>
      </div>
    </div>
  </section>
</main>

<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>