@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.2.0/github-markdown.min.css">
<style>
    .chat-layout {
        max-width: 1300px;
        margin: 0 auto;
        padding: 2rem 2.5rem 4rem;
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 2.5rem;
        align-items: start;
        perspective: 1000px;
    }

    @media (max-width: 900px) {
        .chat-layout {
            display: flex !important;
            flex-direction: column !important;
            padding: 0.75rem 0.75rem 3rem !important;
            gap: 1rem !important;
        }
        .chat-main-wrapper {
            order: 1 !important;
            width: 100% !important;
        }
        .aria-panel {
            order: 2 !important;
            position: static !important;
            padding: 1.25rem 1rem !important;
            border-radius: 24px !important;
            width: 100% !important;
        }
        .aria-avatar {
            width: 70px !important;
            height: 70px !important;
            margin-bottom: 0.5rem !important;
            border-radius: 20px !important;
        }
        .chat-interface {
            height: 560px !important;
            border-radius: 24px !important;
        }
        .stream-row {
            max-width: 96% !important;
        }
        .chat-topbar {
            padding: 0.75rem 1rem !important;
        }
        .chat-actions {
            padding: 0.6rem 0.75rem !important;
            flex-direction: column !important;
            gap: 0.5rem !important;
            align-items: stretch !important;
        }
        .chat-actions > div:first-child {
            width: 100% !important;
            display: flex !important;
            gap: 0.5rem !important;
        }
        .chat-actions button {
            flex: 1 !important;
            padding: 0.6rem 0.5rem !important;
            font-size: 0.72rem !important;
            border-radius: 12px !important;
            white-space: nowrap !important;
        }
        .chat-actions > div:last-child {
            justify-content: center !important;
            font-size: 0.78rem !important;
        }
        .chat-input-bar {
            padding: 0.6rem 0.5rem !important;
            gap: 0.35rem !important;
            display: flex !important;
            align-items: center !important;
        }
        .chat-input-bar button {
            width: 42px !important;
            height: 42px !important;
            font-size: 1.1rem !important;
            border-radius: 12px !important;
            flex-shrink: 0 !important;
            padding: 0 !important;
        }
        .msg-input {
            padding: 0.65rem 0.85rem !important;
            font-size: 0.88rem !important;
            border-radius: 14px !important;
            min-height: 42px !important;
            max-height: 100px !important;
            flex: 1 1 auto !important;
            min-width: 0 !important;
        }
        .msg-bubble {
            padding: 0.85rem 1.15rem !important;
            font-size: 0.9rem !important;
            border-radius: 18px !important;
        }
    }

    /* Aria Identity Panel */
    .aria-panel {
        background: var(--surface);
        backdrop-filter: var(--glass-blur);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 2rem 1.75rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        box-shadow: var(--shadow-sm);
        position: sticky;
        top: 80px;
    }

    .aria-avatar {
        width: 120px;
        height: 120px;
        border-radius: 35px;
        overflow: hidden;
        margin-bottom: 1.5rem;
        border: 4px solid white;
        box-shadow: 0 20px 40px rgba(13, 148, 136, 0.2);
        position: relative;
        z-index: 1;
        cursor: pointer;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .aria-avatar:hover { transform: scale(1.05) rotate(2deg); }

    .aria-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Lightbox Styles */
    .aria-lightbox {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.9);
        backdrop-filter: blur(15px);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        cursor: zoom-out;
    }
    .aria-lightbox.open { display: flex; }
    .lightbox-content {
        max-width: 90vw;
        max-height: 90vh;
        border-radius: 40px;
        box-shadow: 0 30px 100px rgba(0,0,0,0.5);
        border: 2px solid rgba(255,255,255,0.1);
        transform: scale(0.9);
        opacity: 0;
        transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .aria-lightbox.open .lightbox-content { transform: scale(1); opacity: 1; }

    .aria-avatar::before {
        content: '';
        position: absolute;
        inset: -10px;
        background: var(--primary-glow);
        border-radius: 40px;
        z-index: -1;
        filter: blur(20px);
        opacity: 0.6;
        animation: aura-pulse 3s infinite ease-in-out;
    }

    @keyframes aura-pulse {
        0%, 100% { transform: scale(1); opacity: 0.4; filter: blur(20px); }
        50%       { transform: scale(1.15); opacity: 0.7; filter: blur(30px); }
    }

    @keyframes pulse-aria {
        0%, 100% { transform: scale(1); box-shadow: 0 8px 20px rgba(13, 148, 136, 0.1); }
        50%       { transform: scale(1.03); box-shadow: 0 12px 28px rgba(13, 148, 136, 0.15); }
    }

    .aria-avatar.speaking {
        animation: aria-speak-pulse 0.4s infinite ease-in-out;
        border-color: #10b981;
        box-shadow: 0 0 30px rgba(16, 185, 129, 0.4);
    }

    @keyframes aria-speak-pulse {
        0%, 100% { transform: scale(1.05); }
        50% { transform: scale(1.1) rotate(2deg); }
    }

    .online-badge {
        padding: 0.35rem 0.85rem;
        background: #ecfdf5;
        color: #059669;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.25rem;
        border: 1px solid rgba(5, 150, 105, 0.1);
    }
    .online-badge span { width: 7px; height: 7px; background: #10b981; border-radius: 50%; box-shadow: 0 0 6px #10b981; }

    .stat-block {
        background: var(--surface-2);
        border-radius: var(--radius-sm);
        padding: 1.25rem;
        width: 100%;
        margin-top: 1.25rem;
        border: 1px solid var(--border);
    }
    .stat-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
    .stat-row:last-child { margin-bottom: 0; }
    .stat-lbl { font-size: 0.68rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.04em; }
    .stat-val { font-weight: 700; color: var(--primary); font-size: 0.82rem; }

    .crisis-box {
        margin-top: 1.5rem;
        width: 100%;
        padding: 1.5rem;
        background: rgba(239, 68, 68, 0.05);
        border-radius: 20px;
        border: 2px solid rgba(239, 68, 68, 0.2);
        text-align: left;
    }
    .dark-mode .crisis-box {
        background: rgba(239, 68, 68, 0.1) !important;
        border-color: rgba(239, 68, 68, 0.3) !important;
    }

    /* Main Chat Interface */
    .chat-interface {
        background: var(--surface-solid);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        height: 780px;
        overflow: hidden;
    }

    .chat-topbar {
        padding: 1.25rem 2rem;
        border-bottom: 1px solid var(--border);
        background: var(--surface);
        backdrop-filter: var(--glass-blur);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .message-stream {
        flex: 1;
        overflow-y: auto;
        padding: 2rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        background: var(--bg);
    }

    .stream-row {
        display: flex;
        gap: 1.25rem;
        max-width: 88%;
        opacity: 1;
    }

    @keyframes fade-up {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .stream-row.user-row { align-self: flex-end; flex-direction: row-reverse; }

    @keyframes dot-pulse {
        0%, 100% { transform: scale(1); opacity: 0.4; }
        50%       { transform: scale(1.3); opacity: 1; }
    }

    .msg-bubble {
        padding: 1.25rem 2rem;
        border-radius: 24px;
        font-size: 1rem;
        line-height: 1.8;
        font-weight: 500;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        transition: transform 0.3s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.3s ease;
    }

    .aria-row .msg-bubble {
        background: var(--surface-2);
        color: var(--text);
        border: 2px solid var(--border);
        border-bottom-left-radius: 4px;
        min-width: 60px;
        box-shadow: 0 12px 30px rgba(0,0,0,0.06);
    }
    .dark-mode .aria-row .msg-bubble {
        background: #1e293b !important;
        color: #ffffff !important;
        border-color: #334155 !important;
    }

    .user-row .msg-bubble {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border-bottom-right-radius: 4px;
        box-shadow: 0 12px 35px rgba(16, 185, 129, 0.25);
        border: 1px solid rgba(255,255,255,0.1);
    }

    /* Markdown High-Contrast Overrides */
    .msg-bubble h1, .msg-bubble h2, .msg-bubble h3 { 
        font-size: 1.25rem; 
        font-weight: 900;
        margin: 1.5rem 0 0.75rem; 
        color: #10b981;
        font-family: 'Outfit', sans-serif;
    }
    .dark-mode .msg-bubble h1, .dark-mode .msg-bubble h2, .dark-mode .msg-bubble h3 {
        color: #10b981 !important;
    }
    .msg-bubble strong { font-weight: 900; color: inherit; } /* Use bubble color */
    .msg-bubble code {
        background: rgba(16, 185, 129, 0.1);
        padding: 0.25rem 0.6rem;
        border-radius: 8px;
        font-family: 'Inter', monospace;
        font-size: 0.9rem;
        color: #10b981;
        font-weight: 700;
    }
    .dark-mode .msg-bubble code {
        background: rgba(255,255,255,0.05) !important;
        color: #10b981 !important;
    }

    .chat-actions {
        padding: 0.75rem 2rem;
        background: #fcfdfe;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chat-input-bar {
        padding: 1.25rem 2rem;
        background: var(--surface-solid);
        border-top: 1px solid var(--border);
        display: flex;
        gap: 0.75rem;
        align-items: flex-end;
    }

    .msg-input {
        flex: 1;
        background: var(--surface-2);
        border: 2.5px solid var(--border);
        border-radius: 20px;
        padding: 1.25rem 1.75rem;
        font-family: inherit;
        font-size: 1rem;
        color: var(--text);
        resize: none;
        max-height: 150px;
        transition: var(--transition);
        line-height: 1.6;
        font-weight: 500;
    }
    .dark-mode .msg-input {
        background: #0f172a !important;
        border-color: #334155 !important;
        color: white !important;
    }
    .msg-input:focus { outline: none; background: var(--surface-solid); border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); }

    .send-btn {
        width: 48px; height: 48px;
        border-radius: var(--radius-sm);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: var(--transition);
        background: var(--primary);
        color: white;
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
        flex-shrink: 0;
    }
    .send-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(13,148,136,0.25); }
    .send-btn:disabled { opacity: 0.5; transform: none; cursor: not-allowed; }

    .starter-tag {
        background: var(--surface-solid);
        border: 1px solid var(--border);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        transition: var(--transition);
    }
    .starter-tag:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-glow); transform: translateY(-1px); }

    /* Report Modal Overlay */
    .report-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(15,23,42,0.5);
        backdrop-filter: blur(8px);
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    .report-overlay.open { display: flex; }
    .report-modal {
        background: var(--surface-solid);
        border-radius: var(--radius-lg);
        padding: 2.5rem;
        max-width: 650px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-lg);
        animation: modal-pop 0.3s cubic-bezier(0.34,1.56,0.64,1);
    }
    @keyframes modal-pop {
        from { transform: scale(0.85); opacity: 0; }
        to   { transform: scale(1);   opacity: 1; }
    }
</style>
@endpush

@section('content')
<div class="chat-layout">

    <!-- Aria Identity Panel -->
    <div class="aria-panel" style="background: var(--surface-solid); border: 2px solid var(--border); border-radius: 32px; box-shadow: var(--shadow-lg);">
        <div class="aria-avatar" onclick="openAriaLightbox()">
            <img src="{{ asset('images/aria-avatar.png') }}" alt="Aria Wellness Assistant">
        </div>
        <div class="online-badge" style="background: rgba(16,185,129,0.1); color: #10b981; border-color: rgba(16,185,129,0.2);"><span></span> Aria Online</div>
        <h2 style="font-family:'Outfit',sans-serif; font-weight:900; color:var(--text); font-size:1.5rem; margin-bottom:0.75rem; letter-spacing:-0.02em;">Meet Aria</h2>
        <p style="color:var(--text-dim); font-size:1rem; line-height:1.75; margin-bottom:1.5rem; font-weight:500;">Your confidential AI mental health companion. Share how you feel — Aria is here to listen and guide you.</p>

        <div class="stat-block" style="border: 2px solid var(--border); border-radius: 20px;">
            <div class="stat-row">
                <span class="stat-lbl">Conversation</span>
                <span class="stat-val" id="exchangeCount">{{ $chat_history->count() }} messages</span>
            </div>
            <div class="stat-row">
                <span class="stat-lbl">Privacy</span>
                <span class="stat-val">End-to-End</span>
            </div>
            <div class="stat-row">
                <span class="stat-lbl">Status</span>
                <span class="stat-val" style="color:#10b981;">Active</span>
            </div>
        </div>

        <div class="stat-block" style="margin-top: 1.25rem; border: 2px solid var(--border); border-radius: 20px; text-align:left;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                <div class="stat-lbl">Conversations</div>
                <button onclick="startNewConversation()" style="border:none; background:#10b981; color:white; border-radius:12px; padding:0.45rem 0.85rem; font-size:0.75rem; font-weight:800; cursor:pointer; box-shadow: 0 4px 10px rgba(16,185,129,0.2);">+ NEW</button>
            </div>
            <div style="display:flex; flex-direction:column; gap:0.6rem; max-height:200px; overflow:auto; padding-right: 0.25rem;">
                @forelse($conversations as $conv)
                    <a href="{{ route('student.chat', ['conversation' => $conv->conversation_id]) }}"
                       style="display:block; text-decoration:none; padding:0.75rem; border-radius:14px; border:2px solid {{ (int)$activeConversationId === (int)$conv->conversation_id ? '#10b981' : 'var(--border)' }}; background: {{ (int)$activeConversationId === (int)$conv->conversation_id ? 'rgba(16,185,129,0.1)' : 'var(--surface-2)' }}; color:var(--text); font-size:0.85rem; font-weight:700; transition: var(--transition);">
                        {{ \Illuminate\Support\Str::limit($conv->title, 32) }}
                    </a>
                @empty
                    <div style="font-size:0.85rem; color:var(--text-dim); font-weight:600; text-align:center; padding: 1rem 0;">No history yet</div>
                @endforelse
            </div>
        </div>

        <div class="crisis-box">
            <div style="font-weight:800; color:#991b1b; font-size:0.8rem; text-transform:uppercase; margin-bottom:0.6rem;">🚨 Crisis Help</div>
            <div style="font-size:0.88rem; color:#b91c1c; font-weight:600; line-height:1.6;">
                NCMH Hotline: 0804-4673<br>
                Emergency: 153
            </div>
        </div>
    </div>

    <!-- Main Chat -->
    <div class="chat-main-wrapper" style="display:flex; flex-direction:column; gap:1.75rem;">

        <div class="chat-interface">
            <div class="chat-topbar" role="banner">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div style="width:10px; height:10px; background:#059669; border-radius:50%; box-shadow:0 0 8px #10b981;"></div>
                    <span style="font-weight:800; font-size:0.95rem; color:var(--text);">Aria is Ready</span>
                </div>
                <div id="exchangeTag" style="font-weight:900; font-size:0.8rem; color:white; text-transform:uppercase; letter-spacing:0.12em; background:#10b981; padding:0.45rem 1.25rem; border-radius:10px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">{{ floor($chat_history->count() / 2) }} EXCHANGES</div>
            </div>

            <div class="message-stream" id="chatMessages" role="log" aria-live="polite" aria-label="Conversation history">
                @php
                    $visibleHistory = $chat_history->filter(fn($c) => !str_starts_with($c->message, '[System:'));
                @endphp

                @if($visibleHistory->count() === 0)
                <!-- Welcome screen -->
                <div id="chatWelcome" style="padding:5rem 1.5rem; text-align:center;">
                    <div style="font-size:4rem; margin-bottom:2rem; animation:pulse-aria 4s infinite;">🕯️</div>
                    <h3 style="font-family:'Outfit',sans-serif; font-size:2rem; font-weight:800; color:var(--primary-dark); margin-bottom:1rem;">Hi {{ explode(' ', auth()->user()->full_name)[0] }}, I'm Aria.</h3>
                    <p style="color:var(--text-dim); font-size:1.05rem; font-weight:600; max-width:460px; margin:0 auto 3rem; line-height:1.8;">How are you feeling today? You can type freely or choose a topic to start.</p>
                    <div style="display:flex; flex-wrap:wrap; gap:0.75rem; justify-content:center;">
                        <button class="starter-tag" onclick="sendStarter(this)">I feel stressed</button>
                        <button class="starter-tag" onclick="sendStarter(this)">I can't sleep</button>
                        <button class="starter-tag" onclick="sendStarter(this)">I'm anxious about school</button>
                        <button class="starter-tag" onclick="sendStarter(this)">I feel lonely</button>
                    </div>
                </div>
                @else
                    @foreach($visibleHistory as $chat)
                        <div class="stream-row {{ $chat->sender === 'user' ? 'user-row' : 'aria-row' }}">
                            <div style="width:40px;height:40px;border-radius:12px;overflow:hidden;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid var(--border);">
                                @if($chat->sender === 'user')
                                    <div style="width:100%;height:100%;background:var(--primary-glow);display:flex;align-items:center;justify-content:center;font-weight:800;color:var(--primary);font-size:0.85rem;">
                                        {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
                                    </div>
                                @else
                                    <img src="{{ asset('images/aria-avatar.png') }}" style="width:100%;height:100%;object-fit:cover;">
                                @endif
                            </div>
                            <div class="msg-bubble @if($chat->sender === 'aria') aria-msg-bubble @endif" data-raw="{{ $chat->message }}">
                                <!-- Rendered via JS on load for consistency -->
                                {!! nl2br(e($chat->message)) !!}
                            </div>
                        </div>
                    @endforeach
                @endif

                <!-- Typing indicator -->
                <div id="typingIndicator" style="display:none; align-items:center; gap:1.25rem; padding: 0.5rem 0; margin-bottom: 1.25rem;">
                    <div style="width:40px;height:40px;border-radius:12px;overflow:hidden;flex-shrink:0;border:1px solid var(--border);">
                        <img src="{{ asset('images/aria-avatar.png') }}" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    <div class="typing-aura" style="display:flex; gap:6px; background:white; padding: 1rem 1.5rem; border-radius:20px; border:1px solid rgba(13, 148, 136, 0.1); box-shadow: 0 10px 20px rgba(0,0,0,0.02);">
                        <span class="dot" style="width:8px; height:8px; background:var(--primary); border-radius:50%; opacity:0.4; animation: dot-pulse 1.4s infinite ease-in-out;"></span>
                        <span class="dot" style="width:8px; height:8px; background:var(--primary); border-radius:50%; opacity:0.4; animation: dot-pulse 1.4s infinite ease-in-out 0.2s;"></span>
                        <span class="dot" style="width:8px; height:8px; background:var(--primary); border-radius:50%; opacity:0.4; animation: dot-pulse 1.4s infinite ease-in-out 0.4s;"></span>
                    </div>
                </div>
            </div>

            <div class="chat-actions">
                <div style="display:flex; gap:1rem;">
                    <button id="reportBtn" onclick="openReportModal()" {{ $chat_history->count() < 4 ? 'disabled' : '' }} style="background:var(--primary); color:white; border:none; padding:0.85rem 1.75rem; border-radius:50px; font-weight:800; font-size:0.85rem; cursor:pointer; opacity:{{ $chat_history->count() < 4 ? '0.4' : '1' }}; transition:var(--transition);">📋 GENERATE REPORT</button>
                    <button onclick="endConversation()" style="background:rgba(239, 68, 68, 0.1); border:1.5px solid rgba(239, 68, 68, 0.2); padding:0.85rem 1.75rem; border-radius:50px; font-weight:800; font-size:0.85rem; color:#dc2626; cursor:pointer; transition:all 0.3s ease;">🚪 END CONVERSATION</button>
                </div>
                <div style="font-size:0.85rem; font-weight:800; color:var(--primary); cursor:pointer; display:flex; align-items:center; gap:0.4rem;" onclick="window.location.href='{{ route('student.appointments') }}'">
                    📅 Book a Counselor
                </div>
            </div>

            <!-- Voice Recording Overlay -->
            <div id="voiceOverlay" style="display:none; padding:1.25rem 2.5rem; background:linear-gradient(135deg,#f5f3ff,#ede9fe); border-top:1.5px solid #c4b5fd; align-items:center; gap:1.25rem;">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div id="voicePulse" style="width:16px; height:16px; background:#4338ca; border-radius:50%; animation:voice-pulse 1s infinite;"></div>
                    <span style="font-weight:800; font-size:0.88rem; color:#4338ca;">Listening…</span>
                </div>
                <div id="interimText" style="flex:1; font-size:0.95rem; color:#6d28d9; font-weight:600; font-style:italic; min-height:1.2rem;"></div>
                <div id="voiceStatus" style="font-size:0.7rem; font-weight:800; color:var(--text-dim); text-transform:uppercase; background:rgba(0,0,0,0.05); padding:0.3rem 0.6rem; border-radius:6px;">Ready</div>
                <button onclick="stopVoice()" style="background:#dc2626; color:white; border:none; padding:0.6rem 1.25rem; border-radius:50px; font-weight:800; font-size:0.8rem; cursor:pointer;">Stop</button>
            </div>

            <div class="chat-input-bar">
                <button id="micBtn" onclick="toggleVoice()" title="Hold to speak" aria-label="Toggle voice input" style="width:60px;height:60px;border-radius:20px;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.4rem;background:#f1f5f9;color:var(--text-dim);transition:var(--transition);flex-shrink:0;">🎤</button>
                <button id="handsFreeBtn" onclick="toggleHandsFree()" title="Toggle Hands-free mode" aria-label="Toggle hands-free mode" style="width:60px;height:60px;border-radius:20px;border:2.5px solid var(--border);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.3rem;background:var(--surface-solid);color:var(--text-dim);transition:var(--transition);flex-shrink:0;">🙌</button>
                <textarea id="chatInput" class="msg-input" placeholder="Type or tap 🎤 to speak…" rows="1" onkeydown="handleKey(event)" oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'" aria-label="Type your message"></textarea>
                <button id="ttsToggle" onclick="toggleTTS()" title="Toggle Aria voice" aria-label="Toggle text to speech" style="width:60px;height:60px;border-radius:20px;border:2.5px solid var(--border);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.3rem;background:var(--surface-solid);color:#10b981;transition:var(--transition);flex-shrink:0;">🔊</button>
                <button class="send-btn" id="sendBtn" onclick="sendMessage()" aria-label="Send message">➤</button>
            </div>
        </div>

        <!-- Disclaimer -->
        <div style="background:#fffbeb; border:1px solid #fef3c7; border-radius:24px; padding:1.75rem 2.5rem; display:flex; gap:1.25rem; align-items:flex-start;">
            <div style="font-size:1.3rem;">📜</div>
            <div style="font-size:0.9rem; line-height:1.6; color:#78350f; font-weight:500;">
                <strong style="display:block; margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em; font-size:0.75rem;">Notice</strong>
                Aria is an AI support tool, not a substitute for professional mental health care. All conversations may be reviewed by your designated counselor.
            </div>
        </div>
    </div>
</div>

<!-- Generate Clinical Report Modal -->
<div class="report-overlay" id="reportModal">
    <div class="report-modal">
        <div id="reportFormSection">
            <div style="font-weight:800; color:var(--primary); font-size:0.85rem; text-transform:uppercase; letter-spacing:0.12em; margin-bottom:1rem;">Clinical Report</div>
            <h2 style="font-family:'Outfit',sans-serif; font-size:2rem; font-weight:800; color:var(--primary-dark); margin-bottom:0.75rem;">Session Summary</h2>
            <p style="color:var(--text-dim); font-weight:600; margin-bottom:2.5rem; line-height:1.7;">Add a few quick details to complete your wellness report. This will be saved and shared with your counselor.</p>

            <form id="reportForm" onsubmit="event.preventDefault(); submitReport();">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:2rem; margin-bottom:2rem;">
                    <div>
                        <label style="display:block; font-weight:800; font-size:0.75rem; color:var(--text-dim); text-transform:uppercase; margin-bottom:0.75rem;">How are you feeling now?</label>
                        <select id="rp_mood" style="width:100%; padding:1rem 1.25rem; border-radius:14px; border:2px solid var(--border); font-family:inherit; font-weight:600; background:#f8fafc; font-size:0.95rem;">
                            <option value="positive">😊 Good / Positive</option>
                            <option value="neutral" selected>😐 Okay / Neutral</option>
                            <option value="low">😔 Low / Sad</option>
                            <option value="concerning">😰 Anxious / Distressed</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-weight:800; font-size:0.75rem; color:var(--text-dim); text-transform:uppercase; margin-bottom:0.75rem;">Sleep quality (last night)</label>
                        <select id="rp_sleep" style="width:100%; padding:1rem 1.25rem; border-radius:14px; border:2px solid var(--border); font-family:inherit; font-weight:600; background:#f8fafc; font-size:0.95rem;">
                            <option value="5">Excellent</option>
                            <option value="4">Good</option>
                            <option value="3" selected>Fair</option>
                            <option value="2">Poor</option>
                            <option value="1">Very Poor</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-weight:800; font-size:0.75rem; color:var(--text-dim); text-transform:uppercase; margin-bottom:0.75rem;">Stress level (1 = calm, 10 = very stressed)</label>
                    <div style="display:flex; align-items:center; gap:1rem;">
                        <input type="range" id="rp_stress" min="1" max="10" value="5" style="flex:1;" oninput="document.getElementById('stressVal').textContent=this.value">
                        <span id="stressVal" style="font-weight:800; color:var(--primary); min-width:30px;">5</span>
                    </div>
                </div>

                <div style="margin-bottom:2rem;">
                    <label style="display:block; font-weight:800; font-size:0.75rem; color:var(--text-dim); text-transform:uppercase; margin-bottom:0.75rem;">Main concern from today's chat (optional)</label>
                    <textarea id="rp_concern" style="width:100%; padding:1.25rem; border-radius:14px; border:2px solid var(--border); font-family:inherit; height:100px; background:#f8fafc; font-weight:500; resize:none; font-size:0.95rem;" placeholder="What was weighing on you most?"></textarea>
                </div>

                <div style="display:flex; gap:1rem;">
                    <button type="submit" id="submitReportBtn" style="flex:2; background:var(--primary); color:white; border:none; padding:1.25rem; border-radius:50px; font-weight:800; font-size:1rem; cursor:pointer; box-shadow:0 12px 30px rgba(67,56,202,0.2);">Generate & Save Report</button>
                    <button type="button" onclick="closeReportModal()" style="flex:1; background:white; border:2px solid var(--border); padding:1.25rem; border-radius:50px; font-weight:800; font-size:1rem; color:var(--text-dim); cursor:pointer;">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Report Result Section -->
        <div id="reportResultSection" style="display:none; text-align:center; padding:1rem;">
            <div style="font-size:3rem; margin-bottom:1rem;">✅</div>
            <h3 style="font-family:'Outfit',sans-serif; font-size:1.5rem; font-weight:800; color:var(--primary-dark); margin-bottom:1rem;">Report Generated</h3>
            <p style="color:var(--text-dim); font-weight:600; margin-bottom:2rem;">Your wellness summary has been saved and shared with your counselor. You can view it in your reports history.</p>
            <div style="display:flex; gap:1rem; justify-content:center;">
                <button onclick="window.location.href='{{ route('student.reports.index') }}'" style="background:var(--primary); color:white; border:none; padding:1rem 2rem; border-radius:50px; font-weight:800; cursor:pointer;">View in Vault</button>
                <button onclick="closeReportModal()" style="background:white; border:2px solid var(--border); color:var(--text-dim); padding:1rem 2rem; border-radius:50px; font-weight:800; cursor:pointer;">Continue Chatting</button>
            </div>
        </div>
    </div>
</div>

<!-- End Conversation Confirmation Modal -->
<div class="report-overlay" id="endConversationModal">
    <div class="report-modal" style="max-width: 450px; text-align: center; padding: 3.5rem 2.5rem;">
        <div style="width: 80px; height: 80px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem;">
            <i class="ph ph-door-open"></i>
        </div>
        <h3 style="font-family:'Outfit',sans-serif; font-weight:900; font-size:1.75rem; margin-bottom:0.75rem; color: var(--text); letter-spacing: -0.02em;">Archive Session?</h3>
        <p style="color:var(--text-dim); font-size:1rem; margin-bottom:2.5rem; font-weight: 500; line-height: 1.6;">Are you sure you want to end this session? Aria will start a fresh conversation for your next check-in.</p>
        <div style="display:flex; gap:1.25rem;">
            <button onclick="closeEndConversationModal()" style="flex:1; background:var(--surface-2); border:1.5px solid var(--border); padding:1rem; border-radius:16px; font-weight:800; cursor:pointer; color: var(--text); font-size: 0.9rem;">CANCEL</button>
            <button onclick="confirmEndConversation()" style="flex:2; border:none; padding:1rem; border-radius:16px; font-weight:900; cursor:pointer; background:#ef4444; color:white; font-size: 0.9rem; box-shadow: 0 10px 20px rgba(239, 68, 68, 0.2);">CONFIRM END</button>
        </div>
    </div>
</div>

<!-- Aria Avatar Lightbox -->
<div id="ariaLightbox" class="aria-lightbox" onclick="closeAriaLightbox()">
    <img src="{{ asset('images/aria-avatar.png') }}" class="lightbox-content" alt="Full Avatar">
    <div style="position: absolute; top: 2rem; right: 2rem; color: white; font-size: 2rem; cursor: pointer;">
        <i class="ph ph-x"></i>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Initial Layout Entrance
    gsap.from('.aria-panel', { x: -50, opacity: 0, duration: 1.2, ease: "expo.out", delay: 0.2 });
    gsap.from('.chat-interface', { y: 30, opacity: 0, duration: 1.2, ease: "expo.out", delay: 0.4 });
    
    // Render history messages via Marked for consistency
    document.querySelectorAll('.msg-bubble[data-raw]').forEach(el => {
        el.innerHTML = marked.parse(el.getAttribute('data-raw'));
    });

    scrollToBottom();

    // Magnetic Input Effect
    const input = document.querySelector('.msg-input');
    if (input) {
        input.addEventListener('focus', () => {
            gsap.to('.chat-input-bar', { 
                borderColor: 'var(--primary-light)', 
                boxShadow: '0 0 30px rgba(13, 148, 136, 0.15)',
                duration: 0.4 
            });
        });
        input.addEventListener('blur', () => {
            gsap.to('.chat-input-bar', { 
                borderColor: 'var(--border)', 
                boxShadow: 'none',
                duration: 0.4 
            });
        });
    }

    // Magnetic Buttons (Starters & Primary Actions)
    const interactives = document.querySelectorAll('.starter-tag, .send-btn, #reportBtn');
    interactives.forEach(el => {
        el.addEventListener('mousemove', (e) => {
            const rect = el.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            gsap.to(el, { x: x * 0.3, y: y * 0.3, scale: 1.05, duration: 0.4, ease: "power2.out" });
        });
        el.addEventListener('mouseleave', () => {
            gsap.to(el, { x: 0, y: 0, scale: 1, duration: 0.6, ease: "elastic.out(1, 0.3)" });
        });
    });
});
let isWaiting = false;
const userInitial = "{{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}";
let activeConversationId = {{ $activeConversationId ? (int) $activeConversationId : 'null' }};
const debugRunId = 'initial';
function debugLog(hypothesisId, location, message, data = {}) {
    // #region agent log
    fetch('http://127.0.0.1:7562/ingest/38cc8233-db14-4f39-87ee-19f5a468ae9e',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'a97deb'},body:JSON.stringify({sessionId:'a97deb',runId:debugRunId,hypothesisId,location,message,data,timestamp:Date.now()})}).catch(()=>{});
    // #endregion
}

// Reliable message counter – avoids parseInt on localised strings
let messageCount = {{ $chat_history->count() }};

function updateCounters() {
    messageCount += 2; // user + aria
    document.getElementById('exchangeCount').textContent = messageCount + ' messages';
    const exchanges = Math.floor(messageCount / 2);
    document.getElementById('exchangeTag').textContent = exchanges + ' Exchanges';
    // Enable report button once ≥ 2 full exchanges (4 messages)
    if (messageCount >= 4) {
        const btn = document.getElementById('reportBtn');
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
    }
}

// ── Text-to-Speech (Aria speaks back) ──
let ttsEnabled = true;
let handsFreeEnabled = false;
let ariaVoice = null;

function loadVoices() {
    const voices = window.speechSynthesis.getVoices();
    // Strictly prioritize pleasant, natural female voices
    ariaVoice = voices.find(v => v.name.includes('Google') && v.name.includes('Female'))
             || voices.find(v => v.name.includes('Google') && v.name.includes('UK English Female'))
             || voices.find(v => v.name.includes('Natural') && v.name.includes('Female'))
             || voices.find(v => /samantha|zira|victoria|karen|moira|fiona|tessa/i.test(v.name))
             || voices.find(v => v.lang.startsWith('en') && !v.name.toLowerCase().includes('male'))
             || voices[0] || null;
}

if (window.speechSynthesis) {
    if (speechSynthesis.getVoices().length) loadVoices();
    else speechSynthesis.onvoiceschanged = loadVoices;
}

function speakAria(text) {
    if (!ttsEnabled || !window.speechSynthesis) {
        if (handsFreeEnabled) setTimeout(toggleVoice, 500);
        return;
    }
    speechSynthesis.cancel();

    // Custom 'Aria' Vocal Inflection Engine
    // Splits text into natural phrases to apply unique 'lilting' pitch shifts
    const phrases = text.replace(/<[^>]*>/g, '').split(/(?<=[.!?])\s+/);
    let phraseIndex = 0;

    function speakNextPhrase() {
        if (phraseIndex >= phrases.length) {
            document.querySelectorAll('.aria-avatar').forEach(el => el.classList.remove('speaking'));
            if (handsFreeEnabled) setTimeout(toggleVoice, 500);
            return;
        }

        const phrase = phrases[phraseIndex].trim();
        if (!phrase) { phraseIndex++; speakNextPhrase(); return; }

        const utt = new SpeechSynthesisUtterance(phrase);
        
        // Aria's Signature: Playful, rhythmic, and high-pitched
        // We vary the pitch slightly per phrase to sound 'custom' and alive
        const basePitch = 1.6;
        const pitchShift = (phraseIndex % 2 === 0) ? 0.05 : -0.05;
        
        utt.pitch = basePitch + pitchShift;
        utt.rate = 0.96;
        utt.volume = 1.0;

        if (ariaVoice) utt.voice = ariaVoice;

        utt.onstart = () => {
            document.querySelectorAll('.aria-avatar').forEach(el => el.classList.add('speaking'));
        };

        utt.onend = () => {
            phraseIndex++;
            speakNextPhrase();
        };

        utt.onerror = () => {
            document.querySelectorAll('.aria-avatar').forEach(el => el.classList.remove('speaking'));
        };

        speechSynthesis.speak(utt);
    }

    speakNextPhrase();
}

function toggleTTS() {
    ttsEnabled = !ttsEnabled;
    const btn = document.getElementById('ttsToggle');
    btn.textContent  = ttsEnabled ? '🔊' : '🔇';
    btn.style.color  = ttsEnabled ? 'var(--primary)' : 'var(--text-dim)';
    btn.style.borderColor = ttsEnabled ? 'var(--primary-light)' : 'var(--border)';
    if (!ttsEnabled) speechSynthesis.cancel();
}

function toggleHandsFree() {
    handsFreeEnabled = !handsFreeEnabled;
    const btn = document.getElementById('handsFreeBtn');
    btn.style.color = handsFreeEnabled ? 'var(--primary)' : 'var(--text-dim)';
    btn.style.borderColor = handsFreeEnabled ? 'var(--primary-light)' : 'var(--border)';
    btn.style.background = handsFreeEnabled ? 'var(--primary-glow)' : 'white';
    if (handsFreeEnabled && !recognizing) toggleVoice();
}

// ── Speech Recognition ──
const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
let recognition = null;
let recognizing  = false;
let finalTranscript = '';

if (SpeechRecognition) {
    recognition = new SpeechRecognition();
    recognition.lang = 'en-US';
    recognition.interimResults = true;
    recognition.continuous = false;

    recognition.onstart = () => {
        debugLog('H1', 'student/chat.blade.php:recognition.onstart', 'Speech recognition started', {
            lang: recognition.lang,
            continuous: recognition.continuous
        });
        finalTranscript = '';
        document.getElementById('voiceOverlay').style.display = 'flex';
        document.getElementById('interimText').textContent = '';
        document.getElementById('voiceStatus').textContent = 'Live';
        document.getElementById('micBtn').style.background = '#ede9fe';
        document.getElementById('micBtn').style.color = '#4338ca';
        if (window.speechSynthesis) speechSynthesis.cancel();
    };

    recognition.onresult = (e) => {
        let interim = '';
        for (let i = e.resultIndex; i < e.results.length; i++) {
            if (e.results[i].isFinal) finalTranscript += e.results[i][0].transcript + ' ';
            else interim += e.results[i][0].transcript;
        }
        document.getElementById('interimText').textContent = interim || finalTranscript;
        debugLog('H2', 'student/chat.blade.php:recognition.onresult', 'Speech recognition result', {
            interimLength: interim.length,
            finalLength: finalTranscript.trim().length
        });
    };

    recognition.onend = () => {
        const msg = finalTranscript.trim();
        debugLog('H2', 'student/chat.blade.php:recognition.onend', 'Speech recognition ended', {
            hasMessage: !!msg,
            messageLength: msg.length,
            handsFreeEnabled,
            recognizing
        });
        if (msg) {
            document.getElementById('chatInput').value = msg;
            sendMessage();
        } else if (handsFreeEnabled && recognizing) {
            setTimeout(toggleVoice, 500);
        }
        closeVoiceOverlay();
    };
    recognition.onerror = (event) => {
        debugLog('H1', 'student/chat.blade.php:recognition.onerror', 'Speech recognition error', {
            error: event.error || 'unknown',
            message: event.message || ''
        });
    };
}

async function toggleVoice() {
    if (!SpeechRecognition) {
        debugLog('H1', 'student/chat.blade.php:toggleVoice', 'Speech recognition unavailable', {
            userAgent: navigator.userAgent
        });
        return alert('Speech Recognition is NOT available in this browser.');
    }
    if (recognizing) { recognition.stop(); return; }
    try {
        recognizing = true;
        debugLog('H1', 'student/chat.blade.php:toggleVoice', 'Attempting recognition.start', {
            recognizing
        });
        recognition.start();
    } catch (err) {
        recognizing = false;
        debugLog('H1', 'student/chat.blade.php:toggleVoice.catch', 'Failed to start recognition', {
            error: err?.message || String(err)
        });
    }
}

function stopVoice() { 
    if (window.speechSynthesis) speechSynthesis.cancel();
    if (recognition && recognizing) {
        recognition.stop(); 
        recognition.abort(); // Force immediate stop
    }
    closeVoiceOverlay();
}
function closeVoiceOverlay() {
    recognizing = false;
    document.getElementById('voiceOverlay').style.display = 'none';
    document.getElementById('micBtn').style.background = 'var(--surface-2)';
    document.getElementById('micBtn').style.color = 'var(--text-dim)';
}

function scrollToBottom() {
    setTimeout(() => {
        const stream = document.getElementById('chatMessages');
        if (stream) {
            stream.scrollTo({
                top: stream.scrollHeight,
                behavior: 'smooth'
            });
        }
    }, 100);
}

function appendMessage(role, content, isNew = false) {
    const welcome = document.getElementById('chatWelcome');
    if(welcome) welcome.style.display = 'none';
    const stream = document.getElementById('chatMessages');
    const row = document.createElement('div');
    row.className = 'stream-row ' + (role === 'user' ? 'user-row' : 'aria-row');
    
    const avatarHtml = role === 'user' 
        ? `<div style="width:100%;height:100%;background:var(--primary-glow);display:flex;align-items:center;justify-content:center;font-weight:800;color:var(--primary);font-size:0.85rem;">${userInitial}</div>`
        : `<img src="{{ asset('images/aria-avatar.png') }}" style="width:100%;height:100%;object-fit:cover;">`;

    row.innerHTML = `
        <div class="avatar-box" style="width:40px;height:40px;border-radius:12px;overflow:hidden;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid var(--border);">
            ${avatarHtml}
        </div>
        <div class="msg-bubble ${role==='aria'?'aria-msg-bubble':''}"></div>
    `;
    
    const bubble = row.querySelector('.msg-bubble');
    stream.insertBefore(row, document.getElementById('typingIndicator'));

    // Markdown Parser Options
    marked.setOptions({ gfm: true, breaks: true });

    // GSAP Animation for Row Entry
    gsap.from(row, {
        y: 30,
        opacity: 0,
        x: role === 'user' ? 20 : -20,
        duration: 0.6,
        ease: "back.out(1.7)"
    });

    if (role === 'aria' && isNew) {
        let i = 0;
        const rawContent = content;
        const interval = setInterval(() => {
            if (i < rawContent.length) {
                bubble.innerHTML = marked.parse(rawContent.substring(0, i + 3));
                i += 3;
                scrollToBottom();
            } else {
                clearInterval(interval);
                bubble.innerHTML = marked.parse(rawContent);
                scrollToBottom();
            }
        }, 15);
    } else {
        bubble.innerHTML = role === 'user' ? content : marked.parse(content);
        scrollToBottom();
    }
}

async function sendMessage(textOverride) {
    const input = document.getElementById('chatInput');
    const msg = textOverride || input.value.trim();
    if (!msg || isWaiting) return;
    debugLog('H3', 'student/chat.blade.php:sendMessage', 'Sending chat message', {
        messageLength: msg.length,
        fromVoice: !textOverride
    });

    input.value = '';
    input.style.height = 'auto';
    appendMessage('user', msg);

    isWaiting = true;
    document.getElementById('sendBtn').disabled = true;
    document.getElementById('typingIndicator').style.display = 'flex';
    scrollToBottom();

    try {
        const response = await fetch("{{ route('student.chat.send') }}", {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({ message: msg, conversation_id: activeConversationId })
        });

        document.getElementById('typingIndicator').style.display = 'none';

        if (!response.ok) {
            debugLog('H3', 'student/chat.blade.php:sendMessage.response', 'Chat response non-OK', {
                status: response.status
            });
            // Server returned 4xx / 5xx – show a friendly fallback
            const errText = response.status === 422
                ? "Your message couldn't be sent. Please keep it under 1 000 characters."
                : "Aria is taking a moment to respond. Please try again shortly.";
            appendMessage('aria', errText);
        } else {
            const data = await response.json();
            debugLog('H3', 'student/chat.blade.php:sendMessage.response', 'Chat response OK', {
                success: !!data.success,
                responseLength: (data.message || '').length
            });
            if (data.success && data.message) {
                if (data.conversation_id) {
                    activeConversationId = data.conversation_id;
                }
                appendMessage('aria', data.message, true); // true for typing effect
                speakAria(data.message);
                updateCounters();
            } else {
                appendMessage('aria', "I didn't quite catch that. Could you say it again?");
            }
        }
    } catch (e) {
        debugLog('H3', 'student/chat.blade.php:sendMessage.catch', 'Chat request failed', {
            error: e?.message || String(e)
        });
        document.getElementById('typingIndicator').style.display = 'none';
        appendMessage('aria', "I'm having trouble connecting. Please check your connection and try again.");
    } finally {
        isWaiting = false;
        document.getElementById('sendBtn').disabled = false;
    }
}

async function startNewConversation() {
    try {
        const response = await fetch("{{ route('student.chat.new') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({})
        });
        const data = await response.json();
        if (data.success && data.conversation_id) {
            activeConversationId = data.conversation_id;
            // Update URL without reload
            const newUrl = "{{ route('student.chat') }}" + '?conversation=' + data.conversation_id;
            history.pushState(null, '', newUrl);
            
            // Clear UI
            const stream = document.getElementById('chatMessages');
            stream.innerHTML = `
                <div id="chatWelcome" style="padding:5rem 1.5rem; text-align:center;">
                    <div style="font-size:4rem; margin-bottom:2rem; animation:pulse-aria 4s infinite;">🕯️</div>
                    <h3 style="font-family:'Outfit',sans-serif; font-size:2rem; font-weight:800; color:var(--primary-dark); margin-bottom:1rem;">Hi {{ explode(' ', auth()->user()->full_name)[0] }}, I'm Aria.</h3>
                    <p style="color:var(--text-dim); font-size:1.05rem; font-weight:600; max-width:460px; margin:0 auto 3rem; line-height:1.8;">How are you feeling today? You can type freely or choose a topic to start.</p>
                    <div style="display:flex; flex-wrap:wrap; gap:0.75rem; justify-content:center;">
                        <button class="starter-tag" onclick="sendStarter(this)">I feel stressed</button>
                        <button class="starter-tag" onclick="sendStarter(this)">I can't sleep</button>
                        <button class="starter-tag" onclick="sendStarter(this)">I'm anxious about school</button>
                        <button class="starter-tag" onclick="sendStarter(this)">I feel lonely</button>
                    </div>
                </div>
            `;
            
            // Re-append typing indicator which was just cleared
            const ti = document.createElement('div');
            ti.id = 'typingIndicator';
            ti.style.display = 'none';
            ti.style.alignItems = 'center';
            ti.style.gap = '1.25rem';
            ti.style.padding = '0.5rem 0';
            ti.style.marginBottom = '1.25rem';
            ti.innerHTML = `
                <div style="width:40px;height:40px;border-radius:12px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:800;color:var(--text-dim);font-size:0.85rem;">✨</div>
                <div class="typing-aura" style="display:flex; gap:6px; background:white; padding: 1rem 1.5rem; border-radius:20px; border:1px solid rgba(13, 148, 136, 0.1); box-shadow: 0 10px 20px rgba(0,0,0,0.02);">
                    <span class="dot" style="width:8px; height:8px; background:var(--primary); border-radius:50%; opacity:0.4; animation: dot-pulse 1.4s infinite ease-in-out;"></span>
                    <span class="dot" style="width:8px; height:8px; background:var(--primary); border-radius:50%; opacity:0.4; animation: dot-pulse 1.4s infinite ease-in-out 0.2s;"></span>
                    <span class="dot" style="width:8px; height:8px; background:var(--primary); border-radius:50%; opacity:0.4; animation: dot-pulse 1.4s infinite ease-in-out 0.4s;"></span>
                </div>
            `;
            stream.appendChild(ti);
            
            // Reset counters
            messageCount = 0;
            document.getElementById('exchangeCount').textContent = '0 messages';
            document.getElementById('exchangeTag').textContent = '0 EXCHANGES';
            document.getElementById('reportBtn').disabled = true;
            document.getElementById('reportBtn').style.opacity = '0.4';
            
            App.toast({ type: 'success', title: 'New Conversation', message: 'Aria is ready for a fresh start.' });
        }
    } catch (e) {
        App.toast({ type: 'error', title: 'Error', message: 'Could not start a new conversation right now.' });
    }
}

function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
}

function sendStarter(btn) { sendMessage(btn.textContent); }

// ── Conversation Lifecycle ──
function endConversation() {
    document.getElementById('endConversationModal').classList.add('open');
}

function closeEndConversationModal() {
    document.getElementById('endConversationModal').classList.remove('open');
}

function confirmEndConversation() {
    closeEndConversationModal();
    startNewConversation();
}

// ── Report Modal ──
function openReportModal() {
    document.getElementById('reportModal').classList.add('open');
}

function closeReportModal() {
    document.getElementById('reportModal').classList.remove('open');
}

async function submitReport() {
    const btn = document.getElementById('submitReportBtn');
    const originalBtnText = 'Generate & Save Report';
    btn.textContent = 'Generating…';
    btn.disabled = true;

    // Collect transcript – skip typing indicator and any row without a bubble
    const messages = [];
    document.querySelectorAll('.stream-row').forEach(row => {
        const bubble = row.querySelector('.msg-bubble');
        if (!bubble) return; // skip non-message rows (typing indicator, etc.)
        const txt = (bubble.textContent || '').trim();
        if (!txt) return; // skip empty
        const role = row.classList.contains('user-row') ? 'Student' : 'Aria';
        messages.push(role + ': ' + txt);
    });

    if (messages.length < 2) {
        btn.textContent = originalBtnText;
        btn.disabled = false;
        alert('Please have at least a short conversation with Aria before generating a report.');
        return;
    }

    const form = {
        mood_now: document.getElementById('rp_mood').value,
        sleep_quality: parseInt(document.getElementById('rp_sleep').value),
        stress_level: parseInt(document.getElementById('rp_stress').value),
        main_concern: document.getElementById('rp_concern').value,
    };

    try {
        const response = await fetch("{{ route('student.chat.pre-assessment') }}", {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify({ transcript: messages.join('\n\n'), form: form })
        });

        if (!response.ok) {
            throw new Error('Server returned ' + response.status);
        }

        const data = await response.json();
        if (data.success) {
            document.getElementById('reportFormSection').style.display = 'none';
            document.getElementById('reportResultSection').style.display = 'block';
        } else {
            throw new Error(data.message || 'Report generation failed.');
        }
    } catch(e) {
        console.error('Report error:', e);
        btn.textContent = originalBtnText;
        btn.disabled = false;
        alert('Could not generate report. Please try again in a moment.');
    }
}

// ── Lightbox Logic ──
function openAriaLightbox() {
    document.getElementById('ariaLightbox').classList.add('open');
}
function closeAriaLightbox() {
    document.getElementById('ariaLightbox').classList.remove('open');
}

// Reveal initial messages if any
document.addEventListener('DOMContentLoaded', scrollToBottom);
</script>

<style>
    @keyframes bounce-aria {
        0%, 80%, 100% { transform: translateY(0); opacity: 0.5; }
        40% { transform: translateY(-8px); opacity: 1; }
    }
    @keyframes voice-pulse {
        0%, 100% { transform: scale(1);   opacity: 1; }
        50%       { transform: scale(1.6); opacity: 0.4; }
    }
</style>
@endpush
