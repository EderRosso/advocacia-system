const CACHE_NAME = 'advsystem-v4';
const urlsToCache = [
  '/',
  '/login.php',
  '/manifest.json',
  '/assets/css/style.css',
  '/assets/img/icon-192x192.png',
  '/assets/img/icon-512x512.png'
];

// Instalação do Service Worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Cache atualizado para v3');
        return cache.addAll(urlsToCache);
      })
  );
  self.skipWaiting(); // Força a instalação imediata
});

// Estratégia "Network First, fallback to cache" 
// (Tenta a rede primeiro para não quebrar o PHP/Sessão. Se falhar por estar offline, tenta o cache).
self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;

  event.respondWith(
    fetch(event.request).catch(() => {
      return caches.match(event.request);
    })
  );
});

// Limpar caches antigos e assumir controle imediatamente
self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            console.log('Deletando cache antigo:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim(); // Garante que a página use a nova versão do Service Worker
});
