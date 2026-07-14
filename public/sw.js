const CACHE_NAME = 'biblioteca-digital-pwa-v1';

function scopeUrl(path) {
  return new URL(path, self.registration.scope).toString();
}

const APP_SHELL = [
  'manifest.webmanifest',
  'assets/css/app.css',
  'assets/js/app.js',
  'assets/js/pwa.js',
  'assets/icons/icon-192.png',
  'assets/icons/icon-512.png'
].map(scopeUrl);

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(CACHE_NAME).then(function (cache) {
      return cache.addAll(APP_SHELL);
    })
  );

  self.skipWaiting();
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(
        keys.filter(function (key) {
          return key !== CACHE_NAME;
        }).map(function (key) {
          return caches.delete(key);
        })
      );
    })
  );

  self.clients.claim();
});

self.addEventListener('fetch', function (event) {
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);
  const scope = self.registration.scope;

  if (url.origin !== self.location.origin) return;

  const isStaticAsset =
    url.href.startsWith(scope + 'assets/') ||
    url.href === scope + 'manifest.webmanifest';

  if (!isStaticAsset) return;

  event.respondWith(
    caches.match(event.request).then(function (cached) {
      if (cached) return cached;

      return fetch(event.request).then(function (response) {
        const copy = response.clone();
        caches.open(CACHE_NAME).then(function (cache) {
          cache.put(event.request, copy);
        });
        return response;
      });
    })
  );
});
