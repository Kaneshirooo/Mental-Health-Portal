/*  PSU Mental Health Portal — service worker
 *  Kept conservative on purpose: this is a form-heavy, authenticated Laravel
 *  app (CSRF tokens, sessions). We must NEVER serve stale HTML or cached POST
 *  responses. So: network-only for navigations and all non-GET requests;
 *  stale-while-revalidate for same-origin static assets only. This satisfies
 *  PWA installability without risking broken logins or stale pages.
 */
const CACHE = 'mh-portal-static-v1';
const STATIC_RE = /\.(?:css|js|mjs|png|jpg|jpeg|gif|svg|webp|ico|woff2?|ttf|eot)(?:\?|$)/i;

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE).then((cache) =>
      cache.addAll(['/icons/icon-192.png', '/icons/icon-512.png', '/manifest.webmanifest']).catch(() => {})
    )
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return; // never touch forms / mutations

  const url = new URL(req.url);

  // Navigations (pages) -> network only, so sessions/CSRF stay fresh.
  if (req.mode === 'navigate') {
    event.respondWith(fetch(req).catch(() => caches.match('/offline.html').then((r) => r || Response.error())));
    return;
  }

  // Only cache same-origin static assets.
  if (url.origin !== self.location.origin || !STATIC_RE.test(url.pathname)) return;

  event.respondWith(
    caches.open(CACHE).then((cache) =>
      cache.match(req).then((cached) => {
        const fetchPromise = fetch(req)
          .then((res) => {
            if (res && res.ok) cache.put(req, res.clone());
            return res;
          })
          .catch(() => cached);
        return cached || fetchPromise;
      })
    )
  );
});
