const CACHE_NAME = 'smartlistiq-v1';

// Extensões de assets estáticos → Cache First
const STATIC_EXT = ['.css', '.js', '.woff', '.woff2', '.ttf', '.otf',
                    '.png', '.jpg', '.jpeg', '.gif', '.svg', '.ico', '.webp'];

// ── INSTALL: pré-cacheia o offline fallback ────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(['/offline.html', '/favicon.svg', '/favicon.ico']))
            .then(() => self.skipWaiting())
    );
});

// ── ACTIVATE: limpa caches antigos ────────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

// ── FETCH ─────────────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Ignora requisições non-GET e cross-origin
    if (request.method !== 'GET') return;
    const url = new URL(request.url);
    if (url.origin !== location.origin) return;

    const ext = url.pathname.includes('.')
        ? url.pathname.slice(url.pathname.lastIndexOf('.')).toLowerCase()
        : '';

    if (STATIC_EXT.includes(ext)) {
        // Cache First: serve do cache; atualiza em background
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) {
                    // Background refresh
                    fetch(request).then((fresh) => {
                        if (fresh && fresh.status === 200) {
                            caches.open(CACHE_NAME).then((c) => c.put(request, fresh));
                        }
                    }).catch(() => {});
                    return cached;
                }
                return fetch(request).then((response) => {
                    if (!response || response.status !== 200) return response;
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((c) => c.put(request, clone));
                    return response;
                });
            })
        );
    } else if (request.mode === 'navigate') {
        // Network First para navegação HTML: fallback para offline.html
        event.respondWith(
            fetch(request).catch(() => caches.match('/offline.html'))
        );
    }
});

// ── PUSH (preparado para uso futuro) ──────────────────────────────────────
// self.addEventListener('push', (event) => {
//     const data = event.data?.json() ?? {};
//     event.waitUntil(
//         self.registration.showNotification(data.title ?? 'Smart Listiq', {
//             body: data.body ?? '',
//             icon: '/icons/192.png',
//             badge: '/icons/72.png',
//             data: { url: data.url ?? '/' },
//         })
//     );
// });
//
// self.addEventListener('notificationclick', (event) => {
//     event.notification.close();
//     event.waitUntil(clients.openWindow(event.notification.data.url));
// });
