<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Summary - {{ $student->full_name }}</title>
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
            border-bottom: 2px solid #0f2c59;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .lh-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 12px;
        }

        .lh-logo-left, .lh-logo-right {
            flex-shrink: 0;
        }

        .lh-logo-left img {
            width: 76px;
            height: 76px;
            object-fit: contain;
        }

        .lh-logo-right img {
            width: 80px;
            height: auto;
            object-fit: contain;
        }

        .lh-center-text {
            flex: 1;
            text-align: center;
            line-height: 1.25;
        }

        .lh-republic {
            font-size: 11px;
            font-style: italic;
            color: #334155;
        }

        .lh-univ {
            font-size: 18px;
            font-weight: bold;
            color: #0f2c59;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 2px 0;
        }

        .lh-campus {
            font-size: 12px;
            font-weight: 600;
            color: #334155;
        }

        .lh-links {
            font-size: 9px;
            color: #0284c7;
            margin-top: 3px;
            font-family: Arial, sans-serif;
        }

        .lh-divider {
            height: 3px;
            background: linear-gradient(90deg, #0f2c59 0%, #d97706 50%, #0f2c59 100%);
            margin-top: 10px;
            border: none;
        }

        .doc-title-box {
            margin-top: 15px;
            text-align: center;
        }

        .doc-title-box h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #0f172a;
        }

        .doc-title-box p {
            margin: 4px 0 0;
            font-size: 13px;
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

        @media (max-width: 768px) {
            body { padding: 1.25rem 1rem 3rem; }
            .confidential-stamp { position: static; display: inline-block; margin-bottom: 1rem; transform: none; }
            .no-print { position: static; width: 100%; margin-bottom: 1rem; min-height: 44px; }
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
    <button class="no-print" onclick="window.print()">🖨️ Download / Print Support File</button>

    <div class="confidential-stamp">Strictly Confidential</div>

    <div class="header">
        <div class="lh-container">
            <div class="lh-logo-left">
                <img src="{{ asset('images/letterhead/image3.png') }}" alt="PSU Seal">
            </div>
            <div class="lh-center-text">
                <div class="lh-republic">Republic of the Philippines</div>
                <div class="lh-univ">Pangasinan State University</div>
                <div class="lh-campus">Lingayen, Pangasinan</div>
                <div class="lh-links">
                    <span>www.psu.edu.ph</span> • <span>president@psu.edu.ph</span> • <span>www.facebook.com/PSUroars</span>
                </div>
            </div>
            <div class="lh-logo-right">
                <img src="{{ asset('images/letterhead/image6.png') }}" alt="Bagong Pilipinas">
            </div>
        </div>
        <div class="lh-divider"></div>
        <div class="doc-title-box">
            <h1>Student Support Summary & Wellness History</h1>
            <p>PSU Mental Health & Wellness Portal • Confidential Clinical Record</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Student Information</div>
        <div class="info-grid">
            <div class="info-item"><span class="info-label">Full Name:</span> {{ $student->full_name }}</div>
            <div class="info-item"><span class="info-label">Roll Number:</span> {{ $student->roll_number }}</div>
            <div class="info-item"><span class="info-label">Department:</span> {{ $student->department ?? 'N/A' }}</div>
            <div class="info-item"><span class="info-label">File Generated:</span> {{ now()->format('M d, Y H:i') }}</div>
            <div class="info-item"><span class="info-label">Guardian:</span> {{ $student->guardian_name ?? 'N/A' }}{{ $student->guardian_relationship ? ' (' . $student->guardian_relationship . ')' : '' }}</div>
            <div class="info-item"><span class="info-label">Guardian Contact:</span> {{ $student->guardian_contact ?? 'N/A' }}{{ $student->guardian_email ? ' • ' . $student->guardian_email : '' }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Longitudinal Wellness Trajectory</div>
        <div class="chart-container">
            <canvas id="exportChart"></canvas>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Check-in History</div>
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
        <div style="margin-bottom: 12px; border-bottom: 2px solid #0f2c59; padding-bottom: 8px;">
            <img src="{{ asset('images/letterhead/image7.png') }}" alt="PSU Accreditation Badges" style="max-width: 100%; height: auto; max-height: 28px; display: block; margin: 0 auto;">
        </div>
        <div>
            This document contains sensitive psychiatric and wellness data. Unauthorized disclosure is a violation of the Data Privacy Act of 2012 (RA 10173).
        </div>
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
