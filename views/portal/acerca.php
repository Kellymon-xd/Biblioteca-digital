<?php
$tituloPagina = 'Bondades del Sistema';
$activePortal = 'acerca';
require_once SRC_PATH . '/views/layout/header.php';
require_once SRC_PATH . '/views/portal/_navbar.php';
?>
<main class="portal-about-page">
  <section class="about-hero-section">
    <div class="container">
      <div class="row align-items-center g-4">
        <div class="col-lg-7">
          <span class="about-eyebrow">Proyecto semestral · Desarrollo de Software VII</span>
          <h1>Biblioteca Digital para una gestión académica más rápida, ordenada y segura.</h1>
          <p>
            Este sistema digitaliza los procesos principales de una biblioteca académica: consulta de catálogo,
            reservas, control de ejemplares, solicitudes de libros, reportes y administración de usuarios. Su objetivo
            es ofrecer una plataforma práctica para estudiantes, docentes y personal administrativo.
          </p>
          <div class="about-hero-actions">
            <a class="btn btn-primary btn-lg" href="<?= BASE_URL ?>/index.php?mod=portal&accion=index">
              Ver catálogo público
            </a>
            <a class="btn btn-outline-light btn-lg" href="<?= BASE_URL ?>/index.php?mod=portal&accion=login">
              Acceder como lector
            </a>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="about-showcase-card">
            <div class="about-showcase-top">
              <span class="about-showcase-icon"><i class="bi bi-book-half"></i></span>
              <div>
                <h2>Biblioteca Digital</h2>
                <p>UTP Panamá</p>
              </div>
            </div>
            <div class="about-metric-grid">
              <div><strong>24/7</strong><span>Consulta del catálogo</span></div>
              <div><strong>MVC</strong><span>Arquitectura ordenada</span></div>
              <div><strong>API</strong><span>Integración con Postman</span></div>
              <div><strong>PDO</strong><span>Conexión segura a BD</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="container about-section">
    <div class="about-section-heading">
      <span>¿Por qué usarla?</span>
      <h2>Bondades del sistema</h2>
      <p>La plataforma ayuda a reemplazar procesos manuales por flujos digitales simples, trazables y fáciles de administrar.</p>
    </div>

    <div class="row g-4">
      <div class="col-md-6 col-xl-3">
        <article class="about-feature-card">
          <i class="bi bi-search"></i>
          <h3>Consulta rápida</h3>
          <p>Permite buscar libros por título, autor, tema o categoría sin depender de listados físicos.</p>
        </article>
      </div>
      <div class="col-md-6 col-xl-3">
        <article class="about-feature-card">
          <i class="bi bi-calendar-check"></i>
          <h3>Reservas en línea</h3>
          <p>Estudiantes y docentes pueden reservar ejemplares disponibles desde el portal público.</p>
        </article>
      </div>
      <div class="col-md-6 col-xl-3">
        <article class="about-feature-card">
          <i class="bi bi-envelope-plus"></i>
          <h3>Solicitudes</h3>
          <p>Los usuarios pueden pedir libros no disponibles, nuevas adquisiciones o apoyo interbibliotecario.</p>
        </article>
      </div>
      <div class="col-md-6 col-xl-3">
        <article class="about-feature-card">
          <i class="bi bi-clipboard-data"></i>
          <h3>Reportes</h3>
          <p>Genera información útil sobre reservas, inventario, disponibilidad y libros más utilizados.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="about-strengths-section">
    <div class="container">
      <div class="row g-4 align-items-stretch">
        <div class="col-lg-5">
          <div class="about-dark-card h-100">
            <span>Fortalezas</span>
            <h2>Una solución pensada para gestión real de biblioteca.</h2>
            <p>
              La aplicación no solo muestra libros: también controla disponibilidad, usuarios, roles,
              solicitudes, estadísticas y trazabilidad de accesos. Esto facilita una administración más clara
              y una mejor experiencia para la comunidad académica.
            </p>
          </div>
        </div>
        <div class="col-lg-7">
          <div class="row g-3 h-100">
            <div class="col-md-6">
              <div class="about-strength-item">
                <i class="bi bi-shield-check"></i>
                <div>
                  <h3>Seguridad</h3>
                  <p>Incluye CSRF, validación, sanitización, hash de contraseñas, roles y control de intentos fallidos.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="about-strength-item">
                <i class="bi bi-people"></i>
                <div>
                  <h3>Usuarios por rol</h3>
                  <p>Administra estudiantes, docentes, usuarios administrativos y permisos por módulo.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="about-strength-item">
                <i class="bi bi-journals"></i>
                <div>
                  <h3>Inventario controlado</h3>
                  <p>Maneja unidades totales, unidades disponibles, portadas, categorías y costo referencial.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="about-strength-item">
                <i class="bi bi-gear"></i>
                <div>
                  <h3>Escalable</h3>
                  <p>La estructura MVC separa controladores, modelos y vistas, facilitando mantenimiento y mejoras.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="container about-section">
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="about-panel h-100">
          <h2>Funciones principales</h2>
          <ul class="about-check-list">
            <li>Catálogo público con búsqueda y filtros por categoría.</li>
            <li>Reservas para estudiantes y docentes.</li>
            <li>Solicitudes de libros no existentes o sin disponibilidad.</li>
            <li>Panel administrativo con CRUD de libros, usuarios, roles, carreras, categorías, estudiantes y docentes.</li>
            <li>Reportes exportables y estadísticas por período.</li>
            <li>API para pruebas e integración con herramientas como Postman.</li>
          </ul>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="about-panel h-100">
          <h2>Valor para la institución</h2>
          <ul class="about-check-list">
            <li>Reduce el trabajo manual y mejora el control de préstamos.</li>
            <li>Centraliza la información de usuarios, libros y movimientos.</li>
            <li>Permite tomar decisiones con datos de uso y disponibilidad.</li>
            <li>Mejora la atención a estudiantes y docentes.</li>
            <li>Demuestra una solución web completa usando PHP, MySQL, MVC, PDO y seguridad básica.</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="container about-section about-developers-section">
    <div class="about-section-heading">
      <span>Equipo de desarrollo</span>
      <h2>Desarrolladores</h2>
      <p>Proyecto semestral elaborado para la asignatura Desarrollo de Software VII.</p>
    </div>

    <div class="row g-4 justify-content-center">
      <div class="col-md-4">
        <article class="developer-card">
          <div class="developer-avatar">KB</div>
          <h3>Kelly Beitia</h3>
          <p>Desarrolladora del sistema</p>
        </article>
      </div>
      <div class="col-md-4">
        <article class="developer-card">
          <div class="developer-avatar">ED</div>
          <h3>Eric De Leon</h3>
          <p>Desarrollador del sistema</p>
        </article>
      </div>
      <div class="col-md-4">
        <article class="developer-card">
          <div class="developer-avatar">VR</div>
          <h3>Victor Rivas</h3>
          <p>Desarrollador del sistema</p>
        </article>
      </div>
    </div>
  </section>

  <section class="about-cta-section">
    <div class="container">
      <div class="about-cta-card">
        <div>
          <span>Sistema listo para demostración</span>
          <h2>Una biblioteca digital moderna, segura y fácil de usar.</h2>
          <p>Ideal para presentar el flujo completo: catálogo público, acceso de lectores, reservas, solicitudes, administración y reportes.</p>
        </div>
        <a class="btn btn-primary btn-lg" href="<?= BASE_URL ?>/index.php?mod=portal&accion=index">
          Explorar catálogo
        </a>
      </div>
    </div>
  </section>
</main>
<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>
