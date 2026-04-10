@extends('layouts.app')

@section('title', 'Login — Mental Health Portal')

@section('content')
<style>
    body {
        background: var(--bg);
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 1rem;

        overflow: hidden;
    }

    .login-container {
        width: 1000px;
        max-width: 100%;
        z-index: 10;
    }

    .login-card {
        display: flex;
        background: var(--surface-solid);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border);
        min-height: 640px;
        position: relative;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
    }
    
    .dark-mode .login-card {
        background: rgba(17, 24, 39, 0.8);
    }

    /* ── Left Side (Hero) ── */
    .login-hero {
        flex: 1;
        background: linear-gradient(135deg, #064e3b 0%, #059669 50%, #10b981 100%);
        padding: 4.5rem 4rem;
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .login-hero::before {
        content: '';
        position: absolute;
        top: -20%;
        left: -20%;
        width: 140%;
        height: 140%;
        background: radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.15) 0%, transparent 40%),
                    radial-gradient(circle at 80% 70%, rgba(52, 211, 153, 0.25) 0%, transparent 40%);
        filter: blur(60px);
        z-index: 0;
        animation: meshFloat 20s infinite alternate ease-in-out;
    }

    @keyframes meshFloat {
        from { transform: translate(0, 0) rotate(0deg); }
        to { transform: translate(5%, 5%) rotate(5deg); }
    }

    .hero-content { position: relative; z-index: 1; }

    .hero-logo {
        width: 64px;
        height: 64px;
        background: white;
        border-radius: 18px;
        padding: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 3rem;
        box-shadow: 0 12px 24px rgba(0,0,0,0.15);
    }
    .hero-logo img { width: 100%; height: 100%; object-fit: cover; border-radius: 12px; }

    .hero-title {
        font-family: 'Outfit', sans-serif;
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 1.75rem;
        letter-spacing: -0.03em;
    }
    .hero-title b { font-weight: 900; display: block; color: #a7f3d0; margin-top: 0.25rem; }

    .hero-text {
        font-size: 1.1rem;
        opacity: 0.9;
        line-height: 1.7;
        font-weight: 400;
        max-width: 360px;
    }

    .portal-features { position: relative; z-index: 1; }
    .portal-label {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.2em;
        opacity: 0.6;
        margin-bottom: 2rem;
    }

    .feature-pill {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .feature-pill:hover { transform: translateX(12px); background: rgba(255, 255, 255, 0.18); border-color: rgba(255, 255, 255, 0.4); }
    .pill-icon { font-size: 1.6rem; }
    .pill-info { flex: 1; }
    .pill-name { font-weight: 800; font-size: 1rem; margin-bottom: 0.15rem; }
    .pill-desc { font-size: 0.8rem; opacity: 0.75; font-weight: 500; }

    /* ── Right Side (Form) ── */
    .login-form-area {
        flex: 1.3;
        padding: 5rem 6rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .form-header { margin-bottom: 3.5rem; }
    .form-title { font-family: 'Outfit', sans-serif; font-size: 2.75rem; font-weight: 900; color: var(--text); margin-bottom: 0.75rem; letter-spacing: -0.04em; }
    .form-subtitle { color: var(--text-muted); font-size: 1.15rem; font-weight: 400; }

    .input-group { margin-bottom: 2rem; }
    .input-group label { display: block; font-weight: 800; font-size: 0.85rem; margin-bottom: 0.85rem; color: var(--text); text-transform: uppercase; letter-spacing: 0.1em; }
    .input-group input { 
        width: 100%; 
        padding: 1.2rem 1.5rem; 
        border-radius: 16px; 
        border: 2px solid var(--border); 
        background: var(--surface-2);
        transition: var(--transition-fast); 
        font-size: 1.05rem;
        color: var(--text);
        font-family: inherit;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    }
    .input-group input:focus { 
        border-color: var(--primary); 
        background: var(--surface-solid);
        box-shadow: 0 0 0 6px var(--primary-glow); 
        outline: none; 
        transform: translateY(-2px);
    }

    .password-wrapper { position: relative; }
    .password-toggle {
        position: absolute; right: 1rem; top: 16px;
        background: none; border: none; padding: 0.5rem;
        cursor: pointer; color: var(--text-dim);
        display: flex; align-items: center;
        transition: all 0.2s ease;
    }
    .password-toggle:hover { color: var(--primary); transform: scale(1.1); }

    .btn-sign-in {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 1.25rem;
        border-radius: 16px;
        font-weight: 800;
        font-size: 1.1rem;
        border: none;
        cursor: pointer;
        transition: var(--transition);
        width: 100%;
        margin-top: 1rem;
        box-shadow: 0 12px 24px var(--primary-glow);
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    .btn-sign-in:hover { transform: translateY(-3px); box-shadow: 0 20px 40px var(--primary-glow); filter: brightness(1.1); }
    .btn-sign-in:active { transform: translateY(-1px); }
    .btn-sign-in:disabled { opacity: 0.7; cursor: not-allowed; filter: grayscale(0.5); }

    .btn-google:disabled { opacity: 0.7; cursor: not-allowed; }

    .loading-spinner {
        display: none;
        width: 1.2rem;
        height: 1.2rem;
        border: 3px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 1s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .or-divider {
        display: flex; align-items: center; gap: 1.5rem; margin: 3rem 0;
        color: var(--text-dim); font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em;
    }
    .or-divider::before, .or-divider::after { content: ''; flex: 1; height: 2px; background: var(--border); opacity: 0.5; }

    .btn-google {
        display: flex; align-items: center; justify-content: center; gap: 1rem;
        width: 100%; padding: 1.15rem; border-radius: 16px;
        background: var(--surface-solid); border: 2px solid var(--border);
        color: var(--text); text-decoration: none; font-weight: 800; font-size: 1rem;
        transition: var(--transition-fast);
    }
    .btn-google:hover { background: var(--surface-2); border-color: var(--border-hover); transform: translateY(-2px); box-shadow: var(--shadow); }

    .signup-prompt { text-align: center; margin-top: 3rem; font-size: 1rem; color: var(--text-muted); }
    .signup-link { color: var(--primary); font-weight: 800; text-decoration: none; position: relative; padding-bottom: 2px; }
    .signup-link::after { content: ''; position: absolute; bottom: 0; left: 0; width: 0; height: 2px; background: var(--primary); transition: width 0.3s; }
    .signup-link:hover::after { width: 100%; }

    .error-alert {
        background: rgba(239, 68, 68, 0.1); border: 2px solid rgba(239, 68, 68, 0.2); color: #ef4444;
        padding: 1.25rem; border-radius: 16px; margin-bottom: 3rem;
        font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 1rem;
    }

    @media (max-width: 1024px) {
        .login-card { flex-direction: column; min-height: auto; border-radius: 0; }
        .login-hero { padding: 4rem 2rem; }
        .login-form-area { padding: 4rem 2rem; }
    }
</style>

<div class="login-container">
    <div class="login-card" id="tiltCard">
        <!-- ── Left Hero ── -->
        <div class="login-hero">
            <div class="hero-content">
                <div class="hero-logo staggered">
                    <img src="{{ asset('logo/system_logo.jpg') }}" alt="PSU Logo">
                </div>
                <h1 class="hero-title staggered">Compassionate Care <br><b>Every Step of the Way.</b></h1>
                <p class="hero-text staggered">Your wellbeing is our priority. Access premium tools, dedicated resources, and expert support from the PSU Mental Health Team.</p>
            </div>

            <div class="portal-features">
                <div class="portal-label staggered">Our Services</div>
                
                <div class="feature-pill staggered">
                    <span class="pill-icon">🧠</span>
                    <div class="pill-info">
                        <div class="pill-name">Wellness Tools</div>
                        <div class="pill-desc">Clinical Assessments & Insights</div>
                    </div>
                </div>

                <div class="feature-pill staggered">
                    <span class="pill-icon">💬</span>
                    <div class="pill-info">
                        <div class="pill-name">AI Guardian</div>
                        <div class="pill-desc">24/7 Intelligent Support with Aria</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Right Form ── -->
        <div class="login-form-area">
            <div class="form-header">
                <h2 class="form-title staggered">Welcome Back</h2>
                <p class="form-subtitle staggered">Please authenticate with your PSU credentials</p>
            </div>

            @if($errors->any())
                <div class="error-alert staggered">
                    <span style="font-size: 1.5rem;">⚠️</span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <div class="input-group staggered">
                    <label for="email">Institutional Email</label>
                    <input type="email" id="email" name="email" placeholder="student@psu.edu.ph" value="{{ old('email') }}" required autofocus autocomplete="email">
                </div>
                
                <div class="input-group staggered">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="password-toggle" id="togglePassword">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>
                
                <div class="staggered">
                    <button type="submit" class="btn-sign-in" id="submitBtn">
                        <span class="btn-text">Secure Sign In</span>
                        <div class="loading-spinner" id="spinner"></div>
                    </button>
                </div>
            </form>

            <div class="or-divider staggered">Secured SSO Gateway</div>

            <div class="staggered">
                <a href="{{ route('auth.google') }}" class="btn-google">
                    <svg width="22" height="22" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span>Login with PSU Institutional ID</span>
                </a>
            </div>

            <div class="signup-prompt staggered">
                Need an account? <a href="{{ route('register') }}" class="signup-link">Register Institutional Access</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // High-End Entrance Animations
        gsap.from(".staggered", {
            y: 50,
            opacity: 0,
            duration: 1.4,
            stagger: 0.1,
            ease: "expo.out",
            delay: 0.3
        });

        // Loading State Submissions
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const spinner = document.getElementById('spinner');
        const btnText = submitBtn.querySelector('.btn-text');

        loginForm.addEventListener('submit', () => {
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            spinner.style.display = 'block';
            submitBtn.style.display = 'flex';
            submitBtn.style.alignItems = 'center';
            submitBtn.style.justifyContent = 'center';
            submitBtn.style.gap = '1rem';
        });

        // Google Login Loading
        document.querySelector('.btn-google').addEventListener('click', function(e) {
            this.style.opacity = '0.7';
            this.style.pointerEvents = 'none';
            this.innerHTML = '<div class="loading-spinner" style="border-top-color: var(--primary); display: block;"></div> <span>Redirecting...</span>';
        });
    });

        // Toggle Password Visibility
        const toggleBtn = document.getElementById('togglePassword');
        const passField = document.getElementById('password');

        toggleBtn.addEventListener('click', () => {
            const isPass = passField.type === 'password';
            passField.type = isPass ? 'text' : 'password';
            gsap.fromTo(toggleBtn, { scale: 0.8 }, { scale: 1.1, duration: 0.3, ease: "back.out" });
            toggleBtn.innerHTML = isPass 
                ? '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>'
                : '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        });
    });
</script>
@endsection
