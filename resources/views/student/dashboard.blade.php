@extends('layouts.app')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<style>
    .wellness-banner {
        background: linear-gradient(135deg, #064e3b 0%, #059669 60%, #10b981 100%);
        border-radius: var(--radius-lg);
        padding: 3rem;
        color: white;
        margin-bottom: 2.5rem;
        box-shadow: 0 12px 32px rgba(5, 150, 105, 0.2);
        position: relative;
        overflow: hidden;
    }
    .wellness-banner::after {
        content: '🌱';
        position: absolute;
        right: -20px;
        bottom: -20px;
        font-size: 12rem;
        opacity: 0.08;
        transform: rotate(-10deg);
    }
    
    .stat-card-premium {
        background: var(--surface-solid);
        border-radius: var(--radius);
        padding: 1.75rem;
        border: 1px solid var(--border);
        transition: var(--transition);
        box-shadow: var(--shadow-sm);
        position: relative;
    }
    .stat-card-premium:hover { transform: translateY(-5px); box-shadow: var(--shadow); border-color: var(--primary-light); }
    
    .quick-action-premium {
        background: var(--surface-solid);
        border-radius: var(--radius);
        padding: 2rem 1.5rem;
        border: 1px solid var(--border);
        text-decoration: none;
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        box-shadow: var(--shadow-sm);
    }
    .quick-action-premium:hover { 
        background: var(--surface-2);
        border-color: var(--primary);
        transform: translateY(-6px);
        box-shadow: var(--shadow-lg);
    }
    .quick-icon-box {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1.25rem;
        background: var(--surface-2);
        transition: var(--transition);
    }
    .quick-action-premium:hover .quick-icon-box { background: var(--primary); color: white; transform: rotate(10deg) scale(1.1); }

    .chart-container-premium {
        background: var(--surface-solid);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        padding: 2.5rem;
        box-shadow: var(--shadow-sm);
        height: 450px;
        display: flex;
        flex-direction: column;
        transition: var(--transition);
    }
    .chart-container-premium:hover { box-shadow: var(--shadow); }

    @media (max-width: 1024px) {
        .quick-actions-grid { grid-template-columns: repeat(2, 1fr) !important; }
        .stats-grid { grid-template-cols: repeat(2, 1fr) !important; }
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1200px; padding-top: 1rem; padding-bottom: 4rem;">
    
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem;" class="staggered">
        <div>
            <div style="font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 0.5rem;">Student Dashboard</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 800; color: var(--text); letter-spacing: -0.03em;">Hello, {{ explode(' ', $user->full_name)[0] }}</h1>
        </div>
        <div style="display: flex; gap: 1rem;">
            <div class="glass-2" style="padding: 0.75rem 1.25rem; border-radius: 14px; display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 10px #10b981;"></div>
                <span style="font-weight: 700; font-size: 0.8rem; color: var(--text);">Secure Session</span>
            </div>
        </div>
    </div>

    <!-- Daily Tip Banner (Premium Glass) -->
    <div class="wellness-banner staggered">
        <div style="max-width: 800px; position: relative; z-index: 1;">
            <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.2rem; margin-bottom: 1.25rem; color: #a7f3d0; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">Aria's Daily Insight</div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 600; line-height: 1.35; margin-bottom: 2rem; letter-spacing: -0.01em; text-shadow: 0 2px 10px rgba(0,0,0,0.1);">{{ $tip }}</h2>
            <div style="display: flex; gap: 1rem;">
                <button onclick="location.href='{{ route('student.mindfulness.index') }}'" class="btn-primary" style="background: white; color: #064e3b; padding: 0.85rem 2rem;">Start Mindful Moment</button>
                <button onclick="location.href='{{ route('student.chat') }}'" style="padding: 0.85rem 2rem; border-radius: var(--radius-sm); background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.25); font-weight: 700; cursor: pointer; font-size: 0.9rem; backdrop-filter: blur(10px); transition: all 0.3s;">Talk to Aria</button>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-bottom: 2.5rem;">
        <div class="stat-card-premium staggered">
            <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1rem; letter-spacing: 0.1em;">Identity</div>
            <div style="font-size: 1.15rem; font-weight: 800; color: var(--text); margin-bottom: 0.25rem;">{{ $user->roll_number }}</div>
            <div style="font-size: 0.85rem; color: var(--primary); font-weight: 700;">{{ $user->department }}</div>
        </div>
        <div class="stat-card-premium staggered">
            <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1rem; letter-spacing: 0.1em;">Engagement</div>
            <div style="font-size: 2.25rem; font-weight: 900; color: var(--primary); line-height: 1;">{{ count($history) }}</div>
            <div style="font-size: 0.8rem; color: var(--text-dim); font-weight: 600; margin-top: 0.25rem;">Assessments Taken</div>
        </div>
        @if($latest_score)
        <div class="stat-card-premium staggered">
            <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1rem; letter-spacing: 0.1em;">Latest Score</div>
            <div style="font-size: 2.25rem; font-weight: 900; color: var(--text); line-height: 1;">{{ $latest_score->overall_score }}<span style="font-size: 1.15rem; opacity: 0.3; margin-left: 4px;">%</span></div>
            <div style="font-size: 0.8rem; color: var(--text-dim); font-weight: 600; margin-top: 0.25rem;">Wellness Index</div>
        </div>
        <div class="stat-card-premium staggered" style="background: {{ ($latest_score->risk_level === 'Critical' || $latest_score->risk_level === 'High') ? 'rgba(244, 63, 94, 0.05)' : 'rgba(16, 185, 129, 0.05)' }}; border-color: {{ ($latest_score->risk_level === 'Critical' || $latest_score->risk_level === 'High') ? 'rgba(244, 63, 94, 0.2)' : 'rgba(16, 185, 129, 0.2)' }};">
            <div style="font-size: 0.7rem; font-weight: 800; color: {{ ($latest_score->risk_level === 'Critical' || $latest_score->risk_level === 'High') ? 'var(--accent)' : 'var(--primary)' }}; text-transform: uppercase; margin-bottom: 1rem; letter-spacing: 0.1em;">Status</div>
            <div style="font-size: 1.6rem; font-weight: 900; color: {{ ($latest_score->risk_level === 'Critical' || $latest_score->risk_level === 'High') ? 'var(--accent)' : 'var(--primary)' }}; text-transform: uppercase; letter-spacing: -0.02em;">{{ $latest_score->risk_level }}</div>
        </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-bottom: 2.5rem;">
        <a href="{{ route('student.assessment') }}" class="quick-action-premium staggered">
            <div class="quick-icon-box" style="color: var(--primary);">📋</div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--text);">Clinical Scan</h3>
            <p style="color: var(--text-dim); font-size: 0.85rem; line-height: 1.5; font-weight: 500;">Comprehensive pre-assessment protocol.</p>
        </a>
        <a href="{{ route('student.chat') }}" class="quick-action-premium staggered">
            <div class="quick-icon-box" style="color: var(--secondary);">✨</div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--text);">Guardian Chat</h3>
            <p style="color: var(--text-dim); font-size: 0.85rem; line-height: 1.5; font-weight: 500;">Direct AI-assisted wellness companion.</p>
        </a>
        <a href="{{ route('student.mood') }}" class="quick-action-premium staggered">
            <div class="quick-icon-box" style="color: #f59e0b;">📔</div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--text);">Mood Tracker</h3>
            <p style="color: var(--text-dim); font-size: 0.85rem; line-height: 1.5; font-weight: 500;">Secure emotional reflection journal.</p>
        </a>
        <a href="{{ route('student.appointments') }}" class="quick-action-premium staggered">
            <div class="quick-icon-box" style="color: var(--accent);">📅</div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--text);">Counseling</h3>
            <p style="color: var(--text-dim); font-size: 0.85rem; line-height: 1.5; font-weight: 500;">Bridge to professional clinical support.</p>
        </a>
    </div>

    <!-- Charts Section -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2.5rem;">
        <!-- Trend Chart -->
        <div class="chart-container-premium staggered">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 1.25rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.2rem; margin: 0; color: var(--text); letter-spacing: -0.02em;">Wellness Index Trend</h2>
                <a href="{{ route('student.reports.index') }}" class="btn-link">View Detailed Analysis →</a>
            </div>
            <div style="flex: 1; position: relative;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Mood Chart -->
        <div class="chart-container-premium staggered">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 1.25rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.2rem; margin: 0; color: var(--text); letter-spacing: -0.02em;">Emotional Vector Summary</h2>
                <a href="{{ route('student.mood') }}" class="btn-link" style="color: #f59e0b;">Analyze Logs →</a>
            </div>
            <div style="flex: 1; position: relative;">
                <canvas id="moodChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Info Card (Glass 2.0) -->
    <div class="glass-2 staggered" style="padding: 2.5rem; border-radius: var(--radius-lg); border: 1px solid var(--glass-border);">
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 3rem; align-items: center;">
            <div>
                <h3 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.25rem; margin-bottom: 1rem; color: var(--text);">Privacy Statement</h3>
                <p style="font-size: 0.9rem; color: var(--text-dim); line-height: 1.7; font-weight: 500;">
                    All assessment data and reflections are protected under institutional encryption protocols. Clinical staff only access data necessary for dedicated support.
                </p>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                <div style="text-align: center;">
                    <div style="width: 8px; height: 8px; background: var(--primary); border-radius: 50%; display: inline-block; margin-bottom: 0.5rem;"></div>
                    <div style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">Low Risk</div>
                    <div style="font-size: 0.65rem; color: var(--text-dim);">Wellness Phase</div>
                </div>
                <div style="text-align: center;">
                    <div style="width: 8px; height: 8px; background: #f59e0b; border-radius: 50%; display: inline-block; margin-bottom: 0.5rem;"></div>
                    <div style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">Moderate</div>
                    <div style="font-size: 0.65rem; color: var(--text-dim);">Reflection Zone</div>
                </div>
                <div style="text-align: center;">
                    <div style="width: 8px; height: 8px; background: var(--accent); border-radius: 50%; display: inline-block; margin-bottom: 0.5rem;"></div>
                    <div style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">Clinical</div>
                    <div style="font-size: 0.65rem; color: var(--text-dim);">Priority Support</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.weight = '600';
Chart.defaults.color = '{{ Auth::user()->theme === "dark" ? "#9ca3af" : "#64748b" }}';
Chart.defaults.borderColor = 'rgba(0,0,0,0.05)';

@if(count($chart_scores) > 0)
(function() {
    const ctx = document.getElementById('trendChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 350);
    gradient.addColorStop(0, 'rgba(5, 150, 105, 0.25)');
    gradient.addColorStop(1, 'rgba(5, 150, 105, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chart_labels) !!},
            datasets: [{
                label: 'Wellness Score',
                data: {!! json_encode($chart_scores) !!},
                borderColor: '#059669',
                borderWidth: 4,
                backgroundColor: gradient,
                tension: 0.45,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#059669',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 9,
                pointHoverBorderWidth: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0, max: 100, ticks: { callback: v => v + '%', stepSize: 20 } },
                x: { grid: { display: false } }
            }
        }
    });
})();
@endif

@if(count($mood_data) > 0)
(function() {
    const ctx = document.getElementById('moodChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 350);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.25)');
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($mood_labels) !!},
            datasets: [{
                label: 'Mood Level',
                data: {!! json_encode($mood_data) !!},
                borderColor: '#6366f1',
                borderWidth: 4,
                backgroundColor: gradient,
                tension: 0.45,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#6366f1',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 9
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleFont: { size: 13, weight: '800' },
                    padding: 12,
                    callbacks: {
                        label: (ctx) => {
                            const labels = ['', 'Critical', 'Suboptimal', 'Neutral', 'Vibrant', 'Optimal'];
                            return ' Vector: ' + (labels[ctx.raw] || ctx.raw);
                        }
                    }
                }
            },
            scales: {
                y: { 
                    min: 1, max: 5, 
                    ticks: { 
                        stepSize: 1,
                        callback: v => ['', '😰', '🙁', '😐', '🙂', '🌟'][v] || v 
                    } 
                },
                x: { grid: { display: false } }
            }
        }
    });
})();
@endif
</script>
@endpush
