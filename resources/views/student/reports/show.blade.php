@extends('layouts.app')

@section('content')
<div class="p-8 max-w-4xl mx-auto">
    <!-- Header -->
    <header class="mb-12 flex justify-between items-center no-print">
        <a href="{{ route('student.reports.index') }}" class="back-link flex items-center gap-3 font-black uppercase text-[10px] tracking-widest">
            <i class="ph ph-arrow-left"></i>
            Return to Vault
        </a>
        <div class="flex items-center gap-3">
            <button onclick="exportPDF()" class="export-btn font-black px-8 py-3 rounded-2xl uppercase tracking-widest text-[9px] flex items-center gap-3">
                <i class="ph ph-file-pdf"></i>
                Export PDF
            </button>
            @if(in_array(strtolower($score->risk_level), ['high', 'critical']))
            <button id="callCounselorBtn" onclick="requestEmergencyCall()" class="call-btn font-black px-8 py-3 rounded-2xl uppercase tracking-widest text-[9px] flex items-center gap-3">
                <i class="ph ph-phone-call"></i>
                Call Counselor
            </button>
            @endif
        </div>
    </header>

    <article class="glass-card p-12 md:p-16">

        <!-- Metadata -->
        <div class="flex flex-col md:flex-row justify-between gap-8 mb-16 pb-12 divider-line">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                    <p class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-600">Assessment Result</p>
                </div>
                <p class="label-text font-bold uppercase tracking-widest text-[10px]">
                    Date: {{ $score->assessment_date->format('M d, Y h:i A') }}
                </p>
            </div>
            <div class="text-right">
                <p class="label-text text-xs font-black uppercase tracking-widest mb-3">Risk Status</p>
                @php
                    $riskLvl = strtolower($score->risk_level);
                    $badgeStyle = match(true) {
                        in_array($riskLvl, ['critical','high']) => 'background:#fee2e2;border-color:#f87171;color:#7f1d1d;',
                        $riskLvl === 'moderate' => 'background:#fef3c7;border-color:#fbbf24;color:#78350f;',
                        default => 'background:#d1fae5;border-color:#34d399;color:#064e3b;',
                    };
                @endphp
                <span class="risk-badge px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest border" style="{{ $badgeStyle }}">
                    {{ $score->risk_level }} Risk
                </span>
            </div>
        </div>

        <!-- Metric Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-16">
            <div class="metric-box p-10 rounded-[3rem] text-center">
                <h3 class="label-text text-[10px] font-black uppercase tracking-widest mb-8">Overall Distress Score</h3>
                <div class="relative inline-flex items-center justify-center">
                    <div class="w-32 h-32 rounded-full border-8 ring-border"></div>
                    <span class="absolute text-6xl font-black text-blue-700 tracking-tighter">{{ $score->overall_score }}</span>
                </div>
                <p class="mt-6 text-xs font-bold label-text uppercase tracking-widest">Out of 100 — Higher = More Distress</p>
            </div>

            <div class="flex flex-col justify-center">
                <h3 class="section-title text-xs font-black uppercase tracking-widest mb-6 border-l-4 border-blue-600 pl-5">What This Means</h3>
                <p class="body-text text-base font-semibold leading-relaxed">
                    Your answers indicate a
                    <strong class="text-blue-700">{{ strtolower($score->risk_level) }}</strong>
                    level of psychological distress. This is based on your responses to the wellness questionnaire.
                </p>
            </div>
        </div>

        <!-- Factor Breakdown -->
        <section class="space-y-10 mb-16">
            <h3 class="label-text text-[10px] font-black uppercase tracking-[0.3em] border-b-2 pb-4 divider-line">Score Breakdown by Category</h3>

            @php
                $factors = [
                    ['name' => 'Depression', 'score' => $score->depression_score, 'c' => '#6366f1', 'max' => 21],
                    ['name' => 'Anxiety',    'score' => $score->anxiety_score,    'c' => '#3b82f6', 'max' => 21],
                    ['name' => 'Stress',     'score' => $score->stress_score,     'c' => '#0ea5e9', 'max' => 21],
                ];
            @endphp

            @foreach($factors as $f)
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="section-title text-sm font-black uppercase tracking-widest">{{ $f['name'] }}</span>
                        <span class="text-xl font-black" style="color: {{ $f['c'] }}">{{ $f['score'] }} <span class="text-xs label-text font-semibold">/ {{ $f['max'] }}</span></span>
                    </div>
                    <div class="h-4 bar-track rounded-full overflow-hidden border bar-border">
                        <div class="h-full rounded-full" style="width: {{ ($f['score']/$f['max'])*100 }}%; background: {{ $f['c'] }};"></div>
                    </div>
                </div>
            @endforeach
        </section>

        <!-- Computation Explanation -->
        <section class="mb-16 p-8 info-box rounded-[2.5rem]">
            <h3 class="section-title text-xs font-black uppercase tracking-widest mb-4 border-l-4 border-blue-600 pl-5">How Your Score is Calculated</h3>
            <p class="body-text text-sm font-medium leading-relaxed">
                Your scores use the <strong class="section-title">DASS-21</strong> (Depression, Anxiety, and Stress Scales) standard. Each answer you gave (0–3) is totaled per category.
                <br><br>
                The <strong class="section-title">Overall Distress Score</strong> is the average of all three.
                A <strong style="color:#b91c1c">higher score = more distress</strong>.
                A <strong style="color:#047857">lower score = better wellness</strong>.
            </p>
        </section>

        <!-- Counselor Feedback -->
        @if($counselorNote)
            <section class="counselor-box border-2 rounded-[3rem] p-12 relative">
                <div class="absolute top-0 left-12 -translate-y-1/2 bg-blue-600 px-6 py-2 rounded-full">
                    <p class="text-[9px] font-black uppercase tracking-widest text-white">Counselor's Feedback</p>
                </div>
                <div class="mb-8">
                    <p class="body-text font-medium italic leading-relaxed text-base mb-6">
                        "{!! nl2br(e($counselorNote->note_text)) !!}"
                    </p>
                    <div class="rec-box border p-6 rounded-2xl">
                        <p class="text-[9px] font-black uppercase tracking-widest text-blue-700 mb-2">Recommendation:</p>
                        <p class="section-title font-bold text-sm">{{ $counselorNote->recommendation }}</p>
                    </div>
                </div>
                <div class="flex justify-between items-center text-[9px] font-black uppercase tracking-widest label-text border-t pt-6 divider-line">
                    <span>Verified by: Guidance Counselor</span>
                    <span>Review Date: {{ $counselorNote->created_at->format('M d, Y') }}</span>
                </div>
            </section>
        @else
            <section class="text-center py-10 border-2 border-dashed border-slate-300 rounded-[3rem]">
                <p class="label-text font-bold uppercase tracking-widest text-[10px]">Waiting for Counselor's Review</p>
            </section>
        @endif
    </article>

    <footer class="mt-12 text-center no-print">
        <p class="label-text font-black uppercase text-[8px] tracking-[0.5em]">PSU Mental Health Portal</p>
    </footer>
</div>
@push('scripts')
<script>
async function requestEmergencyCall() {
    if (typeof window.gStartCallStudent === 'function') {
        window.gStartCallStudent();
    } else {
        // Fallback if navigation script is not loaded
        window.location.href = "{{ route('student.dashboard') }}";
    }
}
</script>
@endpush
@endsection
