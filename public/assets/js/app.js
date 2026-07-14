document.addEventListener('DOMContentLoaded', function () {
  const wrapper = document.getElementById('wrapper');
  const sidebar = document.getElementById('sidebar');
  const btnToggle = document.getElementById('sidebarToggle');

  if (wrapper && sidebar && btnToggle) {
    btnToggle.addEventListener('click', function () {
      if (window.innerWidth <= 991) {
        wrapper.classList.toggle('sidebar-open');
      } else {
        wrapper.classList.toggle('sidebar-collapsed');
        localStorage.setItem(
          'adminSidebarCollapsed',
          wrapper.classList.contains('sidebar-collapsed') ? '1' : '0'
        );
      }
    });

    if (window.innerWidth > 991 && localStorage.getItem('adminSidebarCollapsed') === '1') {
      wrapper.classList.add('sidebar-collapsed');
    }

    wrapper.addEventListener('click', function (e) {
      if (
        window.innerWidth <= 991 &&
        wrapper.classList.contains('sidebar-open') &&
        !sidebar.contains(e.target) &&
        !btnToggle.contains(e.target)
      ) {
        wrapper.classList.remove('sidebar-open');
      }
    });
  }

  // Collapse local para el navbar del portal sin Bootstrap JS externo
  document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (btn) {
    const targetSelector = btn.getAttribute('data-bs-target') || btn.getAttribute('href');
    if (!targetSelector) return;

    const target = document.querySelector(targetSelector);
    if (!target) return;

    btn.addEventListener('click', function () {
      const isOpen = target.classList.toggle('show');
      btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  });

  // Cerrar menú del portal cuando se toca un enlace en móvil
  document.querySelectorAll('#portalNav a').forEach(function (link) {
    link.addEventListener('click', function () {
      const nav = document.getElementById('portalNav');
      const toggle = document.querySelector('[data-bs-target="#portalNav"]');
      if (nav && window.innerWidth <= 991) {
        nav.classList.remove('show');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
      }
    });
  });

  // Tablas responsive tipo tarjeta: agrega etiquetas desde el thead
  document.querySelectorAll('.table').forEach(function (table) {
    const headers = Array.from(table.querySelectorAll('thead th')).map(function (th) {
      return th.textContent.trim();
    });

    table.querySelectorAll('tbody tr').forEach(function (row) {
      row.querySelectorAll('td').forEach(function (td, index) {
        if (!td.hasAttribute('data-label')) {
          td.setAttribute('data-label', headers[index] || '');
        }
      });
    });
  });

  function activarHeaderMovilAutoHide() {
    const headerAdmin = document.querySelector('#page-content-wrapper > .navbar');
    const headerPortal = document.querySelector('.portal-navbar');
    const header = headerAdmin || headerPortal;

    if (!header) return;

    let lastScrollY = window.scrollY;
    let ticking = false;

    function actualizarHeader() {
      const currentScrollY = window.scrollY;
      const diferencia = currentScrollY - lastScrollY;

      if (window.innerWidth <= 768) {
        if (currentScrollY <= 20) {
          header.classList.remove('mobile-header-hidden');
        } else if (diferencia > 8) {
          header.classList.add('mobile-header-hidden');
        } else if (diferencia < -8) {
          header.classList.remove('mobile-header-hidden');
        }
      } else {
        header.classList.remove('mobile-header-hidden');
      }

      lastScrollY = currentScrollY;
      ticking = false;
    }

    window.addEventListener('scroll', function () {
      if (!ticking) {
        window.requestAnimationFrame(actualizarHeader);
        ticking = true;
      }
    }, { passive: true });
  }

  activarHeaderMovilAutoHide();

  const horaPanama = document.getElementById('horaPanama');

  function actualizarHoraPanama() {
    if (!horaPanama) return;

    const opciones = {
      timeZone: 'America/Panama',
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      hour12: false
    };

    const hora = new Intl.DateTimeFormat('es-PA', opciones).format(new Date());
    horaPanama.innerHTML = '<i class="bi bi-clock me-1"></i>' + hora;
  }

  actualizarHoraPanama();
  setInterval(actualizarHoraPanama, 1000);
});
