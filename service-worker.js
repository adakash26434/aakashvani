const CACHE_VERSION = 'v6.0.0';
const CACHE_ASSETS = `${CACHE_VERSION}-assets`;
const CACHE_DATA = `${CACHE_VERSION}-data`;
const CACHE_IMAGES = `${CACHE_VERSION}-images`;

const OFFLINE_URLS = [
  '/', '/index.php', '/nepali-patro.php', '/tools.php',
  '/assets/css/premium.css', '/assets/favicon.svg', '/manifest.json'
];

// Install: Cache critical assets for offline access
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_ASSETS).then(cache => {
      // Only cache URLs that exist - filter out 404s
      return Promise.all(
        OFFLINE_URLS.map(url =>
          fetch(url, { mode: 'cors' })
            .then(res => { if (res.ok) cache.add(url); })
            .catch(() => null)
        )
      ).then(() => cache);
    }).catch(() => null)
  );
  self.skipWaiting();
});

// Activate: Clean up old cache versions
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys
          .filter(k => !k.startsWith(CACHE_VERSION))
          .map(k => caches.delete(k))
      );
    })
  );
  self.clients.claim();
});

// Fetch: Smart caching strategy based on request type
self.addEventListener('fetch', event => {
  const req = event.request;
  if (req.method !== 'GET') return;
  
  const url = new URL(req.url);

  // API calls: Network first, cache as fallback
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(
      fetch(req)
        .then(response => {
          if (response.ok) {
            return caches.open(CACHE_DATA).then(cache => {
              cache.put(req, response.clone());
              return response;
            });
          }
          return response;
        })
        .catch(() => caches.match(req))
    );
    return;
  }

  // Images: Cache first, network fallback
  if (req.destination === 'image' || url.pathname.match(/\.(png|jpg|jpeg|gif|svg|webp)$/i)) {
    event.respondWith(
      caches.open(CACHE_IMAGES).then(cache => {
        return cache.match(req).then(cached => {
          return cached || fetch(req).then(response => {
            if (response.ok) {
              cache.put(req, response.clone());
            }
            return response;
          });
        });
      }).catch(() => caches.match('/placeholder.svg'))
    );
    return;
  }

  // HTML pages: Network first, cache fallback (for offline)
  if (req.destination === 'document') {
    event.respondWith(
      fetch(req)
        .then(response => {
          if (response.ok) {
            return caches.open(CACHE_ASSETS).then(cache => {
              cache.put(req, response.clone());
              return response;
            });
          }
          return response;
        })
        .catch(() => caches.match(req).then(cached => cached || caches.match('/index.php')))
    );
    return;
  }

  // Default: Cache first, network fallback (CSS, JS)
  event.respondWith(
    caches.match(req).then(cached => {
      return cached || fetch(req).then(response => {
        if (response.ok) {
          return caches.open(CACHE_ASSETS).then(cache => {
            cache.put(req, response.clone());
            return response;
          });
        }
        return response;
      }).catch(() => caches.match('/offline.html'));
    })
  );
});

// Background Sync for rate alerts
self.addEventListener('sync', event => {
  if (event.tag === 'sync-rate-alerts') {
    event.waitUntil(
      fetch('/api/market-data.php?type=all')
        .then(res => res.json())
        .then(data => {
          // Update notifications in background
          return self.registration.showNotification('Market Data Updated', {
            body: `Gold: ₨${data.gold?.hallmarkPerTola || 'N/A'} | USD: ₨${data.forex?.rates?.[0]?.buy || 'N/A'}`,
            icon: '/assets/favicon.svg',
            badge: '/assets/favicon.svg',
          });
        })
        .catch(() => {})
    );
  }
});

// Push Notifications
self.addEventListener('push', event => {
  let data = {};
  try {
    data = event.data ? event.data.json() : {};
  } catch(e) {
    data = {
      title: 'आकाशवाणी',
      body: event.data ? event.data.text() : ''
    };
  }
  
  event.waitUntil(
    self.registration.showNotification(data.title || 'आकाशवाणी', {
      body: data.body || 'नयाँ update उपलब्ध छ',
      icon: '/assets/favicon.svg',
      badge: '/assets/favicon.svg',
      tag: 'nsh-notification',
      requireInteraction: data.requireInteraction || false,
      data: { url: data.url || '/' }
    })
  );
});

// Notification Click
self.addEventListener('notificationclick', event => {
  event.notification.close();
  event.waitUntil(
    clients.matchAll({ type: 'window' }).then(clientList => {
      // Check if window is already open
      for (let i = 0; i < clientList.length; i++) {
        const client = clientList[i];
        if (client.url === event.notification.data?.url && 'focus' in client) {
          return client.focus();
        }
      }
      // Open new window if not found
      if (clients.openWindow) {
        return clients.openWindow(event.notification.data?.url || '/');
      }
    })
  );
});

// Periodic Background Sync (if supported)
self.addEventListener('periodicsync', event => {
  if (event.tag === 'update-news') {
    event.waitUntil(
      fetch('/api/news-expand.php?action=auto-sync')
        .then(() => {
          // Notify clients about update
          return self.clients.matchAll().then(clients => {
            clients.forEach(client => {
              client.postMessage({
                type: 'NEWS_UPDATED',
                timestamp: Date.now()
              });
            });
          });
        })
        .catch(() => {})
    );
  }
});

