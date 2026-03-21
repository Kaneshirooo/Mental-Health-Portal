<?php
require_once 'config.php';
requireStudent();

$user_id = $_SESSION['user_id'];
$user    = getUserData($user_id);
$name    = htmlspecialchars(explode(' ', $user['full_name'])[0]);
$notif_count = getNotificationCount($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talk to Aria — Mental Health Portal</title>
    <meta name="description" content="Chat with Aria, your AI mental health companion.">
    <link rel="stylesheet" href="styles.css">
    <style>
        .chat-layout {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 2rem 3rem;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            align-items: start;
        }

        /* Aria Identity Panel */
        .aria-panel {
            background: white;
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
            width: 80px;
            height: 80px;
            background: var(--primary-glow);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1.25rem;
            border: 2px solid white;
            box-shadow: 0 8px 20px rgba(13, 148, 136, 0.1);
            animation: pulse-aria 4s infinite;
        }

        @keyframes pulse-aria {
            0%, 100% { transform: scale(1); box-shadow: 0 8px 20px rgba(13, 148, 136, 0.1); }
            50%       { transform: scale(1.03); box-shadow: 0 12px 28px rgba(13, 148, 136, 0.15); }
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
            padding: 1.25rem;
            background: #fef2f2;
            border-radius: var(--radius-sm);
            border: 1px solid #fee2e2;
            text-align: left;
        }

        /* Main Chat Interface */
        .chat-interface {
            background: white;
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
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(16px);
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
            background: linear-gradient(to bottom, #ffffff, #fcfdfe);
        }

        .stream-row {
            display: flex;
            gap: 1.25rem;
            max-width: 88%;
            animation: fade-up 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        }

        @keyframes fade-up {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .stream-row.user-row { align-self: flex-end; flex-direction: row-reverse; }

        .msg-bubble {
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            font-size: 0.95rem;
            line-height: 1.65;
            font-weight: 400;
            box-shadow: var(--shadow-sm);
        }

        .aria-row .msg-bubble {
            background: white;
            color: var(--text);
            border: 1px solid var(--border);
            border-bottom-left-radius: 4px;
        }

        .user-row .msg-bubble {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-bottom-right-radius: 4px;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
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
            background: white;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 0.75rem;
            align-items: flex-end;
        }

        .msg-input {
            flex: 1;
            background: var(--surface-2);
            border: 2px solid transparent;
            border-radius: var(--radius-sm);
            padding: 0.85rem 1.25rem;
            font-family: inherit;
            font-size: 0.95rem;
            color: var(--text);
            resize: none;
            max-height: 130px;
            transition: var(--transition);
            line-height: 1.6;
            font-weight: 400;
        }
        .msg-input:focus { outline: none; background: white; border-color: var(--primary-light); box-shadow: 0 0 0 3px rgba(13,148,136,0.06); }

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

        .mic-btn {
            width: 48px; height: 48px;
            border-radius: var(--radius-sm);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            background: var(--surface-2);
            color: var(--text-dim);
            transition: var(--transition);
            flex-shrink: 0;
        }
        .mic-btn.active { background: #fee2e2; color: #dc2626; }

        .starter-tag {
            background: white;
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

        /* Report Modal */
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
            background: white;
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            max-width: 650px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            animation: modal-pop 0.3s cubic-bezier(0.34,1.56,0.64,1);
        }
        @keyframes modal-pop {
            from { transform: scale(0.85); opacity: 0; }
            to   { transform: scale(1);   opacity: 1; }
        }
        /* Print Styles */
        @media print {
            body * { visibility: hidden; }
            #reportResultSection, #reportResultSection * { visibility: visible; }
            #reportResultSection { position: absolute; left: 0; top: 0; width: 100%; border: none !important; padding: 0 !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="chat-layout">

    <!-- Aria Identity Panel -->
    <div class="aria-panel">
        <div class="aria-avatar">✨</div>
        <div class="online-badge"><span></span> Aria Online</div>
        <h2 style="font-family:'Outfit',sans-serif; font-weight:700; color:var(--text); font-size:1.15rem; margin-bottom:0.5rem;">Meet Aria</h2>
        <p style="color:var(--text-dim); font-size:0.98rem; line-height:1.75; margin-bottom:0.5rem;">Your confidential AI mental health companion. Share how you feel — Aria is here to listen and guide you.</p>

        <div class="stat-block">
            <div class="stat-row">
                <span class="stat-lbl">Conversation</span>
                <span class="stat-val" id="exchangeCount">0 messages</span>
            </div>
            <div class="stat-row">
                <span class="stat-lbl">Privacy</span>
                <span class="stat-val">End-to-End</span>
            </div>
            <div class="stat-row">
                <span class="stat-lbl">Status</span>
                <span class="stat-val" style="color:#059669;">Active</span>
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
    <div style="display:flex; flex-direction:column; gap:1.75rem;">

        <div class="chat-interface">
            <div class="chat-topbar">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div style="width:10px; height:10px; background:#10b981; border-radius:50%; box-shadow:0 0 8px #10b981;"></div>
                    <span style="font-weight:800; font-size:0.95rem; color:var(--text);">Aria is Ready</span>
                </div>
                <div id="exchangeTag" style="font-weight:800; font-size:0.8rem; color:var(--text-dim); text-transform:uppercase; letter-spacing:0.08em; background:#f1f5f9; padding:0.4rem 0.9rem; border-radius:8px;">0 Exchanges</div>
            </div>

            <div class="message-stream" id="chatMessages">
                <!-- Welcome screen -->
                <div id="chatWelcome" style="padding:5rem 1.5rem; text-align:center;">
                    <div style="font-size:4rem; margin-bottom:2rem; animation:pulse-aria 4s infinite;">🕯️</div>
                    <h3 style="font-family:'Outfit',sans-serif; font-size:2rem; font-weight:800; color:var(--primary-dark); margin-bottom:1rem;">Hi <?php echo $name; ?>, I'm Aria.</h3>
                    <p style="color:var(--text-dim); font-size:1.05rem; font-weight:600; max-width:460px; margin:0 auto 3rem; line-height:1.8;">How are you feeling today? You can type freely or choose a topic to start.</p>
                    <div style="display:flex; flex-wrap:wrap; gap:0.75rem; justify-content:center;">
                        <button class="starter-tag" onclick="sendStarter(this)">I feel stressed</button>
                        <button class="starter-tag" onclick="sendStarter(this)">I can't sleep</button>
                        <button class="starter-tag" onclick="sendStarter(this)">I'm anxious about school</button>
                        <button class="starter-tag" onclick="sendStarter(this)">I feel lonely</button>
                    </div>
                </div>

                <!-- Typing indicator -->
                <div id="typingIndicator" style="display:none; align-items:center; gap:1rem; animation:fade-up 0.4s ease-out;">
                    <div style="width:38px; height:38px; border-radius:12px; background:var(--primary-glow); display:flex; align-items:center; justify-content:center;">✨</div>
                    <div style="background:#f8fafc; padding:1.25rem 1.75rem; border-radius:20px; border-bottom-left-radius:4px; border:1px solid var(--border);">
                        <div style="display:flex; gap:5px;">
                            <div style="width:7px;height:7px;background:var(--primary);border-radius:50%;animation:bounce-aria 1.4s infinite;"></div>
                            <div style="width:7px;height:7px;background:var(--primary);border-radius:50%;animation:bounce-aria 1.4s infinite 0.2s;"></div>
                            <div style="width:7px;height:7px;background:var(--primary);border-radius:50%;animation:bounce-aria 1.4s infinite 0.4s;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="chat-actions">
                <div style="display:flex; gap:1rem;">
                    <button id="reportBtn" onclick="openReportModal()" disabled style="background:var(--primary); color:white; border:none; padding:0.85rem 1.75rem; border-radius:50px; font-weight:800; font-size:0.85rem; cursor:pointer; opacity:0.4; transition:var(--transition);">📋 GENERATE REPORT</button>
                    <button onclick="clearChat()" style="background:white; border:1.5px solid var(--border); padding:0.85rem 1.75rem; border-radius:50px; font-weight:800; font-size:0.85rem; color:var(--text-dim); cursor:pointer;">CLEAR</button>
                </div>
                <div style="font-size:0.85rem; font-weight:800; color:var(--primary); cursor:pointer; display:flex; align-items:center; gap:0.4rem;" onclick="window.location.href='student_appointments.php'">
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
                <button id="micBtn" onclick="toggleVoice()" title="Hold to speak" style="width:60px;height:60px;border-radius:20px;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.4rem;background:#f1f5f9;color:var(--text-dim);transition:var(--transition);flex-shrink:0;">🎤</button>
                <button id="handsFreeBtn" onclick="toggleHandsFree()" title="Toggle Hands-free mode" style="width:60px;height:60px;border-radius:20px;border:1.5px solid var(--border);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.3rem;background:white;color:var(--text-dim);transition:var(--transition);flex-shrink:0;">🙌</button>
                <textarea id="chatInput" class="msg-input" placeholder="Type or tap 🎤 to speak…" rows="1" onkeydown="handleKey(event)" oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"></textarea>
                <button id="ttsToggle" onclick="toggleTTS()" title="Toggle Aria voice" style="width:60px;height:60px;border-radius:20px;border:1.5px solid var(--border);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.3rem;background:white;color:var(--primary);transition:var(--transition);flex-shrink:0;">🔊</button>
                <button class="send-btn" id="sendBtn" onclick="sendMessage()">➤</button>
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

        <!-- Report Result Section (shown after generation) -->
        <div id="reportResultSection" style="display:none;"></div>
    </div>
</div>

<footer class="footer" style="padding:3rem 4rem; text-align:center; border-top:1px solid var(--border); margin-top:4rem;">
    <p style="color:var(--text-dim); font-weight:700; font-size:0.85rem; text-transform:uppercase;">© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
</footer>

</main>

<style>
@keyframes bounce-aria {
    0%, 80%, 100% { transform: translateY(0); opacity: 0.5; }
    40% { transform: translateY(-8px); opacity: 1; }
}
</style>

<style>
@keyframes voice-pulse {
    0%, 100% { transform: scale(1);   opacity: 1; }
    50%       { transform: scale(1.6); opacity: 0.4; }
}
</style>

<script>
let conversationHistory = [];
let isWaiting = false;
const userInitial = "<?php echo strtoupper(substr($name, 0, 1)); ?>";

// ── Text-to-Speech (Aria speaks back) ──
let ttsEnabled = true;
let handsFreeEnabled = false;
let ariaVoice = null;

function loadVoices() {
    const voices = window.speechSynthesis.getVoices();
    ariaVoice = voices.find(v => /female|zira|samantha|victoria|karen|moira|fiona/i.test(v.name))
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
    const utt = new SpeechSynthesisUtterance(text.replace(/<[^>]*>/g, ''));
    utt.rate  = 0.92;
    utt.pitch = 1.1;
    utt.volume = 1;
    if (ariaVoice) utt.voice = ariaVoice;
    
    utt.onend = () => {
        if (handsFreeEnabled) {
            setTimeout(toggleVoice, 500);
        }
    };
    
    speechSynthesis.speak(utt);
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
    
    if (handsFreeEnabled && !recognizing) {
        toggleVoice();
    }
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
        finalTranscript = '';
        document.getElementById('voiceOverlay').style.display = 'flex';
        document.getElementById('interimText').textContent = '';
        document.getElementById('voiceStatus').textContent = 'Live';
        document.getElementById('voiceStatus').style.color = '#4338ca';
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
    };

    recognition.onerror = (e) => {
        console.warn('Speech error:', e.error);
        document.getElementById('voiceStatus').textContent = 'Error';
        document.getElementById('voiceStatus').style.color = '#dc2626';
        closeVoiceOverlay();
        if (e.error === 'not-allowed' || e.error === 'permission-denied') {
            alert('Microphone access was denied. Please click the camera/mic icon in your browser address bar to allow access, then try again.');
        } else if (e.error === 'no-speech') {
            // Silently close on no speech
        } else {
            alert('Speech recognition error: ' + e.error);
        }
    };

    recognition.onend = () => {
        const msg = finalTranscript.trim();
        if (msg) {
            document.getElementById('voiceStatus').textContent = 'Processing';
            document.getElementById('chatInput').value = msg;
            setTimeout(() => sendMessage(), 400);
        } else if (handsFreeEnabled && recognizing) {
            // Restart if no speech detected in hands-free mode
            setTimeout(toggleVoice, 500);
        }
        closeVoiceOverlay();
    };
}

async function toggleVoice() {
    if (!SpeechRecognition) {
        alert('Speech Recognition is NOT available in this browser. Please use Google Chrome or Microsoft Edge.');
        return;
    }

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert('Microphone access is not supported by your browser in this context (requires HTTPS or localhost).');
        return;
    }
    
    if (recognizing) {
        recognition.stop();
        return;
    }

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        stream.getTracks().forEach(track => track.stop());
        
        recognizing = true;
        recognition.start();
    } catch (err) {
        console.error('Microphone access error:', err);
        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
            alert('Microphone access denied. Please click the icon in your address bar and allow microphone access.');
        } else {
            alert('Could not access microphone. Please ensure your mic is plugged in.');
        }
    }
}

function stopVoice() {
    if (recognition && recognizing) recognition.stop();
}

function closeVoiceOverlay() {
    recognizing = false;
    document.getElementById('voiceOverlay').style.display = 'none';
    document.getElementById('micBtn').style.background = '#f1f5f9';
    document.getElementById('micBtn').style.color = 'var(--text-dim)';
}

function scrollToBottom() {
    const stream = document.getElementById('chatMessages');
    stream.scrollTop = stream.scrollHeight;
}

function appendMessage(role, content) {
    document.getElementById('chatWelcome').style.display = 'none';
    const stream = document.getElementById('chatMessages');
    const row = document.createElement('div');
    row.className = 'stream-row ' + (role === 'user' ? 'user-row' : 'aria-row');
    row.innerHTML = `
        <div style="width:40px;height:40px;border-radius:12px;background:${role==='user'?'var(--primary-glow)':'#f1f5f9'};display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:800;color:${role==='user'?'var(--primary)':'var(--text-dim)'};font-size:0.85rem;">
            ${role === 'user' ? userInitial : '✨'}
        </div>
        <div class="msg-bubble">${content.replace(/\n/g,'<br>')}</div>
    `;
    stream.insertBefore(row, document.getElementById('typingIndicator'));
    scrollToBottom();
}

async function sendMessage(textOverride) {
    const input = document.getElementById('chatInput');
    const msg = textOverride || input.value.trim();
    if (!msg || isWaiting) return;

    input.value = '';
    input.style.height = 'auto';
    appendMessage('user', msg);
    conversationHistory.push({ role: 'user', content: msg });

    isWaiting = true;
    document.getElementById('sendBtn').disabled = true;
    document.getElementById('typingIndicator').style.display = 'flex';
    scrollToBottom();

    // Update exchange counter
    const exchanges = conversationHistory.filter(m => m.role === 'user').length;
    document.getElementById('exchangeTag').textContent = exchanges + (exchanges === 1 ? ' Exchange' : ' Exchanges');
    document.getElementById('exchangeCount').textContent = conversationHistory.length + ' messages';

    if (exchanges >= 2) {
        const btn = document.getElementById('reportBtn');
        btn.disabled = false;
        btn.style.opacity = '1';
    }

    try {
        const response = await fetch('student_ai_chat_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ messages: conversationHistory })
        });

        if (!response.ok) throw new Error('HTTP ' + response.status);

        const data = await response.json();

        if (data.error) throw new Error(data.error);

        const reply = data.reply || "I'm here. Tell me more.";
        document.getElementById('typingIndicator').style.display = 'none';
        appendMessage('assistant', reply);
        conversationHistory.push({ role: 'assistant', content: reply });
        speakAria(reply); // Aria speaks the response aloud
    } catch (e) {
        document.getElementById('typingIndicator').style.display = 'none';
        appendMessage('assistant', "I'm having trouble connecting right now. Please refresh the page and try again, or check with a counselor directly.");
        console.error('Aria error:', e);
    } finally {
        isWaiting = false;
        document.getElementById('sendBtn').disabled = false;
    }
}

function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
}

function sendStarter(btn) {
    sendMessage(btn.textContent);
}

function clearChat() {
    if (conversationHistory.length > 0 && !confirm('Clear this conversation?')) return;
    location.reload();
}

// ── Report Modal ──
function openReportModal() {
    document.getElementById('reportFormSection').style.display = 'block';
    document.getElementById('reportResultSection').style.display = 'none';
    document.getElementById('reportModal').classList.add('open');
}

function closeReportModal() {
    document.getElementById('reportModal').classList.remove('open');
}

document.getElementById('reportModal').addEventListener('click', function(e) {
    if (e.target === this) closeReportModal();
});

async function submitReport() {
    const btn = document.getElementById('submitReportBtn');
    btn.textContent = 'Generating...';
    btn.disabled = true;

    // Build transcript from conversation history
    const transcript = conversationHistory.map(m =>
        (m.role === 'user' ? 'Student: ' : 'Aria: ') + m.content
    ).join('\n\n');

    const form = {
        mood_now: document.getElementById('rp_mood').value,
        sleep_quality: parseInt(document.getElementById('rp_sleep').value),
        stress_level: parseInt(document.getElementById('rp_stress').value),
        energy_level: 10 - parseInt(document.getElementById('rp_stress').value), // inverse heuristic
        main_concern: document.getElementById('rp_concern').value,
    };

    try {
        const response = await fetch('student_generate_preassessment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ transcript, form })
        });

        if (!response.ok) throw new Error('HTTP ' + response.status);
        const data = await response.json();

        if (!data.success) throw new Error(data.error || 'Unknown error');

        showReportResult(data.report, data.pre_id);
    } catch(e) {
        btn.textContent = 'Generate & Save Report';
        btn.disabled = false;
        alert('Could not generate report: ' + e.message + '. Please try again.');
    }
}

function showReportResult(report, preId) {
    const riskColors = {
        'Low': '#059669', 'Moderate': '#d97706', 'High': '#ea580c', 'Critical': '#dc2626'
    };
    const riskBg = { 'Low': '#ecfdf5', 'Moderate': '#fffbeb', 'High': '#fff7ed', 'Critical': '#fef2f2' };
    const risk = report.risk_level || 'Low';
    const moodIcon = { positive: '😊', neutral: '😐', low: '😔', concerning: '😰' };

    let concernsHtml = (report.key_concerns || []).map(c => `<li style="margin-bottom:0.5rem;">${c}</li>`).join('');
    let recsHtml = (report.recommendations || []).map(r => `<li style="margin-bottom:0.5rem;">${r}</li>`).join('');

    document.getElementById('reportResultSection').innerHTML = `
        <div style="text-align:center; margin-bottom:2.5rem;">
            <div style="font-weight:800; color:var(--primary); font-size:0.85rem; text-transform:uppercase; letter-spacing:0.12em; margin-bottom:0.75rem;">Report Generated ✓</div>
            <h2 style="font-family:'Outfit',sans-serif; font-size:1.75rem; font-weight:800; color:var(--primary-dark);">Your Wellness Report</h2>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">
            <div style="background:#f8fafc; border-radius:20px; padding:1.5rem; text-align:center;">
                <div style="font-size:2rem; margin-bottom:0.5rem;">${moodIcon[report.mood] || '😐'}</div>
                <div style="font-size:0.7rem; font-weight:800; color:var(--text-dim); text-transform:uppercase;">Mood</div>
                <div style="font-weight:800; color:var(--text); text-transform:capitalize;">${report.mood || '—'}</div>
            </div>
            <div style="background:${riskBg[risk]}; border-radius:20px; padding:1.5rem; text-align:center; border:1px solid ${riskColors[risk]}44;">
                <div style="font-size:2rem; margin-bottom:0.5rem;">${risk==='Critical'?'🚨':risk==='High'?'⚠️':risk==='Moderate'?'🟡':'✅'}</div>
                <div style="font-size:0.7rem; font-weight:800; color:var(--text-dim); text-transform:uppercase;">Risk Level</div>
                <div style="font-weight:800; color:${riskColors[risk]};">${risk}</div>
            </div>
        </div>

        <div style="background:#f8fafc; border-radius:20px; padding:1.75rem; margin-bottom:1.5rem;">
            <div style="font-weight:800; font-size:0.8rem; text-transform:uppercase; color:var(--text-dim); margin-bottom:0.75rem;">Summary</div>
            <p style="color:var(--text); line-height:1.75; font-weight:500;">${report.summary || 'No summary available.'}</p>
        </div>

        ${concernsHtml ? `<div style="background:white; border:1px solid var(--border); border-radius:20px; padding:1.75rem; margin-bottom:1.5rem;">
            <div style="font-weight:800; font-size:0.8rem; text-transform:uppercase; color:var(--text-dim); margin-bottom:0.75rem;">Key Concerns</div>
            <ul style="padding-left:1.5rem; color:var(--text); font-weight:500; line-height:1.8;">${concernsHtml}</ul>
        </div>` : ''}

        ${recsHtml ? `<div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:20px; padding:1.75rem; margin-bottom:2rem;">
            <div style="font-weight:800; font-size:0.8rem; text-transform:uppercase; color:#059669; margin-bottom:0.75rem;">Recommendations</div>
            <ul style="padding-left:1.5rem; color:#065f46; font-weight:500; line-height:1.8;">${recsHtml}</ul>
        </div>` : ''}

        <div style="display:flex; gap:1rem;">
            <a href="my_reports.php" class="no-print" style="flex:1; background:var(--primary); color:white; border:none; padding:1.25rem; border-radius:50px; font-weight:800; font-size:0.95rem; text-align:center; text-decoration:none; display:flex; align-items:center; justify-content:center;">View My Reports</a>
            <button onclick="window.print()" class="no-print" style="flex:1; background:#f8fafc; border:2px solid var(--border); padding:1.25rem; border-radius:50px; font-weight:800; font-size:0.95rem; color:var(--text); cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.5rem;">🖨️ Print Report</button>
            <button onclick="closeReportModal()" class="no-print" style="flex:1; background:white; border:2px solid var(--border); padding:1.25rem; border-radius:50px; font-weight:800; font-size:0.95rem; color:var(--text-dim); cursor:pointer;">Close</button>
        </div>
    `;

    document.getElementById('reportFormSection').style.display = 'none';
    document.getElementById('reportResultSection').style.display = 'block';
}
</script>

</body>
</html>
