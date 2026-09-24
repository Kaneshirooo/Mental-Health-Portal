@extends('layouts.app')

@section('content')
<div class="p-8 max-w-6xl mx-auto">

    <!-- Header -->
    <header class="mb-12 lg:flex justify-between items-end">
        <div>
            <h1 class="vault-title text-6xl font-black tracking-tighter italic uppercase mb-3">Progress Vault</h1>
            <p class="vault-subtitle font-medium">Your assessment history and AI session summaries.</p>
        </div>
        <div class="mt-8 lg:mt-0">
            <a href="{{ route('student.assessment') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-black px-10 py-5 rounded-[2rem] shadow-xl transition-all uppercase tracking-widest text-xs inline-flex items-center gap-3">
                New Assessment
                <i class="ph ph-arrow-right"></i>
            </a>
        </div>
    </header>

    <!-- Session Summaries -->
    <div id="session-summaries-section" class="mb-16">
        <header class="mb-10">
            <h2 class="vault-title text-4xl font-black italic uppercase mb-2 tracking-tighter">Session Summaries</h2>
            <p class="vault-subtitle font-medium">Observations from your AI chat sessions.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($sessions as $session)
                @php
                    $sr = strtolower($session->ai_report['risk_level'] ?? 'low');
                    $sbadge = match(true) {
                        in_array($sr, ['critical','high']) => 'background:#fee2e2;border-color:#f87171;color:#7f1d1d;',
                        $sr === 'moderate'                 => 'background:#fef3c7;border-color:#fbbf24;color:#78350f;',
                        default                            => 'background:#d1fae5;border-color:#34d399;color:#064e3b;',
                    };
                @endphp
                <div class="vault-card p-8 flex flex-col h-full">
                    <!-- Date & Badge -->
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="card-title text-2xl font-black mb-1">{{ $session->created_at->format('M d, Y') }}</h3>
                            <p class="text-[11px] font-bold uppercase tracking-widest" style="color:#059669">{{ $session->created_at->format('h:i A') }}</p>
                        </div>
                        <span class="px-4 py-2 rounded-full text-[9px] font-black uppercase tracking-widest border" style="{{ $sbadge }}">
                            {{ $session->ai_report['risk_level'] ?? 'Low' }} Risk
                        </span>
                    </div>

                    <!-- Observation Preview -->
                    <div class="mb-6">
                        <p class="card-body text-sm italic line-clamp-3 leading-relaxed font-medium">
                            "{!! \Illuminate\Support\Str::limit($session->ai_report['clinical_observations'] ?? 'No observations recorded.', 150) !!}"
                        </p>
                    </div>

                    <!-- Mood / Sleep -->
                    <div class="grid grid-cols-2 gap-3 mb-8">
                        <div class="stat-box rounded-2xl p-4 text-center">
                            <p class="text-[9px] font-black uppercase tracking-widest mb-1" style="color:#059669">Mood</p>
                            <p class="card-title font-bold text-sm">{{ ucfirst($session->ai_report['mood'] ?? 'Stable') }}</p>
                        </div>
                        <div class="stat-box rounded-2xl p-4 text-center">
                            <p class="text-[9px] font-black uppercase tracking-widest mb-1" style="color:#059669">Sleep</p>
                            <p class="card-title font-bold text-sm">{{ $session->ai_report['sleep'] ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="mt-auto">
                        <a href="{{ route('student.reports.session.show', $session->pre_id) }}"
                           class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 rounded-2xl transition-all uppercase tracking-widest text-[9px]">
                            View Summary
                            <i class="ph ph-sparkle"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="lg:col-span-3 py-20 text-center vault-card">
                    <p class="card-label font-medium italic">No session summaries yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if($reports->count() > 1)
    <!-- Distress Over Time Chart -->
    <div class="vault-card p-10 mb-12">
        <h2 class="section-label text-xs font-black uppercase tracking-[0.3em] mb-4 flex items-center gap-3">
            <i class="ph ph-chart-line text-blue-600 text-lg"></i>
            Distress Score Over Time
        </h2>
        <p class="vault-subtitle text-sm mb-8 leading-relaxed max-w-3xl">
            This visualization tracks your emotional well-being by plotting overall distress scores from your assessment history. The score is calculated as the average of your results across three key clinical dimensions: Depression (PHQ-9), Anxiety (GAD-7), and Stress (DASS-21). A higher score indicates a higher level of psychological distress, helping you identify patterns and monitor your progression over time.
        </p>
        <div class="h-[280px] w-full">
            <canvas id="historyChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Assessment Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
        @forelse($reports as $report)
            @php
                $rl = strtolower($report->risk_level);
                $badge = match(true) {
                    in_array($rl, ['critical','high']) => 'background:#fee2e2;border-color:#f87171;color:#7f1d1d;',
                    $rl === 'moderate'                 => 'background:#fef3c7;border-color:#fbbf24;color:#78350f;',
                    default                            => 'background:#d1fae5;border-color:#34d399;color:#064e3b;',
                };
            @endphp
            <div class="vault-card p-8 flex flex-col h-full">
                <!-- Date & Badge -->
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h3 class="card-title text-2xl font-black mb-1">{{ $report->assessment_date->format('M d, Y') }}</h3>
                        <p class="card-time text-[11px] font-bold uppercase tracking-widest">{{ $report->assessment_date->format('h:i A') }}</p>
                    </div>
                    <span class="px-4 py-2 rounded-full text-[9px] font-black uppercase tracking-widest border" style="{{ $badge }}">
                        {{ $report->risk_level }} Risk
                    </span>
                </div>

                <!-- Overall Score -->
                <div class="mb-8 flex items-end gap-2 px-1">
                    <span class="text-6xl font-black text-blue-700 leading-none tracking-tighter">{{ $report->overall_score }}</span>
                    <span class="card-label text-xs font-bold uppercase mb-1 tracking-widest">/ 100 pts</span>
                </div>

                <!-- D/A/S Breakdown -->
                <div class="stat-box grid grid-cols-3 gap-0 rounded-2xl p-5 mb-8">
                    <div class="text-center">
                        <p class="card-title text-base font-black">{{ $report->depression_score }}</p>
                        <p class="card-label text-[9px] font-black uppercase tracking-widest">Depression</p>
                    </div>
                    <div class="text-center stat-divider">
                        <p class="card-title text-base font-black">{{ $report->anxiety_score }}</p>
                        <p class="card-label text-[9px] font-black uppercase tracking-widest">Anxiety</p>
                    </div>
                    <div class="text-center">
                        <p class="card-title text-base font-black">{{ $report->stress_score }}</p>
                        <p class="card-label text-[9px] font-black uppercase tracking-widest">Stress</p>
                    </div>
                </div>

                <!-- Action -->
                <div class="mt-auto">
                    <a href="{{ route('student.reports.show', $report) }}"
                       class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-2xl transition-all uppercase tracking-widest text-[9px]">
                        View Details
                        <i class="ph ph-arrow-right"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="lg:col-span-3 py-32 text-center vault-card">
                <i class="ph ph-file-dashed text-5xl card-label mb-6 block"></i>
                <h3 class="card-title text-xl font-bold">No Assessments Yet</h3>
                <p class="card-label font-medium mt-2">Take your first assessment to get started.</p>
            </div>
        @endforelse
    </div>
</div>

<style>
/* ===========================
   LIGHT MODE (default)
   =========================== */
.vault-title    { color: #0f172a; }
.vault-subtitle { color: #64748b; }
.section-label  { color: #475569; }

.vault-card {
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 2.5rem;
    box-shadow: 0 2px 16px rgba(0,0,0,0.07);
}

.card-title  { color: #0f172a; }
.card-time   { color: #475569; }
.card-label  { color: #64748b; }
.card-body   { color: #334155; }

.stat-box    { background: #f8fafc; border: 1px solid #e2e8f0; }
.stat-divider { border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; }

/* ===========================
   DARK MODE
   =========================== */
.dark-mode .vault-title    { color: #f1f5f9; }
.dark-mode .vault-subtitle { color: #94a3b8; }
.dark-mode .section-label  { color: #94a3b8; }

.dark-mode .vault-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    backdrop-filter: blur(20px);
}

.dark-mode .card-title  { color: #f1f5f9; }
.dark-mode .card-time   { color: #94a3b8; }
.dark-mode .card-label  { color: #64748b; }
.dark-mode .card-body   { color: #94a3b8; }

.dark-mode .stat-box     { background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.06); }
.dark-mode .stat-divider { border-color: rgba(255,255,255,0.06); }

/* Risk badges get inverted tints in dark mode */
.dark-mode span[style*="fee2e2"] { background: rgba(239,68,68,0.12) !important; border-color: rgba(239,68,68,0.3) !important; color: #fca5a5 !important; }
.dark-mode span[style*="fef3c7"] { background: rgba(245,158,11,0.12) !important; border-color: rgba(245,158,11,0.3) !important; color: #fcd34d !important; }
.dark-mode span[style*="d1fae5"] { background: rgba(16,185,129,0.12) !important; border-color: rgba(16,185,129,0.3) !important; color: #6ee7b7 !important; }
</style>

@if($reports->count() > 1)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('historyChart').getContext('2d');
    const isDark = document.documentElement.classList.contains('dark-mode') || document.body.classList.contains('dark-mode');
    const tickColor = isDark ? '#94a3b8' : '#475569';

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($reports->reverse()->pluck('assessment_date')->map(fn($d) => $d->format('M d, Y'))->values()) !!},
            datasets: [{
                label: 'Distress Level',
                data: {!! json_encode($reports->reverse()->pluck('overall_score')->values()) !!},
                borderColor: '#3b82f6',
                borderWidth: 4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#3b82f6',
                pointBorderWidth: 3,
                pointRadius: 7,
                tension: 0.4,
                fill: true,
                backgroundColor: (context) => {
                    const gradient = ctx.createLinearGradient(0, 0, 0, 280);
                    gradient.addColorStop(0, 'rgba(59,130,246,0.15)');
                    gradient.addColorStop(1, 'rgba(59,130,246,0)');
                    return gradient;
                }
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => 'Score: ' + c.parsed.y + ' / 100' } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: tickColor, font: { weight: '700' } } },
                y: { min: 0, max: 100, grid: { color: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)' }, ticks: { color: tickColor, font: { weight: '700' } } }
            }
        }
    });
</script>
@endif
@endsection
