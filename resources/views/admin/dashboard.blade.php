@extends('layouts.app')

@push('styles')
<style>
    .admin-command-matrix {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 4rem;
    }

    .admin-main-grid {
        display: block;
    }

    .ai-system-pulse-container {
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: 40px;
        padding: 3rem;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
    }

    .pulse-ring {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 300px;
        height: 300px;
        border: 2px solid var(--primary-light);
        border-radius: 50%;
        opacity: 0;
        animation: pulse-ring 4s infinite;
    }

    @keyframes pulse-ring {
        0% { transform: translate(-50%, -50%) scale(0.5); opacity: 0.5; }
        100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; }
    }

    .admin-card-premium {
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: 32px;
        padding: 2rem;
        transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }

    .admin-card-premium:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary-light);
    }

    .status-orb {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 0.5rem;
    }

    .orb-active { background: #10b981; box-shadow: 0 0 15px #10b981; animation: blink 2s infinite; }

    @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }
    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5); }
        70% { box-shadow: 0 0 0 14px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    @media (max-width: 768px) {
        .container {
            padding: 1rem 0.75rem 4rem !important;
        }
        .admin-command-matrix {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 0.75rem !important;
            margin-bottom: 2rem !important;
        }
        .admin-card-premium {
            padding: 1.25rem 1rem !important;
            border-radius: 20px !important;
        }
        .ai-system-pulse-container {
            padding: 1.5rem 1rem !important;
            border-radius: 24px !important;
        }
        div[style*="grid-template-columns: repeat(auto-fill, minmax(400px, 1fr))"] {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
        }
    }
    @media (max-width: 480px) {
        .admin-command-matrix {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 0.7rem !important;
        }
        .admin-card-premium div[style*="font-size: 3.5rem"] {
            font-size: 2rem !important;
        }
    }
    @media (max-width: 768px) {
        /* Priority table → stacked full-width cards */
        .ai-system-pulse-container table,
        .ai-system-pulse-container tbody,
        .ai-system-pulse-container tr,
        .ai-system-pulse-container td {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .ai-system-pulse-container thead {
            display: none !important;
        }
        .ai-system-pulse-container tbody tr {
            background: var(--surface-solid) !important;
            border: 1px solid var(--border) !important;
            border-radius: 18px !important;
            padding: 1rem !important;
            margin-bottom: 0.85rem !important;
        }
        .ai-system-pulse-container tbody tr td {
            padding: 0.3rem 0 !important;
            border: none !important;
            text-align: left !important;
        }
        .ai-system-pulse-container tbody tr td:last-child {
            padding-top: 0.7rem !important;
        }
        .ai-system-pulse-container tbody tr td:last-child a {
            display: flex !important;
            justify-content: center !important;
            background: var(--primary-glow) !important;
            border-radius: 12px !important;
            padding: 0.7rem !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1400px; margin: 0 auto; padding: 2.5rem 2rem 5rem;">
    
    <!-- Admin Header -->
    <header class="staggered" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 4.5rem;">
        <div>
            <div style="font-weight: 900; color: var(--primary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.25em; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.6rem;">
                <span class="status-orb orb-active"></span> School Support Overview
            </div>
            <p style="color: var(--text-muted); font-size: 1.25rem; font-weight: 500; margin-top: 0.75rem;">Care for students and the support team, all in one place.</p>
        </div>
    </header>

    <!-- Matrix Cards -->
    <div class="admin-command-matrix staggered">
        <div class="admin-card-premium">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div style="font-size: 0.7rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em;">Total Students</div>
                <i class="ph-bold ph-users-four" style="font-size: 1.5rem; color: var(--primary); opacity: 0.4;"></i>
            </div>
            <div style="font-size: 3.5rem; font-weight: 950; color: var(--text); line-height: 1; letter-spacing: -0.05em;">{{ $stats['total_users'] }}</div>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 1rem; font-weight: 700;">Registered Students</div>
        </div>

        <div class="admin-card-premium">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div style="font-size: 0.7rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em;">Counselors</div>
                <i class="ph-bold ph-stethoscope" style="font-size: 1.5rem; color: #6366f1; opacity: 0.4;"></i>
            </div>
            <div style="font-size: 3.5rem; font-weight: 950; color: #6366f1; line-height: 1; letter-spacing: -0.05em;">{{ $stats['counselors_count'] }}</div>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 1rem; font-weight: 700;">Support Team Members</div>
        </div>

        <div class="admin-card-premium">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div style="font-size: 0.7rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em;">Appointments</div>
                <i class="ph-bold ph-calendar-check" style="font-size: 1.5rem; color: #f59e0b; opacity: 0.4;"></i>
            </div>
            <div style="font-size: 3.5rem; font-weight: 950; color: #f59e0b; line-height: 1; letter-spacing: -0.05em;">{{ $stats['total_appointments'] }}</div>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 1rem; font-weight: 700;">Sessions Booked</div>
        </div>

        <div class="admin-card-premium" style="background: rgba(239, 68, 68, 0.03); border-color: rgba(239, 68, 68, 0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div style="font-size: 0.7rem; font-weight: 900; color: #ef4444; text-transform: uppercase; letter-spacing: 0.1em;">Needs Care Now</div>
                <i class="ph-bold ph-warning-octagon" style="font-size: 1.5rem; color: #ef4444; opacity: 0.4;"></i>
            </div>
            <div style="font-size: 3.5rem; font-weight: 950; color: #ef4444; line-height: 1; letter-spacing: -0.05em;">{{ $stats['critical_vector'] }}</div>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 1rem; font-weight: 700;">Students Needing Urgent Support</div>
        </div>
    </div>

    <div class="admin-main-grid staggered">
        <!-- Priority Oversight -->
        <div class="ai-system-pulse-container">
            <div class="pulse-ring"></div>
            <div class="pulse-ring" style="animation-delay: 2s;"></div>
            
            <header style="position: relative; z-index: 2; margin-bottom: 3.5rem; border-bottom: 1px solid var(--border); padding-bottom: 2rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 900; color: var(--text); margin: 0; letter-spacing: -0.04em;">Students to Prioritize</h2>
                <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 600; margin-top: 0.5rem;">Students who need support first, based on their latest check-in.</p>
            </header>

            <div style="position: relative; z-index: 2; overflow: hidden; border-radius: 24px; background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(10px); border: 1px solid var(--border);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--surface-2); color: var(--text-dim); font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.15em;">
                            <th style="padding: 1.5rem 2rem; text-align: left;">Student</th>
                            <th style="padding: 1.5rem 2rem; text-align: left;">How They're Doing</th>
                            <th style="padding: 1.5rem 2rem; text-align: left;">Date</th>
                            <th style="padding: 1.5rem 2rem; text-align: right;">View</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($priority_queue as $student)
                        <tr style="border-bottom: 1px solid var(--border); transition: background 0.3s ease;">
                            <td style="padding: 1.5rem 2rem;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div style="width: 44px; height: 44px; border-radius: 12px; background: var(--primary-glow); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.1rem; border: 1.5px solid var(--primary-light);">
                                        {{ strtoupper(substr($student->full_name, 0, 1)) }}
                                    </div>
                                    <div style="font-weight: 800; color: var(--text);">{{ $student->full_name }}</div>
                                </div>
                            </td>
                            <td style="padding: 1.5rem 2rem;">
                                @php
                                    $risk = $student->latestAssessment?->risk_level ?? 'Moderate';
                                    $color = $risk === 'Critical' ? '#ef4444' : ($risk === 'High' ? '#f97316' : '#f59e0b');
                                    $bg = $risk === 'Critical' ? 'rgba(239, 68, 68, 0.1)' : ($risk === 'High' ? 'rgba(249, 115, 22, 0.1)' : 'rgba(245, 158, 11, 0.1)');
                                @endphp
                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.45rem 1rem; border-radius: 100px; background: {{ $bg }}; color: {{ $color }}; font-weight: 900; font-size: 0.7rem; text-transform: uppercase; border: 1.5px solid {{ $color }}40;">
                                    {{ $risk }}
                                </span>
                            </td>
                            <td style="padding: 1.5rem 2rem; color: var(--text-dim); font-size: 0.95rem; font-weight: 750; font-family: 'Outfit', sans-serif;">
                                {{ $student->latestAssessment?->assessment_date?->format('M d, Y') ?? 'N/A' }}
                            </td>
                            <td style="padding: 1.5rem 2rem; text-align: right; white-space: nowrap;">
                                @php $rowRisk = $student->latestAssessment?->risk_level ?? ''; @endphp
                                @if(in_array($rowRisk, ['High', 'Critical'], true))
                                    <button onclick="openCallModal({{ $student->user_id }}, '{{ e($student->full_name) }}', '{{ $rowRisk }}')" title="Start a video call with this student now" style="background: #ef4444; color: white; border: none; padding: 0.55rem 1rem; border-radius: 10px; font-weight: 800; font-size: 0.72rem; text-transform: uppercase; cursor: pointer; margin-right: 0.5rem;">
                                        📞 Call
                                    </button>
                                @endif
                                <a href="{{ route('counselor.students.show', $student->user_id) }}" style="color: var(--primary); font-weight: 900; font-size: 0.75rem; text-transform: uppercase; text-decoration: none; border-bottom: 2px solid transparent; transition: all 0.3s ease;" onmouseover="this.style.borderBottomColor='var(--primary)'" onmouseout="this.style.borderBottomColor='transparent'">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="padding: 6rem 2rem; text-align: center; color: var(--text-dim); font-weight: 700; font-size: 1.1rem;">All clear. No students need priority support right now.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('components.simple-pager', ['paginator' => $priority_queue, 'label' => 'students'])
        </div>


    </div>

    <!-- Student Voice Feed -->
    <div class="staggered" style="margin-top: 4rem;">
        <header style="margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 900; color: var(--text); margin: 0; letter-spacing: -0.04em;">Student Voice</h2>
                <p style="color: var(--text-muted); font-size: 1rem; font-weight: 500; margin-top: 0.35rem;">Direct student check-ins and support messages.</p>
            </div>
            <div style="background: var(--surface-2); padding: 0.5rem 1rem; border-radius: 100px; font-weight: 800; font-size: 0.75rem; color: var(--primary);">
                {{ $anon_notes->total() }} ACTIVE DIALOGS
            </div>
        </header>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 1.5rem;">
            @foreach($anon_notes as $note)
                <div style="background: white; border: 1px solid var(--border); border-radius: 24px; padding: 1.75rem; box-shadow: 0 10px 20px rgba(0,0,0,0.02);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                        <span style="font-size: 0.65rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.1em;">{{ $note->student?->full_name ?? 'SYSTEM' }}</span>
                        <span style="font-size: 0.65rem; color: var(--text-muted); font-weight: 700;">{{ $note->created_at->diffForHumans() }}</span>
                    </div>
                    <div style="font-size: 0.92rem; line-height: 1.6; color: var(--text); font-weight: 500; min-height: 3.2rem;">
                        {{ \Illuminate\Support\Str::limit($note->messages->where('sender_type', 'student')->last()?->message_text, 120) }}
                    </div>
                    <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; gap: 0.4rem;">
                            @php $c = count($note->messages); @endphp
                            <span style="font-size: 0.72rem; font-weight: 800; color: var(--text-muted);">{{ $c }} msg</span>
                        </div>
                        <a href="{{ route('counselor.dashboard') }}" style="font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase; text-decoration: none;">Message →</a>
                    </div>
                </div>
            @endforeach
        </div>
        @include('components.simple-pager', ['paginator' => $anon_notes, 'label' => 'messages'])
    </div>
</div>

<!-- Call Student Confirm Modal -->
<div id="callModal" style="display: none; position: fixed; inset: 0; z-index: 10000; background: rgba(15,23,42,0.55); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 1.5rem;" onclick="if(event.target===this)closeCallModal()">
    <div style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 28px; padding: 2.5rem; max-width: 440px; width: 100%; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3);">
        <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(239,68,68,0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1.25rem; animation: pulse-red 2s infinite;">
            <i class="ph-bold ph-phone-call"></i>
        </div>
        <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.4rem; font-weight: 900; color: var(--text); margin: 0 0 0.5rem;">Call this student now?</h3>
        <p style="color: var(--text); font-weight: 800; font-size: 1.05rem; margin: 0 0 0.25rem;" id="callModalName">Student</p>
        <p style="margin: 0 0 1rem;"><span id="callModalRisk" style="padding: 0.3rem 0.85rem; border-radius: 100px; font-weight: 800; font-size: 0.68rem; text-transform: uppercase;">Critical</span></p>
        <p style="color: var(--text-dim); font-size: 0.9rem; line-height: 1.6; margin: 0 0 2rem;">They will be notified immediately and asked to join your video call. Only written safety notes are kept if they chose not to record.</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <button onclick="closeCallModal()" style="padding: 0.9rem; border-radius: 14px; font-weight: 800; cursor: pointer; background: var(--surface-2); border: 1.5px solid var(--border); color: var(--text);">Cancel</button>
            <button id="callModalConfirm" onclick="confirmCallStudent()" style="padding: 0.9rem; border-radius: 14px; font-weight: 800; cursor: pointer; background: #ef4444; border: none; color: white;">📞 Call Now</button>
        </div>
    </div>
</div>

<!-- Student Offline Notice -->
<div id="offlineModal" style="display: none; position: fixed; inset: 0; z-index: 10001; background: rgba(2,6,23,0.7); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 1.5rem;" onclick="if(event.target===this)document.getElementById('offlineModal').style.display='none'">
    <div style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 28px; padding: 2.5rem; max-width: 460px; width: 100%; text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3);">
        <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(100,116,139,0.12); color: #64748b; display: flex; align-items: center; justify-content: center; font-size: 2.25rem; margin: 0 auto 1.25rem;">
            <i class="ph-bold ph-user-minus"></i>
        </div>
        <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.4rem; font-weight: 900; color: #b91c1c; margin: 0 0 0.5rem;">Student Is Not Online</h3>
        <p style="color: var(--text); font-weight: 700; margin: 0 0 0.75rem;" id="offlineModalName">This student</p>
        <p style="color: var(--text-dim); font-size: 0.9rem; line-height: 1.6; margin: 0 0 0.5rem;">They haven't opened the portal recently, so the call can't connect right now — just like students see "No Counselor Currently Online" when no counselor is around.</p>
        <p style="color: var(--text-dim); font-size: 0.9rem; line-height: 1.6; margin: 0 0 2rem;">We've already notified them by email and in-app alert. Meanwhile you can send them a Quick Note or set an appointment.</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <button onclick="document.getElementById('offlineModal').style.display='none'" style="padding: 0.9rem; border-radius: 14px; font-weight: 800; cursor: pointer; background: var(--surface-2); border: 1.5px solid var(--border); color: var(--text);">Close</button>
            <a href="{{ route('counselor.notes.index') }}" style="padding: 0.9rem; border-radius: 14px; font-weight: 800; background: var(--primary); color: white; text-decoration: none; display: flex; align-items: center; justify-content: center;">Send a Quick Note</a>
        </div>
    </div>
</div>

<script>
let pendingCall = { studentId: null, studentName: '', risk: '' };

function openCallModal(studentId, studentName, risk) {
    pendingCall = { studentId, studentName, risk };
    document.getElementById('callModalName').textContent = studentName;
    const riskBadge = document.getElementById('callModalRisk');
    riskBadge.textContent = risk;
    riskBadge.style.background = risk === 'Critical' ? 'rgba(239,68,68,0.12)' : 'rgba(249,115,22,0.12)';
    riskBadge.style.color = risk === 'Critical' ? '#ef4444' : '#f97316';
    riskBadge.style.border = '1px solid ' + (risk === 'Critical' ? 'rgba(239,68,68,0.35)' : 'rgba(249,115,22,0.35)');
    document.getElementById('callModal').style.display = 'flex';
}

function closeCallModal() {
    document.getElementById('callModal').style.display = 'none';
    pendingCall = { studentId: null, studentName: '', risk: '' };
}

async function confirmCallStudent() {
    const { studentId } = pendingCall;
    if (!studentId) return;
    const btn = document.getElementById('callModalConfirm');
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Calling…';
    try {
        const res = await fetch("{{ url('/counselor/emergency-calls/call-student') }}/" + studentId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success && data.redirect) {
            window.location.href = data.redirect;
        } else if (data.offline) {
            closeCallModal();
            document.getElementById('offlineModalName').textContent = pendingCall.studentName || 'This student';
            document.getElementById('offlineModal').style.display = 'flex';
            btn.disabled = false;
            btn.innerHTML = original;
        } else {
            App.toast({ type: 'error', title: 'Cannot Call', message: data.error || 'Could not start the call.' });
            btn.disabled = false;
            btn.innerHTML = original;
        }
    } catch (e) {
        App.toast({ type: 'error', title: 'Network Error', message: 'Could not reach the server.' });
        btn.disabled = false;
        btn.innerHTML = original;
    }
}
document.addEventListener('DOMContentLoaded', () => {
    if (window.gsap) {
        gsap.from('.staggered', { y: 60, opacity: 0, duration: 1.4, stagger: 0.2, ease: "expo.out", clearProps: "all" });
        
        // Premium Card Interaction
        document.querySelectorAll('.admin-card-premium').forEach(card => {
            card.addEventListener('mouseenter', () => {
                gsap.to(card, { y: -10, scale: 1.02, boxShadow: "0 30px 60px rgba(0,0,0,0.1)", duration: 0.4, ease: "power2.out" });
            });
            card.addEventListener('mouseleave', () => {
                gsap.to(card, { y: 0, scale: 1, boxShadow: "var(--shadow-sm)", duration: 0.6, ease: "elastic.out(1, 0.3)" });
            });
        });
    }
});
</script>
@endsection
