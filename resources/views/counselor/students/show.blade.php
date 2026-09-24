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
@section('content')
<div class="container" style="max-width: 1300px; margin: 0 auto; padding: 2rem 1.5rem 4rem;">
    
    <!-- Profile Header -->
    <header class="staggered" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3.5rem;">
        <div style="display: flex; align-items: center; gap: 2rem;">
            @php
                $initial = strtoupper(substr($student?->full_name ?? 'S', 0, 1));
            @endphp
            <div style="width: 80px; height: 80px; border-radius: 24px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 900; font-family: 'Outfit', sans-serif; box-shadow: 0 15px 35px var(--primary-glow);">
                {{ $initial }}
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                    <div style="font-weight: 800; color: var(--primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em;">Clinical Profile</div>
                    <button id="generateAISummary" class="btn-sm" style="padding: 0.4rem 0.85rem; border-radius: 100px; background: var(--primary-glow); border: 1px solid var(--primary-light); color: var(--primary); font-weight: 800; font-size: 0.65rem; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; text-transform: uppercase;">
                        <i class="ph ph-sparkle"></i> AI Insight
                    </button>
                </div>
                <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.75rem; font-weight: 900; color: var(--text); letter-spacing: -0.04em; margin: 0;">{{ $student->full_name ?? 'N/A' }}</h1>
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem 1.25rem; margin-top: 0.75rem;">
                    <span style="font-weight: 800; color: var(--text-muted); font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem;">
                        <i class="ph-bold ph-identification-badge" style="color: var(--primary);"></i> {{ $student->roll_number ?? 'N/A' }}
                    </span>
                    <span style="font-weight: 800; color: var(--text-muted); font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem;">
                        <i class="ph-bold ph-buildings" style="color: var(--primary);"></i> {{ $student->department ?? 'General' }}
                    </span>
                    <span style="font-weight: 800; color: var(--text-muted); font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem;">
                        <i class="ph-bold ph-student" style="color: var(--primary);"></i> {{ $student->course ?? 'Not Set' }}
                    </span>
                    <span style="font-weight: 800; color: var(--text-muted); font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem;">
                        <i class="ph-bold ph-calendar-blank" style="color: var(--primary);"></i> {{ $student->semester ?? 'Not Set' }}
                    </span>
                </div>
            </div>
        </div>
        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 1rem;">
            <a href="{{ route('counselor.students.export', $student->user_id) }}" target="_blank" class="btn-secondary" style="padding: 1rem 1.5rem; border-radius: 16px; font-weight: 800; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; gap: 0.75rem; background: var(--surface-solid); border: 1px solid var(--border); text-transform: uppercase;">
                <i class="ph ph-file-pdf" style="font-size: 1.1rem;"></i> Export Clinical File
            </a>
            <div style="text-align: right;">
                <div style="font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.25rem;">Last Evaluation</div>
                <div style="font-weight: 900; color: var(--text); font-size: 1.1rem;">{{ count($assessments) > 0 ? (is_array($assessments) ? end($assessments)['assessment_date'] : $assessments->last()->assessment_date)->format('M d, Y') : 'No record' }}</div>
            </div>
        </div>
    </header>

    <!-- AI Summary -->
    <div id="aiSummaryBox" style="display: none; margin-bottom: 3.5rem; background: var(--surface-solid); border: 1px solid var(--border); border-radius: 32px; padding: 2.5rem; box-shadow: 0 20px 40px rgba(0,0,0,0.03); position: relative;" class="staggered">
        <button onclick="document.getElementById('aiSummaryBox').style.display='none'" style="position: absolute; top: 1.5rem; right: 1.5rem; background: var(--surface-2); border: 1px solid var(--border); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-dim); cursor: pointer; transition: all 0.2s;">
            <i class="ph ph-x" style="font-size: 1rem;"></i>
        </button>
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: var(--primary-glow); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                <i class="ph ph-brain"></i>
            </div>
            <div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 800; color: var(--text); margin: 0;">AI Clinical Synthesis</h3>
                <span style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted);">Neural Assessment Profile</span>
            </div>
        </div>
        <div id="aiSummaryContent" style="color: var(--text); line-height: 1.8; font-size: 1.05rem; font-weight: 500; white-space: pre-wrap; padding: 1.5rem; background: var(--surface-2); border-radius: 20px; border: 1px solid var(--border);">
            Synthesizing longitudinal data...
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.6fr 1fr; gap: 3.5rem; margin-bottom: 5rem;" class="staggered">
        <!-- Visualization & History -->
        <div style="display: flex; flex-direction: column; gap: 3.5rem;">
            @if(count($assessments) > 0)
            <div style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 32px; padding: 2.5rem; box-shadow: 0 20px 40px rgba(0,0,0,0.03);">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="ph ph-trend-up" style="color: var(--primary);"></i> Wellness Trajectory
                </h2>
                <p style="font-size: 0.9rem; color: var(--text-dim); margin-bottom: 2rem; font-weight: 500; line-height: 1.6;">
                    This visualization tracks the student's longitudinal wellness data derived from self-assessment scores. It helps identify clinical patterns, recovery progress, or potential risks that may require immediate intervention.
                </p>
                <div style="height: 350px; margin-bottom: 3.5rem;">
                    <canvas id="trendChart"></canvas>
                </div>

                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 800; color: var(--text); margin-bottom: 2rem;">Clinical Evaluation History</h3>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: separate; border-spacing: 0 0.5rem;">
                        <thead>
                            <tr style="text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.65rem; color: var(--text-muted); font-weight: 800;">
                                <th style="padding: 1rem 0; text-align: left;">Date</th>
                                <th style="padding: 1rem 0; text-align: center;">Diagnostic Score</th>
                                <th style="padding: 1rem 0; text-align: right;">Risk Classification</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assessments->reverse() as $r)
                            <tr class="staggered-row" style="background: var(--surface-2); transition: all 0.3s ease;">
                                <td style="padding: 1.25rem 1rem; border-top-left-radius: 12px; border-bottom-left-radius: 12px; font-weight: 800; color: var(--text); font-size: 0.95rem;">
                                    {{ $r?->assessment_date instanceof \DateTimeInterface ? $r?->assessment_date?->format('M d, Y') : ($r?->assessment_date ?? 'N/A') }}
                                </td>
                                <td style="padding: 1.25rem 1rem; text-align: center;">
                                    <div style="display: inline-flex; align-items: center; gap: 0.75rem; font-weight: 900; color: var(--primary); font-size: 1.1rem;">
                                        {{ $r?->overall_score ?? 0 }}<span style="font-size: 0.75rem; opacity: 0.4; font-weight: 700;">/100</span>
                                    </div>
                                </td>
                                <td style="padding: 1.25rem 1rem; text-align: right; border-top-right-radius: 12px; border-bottom-right-radius: 12px;">
                                    @php
                                        $riskClass = strtolower($r?->risk_level ?? 'low');
                                        $riskColor = $riskClass === 'critical' ? '#ef4444' : ($riskClass === 'high' ? '#f97316' : ($riskClass === 'moderate' ? '#f59e0b' : '#10b981'));
                                    @endphp
                                    <span style="padding: 0.4rem 0.85rem; border-radius: 100px; font-weight: 800; font-size: 0.65rem; text-transform: uppercase; background: var(--surface-3); color: {{ $riskColor }}; border: 1.5px solid currentColor;">
                                        {{ $r?->risk_level ?? 'LOW' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
                <div style="padding: 6rem 2rem; text-align: center; background: var(--surface-solid); border: 2px dashed var(--border); border-radius: 32px;">
                    <div style="font-size: 4rem; margin-bottom: 2rem; opacity: 0.15;">📊</div>
                    <h3 style="color: var(--text-dim); font-weight: 800; font-size: 1.25rem;">No diagnostic data identified.</h3>
                </div>
            @endif
        </div>

        <!-- Clinical Action Panel -->
        <div style="display: flex; flex-direction: column; gap: 3rem;">
            <div style="background: var(--surface-solid); border: 1px solid var(--border); padding: 2.5rem; border-radius: 32px; box-shadow: 0 20px 40px rgba(0,0,0,0.03);">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text); margin-bottom: 2.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="ph ph-note-pencil" style="color: var(--primary);"></i> Archive Session
                </h2>
                <form id="archiveNoteForm" method="POST" action="{{ route('counselor.students.note', $student->user_id ?? 0) }}" style="display: flex; flex-direction: column; gap: 2rem;">
                    @csrf
                    <div>
                        <label style="display: block; font-weight: 800; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.05em;">Clinical Observations</label>
                        <textarea name="note_text" rows="4" required style="width: 100%; padding: 1.25rem; border-radius: 16px; border: 1.5px solid var(--border); font-weight: 500; font-size: 1rem; background: var(--surface-2); color: var(--text); outline: none; transition: all 0.3s;" placeholder="Document assessment findings…" onfocus="this.style.borderColor='var(--primary)'; this.style.background='var(--surface-solid)';"></textarea>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 800; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.05em;">Intervention Protocol</label>
                        <textarea name="recommendation" rows="2" style="width: 100%; padding: 1.25rem; border-radius: 16px; border: 1.5px solid var(--border); font-weight: 500; font-size: 1rem; background: var(--surface-2); color: var(--text); outline: none; transition: all 0.3s;" placeholder="Define next clinical steps…" onfocus="this.style.borderColor='var(--primary)'; this.style.background='var(--surface-solid)';"></textarea>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 800; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.05em;">Follow-up Schedule</label>
                        <input type="date" name="follow_up_date" style="width: 100%; padding: 1.25rem; border-radius: 16px; border: 1.5px solid var(--border); font-weight: 600; font-size: 1rem; background: var(--surface-2); color: var(--text); outline: none;" onfocus="this.style.borderColor='var(--primary)';">
                    </div>
                    <button type="submit" class="btn-primary" style="padding: 1.25rem; border-radius: 18px; font-weight: 800; font-size: 1rem; text-transform: uppercase; gap: 0.75rem;">
                        <i id="btnIcon" class="ph ph-floppy-disk"></i> Archive Session Data
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Clinical Timeline Archive -->
    <div id="timelineContainer" class="staggered" style="background: var(--surface-solid); border: 2px solid var(--border); padding: 3.5rem; border-radius: 40px; margin-bottom: 4rem; box-shadow: var(--shadow-lg); display: {{ count($notes) > 0 ? 'block' : 'none' }}">
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4rem;">
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 900; color: var(--text); margin-bottom: 0.5rem; letter-spacing: -0.02em;">Clinical History Timeline</h2>
                <p style="color: var(--text-muted); font-weight: 600; font-size: 1rem;">Complete medical-style ledger of observations and interventions.</p>
            </div>
            <div id="noteCount" style="background: var(--primary-glow); border: 1px solid var(--primary-light); padding: 0.75rem 1.5rem; border-radius: 100px; font-weight: 900; font-size: 0.8rem; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em;">
                {{ count($notes) }} Ledger Entries
            </div>
        </header>

        <div style="position: relative; padding-left: 3rem;">
            <!-- Timeline Line -->
            <div style="position: absolute; left: 0.75rem; top: 0; bottom: 0; width: 2px; background: linear-gradient(to bottom, var(--primary), var(--border));"></div>

            <div id="clinicalTimeline" style="display: flex; flex-direction: column; gap: 3rem;">
                @foreach($notes as $note)
                <div class="staggered-row" style="position: relative;">
                    <!-- Timeline Node -->
                    <div style="position: absolute; left: -2.75rem; top: 0.5rem; width: 14px; height: 14px; border-radius: 50%; background: var(--surface-solid); border: 3px solid var(--primary); box-shadow: 0 0 0 6px var(--surface-solid);"></div>

                    <div style="background: var(--surface-2); border: 1.5px solid var(--border); border-radius: 28px; padding: 2.5rem; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); hover: border-color: var(--primary-light); hover: transform: translateX(10px);">
                        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span style="font-size: 0.85rem; font-weight: 900; color: var(--text); text-transform: uppercase; letter-spacing: 0.05em;">
                                    {{ $note?->created_at instanceof \DateTimeInterface ? $note?->created_at?->format('F d, Y') : ($note?->created_at ?? 'N/A') }}
                                </span>
                                @if($note?->recommendation)
                                    <span style="background: #10b98120; color: #10b981; padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.65rem; font-weight: 900; text-transform: uppercase;">Action Taken</span>
                                @else
                                    <span style="background: var(--surface-3); color: var(--text-dim); padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.65rem; font-weight: 900; text-transform: uppercase;">Observation</span>
                                @endif
                            </div>
                            <span style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); opacity: 0.5;">ID: #{{ str_pad($note->note_id, 4, '0', STR_PAD_LEFT) }}</span>
                        </header>

                        <div style="color: var(--text); line-height: 1.8; font-size: 1.1rem; font-weight: 500; margin-bottom: 2rem; color: #374151;">
                            {!! nl2br(e($note?->note_text ?? '')) !!}
                        </div>

                        @if($note?->recommendation || $note?->follow_up_date)
                        <div style="background: white; border: 1.5px solid var(--border); border-radius: 20px; padding: 1.75rem; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                            @if($note?->recommendation)
                                <div>
                                    <h4 style="font-size: 0.7rem; font-weight: 900; color: var(--primary); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem;">Intervention Strategy</h4>
                                    <p style="color: var(--text); font-weight: 600; font-size: 0.95rem; line-height: 1.6;">{{ $note?->recommendation }}</p>
                                </div>
                            @endif
                            @if($note?->follow_up_date)
                                <div>
                                    <h4 style="font-size: 0.7rem; font-weight: 900; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem;">Clinical Follow-up</h4>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text); font-weight: 800; font-size: 1rem;">
                                        <i class="ph-bold ph-calendar-check" style="color: #f59e0b;"></i>
                                        {{ $note?->follow_up_date instanceof \DateTimeInterface ? $note?->follow_up_date?->format('M d, Y') : $note?->follow_up_date }}
                                    </div>
                                </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Aria AI Session Summaries (Counselor View) -->
    <div class="staggered" style="background: var(--surface-solid); border: 2px solid var(--border); padding: 3.5rem; border-radius: 40px; margin-bottom: 4rem; box-shadow: var(--shadow-lg);">
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4rem;">
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 900; color: var(--text); margin-bottom: 0.5rem; letter-spacing: -0.02em;">Aria AI Session Summaries</h2>
                <p style="color: var(--text-muted); font-weight: 600; font-size: 1rem;">Automated clinical insights generated during student-AI chat sessions.</p>
            </div>
            <div style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); padding: 0.75rem 1.5rem; border-radius: 100px; font-weight: 900; font-size: 0.8rem; color: #10b981; text-transform: uppercase; letter-spacing: 0.05em;">
                {{ count($sessions) }} AI Reports
            </div>
        </header>

        @if(count($sessions) > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 2rem;">
                @foreach($sessions as $session)
                    <div style="background: var(--surface-2); border: 1.5px solid var(--border); border-radius: 28px; padding: 2rem; display: flex; flex-direction: column; transition: all 0.3s ease; border-left: 5px solid {{ $session->ai_report['risk_level'] === 'High' || $session->ai_report['risk_level'] === 'Critical' ? '#ef4444' : '#10b981' }};">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                            <div>
                                <h4 style="font-weight: 900; color: var(--text); font-size: 1.1rem; margin-bottom: 0.25rem;">{{ $session->created_at->format('F d, Y') }}</h4>
                                <span style="font-size: 0.65rem; font-weight: 800; color: #10b981; text-transform: uppercase;">Logged at {{ $session->created_at->format('h:i A') }}</span>
                            </div>
                            <span style="padding: 0.35rem 0.75rem; border-radius: 8px; font-size: 0.6rem; font-weight: 900; text-transform: uppercase; {{ 
                                ($session->ai_report['risk_level'] ?? 'Low') === 'High' || ($session->ai_report['risk_level'] ?? 'Low') === 'Critical' 
                                ? 'background: #ef444420; color: #ef4444;' 
                                : 'background: #10b98120; color: #10b981;' 
                            }}">
                                {{ $session->ai_report['risk_level'] ?? 'Low' }} Risk
                            </span>
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <p style="color: var(--text-muted); font-size: 0.9rem; font-style: italic; line-clamp: 3; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                "{!! \Illuminate\Support\Str::limit($session->ai_report['clinical_observations'] ?? 'No observations recorded.', 140) !!}"
                            </p>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem; background: var(--surface-solid); padding: 1rem; border-radius: 16px; border: 1px solid var(--border);">
                            <div style="text-align: center;">
                                <div style="font-size: 0.6rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.25rem;">Mood</div>
                                <div style="font-weight: 900; color: var(--text); font-size: 0.85rem;">{{ ucfirst($session->ai_report['mood'] ?? 'Stable') }}</div>
                            </div>
                            <div style="text-align: center; border-left: 1px solid var(--border);">
                                <div style="font-size: 0.6rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.25rem;">Sleep</div>
                                <div style="font-weight: 900; color: var(--text); font-size: 0.85rem;">{{ $session->ai_report['sleep'] ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <a href="{{ route('counselor.students.session.show', [$student->user_id, $session->pre_id]) }}" style="margin-top: auto; display: block; text-align: center; background: var(--primary); color: white; padding: 0.85rem; border-radius: 12px; font-weight: 800; font-size: 0.75rem; text-decoration: none; text-transform: uppercase; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 20px var(--primary-glow)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                            Inspect AI Summary
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div style="padding: 4rem 2rem; text-align: center; background: var(--surface-2); border: 2px dashed var(--border); border-radius: 32px;">
                <p style="color: var(--text-dim); font-weight: 700; font-style: italic;">No AI session summaries recorded for this student.</p>
            </div>
        @endif
    </div>

    <!-- Emergency Call Conversation History -->
    <div class="staggered" style="background: var(--surface-solid); border: 2px solid var(--border); padding: 3rem; border-radius: 36px; margin-bottom: 4rem; box-shadow: var(--shadow-lg);">
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 900; color: var(--text); margin-bottom: 0.35rem;">Emergency Call Conversations</h2>
                <p style="color: var(--text-muted); font-weight: 600; font-size: 0.95rem;">Review previous counselor-student emergency call transcripts and AI post-session advice.</p>
            </div>
            <div style="background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.25); padding: 0.55rem 1rem; border-radius: 100px; font-weight: 900; font-size: 0.72rem; color: #6366f1; text-transform: uppercase;">
                {{ count($emergencyCalls ?? []) }} Sessions
            </div>
        </header>

        @if(!empty($emergencyCalls) && count($emergencyCalls) > 0)
            <div style="display: grid; gap: 1rem;">
                @foreach($emergencyCalls as $ecall)
                    <div style="display: flex; justify-content: space-between; align-items: center; background: var(--surface-2); border: 1px solid var(--border); border-radius: 16px; padding: 1rem 1.25rem;">
                        <div>
                            <div style="font-weight: 800; color: var(--text);">Session #{{ $ecall->call_id }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-dim); font-weight: 600;">
                                {{ $ecall->created_at?->format('M d, Y h:i A') }} • {{ strtoupper($ecall->status) }}
                            </div>
                        </div>
                        <a href="{{ route('counselor.emergency.calls.history', $ecall->call_id) }}" style="text-decoration: none; background: var(--primary); color: white; padding: 0.6rem 0.9rem; border-radius: 10px; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">
                            View Conversation
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div style="padding: 2rem; text-align: center; border: 1px dashed var(--border); border-radius: 16px; color: var(--text-dim); font-weight: 600;">
                No emergency call conversations are available for this student yet.
            </div>
        @endif
    </div>

    <a href="{{ route('counselor.students.index') }}" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 0.75rem; padding: 1rem 2rem; border-radius: 16px; text-decoration: none; font-weight: 800; font-size: 0.9rem; margin-top: 2rem;">
        <i class="ph ph-arrow-left"></i> Return to Registry
    </a>
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
        const response = await fetch('{{ route("counselor.students.ai-summary", $student->user_id ?? 0) }}', {
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

// Archive Note AJAX
document.getElementById('archiveNoteForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const btn = form.querySelector('button[type="submit"]');
    const btnIcon = document.getElementById('btnIcon');
    const fd = new FormData(form);

    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Archiving...';

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();

        if (data.success) {
            App.toast({ type: 'success', title: 'Archived', message: data.message });
            form.reset();
            
            // Show timeline if it was hidden
            document.getElementById('timelineContainer').style.display = 'block';
            
            // Reload timeline via AJAX
            const listRes = await fetch(window.location.href);
            const listHtml = await listRes.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(listHtml, 'text/html');
            const newList = doc.querySelector('#clinicalTimeline');
            const newCount = doc.querySelector('#noteCount');
            if (newList) document.querySelector('#clinicalTimeline').innerHTML = newList.innerHTML;
            if (newCount) document.querySelector('#noteCount').innerHTML = newCount.innerHTML;
            
        } else {
            App.toast({ type: 'error', title: 'Failed', message: data.error || 'Check fields and try again.' });
        }
    } catch (err) {
        App.toast({ type: 'error', title: 'Error', message: 'Could not reach server.' });
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent || '<i class="ph ph-floppy-disk"></i> Archive Session Data';
    }
});
</script>
@endpush
