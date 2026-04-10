@extends('layouts.app')

@section('title', 'Verify Login — Mental Health Portal')

@section('content')
<style>
    body {
        background: var(--bg);
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
    }

    .otp-card {
        background: var(--surface-solid);
        padding: 3rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        width: 440px;
        max-width: 90vw;
        text-align: center;
        border: 1px solid var(--border);
        animation: cardFade 0.4s ease-out;
        margin: auto;
    }

    @keyframes cardFade {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .otp-icon {
        font-size: 3rem;
        margin-bottom: 1.5rem;
        display: inline-block;
        background: var(--primary-glow);
        width: 80px;
        height: 80px;
        line-height: 80px;
        border-radius: 20px;
        color: var(--primary);
    }

    .otp-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--text);
    }

    .otp-subtitle {
        font-size: 0.95rem;
        color: var(--text-muted);
        line-height: 1.6;
        margin-bottom: 2rem;
    }

    .otp-subtitle strong {
        color: var(--text);
    }

    .otp-input-group {
        margin-bottom: 2rem;
    }

    .otp-input {
        width: 100%;
        padding: 1rem;
        font-size: 2rem;
        letter-spacing: 0.75rem;
        text-align: center;
        border-radius: var(--radius-sm);
        border: 2px solid var(--border);
        background: var(--surface-2);
        color: var(--text);
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
        transition: var(--transition);
    }

    .otp-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px var(--primary-glow);
    }

    .btn-verify {
        width: 100%;
        padding: 1rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
        margin-bottom: 1.5rem;
    }

    .btn-verify:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(13, 148, 136, 0.25);
    }

    .resend-link {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .resend-link a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
    }

    .resend-link a:hover {
        text-decoration: underline;
    }

    .error-msg {
        background: #fef2f2;
        color: #991b1b;
        padding: 0.75rem;
        border-radius: var(--radius-sm);
        font-size: 0.85rem;
        margin-bottom: 1.5rem;
        border: 1px solid #fecaca;
    }
    
    .success-msg {
        background: #f0fdf4;
        color: #166534;
        padding: 0.75rem;
        border-radius: var(--radius-sm);
        font-size: 0.85rem;
        margin-bottom: 1.5rem;
        border: 1px solid #bbf7d0;
    }
</style>

<div class="otp-card">
    <div class="otp-icon">✉️</div>
    <h1 class="otp-title">Verify Your Login</h1>
    <p class="otp-subtitle">
        We've sent a 6-digit verification code to<br>
        <strong>{{ $email }}</strong>
    </p>

    @if($errors->any())
        <div class="error-msg">⚠️ {{ $errors->first() }}</div>
    @endif

    @if(session('success'))
        <div class="success-msg">✅ {{ session('success') }}</div>
    @endif

    <form id="otpVerifyForm" method="POST" action="{{ route('verify.otp') }}">
        @csrf
        <div class="otp-input-group">
            <input type="text" name="otp_code" class="otp-input" placeholder="000000" maxlength="6" required autofocus autocomplete="one-time-code">
        </div>
        <button type="button" onclick="submitOtpForm()" class="btn-verify">Verify & Sign In</button>
    </form>

    <script>
        function submitOtpForm() {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const form = document.getElementById('otpVerifyForm');
            if (form && token) {
                const tokenInput = form.querySelector('input[name="_token"]');
                if (tokenInput) tokenInput.value = token;
                form.submit();
            } else if (form) {
                form.submit();
            }
        }
    </script>

    <div class="resend-link">
        Didn't receive the code? <a href="{{ route('resend.otp') }}">Resend Code</a>
    </div>
</div>
@endsection
