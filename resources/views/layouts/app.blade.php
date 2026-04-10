<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Mental Health Portal') }}</title>

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
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

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

    @stack('styles')
</head>

<body class="reveal">
    {{-- Spatial Background Element --}}
    <div class="spatial-bg"
        style="position:fixed; inset:0; z-index:-1; pointer-events:none; background: radial-gradient(circle at 0% 0%, rgba(16, 185, 129, 0.05) 0%, transparent 50%), radial-gradient(circle at 100% 100%, rgba(99, 102, 241, 0.05) 0%, transparent 50%); opacity: 0.6;">
    </div>

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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
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

            // Magnetic Button Effect (Premium)
            const magneticElements = document.querySelectorAll('.btn-primary, .sidebar-link');
            magneticElements.forEach(el => {
                el.addEventListener('mousemove', (e) => {
                    const rect = el.getBoundingClientRect();
                    const x = e.clientX - rect.left - rect.width / 2;
                    const y = e.clientY - rect.top - rect.height / 2;
                    gsap.to(el, {
                        x: x * 0.3,
                        y: y * 0.3,
                        duration: 0.4,
                        ease: "power2.out"
                    });
                });
                el.addEventListener('mouseleave', () => {
                    gsap.to(el, {
                        x: 0,
                        y: 0,
                        duration: 0.6,
                        ease: "elastic.out(1, 0.3)"
                    });
                });
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
            // Get the fresh CSRF token from the meta tag
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const form = document.getElementById('logoutForm');
            if (form && token) {
                // Update the token input in the hidden form before submission
                const tokenInput = form.querySelector('input[name="_token"]');
                if (tokenInput) tokenInput.value = token;
                form.submit();
            } else if (form) {
                form.submit();
            }
        }
    </script>

    @stack('scripts')
</body>

</html>