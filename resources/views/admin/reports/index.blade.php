@extends('layouts.app')

@section('content')
<main class="main-content">
<div class="container" style="max-width: 1200px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <div>
            <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Diagnostic Intelligence Hub</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Institutional Analytics</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">System-wide wellness metrics and clinical trends.</p>
        </div>
        <a href="{{ route('admin.reports.export') }}" class="btn-primary" style="padding: 0.65rem 1.25rem; border-radius: var(--radius-sm); background: var(--primary); color: white; text-decoration: none; font-weight: 600; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); transition: var(--transition);">Systems Export ↓</a>
    </div>

    <!-- Analytics Matrix -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: var(--surface-solid); border-radius: var(--radius); padding: 1.5rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
            <div style="font-size: 0.65rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 1rem; letter-spacing: 0.04em;">Intake Volume</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary);">{{ number_format($total_assessments) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 400; margin-top: 0.25rem;">{{ number_format($total_students) }} students</div>
        </div>
        <div style="background: #f0fdf4; border-radius: var(--radius); padding: 1.5rem; border: 1px solid rgba(22, 163, 74, 0.2);">
            <div style="font-size: 0.65rem; font-weight: 700; color: #166534; text-transform: uppercase; margin-bottom: 1rem; letter-spacing: 0.04em;">Low Risk</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #166534;">{{ $risk_counts['Low'] ?? 0 }}</div>
            <div style="font-size: 0.75rem; color: #166534; opacity: 0.9; font-weight: 500; margin-top: 0.25rem;">Standard Baseline</div>
        </div>
        <div style="background: #fffbeb; border-radius: var(--radius); padding: 1.5rem; border: 1px solid rgba(217, 119, 6, 0.2);">
            <div style="font-size: 0.65rem; font-weight: 700; color: #92400e; text-transform: uppercase; margin-bottom: 1rem; letter-spacing: 0.04em;">Mid Priority</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #92400e;">{{ ($risk_counts['Moderate'] ?? 0) + ($risk_counts['High'] ?? 0) }}</div>
            <div style="font-size: 0.75rem; color: #92400e; opacity: 0.9; font-weight: 500; margin-top: 0.25rem;">Focused Observation</div>
        </div>
        <div style="background: #fff1f2; border-radius: var(--radius); padding: 1.5rem; border: 1px solid rgba(225, 29, 72, 0.2);">
            <div style="font-size: 0.65rem; font-weight: 700; color: #991b1b; text-transform: uppercase; margin-bottom: 1rem; letter-spacing: 0.04em;">Critical Intervention</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #991b1b;">{{ $risk_counts['Critical'] ?? 0 }}</div>
            <div style="font-size: 0.75rem; color: #991b1b; opacity: 0.9; font-weight: 500; margin-top: 0.25rem;">Urgent Clinical Action</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Volume chart -->
        <div class="card" style="padding: 1.5rem; border-radius: var(--radius);">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.15rem; margin-bottom: 1.5rem; color: var(--text);">Assessment Patterns</h2>
            <div style="height: 300px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Risk Distribution -->
        <div class="card" style="padding: 2rem; border-radius: var(--radius); border: 1px solid var(--border);">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; margin-bottom: 2rem; color: var(--text);">Risk Propensity Scale</h2>
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
                <div class="risk-bar-row" style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">
                        <span>{{ $lvl }}</span>
                        <span style="color: {{ $color }};">{{ $pct }}% ({{ $cnt }})</span>
                    </div>
                    <div style="height: 12px; background: var(--surface-2); border-radius: 10px; overflow: hidden; border: 1px solid var(--border);">
                        <div class="risk-bar-inner" data-width="{{ $pct }}"
                             style="background:{{ $color }};width:0%;height:100%;transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div style="margin-top: 2rem; padding: 1.5rem; background: var(--surface-2); border-radius: 20px; border: 1px solid var(--border);">
                <h4 style="font-weight: 800; color: var(--text); margin-bottom: 0.5rem; font-size: 0.9rem;">Diagnostic Summary</h4>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.6; font-weight: 500;">Highest volume observed in the <strong style="color: var(--primary);">{{ array_search($risk_counts->max(), $risk_counts->toArray()) ?: 'N/A' }}</strong> demographic. Recommending focused intervention for high-priority sectors.</p>
            </div>
        </div>
    </div>

    <!-- Latest assessments table -->
    <div class="card" style="padding: 0; border-radius: var(--radius); overflow: hidden; border: 1px solid var(--border); box-shadow: var(--shadow-sm); margin-bottom: 2rem;">
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); background: var(--surface-2); display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.15rem; margin: 0; color: var(--text);">Clinical Feed</h2>
            <span style="font-weight: 600; color: var(--text-muted); font-size: 0.75rem;">Last 20 records</span>
        </div>
        <div class="table-wrapper">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead style="background: var(--surface-2); border-bottom: 1px solid var(--border);">
                    <tr>
                        <th style="padding: 1rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: left;">Identity</th>
                        <th style="padding: 1rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: left;">ID</th>
                        <th style="padding: 1rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: left;">Score</th>
                        <th style="padding: 1rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: left;">Priority</th>
                        <th style="padding: 1rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Date</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($recent_assessments as $r)
                @php
                    $nameParts = explode(' ', $r->user->full_name ?? 'U N');
                    $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                @endphp
                <tr style="border-bottom: 1px solid var(--border); transition: var(--transition);">
                    <td style="padding: 1rem 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--surface-2); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; border: 1px solid var(--border);">
                                {{ $initials }}
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-weight: 600; color: var(--text); font-size: 0.88rem;">{{ $r->user->full_name ?? 'Unknown' }}</span>
                                <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 400;">{{ $r->user->email ?? '' }}</span>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 1rem 1.5rem;"><span style="font-weight: 600; background: var(--surface-2); padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.72rem; color: var(--text-muted); border: 1px solid var(--border);">{{ $r->user->roll_number ?? '' }}</span></td>
                    <td style="padding: 1rem 1.5rem; font-weight: 700; color: var(--primary); font-size: 0.9rem;">{{ $r->overall_score }}%</td>
                    <td style="padding: 1rem 1.5rem;">
                         <span style="font-size: 0.62rem; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 4px; border: 1px solid transparent; background: var(--surface-2); color: var(--text-muted);">
                            {{ strtoupper($r->risk_level) }}
                        </span>
                    </td>
                    <td style="padding: 1rem 1.5rem; font-weight: 500; color: var(--text-muted); font-size: 0.8rem; text-align: right;">{{ $r->assessment_date->format('M d, Y') }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer class="footer">
    <p>© {{ date('Y') }} PSU Mental Health Portal</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
window.addEventListener('load', () => {
    document.querySelectorAll('.risk-bar-inner[data-width]')
        .forEach(b => setTimeout(() => b.style.width = b.dataset.width + '%', 400));
});

new Chart(document.getElementById('monthlyChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthly_labels) !!},
        datasets: [{
            label: 'Assessments',
            data: {!! json_encode($monthly_counts) !!},
            backgroundColor: '#0d9488',
            borderRadius: 4,
            barThickness: 20
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color:'rgba(0,0,0,.05)' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
</main>
@endsection
