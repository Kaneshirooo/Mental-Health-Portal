@extends('layouts.app')

@push('styles')
<style>
    @media print {
        .sidebar, .action-buttons, .no-print, footer { display:none!important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; }
        body { background:#fff; }
    }
    .score-fill {
        background: var(--primary);
        height: 100%;
        transition: width 1.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 800px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <div style="text-align: center; margin-bottom: 2.5rem;">
        <div style="font-size: 2.5rem; margin-bottom: 1rem;">✨</div>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Insight Generated</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">Your clinical reflection has been analyzed. Here are the personalized findings.</p>
    </div>

    <div style="background: var(--surface-solid); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm); padding: 2rem; position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: {{ $risk_colors[$score->risk_level] ?? '#3b82f6' }};"></div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center; margin-bottom: 3rem;">
            <div style="text-align: center;">
                <div style="width: 140px; height: 140px; border-radius: 50%; border: 8px solid #f1f5f9; display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 0 auto; position: relative;">
                    <div style="font-size: 2.25rem; font-weight: 700; color: {{ $risk_colors[$score->risk_level] ?? '#3b82f6' }}; line-height: 1;">{{ $display_score }}</div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-top: 0.15rem;">/ 100</div>
                    {{-- Rough representation of circular progress --}}
                    <div style="position: absolute; inset: -8px; border-radius: 50%; border: 8px solid {{ $risk_colors[$score->risk_level] ?? '#3b82f6' }}; clip-path: inset(0 0 {{ 100 - $display_score }}% 0);"></div>
                </div>
                <div style="font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 1.5rem; font-size: 0.7rem;">Wellness Index</div>
            </div>

            <div>
                <div style="display: inline-block; padding: 0.35rem 0.85rem; border-radius: 6px; font-weight: 700; font-size: 0.72rem; text-transform: uppercase; margin-bottom: 1rem; 
                    background: {{ ($risk_colors[$score->risk_level] ?? '#3b82f6') }}15; color: {{ $risk_colors[$score->risk_level] ?? '#3b82f6' }};">
                    {{ $score->risk_level }} Risk Profile
                </div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; color: var(--text); margin-bottom: 0.75rem;">Analysis Complete</h2>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; font-weight: 400;">{{ $recommendations[$score->risk_level] ?? '' }}</p>
                
                <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;" class="no-print">
                    <button onclick="window.print()" class="btn-sm btn-secondary" style="padding: 0.5rem 1rem; background: var(--surface-2); border: 1px solid var(--border); border-radius: 8px; font-weight: 600; cursor: pointer;">Export PDF</button>
                    <a href="{{ route('student.reports.index') }}" class="btn-sm btn-secondary" style="padding: 0.5rem 1rem; text-decoration: none; background: var(--surface-2); border: 1px solid var(--border); border-radius: 8px; font-weight: 600; color: inherit;">View History</a>
                </div>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border); padding-top: 2rem;">
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700; margin-bottom: 1.5rem;">Dimension Breakdown</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">
                @php
                    $dims = [
                        ['label' => 'Depression', 'score' => $score->depression_score, 'info' => $dep_info],
                        ['label' => 'Anxiety', 'score' => $score->anxiety_score, 'info' => $anx_info],
                        ['label' => 'Stress', 'score' => $score->stress_score, 'info' => $str_info],
                    ];
                @endphp
                @foreach($dims as $dim)
                    @php $pct = ($dim['score'] / 20) * 100; @endphp
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1rem;">
                            <div>
                                <div style="font-weight: 800; font-size: 0.85rem; color: var(--text); margin-bottom: 0.25rem;">{{ $dim['label'] }}</div>
                                <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">{{ $dim['info']['label'] }}</div>
                            </div>
                            <div style="font-weight: 800; color: var(--text);">{{ $dim['score'] }}<span style="font-size: 0.7rem; color: var(--text-dim); margin-left: 0.2rem;">/20</span></div>
                        </div>
                        <div style="height: 8px; background: var(--surface-2); border-radius: 10px; overflow: hidden; border: 1px solid var(--border);">
                            <div class="score-fill" data-width="{{ $pct }}" style="width: 0%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div style="margin-top: 3rem; padding: 1.5rem; background: var(--surface-2); border-radius: var(--radius); display: flex; align-items: center; justify-content: space-between;" class="no-print">
            <div style="max-width: 400px;">
                <h4 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700; color: var(--primary); margin-bottom: 0.25rem;">Seek Professional Support</h4>
                <p style="color: var(--text-muted); font-weight: 400; font-size: 0.88rem;">Speak 1-on-1 with clinical experts for a deeper analysis.</p>
            </div>
            <a href="{{ route('student.appointments') }}" class="btn-primary" style="padding: 0.65rem 1.5rem; border-radius: var(--radius-sm); font-size: 0.85rem; text-decoration: none; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);">Book Consultation →</a>
        </div>
    </div>

    <div style="margin-top: 2rem; text-align: center;" class="no-print">
        <a href="{{ route('student.dashboard') }}" style="font-weight: 600; color: var(--text-muted); text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-size: 0.85rem;">
            <span>🏠</span> Return to Dashboard
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.addEventListener('load', function() {
    const fills = document.querySelectorAll('.score-fill[data-width]');
    setTimeout(() => {
        fills.forEach(fill => {
            const w = fill.getAttribute('data-width');
            fill.style.width = w + '%';
        });
    }, 300);
});
</script>
@endpush
