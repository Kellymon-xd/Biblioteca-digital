<nav class="navbar navbar-expand-lg bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= BASE_URL ?>/index.php?mod=portal">
            <i class="bi bi-book-half text-primary me-2"></i>Biblioteca Digital
        </a>

        <div class="d-flex gap-2">
            <a class="btn btn-outline-primary btn-sm <?= ($activePortal ?? '') === 'catalogo' ? 'active' : '' ?>"
                href="<?= BASE_URL ?>/index.php?mod=portal">
                Catálogo
            </a>

            <?php if (!empty($_SESSION['estudiante'])): ?>
                <a class="btn btn-outline-primary btn-sm <?= ($activePortal ?? '') === 'reservas' ? 'active' : '' ?>"
                    href="<?= BASE_URL ?>/index.php?mod=reservas">
                    Mis reservas
                </a>

                <a class="btn btn-outline-warning btn-sm <?= ($activePortal ?? '') === 'solicitar' ? 'active' : '' ?>"
                    href="<?= BASE_URL ?>/index.php?mod=solicitudes&accion=mis">
                    Solicitar libro
                </a>

                <a class="btn btn-outline-secondary btn-sm" href="<?= BASE_URL ?>/index.php?mod=portal&accion=logout">
                    Salir
                </a>
            <?php else: ?>
                <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/index.php?mod=portal&accion=login">
                    Portal estudiantil
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>