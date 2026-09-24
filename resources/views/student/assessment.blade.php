@extends('layouts.app')

@push('styles')
<style>
    .assessment-header {
        position: fixed;
        top: 0;
        left: 260px;
        right: 0;
        height: 64px;
        background: var(--surface);
        backdrop-filter: var(--glass-blur);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 2.5rem;
        border-bottom: 1px solid var(--border);
    }
    .progress-indicator {
        display: flex;
        gap: 1.5rem;
        align-items: center;
    }
    .step-node {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        background: var(--surface-2);
        color: var(--text-dim);
        transition: var(--transition);
        border: 2px solid transparent;
    }
    .step-node.active {
        background: var(--primary-glow);
        color: var(--primary);
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.15);
    }
    .step-node.completed {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border-color: #10b981;
    }

    /* ── Question card (vertical, full-width) ── */
    .question-strip {
        background: var(--surface-solid);
        padding: 2.5rem 2rem;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.75s cubic-bezier(0.23, 1, 0.32, 1);
    }
    .question-strip.visible {
        opacity: 1;
        transform: translateY(0);
    }
    .question-strip.unanswered-highlight {
        outline: 3px solid #fca5a5;
        outline-offset: 6px;
    }

    /* ── Choice cards ── */
    .choice-matrix {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.875rem;
        margin-top: 1.75rem;
    }
    .choice-card {
        background: var(--surface-2);
        border: 2px solid transparent;
        border-radius: var(--radius);
        padding: 1.35rem 1rem;
        text-align: center;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }
    .choice-card .val {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--text-dim);
        transition: var(--transition);
        line-height: 1;
    }
    .choice-card .label {
        font-size: 0.68rem;
        font-weight: 600;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        line-height: 1.3;
    }
    .choice-card:hover {
        transform: translateY(-4px);
        background: var(--surface-solid);
        border-color: var(--primary-light);
        box-shadow: var(--shadow-sm);
    }
    .choice-card.selected {
        background: var(--surface-solid);
        border-color: var(--primary);
        box-shadow: 0 8px 20px var(--primary-glow);
        transform: translateY(-3px);
    }
    .choice-card.selected .val  { color: var(--primary); transform: scale(1.08); }
    .choice-card.selected .label { color: var(--primary); }

    @keyframes toastIn {
        from { opacity:0; transform:translateX(-50%) translateY(-12px); }
        to   { opacity:1; transform:translateX(-50%) translateY(0); }
    }
    @keyframes modalPop {
        from { transform:scale(0.85); opacity:0; }
        to   { transform:scale(1); opacity:1; }
    }
</style>
@endpush

@php
    $tagalogQuestions = [
        1  => 'Kawalan ng interes o kawalan ng sigla sa paggawa ng mga bagay-bagay',
        2  => 'Pakiramdam na malungkot, nalulumbay, o walang pag-asa',
        3  => 'Hirap sa pagtulog, madalas na paggising, o labis na pagtulog',
        4  => 'Pakiramdam na pagod o kapos sa lakas at sigla',
        5  => 'Walang ganang kumain o labis na pagkain',
        6  => 'Masamang pakiramdam sa sarili — o pakiramdam na ikaw ay nabigo o nakabigo sa iyong sarili o sa iyong pamilya',
        7  => 'Hirap sa pagtutuon ng pansin sa mga bagay-bagay, tulad ng pagbabasa o panonood ng telebisyon',
        8  => 'Mabagal na pagkilos o pagsasalita na napapansin na ng iba? O kabaligtaran — labis na pagkabalisa o di-mapakali kaya mas galaw nang galaw kaysa sa karaniwan',
        9  => 'Mga kaisipang mas mabuti pang mawala na o saktan ang sarili sa anumang paraan',
        10 => 'Pakiramdam na ninenerbyos, balisa, o balisa sa paligid',
        11 => 'Hindi mapigilan o hindi makontrol ang labis na pag-aalala',
        12 => 'Labis na pag-aalala sa iba\'t ibang bagay',
        13 => 'Hirap mag-relax o magpahinga ng isip',
        14 => 'Sobrang di-mapakali kaya mahirap umupo nang tahimik',
        15 => 'Madaling mainis o maging iritable',
        16 => 'Pakiramdam na natatakot na tila may masamang mangyayari',
        17 => 'Nahirapan akong magpalipas ng pagod o magbawas ng tensyon',
        18 => 'Mabilis akong mag-overreact sa mga sitwasyon',
        19 => 'Naramdaman kong labis akong gumagamit ng lakas dahil sa kaba',
        20 => 'Naramdaman kong madali akong maging balisa o magalit',
        21 => 'Nahirapan akong mag-relax o maging palagay',
        22 => 'Wala akong pasensya sa anumang humahadlang sa aking ginagawa',
        23 => 'Naramdaman kong medyo sensitibo o madaling maapektuhan ang aking damdamin',
    ];

    $tagalogCategories = [
        'Depression' => 'Depresyon',
        'Anxiety'    => 'Kabalisahan',
        'Stress'     => 'Tensyon',
    ];
@endphp

@section('content')
<div class="assessment-header">
    <div class="progress-indicator">
        @foreach($categories as $i => $cat)
            <div class="step-node {{ $i === 0 ? 'active' : '' }}" id="node-{{ $i }}">
                {{ $i + 1 }}
            </div>
        @endforeach
    </div>
    <div style="display: flex; align-items: center; gap: 1.5rem;">
        <div style="text-align: right; border-left: 1px solid var(--border); padding-left: 1.5rem;">
            <div id="sectionTitle" style="font-weight: 600; font-size: 0.95rem; color: var(--primary-dark); margin-bottom: 0.15rem;">
                {{ $categories[0] }} <span style="font-style: italic; font-weight: 500; color: var(--text-dim); font-size: 0.85rem;">({{ $tagalogCategories[$categories[0]] ?? '' }})</span>
            </div>
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.06em;">In Progress</div>
        </div>
    </div>
</div>

<div class="container" style="max-width: 820px; margin: 0 auto; padding-top: 6rem; padding-bottom: 6rem;">

    <div style="text-align: center; margin-bottom: 3.5rem;">
        <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.75rem;">Pre-Assessment</div>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.1rem; font-weight: 700; color: var(--text); margin-bottom: 0.2rem;">Wellness Check-in</h1>
        <div style="font-style: italic; color: var(--primary); font-size: 1.05rem; font-weight: 600; margin-bottom: 0.85rem;">Pagsusuri sa Kalusugang Pangkaisipan</div>
        <p style="color: var(--text-muted); font-size: 1rem; font-weight: 400; max-width: 580px; margin: 0 auto; line-height: 1.65;">
            Answer honestly — your responses help us understand how you're doing and provide better support.
            <span style="font-style: italic; color: var(--text-dim); font-size: 0.9rem; display: block; margin-top: 0.35rem;">
                Sumagot nang buong katapatan — ang iyong mga sagot ay tumutulong sa amin na maunawaan ang iyong kalagayan at makapagbigay ng mas mabuting suporta.
            </span>
        </p>
    </div>

    <form method="POST" action="{{ route('student.assessment.store') }}" id="assessmentForm">
        @csrf
        @foreach($categories as $stepIdx => $category)
            <div class="assessment-section" id="step-{{ $stepIdx }}" style="display: {{ $stepIdx === 0 ? 'block' : 'none' }};">

                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    @foreach(collect($questions)->where('category', $category) as $question)
                        <div class="question-strip">
                            {{-- Question text + number + Tagalog translation --}}
                            <div style="display: flex; align-items: flex-start; gap: 1.25rem; margin-bottom: 0.25rem;">
                                <div style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 700; color: var(--primary); opacity: 0.15; line-height: 1; min-width: 40px; text-align: center;">
                                    {{ str_pad($question->question_number, 2, '0', STR_PAD_LEFT) }}
                                </div>
                                <div style="flex: 1; padding-top: 0.2rem;">
                                    <label style="font-size: 1.05rem; font-weight: 600; color: var(--text); line-height: 1.45; display: block;">
                                        {{ $question->question_text }}
                                    </label>
                                    @if(isset($tagalogQuestions[$question->question_number]))
                                        <div style="font-style: italic; font-size: 0.92rem; color: var(--text-dim); margin-top: 0.35rem; line-height: 1.45; font-weight: 400;">
                                            {{ $tagalogQuestions[$question->question_number] }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Choice grid (below question) --}}
                            <div class="choice-matrix">
                                @php
                                    $opts = [
                                        0 => ['en' => 'Not at all', 'tl' => 'Hindi kailanman'],
                                        1 => ['en' => 'Several days', 'tl' => 'Ilang araw'],
                                        2 => ['en' => 'More than half the days', 'tl' => 'Higit sa kalahati ng mga araw'],
                                        3 => ['en' => 'Nearly every day', 'tl' => 'Halos araw-araw'],
                                    ];
                                @endphp
                                @foreach($opts as $val => $opt)
                                    <div class="choice-card"
                                         onclick="selectChoice('q_{{ $question->question_id }}', {{ $val }}, this)"
                                         title="{{ $opt['en'] }} ({{ $opt['tl'] }})">
                                        <input type="radio" name="q_{{ $question->question_id }}"
                                               value="{{ $val }}" style="display:none;"
                                               {{ $stepIdx === 0 ? 'required' : '' }}>
                                        <div class="val">{{ $val }}</div>
                                        <div class="label" style="display: flex; flex-direction: column; gap: 0.2rem;">
                                            <span>{{ $opt['en'] }}</span>
                                            <span style="font-style: italic; font-weight: 500; font-size: 0.65rem; text-transform: none; letter-spacing: normal; opacity: 0.85;">{{ $opt['tl'] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Navigation bar --}}
                <div style="margin-top: 3rem; display: flex; justify-content: space-between; align-items: center; background: var(--surface-2); padding: 1.5rem 2rem; border-radius: var(--radius); border: 1px solid var(--border);">
                    @if($stepIdx > 0)
                        <button type="button" onclick="goStep({{ $stepIdx - 1 }})"
                                style="background: var(--surface-solid); border: 1px solid var(--border); padding: 0.75rem 1.75rem; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; color: var(--text-muted); transition: var(--transition); font-size: 0.9rem;">← Back</button>
                    @else
                        <div style="width: 120px;"></div>
                    @endif

                    <div style="text-align: center;">
                        <div style="font-size: 0.95rem; font-weight: 600; color: var(--primary); margin-bottom: 0.25rem;">Section {{ $stepIdx + 1 }}</div>
                        <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.06em;">
                            {{ $category }} <span style="font-style: italic; font-weight: 500; text-transform: none;">({{ $tagalogCategories[$category] ?? '' }})</span>
                        </div>
                    </div>

                    @if($stepIdx < count($categories) - 1)
                        <button type="button" onclick="goStep({{ $stepIdx + 1 }})"
                                style="background: var(--primary); border: none; padding: 0.75rem 2rem; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; color: white; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); transition: var(--transition); font-size: 0.9rem;">Next →</button>
                    @else
                        <button type="button" onclick="confirmSubmit()"
                                style="background: #059669; border: none; padding: 0.75rem 2rem; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; color: white; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); transition: var(--transition); font-size: 0.9rem;">Submit Assessment</button>
                    @endif
                </div>

            </div>
        @endforeach
    </form>
</div>

{{-- Validation toast --}}
<div id="validationToast" style="display:none; position:fixed; top:2rem; left:50%; transform:translateX(-50%); z-index:9999; background:#fef2f2; border:2px solid #fca5a5; color:#991b1b; padding:1.25rem 2.5rem; border-radius:20px; font-weight:800; font-size:1rem; box-shadow:0 20px 40px rgba(0,0,0,0.15); text-align:center; animation: toastIn 0.3s cubic-bezier(0.34,1.56,0.64,1);">
    ⚠️ Please answer <strong id="toastCount"></strong> before continuing.
</div>

@push('modals')
{{-- Confirm Submit Modal --}}
<div id="confirmModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.6); backdrop-filter:blur(10px); align-items:center; justify-content:center;">
    <div style="background:var(--surface-solid); border:1px solid var(--border); border-radius:var(--radius-lg); padding:2.75rem; max-width:420px; width:90%; text-align:center; box-shadow:var(--shadow-lg); animation:modalPop 0.25s cubic-bezier(0.34,1.56,0.64,1);">
        <div style="font-size:2.75rem; margin-bottom:1rem;">📋</div>
        <h3 style="font-family:'Outfit',sans-serif; font-size:1.3rem; font-weight:700; color:var(--text); margin-bottom:0.5rem;">Submit Your Assessment?</h3>
        <p style="color:var(--text-muted); font-size:0.9rem; font-weight:400; line-height:1.55; margin-bottom:1.85rem;">Make sure all your answers reflect how you truly feel before submitting.</p>
        <div style="display:flex; gap:0.75rem;">
            <button onclick="closeConfirmModal()" style="flex:1; background:var(--surface-2); border:none; padding:0.8rem; border-radius:var(--radius-sm); font-weight:600; font-size:0.9rem; color:var(--text-muted); cursor:pointer;">Go Back</button>
            <button onclick="doSubmit()" style="flex:2; background:#059669; border:none; padding:0.8rem; border-radius:var(--radius-sm); font-weight:700; font-size:0.9rem; color:white; cursor:pointer; box-shadow:0 4px 12px rgba(16,185,129,0.22);">Yes, Submit →</button>
        </div>
    </div>
</div>
@endpush
@endsection

@push('scripts')
<script>
const categories = @json($categories);
let currentStep = 0;
let formSubmitting = false;

// Warn before leaving
function warnBeforeLeave(e) {
    if (formSubmitting) return;
    e.preventDefault();
    e.returnValue = '';
}
window.addEventListener('beforeunload', warnBeforeLeave);

function selectChoice(name, val, el) {
    const parent = el.parentElement;
    parent.querySelectorAll('.choice-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    const input = el.querySelector('input[type="radio"]');
    if (input) {
        input.checked = true;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

let toastTimer = null;
function showToast(msg) {
    const toast = document.getElementById('validationToast');
    document.getElementById('toastCount').textContent = msg;
    toast.style.display = 'block';
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { toast.style.display = 'none'; }, 3500);

    // Scroll to + highlight first unanswered
    const strip = document.querySelector('#step-' + currentStep + ' .question-strip:not(:has(input:checked))');
    if (strip) {
        strip.scrollIntoView({ behavior: 'smooth', block: 'center' });
        strip.classList.add('unanswered-highlight');
        setTimeout(() => strip.classList.remove('unanswered-highlight'), 2600);
    }
}

function goStep(next) {
    if (next > currentStep) {
        const section = document.getElementById('step-' + currentStep);
        const strips  = section.querySelectorAll('.question-strip');
        let unanswered = 0;
        strips.forEach(q => { if (!q.querySelector('input[type="radio"]:checked')) unanswered++; });
        if (unanswered > 0) {
            showToast(unanswered + ' unanswered question' + (unanswered > 1 ? 's' : ''));
            return;
        }
    }

    document.getElementById('step-' + currentStep).style.display = 'none';
    document.getElementById('node-' + currentStep).classList.remove('active');
    if (next > currentStep) document.getElementById('node-' + currentStep).classList.add('completed');

    currentStep = next;
    document.getElementById('step-' + currentStep).style.display = 'block';
    document.getElementById('node-' + currentStep).classList.add('active');
    
    const catName = categories[currentStep];
    const tagalogCats = { 'Depression': 'Depresyon', 'Anxiety': 'Kabalisahan', 'Stress': 'Tensyon' };
    const catTl = tagalogCats[catName] ? ` (${tagalogCats[catName]})` : '';
    document.getElementById('sectionTitle').innerHTML = catName + ' <span style="font-style: italic; font-weight: 500; color: var(--text-dim); font-size: 0.85rem;">' + catTl + '</span>';

    window.scrollTo({ top: 0, behavior: 'instant' });
    initObserver();
}

function confirmSubmit() {
    const section = document.getElementById('step-' + currentStep);
    const strips  = section.querySelectorAll('.question-strip');
    let unanswered = 0;
    strips.forEach(q => { if (!q.querySelector('input[type="radio"]:checked')) unanswered++; });
    if (unanswered > 0) {
        showToast(unanswered + ' unanswered question' + (unanswered > 1 ? 's' : ''));
        return;
    }
    document.getElementById('confirmModal').style.display = 'flex';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

function doSubmit() {
    if (formSubmitting) return;
    formSubmitting = true;
    window.removeEventListener('beforeunload', warnBeforeLeave);

    const btn = document.querySelector('button[onclick="doSubmit()"]');
    btn.disabled = true;
    btn.textContent = 'Submitting…';

    const form = document.getElementById('assessmentForm');
    const fd   = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: fd,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            App.toast({ type: 'success', title: 'Submitted', message: data.message });
            closeConfirmModal();
            setTimeout(() => { window.location.href = data.redirect_url; }, 600);
        } else {
            App.toast({ type: 'error', title: 'Error', message: data.message || 'Submission failed.' });
            btn.disabled = false;
            btn.textContent = 'Yes, Submit →';
            formSubmitting = false;
            closeConfirmModal();
        }
    })
    .catch(() => {
        App.toast({ type: 'error', title: 'Error', message: 'Failed to connect.' });
        btn.disabled = false;
        btn.textContent = 'Yes, Submit →';
        formSubmitting = false;
        closeConfirmModal();
    });
}

// Close modal on backdrop click
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeConfirmModal();
});

function initObserver() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        });
    }, { threshold: 0.08 });
    document.querySelectorAll('.question-strip').forEach(s => observer.observe(s));
}

document.addEventListener('DOMContentLoaded', initObserver);
</script>
@endpush
