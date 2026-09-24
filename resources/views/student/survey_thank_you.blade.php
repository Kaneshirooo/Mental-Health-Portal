@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px; padding-top: 10rem; padding-bottom: 10rem; text-align: center;">
    <div style="font-size: 5rem; margin-bottom: 2rem;">🌟</div>
    <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 900; color: var(--text); margin-bottom: 1rem;">Feedback Submitted</h1>
    <p style="color: var(--text-muted); font-size: 1.1rem; line-height: 1.6; margin-bottom: 3rem;">Thank you for your valuable input. Your responses have been archived as part of our clinic's quality assurance process.</p>
    
    <a href="{{ route('student.dashboard') }}" class="btn-primary" style="padding: 1rem 2.5rem; text-decoration: none; border-radius: 12px; font-weight: 700; display: inline-block;">Return to Dashboard</a>
</div>
@endsection
