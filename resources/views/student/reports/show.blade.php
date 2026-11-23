@extends('layouts.app')

@push('styles')
<style>
    @media (max-width: 768px) {
        .report-page {
            padding: 0 0 3rem !important;
        }
        .report-actions {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.85rem !important;
            margin-bottom: 1.5rem !important;
        }
        .report-action-buttons {
            width: 100% !important;
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 0.5rem !important;
        }
        .report-action-buttons > a,
        .report-action-buttons > button {
            width: 100% !important;
            justify-content: center !important;
            padding: 0.8rem 0.75rem !important;
            min-height: 44px !important;
        }
        .report-card {
            padding: 1.5rem 1.15rem !important;
            border-radius: 24px !important;
        }
        .report-meta {
            flex-direction: column !important;
            gap: 1rem !important;
            margin-bottom: 1.75rem !important;
            padding-bottom: 1.25rem !important;
        }
        .report-meta > div:last-child {
            text-align: left !important;
        }
        .report-metrics {
            gap: 1rem !important;
            margin-bottom: 2rem !important;
        }
        .report-tile {
            padding: 1.25rem 1rem !important;
            border-radius: 20px !important;
        }
        .report-card section {
            margin-bottom: 1.75rem !important;
        }
        .report-card h1 {
            font-size: 2rem !important;
        }
        .report-card .text-6xl {
            font-size: 3rem !important;
        }
        .report-counselor-meta {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 0.4rem !important;
        }
    }
    body.pdf-export {
        background: #fff !important;
        color: #0f172a !important;
    }
    body.pdf-export .sidebar,
    body.pdf-export .no-print,
    body.pdf-export header.report-actions {
        display: none !important;
    }
    body.pdf-export .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    body.pdf-export .report-page {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    body.pdf-export .report-card {
        background: #fff !important;
        box-shadow: none !important;
        border: 1px solid #cbd5e1 !important;
        padding: 2rem !important;
        border-radius: 16px !important;
        color: #0f172a !important;
    }
    body.pdf-export .report-tile {
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        html, body {
            background: #fff !important;
            color: #0f172a !important;
        }
        .sidebar, .no-print, header.report-actions {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .report-page {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .report-card {
            background: #fff !important;
            box-shadow: none !important;
            border: 1px solid #cbd5e1 !important;
            padding: 2rem !important;
            border-radius: 16px !important;
            color: #0f172a !important;
        }
        .report-tile {
            background: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="report-page p-8 max-w-4xl mx-auto">
    <!-- Header -->
    <header class="report-actions mb-12 flex justify-between items-center no-print">
        <a href="{{ route('student.reports.index') }}" class="back-link flex items-center gap-3 font-black uppercase text-[10px] tracking-widest">
            <i class="ph ph-arrow-left"></i>
            Back to My Progress
        </a>
        <div class="report-action-buttons flex items-center gap-3">
            @isset($prevScore)
                @if($prevScore)
                    <a href="{{ route('student.reports.show', $prevScore) }}" class="export-btn font-black px-6 py-3 rounded-2xl uppercase tracking-widest text-[9px] flex items-center gap-2 no-print">
                        <i class="ph ph-arrow-left"></i>
                        Prev
                    </a>
                @endif
            @endisset
            <button onclick="window.print()" class="export-btn font-black px-8 py-3 rounded-2xl uppercase tracking-widest text-[9px] flex items-center gap-3 no-print">
                <i class="ph ph-printer"></i>
                Print
            </button>
            <button id="exportBtn" onclick="exportPDF()" class="export-btn font-black px-8 py-3 rounded-2xl uppercase tracking-widest text-[9px] flex items-center gap-3 no-print">
                <i class="ph ph-file-pdf"></i>
                Export PDF
            </button>
            @isset($nextScore)
                @if($nextScore)
                    <a href="{{ route('student.reports.show', $nextScore) }}" class="export-btn font-black px-6 py-3 rounded-2xl uppercase tracking-widest text-[9px] flex items-center gap-2 no-print">
                        Next
                        <i class="ph ph-arrow-right"></i>
                    </a>
                @endif
            @endisset
            @if(in_array(strtolower($score->risk_level), ['high', 'critical']))
            <button id="callCounselorBtn" onclick="requestEmergencyCall()" class="call-btn font-black px-8 py-3 rounded-2xl uppercase tracking-widest text-[9px] flex items-center gap-3">
                <i class="ph ph-phone-call"></i>
                Call Counselor
            </button>
            @endif
        </div>
    </header>

    @include('partials.print-letterhead')

    <article class="report-card glass-card p-12 md:p-16">

        <!-- Metadata -->
        <div class="report-meta flex flex-col md:flex-row justify-between gap-8 mb-16 pb-12 divider-line">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                    <p class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-600">My Check-In Result</p>
                </div>
                <p class="label-text font-bold uppercase tracking-widest text-[10px]">
                    Date: {{ $score->assessment_date->format('M d, Y h:i A') }}
                </p>
            </div>
            <div class="text-right">
                <p class="label-text text-xs font-black uppercase tracking-widest mb-3">How I Was Doing</p>
                @php
                    $riskLvl = strtolower($score->risk_level);
                    $badgeStyle = match(true) {
                        in_array($riskLvl, ['critical','high']) => 'background:#fee2e2;border-color:#f87171;color:#7f1d1d;',
                        $riskLvl === 'moderate' => 'background:#fef3c7;border-color:#fbbf24;color:#78350f;',
                        default => 'background:#d1fae5;border-color:#34d399;color:#064e3b;',
                    };
                @endphp
                <span class="risk-badge px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest border" style="{{ $badgeStyle }}">
                    {{ ['Low' => 'Doing Okay', 'Moderate' => 'A Bit Tough', 'Medium' => 'A Bit Tough', 'High' => 'Need Care', 'Critical' => 'Need Care'][$score->risk_level] ?? $score->risk_level }}
                </span>
            </div>
        </div>

        <!-- Metric Grid -->
        <div class="report-metrics grid grid-cols-1 md:grid-cols-2 gap-10 mb-16">
            <div class="report-tile metric-box p-10 rounded-[3rem] text-center">
                <h3 class="label-text text-[10px] font-black uppercase tracking-widest mb-8">My Feel-Good Score</h3>
                <div class="relative inline-flex items-center justify-center">
                    <div class="w-32 h-32 rounded-full border-8 ring-border"></div>
                    <span class="absolute text-6xl font-black text-blue-700 tracking-tighter">{{ $score->overall_score }}</span>
                </div>
                <p class="mt-6 text-xs font-bold label-text uppercase tracking-widest">Out of 100 — Higher = Feeling Better</p>
            </div>

            <div class="flex flex-col justify-center">
                <h3 class="section-title text-xs font-black uppercase tracking-widest mb-6 border-l-4 border-blue-600 pl-5">What This Means</h3>
                <p class="body-text text-base font-semibold leading-relaxed">
                    Your answers show you've been feeling
                    <strong class="text-blue-700">{{ strtolower(['Low' => 'mostly okay', 'Moderate' => 'a bit weighed down', 'Medium' => 'a bit weighed down', 'High' => 'pretty stretched lately', 'Critical' => 'pretty stretched lately'][$score->risk_level] ?? strtolower($score->risk_level)) }}</strong>
                    lately. This is just from your check-in answers — not a label about you.
                </p>
            </div>
        </div>

        <!-- Factor Breakdown -->
        <section class="space-y-10 mb-16">
            <h3 class="label-text text-[10px] font-black uppercase tracking-[0.3em] border-b-2 pb-4 divider-line">My 3 Check-In Areas</h3>

            @php
                $factors = [
                    ['name' => 'Low Mood', 'score' => $score->depression_score, 'c' => '#6366f1', 'max' => 21],
                    ['name' => 'Worries',    'score' => $score->anxiety_score,    'c' => '#3b82f6', 'max' => 21],
                    ['name' => 'Pressure',     'score' => $score->stress_score,     'c' => '#0ea5e9', 'max' => 21],
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

        <!-- How your score works -->
        <section class="report-tile mb-16 p-8 info-box rounded-[2.5rem]">
            <h3 class="section-title text-xs font-black uppercase tracking-widest mb-4 border-l-4 border-blue-600 pl-5">How Your Score Works</h3>
            <p class="body-text text-sm font-medium leading-relaxed">
                You answered a few short questions (0–3 each). We added them up in 3 groups: low mood, worries, and pressure.
                <br><br>
                Your <strong class="section-title">feel-good score</strong> is the average turned around so higher is better.
                A <strong style="color:#b91c1c">lower score = tougher days lately</strong>.
                A <strong style="color:#047857">higher score = brighter days lately</strong>.
            </p>
        </section>

        <!-- Counselor note -->
        @if($counselorNote)
            <section class="report-tile counselor-box border-2 rounded-[3rem] p-12 relative">
                <div class="absolute top-0 left-12 -translate-y-1/2 bg-blue-600 px-6 py-2 rounded-full">
                    <p class="text-[9px] font-black uppercase tracking-widest text-white">Note From Your Counselor</p>
                </div>
                <div class="mb-8">
                    <p class="body-text font-medium italic leading-relaxed text-base mb-6">
                        "{!! nl2br(e($counselorNote->note_text)) !!}"
                    </p>
                    <div class="rec-box border p-6 rounded-2xl">
                        <p class="text-[9px] font-black uppercase tracking-widest text-blue-700 mb-2">A gentle suggestion:</p>
                        <p class="section-title font-bold text-sm">{{ $counselorNote->recommendation }}</p>
                    </div>
                </div>
                <div class="report-counselor-meta flex justify-between items-center text-[9px] font-black uppercase tracking-widest label-text border-t pt-6 divider-line">
                    <span>Shared by: School Counselor</span>
                    <span>Shared on: {{ $counselorNote->created_at->format('M d, Y') }}</span>
                </div>
            </section>
        @else
            <section class="text-center py-10 border-2 border-dashed border-slate-300 rounded-[3rem]">
                <p class="label-text font-bold uppercase tracking-widest text-[10px]">Your counselor hasn't added a note yet — check back soon</p>
            </section>
        @endif

        @include('partials.print-footer')
    </article>

    <footer class="mt-12 text-center no-print">
        <p class="label-text font-black uppercase text-[8px] tracking-[0.5em]">PSU Mental Health Portal</p>
    </footer>
</div>
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
async function requestEmergencyCall() {
    if (typeof window.gStartCallStudent === 'function') {
        window.gStartCallStudent();
    } else {
        // Fallback if navigation script is not loaded
        window.location.href = "{{ route('student.dashboard') }}";
    }
}

async function exportPDF() {
    const btn = document.getElementById('exportBtn');
    const originalHTML = btn ? btn.innerHTML : '';
    if (btn) {
        btn.innerHTML = '<i class="ph ph-circle-notch"></i> Generating...';
        btn.disabled = true;
    }
    // Reveal the shared PSU letterhead/footer for the html2canvas snapshot.
    document.body.classList.add('pdf-export');
    document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
    try {
        const { jsPDF } = window.jspdf;
        const element = document.querySelector('.report-page') || document.querySelector('article.glass-card');
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
        pdf.save('PSU-Assessment-Report.pdf');
    } catch (err) {
        console.error(err);
        alert('PDF generation failed. Please try again.');
    } finally {
        document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
        document.body.classList.remove('pdf-export');
        if (btn) {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }
    }
}
</script>
@endpush
@endsection
