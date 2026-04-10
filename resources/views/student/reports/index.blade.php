@extends('layouts.app')

@section('content')
<div class="p-8 max-w-6xl mx-auto">
    <!-- Header -->
    <header class="mb-12 lg:flex justify-between items-end">
        <div>
            <h1 class="text-6xl font-black text-white tracking-tighter italic uppercase mb-4">Progress Vault</h1>
            <p class="text-gray-500 font-medium">Historical wellness analytics and clinical report registry.</p>
        </div>
        <div class="mt-8 lg:mt-0">
            <a href="{{ route('student.assessment') }}" class="bg-blue-600 hover:bg-blue-500 text-white font-black px-10 py-5 rounded-[2rem] shadow-xl shadow-blue-600/20 transition-all active:scale-95 uppercase tracking-widest text-xs inline-flex items-center gap-3">
                New Assessment Protocol
                <i class="ph ph-arrow-right"></i>
            </a>
        </div>
    </header>

    @if($reports->count() > 1)
        <!-- Dynamic Progression Chart -->
        <div class="glass-card p-12 mb-12">
            <h2 class="text-xs font-black uppercase tracking-[0.3em] text-gray-500 mb-10 flex items-center gap-4">
                <i class="ph ph-chart-line text-blue-500 text-xl"></i>
                Score Delta Progression
            </h2>
            <div class="h-[300px] w-full">
                <canvas id="historyChart"></canvas>
            </div>
        </div>
    @endif

    <!-- Registry Feed -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($reports as $report)
            <div class="glass-card p-10 group hover:border-blue-500/30 transition-all flex flex-col h-full">
                <div class="flex justify-between items-start mb-10">
                    <div>
                        <h3 class="text-2xl font-black text-white mb-1">{{ $report->assessment_date->format('M d, Y') }}</h3>
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-600 italic">Report #WA-{{ str_pad($report->score_id, 4, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <span class="px-5 py-2 rounded-full text-[9px] font-black uppercase tracking-widest bg-white/5 border border-white/10 text-gray-400">
                        {{ $report->risk_level }} Risk
                    </span>
                </div>

                <div class="mb-12 flex items-end gap-3 px-2">
                    <span class="text-6xl font-black text-blue-500 leading-none tracking-tighter">{{ $report->overall_score }}</span>
                    <span class="text-xs font-bold text-gray-700 uppercase mb-2 tracking-widest">/ 100 PTS</span>
                </div>

                <div class="grid grid-cols-3 gap-1 bg-white/[0.02] border border-white/5 rounded-3xl p-6 mb-10">
                    <div class="text-center">
                        <p class="text-lg font-black text-white">{{ $report->depression_score }}</p>
                        <p class="text-[8px] font-black text-gray-600 uppercase tracking-widest">Depr</p>
                    </div>
                    <div class="text-center border-x border-white/5">
                        <p class="text-lg font-black text-white">{{ $report->anxiety_score }}</p>
                        <p class="text-[8px] font-black text-gray-600 uppercase tracking-widest">Anx</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-black text-white">{{ $report->stress_score }}</p>
                        <p class="text-[8px] font-black text-gray-600 uppercase tracking-widest">Stress</p>
                    </div>
                </div>

                <div class="mt-auto">
                    <a href="{{ route('student.reports.show', $report) }}" class="w-full inline-flex items-center justify-center gap-3 bg-white/5 hover:bg-white/10 border border-white/10 text-white font-black py-5 rounded-2xl transition-all uppercase tracking-widest text-[9px]">
                        Inspect Protocol
                        <i class="ph ph-magnifying-glass"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="lg:col-span-3 py-40 text-center bg-white/5 border border-dashed border-white/10 rounded-[4rem]">
                <div class="w-24 h-24 bg-white/5 rounded-full mx-auto mb-8 flex items-center justify-center border border-white/5">
                    <i class="ph ph-file-dashed text-gray-700 text-5xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-500 tracking-tight">Empty Database</h3>
                <p class="text-gray-600 font-medium">No assessment sequences recorded for this identity.</p>
            </div>
        @endforelse
    </div>
</div>

<style>
.glass-card {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 3.5rem;
}
</style>

@if($reports->count() > 1)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('historyChart').getContext('2d');
    const chartData = {
        labels: {!! json_encode($reports->reverse()->pluck('assessment_date')->map(fn($d) => $d->format('M d'))->values()) !!},
        datasets: [{
            label: 'Wellness Vector',
            data: {!! json_encode($reports->reverse()->pluck('overall_score')->values()) !!},
            borderColor: '#3b82f6',
            borderWidth: 6,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#3b82f6',
            pointBorderWidth: 4,
            pointRadius: 8,
            pointHoverRadius: 12,
            tension: 0.4,
            fill: true,
            backgroundColor: (context) => {
                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(59, 130, 246, 0.1)');
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');
                return gradient;
            }
        }]
    };

    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#666', font: { weight: '900' } } },
                y: { min: 0, max: 100, grid: { color: 'rgba(255,255,255,0.03)' }, ticks: { color: '#666', font: { weight: '900' } } }
            }
        }
    });
</script>
@endif
@endsection
