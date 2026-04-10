@extends('layouts.app')

@push('styles')
<style>
    .stats-matrix-premium {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1.25rem;
        margin-bottom: 3rem;
    }
    
    .stat-card-clinical-premium {
        background: var(--surface-solid);
        border-radius: var(--radius);
        padding: 1.75rem;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }
    
    .stat-card-clinical-premium:hover { 
        transform: translateY(-5px); 
        box-shadow: var(--shadow-lg); 
        border-color: var(--primary-light); 
    }

    .stat-card-clinical-premium::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 4px; height: 100%;
        background: var(--primary);
        opacity: 0.8;
    }

    .intervention-registry-premium {
        background: var(--surface-solid);
        border-radius: var(--radius-lg);
        padding: 2.5rem;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        margin-bottom: 3rem;
        transition: var(--transition);
    }
    
    .dialog-vault-premium {
        background: var(--surface-2);
        border-radius: var(--radius);
        padding: 2rem;
        border: 1px solid var(--border);
        margin-bottom: 2rem;
        height: fit-content;
        transition: var(--transition);
    }
    
    .dialog-vault-premium:hover {
        background: var(--surface-solid);
        box-shadow: var(--shadow);
        border-color: var(--primary-glow);
    }

    .message-bubble-premium {
        padding: 1rem 1.25rem;
        border-radius: 18px;
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        max-width: 85%;
        font-weight: 500;
        box-shadow: var(--shadow-sm);
    }

    .clinical-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 0.85rem;
        border-radius: 100px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    @media (max-width: 1024px) {
        .stats-matrix-premium { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1200px; padding-top: 1rem; padding-bottom: 4rem;">
    
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem;" class="staggered">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 0.5rem;">Clinical Operations Center</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 900; color: var(--text); letter-spacing: -0.04em;">Counselor Overview</h1>
            <p style="color: var(--text-muted); font-size: 1rem; font-weight: 500;">Currently managing {{ $stats['total_students'] }} active clinical profiles. ({{ count($anon_notes) }} notes pending)</p>
        </div>
        <div style="display: flex; gap: 1.5rem; align-items: center;">
            <div style="text-align: right; padding-right: 1.5rem; border-right: 2px solid var(--border);">
                <div style="font-size: 2rem; font-weight: 900; color: var(--accent); line-height: 1;">{{ $stats['critical_risk'] }}</div>
                <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-top: 0.25rem;">Critical Priority</div>
            </div>
            <a href="{{ route('counselor.appointments.index') }}" class="btn-primary" style="padding: 0.85rem 1.75rem;">
                <span>📅</span> Clinical Schedule
            </a>
        </div>
    </div>

    <!-- Stats Matrix -->
    <div class="stats-matrix-premium">
        <div class="stat-card-clinical-premium staggered">
            <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 1.25rem; letter-spacing: 0.1em;">Total Patient Load</div>
            <div style="font-size: 2.5rem; font-weight: 900; color: var(--text); line-height: 1;">{{ $stats['total_students'] }}</div>
        </div>
        <div class="stat-card-clinical-premium staggered" style="border-left-color: var(--primary);">
            <div style="font-size: 0.7rem; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-bottom: 1.25rem; letter-spacing: 0.1em;">Low Risk Cluster</div>
            <div style="font-size: 2.5rem; font-weight: 900; color: var(--primary); line-height: 1;">{{ $stats['low_risk'] }}</div>
        </div>
        <div class="stat-card-clinical-premium staggered" style="border-left-color: #f59e0b;">
            <div style="font-size: 0.7rem; font-weight: 800; color: #f59e0b; text-transform: uppercase; margin-bottom: 1.25rem; letter-spacing: 0.1em;">Moderate Risk</div>
            <div style="font-size: 2.5rem; font-weight: 900; color: #f59e0b; line-height: 1;">{{ $stats['moderate_risk'] }}</div>
        </div>
        <div class="stat-card-clinical-premium staggered" style="border-left-color: #f97316;">
            <div style="font-size: 0.7rem; font-weight: 800; color: #f97316; text-transform: uppercase; margin-bottom: 1.25rem; letter-spacing: 0.1em;">High Risk Vector</div>
            <div style="font-size: 2.5rem; font-weight: 900; color: #f97316; line-height: 1;">{{ $stats['high_risk'] }}</div>
        </div>
        <div class="stat-card-clinical-premium staggered" style="background: rgba(244, 63, 94, 0.03); border-left-color: var(--accent);">
            <div style="font-size: 0.8rem; font-weight: 900; color: var(--accent); text-transform: uppercase; margin-bottom: 1.25rem; letter-spacing: 0.15em;">Critical Intervention</div>
            <div style="font-size: 3rem; font-weight: 900; color: var(--accent); line-height: 1;">{{ $stats['critical_risk'] }}</div>
        </div>
    </div>

    <!-- Intervention Registry -->
    <div class="intervention-registry-premium staggered">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; border-bottom: 2px solid var(--border); padding-bottom: 1.5rem;">
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem; letter-spacing: -0.02em;">Priority Intervention Queue</h2>
                <p style="color: var(--text-muted); font-weight: 500; font-size: 1rem;">Clinical priority assigned based on multi-factor wellness index scans.</p>
            </div>
            <a href="{{ route('counselor.students.index') }}" class="btn-secondary" style="padding: 0.75rem 1.5rem;">View Full Registry</a>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="padding: 1.25rem; text-align: left; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; background: var(--surface-2); border-radius: 12px 0 0 12px;">Patient Identity</th>
                    <th style="padding: 1.25rem; text-align: left; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; background: var(--surface-2);">Wellness Vector</th>
                    <th style="padding: 1.25rem; text-align: left; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; background: var(--surface-2);">Risk Classification</th>
                    <th style="padding: 1.25rem; text-align: left; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; background: var(--surface-2);">Last Diagnostic</th>
                    <th style="padding: 1.25rem; text-align: right; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; background: var(--surface-2); border-radius: 0 12px 12px 0;">Case Protocol</th>
                </tr>
            </thead>
            <tbody style="border-top: 1.5rem solid transparent;">
                @forelse ($priority_queue as $student)
                <tr style="border-bottom: 1px solid var(--border); transition: all 0.3s;" class="staggered-row" onmouseover="this.style.background='var(--surface-2)'; this.style.transform='scale(1.005)'" onmouseout="this.style.background='transparent'; this.style.transform='scale(1)'">
                    <td style="padding: 1.5rem 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 44px; height: 44px; border-radius: 14px; background: linear-gradient(135deg, var(--surface-2) 0%, var(--surface-3) 100%); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 900; border: 1px solid var(--border); font-size: 1rem;">
                                {{ strtoupper(substr($student->full_name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight: 800; color: var(--text); font-size: 1.05rem; margin-bottom: 0.2rem;">{{ $student->full_name }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; opacity: 0.7;">ID: {{ $student->roll_number }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 1.5rem 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="flex: 1; height: 10px; background: var(--surface-2); border-radius: 10px; overflow: hidden; max-width: 120px; border: 1px solid var(--border);">
                                <div style="width: {{ $student->latest_assessment->overall_score }}%; height: 100%; background: {{ ($student->latest_assessment->risk_level === 'Critical') ? 'var(--accent)' : '#f97316' }};"></div>
                            </div>
                            <span style="font-weight: 900; color: var(--text); font-size: 0.9rem;">{{ $student->latest_assessment->overall_score }}%</span>
                        </div>
                    </td>
                    <td style="padding: 1.5rem 1.25rem;">
                        <span class="clinical-indicator" style="background: {{ ($student->latest_assessment->risk_level === 'Critical') ? 'rgba(244, 63, 94, 0.1)' : 'rgba(249, 115, 22, 0.1)' }}; color: {{ ($student->latest_assessment->risk_level === 'Critical') ? 'var(--accent)' : '#f97316' }};">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                            {{ $student->latest_assessment->risk_level }}
                        </span>
                    </td>
                    <td style="padding: 1.5rem 1.25rem; color: var(--text-muted); font-size: 0.95rem; font-weight: 700;">{{ $student->latest_assessment->assessment_date->format('M d, Y') }}</td>
                    <td style="padding: 1.5rem 1.25rem; text-align: right;">
                        <a href="{{ route('counselor.students.show', $student->user_id) }}" class="btn-primary" style="padding: 0.65rem 1.25rem; font-size: 0.85rem;">Clinical Profile</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 5rem 2rem; text-align: center;">
                        <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.1;">🧘</div>
                        <div style="font-weight: 800; color: var(--text-dim); font-size: 1.15rem; letter-spacing: -0.01em;">Intervention queue optimized. No pending critical actions.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Anonymous Voice (Premium Feed) -->
    <div class="staggered">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem;">
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 900; color: var(--text); margin-bottom: 0.5rem; letter-spacing: -0.03em;">Patient Voice Feed</h2>
                <p style="color: var(--text-muted); font-weight: 600; font-size: 1rem;">Direct secure communication via anonymous reflection notes.</p>
            </div>
            <div class="glass-2" style="padding: 0.6rem 1.25rem; border-radius: 12px; font-weight: 800; font-size: 0.75rem; color: var(--primary);">
                PROTECTED DIALOGS: {{ count($anon_notes) }}
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(500px, 1fr)); gap: 2rem;">
            @foreach ($anon_notes as $note)
            <div class="dialog-vault-premium staggered">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 2px solid var(--border); padding-bottom: 1.25rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 12px; height: 12px; border-radius: 50%; background: var(--primary); box-shadow: 0 0 10px var(--primary-glow);"></div>
                        <span style="font-weight: 900; font-size: 0.8rem; letter-spacing: 0.1em; color: var(--text); text-transform: uppercase;">SESSION ID #{{ str_pad($note->note_id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div style="display: flex; gap: 0.75rem;">
                        <span class="clinical-indicator" style="background: var(--surface-solid); border: 1.5px solid var(--border); color: var(--text-muted);">
                            {{ $note->status }}
                        </span>
                        <a href="?archive_note={{ $note->note_id }}" style="text-decoration: none; padding: 0.45rem 0.85rem; border-radius: 100px; background: rgba(244, 63, 94, 0.1); font-size: 0.7rem; font-weight: 800; color: var(--accent); text-transform: uppercase;">Archive</a>
                    </div>
                </div>

                <div style="max-height: 350px; overflow-y: auto; padding-right: 1.5rem; margin-bottom: 2.5rem;" class="custom-scrollbar">
                    @foreach ($note->messages as $msg)
                    <div style="margin-bottom: 2rem; display: flex; flex-direction: column; align-items: {{ ($msg->sender_type === 'student') ? 'flex-start' : 'flex-end' }};">
                        <div style="font-weight: 900; font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.6rem; letter-spacing: 0.08em;">
                            {{ ($msg->sender_type === 'student') ? 'Patient Voice' : 'Clinical Response' }}
                        </div>
                        <div class="message-bubble-premium" style="{{ ($msg->sender_type === 'student') ? 'background: var(--surface-solid); border: 1.5px solid var(--border); color: var(--text); border-bottom-left-radius: 4px;' : 'background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border-bottom-right-radius: 4px;' }}">
                            {{ $msg->message_text }}
                        </div>
                    </div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('counselor.notes.reply', $note->note_id) }}" style="display: flex; flex-direction: column; gap: 1.25rem;">
                    @csrf
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label style="font-weight: 900; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Draft Clinical Response</label>
                        <button type="button" onclick="suggestReply({{ $note->note_id }})" style="background: none; border: none; color: var(--primary); font-weight: 900; font-size: 0.75rem; cursor: pointer; text-transform: uppercase; letter-spacing: 0.05em;">✨ Assist Response</button>
                    </div>
                    <textarea name="message" id="reply_textarea_{{ $note->note_id }}" placeholder="Type high-level institutional response..." class="form-input" style="width: 100%; border-radius: 18px; border: 2px solid var(--border); resize: none; height: 110px; padding: 1.25rem; font-size: 0.95rem; background: var(--surface-solid);" required></textarea>
                    <button type="submit" class="btn-primary" style="padding: 1rem; font-weight: 800; font-size: 1rem;">Transmit Response →</button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function suggestReply(noteId) {
    const textarea = document.getElementById('reply_textarea_' + noteId);
    if (!textarea) return;

    // Pulse effect while loading
    gsap.to(textarea, { opacity: 0.5, duration: 0.3, repeat: -1, yoyo: true });

    // Get the last student message text
    const bubbles = textarea.closest('.dialog-vault-premium').querySelectorAll('.message-bubble-premium');
    let studentMsg = "";
    for (let i = bubbles.length - 1; i >= 0; i--) {
        if (bubbles[i].textContent && bubbles[i].style.background.includes('var(--surface-solid)')) {
            studentMsg = bubbles[i].textContent.trim();
            break;
        }
    }

    if (!studentMsg) {
        gsap.to(textarea, { opacity: 1, duration: 0.3 });
        return;
    }

    try {
        const res = await fetch("{{ route('counselor.ai.suggest') }}", {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({ note_text: studentMsg })
        });
        const data = await res.json();
        gsap.to(textarea, { opacity: 1, duration: 0.3 });
        if (data.success) {
            textarea.value = data.suggestion;
            textarea.focus();
            gsap.from(textarea, { y: 10, opacity: 0, duration: 0.5, ease: "back.out" });
        }
    } catch(err) {
        gsap.to(textarea, { opacity: 1, duration: 0.3 });
        console.error('AI suggestion failed', err);
    }
}
</script>
@endpush
