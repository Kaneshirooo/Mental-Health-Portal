@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem 4rem;">
    <header class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; flex-wrap: wrap; gap: 1rem;">
        <a href="{{ route('counselor.students.show', $student->user_id) }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: var(--text-dim); font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">
            <i class="ph ph-arrow-left"></i> Back to Student Profile
        </a>
        <button onclick="window.print()" class="btn-secondary" style="padding: 0.75rem 1.25rem; border-radius: 12px; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; display: flex; align-items: center; gap: 0.5rem;">
            <i class="ph ph-printer"></i> Print Answers
        </button>
    </header>

    <div style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 28px; padding: 2.5rem; margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
        <div style="font-weight: 800; color: var(--primary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 0.5rem;">Check-in Answers</div>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 900; color: var(--text); margin: 0;">{{ $student->full_name }}</h1>
        <p style="color: var(--text-muted); font-weight: 600; margin-top: 0.4rem;">{{ $score->assessment_date ? $score->assessment_date->format('l, M d, Y') . ' at ' . $score->assessment_date->format('h:i A') : 'N/A' }}</p>
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 1.25rem; align-items: center;">
            @php
                $riskColor = strtolower($score->risk_level ?? 'low') === 'critical' ? '#ef4444' : (strtolower($score->risk_level ?? '') === 'high' ? '#f97316' : (strtolower($score->risk_level ?? '') === 'moderate' ? '#f59e0b' : '#10b981'));
            @endphp
            <span style="padding: 0.4rem 0.9rem; border-radius: 100px; font-weight: 800; font-size: 0.7rem; text-transform: uppercase; background: var(--surface-2); color: {{ $riskColor }}; border: 1.5px solid currentColor;">{{ $score->risk_level }}</span>
            <span style="font-weight: 800; color: var(--text); font-size: 0.9rem;">Low mood {{ $score->depression_score }}/27 • Worries {{ $score->anxiety_score }}/21 • Pressure {{ $score->stress_score }}/21</span>
        </div>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem; font-size: 0.72rem; font-weight: 700; color: var(--text-dim);">
            <span>0 = Not at all</span><span>1 = Several days</span><span>2 = More than half the days</span><span>3 = Nearly every day</span>
        </div>
    </div>

    @php
        $grouped = $responses->groupBy(fn($r) => strtolower($r->question?->category ?? 'other'));
        $areaNames = ['depression' => 'Low Mood', 'anxiety' => 'Worries', 'stress' => 'Pressure'];
    @endphp

    @forelse($grouped as $cat => $items)
        <div style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 24px; padding: 2rem; margin-bottom: 1.5rem;">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 800; color: var(--text); margin: 0 0 1.25rem; text-transform: capitalize;">{{ $areaNames[$cat] ?? ucfirst($cat) }}</h2>
            @foreach($items->sortBy(fn($r) => $r->question?->question_number ?? 0) as $r)
                <div style="display: flex; gap: 1rem; align-items: flex-start; padding: 0.9rem 0; border-bottom: 1px solid var(--border);">
                    <div style="min-width: 36px; height: 36px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1rem; {{ ($r->response_value ?? 0) >= 2 ? 'background: rgba(239,68,68,0.1); color: #dc2626;' : (($r->response_value ?? 0) === 1 ? 'background: rgba(245,158,11,0.12); color: #d97706;' : 'background: rgba(16,185,129,0.1); color: #059669;') }}">{{ $r->response_value ?? '–' }}</div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: var(--text); font-size: 0.95rem; line-height: 1.5;">
                            <span style="color: var(--text-dim); font-weight: 800; margin-right: 0.5rem;">#{{ $r->question?->question_number ?? '–' }}</span>{{ $r->question?->question_text ?? 'Question no longer available' }}
                        </div>
                        <div style="font-size: 0.78rem; color: var(--text-dim); font-weight: 600; margin-top: 0.2rem;">{{ $scaleLabels[$r->response_value] ?? '' }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div style="text-align: center; padding: 4rem 2rem; background: var(--surface-solid); border: 2px dashed var(--border); border-radius: 28px; color: var(--text-dim); font-weight: 600;">
            No saved answers found for this check-in. The student may have taken it before answer tracking was enabled.
        </div>
    @endforelse

    <footer class="footer no-print">
        <p>© {{ date('Y') }} PSU Mental Health Portal</p>
    </footer>
</div>

<style>
@media print {
    .no-print, .sidebar { display: none !important; }
    .main-content { margin-left: 0 !important; }
}
</style>
@endsection
