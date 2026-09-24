@extends('layouts.app')

@push('styles')
<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.05);
        --glass-border: rgba(255, 255, 255, 0.12);
        --primary-glow: rgba(13, 148, 136, 0.15);
    }

    .dark-mode {
        --glass-bg: rgba(15, 23, 42, 0.4);
        --glass-border: rgba(255, 255, 255, 0.08);
    }

    .survey-container {
        max-width: 850px;
        margin: 0 auto;
        padding: 4rem 1.5rem;
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, border-color 0.3s ease;
    }

    .section-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 1.25rem;
        color: var(--primary);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .question-label {
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--text);
        margin-bottom: 1rem;
        display: block;
        line-height: 1.5;
    }

    /* CC Awareness Options */
    .cc-options {
        display: grid;
        gap: 0.75rem;
    }

    .cc-option {
        position: relative;
        cursor: pointer;
    }

    .cc-option input {
        position: absolute;
        opacity: 0;
    }

    .cc-option-content {
        padding: 1.25rem;
        background: var(--surface-2);
        border: 1.5px solid var(--border);
        border-radius: 16px;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-muted);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .cc-option input:checked + .cc-option-content {
        background: var(--primary-glow);
        border-color: var(--primary);
        color: var(--primary);
        box-shadow: 0 4px 12px var(--primary-glow);
    }

    /* Likert Scale for SQD */
    .sqd-grid {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .sqd-item {
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--border);
    }
    .sqd-item:last-child { border: none; }

    .likert-scale {
        display: flex;
        justify-content: space-between;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .likert-option {
        flex: 1;
        text-align: center;
        position: relative;
    }

    .likert-option input {
        position: absolute;
        opacity: 0;
    }

    .likert-box {
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--surface-2);
        border: 1.5px solid var(--border);
        border-radius: 12px;
        font-weight: 800;
        font-size: 1.1rem;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .likert-label {
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--text-dim);
        margin-top: 0.5rem;
        display: block;
    }

    .likert-option input:checked + .likert-box {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(13, 148, 136, 0.3);
    }

    #ccSection2, #ccSection3 {
        overflow: hidden;
        transition: max-height 0.4s ease, opacity 0.4s ease;
    }

    .hidden-cc {
        max-height: 0 !important;
        opacity: 0 !important;
        margin-bottom: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        pointer-events: none;
    }

    @media (max-width: 600px) {
        .likert-scale { gap: 0.25rem; }
        .likert-box { height: 44px; font-size: 1rem; border-radius: 8px; }
        .likert-label { font-size: 0.55rem; letter-spacing: -0.02em; }
    }
</style>
@endpush

@section('content')
<div class="survey-container">
    {{-- Header --}}
    <div style="text-align: center; margin-bottom: 4rem;" class="reveal-up">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 20px; background: var(--primary-glow); color: var(--primary); font-size: 2rem; margin-bottom: 1.5rem;">
            <i class="ph-fill ph-chat-circle-dots"></i>
        </div>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.5rem; font-weight: 900; color: var(--text); margin-bottom: 0.75rem;">Clinical Excellence Survey</h1>
        <p style="color: var(--text-muted); font-size: 1.15rem; max-width: 550px; margin: 0 auto; line-height: 1.6;">
            Your feedback helps us maintain the highest standards of mental health care. Please complete this official satisfaction form.
        </p>
    </div>

    <form action="{{ route('student.survey.store', $appointment->appointment_id) }}" method="POST" id="csatForm">
        @csrf



        @php
            $sqd_questions = [
                'sqd0' => 'Overall, I am satisfied with the clinical service I received.',
                'sqd1' => 'I spent a reasonable amount of time for my session.',
                'sqd2' => 'The counselor followed clinical requirements and steps correctly.',
                'sqd3' => 'The steps I needed to take for my session were easy and simple.',
                'sqd4' => 'I easily found information about the clinical services available.',
                'sqd5' => 'The "fees" (clinical processing) were reasonable/appropriate.',
                'sqd6' => 'I am confident my sessions are kept confidential and no favoritism was involved.',
                'sqd7' => 'The staff/counselor gave quick and appropriate attention to my concerns.',
                'sqd8' => 'I got exactly what I needed from this clinical session.',
            ];
        @endphp

        {{-- Section B: Service Quality Dimensions --}}
        <h2 class="section-title reveal-up"><i class="ph-bold ph-chart-bar"></i> Service Quality Evaluation</h2>
        
        <div class="glass-card reveal-up" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                    <thead>
                        <tr style="background: var(--surface-2); border-bottom: 1px solid var(--border);">
                            <th style="padding: 2rem; text-align: left; width: 40%; font-size: 0.85rem; color: var(--text-muted); font-weight: 850; text-transform: uppercase; letter-spacing: 0.05em;">Clinical Dimension</th>
                            @for($i=1; $i<=5; $i++)
                            <th style="padding: 1.5rem 0.5rem; text-align: center; font-size: 0.7rem; color: var(--text-dim); font-weight: 800; text-transform: uppercase;">
                                {{ $i }}<br>
                                <span style="font-size: 0.6rem; opacity: 0.7;">
                                    @if($i == 1) SD @elseif($i == 2) D @elseif($i == 3) N @elseif($i == 4) A @elseif($i == 5) SA @endif
                                </span>
                            </th>
                            @endfor
                            <th style="padding: 1.5rem 0.5rem; text-align: center; font-size: 0.7rem; color: var(--text-dim); font-weight: 800; text-transform: uppercase;">N/A</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sqd_questions as $key => $q)
                        <tr class="sqd-row-clinical reveal-up" style="border-bottom: 1px solid var(--border); transition: background 0.3s ease;">
                            <td style="padding: 1.75rem 2rem;">
                                <div style="font-size: 1rem; font-weight: 600; color: var(--text); line-height: 1.5;">{{ $q }}</div>
                                <div style="font-size: 0.7rem; color: var(--text-dim); font-weight: 700; text-transform: uppercase; margin-top: 0.3rem; letter-spacing: 0.02em;">{{ strtoupper($key) }}</div>
                            </td>
                            @for($i=1; $i<=5; $i++)
                            <td style="padding: 0.5rem; text-align: center;">
                                <label style="display: block; cursor: pointer;">
                                    <input type="radio" name="{{ $key }}" value="{{ $i }}" required style="display: none;">
                                    <div class="likert-box-minimal" style="width: 44px; height: 44px; margin: 0 auto; border-radius: 12px; border: 1.5px solid var(--border); background: var(--surface-2); display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--text-dim); transition: all 0.2s ease;">{{ $i }}</div>
                                </label>
                            </td>
                            @endfor
                            <td style="padding: 0.5rem; text-align: center;">
                                <label style="display: block; cursor: pointer;">
                                    <input type="radio" name="{{ $key }}" value="0" style="display: none;">
                                    <div class="likert-box-minimal" style="width: 44px; height: 44px; margin: 0 auto; border-radius: 12px; border: 1.5px solid var(--border); background: var(--surface-2); display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800; color: var(--text-dim); transition: all 0.2s ease;">N/A</div>
                                </label>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <style>
            .sqd-row-clinical:hover { background: rgba(13, 148, 136, 0.02); }
            .sqd-row-clinical input:checked + .likert-box-minimal {
                background: var(--primary);
                border-color: var(--primary);
                color: white;
                box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
                transform: scale(1.1);
            }
        </style>

        {{-- Final Comments --}}
        <div class="glass-card reveal-up">
            <label class="question-label"><i class="ph-bold ph-pencil-line" style="color: var(--primary);"></i> Suggestions/Comments (Optional)</label>
            <textarea name="feedback_text" placeholder="Tell us how we can improve our services further..." style="width: 100%; height: 120px; padding: 1.25rem; border-radius: 16px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text); font-family: inherit; font-size: 1rem; resize: none; transition: border-color 0.3s ease;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'"></textarea>
        </div>

        <div style="margin-top: 3rem; text-align: center;" class="reveal-up">
            <button type="submit" class="btn-primary" style="padding: 1.25rem 4rem; font-size: 1.1rem; font-weight: 800; border-radius: 20px; box-shadow: 0 10px 30px rgba(13, 148, 136, 0.2);">
                Submit Official Feedback →
            </button>
            <p style="margin-top: 1.5rem; font-size: 0.85rem; color: var(--text-dim); font-weight: 600;">
                <i class="ph-bold ph-lock"></i> All responses are treated with strict confidentiality.
            </p>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>


document.addEventListener('DOMContentLoaded', () => {
    // GSAP reveal animations (check if GSAP exists)
    if (typeof gsap !== 'undefined') {
        gsap.from('.reveal-up', {
            y: 40,
            opacity: 0,
            duration: 1.2,
            stagger: 0.1,
            ease: "expo.out",
        });
    }

    // Custom checkmark animation for radio buttons
    const radios = document.querySelectorAll('input[type="radio"]');
    radios.forEach(radio => {
        radio.addEventListener('change', (e) => {
            const container = e.target.closest('.cc-options, .likert-scale');
            if (container) {
                // If CC option, show dot
                const dots = container.querySelectorAll('.check-dot');
                dots.forEach(d => d.style.display = 'none');
                const selectedDot = e.target.closest('.cc-option')?.querySelector('.check-dot');
                if (selectedDot) selectedDot.style.display = 'block';
            }
        });
    });

    // AJAX Form Submission
    const form = document.getElementById('csatForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        const fd = new FormData(form);

        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Processing...';

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            const data = await res.json();

            if (data.success) {
                gsap.to('.survey-container > *', {
                    opacity: 0,
                    y: -20,
                    stagger: 0.1,
                    duration: 0.6,
                    onComplete: () => {
                        const container = document.querySelector('.survey-container');
                        container.innerHTML = `
                            <div style="text-align: center; padding: 6rem 0;" class="reveal-up">
                                <div style="display: inline-flex; align-items: center; justify-content: center; width: 100px; height: 100px; border-radius: 30px; background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 3.5rem; margin-bottom: 2.5rem; box-shadow: 0 20px 40px rgba(16, 185, 129, 0.15);">
                                    <i class="ph-fill ph-check-circle"></i>
                                </div>
                                <h1 style="font-family: 'Outfit', sans-serif; font-size: 3rem; font-weight: 900; color: var(--text); margin-bottom: 1rem;">Protocol Complete</h1>
                                <p style="color: var(--text-muted); font-size: 1.25rem; max-width: 600px; margin: 0 auto 3rem; line-height: 1.6;">
                                    Your institutional feedback has been securely archived. We appreciate your contribution to clinical excellence.
                                </p>
                                <a href="{{ route('student.dashboard') }}" class="btn-primary" style="padding: 1.25rem 3rem; font-weight: 800; border-radius: 20px; text-decoration: none;">
                                    Return to Command Center
                                </a>
                            </div>
                        `;
                        gsap.from('.reveal-up > *', { opacity: 0, y: 30, stagger: 0.2, duration: 1, ease: "expo.out" });
                    }
                });
            } else {
                App.toast({ type: 'error', title: 'Submission Failed', message: data.error || 'Please ensure all required fields are completed.' });
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        } catch (err) {
            App.toast({ type: 'error', title: 'Network Error', message: 'Could not connect to institutional core.' });
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
});
</script>
@endpush
