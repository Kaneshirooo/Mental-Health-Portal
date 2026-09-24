@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1000px; margin: 0 auto; padding: 2rem 1.5rem 4rem;">
    <!-- Header -->
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3.5rem;">
        <a href="{{ route('counselor.students.show', $student->user_id) }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: var(--text-dim); font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">
            <i class="ph ph-arrow-left"></i> Back to Student Profile
        </a>
        <button onclick="window.print()" class="btn-secondary" style="padding: 0.75rem 1.25rem; border-radius: 12px; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; display: flex; align-items: center; gap: 0.5rem;">
            <i class="ph ph-printer"></i> Export Summary
        </button>
    </header>

    <article style="background: var(--surface-solid); border: 2px solid var(--border); border-radius: 40px; padding: 4rem; box-shadow: var(--shadow-lg); position: relative; overflow: hidden;">
        <!-- Status Badge -->
        <div style="position: absolute; top: 0; right: 0; padding: 2rem;">
             <span style="padding: 0.5rem 1.25rem; border-radius: 100px; font-weight: 900; font-size: 0.7rem; text-transform: uppercase; {{ 
                ($session->ai_report['risk_level'] ?? 'Low') === 'High' || ($session->ai_report['risk_level'] ?? 'Low') === 'Critical' 
                ? 'background: #ef444420; color: #ef4444; border: 1.5px solid #ef4444;' 
                : 'background: #10b98120; color: #10b981; border: 1.5px solid #10b981;' 
            }}">
                {{ $session->ai_report['risk_level'] ?? 'Low' }} Risk Protocol
            </span>
        </div>

        <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 4rem;">
            <div style="width: 64px; height: 64px; border-radius: 20px; background: rgba(16,185,129,0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.75rem;">
                <i class="ph ph-sparkle"></i>
            </div>
            <div>
                <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 900; color: var(--text); margin: 0; letter-spacing: -0.02em;">{{ $session->created_at->format('F d, Y') }}</h1>
                <p style="color: var(--text-muted); font-weight: 600; font-size: 1rem; margin-top: 0.25rem;">Clinical Session Summary • Logged at {{ $session->created_at->format('h:i A') }}</p>
            </div>
        </div>

        <!-- Metric Grid -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 4rem;">
            @php
                $metrics = [
                    ['label' => 'Mood', 'val' => $session->ai_report['mood'] ?? 'Stable', 'icon' => 'ph-smiley'],
                    ['label' => 'Sleep', 'val' => $session->ai_report['sleep'] ?? 'N/A', 'icon' => 'ph-moon'],
                    ['label' => 'Energy', 'val' => $session->ai_report['energy'] ?? 'N/A', 'icon' => 'ph-lightning'],
                    ['label' => 'Focus', 'val' => $session->ai_report['focus'] ?? 'N/A', 'icon' => 'ph-target'],
                    ['label' => 'Social', 'val' => $session->ai_report['social'] ?? 'N/A', 'icon' => 'ph-users'],
                    ['label' => 'Appetite', 'val' => $session->ai_report['appetite'] ?? 'N/A', 'icon' => 'ph-fork-knife'],
                ];
            @endphp
            @foreach($metrics as $m)
                <div style="background: var(--surface-2); border: 1.5px solid var(--border); border-radius: 24px; padding: 1.5rem; text-align: center;">
                    <i class="ph {{ $m['icon'] }}" style="color: #10b981; font-size: 1.25rem; margin-bottom: 0.75rem;"></i>
                    <div style="font-size: 0.65rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.25rem;">{{ $m['label'] }}</div>
                    <div style="font-weight: 900; color: var(--text); font-size: 1rem; text-transform: uppercase;">{{ $m['val'] }}</div>
                </div>
            @endforeach
        </div>

        <!-- Clinical Content -->
        <div style="display: flex; flex-direction: column; gap: 3.5rem;">
            <div>
                <h3 style="font-size: 0.75rem; font-weight: 900; color: var(--primary); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <span style="width: 24px; height: 2px; background: var(--primary);"></span> Core Concerns Identified
                </h3>
                <div style="background: var(--surface-2); border: 1.5px solid var(--border); border-radius: 28px; padding: 2.5rem; color: var(--text); font-size: 1.15rem; font-weight: 500; line-height: 1.8; font-style: italic;">
                    "{!! $session->ai_report['core_concerns'] ?? 'No concerns identified.' !!}"
                </div>
            </div>

            <div>
                <h3 style="font-size: 0.75rem; font-weight: 900; color: var(--primary); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <span style="width: 24px; height: 2px; background: var(--primary);"></span> Clinical Observations
                </h3>
                <div style="background: var(--surface-2); border: 1.5px solid var(--border); border-radius: 28px; padding: 2.5rem; color: var(--text); font-size: 1.15rem; font-weight: 500; line-height: 1.8; font-style: italic;">
                    "{!! $session->ai_report['clinical_observations'] ?? 'No observations recorded.' !!}"
                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <footer style="margin-top: 5rem; padding-top: 2rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                Follow-up Needed: <span style="{{ ($session->ai_report['follow_up_needed'] ?? false) ? 'color: #ef4444;' : 'color: #10b981;' }}">{{ ($session->ai_report['follow_up_needed'] ?? false) ? 'YES' : 'NO' }}</span>
            </div>
            <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                Aria AI Protocol v2.1
            </div>
        </footer>
    </article>
</div>

<style>
@media print {
    .btn-secondary, header { display: none !important; }
    article { border: 1px solid #ddd !important; box-shadow: none !important; padding: 2rem !important; }
    .container { max-width: 100% !important; padding: 0 !important; }
}
</style>
@endsection
