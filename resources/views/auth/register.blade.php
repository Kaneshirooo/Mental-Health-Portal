@extends('layouts.app')

@section('title', 'Register — Mental Health Portal')

@section('content')
<style>
    body {
        background: var(--bg);
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 2rem 1rem;

    }

    .register-container {
        width: 1100px;
        max-width: 100%;
        z-index: 10;
    }

    .register-card {
        display: flex;
        background: var(--surface-solid);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border);
        min-height: 700px;
        position: relative;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
    }
    
    .dark-mode .register-card {
        background: rgba(17, 24, 39, 0.8);
    }

    /* ── Left Side (Hero) ── */
    .register-hero {
        flex: 0.8;
        background: linear-gradient(135deg, #064e3b 0%, #059669 50%, #10b981 100%);
        padding: 4rem;
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .register-hero::before {
        content: '';
        position: absolute;
        top: -20%;
        left: -20%;
        width: 140%;
        height: 140%;
        background: radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 40%),
                    radial-gradient(circle at 70% 80%, rgba(52, 211, 153, 0.25) 0%, transparent 40%);
        filter: blur(60px);
        z-index: 0;
        animation: meshFloat 25s infinite alternate ease-in-out;
    }

    .hero-content { 
        position: relative; 
        z-index: 1; 
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 1.25rem;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 100px;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        margin: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .hero-title {
        font-family: 'Outfit', sans-serif;
        font-size: 3rem;
        font-weight: 900;
        line-height: 1.15;
        margin: 0;
        letter-spacing: -0.04em;
    }
    .hero-title span { color: #a7f3d0; }

    .hero-text {
        font-size: 1.15rem;
        opacity: 0.88;
        line-height: 1.65;
        margin: 0;
        max-width: 380px;
    }

    .stat-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        padding-top: 2rem;
        margin-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        width: 100%;
    }
    .stat-item h3 { font-size: 2.25rem; font-weight: 900; line-height: 1; margin: 0 0 0.5rem 0; color: #a7f3d0; }
    .stat-item p { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.7; margin: 0; }

    /* ── Right Side (Form) ── */
    .register-form-area {
        flex: 1.2;
        padding: 5rem;
        display: flex;
        flex-direction: column;
        background: var(--surface-solid);
        max-height: 90vh;
        overflow-y: auto;
    }

    .form-header { margin-bottom: 3.5rem; }
    .header-group { display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem; }
    .form-title { font-family: 'Outfit', sans-serif; font-size: 2.5rem; font-weight: 900; color: var(--text); letter-spacing: -0.04em; }
    .form-subtitle { color: var(--text-muted); font-size: 1.1rem; }
    .form-subtitle a { color: var(--primary); font-weight: 800; text-decoration: none; margin-left: 0.5rem; }

    .section-label {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 2.5rem 0 1.5rem;
    }
    .section-label::after { content: ''; flex: 1; height: 1px; background: var(--border); opacity: 0.5; }

    .input-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
    .input-group { margin-bottom: 0; }
    .input-group label { display: block; font-weight: 800; font-size: 0.8rem; margin-bottom: 0.75rem; color: var(--text); text-transform: uppercase; letter-spacing: 0.05em; }
    
    input, select { 
        width: 100%; 
        padding: 1.1rem 1.25rem; 
        border-radius: 14px; 
        border: 2px solid var(--border); 
        background: var(--surface-2);
        transition: var(--transition-fast); 
        font-size: 0.95rem;
        color: var(--text);
        font-family: inherit;
    }
    input:focus, select:focus { 
        border-color: var(--primary); 
        background: var(--surface-solid);
        box-shadow: 0 0 0 5px var(--primary-glow); 
        outline: none; 
        transform: translateY(-2px);
    }

    .btn-register {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 1.25rem;
        border-radius: 18px;
        font-weight: 800;
        font-size: 1.1rem;
        border: none;
        cursor: pointer;
        transition: var(--transition);
        width: 100%;
        margin-top: 2rem;
        box-shadow: 0 12px 24px var(--primary-glow);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .btn-register:hover { transform: translateY(-3px); box-shadow: 0 20px 40px var(--primary-glow); filter: brightness(1.1); }
    .btn-register:active { transform: translateY(-1px); }
    .btn-register:disabled { opacity: 0.7; cursor: not-allowed; filter: grayscale(0.5); }

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

    .privacy-notice { text-align: center; margin-top: 2rem; font-size: 0.75rem; color: var(--text-dim); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }

    .error-alert {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #ef4444;
        padding: 1.25rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        font-size: 0.95rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    @media (max-width: 1024px) {
        .register-card { flex-direction: column; min-height: auto; border-radius: var(--radius-lg); }
        .register-hero { padding: 3rem 2rem; }
        .register-form-area { padding: 3.5rem 2.5rem; max-height: none; }
        .input-row { grid-template-columns: 1fr; gap: 1rem; }
    }
    @media (max-width: 640px) {
        body { 
            padding: 0.75rem; 
            align-items: center; 
            justify-content: center;
            min-height: 100vh;
            min-height: 100dvh;
        }
        .register-container { 
            width: 100%; 
            max-width: 100%;
        }
        .register-card { 
            border-radius: 24px; 
            box-shadow: var(--shadow-lg);
            display: flex;
            flex-direction: column;
        }
        .register-hero { 
            padding: 1.75rem 1.5rem; 
            flex-shrink: 0;
            background: linear-gradient(135deg, #064e3b 0%, #059669 60%, #10b981 100%);
        }
        .hero-content {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .hero-badge { 
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.85rem; 
            font-size: 0.68rem; 
            font-weight: 800;
            letter-spacing: 0.12em;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 100px;
            margin: 0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .hero-title { 
            font-size: 1.65rem; 
            line-height: 1.25; 
            margin: 0;
            font-weight: 900;
        }
        .hero-text { 
            font-size: 0.88rem; 
            margin: 0;
            line-height: 1.45; 
            opacity: 0.92;
        }
        .stat-grid { 
            display: flex; 
            gap: 2rem; 
            padding-top: 1rem; 
            margin-top: 0.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
        }
        .stat-item h3 { font-size: 1.5rem; margin: 0 0 0.15rem 0; }
        .stat-item p { font-size: 0.68rem; margin: 0; opacity: 0.75; letter-spacing: 0.08em; }
        .register-form-area { padding: 2rem 1.5rem; flex: 1; }
        .form-header { margin-bottom: 1.75rem; }
        .form-title { font-size: 1.9rem; }
        .section-label { margin: 1.75rem 0 1rem; font-size: 0.75rem; }
        .input-group label { font-size: 0.8rem; }
        input, select { padding: 0.95rem 1.1rem; font-size: 0.95rem; border-radius: 14px; }
        .btn-register { padding: 1.1rem; font-size: 1rem; border-radius: 14px; margin-top: 1.5rem; }
    }

    /* Custom Scrollbar */
    .register-form-area::-webkit-scrollbar { width: 6px; }
    .register-form-area::-webkit-scrollbar-track { background: transparent; }
    .register-form-area::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }
</style>

<div class="register-container">
    <div class="register-card" id="tiltCard">
        <!-- ── Left Hero ── -->
        <div class="register-hero">
            <div class="hero-content">
                <div class="hero-badge staggered">Account Verification Required</div>
                <h1 class="hero-title staggered">Join the <span>Circle of Support.</span></h1>
                <p class="hero-text staggered">Create your account to access specialized mental health resources, professional counseling, and wellness tracking.</p>
                
                <div class="stat-grid staggered">
                    <div class="stat-item">
                        <h3>100%</h3>
                        <p>Confidential</p>
                    </div>
                    <div class="stat-item">
                        <h3>24/7</h3>
                        <p>Support Access</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Right Form ── -->
        <div class="register-form-area">
            <div class="form-header">
                <div class="header-group">
                    <h2 class="form-title staggered">New Account</h2>
                </div>
                <p class="form-subtitle staggered">Already registered? <a href="{{ route('login') }}">Sign in here</a></p>
            </div>

            @if($errors->any())
                <div class="error-alert staggered">
                    <span style="font-size: 1.4rem;">⚠️</span>
                    <div style="display: flex; flex-direction: column;">
                        @foreach($errors->all() as $error)
                            <span>{{ $error }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" id="registrationForm">
                @csrf
                
                <div class="section-label staggered">Personal Identity</div>
                <div class="input-row">
                    <div class="input-group staggered">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required placeholder="Juan Dela Cruz">
                    </div>
                    <div class="input-group staggered">
                        <label for="student_id">Student ID / Faculty ID</label>
                        <input type="text" id="student_id" name="student_id" value="{{ old('student_id') }}" required placeholder="2024-XXXXX">
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-group staggered">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="your@email.com">
                    </div>
                    <div class="input-group staggered">
                        <label for="contact_number">Mobile Number</label>
                        <input type="tel" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" placeholder="09XXXXXXXXX">
                    </div>
                </div>

                <div class="section-label staggered">Demographics</div>
                <div class="input-row">
                    <div class="input-group staggered">
                        <label for="date_of_birth">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                    </div>
                    <div class="input-group staggered">
                        <label for="gender">Gender Identity</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Option</option>
                            <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Non-binary" {{ old('gender') === 'Non-binary' ? 'selected' : '' }}>Non-binary</option>
                            <option value="Prefer not to say" {{ old('gender') === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                        </select>
                    </div>
                </div>

                <div class="section-label staggered">Academic Context</div>
                <div class="input-row">
                    <div class="input-group staggered">
                        <label for="course">Academic Course</label>
                        <input type="text" id="course" name="course" value="{{ old('course') }}" required placeholder="e.g. BS Information Technology">
                    </div>
                    <div class="input-group staggered">
                        <label for="semester">Current Semester</label>
                        <select id="semester" name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="1st Semester" {{ old('semester') === '1st Semester' ? 'selected' : '' }}>1st Semester</option>
                            <option value="2nd Semester" {{ old('semester') === '2nd Semester' ? 'selected' : '' }}>2nd Semester</option>
                            <option value="Midyear" {{ old('semester') === 'Midyear' ? 'selected' : '' }}>Midyear</option>
                        </select>
                    </div>
                </div>

                <div class="input-group staggered" style="margin-bottom: 1.5rem;">
                    <label for="department">Department / College</label>
                    <select id="department" name="department" required>
                        <option value="">Select Department / College</option>
                        <option value="CHMBAC" {{ old('department') === 'CHMBAC' ? 'selected' : '' }}>CHMBAC</option>
                        <option value="COA" {{ old('department') === 'COA' ? 'selected' : '' }}>COA</option>
                        <option value="CTE" {{ old('department') === 'CTE' ? 'selected' : '' }}>CTE</option>
                    </select>
                </div>

                <div class="section-label staggered">Security Protocol</div>
                <div class="input-row">
                    <div class="input-group staggered">
                        <label for="password">Create Password</label>
                        <input type="password" id="password" name="password" required placeholder="••••••••">
                    </div>
                    <div class="input-group staggered">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••">
                    </div>
                </div>

                <div class="staggered">
                    <button type="submit" class="btn-register" id="regSubmitBtn">
                        <span class="btn-text">Complete Registration</span>
                        <div class="loading-spinner" id="regSpinner"></div>
                    </button>
                    <p class="privacy-notice">Data handled under strict institutional privacy protocols.</p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // High-Performance Entrance
        gsap.from(".staggered", {
            y: 40,
            opacity: 0,
            duration: 1.2,
            stagger: 0.1,
            ease: "expo.out",
            delay: 0.2
        });

        // Registration Loading Logic
        const regForm = document.getElementById('registrationForm');
        const regBtn = document.getElementById('regSubmitBtn');
        const regSpinner = document.getElementById('regSpinner');
        const regBtnText = regBtn ? regBtn.querySelector('.btn-text') : null;

        if (regForm && regBtn) {
            regForm.addEventListener('submit', (e) => {
                if (!regForm.checkValidity()) {
                    return;
                }
                regBtn.disabled = true;
                if (regBtnText) regBtnText.style.display = 'none';
                if (regSpinner) regSpinner.style.display = 'block';
                regBtn.style.display = 'flex';
                regBtn.style.alignItems = 'center';
                regBtn.style.justifyContent = 'center';
                regBtn.style.gap = '1rem';
            });
        }
    });
</script>

@endsection
