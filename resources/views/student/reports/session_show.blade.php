@extends('layouts.app')

@section('content')
<div class="p-8 max-w-4xl mx-auto">
    <!-- Header -->
    <header class="mb-12 flex justify-between items-center no-print">
        <a href="{{ route('student.reports.index') }}" class="flex items-center gap-3 text-gray-500 hover:text-white transition-all font-black uppercase text-[10px] tracking-widest">
            <i class="ph ph-arrow-left"></i>
            Return to Vault
        </a>
        <button onclick="exportPDF()" class="bg-white/5 hover:bg-white/10 border border-white/10 text-white font-black px-8 py-3 rounded-2xl transition-all uppercase tracking-widest text-[9px] flex items-center gap-3" id="exportBtn">
            <i class="ph ph-file-pdf"></i>
            Export PDF
        </button>
    </header>

    <article class="glass-card p-16 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-600/5 blur-[100px] rounded-full -mr-20 -mt-20"></div>
        
        <!-- Metadata -->
        <div class="flex flex-col md:flex-row justify-between gap-8 mb-20 pb-12 border-b border-white/5">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-3 h-3 bg-emerald-500 rounded-full shadow-[0_0_15px_rgba(16,185,129,0.5)] animate-pulse"></span>
                    <p class="text-[10px] font-black uppercase tracking-[0.4em] text-emerald-500 italic">AI Session Summary Protocol</p>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tighter mb-2">{{ $session->created_at->format('F d, Y') }}</h1>
                <p class="text-emerald-500 font-black uppercase tracking-[0.4em] text-[10px] italic">Session Logged at {{ $session->created_at->format('h:i A') }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest mb-2 italic">Clinical Risk Status</p>
                <div class="flex items-center gap-3 justify-end">
                    <span class="px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest {{ 
                        ($session->ai_report['risk_level'] ?? 'Low') === 'High' || ($session->ai_report['risk_level'] ?? 'Low') === 'Critical' 
                        ? 'bg-red-500/10 text-red-400 border-red-500/20' 
                        : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' 
                    }} border shadow-lg shadow-emerald-500/5">
                        {{ $session->ai_report['risk_level'] ?? 'Low' }} Risk Level
                    </span>
                </div>
            </div>
        </div>

        <!-- Metric Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-8 mb-20">
            <div class="bg-white/[0.02] border border-white/5 p-8 rounded-3xl text-center">
                <p class="text-[8px] font-black text-emerald-500 uppercase tracking-widest mb-4">Current Mood</p>
                <p class="text-2xl font-black text-white italic uppercase">{{ $session->ai_report['mood'] ?? 'Stable' }}</p>
            </div>
            <div class="bg-white/[0.02] border border-white/5 p-8 rounded-3xl text-center">
                <p class="text-[8px] font-black text-emerald-500 uppercase tracking-widest mb-4">Sleep Quality</p>
                <p class="text-2xl font-black text-white italic uppercase">{{ $session->ai_report['sleep'] ?? 'N/A' }}</p>
            </div>
            <div class="bg-white/[0.02] border border-white/5 p-8 rounded-3xl text-center">
                <p class="text-[8px] font-black text-emerald-500 uppercase tracking-widest mb-4">Energy Level</p>
                <p class="text-2xl font-black text-white italic uppercase">{{ $session->ai_report['energy'] ?? 'N/A' }}</p>
            </div>
            <div class="bg-white/[0.02] border border-white/5 p-8 rounded-3xl text-center">
                <p class="text-[8px] font-black text-emerald-500 uppercase tracking-widest mb-4">Focus</p>
                <p class="text-2xl font-black text-white italic uppercase">{{ $session->ai_report['focus'] ?? 'N/A' }}</p>
            </div>
            <div class="bg-white/[0.02] border border-white/5 p-8 rounded-3xl text-center">
                <p class="text-[8px] font-black text-emerald-500 uppercase tracking-widest mb-4">Social</p>
                <p class="text-2xl font-black text-white italic uppercase">{{ $session->ai_report['social'] ?? 'N/A' }}</p>
            </div>
            <div class="bg-white/[0.02] border border-white/5 p-8 rounded-3xl text-center">
                <p class="text-[8px] font-black text-emerald-500 uppercase tracking-widest mb-4">Appetite</p>
                <p class="text-2xl font-black text-white italic uppercase">{{ $session->ai_report['appetite'] ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- AI Observations -->
        <section class="space-y-12 mb-20">
            <div>
                <h3 class="text-xs font-black uppercase tracking-widest text-white mb-6 italic border-l-4 border-emerald-500 pl-6">Core Concerns Identified</h3>
                <div class="bg-white/[0.02] border border-white/5 p-10 rounded-[3rem]">
                    <p class="text-lg text-gray-300 font-medium leading-relaxed italic">
                        "{!! $session->ai_report['core_concerns'] ?? 'No concerns identified.' !!}"
                    </p>
                </div>
            </div>

            <div>
                <h3 class="text-xs font-black uppercase tracking-widest text-white mb-6 italic border-l-4 border-emerald-500 pl-6">Clinical Observations</h3>
                <div class="bg-white/[0.02] border border-white/5 p-10 rounded-[3rem]">
                    <p class="text-lg text-gray-300 font-medium leading-relaxed italic">
                        "{!! $session->ai_report['clinical_observations'] ?? 'No observations available.' !!}"
                    </p>
                </div>
            </div>
        </section>

        <!-- System Footer -->
        <div class="flex justify-between items-center text-[9px] font-black uppercase tracking-widest text-gray-600 border-t border-white/5 pt-8">
            <span>Clinical Assistant Protocol</span>
            <span>Follow-up Recommended: {{ ($session->ai_report['follow_up_needed'] ?? false) ? 'YES' : 'NO' }}</span>
        </div>
    </article>

    <footer class="mt-12 text-center no-print">
        <p class="text-gray-700 font-black uppercase text-[8px] tracking-[0.5em]">PSU-SCC Mental Health Evaluation System</p>
    </footer>
</div>

<style>
.glass-card {
    background: rgba(18, 18, 18, 0.9);
    backdrop-filter: blur(40px);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 4.5rem;
    box-shadow: 0 50px 100px -20px rgba(0,0,0,0.5);
    transition: none !important;
    transform: none !important;
}
.glass-card:hover {
    transform: none !important;
    background: rgba(18, 18, 18, 0.9) !important;
    box-shadow: 0 50px 100px -20px rgba(0,0,0,0.5) !important;
}
@media print {
    body { background: white !important; color: black !important; }
    .glass-card { background: white !important; box-shadow: none !important; border: 1px solid #eee !important; color: black !important; }
    .no-print { display: none !important; }
    .text-gray-500, .text-gray-600 { color: #555 !important; }
    .text-white { color: black !important; }
    .bg-white\/\[0\.02\] { background: #f9f9f9 !important; }
}
</style>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
async function exportPDF() {
    const btn = document.getElementById('exportBtn');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="ph ph-circle-notch"></i> Generating...';
    btn.disabled = true;
    document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
    try {
        const { jsPDF } = window.jspdf;
        const element = document.querySelector('article.glass-card');
        const canvas  = await html2canvas(element, {
            scale: 2, useCORS: true, backgroundColor: '#ffffff',
            windowWidth: element.scrollWidth, windowHeight: element.scrollHeight
        });
        const imgData = canvas.toDataURL('image/jpeg', 0.92);
        const pdf = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });
        const pageW = pdf.internal.pageSize.getWidth();
        const pageH = pdf.internal.pageSize.getHeight();
        const imgH  = (canvas.height * pageW) / canvas.width;
        let remaining = imgH, yOffset = 0;
        while (remaining > 0) {
            pdf.addImage(imgData, 'JPEG', 0, -yOffset, pageW, imgH);
            remaining -= pageH; yOffset += pageH;
            if (remaining > 0) pdf.addPage();
        }
        pdf.save('PSU-AI-Session-Report.pdf');
    } catch (err) {
        console.error(err);
        alert('PDF generation failed. Please try again.');
    } finally {
        document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
        btn.innerHTML = originalHTML;
        btn.disabled  = false;
    }
}
</script>
@endpush
