<?php
$tituloPagina = 'Portal de Usuarios';
require_once SRC_PATH . '/views/layout/header.php';
?>
<main class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
      <div class="text-center mb-4">
        <i class="bi bi-book-half display-5 text-primary"></i>
        <h1 class="h3 mt-2">Portal de Biblioteca</h1>
        <p class="text-muted mb-0">Acceso para estudiantes y docentes.</p>
      </div>
      <?php require_once SRC_PATH . '/views/layout/flash.php'; ?>
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <form method="POST" action="<?= BASE_URL ?>/index.php?mod=portal&accion=login" novalidate>
            <?= CsrfToken::campoOculto() ?>
            <div class="mb-3">
              <label class="form-label">Tipo de usuario</label>
              <select name="tipo" class="form-select">
                <option value="ESTUDIANTE" <?= ($_POST['tipo'] ?? '') !== 'PROFESOR' ? 'selected' : '' ?>>Estudiante</option>
                <option value="PROFESOR" <?= ($_POST['tipo'] ?? '') === 'PROFESOR' ? 'selected' : '' ?>>Docente</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">CIP o correo</label>
              <input type="text" name="identificador" class="form-control" required value="<?= Sanitizador::html((string)($_POST['identificador'] ?? '')) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Contraseña</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
          </form>
        </div>
      </div>
      <div class="text-center mt-3">
        <a href="<?= BASE_URL ?>/index.php?mod=portal" class="text-decoration-none">Volver al página principal</a>
      </div>
    </div>
  </div>
</main>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>
