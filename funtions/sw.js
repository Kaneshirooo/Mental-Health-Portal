// PSU Mental Health Portal - Service Worker
const CACHE_NAME = 'mh-portal-v1';

// Core assets to cache for offline use
const STATIC_ASSETS = [
  './login.php',
  './register.php',
  './styles.css',
  './manifest.json',
  './logo/system_logo.jpg'
];

// Install: cache static assets
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(STATIC_ASSETS).catch(() => {
        // Silently fail on individual asset errors
      });
    })
  );
  self.skipWaiting();
});

// Activate: clean up old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// Fetch: network-first strategy (always fresh PHP data, fallback to cache)
self.addEventListener('fetch', event => {
  // Skip non-GET, chrome-extension, and cross-origin requests
  if (event.request.method !== 'GET') return;
  if (!event.request.url.startsWith(self.location.origin)) return;

  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Cache successful responses for static assets
        if (response.ok) {
          const url = event.request.url;
          if (url.endsWith('.css') || url.endsWith('.jpg') || url.endsWith('.png') || url.endsWith('.svg') || url.endsWith('.js')) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
          }
        }
        return response;
      })
      .catch(() => {
        // Offline fallback: serve from cache
        return caches.match(event.request).then(cached => {
          if (cached) return cached;
          // If it's a navigation request and nothing cached, show login
          if (event.request.mode === 'navigate') {
            return caches.match('./login.php');
          }
        });
      })
  );
});
