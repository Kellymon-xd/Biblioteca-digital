<?php
$mensajes = ErrorHandler::obtenerMensajes();
foreach ($mensajes as $tipo => $lista):
    foreach ($lista as $msg):
?>
<div class="alert alert-<?= Sanitizador::html($tipo) ?> alert-dismissible fade show" role="alert">
  <?= Sanitizador::html($msg) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endforeach; endforeach; ?>
