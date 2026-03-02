const CACHE_NAME = 'replyai-v7';

// Skip service worker for navigation requests
self.addEventListener('fetch', event => {
  // Don't cache navigation requests
  if (event.request.mode === 'navigate') {
    return;
  }
  
  // Only cache static assets
  if (event.request.destination === 'image' || 
      event.request.destination === 'style' ||
      event.request.destination === 'script' ||
      event.request.url.includes('/build/')) {
    event.respondWith(
      caches.match(event.request).then(response => {
        return response || fetch(event.request);
      })
    );
  }
});

self.addEventListener('install', event => {
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(self.clients.claim());
});
