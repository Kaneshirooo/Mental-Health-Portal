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
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1400px; margin: 0 auto; padding: 2.5rem 2rem 5rem;">
    
    <!-- Admin Header -->
    <header class="staggered" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 4.5rem;">
        <div>
            <div style="font-weight: 900; color: var(--primary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.25em; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.6rem;">
                <span class="status-orb orb-active"></span> Institutional Administration
            </div>
            <p style="color: var(--text-muted); font-size: 1.25rem; font-weight: 500; margin-top: 0.75rem;">System-wide clinical oversight and personnel management.</p>
        </div>
    </header>

    <!-- Matrix Cards -->
    <div class="admin-command-matrix staggered">
        <div class="admin-card-premium">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div style="font-size: 0.7rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em;">Total Registry</div>
                <i class="ph-bold ph-users-four" style="font-size: 1.5rem; color: var(--primary); opacity: 0.4;"></i>
            </div>
            <div style="font-size: 3.5rem; font-weight: 950; color: var(--text); line-height: 1; letter-spacing: -0.05em;">{{ $stats['total_users'] }}</div>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 1rem; font-weight: 700;">Managed Identities</div>
        </div>

        <div class="admin-card-premium">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div style="font-size: 0.7rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em;">Clinical Staff</div>
                <i class="ph-bold ph-stethoscope" style="font-size: 1.5rem; color: #6366f1; opacity: 0.4;"></i>
            </div>
            <div style="font-size: 3.5rem; font-weight: 950; color: #6366f1; line-height: 1; letter-spacing: -0.05em;">{{ $stats['counselors_count'] }}</div>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 1rem; font-weight: 700;">Active Providers</div>
        </div>

        <div class="admin-card-premium">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div style="font-size: 0.7rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em;">Interventions</div>
                <i class="ph-bold ph-calendar-check" style="font-size: 1.5rem; color: #f59e0b; opacity: 0.4;"></i>
            </div>
            <div style="font-size: 3.5rem; font-weight: 950; color: #f59e0b; line-height: 1; letter-spacing: -0.05em;">{{ $stats['total_appointments'] }}</div>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 1rem; font-weight: 700;">Nodes Scheduled</div>
        </div>

        <div class="admin-card-premium" style="background: rgba(239, 68, 68, 0.03); border-color: rgba(239, 68, 68, 0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div style="font-size: 0.7rem; font-weight: 900; color: #ef4444; text-transform: uppercase; letter-spacing: 0.1em;">Critical Vector</div>
                <i class="ph-bold ph-warning-octagon" style="font-size: 1.5rem; color: #ef4444; opacity: 0.4;"></i>
            </div>
            <div style="font-size: 3.5rem; font-weight: 950; color: #ef4444; line-height: 1; letter-spacing: -0.05em;">{{ $stats['critical_vector'] }}</div>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 1rem; font-weight: 700;">Priority Risk Alerts</div>
        </div>
    </div>

    <div class="admin-main-grid staggered">
        <!-- Priority Oversight -->
        <div class="ai-system-pulse-container">
            <div class="pulse-ring"></div>
            <div class="pulse-ring" style="animation-delay: 2s;"></div>
            
            <header style="position: relative; z-index: 2; margin-bottom: 3.5rem; border-bottom: 1px solid var(--border); padding-bottom: 2rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 900; color: var(--text); margin: 0; letter-spacing: -0.04em;">Institutional Triage</h2>
                <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 600; margin-top: 0.5rem;">Managed overview of clinical priority escalations.</p>
            </header>

            <div style="position: relative; z-index: 2; overflow: hidden; border-radius: 24px; background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(10px); border: 1px solid var(--border);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--surface-2); color: var(--text-dim); font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.15em;">
                            <th style="padding: 1.5rem 2rem; text-align: left;">Identity</th>
                            <th style="padding: 1.5rem 2rem; text-align: left;">Risk Matrix</th>
                            <th style="padding: 1.5rem 2rem; text-align: left;">Temporal</th>
                            <th style="padding: 1.5rem 2rem; text-align: right;">Action</th>
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
                            <td style="padding: 1.5rem 2rem; text-align: right;">
                                <a href="{{ route('counselor.students.show', $student->user_id) }}" style="color: var(--primary); font-weight: 900; font-size: 0.75rem; text-transform: uppercase; text-decoration: none; border-bottom: 2px solid transparent; transition: all 0.3s ease;" onmouseover="this.style.borderBottomColor='var(--primary)'" onmouseout="this.style.borderBottomColor='transparent'">View Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="padding: 6rem 2rem; text-align: center; color: var(--text-dim); font-weight: 700; font-size: 1.1rem;">Matrix Clean. No priority cases flagged.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


    </div>

    <!-- Patient Voice Feed Integration -->
    <div class="staggered" style="margin-top: 4rem;">
        <header style="margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 900; color: var(--text); margin: 0; letter-spacing: -0.04em;">Patient Voice Feed</h2>
                <p style="color: var(--text-muted); font-size: 1rem; font-weight: 500; margin-top: 0.35rem;">Direct student check-ins and clinical inquiries.</p>
            </div>
            <div style="background: var(--surface-2); padding: 0.5rem 1rem; border-radius: 100px; font-weight: 800; font-size: 0.75rem; color: var(--primary);">
                {{ count($anon_notes) }} ACTIVE DIALOGS
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
                        <a href="{{ route('counselor.dashboard') }}" style="font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase; text-decoration: none;">Join Thread →</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
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
