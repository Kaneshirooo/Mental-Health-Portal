<?php
/**
 * pwa_head.php — Include this inside the <head> of every page.
 * Adds PWA manifest link, theme color, apple touch icon, and registers sw.js.
 */
?>
<!-- PWA Support -->
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#4338ca">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="MH Portal">
<link rel="apple-touch-icon" href="logo/icon-512.png">

<script>
// Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js')
            .catch(err => console.warn('SW registration failed:', err));
    });
}

// PWA Install Prompt
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;

    // Show install banner after 2 seconds if not dismissed before
    const dismissed = localStorage.getItem('pwa_install_dismissed');
    if (!dismissed) {
        setTimeout(() => showInstallBanner(), 2000);
    }
});

function showInstallBanner() {
    if (!deferredPrompt) return;
    const banner = document.getElementById('pwa-install-banner');
    if (banner) banner.style.display = 'flex';
}

function installPWA() {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(choice => {
        deferredPrompt = null;
        hidePWABanner();
    });
}

function hidePWABanner() {
    const banner = document.getElementById('pwa-install-banner');
    if (banner) banner.style.display = 'none';
    localStorage.setItem('pwa_install_dismissed', '1');
}

// Hide banner if already installed
window.addEventListener('appinstalled', () => {
    hidePWABanner();
    localStorage.removeItem('pwa_install_dismissed');
});
</script>
