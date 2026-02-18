/**
 * PWA Service Worker Registration
 */

// Register service worker
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').then(registration => {
        console.log('✓ Service Worker registered');
        
        // Check for updates periodically
        setInterval(() => {
            registration.update();
        }, 60000); // Check every minute
        
    }).catch(error => {
        console.error('Service Worker registration failed:', error);
    });
}

// Request persistent storage
if (navigator.storage && navigator.storage.persist) {
    navigator.storage.persist().then(persistent => {
        if (persistent) {
            console.log('✓ Persistent storage granted');
        }
    });
}

// Handle app installation
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    console.log('✓ PWA ready to install');
});
