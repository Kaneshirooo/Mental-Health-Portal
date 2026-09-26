@extends('layouts.app')

@push('styles')
<style>
    .reports-container {
        max-width: 1380px;
        margin: 0 auto;
        padding: 2.25rem 1.75rem 6rem;
    }
    .kpi-card {
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: 24px;
        padding: 1.75rem;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }
    .dark-mode .kpi-card {
        background: #1e293b;
        border-color: #334155;
    }
    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
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
    .chart-card {
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: 28px;
        padding: 2.25rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.03);
        transition: all 0.3s ease;
    }
    .dark-mode .chart-card {
        background: #1e293b;
        border-color: #334155;
    }
    .preset-pill {
        padding: 0.6rem 1.25rem;
        border-radius: 100px;
        font-size: 0.9rem;
        font-weight: 850;
        background: var(--surface-2);
        color: var(--text);
        border: 1px.5px solid var(--border);
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .preset-pill:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-1px);
        background: var(--surface-solid);
    }
    .preset-pill.active {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        color: #ffffff !important;
        border-color: #059669 !important;
        box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
    }
    .chart-type-btn {
        background: var(--surface-2);
        border: 1px solid var(--border);
        color: var(--text-muted);
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .chart-type-btn.active {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
    }
    .heatmap-card {
        background: var(--surface-solid);
        border-radius: 20px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .heatmap-card:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 10px 25px rgba(239, 68, 68, 0.15);
    }
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
        box-shadow: 0 0 0 3px var(--primary-glow);
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
        transition: all 0.2s ease;
        height: 48px;
        flex: 1;
        min-width: 210px;
    }
    .filter-select:focus, .filter-select:hover {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
    }
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
                <div style="display: flex; align-items: center; gap: 0.75rem; background: var(--surface-solid); padding: 0 1.25rem; border-radius: 14px; border: 1.5px solid var(--border); height: 48px; flex: 1.5; min-width: 340px;">
                    <i class="ph-bold ph-calendar-blank" style="color: var(--primary); font-size: 1.25rem; flex-shrink: 0;"></i>
                    <span style="font-size: 0.8rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em;">From</span>
                    <input type="date" id="startDate" name="start_date" value="{{ $start_date }}" onchange="refreshAnalytics()" style="background: transparent; border: none; color: var(--text); font-weight: 800; font-size: 0.95rem; outline: none; cursor: pointer; font-family: inherit; flex: 1;">
                    <span style="font-size: 0.8rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em;">To</span>
                    <input type="date" id="endDate" name="end_date" value="{{ $end_date }}" onchange="refreshAnalytics()" style="background: transparent; border: none; color: var(--text); font-weight: 800; font-size: 0.95rem; outline: none; cursor: pointer; font-family: inherit; flex: 1;">
                </div>

                <!-- Semester Select -->
                <select name="semester" onchange="refreshAnalytics()" class="filter-select">
                    <option value="">All Semesters</option>
                    @foreach ($semesters as $s)
                        <option value="{{ $s }}" {{ $semester === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>

                <!-- Course Select -->
                <select name="course" onchange="refreshAnalytics()" class="filter-select">
                    <option value="">All Courses / Programs</option>
                    @foreach ($courses as $c)
                        <option value="{{ $c }}" {{ $course === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>

                <!-- Risk Level Select -->
                <select name="risk_level" onchange="refreshAnalytics()" class="filter-select">
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
    <div id="analyticsMatrix" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem; margin-bottom: 2.5rem;">
        
        <!-- Total Scale / Assessments -->
        <div class="kpi-card staggered" style="border-top: 4px solid #6366f1;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div class="kpi-icon" style="background: rgba(99, 102, 241, 0.12); color: #4f46e5;">
                    <i class="ph-bold ph-clipboard-text"></i>
                </div>
                <span style="font-size: 0.72rem; font-weight: 850; background: rgba(99, 102, 241, 0.12); color: #4f46e5; padding: 0.35rem 0.75rem; border-radius: 100px;">
                    {{ number_format($total_students) }} Active Students
                </span>
            </div>
            <div style="font-size: 0.7rem; font-weight: 850; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.3rem;">Total Assessment Volume</div>
            <div style="font-size: 2.5rem; font-weight: 900; color: var(--text); line-height: 1; font-family: 'Outfit', sans-serif;">{{ number_format($total_assessments) }}</div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.3rem;">
                <i class="ph-bold ph-check-circle" style="color: #6366f1;"></i> Completed evaluations
            </div>
        </div>

        <!-- Low Risk -->
        <div class="kpi-card staggered" style="border-top: 4px solid #10b981;">
            @php
                $low_cnt = $risk_counts['Low'] ?? 0;
                $low_pct = $total_assessments > 0 ? round(($low_cnt / $total_assessments) * 100) : 0;
            @endphp
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                    <i class="ph-bold ph-shield-check"></i>
                </div>
                <span style="font-size: 0.72rem; font-weight: 850; background: rgba(16, 185, 129, 0.12); color: #059669; padding: 0.35rem 0.75rem; border-radius: 100px;">
                    {{ $low_pct }}% Share
                </span>
            </div>
            <div style="font-size: 0.7rem; font-weight: 850; color: #059669; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.3rem;">Low Risk (Baseline)</div>
            <div style="font-size: 2.5rem; font-weight: 900; color: #059669; line-height: 1; font-family: 'Outfit', sans-serif;">{{ number_format($low_cnt) }}</div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.3rem;">
                <i class="ph-bold ph-trend-up" style="color: #059669;"></i> Stable wellness status
            </div>
        </div>

        <!-- Observation / Moderate-High -->
        <div class="kpi-card staggered" style="border-top: 4px solid #f59e0b;">
            @php
                $obs_cnt = ($risk_counts['Moderate'] ?? 0) + ($risk_counts['High'] ?? 0);
                $obs_pct = $total_assessments > 0 ? round(($obs_cnt / $total_assessments) * 100) : 0;
            @endphp
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.12); color: #d97706;">
                    <i class="ph-bold ph-eye"></i>
                </div>
                <span style="font-size: 0.72rem; font-weight: 850; background: rgba(245, 158, 11, 0.12); color: #d97706; padding: 0.35rem 0.75rem; border-radius: 100px;">
                    {{ $obs_pct }}% Share
                </span>
            </div>
            <div style="font-size: 0.7rem; font-weight: 850; color: #d97706; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.3rem;">Active Observation</div>
            <div style="font-size: 2.5rem; font-weight: 900; color: #d97706; line-height: 1; font-family: 'Outfit', sans-serif;">{{ number_format($obs_cnt) }}</div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.3rem;">
                <i class="ph-bold ph-clock" style="color: #d97706;"></i> Moderate to High cases
            </div>
        </div>

        <!-- Critical Risk -->
        <div class="kpi-card staggered" style="border-top: 4px solid #ef4444;">
            @php
                $crit_cnt = $risk_counts['Critical'] ?? 0;
                $crit_pct = $total_assessments > 0 ? round(($crit_cnt / $total_assessments) * 100) : 0;
            @endphp
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div class="kpi-icon" style="background: rgba(239, 68, 68, 0.12); color: #dc2626;">
                    <i class="ph-bold ph-warning-octagon"></i>
                </div>
                <span style="font-size: 0.72rem; font-weight: 850; background: rgba(239, 68, 68, 0.12); color: #dc2626; padding: 0.35rem 0.75rem; border-radius: 100px;">
                    {{ $crit_pct }}% Share
                </span>
            </div>
            <div style="font-size: 0.7rem; font-weight: 850; color: #dc2626; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.3rem;">Critical Severity</div>
            <div style="font-size: 2.5rem; font-weight: 900; color: #dc2626; line-height: 1; font-family: 'Outfit', sans-serif;">{{ number_format($crit_cnt) }}</div>
            <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.3rem;">
                <i class="ph-bold ph-bell-ringing" style="color: #dc2626;"></i> Immediate intervention
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
                    <div style="display: flex; gap: 0.4rem; background: var(--surface-2); padding: 0.25rem; border-radius: 12px; border: 1px solid var(--border);">
                        <button type="button" class="chart-type-btn active" id="btnChartLine" onclick="switchChartType('line')"><i class="ph ph-chart-line-up"></i> Line</button>
                        <button type="button" class="chart-type-btn" id="btnChartBar" onclick="switchChartType('bar')"><i class="ph ph-chart-bar"></i> Bar</button>
                    </div>
                </div>

                <div style="height: 285px; position: relative;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <!-- Volume Analytics Footer Summary Strip -->
            <div style="margin-top: 1.25rem; padding-top: 1.1rem; border-top: 1px solid var(--border); display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.85rem;">
                <div style="background: var(--surface-2); padding: 0.75rem 1rem; border-radius: 14px; border: 1px solid var(--border);">
                    <div style="font-size: 0.68rem; font-weight: 850; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em;">Peak Month</div>
                    <div style="font-size: 1.05rem; font-weight: 900; color: #059669; font-family: 'Outfit', sans-serif; margin-top: 0.15rem;" id="peakMonthLabel">--</div>
                </div>
                <div style="background: var(--surface-2); padding: 0.75rem 1rem; border-radius: 14px; border: 1px solid var(--border);">
                    <div style="font-size: 0.68rem; font-weight: 850; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em;">Monthly Average</div>
                    <div style="font-size: 1.05rem; font-weight: 900; color: var(--text); font-family: 'Outfit', sans-serif; margin-top: 0.15rem;" id="monthlyAvgLabel">--</div>
                </div>
                <div style="background: var(--surface-2); padding: 0.75rem 1rem; border-radius: 14px; border: 1px solid var(--border);">
                    <div style="font-size: 0.68rem; font-weight: 850; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em;">Volume Trajectory</div>
                    <div style="font-size: 0.88rem; font-weight: 850; color: #10b981; margin-top: 0.2rem; display: flex; align-items: center; gap: 0.3rem;" id="trajectoryLabel">
                        <i class="ph-bold ph-trend-up"></i> Steady Growth
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
let currentChartType = 'line';
let currentTableRiskFilter = 'ALL';

document.addEventListener('DOMContentLoaded', () => {
    if (window.gsap) {
        gsap.from('.staggered', { y: 25, opacity: 0, duration: 0.7, stagger: 0.08, ease: "expo.out", clearProps: "all" });
    }

    setTimeout(() => {
        animateBars();
    }, 350);
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
    if (spinIcon) spinIcon.classList.add('ph-spin');

    const fd = new FormData(form);
    const params = new URLSearchParams(fd);
    const url = new URL(window.location.href);
    
    for (const [key, value] of params) {
        if (value) url.searchParams.set(key, value);
        else url.searchParams.delete(key);
    }
    
    history.pushState({}, '', url);
    document.body.style.opacity = '0.85';
    
    try {
        const res = await fetch(url);
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
    } catch (err) {
        if (window.App && App.toast) {
            App.toast({ type: 'error', title: 'Error', message: 'Failed to update analytics filters.' });
        }
    } finally {
        document.body.style.opacity = '1';
        if (spinIcon) spinIcon.classList.remove('ph-spin');
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

initCharts();
</script>
@endpush
@endsection
