const CACHE = 'hotelhms-v1';
self.addEventListener('install', e => {
    self.skipWaiting();
});
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys => Promise.all(
            keys.filter(k => k !== CACHE).map(k => caches.delete(k))
        ))
    );
});
self.addEventListener('fetch', e => {
    e.respondWith(
        caches.match(e.request).then(r => r || fetch(e.request))
    );
});
