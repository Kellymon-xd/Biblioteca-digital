document.addEventListener('DOMContentLoaded', function () {
  const btnToggle = document.getElementById('sidebarToggle');
  const wrapper = document.getElementById('wrapper');

  if (!btnToggle || !wrapper) return;

  btnToggle.addEventListener('click', function () {
    wrapper.classList.toggle('sidebar-collapsed');
  });
});