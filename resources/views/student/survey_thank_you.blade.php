@extends('layouts.app')

@push('styles')
<style>
    .thank-you-wrap {
        max-width: 600px;
        padding-top: 7rem;
        padding-bottom: 7rem;
        text-align: center;
    }
    @media (max-width: 768px) {
        .main-content .thank-you-wrap {
            padding-top: 2.5rem !important;
            padding-bottom: 3rem !important;
        }
        .thank-you-emoji { font-size: 3.5rem !important; margin-bottom: 1.25rem !important; }
        .thank-you-wrap h1 { font-size: 1.9rem !important; }
        .thank-you-wrap p { font-size: 1rem !important; margin-bottom: 2rem !important; }
    }
</style>
@endpush

@section('content')
<div class="container thank-you-wrap">
    <div class="thank-you-emoji" style="font-size: 5rem; margin-bottom: 2rem;">🌟</div>
    <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 900; color: var(--text); margin-bottom: 1rem;">Feedback Submitted</h1>
    <p style="color: var(--text-muted); font-size: 1.1rem; line-height: 1.6; margin-bottom: 3rem;">Thank you for your valuable input. Your responses have been archived as part of our clinic's quality assurance process.</p>
    
    <a href="{{ route('student.dashboard') }}" class="btn-primary" style="padding: 1rem 2.5rem; text-decoration: none; border-radius: 12px; font-weight: 700; display: inline-block;">Return to Dashboard</a>
</div>
@endsection
