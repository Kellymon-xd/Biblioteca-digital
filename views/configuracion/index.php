<?php
$tituloPagina = 'Configuración de préstamos';
require_once SRC_PATH . '/views/layout/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-0">Configuración de préstamos</h1>
      <p class="text-muted mb-0">Define dinámicamente la cantidad de días del préstamo.</p>
    </div>
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form method="POST" action="<?= BASE_URL ?>/index.php?mod=configuracion&accion=guardar" class="row g-3">
        <?= CsrfToken::campoOculto() ?>
        <div class="col-md-4">
          <label class="form-label">Días para estudiantes</label>
          <input type="number" min="1" max="365" name="dias_prestamo_estudiante" class="form-control" value="<?= (int)$diasEstudiante ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Días para profesores</label>
          <input type="number" min="1" max="365" name="dias_prestamo_profesor" class="form-control" value="<?= (int)$diasProfesor ?>">
        </div>
        <div class="col-12">
          <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar configuración</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>
