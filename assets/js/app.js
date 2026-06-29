document.addEventListener('DOMContentLoaded', function () {
  const btnToggle = document.getElementById('sidebarToggle');
  const wrapper = document.getElementById('wrapper');

  if (!btnToggle || !wrapper) return;

  btnToggle.addEventListener('click', function () {
    wrapper.classList.toggle('sidebar-collapsed');
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const btnToggle = document.getElementById('sidebarToggle');
  const wrapper = document.getElementById('wrapper');

  if (btnToggle && wrapper) {
    btnToggle.addEventListener('click', function () {
      wrapper.classList.toggle('sidebar-collapsed');
    });
  }

  const horaPanama = document.getElementById('horaPanama');

  function actualizarHoraPanama() {
    if (!horaPanama) return;

    const ahora = new Date();
    const opciones = {
      timeZone: 'America/Panama',
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      hour12: false
    };

    const horaFormateada = new Intl.DateTimeFormat('es-PA', opciones).format(ahora);
    horaPanama.innerHTML = '<i class="bi bi-clock me-1"></i>' + horaFormateada;
  }

  actualizarHoraPanama();
  setInterval(actualizarHoraPanama, 1000);
});