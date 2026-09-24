@extends('layouts.app')


@push('styles')
<style>
    /* ── Design tokens ── */
    :root {
        --clinical-accent: #10b981;
        --clinical-purple: #6366f1;
        --clinical-rose:   #f43f5e;
        --clinical-amber:  #f59e0b;
    }

    /* ── Ambient orb background ── */
    .dash-orb {
        position: fixed;
        border-radius: 50%;
        filter: blur(120px);
        pointer-events: none;
        z-index: 0;
        animation: orb-drift 12s ease-in-out infinite alternate;
    }
    .dash-orb-1 { width:600px;height:600px;background:rgba(16,185,129,0.08);top:-180px;right:-120px;animation-delay:0s; }
    .dash-orb-2 { width:500px;height:500px;background:rgba(5,150,105,0.05);bottom:-100px;left:-80px;animation-delay:-5s; }
    @keyframes orb-drift {
        from { transform: translate(0,0) scale(1); }
        to   { transform: translate(40px,30px) scale(1.08); }
    }

    /* ── Layout ── */
    .dash-content { position: relative; z-index: 1; }

    /* ── Stat cards ── */
    .triage-matrix-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }
    .stat-card {
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: 28px;
        padding: 2rem 1.75rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.35s cubic-bezier(0.16,1,0.3,1), box-shadow 0.35s ease;
        cursor: default;
    }
    .stat-card::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        opacity: 0;
        transition: opacity 0.35s ease;
        background: radial-gradient(ellipse at top left, var(--card-glow, rgba(16,185,129,0.08)) 0%, transparent 70%);
    }
    .stat-card:hover { transform: translateY(-6px); box-shadow: 0 24px 48px rgba(0,0,0,0.12); }
    .stat-card:hover::after { opacity: 1; }
    .stat-card-bar {
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: 28px 28px 0 0;
    }
    .stat-icon {
        width: 48px; height: 48px;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
    }
    .stat-value {
        font-size: 2.75rem;
        font-weight: 950;
        line-height: 1;
        letter-spacing: -0.05em;
        font-family: 'Outfit', sans-serif;
    }
    .stat-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-dim);
        margin-top: 0.75rem;
    }
    .stat-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 0.25rem 0.65rem;
        border-radius: 100px;
        margin-top: 1rem;
    }

    /* ── AI badge ── */
    .ai-insight-badge {
        background: rgba(16,185,129,0.1);
        color: var(--primary);
        padding: 0.45rem 1rem;
        border-radius: 100px;
        font-size: 0.65rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid rgba(16,185,129,0.25);
        backdrop-filter: blur(8px);
    }
    .pulse-ai {
        width: 7px; height: 7px;
        background: var(--primary);
        border-radius: 50%;
        animation: ai-pulse 2s infinite;
    }
    @keyframes ai-pulse {
        0%   { transform:scale(1);   box-shadow: 0 0 0 0   rgba(16,185,129,0.5); }
        70%  { transform:scale(1.2); box-shadow: 0 0 0 8px rgba(16,185,129,0);   }
        100% { transform:scale(1);   box-shadow: 0 0 0 0   rgba(16,185,129,0);   }
    }

    /* ── Triage panel ── */
    .clinical-control-panel {
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: 36px;
        padding: 2.5rem;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
    }
    .clinical-control-panel::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, var(--clinical-accent) 0%, var(--clinical-purple) 50%, var(--clinical-rose) 100%);
    }

    /* ── Table rows ── */
    .priority-row {
        transition: background 0.25s ease, transform 0.25s ease;
        cursor: pointer;
    }
    .priority-row:hover {
        background: var(--surface-2) !important;
        transform: translateX(4px);
    }

    /* ── Avatar initials ── */
    .avatar-init {
        width: 46px; height: 46px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 900;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    /* ── Risk pill ── */
    .risk-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 1rem;
        border-radius: 100px;
        font-weight: 900;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-width: 1.5px;
        border-style: solid;
    }

    /* ── Note cards ── */
    .note-card {
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: 32px;
        padding: 2.25rem;
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.35s ease, transform 0.35s ease;
        position: relative;
        overflow: hidden;
    }
    .note-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 2px;
        background: linear-gradient(90deg, var(--clinical-accent), var(--clinical-purple));
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .note-card:hover { box-shadow: 0 20px 48px rgba(0,0,0,0.1); transform: translateY(-4px); }
    .note-card:hover::before { opacity: 1; }

    /* ── Chat bubbles ── */
    .bubble-student {
        background: var(--surface-2);
        border: 1px solid var(--border);
        color: var(--text);
        border-bottom-left-radius: 6px;
        padding: 1rem 1.25rem;
        border-radius: 20px;
        font-size: 0.95rem;
        line-height: 1.6;
        max-width: 90%;
        font-weight: 500;
    }
    .bubble-counselor {
        background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
        color: white;
        border-bottom-right-radius: 6px;
        padding: 1rem 1.25rem;
        border-radius: 20px;
        font-size: 0.95rem;
        line-height: 1.6;
        max-width: 90%;
        font-weight: 500;
        box-shadow: 0 4px 16px rgba(5,150,105,0.25);
    }

    /* ── Buttons ── */
    .btn-tts {
        background: rgba(16,185,129,0.1) !important;
        border: 1px solid rgba(16,185,129,0.25) !important;
        width: 44px !important; height: 44px !important;
        border-radius: 14px !important;
        color: var(--primary) !important;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
        transition: all 0.25s ease;
    }
    .btn-tts:hover { background: rgba(16,185,129,0.2) !important; transform: scale(1.08); }

    .btn-ai-assist {
        background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
        border: none !important;
        border-radius: 100px !important;
        color: white !important;
        font-weight: 800 !important;
        font-size: 0.72rem !important;
        padding: 0.5rem 1.1rem !important;
        cursor: pointer;
        display: flex; align-items: center; gap: 0.4rem;
        box-shadow: 0 4px 14px rgba(99,102,241,0.35);
        transition: all 0.25s ease;
    }
    .btn-ai-assist:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(99,102,241,0.4); }

    /* ── Section divider ── */
    .section-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.65rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        color: var(--text-dim);
        margin-bottom: 2rem;
    }
    .section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    /* ── Live clock ── */
    #liveClock {
        font-family: 'Outfit', monospace;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-dim);
        letter-spacing: 0.05em;
    }

    /* ── Score bar ── */
    .score-track {
        width: 90px; height: 8px;
        background: var(--surface-2);
        border-radius: 100px;
        overflow: hidden;
        border: 1px solid var(--border);
    }
    .score-fill-bar {
        height: 100%;
        border-radius: 100px;
        transition: width 1.2s cubic-bezier(0.34,1.56,0.64,1);
    }

    /* ── Section header ── */
    .section-header-line {
        width: 4px; height: 28px;
        border-radius: 4px;
        background: linear-gradient(to bottom, var(--clinical-accent), var(--clinical-purple));
        flex-shrink: 0;
    }

    @media (max-width: 1200px) { .triage-matrix-container { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 640px)  { .triage-matrix-container { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<!-- Ambient orbs -->
<div class="dash-orb dash-orb-1"></div>
<div class="dash-orb dash-orb-2"></div>

<div class="container dash-content" style="max-width: 1400px; margin: 0 auto; padding: 2rem 2rem 5rem;">

    <!-- ── Premium Header ── -->
    <header class="staggered" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
        <div>
            <div class="ai-insight-badge" style="margin-bottom: 0.9rem;">
                <div class="pulse-ai"></div>
                AI Core Active &mdash; GPT-4o
            </div>
            <h1 style="font-family:'Outfit',sans-serif; font-size:2.9rem; font-weight:900; letter-spacing:-0.05em; line-height:1.05; margin:0;"
                class="text-gradient">Clinical Triage
                <span style="display:block; font-size:1rem; font-weight:500; letter-spacing:0; color:var(--text-muted); margin-top:0.4rem;">Advanced institutional oversight &amp; predictive wellness monitoring</span>
            </h1>
        </div>

        <div style="display:flex; flex-direction:column; align-items:flex-end; gap:1rem;">
            <span id="liveClock">--:-- --</span>
            <div style="display:flex; gap:0.85rem; align-items:center;">
                <button id="emergencyToggleBtn" onclick="toggleEmergencyStatus()"
                        style="padding:0.9rem 1.6rem; border-radius:18px; font-weight:800; text-transform:uppercase; font-size:0.78rem; letter-spacing:0.05em; border:none; cursor:pointer; transition:all 0.35s cubic-bezier(0.175,0.885,0.32,1.275); display:flex; align-items:center; gap:0.6rem;
                        {{ auth()->user()->is_emergency_available ? 'background:#10b981;color:white;box-shadow:0 8px 20px rgba(16,185,129,0.3);' : 'background:#ef4444;color:white;box-shadow:0 8px 20px rgba(239,68,68,0.3);' }}"
                        onmouseover="this.style.transform='translateY(-3px)'"
                        onmouseout="this.style.transform='translateY(0)'">
                    <i class="ph-bold {{ auth()->user()->is_emergency_available ? 'ph-shield-check' : 'ph-shield-slash' }}" style="font-size:1.15rem;"></i>
                    <span id="emergencyStatusText">{{ auth()->user()->is_emergency_available ? 'Available' : 'Unavailable' }}</span>
                </button>
                <a href="{{ route('counselor.appointments.index') }}" class="btn-primary"
                   style="padding:0.9rem 1.75rem; border-radius:18px; font-weight:800; text-transform:uppercase; font-size:0.78rem; letter-spacing:0.05em; text-decoration:none; display:flex; align-items:center; gap:0.6rem; box-shadow:0 8px 20px rgba(5,150,105,0.2);">
                    <i class="ph-bold ph-calendar-blank" style="font-size:1.15rem;"></i> Schedule
                </a>
            </div>
        </div>
    </header>

    <!-- ── Stat Cards ── -->
    <div class="triage-matrix-container staggered">

        <!-- Card 1: Caseload -->
        <div class="stat-card" style="--card-glow: rgba(16,185,129,0.08);">
            <div class="stat-card-bar" style="background: linear-gradient(90deg,#10b981,#059669);"></div>
            <div class="stat-icon" style="background:rgba(16,185,129,0.1); color:#10b981;">
                <i class="ph-bold ph-users-four"></i>
            </div>
            <div class="stat-value" style="color:var(--text);">{{ $stats['total_students'] }}</div>
            <div class="stat-label">Clinical Caseload</div>
            <div class="stat-tag" style="background:rgba(16,185,129,0.08); color:#10b981;">
                <i class="ph-bold ph-dot-outline"></i> Total monitored
            </div>
        </div>

        <!-- Card 2: Pending -->
        <div class="stat-card" style="--card-glow: rgba(99,102,241,0.08);">
            <div class="stat-card-bar" style="background: linear-gradient(90deg,#6366f1,#4f46e5);"></div>
            <div class="stat-icon" style="background:rgba(99,102,241,0.1); color:#6366f1;">
                <i class="ph-bold ph-activity"></i>
            </div>
            <div class="stat-value" style="color:#6366f1;">{{ $stats['pending_triages'] }}</div>
            <div class="stat-label">Pending Validation</div>
            <div class="stat-tag" style="background:rgba(99,102,241,0.08); color:#6366f1;">
                <i class="ph-bold ph-clock"></i> Awaiting action
            </div>
        </div>

        <!-- Card 3: Critical -->
        <div class="stat-card" style="--card-glow: rgba(239,68,68,0.08); background:rgba(239,68,68,0.02); border-color:rgba(239,68,68,0.12);">
            <div class="stat-card-bar" style="background: linear-gradient(90deg,#ef4444,#dc2626);"></div>
            <div class="stat-icon" style="background:rgba(239,68,68,0.1); color:#ef4444;">
                <i class="ph-bold ph-warning-octagon"></i>
            </div>
            <div class="stat-value" style="color:#ef4444;">{{ $stats['critical_vector'] }}</div>
            <div class="stat-label">Critical Density</div>
            <div class="stat-tag" style="background:rgba(239,68,68,0.08); color:#ef4444;">
                <i class="ph-bold ph-siren"></i> High-risk cases
            </div>
        </div>

        <!-- Card 4: Dialogues -->
        <div class="stat-card" style="--card-glow: rgba(245,158,11,0.08);">
            <div class="stat-card-bar" style="background: linear-gradient(90deg,#f59e0b,#d97706);"></div>
            <div class="stat-icon" style="background:rgba(245,158,11,0.1); color:#f59e0b;">
                <i class="ph-bold ph-chat-centered-text"></i>
            </div>
            <div class="stat-value" style="color:#f59e0b;">{{ $stats['active_dialogues'] }}</div>
            <div class="stat-label">Interaction Feed</div>
            <div class="stat-tag" style="background:rgba(245,158,11,0.08); color:#f59e0b;">
                <i class="ph-bold ph-chat-dots"></i> Active threads
            </div>
        </div>
    </div>

    <!-- ── Triage Priority Queue ── -->
    <div class="clinical-control-panel staggered" style="margin-bottom: 4rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2.5rem;">
            <div style="display:flex; align-items:center; gap:1rem;">
                <div class="section-header-line"></div>
                <div>
                    <h2 style="font-family:'Outfit',sans-serif; font-size:1.5rem; font-weight:900; color:var(--text); margin:0; letter-spacing:-0.03em;">Triage Priority Queue</h2>
                    <p style="color:var(--text-muted); font-size:0.85rem; font-weight:500; margin:0.2rem 0 0;">Real-time prioritization of student clinical scores.</p>
                </div>
            </div>
            <a href="{{ route('counselor.students.index') }}" class="btn-secondary"
               style="padding:0.7rem 1.5rem; border-radius:14px; font-weight:800; text-transform:uppercase; font-size:0.72rem; letter-spacing:0.05em;">Access Registry</a>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:separate; border-spacing:0 0.65rem;">
                <thead>
                    <tr style="text-transform:uppercase; letter-spacing:0.12em; font-size:0.65rem; color:var(--text-dim); font-weight:900;">
                        <th style="padding:0.75rem 1.25rem; text-align:left;">Student</th>
                        <th style="padding:0.75rem 1.25rem; text-align:left;">Wellness Score</th>
                        <th style="padding:0.75rem 1.25rem; text-align:left;">Clinical Trend</th>
                        <th style="padding:0.75rem 1.25rem; text-align:left;">Risk Level</th>
                        <th style="padding:0.75rem 1.25rem; text-align:left;">Last Assessment</th>
                        <th style="padding:0.75rem 1.25rem; text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($priority_queue as $student)
                    @php
                        $latest = $student->latestAssessment;
                        $score  = $latest?->overall_score ?? 0;
                        $risk   = $latest?->risk_level ?? 'Moderate';
                        $shift  = $student->clinical_shift ?? 'Stable';
                        $dir    = $student->shift_direction ?? 'none';
                        // Avatar color based on first letter
                        $avatarColors = ['A'=>'#10b981','B'=>'#6366f1','C'=>'#f59e0b','D'=>'#ef4444','E'=>'#8b5cf6','F'=>'#0ea5e9','G'=>'#10b981','H'=>'#f43f5e','I'=>'#6366f1','J'=>'#d97706'];
                        $initial = strtoupper(mb_substr(trim($student->full_name), 0, 1));
                        $fallbackColor = 'var(--primary)';
                        $avatarColor = $avatarColors[$initial] ?? $fallbackColor;
                        $isVar = str_contains($avatarColor, 'var(');
                    @endphp
                    <tr class="priority-row" style="background:var(--surface-2); border-radius:20px;">
                        <td style="padding:1.1rem 1.25rem; border-radius:20px 0 0 20px;">
                            <div style="display:flex; align-items:center; gap:1rem;">
                                <div class="avatar-init" style="background:{{ $isVar ? 'rgba(16,185,129,0.12)' : $avatarColor . '18' }}; color:{{ $avatarColor }};">
                                    {{ $initial }}
                                </div>
                                <div>
                                    <div style="font-weight:800; color:var(--text); font-size:0.95rem;">{{ $student->full_name }}</div>
                                    <div style="font-size:0.7rem; color:var(--text-dim); font-weight:600; text-transform:uppercase; letter-spacing:0.04em; margin-top:0.15rem;">#{{ $student->user_id }} &middot; {{ $student->roll_number ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:1.1rem 1.25rem;">
                            <div style="display:flex; align-items:center; gap:0.85rem;">
                                <div class="score-track">
                                    <div class="score-fill-bar" data-w="{{ $score }}" style="width:0%; background:{{ $score > 70 ? '#ef4444' : ($score > 40 ? '#f59e0b' : '#10b981') }};"></div>
                                </div>
                                <span style="font-weight:900; color:var(--text); font-size:1rem; font-variant-numeric:tabular-nums;">{{ $score }}<span style="font-size:0.7rem; color:var(--text-dim);">%</span></span>
                            </div>
                        </td>
                        <td style="padding:1.1rem 1.25rem;">
                            @php
                                $tStyles = [
                                    'Improving' => ['color'=>'#10b981','icon'=>'ph-trend-up','label'=>'Improving'],
                                    'Declining' => ['color'=>'#ef4444','icon'=>'ph-trend-down','label'=>'Declining'],
                                    'Stable'    => ['color'=>'#64748b','icon'=>'ph-arrows-left-right','label'=>'Stable'],
                                ];
                                $ts = $tStyles[$shift] ?? $tStyles['Stable'];
                            @endphp
                            <div style="display:flex; align-items:center; gap:0.45rem; color:{{ $ts['color'] }};">
                                <i class="ph-bold {{ $ts['icon'] }}" style="font-size:1.1rem;"></i>
                                <span style="font-weight:700; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">{{ $ts['label'] }}</span>
                            </div>
                        </td>
                        <td style="padding:1.1rem 1.25rem;">
                            @php
                                $rStyles = [
                                    'Critical' => ['bg'=>'rgba(239,68,68,0.1)',   'color'=>'#ef4444', 'border'=>'#ef444440', 'icon'=>'ph-warning-octagon'],
                                    'High'     => ['bg'=>'rgba(249,115,22,0.1)',  'color'=>'#f97316', 'border'=>'#f9731640', 'icon'=>'ph-trend-up'],
                                    'Moderate' => ['bg'=>'rgba(245,158,11,0.1)',  'color'=>'#f59e0b', 'border'=>'#f59e0b40', 'icon'=>'ph-equals'],
                                    'Low'      => ['bg'=>'rgba(16,185,129,0.1)',  'color'=>'#10b981', 'border'=>'#10b98140', 'icon'=>'ph-check-circle'],
                                ];
                                $rs = $rStyles[$risk] ?? $rStyles['Moderate'];
                            @endphp
                            <span class="risk-pill" style="background:{{ $rs['bg'] }}; color:{{ $rs['color'] }}; border-color:{{ $rs['border'] }};">
                                <i class="ph-bold {{ $rs['icon'] }}"></i> {{ $risk }}
                            </span>
                        </td>
                        <td style="padding:1.1rem 1.25rem; color:var(--text-muted); font-size:0.85rem; font-weight:600;">
                            {{ $latest?->assessment_date?->format('M d, Y') ?? '—' }}
                        </td>
                        <td style="padding:1.1rem 1.25rem; text-align:right; border-radius:0 20px 20px 0;">
                            <a href="{{ route('counselor.students.show', $student->user_id) }}"
                               style="display:inline-flex; align-items:center; gap:0.4rem; background:var(--primary-glow); color:var(--primary); border:1.5px solid rgba(16,185,129,0.3); font-weight:800; font-size:0.72rem; text-transform:uppercase; padding:0.55rem 1.1rem; border-radius:12px; text-decoration:none; transition:all 0.2s ease; letter-spacing:0.04em;"
                               onmouseover="this.style.background='var(--primary)';this.style.color='white';"
                               onmouseout="this.style.background='var(--primary-glow)';this.style.color='var(--primary)';"
                               ><i class="ph-bold ph-arrow-square-out"></i> View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:6rem 2rem; text-align:center;">
                            <div style="width:72px; height:72px; border-radius:24px; background:var(--surface-2); display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem; font-size:2rem;">🛡️</div>
                            <h3 style="font-weight:800; color:var(--text-dim); font-size:1.1rem; margin:0 0 0.5rem;">Clinical Buffer Clear</h3>
                            <p style="color:var(--text-muted); font-weight:500; font-size:0.9rem; margin:0;">No priority escalations in the system matrix.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── Interaction Network ── -->
    <div class="staggered">
        <div class="section-label">
            <i class="ph-bold ph-chat-centered-dots" style="color:var(--clinical-amber);"></i>
            Interaction Network
        </div>

        <header style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:3rem;">
            <div>
                <h2 style="font-family:'Outfit',sans-serif; font-size:2.2rem; font-weight:900; color:var(--text); margin:0; letter-spacing:-0.04em;">Subject Dialogues</h2>
                <p style="color:var(--text-muted); font-size:1rem; font-weight:500; margin-top:0.4rem;">Identifiable clinical communications and student notes.</p>
            </div>
            <div style="background:var(--primary-glow); padding:0.6rem 1.2rem; border-radius:100px; font-weight:900; font-size:0.75rem; color:var(--primary); text-transform:uppercase; letter-spacing:0.08em; border:1px solid rgba(16,185,129,0.2);">
                {{ count($anon_notes) }} Active Triage Threads
            </div>
        </header>

        <div class="patient-voice-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(550px, 1fr)); gap:2.5rem;">
            @foreach ($anon_notes as $note)
            <div class="note-card staggered">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; border-bottom:1px solid var(--border); padding-bottom:1.5rem;">
                    <div style="display:flex; align-items:center; gap:1.1rem;">
                        <div style="width:12px; height:12px; background:#10b981; border-radius:50%; box-shadow:0 0 12px #10b981;"></div>
                        <div>
                            <span style="font-weight:900; font-size:1.1rem; color:var(--text); letter-spacing:-0.01em;">
                                {{ $note->student?->full_name ?? 'Student' }}
                            </span>
                            <div style="font-size:0.7rem; font-weight:800; color:var(--text-dim); text-transform:uppercase; letter-spacing:0.05em; margin-top:0.15rem;">{{ $note->student?->roll_number ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div style="display:flex;">
                        <button onclick="speakMessage('note_content_{{ $note->note_id }}', this)" 
                                                class="btn-icon" title="Vocalize Feedback">
                            <i class="ph-bold ph-speaker-high"></i>
                        </button>
                    </div>
                </div>

                <div id="note_content_{{ $note->note_id }}" style="max-height:350px; overflow-y:auto; padding-right:1rem; margin-bottom:2.5rem; scroll-behavior:smooth;">
                    @foreach ($note->messages as $msg)
                    <div style="margin-bottom:2rem; display:flex; flex-direction:column; align-items: {{ ($msg->sender_type === 'student') ? 'flex-start' : 'flex-end' }};">
                        <div style="font-weight:900; font-size:0.65rem; color:var(--text-dim); text-transform:uppercase; margin-bottom:0.4rem; opacity:0.6; letter-spacing:0.06em;">
                            {{ ($msg->sender_type === 'student') ? 'Subject' : 'Clinical Officer' }}
                        </div>
                        <div class="{{ ($msg->sender_type === 'student') ? 'bubble-student' : 'bubble-counselor' }}">
                            {{ $msg->message_text }}
                        </div>
                    </div>
                    @endforeach
                </div>

                <form id="note_reply_form_{{ $note->note_id }}" onsubmit="submitReply({{ $note->note_id }}, event)" style="display:flex; flex-direction:column; gap:1.25rem; margin-top:auto; background:var(--surface-2); padding:1.75rem; border-radius:24px; border:1px solid var(--border);">
                    @csrf
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <label style="font-weight:900; font-size:0.72rem; color:var(--text-dim); text-transform:uppercase; letter-spacing:0.1em;">Institutional Response</label>
                        <button type="button" onclick="suggestReply({{ $note->note_id }}, event)" class="btn-ai-assist">
                            <i class="ph-bold ph-sparkle"></i> GPT-4o Assist
                        </button>
                    </div>
                    <textarea name="message" id="reply_textarea_{{ $note->note_id }}" placeholder="Draft professional clinical guidance..." class="form-input-premium" style="height:100px; resize:none; border-radius:16px; padding:1.2rem; font-size:0.95rem; background:var(--surface-solid); border:1.5px solid var(--border); transition:all 0.3s ease;" required></textarea>
                    <button type="submit" class="btn-primary" style="padding:1rem; font-weight:900; border-radius:16px; text-transform:uppercase; font-size:0.85rem; letter-spacing:0.05em; display:flex; align-items:center; justify-content:center; gap:0.6rem;">
                        <span class="btn-text">Dispatch Response</span> <i class="ph-bold ph-paper-plane-tilt"></i>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // GSAP Entrance Animations
    if (window.gsap) {
        gsap.from('.staggered', {
            y: 40,
            opacity: 0,
            duration: 1.2,
            stagger: 0.15,
            ease: "expo.out",
            clearProps: "all"
        });

        // Score bar fill animation
        setTimeout(() => {
            document.querySelectorAll('.score-fill-bar').forEach(bar => {
                const targetW = bar.getAttribute('data-w');
                bar.style.width = targetW + '%';
            });
        }, 600);
    }

    // Live Clock Updater
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
        const dateStr = now.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric'
        });
        const el = document.getElementById('liveClock');
        if (el) el.innerHTML = `<i class="ph ph-clock" style="margin-right:4px;"></i> ${dateStr} &bull; ${timeStr}`;
    }
    updateClock();
    setInterval(updateClock, 1000);
});

/** Clinical Control: Emergency Availability Toggle **/
async function toggleEmergencyStatus() {
    const btn = document.getElementById('emergencyToggleBtn');
    const textEl = document.getElementById('emergencyStatusText');
    const icon = btn.querySelector('i');

    try {
        const response = await fetch("{{ route('counselor.emergency.toggle') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            }
        });
        const data = await response.json();

        if (data.success) {
            if (data.is_available) {
                btn.style.background = '#10b981';
                btn.style.boxShadow = '0 8px 20px rgba(16,185,129,0.3)';
                icon.className = 'ph-bold ph-shield-check';
                textEl.innerText = 'Available';
            } else {
                btn.style.background = '#ef4444';
                btn.style.boxShadow = '0 8px 20px rgba(239,68,68,0.3)';
                icon.className = 'ph-bold ph-shield-slash';
                textEl.innerText = 'Unavailable';
            }
            
            // Success animation
            if (window.gsap) gsap.from(btn, { scale: 0.9, duration: 0.4, ease: "back.out(2)" });
            App.toast({ type: 'success', title: 'Status Updated', message: 'Institutional availability state synchronized.' });
        }
    } catch (err) {
        console.error("Emergency toggle failed", err);
        App.toast({ type: 'error', title: 'Sync Failed', message: 'Critical error in status synchronization.' });
    }
}

/** Institutional Voice: Text-to-Speech Feed **/
async function speakMessage(elementId, btn) {
    if (!btn) return;
    const container = document.getElementById(elementId);
    if (!container) return;

    const studentMessages = Array.from(container.querySelectorAll('.bubble-student'))
        .map(el => el.textContent.trim())
        .join(". ");

    if (!studentMessages) return;

    // Cancel existing speech
    window.speechSynthesis.cancel();

    const utterance = new SpeechSynthesisUtterance(studentMessages);
    utterance.rate = 0.95;
    utterance.pitch = 1;
    
    const voices = window.speechSynthesis.getVoices();
    const voice = voices.find(v => v.lang.startsWith('en')) || voices[0];
    if (voice) utterance.voice = voice;

    window.speechSynthesis.speak(utterance);

    // Visual feedback
    const originalIcon = btn.innerHTML;
    btn.innerHTML = '<i class="ph-bold ph-wave-sine"></i>';
    if (window.gsap) gsap.to(btn, { scale: 1.1, repeat: -1, yoyo: true, duration: 0.4 });
    
    utterance.onend = () => {
        btn.innerHTML = originalIcon;
        if (window.gsap) {
            gsap.killTweensOf(btn);
            gsap.to(btn, { scale: 1, duration: 0.3 });
        }
    };
}

async function suggestReply(noteId, event) {
    const textarea = document.getElementById('reply_textarea_' + noteId);
    if (!textarea) return;
    
    const btn = event.currentTarget;
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="ph-bold ph-circle-notch animate-spin"></i> Analyzing...';
    btn.disabled = true;

    // Find last student message in the note-card
    const card = textarea.closest('.note-card');
    const studentMessages = Array.from(card.querySelectorAll('.bubble-student'));
    let studentMsg = studentMessages.length > 0 ? studentMessages[studentMessages.length-1].textContent.trim() : "";

    if (!studentMsg) {
        btn.innerHTML = originalContent;
        btn.disabled = false;
        return;
    }

    try {
        const res = await fetch("{{ route('counselor.ai.suggest') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
            body: JSON.stringify({ note_text: studentMsg })
        });
        const data = await res.json();
        
        btn.innerHTML = originalContent;
        btn.disabled = false;
        
        if (data.success) {
            let i = 0;
            textarea.value = "";
            const text = data.suggestion;
            const timer = setInterval(() => {
                if (i < text.length) {
                    textarea.value += text.charAt(i);
                    i++;
                } else {
                    clearInterval(timer);
                }
            }, 8);
            textarea.focus();
        }
    } catch(err) {
        btn.innerHTML = originalContent;
        btn.disabled = false;
    }
}

async function submitReply(noteId, event) {
    event.preventDefault();
    const form = event.target;
    const btn = form.querySelector('button[type="submit"]');
    const btnText = btn.querySelector('.btn-text');
    const textarea = form.querySelector('textarea');
    const fd = new FormData(form);

    btn.disabled = true;
    btnText.textContent = 'Dispatching...';

    try {
        const response = await fetch("{{ url('counselor/notes') }}/" + noteId + "/reply", {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            }
        });
        const data = await response.json();

        if (data.success) {
            App.toast({ type: 'success', title: 'Response Dispatched', message: 'Integrated into clinical thread.' });
            textarea.value = '';
            
            const res = await fetch("{{ route('counselor.dashboard') }}");
            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const newContent = doc.querySelector('#note_content_' + noteId);
            const oldContent = document.querySelector('#note_content_' + noteId);
            if (newContent && oldContent) {
                oldContent.innerHTML = newContent.innerHTML;
                oldContent.scrollTop = oldContent.scrollHeight;
                const lastMsg = oldContent.lastElementChild;
                if (lastMsg && window.gsap) gsap.from(lastMsg, { x: 20, opacity: 0, duration: 0.5, ease: "power2.out" });
            }
        } else {
            App.toast({ type: 'error', title: 'Dispatch Failed', message: data.error || 'Check content.' });
        }
    } catch (err) {
        console.error("Reply failed", err);
        App.toast({ type: 'error', title: 'Network Error', message: 'Connection failed.' });
    } finally {
        btn.disabled = false;
        btnText.textContent = 'Dispatch Response';
    }
}
</script>
@endpush
