@extends('layouts.app')

@section('content')
<div class="p-8 max-w-4xl mx-auto">
    <!-- Header -->
    <header class="mb-12 flex justify-between items-center no-print">
        <a href="{{ route('student.reports.index') }}" class="flex items-center gap-3 text-gray-500 hover:text-white transition-all font-black uppercase text-[10px] tracking-widest">
            <i class="ph ph-arrow-left"></i>
            Return to Vault
        </a>
        <button onclick="window.print()" class="bg-white/5 hover:bg-white/10 border border-white/10 text-white font-black px-8 py-3 rounded-2xl transition-all uppercase tracking-widest text-[9px] flex items-center gap-3">
            <i class="ph ph-printer"></i>
            Export PDF
        </button>
    </header>

    <article class="glass-card p-16 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-600/5 blur-[100px] rounded-full -mr-20 -mt-20"></div>
        
        <!-- Metadata -->
        <div class="flex flex-col md:flex-row justify-between gap-8 mb-20 pb-12 border-b border-white/5">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-3 h-3 bg-blue-500 rounded-full shadow-[0_0_15px_rgba(59,130,246,0.5)] animate-pulse"></span>
                    <p class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-500 italic">Wellness Analysis Protocol</p>
                </div>
                <h1 class="text-4xl font-black text-white italic uppercase mb-2">Ref: #WA-{{ str_pad($score->score_id, 5, '0', STR_PAD_LEFT) }}</h1>
                <p class="text-gray-600 font-bold uppercase tracking-widest text-[10px]">Issued Point: {{ $score->assessment_date->format('M d, Y') }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest mb-2 italic">Institutional Context</p>
                <div class="flex items-center gap-3 justify-end">
                    <span class="px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest bg-blue-500/10 text-blue-400 border border-blue-500/20 shadow-lg shadow-blue-500/5">
                        {{ $score->risk_level }} Risk Level
                    </span>
                </div>
            </div>
        </div>

        <!-- Metric Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-20">
            <div class="bg-white/[0.02] border border-white/5 p-12 rounded-[4rem] text-center">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-8 italic">Index Score</h3>
                <div class="relative inline-flex items-center justify-center">
                    <div class="w-32 h-32 rounded-full border-8 border-white/5"></div>
                    <span class="absolute text-7xl font-black text-blue-500 tracking-tighter">{{ $score->overall_score }}</span>
                </div>
                <p class="mt-8 text-xs font-bold text-gray-500 uppercase tracking-widest italic">Normalized wellbeing metric</p>
            </div>

            <div class="p-4 flex flex-col justify-center">
                <h3 class="text-xs font-black uppercase tracking-widest text-white mb-8 italic border-l-4 border-blue-500 pl-6">Analysis Summary</h3>
                <p class="text-lg text-gray-400 font-medium leading-relaxed italic">
                    "This sequence indicates a <span class="text-white font-bold">{{ strtolower($score->risk_level) }}</span> level of clinical distress. The metrics are derived from multidimensional behavioral tracking."
                </p>
            </div>
        </div>

        <!-- Factor Breakdown -->
        <section class="space-y-12 mb-20">
            <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-500 border-b border-white/5 pb-4">Biological Factor Breakdown</h3>
            
            @php
                $factors = [
                    ['name' => 'Depressive Spectrum', 'score' => $score->depression_score, 'c' => '#6366f1'],
                    ['name' => 'Anxiety Vectors', 'score' => $score->anxiety_score, 'c' => '#3b82f6'],
                    ['name' => 'Stress Load', 'score' => $score->stress_score, 'c' => '#0ea5e9']
                ];
            @endphp

            @foreach($factors as $f)
                <div class="space-y-4">
                    <div class="flex justify-between items-end">
                        <span class="text-xs font-black uppercase tracking-widest text-gray-400">{{ $f['name'] }}</span>
                        <span class="text-xl font-black italic" style="color: {{ $f['c'] }}">{{ $f['score'] }} <small class="text-[10px] opacity-40">/ 20</small></span>
                    </div>
                    <div class="h-4 bg-white/5 rounded-full overflow-hidden p-1 border border-white/5">
                        <div class="h-full rounded-full transition-all duration-1000" style="width: {{ ($f['score']/20)*100 }}%; background: {{ $f['c'] }}; box-shadow: 0 0 20px {{ $f['c'] }}44;"></div>
                    </div>
                </div>
            @endforeach
        </section>

        <!-- Counselor Logic -->
        @if($counselorNote)
            <section class="bg-[#1a1a1a] border border-blue-500/20 rounded-[4rem] p-12 relative">
                <div class="absolute top-0 left-12 -translate-y-1/2 bg-blue-600 px-6 py-2 rounded-full shadow-lg shadow-blue-500/20">
                    <p class="text-[9px] font-black uppercase tracking-widest text-white">Clinical Feedback Loop</p>
                </div>
                
                <div class="mb-10">
                    <p class="text-gray-300 font-medium italic leading-relaxed text-lg mb-8">
                        "{!! nl2br(e($counselorNote->note_text)) !!}"
                    </p>
                    <div class="bg-blue-600/10 border border-blue-500/10 p-8 rounded-3xl">
                        <p class="text-[9px] font-black uppercase tracking-widest text-blue-500 mb-2">Actionable Intelligence:</p>
                        <p class="text-white font-bold text-sm italic">{{ $counselorNote->recommendation }}</p>
                    </div>
                </div>

                <div class="flex justify-between items-center text-[9px] font-black uppercase tracking-widest text-gray-600 border-t border-white/5 pt-8">
                    <span>Authorized Node: Guidance Counselor</span>
                    <span>Timestamp: {{ $counselorNote->created_at->format('M d, Y') }}</span>
                </div>
            </section>
        @else
            <section class="text-center py-12 border-2 border-dashed border-white/5 rounded-[4rem]">
                <p class="text-gray-600 font-bold uppercase tracking-widest text-[10px]">Awaiting Clinical Review Protocol</p>
            </section>
        @endif
    </article>

    <footer class="mt-12 text-center no-print">
        <p class="text-gray-700 font-black uppercase text-[8px] tracking-[0.5em]">Kaneshiro Multi-Layered Security Core</p>
    </footer>
</div>

<style>
.glass-card {
    background: rgba(18, 18, 18, 0.8);
    backdrop-filter: blur(40px);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 4.5rem;
    box-shadow: 0 50px 100px -20px rgba(0,0,0,0.5);
}
@media print {
    body { background: white !important; color: black !important; }
    .glass-card { background: white !important; box-shadow: none !important; border: 1px solid #eee !important; color: black !important; }
    .no-print { display: none !important; }
    .text-gray-500, .text-gray-600 { color: #555 !important; }
    .text-white { color: black !important; }
    .bg-white\/\[0\.02\], .bg-\[\#1a1a1a\] { background: #f9f9f9 !important; }
}
</style>
@endsection
