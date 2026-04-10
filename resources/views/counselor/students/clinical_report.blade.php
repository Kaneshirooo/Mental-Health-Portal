<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinical Report - {{ $student->full_name }}</title>
    <style>
        :root {
            --primary-black: #000000;
            --secondary-gray: #4b5563;
            --border-light: #e5e7eb;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.5;
            color: var(--primary-black);
            margin: 0;
            padding: 40px;
            background: #fff;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid var(--primary-black);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 14px;
            color: var(--secondary-gray);
        }

        .confidential-stamp {
            position: absolute;
            top: 20px;
            right: 40px;
            border: 2px solid #dc2626;
            color: #dc2626;
            padding: 5px 10px;
            font-weight: bold;
            text-transform: uppercase;
            transform: rotate(5deg);
            font-size: 12px;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid var(--primary-black);
            margin-bottom: 15px;
            padding-bottom: 5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .info-item {
            font-size: 14px;
        }

        .info-label {
            font-weight: bold;
            width: 150px;
            display: inline-block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid var(--border-light);
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f9fafb;
            font-weight: bold;
        }

        .risk-badge {
            font-weight: bold;
            text-transform: uppercase;
        }

        .chart-container {
            width: 100%;
            height: 250px;
            margin: 20px 0;
            text-align: center;
        }

        .footer {
            margin-top: 50px;
            font-size: 11px;
            text-align: center;
            color: var(--secondary-gray);
            border-top: 1px solid var(--border-light);
            padding-top: 10px;
        }

        .signature-line {
            margin-top: 60px;
            border-top: 1px solid var(--primary-black);
            width: 250px;
            text-align: center;
            font-size: 12px;
            padding-top: 5px;
        }

        /* Screen only elements */
        .no-print {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 10px 20px;
            background: #0d9488;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            @page { margin: 2cm; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</head>
<body>
    <button class="no-print" onclick="window.print()">🖨️ Download / Print Clinical File</button>

    <div class="confidential-stamp">Strictly Confidential</div>

    <div class="header">
        <h1>PSU Mental Health Portal</h1>
        <p>Clinical Case Documentation & Longitudinal Trajectory</p>
    </div>

    <div class="section">
        <div class="section-title">Patient Identification</div>
        <div class="info-grid">
            <div class="info-item"><span class="info-label">Full Name:</span> {{ $student->full_name }}</div>
            <div class="info-item"><span class="info-label">Roll Number:</span> {{ $student->roll_number }}</div>
            <div class="info-item"><span class="info-label">Department:</span> {{ $student->department ?? 'N/A' }}</div>
            <div class="info-item"><span class="info-label">File Generated:</span> {{ now()->format('M d, Y H:i') }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Longitudinal Wellness Trajectory</div>
        <div class="chart-container">
            <canvas id="exportChart"></canvas>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Diagnostic History</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Overall Wellness</th>
                    <th>Risk Classification</th>
                    <th>D-A-S Breakdown</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assessments as $a)
                <tr>
                    <td>{{ $a->assessment_date->format('M d, Y') }}</td>
                    <td style="font-weight: bold;">{{ $a->overall_score }}%</td>
                    <td class="risk-badge">{{ $a->risk_level }}</td>
                    <td>D:{{ $a->depression_score }}% | A:{{ $a->anxiety_score }}% | S:{{ $a->stress_score }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Archived Counselor Observations</div>
        @forelse($notes as $note)
            <div style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px dashed var(--border-light);">
                <div style="font-weight: bold; font-size: 13px;">{{ $note->created_at->format('M d, Y') }} — Observation</div>
                <p style="font-size: 13px; margin: 5px 0;">{{ $note->note_text }}</p>
                @if($note->recommendation)
                    <p style="font-size: 13px; margin: 0; font-style: italic;"><strong>Management Plan:</strong> {{ $note->recommendation }}</p>
                @endif
            </div>
        @empty
            <p style="font-size: 13px; font-style: italic; color: var(--secondary-gray);">No clinical notes recorded.</p>
        @endforelse
    </div>

    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div class="signature-line">
            Authorized Counselor Signature
        </div>
        <div style="font-size: 12px;">
            Page 1 of 1
        </div>
    </div>

    <div class="footer">
        This document contains sensitive psychiatric and wellness data. Unauthorized disclosure is a violation of the Data Privacy Act of 2012 (RA 10173).
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('exportChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chart_labels) !!},
                    datasets: [{
                        label: 'Wellness Index',
                        data: {!! json_encode($chart_scores) !!},
                        borderColor: '#000',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#000',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { min: 0, max: 100, ticks: { stepSize: 20 } },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
</body>
</html>
