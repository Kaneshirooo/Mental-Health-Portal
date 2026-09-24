@extends('layouts.app')

@push('styles')
<style>
    .history-page { position: relative; }

    .orb-bg {
        position: fixed; border-radius: 50%; pointer-events: none; z-index: 0;
        filter: blur(130px); animation: orb-drift 14s ease-in-out infinite alternate;
    }
    .orb-1 { width:700px; height:700px; background:rgba(16,185,129,0.07); top:-200px; right:-160px; }
    .orb-2 { width:500px; height:500px; background:rgba(5,150,105,0.05); bottom:-120px; left:-100px; animation-delay:-6s; }
    @keyframes orb-drift { from{transform:translate(0,0) scale(1)} to{transform:translate(50px,35px) scale(1.06)} }

    .history-content { position: relative; z-index: 1; }

    /* Session Metadata Card */
    .session-meta-card {
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: 28px;
        padding: 2rem 2.25rem;
        box-shadow: var(--shadow-md);
        position: relative;
        overflow: hidden;
        margin-bottom: 1.75rem;
    }
    .session-meta-card::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, #10b981 0%, #6366f1 50%, #f43f5e 100%);
    }

    .meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }
    .meta-item { display: flex; flex-direction: column; gap: 0.25rem; }
    .meta-label {
        font-size: 0.62rem; font-weight: 900; text-transform: uppercase;
        letter-spacing: 0.1em; color: var(--text-dim);
    }
    .meta-value { font-size: 0.92rem; font-weight: 800; color: var(--text); }
    .meta-value-sub { font-size: 0.75rem; font-weight: 600; color: var(--text-dim); margin-top: 0.1rem; }

    /* Filter Tabs */
    .filter-tabs {
        display: flex; gap: 0.5rem; background: var(--surface-2);
        padding: 0.3rem; border-radius: 14px; border: 1px solid var(--border);
        width: fit-content;
    }
    .filter-tab {
        padding: 0.45rem 1.1rem; border-radius: 10px; border: none;
        font-weight: 800; font-size: 0.72rem; text-transform: uppercase;
        letter-spacing: 0.04em; cursor: pointer; transition: all 0.2s ease;
        color: var(--text-dim); background: transparent;
    }
    .filter-tab.active {
        background: var(--surface-solid); color: var(--text);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .filter-tab.tab-primary.active { background: var(--primary); color: white; }
    .filter-tab.tab-chat.active    { background: #10b981; color: white; }
    .filter-tab.tab-speech.active  { background: #6366f1; color: white; }

    /* Message Cards */
    .msg-card {
        border-radius: 18px; padding: 1rem 1.3rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1.5px solid var(--border);
    }
    .msg-card:hover { transform: translateX(4px); box-shadow: var(--shadow-sm); }
    .msg-card.msg-student { background: var(--surface-2); }
    .msg-card.msg-counselor { background: rgba(16,185,129,0.06); border-color: rgba(16,185,129,0.2); }
    .msg-card.msg-speech { background: rgba(99,102,241,0.06); border-color: rgba(99,102,241,0.2); border-style: dashed; }
    .msg-card.msg-ai { background: rgba(16,185,129,0.07); border-color: rgba(16,185,129,0.25); }

    .role-badge {
        padding: 2px 8px; border-radius: 6px; font-size: 0.6rem;
        font-weight: 900; text-transform: uppercase; letter-spacing: 0.04em;
    }
    .badge-student  { background: #dcfce7; color: #166534; }
    .badge-counselor { background: #dbeafe; color: #1e40af; }
    .badge-admin    { background: #fef9c3; color: #854d0e; }
    .badge-speech   { background: rgba(99,102,241,0.15); color: #4f46e5; border: 1px solid rgba(99,102,241,0.3); }
    .badge-ai       { background: rgba(16,185,129,0.15); color: #059669; border: 1px solid rgba(16,185,129,0.3); }

    .role-prefix-student   { font-weight: 800; color: #166534; opacity: 0.95; margin-right: 4px; }
    .role-prefix-counselor { font-weight: 800; color: #1e40af; opacity: 0.95; margin-right: 4px; }
    .msg-body-text         { color: var(--text); white-space: pre-wrap; line-height: 1.6; font-size: 0.93rem; font-weight: 500; }
    .msg-body-italic       { font-style: italic; }

    /* Read Aloud btn */
    .btn-read-aloud {
        display: inline-flex; align-items: center; gap: 0.5rem;
        background: var(--primary); color: white; border: none;
        padding: 0.65rem 1.25rem; border-radius: 14px; font-size: 0.78rem;
        font-weight: 800; text-transform: uppercase; cursor: pointer;
        box-shadow: 0 4px 12px var(--primary-glow); transition: all 0.2s ease;
    }
    .btn-read-aloud:hover { transform: translateY(-2px); box-shadow: 0 8px 20px var(--primary-glow); }
    .btn-read-aloud.reading { background: #ef4444; box-shadow: 0 4px 12px rgba(239,68,68,0.3); }

    .empty-state {
        padding: 4rem 2rem; text-align: center;
        border: 2px dashed var(--border); border-radius: 24px; color: var(--text-dim);
    }

    .count-badge {
        font-size: 0.6rem; font-weight: 900; padding: 2px 6px;
        border-radius: 6px; margin-left: 4px;
        background: rgba(255,255,255,0.2);
    }
    .filter-tab.active .count-badge { background: rgba(255,255,255,0.25); }
</style>
@endpush

@section('content')
<div class="orb-bg orb-1"></div>
<div class="orb-bg orb-2"></div>

<div class="container history-content" style="max-width: 1020px; margin: 0 auto; padding: 2rem 1.5rem 5rem;">

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <header style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.4rem;">
                <span style="background: rgba(16,185,129,0.12); color: #10b981; border: 1px solid rgba(16,185,129,0.3); padding: 0.25rem 0.75rem; border-radius: 999px; font-weight: 900; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.06em;">
                    Clinical Session Record
                </span>
                <span style="font-size: 0.72rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.06em;">
                    @php
                        $statusColors = [
                            'ended'   => 'color:#64748b',
                            'active'  => 'color:#10b981',
                            'pending' => 'color:#f59e0b',
                        ];
                    @endphp
                    <span style="{{ $statusColors[$call->status] ?? 'color:var(--text-dim)' }}">
                        ● {{ strtoupper($call->status) }}
                    </span>
                </span>
            </div>
            <h1 style="font-family:'Outfit',sans-serif; font-size:2.2rem; font-weight:900; margin:0; color:var(--text); letter-spacing:-0.04em; line-height:1.05;">
                Emergency Session <span style="color:var(--primary)">#{{ $call->call_id }}</span>
            </h1>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <button id="copyTranscriptBtn" onclick="copyDialogueTranscript()" class="btn-read-aloud" style="background: var(--surface-2); color: var(--text); border: 1px solid var(--border); box-shadow: none;">
                <i class="ph-bold ph-copy" style="font-size:1rem;"></i>
                <span>Copy Dialogue</span>
            </button>
            <button id="readAloudBtn" onclick="toggleReadAloud()" class="btn-read-aloud">
                <i class="ph-bold ph-speaker-high" style="font-size:1rem;"></i>
                <span>Read Aloud</span>
            </button>
            <a href="{{ route('counselor.emergency.calls.logs') }}"
               style="text-decoration:none; background:var(--surface-2); border:1px solid var(--border); color:var(--text); padding:0.65rem 1rem; border-radius:12px; font-size:0.78rem; font-weight:800; text-transform:uppercase; display:flex; align-items:center; gap:0.5rem;">
                <i class="ph-bold ph-arrow-left"></i> Call Logs
            </a>
            <a href="{{ route('counselor.students.show', $call->student_id) }}"
               style="text-decoration:none; background:var(--surface-2); border:1px solid var(--border); color:var(--text); padding:0.65rem 1rem; border-radius:12px; font-size:0.78rem; font-weight:800; text-transform:uppercase; display:flex; align-items:center; gap:0.5rem;">
                <i class="ph-bold ph-user"></i> Profile
            </a>
        </div>
    </header>

    {{-- ── Session Metadata Card ───────────────────────────────────────── --}}
    <div class="session-meta-card">
        <div style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-dim);">
            <i class="ph-bold ph-info" style="font-size: 1rem;"></i> Session Metadata
        </div>
        <div class="meta-grid">
            <div class="meta-item">
                <span class="meta-label">Student</span>
                <span class="meta-value">{{ $call->student?->full_name ?? '—' }}</span>
                @if($call->student?->roll_number)
                    <span class="meta-value-sub">{{ $call->student->roll_number }}</span>
                @endif
            </div>
            <div class="meta-item">
                <span class="meta-label">Counselor</span>
                <span class="meta-value">{{ $call->counselor?->full_name ?? 'Not Assigned' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Call Date</span>
                <span class="meta-value">{{ $call->created_at?->format('M d, Y') ?? '—' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Start Time</span>
                <span class="meta-value">{{ $call->started_at?->format('h:i A') ?? '—' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">End Time</span>
                <span class="meta-value">{{ $call->ended_at?->format('h:i A') ?? '—' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Duration</span>
                <span class="meta-value">{{ $duration ?? '—' }}</span>
            </div>
        </div>
    </div>

    {{-- ── AI Post-Session Analysis ────────────────────────────────────── --}}
    @if($postSessionAssessment)
    <div style="margin-bottom: 1.75rem; background: rgba(16,185,129,0.07); border: 1.5px solid rgba(16,185,129,0.25); border-radius: 24px; padding: 1.5rem 1.75rem; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.7rem; color: #059669; font-weight: 900; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.85rem;">
            <i class="ph-bold ph-brain" style="font-size: 1.15rem;"></i> AI Post-Session Clinical Analysis
        </div>
        <div style="white-space: pre-wrap; font-family: Inter, sans-serif; color: var(--text); line-height: 1.7; font-size: 0.93rem; font-weight: 500; background: rgba(255,255,255,0.5); padding: 1rem 1.25rem; border-radius: 14px; border: 1px solid rgba(16,185,129,0.15);">{{ $postSessionAssessment->message_text }}</div>
    </div>
    @endif

    {{-- ── Filter & Stats Bar ──────────────────────────────────────────── --}}
    @php
        $chatCount   = $messages->filter(fn($m) => !str_contains($m->message_text, 'webrtc_signal') && !str_starts_with($m->message_text, '[Speech') && !str_starts_with($m->message_text, '[AI Post-Session'))->count();
        $speechCount = $messages->filter(fn($m) => str_starts_with($m->message_text, '[Speech'))->count();
        $totalCount  = $chatCount + $speechCount;
    @endphp
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <h3 style="font-family:'Outfit',sans-serif; font-size:1.2rem; font-weight:900; margin:0; color:var(--text);">
                Conversation & Transcript
            </h3>
            <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-dim);">
                {{ $totalCount }} message{{ $totalCount !== 1 ? 's' : '' }}
            </span>
        </div>
        <div class="filter-tabs">
            <button onclick="filterTranscript('all')" class="filter-tab tab-primary active" id="tabAll">
                All <span class="count-badge">{{ $totalCount }}</span>
            </button>
            <button onclick="filterTranscript('chat')" class="filter-tab tab-chat" id="tabChat">
                💬 Chat <span class="count-badge">{{ $chatCount }}</span>
            </button>
            <button onclick="filterTranscript('speech')" class="filter-tab tab-speech" id="tabSpeech">
                🎤 Speech <span class="count-badge">{{ $speechCount }}</span>
            </button>
        </div>
    </div>

    {{-- ── Messages List ───────────────────────────────────────────────── --}}
    <div id="transcriptList" style="display: flex; flex-direction: column; gap: 0.9rem;">
        @forelse($messages as $message)
            @php
                $isSignal = str_contains($message->message_text, 'webrtc_signal');
                if ($isSignal) continue;

                $isAI     = str_starts_with($message->message_text, '[AI Post-Session Assessment]');
                $isSpeech = str_starts_with($message->message_text, '[Speech');

                $isStudent  = ((int)$message->sender_id === (int)$call->student_id);
                if ($isSpeech && preg_match('/^\[Speech:(\w+)\]/i', $message->message_text, $mTag)) {
                    $speakerTag = strtolower($mTag[1]);
                    if ($speakerTag === 'student') {
                        $isStudent = true;
                    } elseif ($speakerTag === 'counselor') {
                        $isStudent = false;
                    }
                }
                $senderName = $message->sender?->full_name ?? ($isStudent ? ($call->student?->full_name ?? 'Student') : ($call->counselor?->full_name ?? 'Counselor'));

                // Clean text: strip prefixes
                if ($isSpeech) {
                    $cleanText = trim(preg_replace('/^\[Speech:?\w*\]\s*/', '', $message->message_text));
                    $speechLabel = 'Voice Transcript';
                    if (preg_match('/^\[Speech:(\w+)\]/', $message->message_text, $m)) {
                        $speechLabel = ucfirst($m[1]) . ' Voice Transcript';
                    }
                } else {
                    $cleanText = $message->message_text;
                }

                $cardClass   = $isAI ? 'msg-ai' : ($isSpeech ? 'msg-speech' : ($isStudent ? 'msg-student' : 'msg-counselor'));
                $dataType    = $isAI ? 'ai' : ($isSpeech ? 'speech' : 'chat');
                $timestamp   = $message->created_at?->format('h:i A') ?? '—';
                $dateLabel   = $message->created_at?->format('M d') ?? '';
                $roleLabel   = $isStudent ? 'Student' : 'Counselor';
                $prefixClass = $isStudent ? 'role-prefix-student' : 'role-prefix-counselor';
                $bodyClass   = 'msg-body-text' . ($isSpeech ? ' msg-body-italic' : '');
            @endphp

            <div class="msg-card {{ $cardClass }}"
                 data-type="{{ $dataType }}"
                 data-text="{{ e($cleanText) }}"
                 data-sender="{{ e($senderName) }}"
                 data-role="{{ $roleLabel }}">

                {{-- Card Header --}}
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.55rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        @if($isAI)
                            <span class="role-badge badge-ai">
                                <i class="ph-bold ph-brain"></i> AI Analysis
                            </span>
                        @elseif($isSpeech)
                            <span class="role-badge {{ $isStudent ? 'badge-student' : 'badge-counselor' }}">
                                {{ $isStudent ? 'Student' : 'Counselor' }}
                            </span>
                            <span class="role-badge badge-speech">
                                <i class="ph-bold ph-microphone-stage"></i> {{ $speechLabel }}
                            </span>
                        @else
                            <span class="role-badge {{ $isStudent ? 'badge-student' : 'badge-counselor' }}">
                                {{ $isStudent ? 'Student' : 'Counselor' }}
                            </span>
                        @endif

                        @if(!$isAI)
                        <span style="font-weight: 800; font-size: 0.85rem; color: var(--text);">
                            {{ $senderName }}
                        </span>
                        @endif
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        @if($dateLabel)
                            <span style="font-size: 0.65rem; font-weight: 700; color: var(--text-dim); opacity: 0.7;">{{ $dateLabel }}</span>
                        @endif
                        <span style="font-size: 0.72rem; font-weight: 800; color: var(--text-dim); background: var(--surface-2); padding: 2px 8px; border-radius: 6px;">
                            {{ $timestamp }}
                        </span>
                    </div>
                </div>

                {{-- Message Body --}}
                <div class="{{ $bodyClass }}">
                    @if(!$isAI)
                        <span class="{{ $prefixClass }}">{{ $roleLabel }}:</span>
                    @endif
                    {{ $cleanText }}
                </div>
            </div>

        @empty
            <div class="empty-state">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💬</div>
                <h3 style="font-weight: 800; font-size: 1.1rem; margin: 0 0 0.4rem; color: var(--text-dim);">No Transcript Available</h3>
                <p style="font-weight: 500; color: var(--text-muted); margin: 0; font-size: 0.9rem;">No conversation messages or speech transcripts were recorded for this session.</p>
            </div>
        @endforelse

        {{-- Hidden "no results" state for filter --}}
        <div id="noFilterResults" style="display: none;" class="empty-state">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
            <p style="font-weight: 600; color: var(--text-dim); margin: 0;">No messages match this filter.</p>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let isReading = false;
    let synth = window.speechSynthesis;
    let currentUtterance = null;
    let activeFilter = 'all';

    function filterTranscript(type) {
        activeFilter = type;
        const cards = document.querySelectorAll('.msg-card');
        const noResults = document.getElementById('noFilterResults');

        // Update tab styles
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        document.getElementById('tab' + type.charAt(0).toUpperCase() + type.slice(1)).classList.add('active');

        let visible = 0;
        cards.forEach(c => {
            const dataType = c.dataset.type;
            // 'all'    → show everything except nothing
            // 'chat'   → only chat (not speech, not ai)
            // 'speech' → only speech (not chat, not ai)
            let shouldShow;
            if (type === 'all') {
                shouldShow = true;
            } else if (type === 'speech') {
                shouldShow = dataType === 'speech';
            } else if (type === 'chat') {
                shouldShow = dataType === 'chat';
            } else {
                shouldShow = dataType === type;
            }
            c.style.display = shouldShow ? 'block' : 'none';
            if (shouldShow) visible++;
        });

        noResults.style.display = (visible === 0 && document.querySelectorAll('.msg-card').length > 0) ? 'block' : 'none';
    }

    function toggleReadAloud() {
        if (!synth) {
            alert('Text-to-speech is not supported in this browser.');
            return;
        }

        const btn = document.getElementById('readAloudBtn');

        if (isReading) {
            synth.cancel();
            isReading = false;
            btn.innerHTML = '<i class="ph-bold ph-speaker-high" style="font-size:1rem;"></i> <span>Read Aloud</span>';
            btn.classList.remove('reading');
            return;
        }

        const cards = Array.from(document.querySelectorAll('.msg-card')).filter(c => c.style.display !== 'none');
        if (!cards.length) {
            alert('No transcript lines available to read.');
            return;
        }

        const fullText = cards.map(c => {
            const sender = c.dataset.sender || 'Speaker';
            const text   = c.dataset.text || '';
            return `${sender} said: ${text}`;
        }).join('. ');

        currentUtterance = new SpeechSynthesisUtterance(fullText);
        currentUtterance.rate  = 1.0;
        currentUtterance.pitch = 1.0;
        currentUtterance.lang  = 'en-US';

        currentUtterance.onend = () => {
            isReading = false;
            btn.innerHTML = '<i class="ph-bold ph-speaker-high" style="font-size:1rem;"></i> <span>Read Aloud</span>';
            btn.classList.remove('reading');
        };
        currentUtterance.onerror = () => {
            isReading = false;
            btn.innerHTML = '<i class="ph-bold ph-speaker-high" style="font-size:1rem;"></i> <span>Read Aloud</span>';
            btn.classList.remove('reading');
        };

        synth.speak(currentUtterance);
        isReading = true;
        btn.innerHTML = '<i class="ph-bold ph-stop-circle" style="font-size:1rem;"></i> <span>Stop</span>';
        btn.classList.add('reading');
    }

    function copyDialogueTranscript() {
        const cards = Array.from(document.querySelectorAll('.msg-card')).filter(c => c.style.display !== 'none' && c.dataset.type !== 'ai');
        if (!cards.length) {
            alert('No transcript lines available to copy.');
            return;
        }

        const transcriptText = cards.map(c => {
            const role = c.dataset.role || 'Participant';
            const text = c.dataset.text || '';
            return `${role}: ${text}`;
        }).join('\n');

        navigator.clipboard.writeText(transcriptText).then(() => {
            const btn = document.getElementById('copyTranscriptBtn');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="ph-bold ph-check" style="font-size:1rem; color:#10b981;"></i> <span style="color:#10b981;">Copied!</span>';
            setTimeout(() => {
                btn.innerHTML = originalHTML;
            }, 2500);
        }).catch(err => {
            console.error('Failed to copy transcript: ', err);
            alert('Failed to copy dialogue transcript to clipboard.');
        });
    }

    // Animate cards on load
    document.addEventListener('DOMContentLoaded', () => {
        if (window.gsap) {
            gsap.from('.session-meta-card', { y: 30, opacity: 0, duration: 0.9, ease: 'expo.out' });
            gsap.from('.msg-card', { y: 20, opacity: 0, duration: 0.6, stagger: 0.04, ease: 'expo.out', delay: 0.2, clearProps: 'all' });
        }
    });
</script>
@endpush
