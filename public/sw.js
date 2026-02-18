/**
 * Service Worker
 * Handles caching, offline support, and background sync
 */

const CACHE_NAME = 'hml-v1';
const API_CACHE = 'hml-api-v1';
const URLS_TO_CACHE = [
    '/',
    '/login.html',
    '/dashboard.html',
    '/css/style.css',
    '/js/app.js',
    '/js/pwa.js',
    '/images/logo.svg',
    '/manifest.json'
];

// Install event
self.addEventListener('install', event => {
    console.log('[SW] Installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            console.log('[SW] Caching app shell');
            return cache.addAll(URLS_TO_CACHE);
        })
    );
    
    self.skipWaiting();
});

// Activate event
self.addEventListener('activate', event => {
    console.log('[SW] Activating...');
    
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME && cacheName !== API_CACHE) {
                        console.log('[SW] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    
    self.clients.claim();
});

// Fetch event
self.addEventListener('fetch', event => {
    const {request} = event;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // API requests: Network-first strategy
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(networkFirst(request));
        return;
    }
    
    // Static assets: Cache-first strategy
    event.respondWith(cacheFirst(request));
});

// Cache-first strategy (for static assets)
async function cacheFirst(request) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);
    
    if (cached) {
        return cached;
    }
    
    try {
        const response = await fetch(request);
        
        if (response && response.status === 200) {
            const clonedResponse = response.clone();
            cache.put(request, clonedResponse);
        }
        
        return response;
    } catch (error) {
        console.log('[SW] Fetch failed for:', request.url, error);
        return new Response('Offline', {status: 503});
    }
}

// Network-first strategy (for API calls)
async function networkFirst(request) {
    const cache = await caches.open(API_CACHE);
    
    try {
        const response = await fetch(request);
        
        if (response && response.status === 200) {
            const clonedResponse = response.clone();
            cache.put(request, clonedResponse);
        }
        
        return response;
    } catch (error) {
        console.log('[SW] Network failed, using cache for:', request.url);
        const cached = await cache.match(request);
        
        if (cached) {
            return cached;
        }
        
        return new Response(JSON.stringify({
            status: 'error',
            message: 'Çevrimdışı durumda. Lütfen internet bağlantınızı kontrol edin.'
        }), {
            status: 503,
            headers: {'Content-Type': 'application/json'}
        });
    }
}

// Handle messages from clients
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
