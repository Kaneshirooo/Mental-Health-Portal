<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Mental Health Portal') }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('logo/system_logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('logo/system_logo.jpg') }}">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- GSAP (Premium Animations) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

    <!-- Original CSS -->
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}?v={{ filemtime(public_path('css/styles.css')) }}">

    <!-- Tailwind (Optional/Keep for some utilities if needed) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        // Inline theme init to prevent flicker
        (function () {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>

    <style>
        /* Toast Notification System */
        #clinical-toast-container {
            position: fixed;
            top: 2rem;
            right: 2rem;
            z-index: 999999;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            pointer-events: none;
        }
        .clinical-toast {
            pointer-events: auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0,0,0,0.1);
            padding: 1.25rem 1.75rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 320px;
            max-width: 450px;
            opacity: 0;
            transform: translateX(40px);
        }
        .dark-mode .clinical-toast {
            background: rgba(30, 41, 59, 0.9);
            border-color: rgba(255,255,255,0.1);
            color: #f8fafc;
        }
        .toast-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .toast-success .toast-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .toast-error .toast-icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .toast-info .toast-icon { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        
        .toast-content { flex: 1; }
        .toast-title { font-weight: 850; font-family: 'Outfit', sans-serif; font-size: 1rem; margin-bottom: 0.1rem; }
        .toast-message { font-size: 0.85rem; font-weight: 500; opacity: 0.8; line-height: 1.4; }

        /* Global Password Eye Toggle System */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }
        .password-wrapper input {
            width: 100%;
            padding-right: 2.75rem !important;
        }
        .password-toggle {
            position: absolute;
            right: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0.35rem;
            cursor: pointer;
            color: var(--text-dim, #94a3b8);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s, transform 0.2s;
            z-index: 5;
        }
        .password-toggle:hover {
            color: var(--primary, #10b981);
            transform: translateY(-50%) scale(1.1);
        }
    </style>


    @stack('styles')
</head>

<body class="reveal">
    {{-- Spatial Background Element --}}
    <div class="spatial-bg"
        style="position:fixed; inset:0; z-index:-1; pointer-events:none; background: radial-gradient(circle at 0% 0%, rgba(16, 185, 129, 0.05) 0%, transparent 50%), radial-gradient(circle at 100% 100%, rgba(99, 102, 241, 0.05) 0%, transparent 50%); opacity: 0.6;">
    </div>

    <div id="clinical-toast-container"></div>

    @auth
        @include('layouts.navigation')
        <main class="main-content">
            <div id="content-reveal">
                @yield('content')
            </div>

            <footer class="footer mt-12 py-8 border-t border-white/5 opacity-50 text-center text-sm">
                <p>© {{ date('Y') }} Mental Health Pre-Assessment System. All rights reserved.</p>
            </footer>
        </main>
    @else
        @yield('content')
    @endauth

    @stack('modals')

    <!-- Global Scroll-To-Top Floating Button -->
    <button id="scrollTopBtn" class="scroll-top-btn" aria-label="Scroll to top" title="Scroll to top">
        <i class="ph-bold ph-caret-up"></i>
    </button>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/ajax-helpers.js') }}"></script>

    <script>
        // Global Scroll-To-Top Button Controller
        document.addEventListener('DOMContentLoaded', () => {
            const scrollBtn = document.getElementById('scrollTopBtn');
            if (scrollBtn) {
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 280) {
                        scrollBtn.classList.add('visible');
                    } else {
                        scrollBtn.classList.remove('visible');
                    }
                }, { passive: true });

                scrollBtn.addEventListener('click', () => {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }
        });

        // GSAP Reveal Logic
        document.addEventListener('DOMContentLoaded', () => {
            gsap.registerPlugin(ScrollTrigger);

            // Stagger reveal for cards and sections
            gsap.from('.card, .feature-card, .stat-card-clinical', {
                y: 30,
                opacity: 0,
                duration: 1,
                stagger: 0.1,
                ease: "expo.out",
                clearProps: "all"
            });

        });


        // Theme Toggle Logic
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggle = document.getElementById('themeToggle');
            if (themeToggle) {
                const themeIcon = document.getElementById('themeIcon');
                const themeLabel = document.getElementById('themeLabel');
                const html = document.documentElement;

                themeToggle.addEventListener('click', () => {
                    const isDark = html.classList.toggle('dark-mode');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    if (themeIcon) themeIcon.textContent = isDark ? '☀️' : '🌙';
                    if (themeLabel) themeLabel.textContent = isDark ? 'Light Mode' : 'Dark Mode';
                });

                // Set initial state
                const isDark = html.classList.contains('dark-mode');
                if (themeIcon) themeIcon.textContent = isDark ? '☀️' : '🌙';
                if (themeLabel) themeLabel.textContent = isDark ? 'Light Mode' : 'Dark Mode';
            }
        });

        // Logout Modal
        function openSignOutModal() {
            const modal = document.getElementById('signOutModal');
            if (modal) modal.style.display = 'flex';
        }
        function closeSignOutModal() {
            const modal = document.getElementById('signOutModal');
            if (modal) modal.style.display = 'none';
        }
        function performLogout() {
            const btn = event?.target || document.querySelector('#signOutModal .btn-primary');
            const form = document.getElementById('logoutForm');
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            if (form) {
                if (btn && btn.tagName === 'BUTTON') {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Signing Out...';
                    btn.style.opacity = '0.7';
                    btn.style.cursor = 'not-allowed';
                }
                
                if (token) {
                    const tokenInput = form.querySelector('input[name="_token"]');
                    if (tokenInput) tokenInput.value = token;
                }
                
                // Add a small delay for visual feedback before submission
                setTimeout(() => form.submit(), 400);
            }
        }

        /**
         * Global App AJAX & Interface Utility
         */
        const App = {
            toast: function({ type = 'success', title = '', message = '' }) {
                const container = document.getElementById('clinical-toast-container');
                const toast = document.createElement('div');
                toast.className = `clinical-toast toast-${type}`;
                
                const icons = {
                    success: 'ph-check-circle',
                    error: 'ph-warning-circle',
                    info: 'ph-info'
                };
                
                toast.innerHTML = `
                    <div class="toast-icon"><i class="ph-bold ${icons[type] || icons.info}"></i></div>
                    <div class="toast-content">
                        <div class="toast-title">${title}</div>
                        <div class="toast-message">${message}</div>
                    </div>
                `;
                
                container.appendChild(toast);
                
                // GSAP Entrance
                gsap.to(toast, { x: 0, opacity: 1, duration: 0.8, ease: "expo.out" });
                
                // Auto removal
                setTimeout(() => {
                    gsap.to(toast, { 
                        x: 100, opacity: 0, scale: 0.9, duration: 0.6, ease: "expo.in",
                        onComplete: () => toast.remove() 
                    });
                }, 5000);
            },

            ajax: function(options) {
                const defaults = {
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    error: (xhr) => {
                        const msg = xhr.responseJSON?.message || 'A system transition error occurred.';
                        App.toast({ type: 'error', title: 'Action Failed', message: msg });
                    }
                };
                return $.ajax({ ...defaults, ...options });
            }
        };

        // Blade fallback for session flash messages
        @if(session('success'))
            App.toast({ type: 'success', title: 'Process Complete', message: "{{ session('success') }}" });
        @endif
        @if(session('error'))
            App.toast({ type: 'error', title: 'System Warning', message: "{{ session('error') }}" });
        @endif
    </script>

    <script>
    // Global Password Eye Toggle — auto-applies to all password inputs on every page
    (function initPasswordToggles() {
        const EYE_OPEN = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
        const EYE_OFF  = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;

        function wrapInput(input) {
            // Skip if already wrapped or is in an existing password-wrapper
            if (input.closest('.password-wrapper')) return;

            const wrapper = document.createElement('div');
            wrapper.className = 'password-wrapper';

            // Preserve existing inline styles / classes on input
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'password-toggle';
            btn.setAttribute('aria-label', 'Toggle password visibility');
            btn.innerHTML = EYE_OPEN;
            wrapper.appendChild(btn);

            btn.addEventListener('click', () => {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                btn.innerHTML = isPassword ? EYE_OFF : EYE_OPEN;
                btn.style.color = isPassword ? 'var(--primary, #10b981)' : '';
                input.focus();
            });
        }

        function init() {
            document.querySelectorAll('input[type="password"]').forEach(wrapInput);
        }

        // Run on DOMContentLoaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }

        // Also watch for dynamically added inputs (modals, etc.)
        const observer = new MutationObserver(mutations => {
            mutations.forEach(m => {
                m.addedNodes.forEach(node => {
                    if (node.nodeType !== 1) return;
                    if (node.matches('input[type="password"]')) wrapInput(node);
                    node.querySelectorAll && node.querySelectorAll('input[type="password"]').forEach(wrapInput);
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    })();
    </script>

    @stack('scripts')
</body>

</html>