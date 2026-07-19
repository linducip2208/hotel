const CACHE_VERSION = 'hotelhub-v2';
const STATIC_CACHE = 'hotelhub-static-v2';
const DYNAMIC_CACHE = 'hotelhub-dynamic-v2';
const API_CACHE = 'hotelhub-api-v2';

// ── Precache URLs ──────────────────────────────────────────────
const PRECACHE_URLS = [
    '/',
    '/panel',
    '/booking',
    '/rooms',
    '/offline',
];

// ── Install ────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => {
            return cache.addAll(PRECACHE_URLS);
        }).then(() => self.skipWaiting())
    );
});

// ── Activate ───────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== STATIC_CACHE && key !== DYNAMIC_CACHE && key !== API_CACHE)
                    .map((key) => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

// ── Fetch ──────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // API requests: Network First
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/panel/')) {
        event.respondWith(networkFirst(request));
        return;
    }

    // Static assets: Cache First
    if (request.destination === 'style' || request.destination === 'script' ||
        request.destination === 'image' || request.destination === 'font' ||
        url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|woff2?)$/)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Everything else: Network First with offline fallback
    event.respondWith(networkFirst(request));
});

// ── Strategies ─────────────────────────────────────────────────
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (err) {
        return caches.match('/offline');
    }
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (err) {
        const cached = await caches.match(request);
        if (cached) return cached;
        if (request.headers.get('Accept')?.includes('text/html')) {
            return caches.match('/offline');
        }
        return new Response(JSON.stringify({ error: 'offline' }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

// ── Push Notification ──────────────────────────────────────────
self.addEventListener('push', (event) => {
    let data = {};
    if (event.data) {
        try { data = event.data.json(); } catch (e) {
            data = { title: 'HotelHub', body: event.data.text() };
        }
    }

    const options = {
        body: data.body || 'New notification',
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        vibrate: [200, 100, 200],
        data: { url: data.url || '/panel' },
        actions: data.actions || [],
        tag: data.tag || 'default',
    };

    event.waitUntil(self.registration.showNotification(data.title || 'HotelHub', options));
});

// ── Notification Click ─────────────────────────────────────────
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/panel';
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then((clientList) => {
            for (const client of clientList) {
                if (client.url.includes(url) && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) return clients.openWindow(url);
        })
    );
});

// ── Background Sync ────────────────────────────────────────────
self.addEventListener('sync', (event) => {
    if (event.tag === 'offline-folio-charges') {
        event.waitUntil(syncFolioCharges());
    }
    if (event.tag === 'offline-room-status') {
        event.waitUntil(syncRoomStatus());
    }
});

async function syncFolioCharges() {
    const queue = await getQueue('folio-charges');
    for (const item of queue) {
        try {
            await fetch('/panel/fo/folios/' + item.folio_id + '/charges', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(item.payload),
            });
        } catch (e) {
            // Re-queue on failure
            return;
        }
    }
    await clearQueue('folio-charges');
}

async function syncRoomStatus() {
    const queue = await getQueue('room-status');
    for (const item of queue) {
        try {
            await fetch('/panel/hk/rooms/' + item.room_id + '/status', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: item.status }),
            });
        } catch (e) {
            return;
        }
    }
    await clearQueue('room-status');
}

// ── Queue helpers (IndexedDB) ──────────────────────────────────
function openQueueDb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open('hotelhub-sync-queue', 1);
        req.onupgradeneeded = () => {
            req.result.createObjectStore('queue', { keyPath: 'id', autoIncrement: true });
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

async function getQueue(type) {
    const db = await openQueueDb();
    return new Promise((resolve) => {
        const tx = db.transaction('queue', 'readonly');
        const store = tx.objectStore('queue');
        const req = store.getAll();
        req.onsuccess = () => resolve(req.result.filter((i) => i.type === type));
    });
}

async function clearQueue(type) {
    const db = await openQueueDb();
    const tx = db.transaction('queue', 'readwrite');
    const store = tx.objectStore('queue');
    const items = await new Promise((r) => { const req = store.getAll(); req.onsuccess = () => r(req.result); });
    items.filter((i) => i.type === type).forEach((i) => store.delete(i.id));
    return new Promise((resolve) => { tx.oncomplete = resolve; });
}
