@extends('layouts.app')

@push('styles')
<style>
    .ledger-row {
        background: var(--surface-solid);
        border-radius: 18px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .ledger-row:hover {
        background: var(--surface-2);
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }

    .badge-live {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.85rem;
        border-radius: 100px;
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        font-weight: 800;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    .badge-live::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 8px #10b981;
        animation: pulse-live 1.5s infinite;
    }
    @keyframes pulse-live {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    .badge-archive {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.85rem;
        border-radius: 100px;
        background: var(--surface-2);
        color: var(--text-dim);
        font-weight: 800;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border: 1px solid var(--border);
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1320px; margin: 0 auto; padding: 2rem 1.5rem 4rem;">
    
    <!-- Ledger Header -->
    <header class="staggered" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3.5rem; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="ph-bold ph-receipt" style="font-size: 1.1rem;"></i> Activity Audit
            </div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.75rem; font-weight: 900; color: var(--text); letter-spacing: -0.04em; margin: 0;">Institutional Ledger</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin-top: 0.5rem;">Historical journal of institutional interactions and clinical session logs.</p>
        </div>
        <div style="text-align: right; padding-right: 1.5rem; border-right: 1px solid var(--border);">
            <div style="font-size: 1.65rem; font-weight: 900; color: var(--primary); font-family: 'Outfit', sans-serif;">{{ count($logs) }}</div>
            <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em;">Recorded Logs</div>
        </div>
    </header>

    <!-- Filters -->
    <div class="staggered" style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 28px; padding: 1.5rem 1.75rem; margin-bottom: 2.5rem; box-shadow: var(--shadow-sm);">
        <form id="ledgerFilterForm" style="display: flex; gap: 1.25rem; align-items: center; flex-wrap: wrap;" onsubmit="return false;">
            <div style="flex: 1; min-width: 280px; position: relative;">
                <i class="ph-bold ph-magnifying-glass" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.1rem;"></i>
                <input type="text" id="searchBox" name="search" value="{{ request('search') }}" placeholder="Search identity or activity profile..." style="width: 100%; padding: 0.85rem 1.25rem 0.85rem 3.25rem; border-radius: 14px; border: 1.5px solid var(--border); font-size: 0.92rem; font-weight: 500; background: var(--surface-2); color: var(--text); outline: none; transition: all 0.25s ease;" onfocus="this.style.borderColor='var(--primary)'; this.style.background='var(--surface-solid)';" onblur="this.style.borderColor='var(--border)'; this.style.background='var(--surface-2)';">
            </div>
            
            <select id="roleSelect" name="role" style="padding: 0.85rem 1.5rem; border-radius: 14px; border: 1.5px solid var(--border); background: var(--surface-solid); font-weight: 800; color: var(--text); cursor: pointer; font-size: 0.85rem; outline: none; transition: all 0.2s ease;">
                <option value="">All Role Profiles</option>
                <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Students</option>
                <option value="counselor" {{ request('role') === 'counselor' ? 'selected' : '' }}>Counselors</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admins</option>
            </select>
            
            @if (request('search') || request('role'))
                <a href="{{ route('counselor.ledger.index') }}" style="padding: 0.85rem 1.5rem; border-radius: 14px; background: rgba(239, 68, 68, 0.08); color: #ef4444; font-weight: 800; text-decoration: none; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 0.85rem;">Reset Audit</a>
            @endif
            
            <a href="{{ route('counselor.ledger.export', request()->all()) }}" class="btn-secondary" style="padding: 0.85rem 1.5rem; border-radius: 14px; font-weight: 800; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; text-transform: uppercase;">
                <i class="ph-bold ph-file-csv" style="font-size: 1.1rem;"></i> Export Dataset
            </a>
        </form>
    </div>

    <!-- Ledger Table -->
    <div class="staggered" style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 32px; padding: 2.5rem; box-shadow: var(--shadow-md);">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0 0.65rem;">
                <thead>
                    <tr style="text-transform: uppercase; letter-spacing: 0.12em; font-size: 0.68rem; color: var(--text-dim); font-weight: 900;">
                        <th style="padding: 1.25rem 1.5rem; text-align: left;">Identity Node</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left;">Session Login</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left;">Session Logout</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left;">Total Duration</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: left;">Observed Activity</th>
                        <th style="padding: 1.25rem 1.5rem; text-align: right;">Clinical Status</th>
                    </tr>
                </thead>
                <tbody id="ledgerTableBody">
                    @forelse ($logs as $log)
                    @php
                        $is_online = !$log->logout_time;
                        $initial = strtoupper(substr($log->user->full_name ?? 'U', 0, 1));
                        
                        if (!$log->logout_time) {
                            $duration = 'LIVE SESSION';
                        } else {
                            $start = strtotime($log->login_time);
                            $end = strtotime($log->logout_time);
                            $diff = $end - $start;
                            if ($diff < 60) $duration = $diff . "s";
                            elseif ($diff < 3600) $duration = round($diff / 60) . "m";
                            else $duration = round($diff / 3600, 1) . "h";
                        }
                    @endphp
                    <tr class="ledger-row staggered-row">
                        <td style="padding: 1.25rem 1.5rem; border-top-left-radius: 18px; border-bottom-left-radius: 18px;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 44px; height: 44px; border-radius: 14px; background: var(--surface-2); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-weight: 900; color: var(--primary); font-family: 'Outfit', sans-serif; font-size: 1.1rem;">
                                    {{ $initial }}
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-weight: 800; color: var(--text); font-size: 0.98rem;">{{ $log->user->full_name ?? 'System Node' }}</span>
                                    <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">{{ $log->user->user_type ?? 'GUEST' }}</span>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 800; color: var(--text); font-size: 0.9rem;">
                            {{ \Carbon\Carbon::parse($log->login_time)->format('M d, H:i') }}
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">
                            {{ $log->logout_time ? \Carbon\Carbon::parse($log->logout_time)->format('M d, H:i') : '--:--' }}
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 900; font-size: 0.9rem;">
                            @if($is_online)
                                <span style="color:#10b981; font-weight:900;">LIVE SESSION</span>
                            @else
                                <span style="color: var(--text-muted);">{{ $duration }}</span>
                            @endif
                        </td>
                        <td style="padding: 1.25rem 1.5rem; font-weight: 500; color: var(--text-dim); font-size: 0.9rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $log->activity }}">
                            {{ $log->activity }}
                        </td>
                        <td style="padding: 1.25rem 1.5rem; text-align: right; border-top-right-radius: 18px; border-bottom-right-radius: 18px;">
                            @if ($is_online)
                                <span class="badge-live">LIVE</span>
                            @else
                                <span class="badge-archive">ARCHIVED</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 6rem 2rem;">
                            <div style="width: 72px; height: 72px; border-radius: 24px; background: var(--surface-2); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2rem; color: var(--text-dim);">
                                <i class="ph ph-shield"></i>
                            </div>
                            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 900; color: var(--text); font-size: 1.35rem; margin-bottom: 0.5rem;">No Logged Activity</h2>
                            <p style="color: var(--text-muted); font-weight: 500; font-size: 0.95rem; margin: 0;">No institutional interaction records match the specified query.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.gsap) {
        gsap.from('.staggered', { y: 35, opacity: 0, duration: 0.9, stagger: 0.12, ease: "expo.out", clearProps: "all" });
        gsap.from('.staggered-row', { x: -20, opacity: 0, duration: 0.6, stagger: 0.04, ease: "expo.out", delay: 0.3, clearProps: "all" });
    }

    const searchBox = document.getElementById('searchBox');
    const roleSelect = document.getElementById('roleSelect');
    let debounceTimer;

    const refreshLedger = async () => {
        const url = new URL(window.location.href);
        const search = searchBox.value;
        const role = roleSelect.value;
        
        if (search) url.searchParams.set('search', search);
        else url.searchParams.delete('search');
        
        if (role) url.searchParams.set('role', role);
        else url.searchParams.delete('role');
        
        history.pushState({}, '', url);
        await AjaxHelpers.refreshSection(url.toString(), '#ledgerTableBody');
    };

    if (searchBox) {
        searchBox.addEventListener('keyup', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(refreshLedger, 500);
        });
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', refreshLedger);
    }
});
</script>
@endpush
@endsection

