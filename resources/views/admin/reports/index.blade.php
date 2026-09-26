@extends('layouts.app')

@push('styles')
<style>
    .reports-container {
        max-width: 1380px;
        margin: 0 auto;
        padding: 2.25rem 1.75rem 6rem;
    }

    /* ── KPI Cards ── */
    .kpi-card {
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: 24px;
        padding: 1.75rem;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.04);
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }
    .kpi-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.06) 0%, transparent 60%);
        pointer-events: none;
    }
    .dark-mode .kpi-card { background: #1e293b; border-color: #334155; }
    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.1);
    }
    .kpi-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 1rem;
    }

    /* ── Chart Cards ── */
    .chart-card {
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: 28px;
        padding: 2.25rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.03);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .chart-card::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #059669, #10b981, #34d399);
        border-radius: 28px 28px 0 0;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .chart-card:hover::after { opacity: 1; }
    .dark-mode .chart-card { background: #1e293b; border-color: #334155; }

    /* ── Preset Pills ── */
    .preset-pill {
        padding: 0.55rem 1.15rem;
        border-radius: 100px;
        font-size: 0.84rem;
        font-weight: 800;
        background: var(--surface-2);
        color: var(--text-muted);
        border: 1.5px solid var(--border);
        cursor: pointer;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        white-space: nowrap;
    }
    .preset-pill:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
        background: rgba(16, 185, 129, 0.06);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.12);
    }
    .preset-pill.active {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        color: #ffffff !important;
        border-color: transparent !important;
        box-shadow: 0 4px 16px rgba(5, 150, 105, 0.35);
        transform: translateY(-1px);
    }

    /* ── Chart Type Buttons ── */
    .chart-type-btn {
        background: transparent;
        border: none;
        color: var(--text-muted);
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .chart-type-btn:hover { color: var(--text); background: rgba(16, 185, 129, 0.08); }
    .chart-type-btn.active {
        background: var(--primary);
        color: #ffffff;
        box-shadow: 0 3px 10px rgba(5, 150, 105, 0.3);
    }

    /* ── Heatmap Cards ── */
    .heatmap-card {
        background: var(--surface-solid);
        border-radius: 20px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
    }
    .heatmap-card:hover {
        transform: translateY(-4px) scale(1.025);
        box-shadow: 0 14px 30px rgba(239, 68, 68, 0.18);
    }

    /* ── Search / Inputs ── */
    .search-input-box {
        background: var(--surface-2);
        border: 1.5px solid var(--border);
        border-radius: 14px;
        padding: 0.75rem 1.35rem;
        color: var(--text);
        font-weight: 700;
        font-size: 0.95rem;
        outline: none;
        width: 320px;
        transition: all 0.25s ease;
    }
    .search-input-box:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
        background: var(--surface-solid);
    }
    .filter-select {
        padding: 0.7rem 1.1rem;
        border-radius: 14px;
        border: 1.5px solid var(--border);
        background: var(--surface-solid);
        color: var(--text);
        font-weight: 800;
        cursor: pointer;
        font-size: 0.92rem;
        outline: none;
        transition: all 0.22s ease;
        height: 48px;
        flex: 1;
        min-width: 210px;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 12px;
        padding-right: 2.5rem;
    }
    .filter-select:focus, .filter-select:hover {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        background-color: var(--surface-solid);
    }

    /* ── Date Input Wrapper ── */
    .date-input-wrapper {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: var(--surface-solid);
        padding: 0 1.25rem;
        border-radius: 14px;
        border: 1.5px solid var(--border);
        height: 48px;
        flex: 1.5;
        min-width: 340px;
        transition: all 0.22s ease;
    }
    .date-input-wrapper:focus-within {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    /* ── Refresh Loading Overlay ── */
    #refreshOverlay {
        position: fixed;
        inset: 0;
        z-index: 9998;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    #refreshOverlay.active { opacity: 1; pointer-events: all; }
    .refresh-bar {
        position: fixed;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #059669, #10b981, #34d399, #10b981, #059669);
        background-size: 200% 100%;
        animation: shimmerBar 1.2s linear infinite;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .refresh-bar.active { opacity: 1; }
    @keyframes shimmerBar {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* ── Shimmer Skeleton ── */
    @keyframes shimmer {
        0% { background-position: -400px 0; }
        100% { background-position: 400px 0; }
    }
    .shimmer-pulse {
        background: linear-gradient(90deg, var(--surface-2) 25%, var(--border) 50%, var(--surface-2) 75%);
        background-size: 400px 100%;
        animation: shimmer 1.4s ease-in-out infinite;
        border-radius: 8px;
    }

    /* ── Live Indicator Dot ── */
    .live-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5);
        animation: livePulse 2s ease-in-out infinite;
    }
    @keyframes livePulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
        50% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    }

    /* ── Stats Footer Tiles ── */
    .stat-tile {
        background: var(--surface-2);
        padding: 0.85rem 1.1rem;
        border-radius: 16px;
        border: 1px solid var(--border);
        transition: all 0.25s ease;
        cursor: default;
    }
    .stat-tile:hover {
        border-color: var(--primary);
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.1);
        transform: translateY(-2px);
    }
    .stat-tile-label {
        font-size: 0.67rem;
        font-weight: 850;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 0.25rem;
    }
    .stat-tile-value {
        font-size: 1.05rem;
        font-weight: 900;
        font-family: 'Outfit', sans-serif;
        line-height: 1.2;
    }

    /* ── Updating state ── */
    .charts-updating canvas { opacity: 0.45; filter: blur(1px); pointer-events: none; }
    .charts-updating { transition: all 0.3s ease; }

    /* ── Enhanced KPI Cards ── */
    .kpi-card-v2 {
        position: relative;
        border-radius: 24px;
        padding: 1.6rem 1.75rem;
        cursor: pointer;
        overflow: hidden;
        transition: all 0.32s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1.5px solid transparent;
        background: var(--surface-solid);
    }
    .kpi-card-v2::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.07) 0%, transparent 55%);
        pointer-events: none;
        border-radius: 24px;
    }
    .kpi-card-v2:hover {
        transform: translateY(-6px) scale(1.015);
        box-shadow: 0 20px 50px rgba(0,0,0,0.1);
    }
    .kpi-card-v2 .kpi-bg-blob {
        position: absolute;
        width: 140px;
        height: 140px;
        border-radius: 50%;
        right: -30px;
        top: -30px;
        opacity: 0.12;
        filter: blur(20px);
        transition: all 0.5s ease;
    }
    .kpi-card-v2:hover .kpi-bg-blob { opacity: 0.22; transform: scale(1.2); }
    .kpi-click-hint {
        font-size: 0.68rem;
        font-weight: 800;
        opacity: 0;
        transition: opacity 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        margin-top: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    .kpi-card-v2:hover .kpi-click-hint { opacity: 1; }

    /* ── KPI Drawer Slide-in ── */
    #kpiDrawer {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: min(520px, 96vw);
        z-index: 99990;
        background: var(--surface-solid);
        box-shadow: -16px 0 60px rgba(0,0,0,0.14);
        transform: translateX(110%);
        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        border-left: 1px solid var(--border);
    }
    #kpiDrawer.open { transform: translateX(0); }
    #kpiDrawerBackdrop {
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,0.5);
        backdrop-filter: blur(6px);
        z-index: 99989;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    #kpiDrawerBackdrop.open { opacity: 1; pointer-events: all; }
    .drawer-header {
        padding: 1.75rem 2rem 1.25rem;
        border-bottom: 1px solid var(--border);
        flex-shrink: 0;
    }
    .drawer-body {
        flex: 1;
        overflow-y: auto;
        padding: 1.25rem 2rem 2rem;
    }
    .drawer-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.95rem 1.1rem;
        border-radius: 18px;
        border: 1px solid var(--border);
        background: var(--surface-2);
        margin-bottom: 0.65rem;
        transition: all 0.2s ease;
    }
    .drawer-row:hover { border-color: var(--primary); transform: translateX(4px); background: var(--surface-solid); }
    .drawer-avatar {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 1.15rem;
        flex-shrink: 0;
        border: 1px solid var(--border);
    }
    .kpi-progress-ring { transform: rotate(-90deg); }
    .kpi-progress-ring circle.track { stroke-width: 4; fill: none; stroke: rgba(255,255,255,0.12); }
    .kpi-progress-ring circle.fill { stroke-width: 4; fill: none; stroke-dasharray: 100; transition: stroke-dashoffset 1.2s cubic-bezier(0.16,1,0.3,1); stroke-linecap: round; }
</style>
@endpush

@section('content')
<div class="reports-container">
    
    <!-- Page Header -->
    <header class="staggered" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.25rem; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.6rem;">
                <span style="font-weight: 850; background: rgba(16, 185, 129, 0.12); color: #059669; padding: 0.4rem 0.95rem; border-radius: 100px; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.15em; border: 1px solid rgba(16, 185, 129, 0.25); display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="ph-bold ph-shield-check" style="font-size: 1rem;"></i> Clinical Analytics & Governance
                </span>
            </div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.65rem; font-weight: 900; color: var(--text); letter-spacing: -0.04em; margin: 0; line-height: 1.15;">Institutional System Reports</h1>
            <p style="color: var(--text-muted); font-size: 1.05rem; font-weight: 500; margin-top: 0.5rem; max-width: 720px;">Comprehensive mental health trends, DASS-21 assessment metrics, and departmental risk distribution.</p>
        </div>
        <div style="display: flex; gap: 0.85rem; align-items: center; flex-wrap: wrap;">
            <button onclick="refreshAnalytics()" class="btn-secondary" style="padding: 0.85rem 1.4rem; border-radius: 14px; font-weight: 850; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.6rem; cursor: pointer; background: var(--surface-solid); border: 1.5px solid var(--border); color: var(--text); box-shadow: 0 2px 10px rgba(0,0,0,0.03); transition: all 0.2s ease;">
                <i class="ph ph-arrows-clockwise" id="refreshSpinIcon" style="font-size: 1.25rem; color: var(--primary);"></i> Live Refresh
            </button>
            <button onclick="window.print()" class="btn-secondary" style="padding: 0.85rem 1.4rem; border-radius: 14px; font-weight: 850; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.6rem; cursor: pointer; background: var(--surface-solid); border: 1.5px solid var(--border); color: var(--text); box-shadow: 0 2px 10px rgba(0,0,0,0.03); transition: all 0.2s ease;">
                <i class="ph ph-printer" style="font-size: 1.25rem; color: var(--text-muted);"></i> Print Report
            </button>
            <a href="{{ route('admin.reports.export') }}" class="btn-primary" style="padding: 0.85rem 1.6rem; border-radius: 14px; font-weight: 850; font-size: 0.9rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.65rem; background: var(--primary); color: #ffffff; box-shadow: 0 4px 16px rgba(5, 150, 105, 0.32);">
                <i class="ph ph-file-arrow-down" style="font-size: 1.3rem;"></i> Export Dataset (CSV)
            </a>
        </div>
    </header>

    <!-- Executive Highlights Banner -->
    <div class="staggered" style="background: var(--surface-solid); border: 1.5px solid var(--border); border-radius: 22px; padding: 1.5rem 2rem; margin-bottom: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.025);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
            
            <!-- Left: Executive Status Overview -->
            <div style="display: flex; align-items: center; gap: 1.35rem; flex-wrap: wrap; flex: 1; min-width: 340px;">
                <div style="width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0; box-shadow: 0 8px 20px rgba(5, 150, 105, 0.3);">
                    <i class="ph-bold ph-chart-polar"></i>
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.45rem;">
                    <div style="font-size: 0.78rem; font-weight: 900; color: #059669; text-transform: uppercase; letter-spacing: 0.14em; display: flex; align-items: center; gap: 0.45rem;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span> Executive Status Overview
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                        <!-- Wellness Index -->
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.05rem; font-weight: 850; color: var(--text);">
                            <span>Institutional Wellness Index:</span>
                            <span style="color: #059669; background: rgba(16, 185, 129, 0.14); padding: 0.35rem 0.95rem; border-radius: 100px; border: 1.5px solid rgba(16, 185, 129, 0.3); font-weight: 900; font-size: 0.95rem;">
                                {{ $wellness_index }}% Low Risk
                            </span>
                        </div>

                        @if($top_risk_count > 0)
                            <span style="color: var(--border); font-size: 1.1rem; font-weight: 300;">|</span>
                            <!-- Priority Focus -->
                            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.05rem; font-weight: 850; color: var(--text);">
                                <span>Priority Focus:</span>
                                <span style="color: #dc2626; background: rgba(239, 68, 68, 0.12); padding: 0.35rem 0.95rem; border-radius: 100px; border: 1.5px solid rgba(239, 68, 68, 0.3); font-weight: 900; font-size: 0.95rem;">
                                    {{ $top_risk_course }} ({{ $top_risk_count }} High/Critical)
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right: Stat Pill Badges -->
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <!-- Total Students -->
                <div style="background: var(--surface-2); border: 1.5px solid var(--border); border-radius: 16px; padding: 0.75rem 1.35rem; display: flex; align-items: center; gap: 0.9rem;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(99, 102, 241, 0.14); color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                        <i class="ph-bold ph-users-three"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.72rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em; white-space: nowrap;">Total Students Evaluated</div>
                        <div style="font-size: 1.65rem; font-weight: 950; color: var(--text); font-family: 'Outfit', sans-serif; line-height: 1.1;">{{ number_format($total_students) }}</div>
                    </div>
                </div>

                <!-- Assessments Count -->
                <div style="background: var(--surface-2); border: 1.5px solid var(--border); border-radius: 16px; padding: 0.75rem 1.35rem; display: flex; align-items: center; gap: 0.9rem;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16, 185, 129, 0.14); color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                        <i class="ph-bold ph-clipboard-text"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.72rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em; white-space: nowrap;">Assessments Count</div>
                        <div style="font-size: 1.65rem; font-weight: 950; color: #059669; font-family: 'Outfit', sans-serif; line-height: 1.1;">{{ number_format($total_assessments) }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Filters & Date Presets Strip -->
    <div class="staggered" style="background: var(--surface-solid); border: 1.5px solid var(--border); border-radius: 22px; padding: 1.5rem 2rem; margin-bottom: 2.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.025);">
        <form id="analyticsFilterForm" method="GET" style="display: flex; flex-direction: column; gap: 1.25rem;">
            
            <!-- Quick Presets Row -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.25rem;">
                <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                    <span style="font-size: 0.82rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.12em; margin-right: 0.4rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <i class="ph-bold ph-funnel" style="color: var(--primary); font-size: 1.05rem;"></i> Quick Presets:
                    </span>
                    <button type="button" onclick="setPreset('7days')" class="preset-pill {{ request('preset') == '7days' ? 'active' : '' }}">Last 7 Days</button>
                    <button type="button" onclick="setPreset('30days')" class="preset-pill {{ request('preset') == '30days' ? 'active' : '' }}">Last 30 Days</button>
                    <button type="button" onclick="setPreset('6months')" class="preset-pill {{ request('preset') == '6months' || (!$start_date && !$end_date) ? 'active' : '' }}">Last 6 Months</button>
                    <button type="button" onclick="setPreset('thisyear')" class="preset-pill {{ request('preset') == 'thisyear' ? 'active' : '' }}">This Year</button>
                    <input type="hidden" name="preset" id="presetInput" value="{{ request('preset') }}">
                </div>

                @if ($start_date || $end_date || $course || $semester || ($risk_level ?? ''))
                    <button type="button" onclick="window.location.href='{{ route('admin.reports.index') }}'" style="padding: 0.55rem 1.15rem; border-radius: 100px; background: rgba(239, 68, 68, 0.1); color: #dc2626; font-weight: 850; border: 1.5px solid rgba(239, 68, 68, 0.25); cursor: pointer; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.4rem; transition: all 0.2s ease;">
                        <i class="ph-bold ph-x-circle" style="font-size: 1rem;"></i> Reset All Filters
                    </button>
                @endif
            </div>

            <!-- Detailed Controls Row (Side-by-side flex alignment) -->
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <!-- Date Picker Box -->
                <div class="date-input-wrapper">
                    <i class="ph-bold ph-calendar-blank" style="color: var(--primary); font-size: 1.25rem; flex-shrink: 0;"></i>
                    <span style="font-size: 0.8rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em;">From</span>
                    <input type="date" id="startDate" name="start_date" value="{{ $start_date }}" onchange="debouncedRefresh()" oninput="debouncedRefresh()" style="background: transparent; border: none; color: var(--text); font-weight: 800; font-size: 0.95rem; outline: none; cursor: pointer; font-family: inherit; flex: 1;">
                    <span style="font-size: 0.8rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em;">To</span>
                    <input type="date" id="endDate" name="end_date" value="{{ $end_date }}" onchange="debouncedRefresh()" oninput="debouncedRefresh()" style="background: transparent; border: none; color: var(--text); font-weight: 800; font-size: 0.95rem; outline: none; cursor: pointer; font-family: inherit; flex: 1;">
                </div>

                <!-- Semester Select -->
                <select name="semester" onchange="debouncedRefresh()" class="filter-select">
                    <option value="">All Semesters</option>
                    @foreach ($semesters as $s)
                        <option value="{{ $s }}" {{ $semester === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>

                <!-- Course Select -->
                <select name="course" onchange="debouncedRefresh()" class="filter-select">
                    <option value="">All Courses / Programs</option>
                    @foreach ($courses as $c)
                        <option value="{{ $c }}" {{ $course === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>

                <!-- Risk Level Select -->
                <select name="risk_level" onchange="debouncedRefresh()" class="filter-select">
                    <option value="">All Risk Tiers</option>
                    <option value="Low" {{ ($risk_level ?? '') === 'Low' ? 'selected' : '' }}>Low Risk</option>
                    <option value="Moderate" {{ ($risk_level ?? '') === 'Moderate' ? 'selected' : '' }}>Moderate Risk</option>
                    <option value="High" {{ ($risk_level ?? '') === 'High' ? 'selected' : '' }}>High Risk</option>
                    <option value="Critical" {{ ($risk_level ?? '') === 'Critical' ? 'selected' : '' }}>Critical Risk</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Analytics Matrix (KPI Stat Cards) -->
    <div id="analyticsMatrix" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.35rem; margin-bottom: 2.5rem;">

        @php
            $low_cnt  = $risk_counts['Low'] ?? 0;
            $low_pct  = $total_assessments > 0 ? round(($low_cnt / $total_assessments) * 100) : 0;
            $obs_cnt  = ($risk_counts['Moderate'] ?? 0) + ($risk_counts['High'] ?? 0);
            $obs_pct  = $total_assessments > 0 ? round(($obs_cnt / $total_assessments) * 100) : 0;
            $crit_cnt = $risk_counts['Critical'] ?? 0;
            $crit_pct = $total_assessments > 0 ? round(($crit_cnt / $total_assessments) * 100) : 0;
            $all_pct  = 100;
        @endphp

        <!-- Card 1: Total Assessment Volume -->
        <div class="kpi-card-v2 staggered"
             style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); border-color: rgba(99,102,241,0.35); box-shadow: 0 8px 32px rgba(99,102,241,0.2);"
             onclick="openKpiDrawer('all')" role="button" tabindex="0">
            <div class="kpi-bg-blob" style="background: #6366f1;"></div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div style="width: 50px; height: 50px; border-radius: 16px; background: rgba(99,102,241,0.25); color: #a5b4fc; display: flex; align-items: center; justify-content: center; font-size: 1.45rem; border: 1px solid rgba(99,102,241,0.3);">
                    <i class="ph-bold ph-clipboard-text"></i>
                </div>
                <svg class="kpi-progress-ring" width="52" height="52" viewBox="0 0 36 36">
                    <circle class="track" cx="18" cy="18" r="15"/>
                    <circle class="fill" cx="18" cy="18" r="15" stroke="#a5b4fc"
                            stroke-dashoffset="{{ 100 - $all_pct }}"/>
                </svg>
            </div>
            <div style="font-size: 0.68rem; font-weight: 900; color: rgba(165,180,252,0.8); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 0.3rem;">Total Assessment Volume</div>
            <div style="font-size: 2.75rem; font-weight: 950; color: #ffffff; line-height: 1; font-family: 'Outfit', sans-serif; letter-spacing: -0.03em;">{{ number_format($total_assessments) }}</div>
            <div style="font-size: 0.78rem; color: rgba(165,180,252,0.75); font-weight: 600; margin-top: 0.45rem; display: flex; align-items: center; gap: 0.3rem;">
                <i class="ph-bold ph-users" style="color: #a5b4fc;"></i> {{ number_format($total_students) }} unique students
            </div>
            <div class="kpi-click-hint" style="color: #a5b4fc;">
                <i class="ph ph-arrow-right"></i> View all records
            </div>
        </div>

        <!-- Card 2: Low Risk -->
        <div class="kpi-card-v2 staggered"
             style="background: linear-gradient(135deg, #064e3b 0%, #065f46 100%); border-color: rgba(16,185,129,0.35); box-shadow: 0 8px 32px rgba(16,185,129,0.18);"
             onclick="openKpiDrawer('low')" role="button" tabindex="0">
            <div class="kpi-bg-blob" style="background: #10b981;"></div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div style="width: 50px; height: 50px; border-radius: 16px; background: rgba(16,185,129,0.25); color: #6ee7b7; display: flex; align-items: center; justify-content: center; font-size: 1.45rem; border: 1px solid rgba(16,185,129,0.3);">
                    <i class="ph-bold ph-shield-check"></i>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.6rem; font-weight: 900; color: #6ee7b7; font-family: 'Outfit', sans-serif; line-height: 1;">{{ $low_pct }}%</div>
                    <div style="font-size: 0.65rem; font-weight: 800; color: rgba(110,231,183,0.7); text-transform: uppercase; letter-spacing: 0.08em;">of total</div>
                </div>
            </div>
            <div style="font-size: 0.68rem; font-weight: 900; color: rgba(110,231,183,0.8); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 0.3rem;">Low Risk (Baseline)</div>
            <div style="font-size: 2.75rem; font-weight: 950; color: #ffffff; line-height: 1; font-family: 'Outfit', sans-serif; letter-spacing: -0.03em;">{{ number_format($low_cnt) }}</div>
            <div style="font-size: 0.78rem; color: rgba(110,231,183,0.75); font-weight: 600; margin-top: 0.45rem; display: flex; align-items: center; gap: 0.3rem;">
                <i class="ph-bold ph-trend-up" style="color: #6ee7b7;"></i> Stable wellness status
            </div>
            <div class="kpi-click-hint" style="color: #6ee7b7;">
                <i class="ph ph-arrow-right"></i> View low risk students
            </div>
        </div>

        <!-- Card 3: Active Observation -->
        <div class="kpi-card-v2 staggered"
             style="background: linear-gradient(135deg, #451a03 0%, #78350f 100%); border-color: rgba(245,158,11,0.35); box-shadow: 0 8px 32px rgba(245,158,11,0.18);"
             onclick="openKpiDrawer('observation')" role="button" tabindex="0">
            <div class="kpi-bg-blob" style="background: #f59e0b;"></div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div style="width: 50px; height: 50px; border-radius: 16px; background: rgba(245,158,11,0.25); color: #fcd34d; display: flex; align-items: center; justify-content: center; font-size: 1.45rem; border: 1px solid rgba(245,158,11,0.3);">
                    <i class="ph-bold ph-eye"></i>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.6rem; font-weight: 900; color: #fcd34d; font-family: 'Outfit', sans-serif; line-height: 1;">{{ $obs_pct }}%</div>
                    <div style="font-size: 0.65rem; font-weight: 800; color: rgba(252,211,77,0.7); text-transform: uppercase; letter-spacing: 0.08em;">of total</div>
                </div>
            </div>
            <div style="font-size: 0.68rem; font-weight: 900; color: rgba(252,211,77,0.8); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 0.3rem;">Active Observation</div>
            <div style="font-size: 2.75rem; font-weight: 950; color: #ffffff; line-height: 1; font-family: 'Outfit', sans-serif; letter-spacing: -0.03em;">{{ number_format($obs_cnt) }}</div>
            <div style="font-size: 0.78rem; color: rgba(252,211,77,0.75); font-weight: 600; margin-top: 0.45rem; display: flex; align-items: center; gap: 0.3rem;">
                <i class="ph-bold ph-clock" style="color: #fcd34d;"></i> Moderate &amp; High cases
            </div>
            <div class="kpi-click-hint" style="color: #fcd34d;">
                <i class="ph ph-arrow-right"></i> View observation cases
            </div>
        </div>

        <!-- Card 4: Critical Severity -->
        <div class="kpi-card-v2 staggered"
             style="background: linear-gradient(135deg, #450a0a 0%, #7f1d1d 100%); border-color: rgba(239,68,68,0.35); box-shadow: 0 8px 32px rgba(239,68,68,0.2);"
             onclick="openKpiDrawer('critical')" role="button" tabindex="0">
            <div class="kpi-bg-blob" style="background: #ef4444;"></div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div style="width: 50px; height: 50px; border-radius: 16px; background: rgba(239,68,68,0.25); color: #fca5a5; display: flex; align-items: center; justify-content: center; font-size: 1.45rem; border: 1px solid rgba(239,68,68,0.3);">
                    <i class="ph-bold ph-warning-octagon"></i>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.6rem; font-weight: 900; color: #fca5a5; font-family: 'Outfit', sans-serif; line-height: 1;">{{ $crit_pct }}%</div>
                    <div style="font-size: 0.65rem; font-weight: 800; color: rgba(252,165,165,0.7); text-transform: uppercase; letter-spacing: 0.08em;">of total</div>
                </div>
            </div>
            <div style="font-size: 0.68rem; font-weight: 900; color: rgba(252,165,165,0.8); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 0.3rem;">Critical Severity</div>
            <div style="font-size: 2.75rem; font-weight: 950; color: #ffffff; line-height: 1; font-family: 'Outfit', sans-serif; letter-spacing: -0.03em;">{{ number_format($crit_cnt) }}</div>
            <div style="font-size: 0.78rem; color: rgba(252,165,165,0.75); font-weight: 600; margin-top: 0.45rem; display: flex; align-items: center; gap: 0.3rem;">
                <i class="ph-bold ph-bell-ringing" style="color: #fca5a5;"></i> Immediate intervention needed
            </div>
            <div class="kpi-click-hint" style="color: #fca5a5;">
                <i class="ph ph-arrow-right"></i> View critical students
            </div>
        </div>
    </div>

    <!-- Charts Grid Section -->
    <div id="chartsContainer" style="display: grid; grid-template-columns: 1.6fr 1fr; gap: 2rem; margin-bottom: 2.5rem;">
        
        <!-- Assessment Frequency Volume Chart -->
        <div class="chart-card staggered" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.35rem; color: var(--text); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="ph-bold ph-chart-line-up" style="color: var(--primary);"></i> Assessment Volume & Trend
                            </h2>
                            <span id="volumeTotalBadge" style="font-size: 0.72rem; font-weight: 850; background: rgba(16, 185, 129, 0.12); color: #059669; padding: 0.2rem 0.65rem; border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.25);">
                                {{ $total_assessments }} Total
                            </span>
                        </div>
                        <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 0.25rem; font-weight: 500;">Temporal distribution of student self-evaluations over time.</p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.72rem; font-weight: 800; color: #10b981;">
                            <span class="live-dot"></span> Live
                        </div>
                        <div style="display: flex; gap: 0.3rem; background: var(--surface-2); padding: 0.3rem; border-radius: 12px; border: 1px solid var(--border);">
                            <button type="button" class="chart-type-btn active" id="btnChartLine" onclick="switchChartType('line')"><i class="ph ph-chart-line-up"></i> Line</button>
                            <button type="button" class="chart-type-btn" id="btnChartBar" onclick="switchChartType('bar')"><i class="ph ph-chart-bar"></i> Bar</button>
                        </div>
                    </div>
                </div>

                <div style="height: 285px; position: relative;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <!-- Volume Analytics Footer Summary Strip -->
            <div style="margin-top: 1.35rem; padding-top: 1.2rem; border-top: 1px solid var(--border); display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.85rem;">
                <div class="stat-tile">
                    <div class="stat-tile-label">Peak Month</div>
                    <div class="stat-tile-value" style="color: #059669;" id="peakMonthLabel">--</div>
                </div>
                <div class="stat-tile">
                    <div class="stat-tile-label">Monthly Average</div>
                    <div class="stat-tile-value" style="color: var(--text);" id="monthlyAvgLabel">--</div>
                </div>
                <div class="stat-tile">
                    <div class="stat-tile-label">Volume Trajectory</div>
                    <div style="font-size: 0.85rem; font-weight: 850; color: #10b981; margin-top: 0.2rem; display: flex; align-items: center; gap: 0.3rem;" id="trajectoryLabel">
                        <i class="ph-bold ph-trend-up"></i> Upward Trend
                    </div>
                </div>
            </div>
        </div>

        <!-- Risk Distribution Doughnut & Progress Bars -->
        <div class="chart-card staggered">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.35rem; color: var(--text); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph-bold ph-chart-pie" style="color: var(--primary);"></i> Risk Level Breakdown
                </h2>
                <span style="font-size: 0.72rem; font-weight: 850; color: var(--primary); text-transform: uppercase; letter-spacing: 0.1em; background: var(--primary-glow); padding: 0.25rem 0.65rem; border-radius: 8px;">DASS-21 Scale</span>
            </div>

            <!-- Interactive Doughnut Chart -->
            <div style="height: 200px; position: relative; margin-bottom: 1.5rem;">
                <canvas id="riskDoughnutChart"></canvas>
            </div>

            <!-- Risk Progress Rows -->
            <div class="risk-bar-chart">
                @php
                    $colors = ['Low' => '#10b981', 'Moderate' => '#f59e0b', 'High' => '#f97316', 'Critical' => '#ef4444'];
                    $total_v = max(1, $total_assessments);
                @endphp
                @foreach ($colors as $lvl => $color)
                @php
                    $cnt = $risk_counts[$lvl] ?? 0;
                    $pct = round($cnt / $total_v * 100);
                @endphp
                <div style="margin-bottom: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem; font-weight: 800; font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted);">
                        <span>{{ $lvl }} Risk</span>
                        <span style="color: {{ $color }};">{{ $cnt }} ({{ $pct }}%)</span>
                    </div>
                    <div style="height: 9px; background: var(--surface-2); border-radius: 10px; overflow: hidden; border: 1px solid var(--border);">
                        <div class="risk-bar-inner" data-width="{{ $pct }}"
                             style="background:{{ $color }}; width:0%; height:100%; transition: width 1.2s cubic-bezier(0.16, 1, 0.3, 1);">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            @php
                $counts_coll = collect($risk_counts);
                $dominant_risk = $counts_coll->isNotEmpty() ? $counts_coll->sortDesc()->keys()->first() : 'Low';
            @endphp
            <div style="margin-top: 1.25rem; padding: 1rem 1.15rem; background: var(--surface-2); border-radius: 16px; border: 1px solid var(--border);">
                <div style="display: flex; gap: 0.85rem; align-items: flex-start;">
                    <i class="ph-bold ph-info" style="font-size: 1.25rem; color: var(--primary); margin-top: 0.15rem; flex-shrink: 0;"></i>
                    <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.45; font-weight: 500; margin: 0;">
                        Primary population concentration in <strong style="color: var(--primary);">{{ $dominant_risk }}</strong> risk tier.
                    </p>
                </div>
            </div>
        </div>

        <!-- Embedded hidden JSON scripts for AJAX sync inside charts container -->
        <script id="monthlyLabels" type="application/json">@json($monthly_labels)</script>
        <script id="monthlyCounts" type="application/json">@json($monthly_counts)</script>
        <script id="riskCountsJson" type="application/json">@json($risk_counts)</script>
    </div>

    <!-- DASS-21 Sub-scale Metrics Card -->
    <div id="domainMetricsContainer" class="staggered" style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 28px; padding: 2rem; margin-bottom: 2.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.35rem; color: var(--text); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph-bold ph-heartbeat" style="color: #6366f1;"></i> DASS-21 Clinical Sub-scale Domain Averages
                </h2>
                <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 0.2rem; font-weight: 500;">Average score intensity breakdown across evaluated sessions (Max domain score: 42).</p>
            </div>
            <span style="font-size: 0.72rem; font-weight: 850; background: var(--surface-2); padding: 0.4rem 0.85rem; border-radius: 100px; color: var(--text-dim); border: 1px solid var(--border);">Standard Clinical Benchmark</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            <!-- Depression -->
            <div style="background: var(--surface-2); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 850; color: #6366f1; text-transform: uppercase; letter-spacing: 0.08em;">Depression Sub-score</div>
                        <span style="font-size: 0.7rem; font-weight: 850; background: {{ $depression_severity['bg'] }}; color: {{ $depression_severity['color'] }}; padding: 0.2rem 0.6rem; border-radius: 100px; display: inline-block; margin-top: 0.3rem;">
                            {{ $depression_severity['label'] }} Intensity
                        </span>
                    </div>
                    <span style="font-size: 1.6rem; font-weight: 900; color: #6366f1; font-family: 'Outfit', sans-serif;">{{ $avg_depression }} <span style="font-size: 0.8rem; opacity: 0.7;">/ 42</span></span>
                </div>
                <div style="height: 8px; background: rgba(0,0,0,0.06); border-radius: 10px; overflow: hidden; margin-top: 1rem;">
                    <div style="width: {{ min(100, round(($avg_depression / 42) * 100)) }}%; height: 100%; background: linear-gradient(90deg, #6366f1, #818cf8); border-radius: 10px; transition: width 1s ease;"></div>
                </div>
            </div>

            <!-- Anxiety -->
            <div style="background: var(--surface-2); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 850; color: #8b5cf6; text-transform: uppercase; letter-spacing: 0.08em;">Anxiety Sub-score</div>
                        <span style="font-size: 0.7rem; font-weight: 850; background: {{ $anxiety_severity['bg'] }}; color: {{ $anxiety_severity['color'] }}; padding: 0.2rem 0.6rem; border-radius: 100px; display: inline-block; margin-top: 0.3rem;">
                            {{ $anxiety_severity['label'] }} Intensity
                        </span>
                    </div>
                    <span style="font-size: 1.6rem; font-weight: 900; color: #8b5cf6; font-family: 'Outfit', sans-serif;">{{ $avg_anxiety }} <span style="font-size: 0.8rem; opacity: 0.7;">/ 42</span></span>
                </div>
                <div style="height: 8px; background: rgba(0,0,0,0.06); border-radius: 10px; overflow: hidden; margin-top: 1rem;">
                    <div style="width: {{ min(100, round(($avg_anxiety / 42) * 100)) }}%; height: 100%; background: linear-gradient(90deg, #8b5cf6, #a78bfa); border-radius: 10px; transition: width 1s ease;"></div>
                </div>
            </div>

            <!-- Stress -->
            <div style="background: var(--surface-2); padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 850; color: #ec4899; text-transform: uppercase; letter-spacing: 0.08em;">Stress Sub-score</div>
                        <span style="font-size: 0.7rem; font-weight: 850; background: {{ $stress_severity['bg'] }}; color: {{ $stress_severity['color'] }}; padding: 0.2rem 0.6rem; border-radius: 100px; display: inline-block; margin-top: 0.3rem;">
                            {{ $stress_severity['label'] }} Intensity
                        </span>
                    </div>
                    <span style="font-size: 1.6rem; font-weight: 900; color: #ec4899; font-family: 'Outfit', sans-serif;">{{ $avg_stress }} <span style="font-size: 0.8rem; opacity: 0.7;">/ 42</span></span>
                </div>
                <div style="height: 8px; background: rgba(0,0,0,0.06); border-radius: 10px; overflow: hidden; margin-top: 1rem;">
                    <div style="width: {{ min(100, round(($avg_stress / 42) * 100)) }}%; height: 100%; background: linear-gradient(90deg, #ec4899, #f472b6); border-radius: 10px; transition: width 1s ease;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- High Risk per Course Heatmap -->
    <div id="heatmapContainer">
        @if($high_risk_by_course->isNotEmpty())
        <div class="staggered" style="margin-bottom: 2.5rem;">
            <div style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 28px; padding: 2.25rem; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
                    <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.35rem; color: #dc2626; display: flex; align-items: center; gap: 0.75rem; margin: 0;">
                        <i class="ph-bold ph-warning-octagon"></i> High Risk Distribution by Academic Program
                    </h2>
                    <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); background: rgba(239, 68, 68, 0.08); padding: 0.35rem 0.85rem; border-radius: 100px; border: 1px solid rgba(239, 68, 68, 0.2);">
                        Click card to drill down student roster
                    </span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem;">
                    @foreach($high_risk_by_course as $c_name => $group)
                    @php
                        $count = $group->count();
                        $max_c = max(1, $high_risk_by_course->map->count()->max());
                        $intensity = min(100, ($count / $max_c) * 100);
                    @endphp
                    <div onclick="showHighRiskDetails('{{ $c_name }}')" style="background: rgba(239, 68, 68, {{ $intensity/400 + 0.04 }}); border: 1.5px solid rgba(239, 68, 68, {{ $intensity/200 + 0.15 }}); padding: 1.5rem; border-radius: 20px; display: flex; justify-content: space-between; align-items: center;" class="heatmap-card">
                        <div>
                            <div style="font-size: 0.72rem; font-weight: 850; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.3rem;">{{ $c_name }}</div>
                            <div style="font-size: 1.85rem; font-weight: 900; color: var(--text); font-family: 'Outfit', sans-serif;">
                                {{ $count }} <span style="font-size: 0.85rem; font-weight: 700; color: #dc2626; font-family: 'Inter', sans-serif;">High / Critical</span>
                            </div>
                        </div>
                        <div style="width: 44px; height: 44px; border-radius: 14px; background: #ffffff; border: 1px solid rgba(239, 68, 68, 0.2); display: flex; align-items: center; justify-content: center; color: #dc2626; box-shadow: 0 4px 12px rgba(239,68,68,0.12);">
                            <i class="ph-bold ph-magnifying-glass-plus" style="font-size: 1.3rem;"></i>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Clinical Intelligence Log (Table) -->
    <div id="intelligenceFeed" class="staggered" style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 28px; padding: 2.25rem; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text); margin: 0; display: flex; align-items: center; gap: 0.6rem;">
                    <i class="ph-bold ph-list-bullets" style="color: var(--primary);"></i> Clinical Intelligence Log
                </h2>
                <p style="font-size: 0.88rem; color: var(--text-muted); margin-top: 0.2rem; font-weight: 500;">Recent student evaluation records and risk classifications.</p>
            </div>
            
            <div style="display: flex; align-items: center; gap: 0.85rem; flex-wrap: wrap;">
                <!-- Table Risk Quick Pills -->
                <div style="display: flex; gap: 0.35rem; background: var(--surface-2); padding: 0.3rem; border-radius: 12px; border: 1px solid var(--border);">
                    <button type="button" class="preset-pill active" id="tabRiskAll" onclick="filterTableByRisk('ALL')" style="padding: 0.35rem 0.75rem; font-size: 0.72rem;">All</button>
                    <button type="button" class="preset-pill" id="tabRiskLow" onclick="filterTableByRisk('LOW')" style="padding: 0.35rem 0.75rem; font-size: 0.72rem;">Low</button>
                    <button type="button" class="preset-pill" id="tabRiskMod" onclick="filterTableByRisk('MODERATE')" style="padding: 0.35rem 0.75rem; font-size: 0.72rem;">Moderate</button>
                    <button type="button" class="preset-pill" id="tabRiskHigh" onclick="filterTableByRisk('HIGH')" style="padding: 0.35rem 0.75rem; font-size: 0.72rem;">High</button>
                    <button type="button" class="preset-pill" id="tabRiskCrit" onclick="filterTableByRisk('CRITICAL')" style="padding: 0.35rem 0.75rem; font-size: 0.72rem;">Critical</button>
                </div>

                <!-- Search Input -->
                <div style="position: relative;">
                    <i class="ph ph-magnifying-glass" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-dim); font-size: 1.1rem;"></i>
                    <input type="text" id="tableSearch" onkeyup="filterTable()" placeholder="Search student records..." class="search-input-box" style="padding-left: 2.75rem; width: 260px;">
                </div>
            </div>
        </div>
        
        <div style="overflow-x: auto;">
            <table id="recordsTable" style="width: 100%; border-collapse: separate; border-spacing: 0 0.6rem;">
                <thead>
                    <tr style="text-transform: uppercase; letter-spacing: 0.08em; font-size: 0.72rem; color: var(--text-dim); font-weight: 850;">
                        <th style="padding: 1rem 1.5rem; text-align: left;">Student Name</th>
                        <th style="padding: 1rem 1.5rem; text-align: left;">Roll / ID Number</th>
                        <th style="padding: 1rem 1.5rem; text-align: left;">Overall Score</th>
                        <th style="padding: 1rem 1.5rem; text-align: left;">Risk Level</th>
                        <th style="padding: 1rem 1.5rem; text-align: left;">Date</th>
                        <th style="padding: 1rem 1.5rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($recent_assessments as $r)
                @php
                    $riskColor = $colors[$r->risk_level] ?? '#6b7280';
                    $studentName = $r->user->full_name ?? 'Anonymous Student';
                    $studentRoll = $r->user->roll_number ?? 'N/A';
                    $studentEmail = $r->user->email ?? '';
                @endphp
                <tr class="staggered-row record-row" data-risk="{{ strtoupper($r->risk_level) }}" style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 16px; transition: all 0.25s ease;" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='var(--surface-solid)'">
                    <td style="padding: 1.1rem 1.5rem; border-top-left-radius: 16px; border-bottom-left-radius: 16px; border-left: 4px solid {{ $riskColor }};">
                        <div style="display: flex; align-items: center; gap: 0.85rem;">
                            <div style="width: 42px; height: 42px; border-radius: 12px; background: var(--surface-2); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-weight: 900; color: var(--primary); font-size: 1.1rem; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                                {{ strtoupper(substr($studentName, 0, 1)) }}
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span class="search-target-name" style="font-weight: 800; color: var(--text); font-size: 0.95rem;">{{ $studentName }}</span>
                                <span class="search-target-email" style="font-size: 0.75rem; color: var(--text-dim); font-weight: 600;">{{ strtolower($studentEmail) }}</span>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 1.1rem 1.5rem;">
                        <span class="search-target-roll" style="font-weight: 800; background: var(--surface-2); padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.78rem; color: var(--text-muted); border: 1px solid var(--border);">
                            {{ $studentRoll }}
                        </span>
                    </td>
                    <td style="padding: 1.1rem 1.5rem; font-weight: 900; color: var(--primary); font-size: 1.25rem; font-family: 'Outfit', sans-serif;">
                        {{ $r->overall_score }}<span style="font-size:0.75rem; opacity:0.7;">%</span>
                    </td>
                    <td style="padding: 1.1rem 1.5rem;">
                         <span class="search-target-risk" style="font-size: 0.68rem; font-weight: 900; padding: 0.4rem 0.9rem; border-radius: 100px; background: {{ $riskColor }}15; color: {{ $riskColor }}; border: 1.5px solid {{ $riskColor }}40; text-transform: uppercase; letter-spacing: 0.08em;">
                            {{ $r->risk_level }}
                        </span>
                    </td>
                    <td style="padding: 1.1rem 1.5rem; font-weight: 700; color: var(--text-muted); font-size: 0.85rem;">{{ $r->assessment_date->format('M d, Y') }}</td>
                    <td style="padding: 1.1rem 1.5rem; text-align: right; border-top-right-radius: 16px; border-bottom-right-radius: 16px;">
                        @if($r->user_id)
                            <a href="{{ route('counselor.students.show', $r->user_id) }}" class="btn-action-small" style="font-size: 0.72rem; padding: 0.45rem 0.9rem; border-radius: 10px; background: var(--surface-2); color: var(--text); border: 1px solid var(--border); font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <i class="ph ph-user-focus"></i> View Profile
                            </a>
                        @else
                            <span style="font-size: 0.75rem; color: var(--text-dim); font-weight: 600;">N/A</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            
            <div id="noResultsMsg" style="display: none; padding: 3rem 1rem; text-align: center; color: var(--text-muted);">
                <i class="ph ph-magnifying-glass" style="font-size: 3rem; opacity: 0.3; margin-bottom: 0.75rem; display: block;"></i>
                <h4 style="font-weight: 800; font-size: 1.1rem; margin: 0; color: var(--text);">No matching student evaluation records found</h4>
                <p style="font-size: 0.88rem; margin-top: 0.25rem;">Try adjusting your search criteria, risk tier tabs, or date filters.</p>
            </div>
        </div>
    </div>
</div>

<!-- Drill-down Modal -->
<div id="riskDrillDownModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.65); backdrop-filter:blur(12px); align-items:center; justify-content:center; opacity: 0;">
    <div style="background:var(--surface-solid); border: 1px solid var(--border); border-radius:32px; padding:2.25rem; max-width:720px; width:92%; position:relative; box-shadow:var(--shadow-lg);">
        <button onclick="closeRiskModal()" style="position:absolute; top:1.5rem; right:1.5rem; background:var(--surface-2); border:none; width:40px; height:40px; border-radius:12px; cursor:pointer; color:var(--text); display:flex; align-items:center; justify-content:center;"><i class="ph ph-x" style="font-size:1.25rem;"></i></button>
        
        <header style="margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.4rem;">
                <i class="ph-bold ph-warning-octagon" style="color: #dc2626; font-size: 1.5rem;"></i>
                <h3 id="modalCourseTitle" style="font-family:'Outfit',sans-serif; font-size:1.6rem; font-weight:900; color:var(--text); margin:0;">Course Name</h3>
            </div>
            <p style="color:var(--text-muted); font-size:0.88rem; font-weight:500; margin:0;">Students flagged at High or Critical risk levels within this program.</p>
        </header>

        <!-- Search inside modal -->
        <div style="margin-bottom: 1.25rem; position: relative;">
            <i class="ph ph-magnifying-glass" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-dim); font-size: 1rem;"></i>
            <input type="text" id="modalStudentSearch" onkeyup="filterModalStudents()" placeholder="Filter flagged students in program..." class="search-input-box" style="width: 100%; padding-left: 2.6rem;">
        </div>

        <div style="max-height: 400px; overflow-y: auto; padding-right: 0.4rem;" id="modalStudentList">
            <!-- Dynamic Content -->
        </div>
    </div>
</div>

<!-- ═══ KPI Detail Drawer ═══ -->
<div id="kpiDrawerBackdrop" onclick="closeKpiDrawer()"></div>
<aside id="kpiDrawer" aria-label="KPI Detail Drawer">
    <div class="drawer-header">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div id="kpiDrawerBadge" style="font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.12em; padding: 0.3rem 0.8rem; border-radius: 100px; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.6rem;"></div>
                <h3 id="kpiDrawerTitle" style="font-family: 'Outfit', sans-serif; font-size: 1.55rem; font-weight: 900; color: var(--text); margin: 0; line-height: 1.2;"></h3>
                <p id="kpiDrawerSubtitle" style="font-size: 0.84rem; color: var(--text-muted); font-weight: 500; margin-top: 0.3rem;"></p>
            </div>
            <button onclick="closeKpiDrawer()" style="width: 40px; height: 40px; border-radius: 12px; background: var(--surface-2); border: 1px solid var(--border); cursor: pointer; color: var(--text); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s ease;" onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background='var(--surface-2)'">
                <i class="ph ph-x" style="font-size: 1.2rem;"></i>
            </button>
        </div>
        <!-- Search inside drawer -->
        <div style="margin-top: 1rem; position: relative;">
            <i class="ph ph-magnifying-glass" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-dim); font-size: 1rem;"></i>
            <input type="text" id="kpiDrawerSearch" oninput="filterKpiDrawer()" placeholder="Search students..." style="width: 100%; padding: 0.7rem 1rem 0.7rem 2.8rem; border-radius: 12px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text); font-weight: 700; font-size: 0.88rem; outline: none; transition: all 0.2s ease; box-sizing: border-box;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
        </div>
    </div>
    <div class="drawer-body" id="kpiDrawerBody">
        <!-- Populated by JS -->
    </div>
</aside>

@push('scripts')
<!-- Refresh loading bar -->
<div class="refresh-bar" id="refreshBar"></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
let currentChartType = 'line';
let currentTableRiskFilter = 'ALL';
let refreshTimer = null;

// Debounced refresh to avoid too many requests on rapid input
function debouncedRefresh(delay = 400) {
    clearTimeout(refreshTimer);
    refreshTimer = setTimeout(() => refreshAnalytics(), delay);
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.gsap) {
        gsap.from('.staggered', { y: 25, opacity: 0, duration: 0.7, stagger: 0.08, ease: "expo.out", clearProps: "all" });
    }
    setTimeout(() => animateBars(), 350);
    initCharts();
});

function setPreset(preset) {
    document.getElementById('presetInput').value = preset;
    const today = new Date();
    let startDate = '';
    let endDate = today.toISOString().split('T')[0];

    if (preset === '7days') {
        const d = new Date();
        d.setDate(d.getDate() - 7);
        startDate = d.toISOString().split('T')[0];
    } else if (preset === '30days') {
        const d = new Date();
        d.setDate(d.getDate() - 30);
        startDate = d.toISOString().split('T')[0];
    } else if (preset === '6months') {
        const d = new Date();
        d.setMonth(d.getMonth() - 6);
        startDate = d.toISOString().split('T')[0];
    } else if (preset === 'thisyear') {
        startDate = today.getFullYear() + '-01-01';
    }

    document.getElementById('startDate').value = startDate;
    document.getElementById('endDate').value = endDate;

    refreshAnalytics();
}

function animateBars() {
    document.querySelectorAll('.risk-bar-inner[data-width]')
        .forEach(b => b.style.width = b.dataset.width + '%');
}

function filterTableByRisk(riskTier) {
    currentTableRiskFilter = riskTier;
    const buttons = {
        'ALL': 'tabRiskAll',
        'LOW': 'tabRiskLow',
        'MODERATE': 'tabRiskMod',
        'HIGH': 'tabRiskHigh',
        'CRITICAL': 'tabRiskCrit'
    };
    
    Object.keys(buttons).forEach(key => {
        const btn = document.getElementById(buttons[key]);
        if (btn) {
            if (key === riskTier) btn.classList.add('active');
            else btn.classList.remove('active');
        }
    });

    filterTable();
}

function filterTable() {
    const query = document.getElementById('tableSearch')?.value.toLowerCase().trim() || '';
    const rows = document.querySelectorAll('.record-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const name = row.querySelector('.search-target-name')?.textContent.toLowerCase() || '';
        const email = row.querySelector('.search-target-email')?.textContent.toLowerCase() || '';
        const roll = row.querySelector('.search-target-roll')?.textContent.toLowerCase() || '';
        const riskText = row.querySelector('.search-target-risk')?.textContent.toLowerCase() || '';
        const rowRiskAttr = row.getAttribute('data-risk') || '';

        const matchesQuery = !query || name.includes(query) || email.includes(query) || roll.includes(query) || riskText.includes(query);
        const matchesRiskTab = currentTableRiskFilter === 'ALL' || rowRiskAttr === currentTableRiskFilter;

        if (matchesQuery && matchesRiskTab) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const noResults = document.getElementById('noResultsMsg');
    if (noResults) {
        noResults.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}

const refreshAnalytics = async () => {
    const form = document.querySelector('#analyticsFilterForm');
    const spinIcon = document.getElementById('refreshSpinIcon');
    const bar = document.getElementById('refreshBar');
    const chartsContainer = document.getElementById('chartsContainer');

    if (spinIcon) spinIcon.classList.add('ph-spin');
    if (bar) bar.classList.add('active');
    if (chartsContainer) chartsContainer.classList.add('charts-updating');

    const fd = new FormData(form);
    const params = new URLSearchParams(fd);
    const url = new URL(window.location.href);

    for (const [key, value] of params) {
        if (value) url.searchParams.set(key, value);
        else url.searchParams.delete(key);
    }

    history.pushState({}, '', url);

    try {
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const html = await res.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        const containers = ['#analyticsMatrix', '#chartsContainer', '#domainMetricsContainer', '#heatmapContainer', '#intelligenceFeed'];
        containers.forEach(selector => {
            const newEl = doc.querySelector(selector);
            const oldEl = document.querySelector(selector);
            if (newEl && oldEl) oldEl.innerHTML = newEl.innerHTML;
        });

        initCharts(doc);
        animateBars();
        filterTableByRisk(currentTableRiskFilter);

        // Subtle flash-in animation on updated cards
        if (window.gsap) {
            gsap.from(['#analyticsMatrix .kpi-card', '#chartsContainer .chart-card'], {
                opacity: 0, y: 10, duration: 0.4, stagger: 0.05, ease: 'power2.out', clearProps: 'all'
            });
        }
    } catch (err) {
        if (window.App && App.toast) {
            App.toast({ type: 'error', title: 'Error', message: 'Failed to update analytics filters.' });
        }
    } finally {
        if (spinIcon) spinIcon.classList.remove('ph-spin');
        if (bar) bar.classList.remove('active');
        if (chartsContainer) chartsContainer.classList.remove('charts-updating');
    }
};

function switchChartType(type) {
    currentChartType = type;
    document.getElementById('btnChartLine')?.classList.toggle('active', type === 'line');
    document.getElementById('btnChartBar')?.classList.toggle('active', type === 'bar');
    initCharts();
}

// Custom Chart Plugin for Doughnut Center Label
const doughnutCenterPlugin = {
    id: 'doughnutCenterLabel',
    afterDraw(chart) {
        if (chart.config.type !== 'doughnut') return;
        const { width, height, ctx } = chart;
        ctx.save();
        const total = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
        ctx.font = "900 1.5rem Outfit, sans-serif";
        ctx.fillStyle = getComputedStyle(document.body).getPropertyValue('--text').trim() || '#0f172a';
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillText(total.toLocaleString(), width / 2, height / 2 - 8);
        ctx.font = "700 0.72rem Inter, sans-serif";
        ctx.fillStyle = "#64748b";
        ctx.fillText("Total Eval", width / 2, height / 2 + 14);
        ctx.restore();
    }
};

const initCharts = (doc = document) => {
    // 1. Assessment Volume Chart
    const monthlyCanvas = document.getElementById('monthlyChart');
    if (monthlyCanvas) {
        const ctx = monthlyCanvas.getContext('2d');
        const labelsEl = doc.querySelector('#monthlyLabels');
        const countsEl = doc.querySelector('#monthlyCounts');

        if (labelsEl && countsEl) {
            const labels = JSON.parse(labelsEl.textContent);
            const counts = JSON.parse(countsEl.textContent);
            
            // Calculate dynamic summary stats
            if (counts && counts.length > 0) {
                let maxVal = -1;
                let maxIdx = 0;
                let sum = 0;
                counts.forEach((val, idx) => {
                    sum += Number(val);
                    if (Number(val) > maxVal) {
                        maxVal = Number(val);
                        maxIdx = idx;
                    }
                });
                const avg = (sum / counts.length).toFixed(1);
                const peakText = labels[maxIdx] ? `${labels[maxIdx]} (${maxVal})` : `${maxVal}`;

                const peakEl = document.getElementById('peakMonthLabel');
                const avgEl = document.getElementById('monthlyAvgLabel');
                const trajEl = document.getElementById('trajectoryLabel');
                const badgeEl = document.getElementById('volumeTotalBadge');

                if (peakEl) peakEl.textContent = peakText;
                if (avgEl) avgEl.textContent = `${avg} / Mo`;
                if (badgeEl) badgeEl.textContent = `${sum} Total`;
                
                if (trajEl) {
                    const lastVal = Number(counts[counts.length - 1] || 0);
                    const prevVal = Number(counts[counts.length - 2] || 0);
                    if (lastVal >= prevVal) {
                        trajEl.innerHTML = `<i class="ph-bold ph-trend-up" style="color:#10b981;"></i> Upward Trend`;
                        trajEl.style.color = '#10b981';
                    } else {
                        trajEl.innerHTML = `<i class="ph-bold ph-trend-down" style="color:#f59e0b;"></i> Consolidating`;
                        trajEl.style.color = '#f59e0b';
                    }
                }
            }

            if (window.myMonthlyChart) window.myMonthlyChart.destroy();

            // Build gradient for bar mode
            function buildVolumeGradient(ctx, chartArea) {
                const grad = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                grad.addColorStop(0, 'rgba(5, 150, 105, 0.95)');
                grad.addColorStop(1, 'rgba(16, 185, 129, 0.55)');
                return grad;
            }

            // Value-on-top plugin
            const valueLabelsPlugin = {
                id: 'volumeValueLabels',
                afterDatasetsDraw(chart) {
                    if (chart.config.type !== 'bar') return;
                    const { ctx: c } = chart;
                    chart.data.datasets.forEach((dataset, di) => {
                        const meta = chart.getDatasetMeta(di);
                        meta.data.forEach((bar, i) => {
                            const v = dataset.data[i];
                            if (!v) return;
                            c.save();
                            c.font = '800 10px Outfit, sans-serif';
                            c.fillStyle = '#059669';
                            c.textAlign = 'center';
                            c.textBaseline = 'bottom';
                            c.fillText(v, bar.x, bar.y - 3);
                            c.restore();
                        });
                    });
                }
            };
            
            window.myMonthlyChart = new Chart(ctx, {
                type: currentChartType,
                plugins: [valueLabelsPlugin],
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Assessments Conducted',
                        data: counts,
                        borderColor: '#059669',
                        backgroundColor: currentChartType === 'line' ? (context) => {
                            const chart = context.chart;
                            const {ctx: c, chartArea} = chart;
                            if (!chartArea) return null;
                            const gradient = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, 'rgba(5, 150, 105, 0.3)');
                            gradient.addColorStop(0.6, 'rgba(5, 150, 105, 0.08)');
                            gradient.addColorStop(1, 'rgba(5, 150, 105, 0.01)');
                            return gradient;
                        } : (context) => {
                            const chart = context.chart;
                            const {ctx: c, chartArea} = chart;
                            if (!chartArea) return '#10b981';
                            return buildVolumeGradient(c, chartArea);
                        },
                        borderRadius: currentChartType === 'bar' ? { topLeft: 8, topRight: 8 } : 0,
                        borderSkipped: false,
                        fill: currentChartType === 'line',
                        tension: 0.42,
                        pointRadius: currentChartType === 'line' ? 5 : 0,
                        pointHoverRadius: 8,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#059669',
                        pointBorderWidth: 2.5,
                        borderWidth: currentChartType === 'line' ? 2.5 : 0,
                        barThickness: 'flex',
                        maxBarThickness: 52,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 700, easing: 'easeInOutQuart' },
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { family: 'Outfit', size: 13, weight: '800' },
                            bodyFont: { family: 'Inter', size: 12, weight: '600' },
                            padding: 14,
                            cornerRadius: 14,
                            displayColors: false,
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.y} assessment${ctx.parsed.y !== 1 ? 's' : ''}`
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { color: 'rgba(5, 150, 105, 0.06)', drawBorder: false }, 
                            border: { display: false },
                            ticks: { font: { weight: '700', size: 11 }, color: '#94a3b8', padding: 8, stepSize: 1 } 
                        },
                        x: { 
                            grid: { display: false }, 
                            border: { display: false },
                            ticks: { font: { weight: '700', size: 11 }, color: '#64748b', padding: 8 } 
                        }
                    }
                }
            });

        }
    }

    // 2. Risk Doughnut Chart
    const doughnutCanvas = document.getElementById('riskDoughnutChart');
    if (doughnutCanvas) {
        const dCtx = doughnutCanvas.getContext('2d');
        const riskCountsEl = doc.querySelector('#riskCountsJson');
        let rLow = {{ $risk_counts['Low'] ?? 0 }};
        let rMod = {{ $risk_counts['Moderate'] ?? 0 }};
        let rHigh = {{ $risk_counts['High'] ?? 0 }};
        let rCrit = {{ $risk_counts['Critical'] ?? 0 }};

        if (riskCountsEl) {
            try {
                const parsed = JSON.parse(riskCountsEl.textContent);
                rLow = parsed.Low || 0;
                rMod = parsed.Moderate || 0;
                rHigh = parsed.High || 0;
                rCrit = parsed.Critical || 0;
            } catch(e) {}
        }

        if (window.myDoughnutChart) window.myDoughnutChart.destroy();

        window.myDoughnutChart = new Chart(dCtx, {
            type: 'doughnut',
            data: {
                labels: ['Low Risk', 'Moderate Risk', 'High Risk', 'Critical Risk'],
                datasets: [{
                    data: [rLow, rMod, rHigh, rCrit],
                    backgroundColor: ['#10b981', '#f59e0b', '#f97316', '#ef4444'],
                    borderWidth: 3,
                    borderColor: getComputedStyle(document.body).getPropertyValue('--surface-solid').trim() || '#ffffff'
                }]
            },
            plugins: [doughnutCenterPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Outfit', size: 13, weight: '800' },
                        bodyFont: { family: 'Inter', size: 12, weight: '600' },
                        padding: 10,
                        cornerRadius: 10
                    }
                }
            }
        });
    }
};

const highRiskData = @json($high_risk_by_course);

function showHighRiskDetails(course) {
    const students = highRiskData[course];
    if (!students) return;

    document.getElementById('modalCourseTitle').textContent = course;
    const list = document.getElementById('modalStudentList');
    const searchInput = document.getElementById('modalStudentSearch');
    if (searchInput) searchInput.value = '';
    list.innerHTML = '';

    students.forEach(s => {
        const row = document.createElement('div');
        row.className = 'modal-student-row';
        row.style.cssText = 'background:var(--surface-2); padding:1.1rem 1.25rem; border-radius:18px; margin-bottom:0.75rem; border:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;';
        
        const riskColor = s.risk_level === 'Critical' ? '#dc2626' : '#ea580c';
        const userName = (s.user && s.user.full_name) ? s.user.full_name : 'Anonymous Student';
        const userRoll = (s.user && s.user.roll_number) ? s.user.roll_number : 'No ID';
        const initial = userName.charAt(0).toUpperCase();
        
        row.setAttribute('data-search', `${userName.toLowerCase()} ${userRoll.toLowerCase()}`);

        row.innerHTML = `
            <div style="display:flex; align-items:center; gap:0.9rem;">
                <div style="width:42px; height:42px; border-radius:12px; background:var(--surface-solid); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; font-weight:900; color:var(--primary); font-size:1.1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    ${initial}
                </div>
                <div>
                    <div style="font-weight:800; color:var(--text); font-size:0.95rem;">${userName}</div>
                    <div style="font-size:0.75rem; color:var(--text-dim); font-weight:600;">ID: ${userRoll}</div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:0.85rem;">
                <div style="text-align:right;">
                    <div style="font-size:1.15rem; font-weight:900; color:var(--primary); font-family:'Outfit', sans-serif;">${s.overall_score}%</div>
                    <div style="font-size:0.65rem; font-weight:900; color:${riskColor}; text-transform:uppercase; letter-spacing:0.08em;">${s.risk_level}</div>
                </div>
                ${s.user_id ? `<a href="/counselor/students/${s.user_id}" class="btn-action-small" style="font-size:0.72rem; padding:0.4rem 0.85rem; border-radius:10px; text-decoration:none; display:inline-flex; align-items:center; gap:0.3rem;">Profile</a>` : ''}
            </div>
        `;
        list.appendChild(row);
    });

    const modal = document.getElementById('riskDrillDownModal');
    modal.style.display = 'flex';
    if (window.gsap) gsap.to(modal, { opacity: 1, duration: 0.3, ease: "power2.out" });
    else modal.style.opacity = '1';
}

function filterModalStudents() {
    const q = document.getElementById('modalStudentSearch')?.value.toLowerCase().trim() || '';
    const rows = document.querySelectorAll('.modal-student-row');
    rows.forEach(r => {
        const text = r.getAttribute('data-search') || '';
        r.style.display = (!q || text.includes(q)) ? 'flex' : 'none';
    });
}

function closeRiskModal() {
    const modal = document.getElementById('riskDrillDownModal');
    if (window.gsap) {
        gsap.to(modal, { opacity: 0, duration: 0.2, onComplete: () => modal.style.display = 'none' });
    } else {
        modal.style.display = 'none';
    }
}

// initCharts() is now called inside DOMContentLoaded above
// — also re-call on back/forward nav
window.addEventListener('pageshow', () => initCharts());

// ═══ KPI Drawer Logic ═══
const kpiAllData = @json($recent_assessments->values());

const kpiDrawerConfig = {
    all: {
        title: 'All Assessments',
        subtitle: 'Complete list of student evaluation records.',
        badge: '📋 All Records',
        badgeStyle: 'background: rgba(99,102,241,0.15); color: #6366f1; border: 1px solid rgba(99,102,241,0.3);',
        accent: '#6366f1',
        avatarBg: 'rgba(99,102,241,0.12)',
        filter: r => true,
    },
    low: {
        title: 'Low Risk Students',
        subtitle: 'Students in the baseline wellness tier.',
        badge: '🛡️ Low Risk',
        badgeStyle: 'background: rgba(16,185,129,0.15); color: #059669; border: 1px solid rgba(16,185,129,0.3);',
        accent: '#10b981',
        avatarBg: 'rgba(16,185,129,0.12)',
        filter: r => r.risk_level === 'Low',
    },
    observation: {
        title: 'Active Observation',
        subtitle: 'Students in Moderate or High risk requiring monitoring.',
        badge: '👁 Under Observation',
        badgeStyle: 'background: rgba(245,158,11,0.15); color: #d97706; border: 1px solid rgba(245,158,11,0.3);',
        accent: '#f59e0b',
        avatarBg: 'rgba(245,158,11,0.12)',
        filter: r => r.risk_level === 'Moderate' || r.risk_level === 'High',
    },
    critical: {
        title: 'Critical Severity',
        subtitle: 'Students flagged for immediate intervention.',
        badge: '🚨 Critical',
        badgeStyle: 'background: rgba(239,68,68,0.15); color: #dc2626; border: 1px solid rgba(239,68,68,0.3);',
        accent: '#ef4444',
        avatarBg: 'rgba(239,68,68,0.12)',
        filter: r => r.risk_level === 'Critical',
    },
};

const riskColors = { Low: '#10b981', Moderate: '#f59e0b', High: '#f97316', Critical: '#ef4444' };

function openKpiDrawer(type) {
    const cfg = kpiDrawerConfig[type];
    if (!cfg) return;

    document.getElementById('kpiDrawerTitle').textContent = cfg.title;
    document.getElementById('kpiDrawerSubtitle').textContent = cfg.subtitle;
    const badge = document.getElementById('kpiDrawerBadge');
    badge.textContent = cfg.badge;
    badge.style.cssText += cfg.badgeStyle;
    document.getElementById('kpiDrawerSearch').value = '';

    renderKpiDrawerRows(cfg.filter, cfg.avatarBg, cfg.accent);

    document.getElementById('kpiDrawer').classList.add('open');
    document.getElementById('kpiDrawerBackdrop').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function renderKpiDrawerRows(filterFn, avatarBg, accent) {
    const body = document.getElementById('kpiDrawerBody');
    const filtered = kpiAllData.filter(filterFn);
    body.innerHTML = '';

    if (filtered.length === 0) {
        body.innerHTML = `
            <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                <i class="ph ph-folder-open" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 0.75rem;"></i>
                <div style="font-weight: 800; font-size: 1rem; color: var(--text);">No records found</div>
                <div style="font-size: 0.85rem; margin-top: 0.3rem;">No students match this category in the current date range.</div>
            </div>`;
        return;
    }

    filtered.forEach(r => {
        const name = r.user?.full_name ?? 'Anonymous Student';
        const roll = r.user?.roll_number ?? 'N/A';
        const email = r.user?.email ?? '';
        const risk = r.risk_level ?? 'Low';
        const score = r.overall_score ?? 0;
        const date = r.assessment_date ? new Date(r.assessment_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
        const initial = name.charAt(0).toUpperCase();
        const riskColor = riskColors[risk] || '#6b7280';
        const userId = r.user_id;

        const row = document.createElement('div');
        row.className = 'drawer-row';
        row.setAttribute('data-search', `${name.toLowerCase()} ${roll.toLowerCase()} ${email.toLowerCase()} ${risk.toLowerCase()}`);
        row.innerHTML = `
            <div class="drawer-avatar" style="background: ${avatarBg}; color: ${accent}; font-size: 1.1rem;">${initial}</div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 800; color: var(--text); font-size: 0.92rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${name}</div>
                <div style="font-size: 0.72rem; color: var(--text-dim); font-weight: 600;">${roll} &nbsp;·&nbsp; ${date}</div>
            </div>
            <div style="text-align: right; flex-shrink: 0;">
                <div style="font-size: 1.1rem; font-weight: 900; color: ${accent}; font-family: 'Outfit', sans-serif;">${score}%</div>
                <span style="font-size: 0.6rem; font-weight: 900; padding: 0.2rem 0.6rem; border-radius: 100px; background: ${riskColor}18; color: ${riskColor}; border: 1px solid ${riskColor}40; text-transform: uppercase; letter-spacing: 0.06em;">${risk}</span>
            </div>
            ${userId ? `<a href="/counselor/students/${userId}" style="width: 36px; height: 36px; border-radius: 10px; background: var(--surface-solid); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--text-muted); text-decoration: none; flex-shrink: 0; transition: all 0.2s ease;" title="View Profile" onmouseover="this.style.borderColor='${accent}'; this.style.color='${accent}'" onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-muted)'"><i class="ph ph-arrow-square-out" style="font-size: 1rem;"></i></a>` : ''}
        `;
        body.appendChild(row);
    });
}

function filterKpiDrawer() {
    const q = document.getElementById('kpiDrawerSearch').value.toLowerCase().trim();
    document.querySelectorAll('#kpiDrawerBody .drawer-row').forEach(row => {
        const text = row.getAttribute('data-search') || '';
        row.style.display = (!q || text.includes(q)) ? 'flex' : 'none';
    });
}

function closeKpiDrawer() {
    document.getElementById('kpiDrawer').classList.remove('open');
    document.getElementById('kpiDrawerBackdrop').classList.remove('open');
    document.body.style.overflow = '';
}

// Close on Escape key
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeKpiDrawer(); });
</script>
@endpush
@endsection
