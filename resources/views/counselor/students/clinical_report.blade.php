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
        .no-print-bar {
            position: fixed;
            top: 20px;
            left: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        .no-print-btn {
            padding: 10px 18px;
            background: #0f2c59;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 13px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .no-print-btn:hover {
            background: #1e40af;
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            body { padding: 1.25rem 1rem 3rem; }
            .confidential-stamp { position: static; display: inline-block; margin-bottom: 1rem; transform: none; }
            .no-print-bar { position: static; width: 100%; margin-bottom: 1.5rem; flex-direction: column; }
            .no-print-btn { width: 100%; justify-content: center; min-height: 44px; }
        }

        body.pdf-export,
        @media print {
            .no-print, .no-print-bar { display: none !important; }
            body { padding: 0 !important; background: #ffffff !important; }
            @page { margin: 15mm; size: A4 portrait; }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="no-print-bar no-print">
        <button class="no-print-btn" onclick="window.print()">🖨️ Print Clinical File</button>
        <button class="no-print-btn" id="exportPdfBtn" onclick="exportPDF()" style="background: #0d9488;">📄 Export PDF</button>
        <a href="javascript:history.back()" class="no-print-btn" style="background: #475569;">← Back</a>
    </div>

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

        async function exportPDF() {
            const btn = document.getElementById('exportPdfBtn');
            const orig = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = 'Generating PDF...';
            }
            document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
            document.body.classList.add('pdf-export');

            try {
                const { jsPDF } = window.jspdf;
                const canvas = await html2canvas(document.body, {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#ffffff'
                });
                const imgData = canvas.toDataURL('image/jpeg', 0.95);
                const pdf = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });
                const pageW = pdf.internal.pageSize.getWidth();
                const pageH = pdf.internal.pageSize.getHeight();
                const imgH  = (canvas.height * pageW) / canvas.width;
                let remaining = imgH, yOffset = 0;
                while (remaining > 0) {
                    pdf.addImage(imgData, 'JPEG', 0, -yOffset, pageW, imgH);
                    remaining -= pageH;
                    yOffset += pageH;
                    if (remaining > 0) pdf.addPage();
                }
                pdf.save('PSU_Clinical_Support_File_{{ \Illuminate\Support\Str::slug($student->full_name) }}.pdf');
            } catch (e) {
                console.error(e);
                window.print();
            } finally {
                document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
                document.body.classList.remove('pdf-export');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = orig;
                }
            }
        }
    </script>
</body>
</html>
