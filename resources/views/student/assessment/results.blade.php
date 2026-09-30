@extends('layouts.app')

@push('styles')
<style>
    @media print {
        .sidebar, .action-buttons, .no-print, footer { display:none!important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; }
        body { background:#fff; }
        .pdf-only-header { display: block !important; }
        .pdf-only-footer { display: block !important; }
    }
    .score-fill {
        background: var(--primary);
        height: 100%;
        transition: width 1.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes call-btn-pulse {
        0%,100% { box-shadow: 0 0 0 0 rgba(220,38,38,0.4); }
        50%      { box-shadow: 0 0 0 8px rgba(220,38,38,0); }
    }
    /* AI Insight Header Styling */
    #aiInsightText h3, #aiInsightText b, #aiInsightText strong {
        display: block;
        color: var(--primary);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
        font-style: normal;
        font-weight: 800;
    }
    #aiInsightText h3:first-child { margin-top: 0; }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 850px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <div style="text-align: center; margin-bottom: 2.5rem;" class="no-print">
        <div style="font-size: 2.5rem; margin-bottom: 1rem;">✨</div>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Here's How You're Doing</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">We looked at your check-in answers. Here's a simple breakdown of what we see.</p>
    </div>

    <div id="printableCardArea" style="background: var(--surface-solid); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm); padding: 2.5rem; position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: {{ $risk_colors[$score->risk_level] ?? '#3b82f6' }};"></div>
        
        {{-- Official PSU PDF Report Header (Visible in exported PDF & Print) --}}
        <div class="pdf-only-header" style="display: none; border-bottom: 2px solid #0f766e; padding-bottom: 1.25rem; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.7rem; font-weight: 800; color: #0f766e; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 0.2rem;">PANGASINAN STATE UNIVERSITY</div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: #0f172a; font-family: 'Outfit', sans-serif;">STUDENT MENTAL HEALTH & WELLNESS CENTER</div>
                    <div style="font-size: 0.8rem; color: #475569; font-weight: 600;">Your Personal Check-In Summary</div>
                </div>
                <div style="text-align: right; font-size: 0.75rem; color: #334155; font-weight: 600; line-height: 1.5; background: #f8fafc; padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div><strong>Report ID:</strong> #PAS-{{ sprintf('%05d', $score->score_id) }}</div>
                    <div><strong>Date:</strong> {{ $score->assessment_date->format('F d, Y h:i A') }}</div>
                    <div><strong>Student:</strong> {{ Auth::user()->full_name ?? Auth::user()->name ?? 'Student' }}</div>
                    <div><strong>Email / ID:</strong> {{ Auth::user()->email }}</div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center; margin-bottom: 3rem;">
            <div style="text-align: center;">
                <div style="width: 140px; height: 140px; border-radius: 50%; border: 8px solid #f1f5f9; display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 0 auto; position: relative;">
                    <div style="font-size: 2.25rem; font-weight: 700; color: {{ $risk_colors[$score->risk_level] ?? '#3b82f6' }}; line-height: 1;">{{ $wellness_index }}</div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-top: 0.15rem;">/ 100</div>
                    {{-- Wellness Progress Indicator --}}
                    <div style="position: absolute; inset: -8px; border-radius: 50%; border: 8px solid {{ $risk_colors[$score->risk_level] ?? '#3b82f6' }}; clip-path: inset(0 0 {{ 100 - $wellness_index }}% 0);"></div>
                </div>
                <div style="font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 1.5rem; font-size: 0.7rem;">Wellness Index</div>
                <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary); margin-top: 0.5rem; margin-bottom: 1.5rem;">{{ $wellness_info['label'] }}</div>

                {{-- Scale Identification Legend --}}
                <div style="background: var(--surface-2); border-radius: 12px; padding: 1rem; border: 1px solid var(--border); text-align: left; max-width: 220px; margin: 0 auto;">
                    <div style="font-size: 0.65rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">What Your Score Means</div>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.7rem; font-weight: 700; color: #ef4444;">
                            <span style="width: 8px; height: 8px; border-radius: 2px; background: #ef4444;"></span> 0-20: Really Tough Time
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.7rem; font-weight: 700; color: #f97316;">
                            <span style="width: 8px; height: 8px; border-radius: 2px; background: #f97316;"></span> 21-40: Tough Patch
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.7rem; font-weight: 700; color: #f59e0b;">
                            <span style="width: 8px; height: 8px; border-radius: 2px; background: #f59e0b;"></span> 41-60: Ups and Downs
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.7rem; font-weight: 700; color: #10b981;">
                            <span style="width: 8px; height: 8px; border-radius: 2px; background: #10b981;"></span> 61-80: Feeling Good
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.7rem; font-weight: 700; color: #059669;">
                            <span style="width: 8px; height: 8px; border-radius: 2px; background: #059669;"></span> 81-100: Feeling Bright
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div style="display: inline-block; padding: 0.35rem 0.85rem; border-radius: 6px; font-weight: 700; font-size: 0.72rem; text-transform: uppercase; margin-bottom: 1rem; 
                    background: {{ ($risk_colors[$score->risk_level] ?? '#3b82f6') }}15; color: {{ $risk_colors[$score->risk_level] ?? '#3b82f6' }}; border: 1px solid {{ $risk_colors[$score->risk_level] ?? '#3b82f6' }}30;">
                    {{ ['Low' => 'Doing Okay', 'Moderate' => 'A Bit Tough', 'High' => 'Need Extra Care', 'Critical' => 'Need Extra Care'][$score->risk_level] ?? $score->risk_level }} — How You're Doing
                </div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; color: var(--text); margin-bottom: 0.75rem;">All Done — Thank You for Sharing</h2>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; font-weight: 400;">{{ $recommendations[$score->risk_level] ?? '' }}</p>
                
                <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap;" class="no-print">
                    <button onclick="exportPDF()" id="exportResultBtn" class="btn-sm btn-secondary" style="padding: 0.5rem 1rem; background: var(--surface-2); border: 1px solid var(--border); border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.4rem;"><i class="ph ph-file-pdf"></i> Export PDF</button>
                    <a href="{{ route('student.reports.index') }}" class="btn-sm btn-secondary" style="padding: 0.5rem 1rem; text-decoration: none; background: var(--surface-2); border: 1px solid var(--border); border-radius: 8px; font-weight: 600; color: inherit;">View History</a>
                    @if(in_array(strtolower($score->risk_level), ['high', 'critical']))
                    <button id="callCounselorBtn" onclick="requestEmergencyCall()" style="padding: 0.5rem 1.1rem; background: #dc2626; border: 2px solid #b91c1c; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.4rem; color: white; font-size: 0.85rem; animation: call-btn-pulse 2s ease-in-out infinite; transition: all 0.2s;" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
                        <i class="ph ph-phone-call"></i> Call Counselor
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border); padding-top: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.6rem;">
                    <span style="font-size: 1.25rem;">💬</span> What This Means for You
                </h3>
                <div style="display: flex; align-items: center; gap: 0.75rem;" class="no-print">
                    <button onclick="translateToTagalog()" id="translateBtn" style="background: none; border: 1.5px solid var(--primary); color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.65rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 0.35rem; transition: all 0.2s;" onmouseover="this.style.background='var(--primary-glow)'" onmouseout="this.style.background='none'">
                        <i class="ph ph-translate"></i> Translate to Tagalog
                    </button>
                    <span style="font-size: 0.65rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.1em; background: var(--primary-glow); padding: 0.3rem 0.6rem; border-radius: 6px;">Powered by GPT-4o</span>
                </div>
            </div>
            
            <div style="background: linear-gradient(135deg, rgba(13, 148, 136, 0.03) 0%, rgba(99, 102, 241, 0.03) 100%); border: 1px solid rgba(13, 148, 136, 0.1); border-radius: 16px; padding: 1.5rem; position: relative;">
                <div id="aiInsightText" style="font-size: 0.95rem; line-height: 1.7; color: var(--text); font-weight: 500; font-style: italic;">
                    {!! preg_replace('/### (.*?)(\n|<br \/>)/', '<b>$1</b>$2', nl2br(e($score->ai_analysis))) !!}
                </div>
                <div id="aiInsightRetry" style="display:none; margin-top:1rem;">
                    <button onclick="retryInsight()" id="retryInsightBtn" class="no-print" style="background:var(--primary);color:#fff;border:none;padding:0.6rem 1.2rem;border-radius:10px;font-weight:800;cursor:pointer;font-size:0.85rem;">Retry Insight</button>
                </div>
            </div>
        </div>

        <div style="border-top: 1px solid var(--border); padding-top: 2.5rem; margin-top: 2.5rem;">
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700; margin-bottom: 1.5rem;">Dimension Breakdown</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">
                @php
                    $dimConfigs = [
                        ['id' => 'depression', 'label' => 'Low Mood', 'score' => $score->depression_score, 'info' => $dep_info],
                        ['id' => 'anxiety',    'label' => 'Worries',    'score' => $score->anxiety_score,    'info' => $anx_info],
                        ['id' => 'stress',     'label' => 'Pressure',     'score' => $score->stress_score,     'info' => $str_info],
                    ];
                @endphp
                @foreach($dimConfigs as $dim)
                    @php 
                        $max = $max_scores[$dim['id']] ?? 20;
                        $pct = ($dim['score'] / $max) * 100; 
                    @endphp
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1rem;">
                            <div>
                                <div style="font-weight: 800; font-size: 0.85rem; color: var(--text); margin-bottom: 0.25rem;">{{ $dim['label'] }}</div>
                                <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">{{ $dim['info']['label'] }}</div>
                            </div>
                            <div style="font-weight: 800; color: var(--text);">{{ $dim['score'] }}<span style="font-size: 0.7rem; color: var(--text-dim); margin-left: 0.2rem;">/{{ $max }}</span></div>
                        </div>
                        <div style="height: 8px; background: var(--surface-2); border-radius: 10px; overflow: hidden; border: 1px solid var(--border);">
                            <div class="score-fill" data-width="{{ $pct }}" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Computation Transparency Section --}}
        <div style="border-top: 1px solid var(--border); padding-top: 2.5rem; margin-top: 2.5rem;">
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.25rem;">📊</span> How Your Score Was Made
            </h3>
            <div style="background: var(--surface-2); border-radius: var(--radius); padding: 1.5rem; border: 1px solid var(--border);">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <h4 style="font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.75rem;">Your 3 Check-In Areas</h4>
                        <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.85rem; color: var(--text); display: flex; flex-direction: column; gap: 0.5rem;">
                            <li>• <strong>Mood:</strong> How down or low you've felt (out of 27)</li>
                            <li>• <strong>Worries:</strong> How nervous or on-edge you've felt (out of 21)</li>
                            <li>• <strong>Pressure:</strong> How stressed or stretched thin you've felt (out of 21)</li>
                        </ul>
                    </div>
                    <div>
                        <h4 style="font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.75rem;">How We Get Your Score</h4>
                        <div style="font-size: 0.85rem; color: var(--text); line-height: 1.6;">
                            Your answers added up to: <strong>{{ $raw_total }}</strong> / {{ $max_total }}<br>
                            That means: <strong>{{ round(($raw_total / $max_total) * 100) }}% tough days</strong><br>
                            Your feel-good score: <code>100 - {{ round(($raw_total / $max_total) * 100) }} = <strong>{{ $wellness_index }}</strong></code><br>
                            <span style="font-size:0.8rem; color:var(--text-dim);">Higher = you're feeling better.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 3rem; padding: 0;" class="no-print">
            @if($score->risk_level === 'Moderate')
            <div style="display:flex; align-items:center; gap:1.25rem; padding: 1.5rem 2rem; background: linear-gradient(135deg, rgba(13,148,136,0.06) 0%, rgba(99,102,241,0.06) 100%); border: 1.5px solid rgba(13,148,136,0.2); border-radius: var(--radius); border-left: 5px solid var(--primary);">
                <div style="font-size:2rem; flex-shrink:0;">💬</div>
                <div style="flex:1;">
                    <h4 style="font-family:'Outfit',sans-serif; font-size:1rem; font-weight:800; color:var(--primary); margin-bottom:0.25rem;">A Gentle Next Step</h4>
                    <p style="color:var(--text-muted); font-weight:400; font-size:0.88rem; margin:0;">If you'd like to talk this through with someone who cares, you can set a time with your counselor here.</p>
                </div>
                <a href="{{ route('student.appointments') }}" class="btn-primary" style="padding:0.65rem 1.5rem; border-radius:var(--radius-sm); font-size:0.85rem; text-decoration:none; box-shadow:0 4px 12px rgba(13,148,136,0.2); white-space:nowrap; flex-shrink:0;">Book Session →</a>
            </div>
            @else
            <div style="display:flex; align-items:center; gap:1.25rem; padding: 1.5rem 2rem; background: linear-gradient(135deg, rgba(239,68,68,0.06) 0%, rgba(245,158,11,0.06) 100%); border: 1.5px solid rgba(239,68,68,0.25); border-radius: var(--radius); border-left: 5px solid #dc2626;">
                <div style="font-size:2rem; flex-shrink:0;">🩺</div>
                <div style="flex:1;">
                    <h4 style="font-family:'Outfit',sans-serif; font-size:1rem; font-weight:800; color:#dc2626; margin-bottom:0.25rem;">We're Here for You</h4>
                    <p style="color:var(--text-muted); font-weight:400; font-size:0.88rem; margin:0;">It looks like things have been really tough. Please reach out to your counselor soon — you don't have to go through this alone.</p>
                </div>
                <a href="{{ route('student.appointments') }}" style="padding:0.65rem 1.5rem; border-radius:var(--radius-sm); font-size:0.85rem; text-decoration:none; background:#dc2626; color:white; font-weight:700; box-shadow:0 4px 12px rgba(220,38,38,0.25); white-space:nowrap; flex-shrink:0;">Book Now →</a>
            </div>
            @endif
        </div>

        {{-- Official PDF Sign-off Footer --}}
        <div class="pdf-only-footer" style="display: none; border-top: 1.5px dashed #cbd5e1; padding-top: 1.5rem; margin-top: 2.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                <div style="font-size: 0.7rem; color: #64748b; line-height: 1.5;">
                    <strong>About this summary:</strong><br>
                    This check-in summary was made for you from your answers in the PSU check-in space.<br>
                    It's private — only you and your school counselor can see it.
                </div>
                <div style="text-align: right; border-top: 1px solid #94a3b8; width: 220px; padding-top: 0.35rem; font-size: 0.72rem; font-weight: 700; color: #334155;">
                    School Counselor<br>
                    <span style="font-size: 0.65rem; font-weight: 500; color: #64748b;">Reviewed Support Summary</span>
                </div>
            </div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
async function requestEmergencyCall() {
    if (typeof window.gStartCallStudent === 'function') {
        window.gStartCallStudent();
    } else {
        window.location.href = "{{ route('student.dashboard') }}";
    }
}

async function exportPDF() {
    const btn = document.getElementById('exportResultBtn');
    const originalHTML = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-circle-notch ph-spin"></i> Generating PDF...';
    }

    const pdfHeaders = document.querySelectorAll('.pdf-only-header');
    const pdfFooters = document.querySelectorAll('.pdf-only-footer');
    const noPrintEls = document.querySelectorAll('.no-print');

    // Display PDF header & footer for snapshot
    pdfHeaders.forEach(el => el.style.display = 'block');
    pdfFooters.forEach(el => el.style.display = 'block');
    noPrintEls.forEach(el => el.style.display = 'none');

    try {
        const element = document.getElementById('printableCardArea');
        const canvas = await html2canvas(element, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false
        });

        const imgData = canvas.toDataURL('image/jpeg', 0.98);
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({
            orientation: 'p',
            unit: 'mm',
            format: 'a4'
        });

        const pageW = pdf.internal.pageSize.getWidth();
        const pageH = pdf.internal.pageSize.getHeight();
        const imgH  = (canvas.height * pageW) / canvas.width;
        
        let remaining = imgH;
        let yOffset = 0;

        while (remaining > 0) {
            pdf.addImage(imgData, 'JPEG', 0, -yOffset, pageW, imgH);
            remaining -= pageH;
            yOffset += pageH;
            if (remaining > 0) {
                pdf.addPage();
            }
        }

        pdf.save(`My_Check-In_Summary_#PAS-{{ sprintf('%05d', $score->score_id) }}.pdf`);
    } catch (err) {
        console.error('PDF export error:', err);
        window.print();
    } finally {
        pdfHeaders.forEach(el => el.style.display = 'none');
        pdfFooters.forEach(el => el.style.display = 'none');
        noPrintEls.forEach(el => el.style.display = '');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
    }
}

async function pollInsight(retries = 4) {
    const box = document.getElementById('aiInsightText');
    const retryWrap = document.getElementById('aiInsightRetry');
    if (!box || !box.textContent.match(/pending|Processing/i)) return;
    for (let i = 0; i < retries; i++) {
        await new Promise(r => setTimeout(r, 3000));
        try {
            const res = await fetch("{{ route('student.assessment.insight', $score->score_id) }}");
            const data = await res.json();
            if (data.success && !data.pending) {
                box.innerHTML = data.insight.replace(/\n/g, '<br>');
                if (retryWrap) retryWrap.style.display = 'none';
                return;
            }
        } catch (e) {}
    }
    if (retryWrap) retryWrap.style.display = 'block';
}
async function retryInsight() {
    const box = document.getElementById('aiInsightText');
    const btn = document.getElementById('retryInsightBtn');
    if (btn) { btn.disabled = true; btn.textContent = 'Retrying...'; }
    try {
        const res = await fetch("{{ route('student.assessment.insight', $score->score_id) }}");
        const data = await res.json();
        if (data.success) {
            box.innerHTML = data.insight.replace(/\n/g, '<br>');
            if (!data.pending) document.getElementById('aiInsightRetry').style.display = 'none';
        }
    } finally {
        if (btn) { btn.disabled = false; btn.textContent = 'Retry Insight'; }
    }
}
document.addEventListener('DOMContentLoaded', () => pollInsight());

async function translateToTagalog() {    const btn = document.getElementById('translateBtn');
    const container = document.getElementById('aiInsightText');
    const originalText = `{!! addslashes($score->ai_analysis) !!}`;
    
    if (btn.dataset.translated === 'true') {
        container.innerHTML = originalText.replace(/\n/g, '<br>');
        btn.innerHTML = '<i class="ph ph-translate"></i> Translate to Tagalog';
        btn.dataset.translated = 'false';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-circle-notch ph-spin"></i> Translating...';

    try {
        const response = await fetch("{{ route('student.assessment.translate') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ text: originalText })
        });
        
        const data = await response.json();
        if (data.success) {
            container.innerHTML = data.translation.replace(/\n/g, '<br>');
            btn.innerHTML = '<i class="ph ph-arrow-counter-clockwise"></i> Show English';
            btn.dataset.translated = 'true';
        } else {
            if (window.App) App.toast({ type: 'error', title: 'Translation Failed', message: 'Sorry, we could not translate your summary right now.' });
            btn.innerHTML = '<i class="ph ph-translate"></i> Translate to Tagalog';
        }
    } catch (e) {
        if (window.App) App.toast({ type: 'error', title: 'Network Error', message: 'Could not connect to translation service.' });
        btn.innerHTML = '<i class="ph ph-translate"></i> Translate to Tagalog';
    } finally {
        btn.disabled = false;
    }
}
</script>
@endpush

