@extends('layouts.app')

@push('styles')
<style>
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

    .logs-content { position: relative; z-index: 1; }

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
        background: linear-gradient(90deg, #10b981 0%, #6366f1 50%, #f43f5e 100%);
    }

    .log-row {
        transition: background 0.25s ease, transform 0.25s ease;
        cursor: pointer;
    }
    .log-row:hover {
        background: var(--surface-2) !important;
        transform: translateX(4px);
    }

    .avatar-init {
        width: 46px; height: 46px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 900;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .status-pill {
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

    .badge-counselor {
        background: rgba(99,102,241,0.08);
        color: #6366f1;
        border: 1px solid rgba(99,102,241,0.2);
        padding: 0.25rem 0.65rem;
        border-radius: 100px;
        font-size: 0.7rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 0.30rem;
    }

    .btn-action-premium {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: var(--primary-glow);
        color: var(--primary);
        border: 1.5px solid rgba(16,185,129,0.3);
        font-weight: 800;
        font-size: 0.72rem;
        text-transform: uppercase;
        padding: 0.55rem 1.1rem;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.2s ease;
        letter-spacing: 0.04em;
    }
    .btn-action-premium:hover {
        background: var(--primary);
        color: white;
    }

    .pagination-wrapper {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
    }

    .pagination-wrapper .pagination {
        display: flex;
        gap: 0.5rem;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .pagination-wrapper .page-item .page-link {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--surface-solid);
        color: var(--text);
        text-decoration: none;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .pagination-wrapper .page-item.active .page-link {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .pagination-wrapper .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>
@endpush

@section('content')
<div class="dash-orb dash-orb-1"></div>
<div class="dash-orb dash-orb-2"></div>

<div class="container logs-content" style="max-width: 1400px; margin: 0 auto; padding: 2rem 2rem 5rem;">
    <header class="staggered" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
        <div>
            <div style="font-weight: 900; color: var(--primary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.25em; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.6rem;">
                <i class="ph-bold ph-phone-call" style="font-size: 1.1rem;"></i> Emergency Communication Center
            </div>
            <h1 style="font-family:'Outfit',sans-serif; font-size:2.9rem; font-weight:900; letter-spacing:-0.05em; line-height:1.05; margin:0;" class="text-gradient">
                Call & Transcript Logs
            </h1>
            <p style="color:var(--text-muted); font-size:1rem; font-weight:500; margin-top:0.4rem;">
                System-wide audit trail of critical intervention audio transcripts and chat records.
            </p>
        </div>
        <div>
            <a href="{{ route(Auth::user()->dashboardRoute()) }}" class="btn-secondary" style="padding:0.9rem 1.75rem; border-radius:18px; font-weight:800; text-transform:uppercase; font-size:0.78rem; letter-spacing:0.05em; text-decoration:none; display:flex; align-items:center; gap:0.6rem;">
                <i class="ph-bold ph-arrow-left"></i> Dashboard
            </a>
        </div>
    </header>

    <div class="clinical-control-panel staggered">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2.5rem; flex-wrap: wrap; gap: 1rem;">
            <div style="display:flex; align-items:center; gap:1rem;">
                <div style="width: 4px; height: 28px; border-radius: 4px; background: linear-gradient(to bottom, #10b981, #6366f1); flex-shrink: 0;"></div>
                <div>
                    <h2 style="font-family:'Outfit',sans-serif; font-size:1.5rem; font-weight:900; color:var(--text); margin:0; letter-spacing:-0.03em;">Audited Sessions Registry</h2>
                    <p style="color:var(--text-muted); font-size:0.85rem; font-weight:500; margin:0.2rem 0 0;">Browse and audit transcripts of emergency help calls.</p>
                </div>
            </div>

            <!-- Filters -->
            <div style="display: flex; gap: 0.85rem; align-items: center;">
                <form action="{{ route('counselor.emergency.calls.logs') }}" method="GET" style="display: flex; gap: 0.75rem; align-items: center;">
                    @if(Auth::user()->isCounselor())
                        <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--surface-2); padding: 0.4rem 1rem; border-radius: 12px; border: 1px solid var(--border);">
                            <input type="checkbox" name="my_calls" id="my_calls_check" value="1" {{ request()->has('my_calls') ? 'checked' : '' }} onchange="this.form.submit()" style="cursor: pointer; width: 16px; height: 16px; accent-color: var(--primary);">
                            <label for="my_calls_check" style="font-size: 0.8rem; font-weight: 700; color: var(--text); cursor: pointer; user-select: none;">Show Only My Calls</label>
                        </div>
                    @endif
                    <select name="status" onchange="this.form.submit()" style="background: var(--surface-2); border: 1px solid var(--border); padding: 0.45rem 1rem; border-radius: 12px; font-weight: 700; font-size: 0.8rem; color: var(--text); outline: none; cursor: pointer;">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="ended" {{ request('status') === 'ended' ? 'selected' : '' }}>Ended</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>Declined</option>
                    </select>
                </form>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:separate; border-spacing:0 0.65rem;">
                <thead>
                    <tr style="text-transform:uppercase; letter-spacing:0.12em; font-size:0.65rem; color:var(--text-dim); font-weight:900;">
                        <th style="padding:0.75rem 1.25rem; text-align:left;">Call ID</th>
                        <th style="padding:0.75rem 1.25rem; text-align:left;">Student</th>
                        <th style="padding:0.75rem 1.25rem; text-align:left;">Assigned Officer</th>
                        <th style="padding:0.75rem 1.25rem; text-align:left;">Time & Duration</th>
                        <th style="padding:0.75rem 1.25rem; text-align:left;">Status</th>
                        <th style="padding:0.75rem 1.25rem; text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($calls as $call)
                        @php
                            $student = $call->student;
                            $counselor = $call->counselor;
                            $status = $call->status;
                            
                            // Colors and icon based on status
                            $statusStyles = [
                                'pending'  => ['bg'=>'rgba(245,158,11,0.1)',  'color'=>'#f59e0b', 'border'=>'#f59e0b40', 'icon'=>'ph-clock-countdown'],
                                'active'   => ['bg'=>'rgba(16,185,129,0.1)',  'color'=>'#10b981', 'border'=>'#10b98140', 'icon'=>'ph-phone-incoming-bold'],
                                'ended'    => ['bg'=>'rgba(100,116,139,0.1)', 'color'=>'#64748b', 'border'=>'#64748b40', 'icon'=>'ph-check-circle'],
                                'declined' => ['bg'=>'rgba(239,68,68,0.1)',   'color'=>'#ef4444', 'border'=>'#ef444440', 'icon'=>'ph-x-circle'],
                            ];
                            $ss = $statusStyles[$status] ?? $statusStyles['ended'];

                            // Duration calculation
                            if ($call->started_at && $call->ended_at) {
                                $diff = $call->started_at->diff($call->ended_at);
                                $duration = '';
                                if ($diff->h > 0) $duration .= $diff->h . 'h ';
                                $duration .= $diff->i . 'm ' . $diff->s . 's';
                            } elseif ($call->started_at && $status === 'active') {
                                $duration = 'Active (' . $call->started_at->diffForHumans(null, true) . ')';
                            } else {
                                $duration = '—';
                            }

                            // Avatar student color
                            $initial = strtoupper(mb_substr(trim($student->full_name ?? 'U'), 0, 1));
                            $avatarColors = ['A'=>'#10b981','B'=>'#6366f1','C'=>'#f59e0b','D'=>'#ef4444','E'=>'#8b5cf6','F'=>'#0ea5e9','G'=>'#10b981','H'=>'#f43f5e','I'=>'#6366f1','J'=>'#d97706'];
                            $avatarColor = $avatarColors[$initial] ?? 'var(--primary)';
                            $isVar = str_contains($avatarColor, 'var(');
                        @endphp
                        <tr class="log-row" style="background:var(--surface-2); border-radius:20px;" onclick="window.location.href='{{ $status === 'active' ? route('counselor.video.call', $call->call_id) : route('counselor.emergency.calls.history', $call->call_id) }}'">
                            <td style="padding:1.1rem 1.25rem; border-radius:20px 0 0 20px; font-weight: 800; color: var(--text-dim);">
                                #{{ $call->call_id }}
                            </td>
                            <td style="padding:1.1rem 1.25rem;">
                                <div style="display:flex; align-items:center; gap:1rem;">
                                    <div class="avatar-init" style="background:{{ $isVar ? 'rgba(16,185,129,0.12)' : $avatarColor . '18' }}; color:{{ $avatarColor }};">
                                        {{ $initial }}
                                    </div>
                                    <div>
                                        <div style="font-weight:800; color:var(--text); font-size:0.95rem;">{{ $student->full_name ?? 'Unknown Student' }}</div>
                                        <div style="font-size:0.7rem; color:var(--text-dim); font-weight:600; text-transform:uppercase; letter-spacing:0.04em; margin-top:0.15rem;">{{ $student->roll_number ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:1.1rem 1.25rem;">
                                @if($counselor)
                                    <span class="badge-counselor">
                                        <i class="ph-bold ph-user"></i> {{ $counselor->full_name }}
                                    </span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; font-style: italic;">Unassigned / Pending</span>
                                @endif
                            </td>
                            <td style="padding:1.1rem 1.25rem;">
                                <div style="font-weight: 700; font-size: 0.85rem; color: var(--text);">
                                    {{ $call->created_at ? $call->created_at->format('M d, Y') : '—' }}
                                </div>
                                <div style="font-size: 0.72rem; color: var(--text-dim); margin-top: 0.2rem; display: flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                                    <i class="ph ph-clock"></i> {{ $duration }}
                                    @if($call->created_at)
                                        &bull; {{ $call->created_at->format('h:i A') }}
                                    @endif
                                </div>
                            </td>
                            <td style="padding:1.1rem 1.25rem;">
                                <span class="status-pill" style="background:{{ $ss['bg'] }}; color:{{ $ss['color'] }}; border-color:{{ $ss['border'] }};">
                                    <i class="ph-bold {{ $ss['icon'] }}"></i> {{ $status }}
                                </span>
                            </td>
                            <td style="padding:1.1rem 1.25rem; text-align:right; border-radius:0 20px 20px 0;" onclick="event.stopPropagation()">
                                @if($status === 'active')
                                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end; align-items: center;">
                                        <a href="{{ route('counselor.video.call', $call->call_id) }}" class="btn-action-premium" style="background: rgba(16,185,129,0.1); border-color: rgba(16,185,129,0.3); color: #10b981;">
                                            <i class="ph-bold ph-video-camera"></i> Join
                                        </a>
                                        <form action="{{ route('counselor.emergency.calls.end', $call->call_id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to end this emergency call session?');">
                                            @csrf
                                            <button type="submit" class="btn-action-premium" style="background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.3); color: #ef4444; cursor: pointer;">
                                                <i class="ph-bold ph-phone-x"></i> End
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <a href="{{ route('counselor.emergency.calls.history', $call->call_id) }}" class="btn-action-premium">
                                        <i class="ph-bold ph-article"></i> Transcript
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:6rem 2rem; text-align:center;">
                                <div style="width:72px; height:72px; border-radius:24px; background:var(--surface-2); display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem; font-size:2rem;">📞</div>
                                <h3 style="font-weight:800; color:var(--text-dim); font-size:1.1rem; margin:0 0 0.5rem;">Audit Registry Empty</h3>
                                <p style="color:var(--text-muted); font-weight:500; font-size:0.9rem; margin:0;">No emergency call records matching current filters exist in the database.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($calls->hasPages())
            <div class="pagination-wrapper">
                {{ $calls->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.gsap) {
        gsap.from('.staggered', {
            y: 40,
            opacity: 0,
            duration: 1.2,
            stagger: 0.15,
            ease: "expo.out",
            clearProps: "all"
        });
    }
});
</script>
@endpush
