@extends('layouts.app')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<style>
    #aiSummaryBox { animation: fadeInUp 0.5s ease; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .note-item {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    .note-date { font-size: 0.75rem; font-weight: 700; color: var(--text-dim); margin-bottom: 0.75rem; text-transform: uppercase; }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1200px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem;">
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            @php
                $names = explode(' ', $student?->full_name ?? 'Student Name');
                $initials = strtoupper(substr($names[0] ?? 'S', 0, 1) . (isset($names[count($names)-1]) ? substr($names[count($names)-1], 0, 1) : ''));
            @endphp
            <div style="width: 64px; height: 64px; border-radius: var(--radius-sm); background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700; font-family: 'Outfit', sans-serif;">
                {{ $initials }}
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.35rem;">
                    <div style="font-weight: 600; color: var(--primary); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em;">Clinical Case Profile</div>
                    <button id="generateAISummary" class="btn-sm" style="padding: 0.25rem 0.65rem; border-radius: 4px; background: var(--surface-solid); border: 1.5px solid var(--primary); color: var(--primary); font-weight: 700; font-size: 0.65rem; cursor: pointer; display: flex; align-items: center; gap: 0.4rem; transition: var(--transition-fast);">
                        ✨ Generate AI Summary
                    </button>
                </div>
                <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.5rem;">{{ $student->full_name ?? 'N/A' }}</h1>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="padding: 0.35rem 0.85rem; border-radius: 4px; background: var(--surface-2); border: 1px solid var(--border); font-weight: 600; font-size: 0.72rem; color: var(--text-muted);">
                        ID: {{ $student->roll_number ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem;">
            <a href="{{ route('counselor.students.export', $student->user_id) }}" target="_blank" class="btn-sm" style="background: var(--surface-solid); border: 1.5px solid var(--border); color: var(--text); padding: 0.5rem 1rem; text-decoration: none; border-radius: 6px; font-weight: 700; font-size: 0.8rem; display: flex; align-items: center; gap: 0.5rem;">
                📄 Export Clinical File
            </a>
            <div>
                <div style="font-size: 0.65rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.25rem;">Last Session</div>
                <div style="font-weight: 700; color: var(--text); font-size: 0.95rem;">{{ count($assessments) > 0 ? (is_array($assessments) ? end($assessments)['assessment_date'] : $assessments->last()->assessment_date)->format('M d, Y') : 'No record' }}</div>
            </div>
        </div>
    </div>

    <!-- AI Summary Result Container -->
    <div id="aiSummaryBox" style="display: none; margin-bottom: 2.5rem; background: var(--surface-solid); border: 1.5px solid var(--border); border-radius: var(--radius); padding: 2rem; position: relative;">
        <button onclick="document.getElementById('aiSummaryBox').style.display='none'" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.25rem; color: var(--text-dim); cursor: pointer;">×</button>
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
            <span style="font-size: 1.5rem;">✨</span>
            <h3 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: var(--primary); margin: 0;">AI Clinical Insight</h3>
            <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-dim); background: var(--surface-2); padding: 0.2rem 0.6rem; border-radius: 4px;">Gemini Powered</span>
        </div>
        <div id="aiSummaryContent" style="color: var(--text); line-height: 1.7; font-size: 0.95rem; white-space: pre-wrap;">
            Loading clinical assessment...
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 4rem; align-items: start; margin-bottom: 6rem;">
        
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <!-- Diagnostic Mirror -->
            @if(count($assessments) > 0)
            <div style="background: var(--surface-solid); border-radius: var(--radius); padding: 2rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text);">Longitudinal Trajectory</h2>
                <div style="height: 300px; margin-bottom: 2rem;">
                    <canvas id="trendChart"></canvas>
                </div>

                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700; margin-bottom: 1rem; color: var(--text);">Clinical History</h3>
                <div class="table-wrapper">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <th style="padding: 0.75rem 0; text-align: left; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Assessment Date</th>
                                <th style="padding: 0.75rem 0; text-align: center; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Diagnostic Score</th>
                                <th style="padding: 0.75rem 0; text-align: right; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Priority</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assessments->reverse() as $r)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 0.75rem 0; font-weight: 600; color: var(--text); font-size: 0.88rem;">{{ $r?->assessment_date instanceof \DateTimeInterface ? $r?->assessment_date?->format('M d, Y') : ($r?->assessment_date ?? 'N/A') }}</td>
                                <td style="padding: 0.75rem 0; text-align: center;">
                                    <div style="font-weight: 700; color: var(--primary); font-size: 0.95rem;">{{ $r?->overall_score ?? 0 }}<span style="font-size: 0.75rem; opacity: 0.4;">/100</span></div>
                                </td>
                                <td style="padding: 0.75rem 0; text-align: right;">
                                    @php
                                        $riskClass = strtolower($r?->risk_level ?? 'low');
                                        $riskColor = $riskClass === 'critical' ? '#dc2626' : ($riskClass === 'high' ? '#ea580c' : ($riskClass === 'moderate' ? '#d97706' : '#16a34a'));
                                    @endphp
                                    <span style="padding: 0.25rem 0.75rem; border-radius: 4px; font-weight: 700; font-size: 0.65rem; background: {{ $riskColor }}15; color: {{ $riskColor }};">
                                        {{ strtoupper($r?->risk_level ?? 'LOW') }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
                <div style="padding: 4rem 2rem; text-align: center; background: var(--surface-2); border-radius: var(--radius); border: 2px dashed var(--border);">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">📊</div>
                    <h3 style="color: var(--text-muted); font-weight: 600; font-size: 1rem;">No clinical data available.</h3>
                </div>
            @endif
        </div>

        <!-- Add note form -->
        <div style="background: var(--surface-solid); border: 1px solid var(--border); padding: 2rem; border-radius: var(--radius);">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text);">Add Clinical Note</h2>
            <form method="POST" action="{{ route('counselor.students.notes.store', $student->user_id ?? 0) }}">
                @csrf
                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-weight: 600; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.04em;">Clinical Observations *</label>
                    <textarea name="note_text" rows="4" required style="width: 100%; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 500; font-family: inherit; font-size: 0.9rem; background: var(--surface-2); color: var(--text);" placeholder="Enter clinical observations…"></textarea>
                </div>
                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-weight: 600; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.04em;">Intervention Plan</label>
                    <textarea name="recommendation" rows="2" style="width: 100%; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 500; font-family: inherit; font-size: 0.9rem; background: var(--surface-2); color: var(--text);" placeholder="Recommended next steps…"></textarea>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.04em;">Next Contact Date</label>
                    <input type="date" name="follow_up_date" style="width: 100%; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 500; font-family: inherit; font-size: 0.9rem; background: var(--surface-2); color: var(--text);">
                </div>
                <button type="submit" style="width: 100%; padding: 0.75rem; border-radius: var(--radius-sm); background: var(--primary); color: white; border: none; font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); cursor: pointer;">💾 Archive Session Note</button>
            </form>
        </div>
    </div>

    <!-- Previous notes list -->
    @if(count($notes) > 0)
    <div style="background: var(--surface-solid); border: 1px solid var(--border); padding: 2rem; border-radius: var(--radius); margin-bottom: 2rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: var(--text); margin: 0;">Previous Notes ({{ count($notes) }})</h2>
        </div>
        <div id="notesList">
            @foreach($notes as $note)
            <div class="note-item">
                <p class="note-date">📅 {{ $note?->created_at instanceof \DateTimeInterface ? $note?->created_at?->format('M d, Y H:i A') : ($note?->created_at ?? 'N/A') }}</p>
                <p style="color: var(--text); line-height: 1.6;"><strong>Note:</strong> {!! nl2br(e($note?->note_text ?? '')) !!}</p>
                @if($note?->recommendation)
                    <p style="color: var(--text); margin-top: 0.5rem;"><strong>Recommendation:</strong> {{ $note?->recommendation }}</p>
                @endif
                @if($note?->follow_up_date)
                    <p style="color: var(--text); margin-top: 0.5rem;"><strong>Follow-up:</strong> {{ $note?->follow_up_date instanceof \DateTimeInterface ? $note?->follow_up_date?->format('M d, Y') : $note?->follow_up_date }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <a href="{{ route('counselor.students.index') }}" style="display: inline-block; padding: 0.75rem 1.5rem; border-radius: var(--radius-sm); background: var(--surface-2); border: 1px solid var(--border); color: var(--text); font-weight: 600; text-decoration: none; font-size: 0.9rem;">← Back to Student List</a>
</div>

<footer class="footer">
    <p>© {{ date('Y') }} PSU Mental Health Portal</p>
</footer>
@endsection

@push('scripts')
<script>
@if(count($assessments) > 0)
(function() {
    new Chart(document.getElementById('trendChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: {!! json_encode($chart_labels) !!},
            datasets: [{
                label: 'Overall Wellness Score',
                data: {!! json_encode($chart_scores) !!},
                borderColor: '#0d9488',
                backgroundColor: 'rgba(13,148,136,0.06)',
                tension: 0.35, fill: true,
                pointBackgroundColor: '#0d9488',
                pointRadius: 4, pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0, max: 100, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });
})();
@endif

// AI Summary Logic
document.getElementById('generateAISummary')?.addEventListener('click', async function() {
    const btn = this;
    const box = document.getElementById('aiSummaryBox');
    const content = document.getElementById('aiSummaryContent');

    btn.disabled = true;
    btn.innerHTML = '✨ Processing...';
    box.style.display = 'block';
    content.innerHTML = 'Loading clinical assessment...';

    try {
        const response = await fetch('{{ route("counselor.students.ai_summary", $student->user_id ?? 0) }}', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await response.json();
        
        if (data.success) {
            content.innerHTML = data.summary;
        } else {
            content.innerHTML = '<span style="color: #dc2626;">Error generating summary: ' + (data.error || 'Unknown error') + '</span>';
        }
    } catch (err) {
        content.innerHTML = '<span style="color: #dc2626;">Connection failed. Please try again.</span>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '✨ Generate AI Summary';
    }
});
</script>
@endpush
