// public/sw.js (الكود الجديد والمُصحح)

const CACHE_NAME = 'dawamy-cache-v2'; // تم تغيير الرقم لإجبار المتصفح على التحديث
const urlsToCache = [
  '/',
  '/manifest.json'
];

// Install a service worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
  );
});

// Cache and return requests
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(function(response) {
        if (response) {
          return response; // Cache hit - return response
        }
        return fetch(event.request);
      }
    )
  );
});

// Update a service worker
self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Listener for push events
self.addEventListener('push', function (event) {
    if (event.data) {
        const data = event.data.json();
        const promiseChain = self.registration.showNotification(data.title, {
            body: data.body,
            icon: data.icon || '/images/icons/icon-192x192.png',
            data: {
                url: data.url // استلام الرابط من الخادم
            }
        });
        event.waitUntil(promiseChain);
    } else {
        console.log('Push event but no data');
    }
});

// Listener for notification click
self.addEventListener('notificationclick', function(event) {
    event.notification.close(); // إغلاق الإشعار عند الضغط عليه

    // فتح الرابط المرفق مع الإشعار
    const urlToOpen = event.notification.data.url;
    if (urlToOpen) {
        event.waitUntil(
            clients.openWindow(urlToOpen)
        );
    }
});