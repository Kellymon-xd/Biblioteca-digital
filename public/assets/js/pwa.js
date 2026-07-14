let deferredInstallPrompt = null;

document.addEventListener('DOMContentLoaded', function () {
  const baseUrl = (window.BD_BASE_URL || '').replace(/\/$/, '');
  const swUrl = baseUrl + '/sw.js';
  const installButton = document.getElementById('installAppButton');

  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(swUrl, { scope: baseUrl + '/' }).catch(function (error) {
      console.warn('No se pudo registrar el service worker:', error);
    });
  }

  window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    deferredInstallPrompt = event;

    if (installButton) {
      installButton.hidden = false;
    }
  });

  if (installButton) {
    installButton.addEventListener('click', async function () {
      if (!deferredInstallPrompt) return;

      deferredInstallPrompt.prompt();
      await deferredInstallPrompt.userChoice;

      deferredInstallPrompt = null;
      installButton.hidden = true;
    });
  }

  window.addEventListener('appinstalled', function () {
    deferredInstallPrompt = null;

    if (installButton) {
      installButton.hidden = true;
    }
  });
});
