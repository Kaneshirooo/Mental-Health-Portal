@extends('layouts.app')

@push('styles')
<style>
    .hero-emergency {
        background: linear-gradient(135deg, #be123c 0%, #dc2626 100%);
        border-radius: var(--radius-lg);
        padding: 3.5rem;
        color: white;
        margin-bottom: 2.5rem;
        text-align: center;
        position: relative;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(220, 38, 38, 0.2);
    }
    .hero-emergency::after {
        content: '🆘';
        position: absolute;
        right: -20px;
        bottom: -20px;
        font-size: 12rem;
        opacity: 0.1;
        transform: rotate(-15deg);
    }
    .contact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    .contact-card {
        background: var(--surface-solid);
        border-radius: var(--radius);
        padding: 2rem;
        border: 1px solid var(--border);
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .contact-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); border-color: #fecaca; }
    .contact-icon {
        width: 48px; height: 48px;
        background: #fef2f2;
        color: #dc2626;
        border-radius: 12px;
        display: flex;
        align-items: center; justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }
    .contact-title { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.25rem; color: var(--text); }
    .contact-desc { color: var(--text-dim); font-size: 0.9rem; line-height: 1.5; }
    .contact-number {
        font-family: 'Inter', monospace;
        font-size: 1.5rem;
        font-weight: 800;
        color: #dc2626;
        margin: 0.5rem 0;
        display: block;
        text-decoration: none;
    }
    .btn-call {
        background: #dc2626;
        color: white;
        padding: 0.85rem;
        border-radius: var(--radius-sm);
        text-align: center;
        text-decoration: none;
        font-weight: 700;
        transition: var(--transition);
        display: block;
        cursor: default;
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1000px; padding-top: 2rem; padding-bottom: 4rem;">
    
    <div class="hero-emergency">
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem;">You Are Not Alone.</h1>
        <p style="font-size: 1.15rem; opacity: 0.9; max-width: 600px; margin: 0 auto;">If you or someone you know is in immediate danger or experiencing a crisis, please reach out to the resources below. Help is available 24/7.</p>
    </div>

    <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text);">Immediate Assistance</h2>
    
    <div class="contact-grid">
        <!-- Campus Security -->
        <div class="contact-card" style="border-left: 5px solid #dc2626;">
            <div class="contact-icon">🏫</div>
            <h3 class="contact-title">Campus Security</h3>
            <p class="contact-desc">Immediate on-campus response for emergencies and safety concerns.</p>
            <span class="contact-number">911 / (Local Sec)</span>
            <span class="btn-call" style="background:#dc2626; color:white;">Call Security Now</span>
        </div>

        <!-- National Crisis Hotline -->
        <div class="contact-card">
            <div class="contact-icon">📞</div>
            <h3 class="contact-title">National Crisis Hotline</h3>
            <p class="contact-desc">Confidential support from trained professionals for any mental health crisis.</p>
            <span class="contact-number">1553</span>
            <span class="btn-call" style="background:#dc2626; color:white;">Call Hotline Now</span>
        </div>

        <!-- Hopeline Philippines -->
        <div class="contact-card">
            <div class="contact-icon">❤️</div>
            <h3 class="contact-title">Hopeline PH</h3>
            <p class="contact-desc">24/7 suicide prevention and emotional support services.</p>
            <span class="contact-number">0917-558-4673</span>
            <span class="btn-call" style="background:#dc2626; color:white;">Call Hopeline</span>
        </div>
    </div>

    <div style="background: var(--surface-solid); border: 1px dashed #dc2626; border-radius: var(--radius); padding: 2rem; text-align: center;">
        <h3 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: #dc2626; margin-bottom: 0.5rem;">Clinical Support</h3>
        <p style="color: var(--text-dim); font-size: 0.9rem; margin-bottom: 1.5rem;">For non-emergency scheduling, you can book an appointment with our clinic during operating hours.</p>
        <a href="{{ route('student.appointments') }}" style="color: var(--primary); font-weight: 700; text-decoration: none; border-bottom: 2px solid var(--primary-glow);">Go to Appointments →</a>
    </div>

</div>
@endsection
