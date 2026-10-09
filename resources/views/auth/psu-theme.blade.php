{{-- PSU blue theme override for login / register pages only.
     Brand greens become PSU blue; semantic greens (success alerts) stay green. --}}
<style>
    body {
        --primary: #2f3da6;
        --primary-dark: #1c256e;
        --primary-light: #7986e8;
        --primary-glow: rgba(47, 61, 166, 0.14);
    }
    .login-hero,
    .register-hero {
        background: linear-gradient(135deg, #141b5c 0%, #2b379b 55%, #3f51b5 100%) !important;
    }
    .hero-title b,
    .hero-title span,
    .stat-item h3 {
        color: #c5cae9 !important;
    }
</style>
