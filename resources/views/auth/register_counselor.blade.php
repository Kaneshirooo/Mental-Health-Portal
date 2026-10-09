@extends('layouts.app')

@section('title', 'Counselor Registration — Mental Health Portal')

@section('content')
@include('auth.psu-theme')
<style>
    .creg-wrap { max-width: 640px; margin: 0 auto; padding: 2rem 1.25rem 4rem; }
    .creg-card { background: var(--surface-solid); border: 1px solid var(--border); border-radius: 28px; padding: 2.5rem; box-shadow: var(--shadow-lg); }
    .creg-card label { display: block; font-weight: 800; font-size: 0.75rem; margin-bottom: 0.5rem; color: var(--text); text-transform: uppercase; letter-spacing: 0.05em; }
    .creg-card input, .creg-card select { width: 100%; padding: 0.95rem 1.1rem; border-radius: 14px; border: 2px solid var(--border); background: var(--surface-2); font-size: 0.95rem; color: var(--text); font-family: inherit; margin-bottom: 1.1rem; }
    .creg-card input:focus, .creg-card select:focus { border-color: var(--primary); background: var(--surface-solid); outline: none; box-shadow: 0 0 0 5px var(--primary-glow); }
    .creg-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media (max-width: 640px) { .creg-row { grid-template-columns: 1fr; } .creg-card { padding: 1.75rem 1.25rem; } }
    .error-alert { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; padding: 1rem 1.25rem; border-radius: 14px; margin-bottom: 1.5rem; font-weight: 700; }
</style>

<div class="creg-wrap">
    <div class="creg-card">
        <div style="font-weight: 800; color: var(--primary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 0.5rem;">Counselor Registration</div>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 900; color: var(--text); margin: 0 0 0.5rem;">Join as a Counselor</h1>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Create a counselor account to support students. A <strong>staff ID is required</strong> — our team will verify it after you register. When you log in, you will be taken straight to the <strong>counselor dashboard</strong>.</p>
        <p style="margin-bottom: 2rem;">Already have an account? <a href="{{ route('login') }}" style="color: var(--primary); font-weight: 800;">Sign in here</a> · <a href="{{ route('register') }}" style="color: var(--primary); font-weight: 800;">Register as a student instead</a></p>

        @if($errors->any())
            <div class="error-alert">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('register.counselor') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label for="full_name">Full Name *</label>
            <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required placeholder="Juan Dela Cruz">

            <div class="creg-row">
                <div>
                    <label for="staff_id">Staff / Faculty ID *</label>
                    <input type="text" id="staff_id" name="staff_id" value="{{ old('staff_id') }}" required placeholder="e.g. EMP-2024-001">
                </div>
                <div>
                    <label for="department">Department / College</label>
                    <select id="department" name="department">
                        <option value="">Select (optional)</option>
                        <option value="CHMBAC" {{ old('department') === 'CHMBAC' ? 'selected' : '' }}>CHMBAC</option>
                        <option value="COA" {{ old('department') === 'COA' ? 'selected' : '' }}>COA</option>
                        <option value="CTE" {{ old('department') === 'CTE' ? 'selected' : '' }}>CTE</option>
                    </select>
                </div>
            </div>

            <div class="creg-row">
                <div>
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="your@email.com">
                </div>
                <div>
                    <label for="contact_number">Contact Number *</label>
                    <input type="tel" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" required placeholder="09XXXXXXXXX">
                </div>
            </div>

            <label for="id_proof">Staff ID / Proof of Employment *</label>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: -0.25rem 0 0.75rem;">Upload a clear photo of your staff ID or appointment paper (JPG, PNG, or PDF, max 5MB).</p>
            <input type="file" id="id_proof" name="id_proof" accept=".jpg,.jpeg,.png,.pdf" required>

            <div class="creg-row">
                <div>
                    <label for="password">Create Password *</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
                <div>
                    <label for="password_confirmation">Confirm Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; padding: 1.1rem; border-radius: 16px; font-weight: 800; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 0.5rem;">Register as Counselor</button>
            <p style="text-align: center; margin-top: 1.25rem; font-size: 0.75rem; color: var(--text-dim); font-weight: 600;">Counselor accounts are verified by the head counselor before full access.</p>
        </form>
    </div>
</div>
@endsection
