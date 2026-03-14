<?php
/**
 * pwa_banner.php — Include this right after <body> opens on every page.
 * Shows a bottom banner prompting the user to install the app.
 */
?>
<!-- PWA Install Banner -->
<div id="pwa-install-banner" style="
    display: none;
    position: fixed;
    bottom: 1.5rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 99999;
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.18), 0 0 0 1px rgba(67,56,202,0.1);
    padding: 1rem 1.5rem;
    align-items: center;
    gap: 1rem;
    width: calc(100% - 2rem);
    max-width: 420px;
    animation: bannerSlideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
">
    <div style="width: 44px; height: 44px; border-radius: 12px; overflow: hidden; flex-shrink: 0; border: 2px solid #e0e7ff;">
        <img src="logo/system_logo.jpg" alt="App Icon" style="width: 100%; height: 100%; object-fit: cover;">
    </div>
    <div style="flex: 1; min-width: 0;">
        <div style="font-weight: 800; font-size: 0.9rem; color: #0f172a; margin-bottom: 0.1rem;">Install MH Portal</div>
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 500;">Add to your home screen for quick access</div>
    </div>
    <div style="display: flex; gap: 0.5rem; flex-shrink: 0;">
        <button onclick="installPWA()" style="
            background: #4338ca;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.8rem;
            cursor: pointer;
            white-space: nowrap;
        ">Install</button>
        <button onclick="hidePWABanner()" style="
            background: #f1f5f9;
            color: #64748b;
            border: none;
            padding: 0.5rem 0.75rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.8rem;
            cursor: pointer;
        ">✕</button>
    </div>
</div>

<style>
@keyframes bannerSlideUp {
    from { opacity: 0; transform: translateX(-50%) translateY(20px); }
    to   { opacity: 1; transform: translateX(-50%) translateY(0); }
}
</style>
