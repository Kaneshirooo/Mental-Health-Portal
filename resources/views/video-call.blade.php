@extends('layouts.app')

@section('content')
<div class="video-call-page" style="height: calc(100vh - 8rem); display: flex; flex-direction: column; gap: 1.5rem;">
    
    <!-- Header / Clinical Status -->
    <div class="clinical-header glass" style="padding: 1.25rem 2rem; border-radius: 24px; display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--glass-border);">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div id="callStatusIndicator" style="width: 12px; height: 12px; border-radius: 50%; background: #f59e0b; box-shadow: 0 0 12px rgba(245,158,11,0.4); animation: pulse-status 2s infinite;"></div>
            <div>
                <h1 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.25rem; color: var(--text); margin: 0;">Clinical Session: #{{ $call->call_id }}</h1>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.1rem;">
                    <p id="callStatusLabel" style="font-size: 0.75rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; margin: 0;">Initializing Session...</p>
                    <div id="transcribingIndicator" style="display: none; align-items: center; gap: 0.35rem; background: rgba(16, 185, 129, 0.1); padding: 2px 8px; border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.2);">
                        <div class="line-wobble"></div>
                        <span style="font-size: 0.6rem; font-weight: 800; color: #10b981; text-transform: uppercase; letter-spacing: 0.05em;">LPT Live</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="session-info" style="display: flex; align-items: center; gap: 2rem;">
            <div class="participant" style="text-align: right;">
                <p style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: var(--text-dim); margin-bottom: 0.15rem;">{{ $user->user_type->value === 'student' ? 'Counselor' : 'Student' }}</p>
                <p id="remoteParticipantName" style="font-weight: 800; color: var(--text);">{{ $user->user_type->value === 'student' ? ($call->counselor->full_name ?? 'Waiting...') : $call->student->full_name }}</p>
            </div>
            <button onclick="confirmEndSession()" class="btn-end-session" style="background: #ef4444; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 16px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 0.6rem; transition: all 0.3s ease; box-shadow: 0 8px 20px rgba(239, 68, 68, 0.25);">
                <i class="ph-bold ph-phone-x" style="font-size: 1.2rem;"></i> Terminate Session
            </button>
        </div>
    </div>

    <!-- Main View Area -->
    <div id="mainViewContainer" style="flex: 1; position: relative; border-radius: 32px; overflow: hidden; background: #020617; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 40px 100px rgba(0,0,0,0.4);">
        
        <!-- Waiting Room View (Shown for pending students) -->
        <div id="waitingRoom" style="{{ $call->status === 'pending' && $user->user_type->value === 'student' ? 'display: flex;' : 'display: none;' }} position: absolute; inset: 0; z-index: 50; flex-direction: column; align-items: center; justify-content: center; background: radial-gradient(circle at center, #1e293b 0%, #020617 100%); text-align: center; padding: 2rem;">
            <div class="waiting-icon-container" style="position: relative; margin-bottom: 2.5rem;">
                <div style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; animation: pulse-ring 2s infinite;">
                    <i class="ph-bold ph-phone-call" style="font-size: 3.5rem; color: #10b981; animation: bounce-slow 2s infinite;"></i>
                </div>
            </div>
            <h2 id="waitingTitle" style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 2rem; color: white; margin-bottom: 0.75rem;">Connecting to a Professional</h2>
            <p id="waitingSubtext" style="color: #94a3b8; font-size: 1.05rem; max-width: 520px; line-height: 1.6; margin-bottom: 1.5rem;">Please stay on this page. We are notifying an available counselor of your emergency request.</p>
            
            <!-- Queue Position & Busy Badge -->
            <div id="queueBadgeContainer" style="display: none; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35); padding: 0.65rem 1.6rem; border-radius: 999px; margin-bottom: 1.75rem; box-shadow: 0 0 20px rgba(245, 158, 11, 0.2);">
                <span id="queueBadgeText" style="color: #fbbf24; font-weight: 800; font-size: 0.95rem; font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph-bold ph-hourglass-high" style="font-size: 1.2rem;"></i> In Queue — Position #1
                </span>
            </div>

            <div class="elapsed-time" style="background: rgba(255,255,255,0.05); padding: 0.75rem 1.5rem; border-radius: 999px; border: 1px solid rgba(255,255,255,0.1);">
                <span style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 0.25rem;">Time Elapsed</span>
                <span id="waitingTimer" style="font-family: 'Inter', sans-serif; font-weight: 700; font-size: 1.5rem; color: #10b981;">0:00</span>
            </div>
        </div>

        <!-- Video Grid View -->
        <div id="videoGrid" style="{{ $call->status === 'active' || $user->user_type->value === 'counselor' ? 'display: block;' : 'display: none;' }} width: 100%; height: 100%;">
            <!-- Remote Video (Large) -->
            <div class="remote-video-container" style="width: 100%; height: 100%; position: relative;">
                <video id="remoteVideo" autoplay playsinline style="width: 100%; height: 100%; object-fit: contain;"></video>
                
                <div id="remoteOverlay" style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(2,6,23,0.8); z-index: 5;">
                    <div style="text-align: center;">
                        <div style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.1); border-top-color: var(--primary); animation: spin 1s linear infinite; margin: 0 auto 1.5rem;"></div>
                        <p id="overlayMessage" style="color: white; font-weight: 600; font-size: 1.1rem;">Establishing Clinical Link...</p>
                    </div>
                </div>
            </div>

            <!-- Local Video (PiP) -->
            <div class="local-video-container" style="position: absolute; width: 280px; aspect-ratio: 16/10; bottom: 2rem; right: 2rem; border-radius: 24px; overflow: hidden; border: 2px solid rgba(255,255,255,0.2); box-shadow: 0 20px 40px rgba(0,0,0,0.5); z-index: 10; background: #111827;">
                <video id="localVideo" autoplay muted playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
                <div style="position: absolute; bottom: 0.75rem; left: 0.75rem; background: rgba(0,0,0,0.5); color: white; padding: 0.25rem 0.6rem; border-radius: 8px; font-size: 0.65rem; font-weight: 800; backdrop-filter: blur(4px);">YOU</div>
            </div>

            <!-- Live Subtitles / Caption Overlay -->
            <div id="liveSubtitlesOverlay" style="position: absolute; bottom: 6.5rem; left: 50%; transform: translateX(-50%); z-index: 25; pointer-events: none; max-width: 80%; display: none; flex-direction: column; align-items: center; gap: 0.5rem; text-align: center;">
                <div id="captionBox" style="background: rgba(15, 23, 42, 0.88); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.18); border-radius: 16px; padding: 0.7rem 1.5rem; color: white; font-family: 'Outfit', sans-serif; font-size: 1.05rem; font-weight: 600; box-shadow: 0 12px 35px rgba(0,0,0,0.6); transition: opacity 0.3s ease;">
                    <span id="captionSpeaker" style="font-size: 0.68rem; font-weight: 900; text-transform: uppercase; color: #10b981; display: block; margin-bottom: 3px; letter-spacing: 0.05em;"></span>
                    <span id="captionText"></span>
                </div>
            </div>

            <!-- Call Controls Overlay -->
            <div class="call-controls" style="position: absolute; bottom: 2.5rem; left: 50%; transform: translateX(-50%); display: flex; gap: 1.25rem; z-index: 20; background: rgba(15,23,42,0.6); backdrop-filter: blur(20px); padding: 1rem; border-radius: 24px; border: 1px solid rgba(255,255,255,0.1);">
                <button id="micBtn" onclick="toggleMute()" class="control-btn" style="position: relative; width: 54px; height: 54px; border-radius: 18px; border: none; background: white; color: #0f172a; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                    <i class="ph-bold ph-microphone" style="font-size: 1.4rem;"></i>
                    <span id="micActiveIndicator" title="Microphone Voice Level" style="position: absolute; top: 6px; right: 6px; width: 10px; height: 10px; border-radius: 50%; background: #10b981; opacity: 0.5; transition: transform 0.1s ease, opacity 0.1s ease;"></span>
                </button>
                <button id="camBtn" onclick="toggleCam()" class="control-btn" style="width: 54px; height: 54px; border-radius: 18px; border: none; background: white; color: #0f172a; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                    <i class="ph-bold ph-video-camera" style="font-size: 1.4rem;"></i>
                </button>
                <button id="speechBtn"
                    onclick="toggleSpeechCapture()"
                    class="control-btn" title="Click to start speaking — click again to stop"
                    style="width: 54px; height: 54px; border-radius: 18px; border: none; background: rgba(255,255,255,0.1); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; user-select: none;">
                    <i class="ph-bold ph-microphone-stage" style="font-size: 1.4rem;"></i>
                </button>
                <div style="width: 1px; background: rgba(255,255,255,0.1); margin: 0 0.5rem;"></div>
                <button id="chatToggleBtn" onclick="toggleChat()" class="control-btn" style="position: relative; width: 54px; height: 54px; border-radius: 18px; border: none; background: rgba(255,255,255,0.1); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                    <i class="ph-bold ph-chat-circle" style="font-size: 1.4rem;"></i>
                    <span id="unreadChatBadge" style="display: none; position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; border-radius: 999px; min-width: 22px; height: 22px; font-size: 0.7rem; font-weight: 900; padding: 0 5px; align-items: center; justify-content: center; border: 2px solid #0f172a; box-shadow: 0 0 12px rgba(239, 68, 68, 0.7); animation: pulse-unread 1.5s infinite;">0</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Sidebar Chat -->
<div id="callChatSidebar" style="position: fixed; top: 0; right: -400px; width: 400px; height: 100vh; background: var(--surface-solid); border-left: 1px solid var(--border); z-index: 10000; transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1); display: flex; flex-direction: column;">
    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
        <h3 style="font-family: 'Outfit', sans-serif; font-weight: 800; margin: 0;">Session Chat</h3>
        <button onclick="toggleChat()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-dim);"><i class="ph ph-x"></i></button>
    </div>
    <div id="chatMessages" style="flex: 1; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;"></div>
    <div style="padding: 1.5rem; border-top: 1px solid var(--border);">
        <form id="chatForm" onsubmit="sendChatMessage(event)" style="display: flex; gap: 0.75rem;">
            <input type="text" id="chatInput" placeholder="Type clinical note..." style="flex: 1; background: var(--surface-2); border: 1px solid var(--border); padding: 0.85rem 1.25rem; border-radius: 14px; color: var(--text);">
            <button type="submit" style="background: var(--primary); color: white; border: none; width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <i class="ph-bold ph-paper-plane-right"></i>
            </button>
        </form>
    </div>
</div>

<style>
@keyframes pulse-status { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.2); opacity: 0.7; } }
@keyframes pulse-unread { 0%, 100% { transform: scale(1); box-shadow: 0 0 12px rgba(239, 68, 68, 0.7); } 50% { transform: scale(1.18); box-shadow: 0 0 22px rgba(239, 68, 68, 1); } }
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes pulse-ring { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); } 70% { box-shadow: 0 0 0 30px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }
@keyframes bounce-slow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
.control-btn:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
.control-btn.off { background: #ef4444 !important; color: white !important; }
.message-wrap { max-width: 85%; display: flex; flex-direction: column; gap: 0.35rem; }
.message-meta { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-dim); }
.message-bubble { padding: 0.75rem 1.25rem; border-radius: 18px; font-size: 0.9rem; line-height: 1.4; white-space: pre-wrap; }
.msg-me { align-self: flex-end; background: var(--primary); color: white; border-bottom-right-radius: 4px; }
.msg-them { align-self: flex-start; background: var(--surface-2); color: var(--text); border-bottom-left-radius: 4px; border: 1px solid var(--border); }
.role-badge { font-size: 0.6rem; padding: 2px 6px; border-radius: 6px; font-weight: 800; text-transform: uppercase; margin-right: 6px; display: inline-block; }
.role-student { background: #dcfce7; color: #166534; }
.role-counselor { background: #dbeafe; color: #1e40af; }
.role-admin { background: #fef9c3; color: #854d0e; }
.role-me { background: rgba(255,255,255,0.2) !important; color: white !important; }
.msg-speech { font-style: italic; background: rgba(255,255,255,0.03) !important; border: 1px dashed var(--border) !important; color: var(--text-dim) !important; border-radius: 12px !important; }
.msg-speech .speech-tag { font-weight: 800; color: var(--primary); font-size: 0.6rem; text-transform: uppercase; margin-bottom: 4px; display: block; }
.msg-speech-live { font-style: italic; background: rgba(99,102,241,0.08) !important; border: 1px dashed rgba(99,102,241,0.4) !important; color: var(--text-dim) !important; border-radius: 12px !important; opacity: 0.75; animation: live-pulse 1.4s ease-in-out infinite; }
.msg-speech-live .speech-tag { font-weight: 800; color: #818cf8; font-size: 0.6rem; text-transform: uppercase; margin-bottom: 4px; display: block; }
@keyframes live-pulse { 0%, 100% { opacity: 0.65; } 50% { opacity: 1; } }
.line-wobble { width: 12px; height: 12px; display: flex; align-items: center; justify-content: space-between; }
.line-wobble::before, .line-wobble::after { content: ''; width: 2px; height: 100%; background: #10b981; animation: wobble 1s infinite ease-in-out; }
.line-wobble::after { animation-delay: 0.5s; }
@keyframes wobble { 0%, 100% { height: 4px; } 50% { height: 12px; } }
</style>

@push('scripts')
<script>
    const CALL_ID = "{{ $call->call_id }}";
    const CURRENT_USER_ID = String("{{ $user->user_id }}");
    const CALL_STUDENT_ID = String("{{ $call->student_id }}");
    const CALL_COUNSELOR_ID = String("{{ $call->counselor_id }}");
    // USER_TYPE comes directly from PHP's authenticated user — always authoritative
    const USER_TYPE = "{{ strtolower($user->user_type->value ?? (is_string($user->user_type) ? $user->user_type : $user->user_type->name ?? 'student')) }}";
    const IS_COUNSELOR = (USER_TYPE === 'counselor' || USER_TYPE === 'admin');
    const IS_STUDENT = (USER_TYPE === 'student');
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const RTC_CONFIG = {
        // Multiple STUN servers for faster, more reliable ICE candidate gathering
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun2.l.google.com:19302' },
            { urls: 'stun:stun3.l.google.com:19302' },
            { urls: 'stun:stun4.l.google.com:19302' },
            { urls: 'stun:global.stun.twilio.com:3478' },
        ],
        // Pre-gather ICE candidates before offer/answer — shaves 300-800ms off connection time
        iceCandidatePoolSize: 10,
        bundlePolicy: 'max-bundle',       // Bundle audio+video into one transport
        rtcpMuxPolicy: 'require',          // Multiplex RTCP, reduces port count
    };

    let localStream = null;
    let peerConnection = null;
    let micOn = true;
    let camOn = true;
    let lastChatMsgId = 0;
    let lastSignalMsgId = 0;
    let statusPoller = null;
    let chatPoller = null;
    let signalPoller = null;
    let chatPollInFlight = false;
    let signalPollInFlight = false;
    let waitingTimerSec = 0;
    let waitingTimerInt = null;
    let speechRecognition = null;
    let speechActive = false;
    // Track already-rendered message IDs to prevent duplicates
    const renderedMsgIds = new Set();
    let pendingOptimisticMsg = null;

    // WebRTC optimizations
    let iceCandidateQueue = [];
    let iceSendTimeout = null;
    let signalPollInterval = 250; // Faster initial signal polling (250ms)
    let pendingRemoteIceCandidates = [];
    let mediaInitPromise = null;

    function agentDebugLog(hypothesisId, location, message, data = {}, runId = 'initial') {
        // #region agent log
        fetch('http://127.0.0.1:7562/ingest/38cc8233-db14-4f39-87ee-19f5a468ae9e',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'d2d10b'},body:JSON.stringify({sessionId:'d2d10b',runId,hypothesisId,location,message,data,timestamp:Date.now()})}).catch(()=>{});
        // #endregion
    }

    document.addEventListener('DOMContentLoaded', async () => {
        if (IS_STUDENT) {
            startWaitingTimer();
        }

        // Click on remote video overlay forces video play if blocked by autoplay policy
        const overlayEl = document.getElementById('remoteOverlay');
        if (overlayEl) {
            overlayEl.addEventListener('click', () => {
                const remoteVid = document.getElementById('remoteVideo');
                if (remoteVid) remoteVid.play().catch(() => {});
            });
        }
        
        // Start real-time chat & signal polling immediately (do not block on local media permissions)
        startPolling();
        
        // Init media (mic + camera)
        mediaInitPromise = initLocalMedia();
        await mediaInitPromise;
        
        // If already active or counselor/admin joining, start call flow immediately
        if ("{{ $call->status }}" === 'active' || IS_COUNSELOR) {
            await startCallFlow();
            setTimeout(() => {
                sendSignal({ signal_type: 'peer_joined' });
            }, 100);
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            pollChatSession();
            pollSignalSession();
        }
    });

    function startWaitingTimer() {
        waitingTimerInt = setInterval(() => {
            waitingTimerSec++;
            const m = Math.floor(waitingTimerSec / 60), s = waitingTimerSec % 60;
            document.getElementById('waitingTimer').textContent = `${m}:${s.toString().padStart(2, '0')}`;
        }, 1000);
    }

    async function initLocalMedia() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: {
                    echoCancellation:   { ideal: true },
                    noiseSuppression:   { ideal: true },
                    autoGainControl:    { ideal: true },
                }
            });
            localStream = stream;
            
            // Ensure audio tracks are explicitly enabled
            localStream.getAudioTracks().forEach(t => t.enabled = micOn);

            const localVid = document.getElementById('localVideo');
            if (localVid) localVid.srcObject = localStream;

            // If peer connection already exists, add tracks
            if (peerConnection) {
                localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
            }

            // Setup mic audio level meter for visual confirmation
            setupMicAudioMeter(localStream);

        } catch (err) {
            console.error('Media permission error:', err);
            const errMsg = err.name === 'NotAllowedError'
                ? 'Microphone/camera permission was denied. Please allow access in your browser settings and reload.'
                : err.name === 'NotFoundError'
                ? 'No microphone or camera detected. Please connect a device.'
                : 'Could not access media: ' + err.message;

            if (window.App) App.toast({ type: 'error', title: 'Media Access Error', message: errMsg });

            // Fallback: Try audio-only if video failed
            try {
                const audioStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                localStream = audioStream;
                localStream.getAudioTracks().forEach(t => t.enabled = micOn);
                if (peerConnection) {
                    localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
                }
                setupMicAudioMeter(localStream);
                if (window.App) App.toast({ type: 'info', title: 'Audio Mode Active', message: 'Camera failed, connected with Microphone only.' });
            } catch (e2) {
                console.error('Audio-only fallback error:', e2);
                if (window.App) App.toast({ type: 'error', title: 'Microphone Failed', message: 'Could not access microphone device.' });
            }
        }
    }

    // Visual Audio Level Meter to prove microphone is capturing live sound
    let audioContext = null;
    function setupMicAudioMeter(stream) {
        try {
            const audioTrack = stream.getAudioTracks()[0];
            if (!audioTrack) return;

            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) return;
            
            audioContext = new AudioContextClass();
            const source = audioContext.createMediaStreamSource(stream);
            const analyser = audioContext.createAnalyser();
            analyser.fftSize = 64;
            source.connect(analyser);

            const dataArray = new Uint8Array(analyser.frequencyBinCount);
            const micIndicator = document.getElementById('micActiveIndicator');

            function checkAudioLevel() {
                if (!localStream || !micOn) {
                    if (micIndicator) micIndicator.style.opacity = '0.3';
                    requestAnimationFrame(checkAudioLevel);
                    return;
                }
                analyser.getByteFrequencyData(dataArray);
                let sum = 0;
                for (let i = 0; i < dataArray.length; i++) sum += dataArray[i];
                let average = sum / dataArray.length;
                
                if (micIndicator) {
                    if (average > 10) {
                        micIndicator.style.opacity = '1';
                        micIndicator.style.transform = 'scale(1.2)';
                    } else {
                        micIndicator.style.opacity = '0.5';
                        micIndicator.style.transform = 'scale(1)';
                    }
                }
                requestAnimationFrame(checkAudioLevel);
            }
            checkAudioLevel();
        } catch (e) {
            console.log('Audio meter setup info:', e);
        }
    }

    async function startCallFlow() {
        if (mediaInitPromise) await mediaInitPromise;

        document.getElementById('waitingRoom').style.display = 'none';
        document.getElementById('videoGrid').style.display = 'block';
        document.getElementById('callStatusIndicator').style.background = '#10b981';
        document.getElementById('callStatusLabel').textContent = 'Secure WebRTC Audio/Video Link Active';
        
        if (waitingTimerInt) clearInterval(waitingTimerInt);

        createPeerConnection();
        
        if (IS_STUDENT) {
            try {
                const offer = await peerConnection.createOffer({
                    offerToReceiveAudio: true,
                    offerToReceiveVideo: true
                });
                await peerConnection.setLocalDescription(offer);
                sendSignal({ signal_type: 'offer', sdp: offer.sdp });
            } catch (e) {
                console.error("Create offer error:", e);
            }
        }

        // Initialize Speech Recognition for both student and counselor
        initSpeechRecognition();
    }

    function createPeerConnection() {
        if (peerConnection) return;
        peerConnection = new RTCPeerConnection(RTC_CONFIG);
        
        if (localStream) {
            localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, localStream);
            });
        }

        peerConnection.ontrack = (e) => {
            console.log("Remote WebRTC track received:", e.track.kind);
            const remoteVideo = document.getElementById('remoteVideo');
            if (remoteVideo) {
                remoteVideo.srcObject = e.streams[0];
                remoteVideo.muted = false; // Ensure remote audio is UNMUTED
                remoteVideo.volume = 1.0;
                remoteVideo.play().catch(err => console.log("Remote play error:", err));
            }
            const overlay = document.getElementById('remoteOverlay');
            if (overlay) overlay.style.display = 'none';
            adjustSignalPollingSpeed(3000); // Slow down polling once connected
        };

        peerConnection.oniceconnectionstatechange = () => {
            if (['connected', 'completed'].includes(peerConnection?.iceConnectionState)) {
                const overlay = document.getElementById('remoteOverlay');
                if (overlay) overlay.style.display = 'none';
                adjustSignalPollingSpeed(3000); // Slow down polling once connected
            }
        };

        peerConnection.onconnectionstatechange = () => {
            if (['connected', 'completed'].includes(peerConnection?.connectionState)) {
                const overlay = document.getElementById('remoteOverlay');
                if (overlay) overlay.style.display = 'none';
                adjustSignalPollingSpeed(3000); // Slow down polling once connected
            }
        };

        peerConnection.onicecandidate = (e) => {
            if (e.candidate) enqueueIceCandidate(e.candidate);
        };
    }

    async function handleIncomingSignal(raw) {
        if (mediaInitPromise) await mediaInitPromise;

        let payload = null;
        try { payload = JSON.parse(raw); } catch (e) { return; }
        if (!payload || payload.kind !== 'webrtc_signal') return;

        if (payload.signal_type === 'hangup') {
            handleRemoteHangup();
            return;
        }

        createPeerConnection();

        if (payload.signal_type === 'peer_joined') {
            if (IS_STUDENT) {
                if (document.getElementById('waitingRoom')?.style.display !== 'none') {
                    await startCallFlow();
                } else if (peerConnection) {
                    try {
                        const offer = await peerConnection.createOffer();
                        await peerConnection.setLocalDescription(offer);
                        sendSignal({ signal_type: 'offer', sdp: offer.sdp });
                    } catch (e) {
                        console.error('Peer joined re-offer error:', e);
                    }
                }
            }
            return;
        }

        if (payload.signal_type === 'offer' && !IS_STUDENT) {
            try {
                await peerConnection.setRemoteDescription(new RTCSessionDescription({ type: 'offer', sdp: payload.sdp }));
                await flushPendingIceCandidates();
                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);
                sendSignal({ signal_type: 'answer', sdp: answer.sdp });
            } catch (e) {
                console.error("Offer handle error:", e);
            }
        } else if (payload.signal_type === 'answer' && IS_STUDENT) {
            try {
                await peerConnection.setRemoteDescription(new RTCSessionDescription({ type: 'answer', sdp: payload.sdp }));
                await flushPendingIceCandidates();
            } catch (e) {
                console.error("Answer handle error:", e);
            }
        } else if (payload.signal_type === 'ice' && payload.candidate) {
            await processIncomingIceCandidate(payload.candidate);
        } else if (payload.signal_type === 'ice_candidates' && Array.isArray(payload.candidates)) {
            for (const cand of payload.candidates) {
                await processIncomingIceCandidate(cand);
            }
        }
    }

    async function processIncomingIceCandidate(candidateData) {
        if (!candidateData) return;
        if (peerConnection && peerConnection.remoteDescription && peerConnection.remoteDescription.type) {
            try {
                await peerConnection.addIceCandidate(new RTCIceCandidate(candidateData));
            } catch (e) {
                console.log("Add ICE candidate info:", e);
            }
        } else {
            pendingRemoteIceCandidates.push(candidateData);
        }
    }

    async function flushPendingIceCandidates() {
        if (!peerConnection || !peerConnection.remoteDescription) return;
        while (pendingRemoteIceCandidates.length > 0) {
            const cand = pendingRemoteIceCandidates.shift();
            try {
                await peerConnection.addIceCandidate(new RTCIceCandidate(cand));
            } catch (e) {
                console.log("Flush ICE error:", e);
            }
        }
    }

    function isWebrtcSignalText(text) {
        const raw = String(text || '');
        if (!raw.includes('webrtc_signal')) return false;
        try {
            const payload = JSON.parse(raw);
            return payload && payload.kind === 'webrtc_signal';
        } catch (e) {
            return true;
        }
    }

    async function sendSignal(sig) {
        try {
            return await fetch(`/video-call/${CALL_ID}/message`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ message: JSON.stringify({ kind: 'webrtc_signal', ...sig }) })
            });
        } catch (e) {}
    }

    async function postCallMessage(messageText) {
        const response = await fetch(`/video-call/${CALL_ID}/message`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ message: messageText })
        });
        setTimeout(pollUnifiedSession, 10);
        return response;
    }

    let pollInFlight = false;
    let mainPoller = null;
    let currentPollInterval = 500;

    async function pollUnifiedSession() {
        if (pollInFlight) return;
        pollInFlight = true;

        try {
            const res = await fetch(`/video-call/${CALL_ID}/messages?after_id=${lastChatMsgId}&channel=all`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;

            const data = await res.json();
            const messages = Array.isArray(data.messages) ? data.messages : [];

            for (const m of messages) {
                const msgId = Number(m.message_id);
                if (!Number.isFinite(msgId)) continue;
                if (msgId > lastChatMsgId) lastChatMsgId = msgId;

                const text = String(m.message_text || '');
                if (isWebrtcSignalText(text)) {
                    // Skip WebRTC signals sent by OURSELF!
                    if (String(m.sender_id) === CURRENT_USER_ID) continue;
                    await handleIncomingSignal(text);
                } else {
                    if (!renderedMsgIds.has(msgId)) {
                        appendChatMessage(m);
                    }
                }
            }

            if (data.call_status) {
                if (data.call_status === 'ended' || data.call_status === 'declined') {
                    handleRemoteHangup(data.call_status === 'declined');
                } else if (data.call_status === 'active' && document.getElementById('waitingRoom')?.style.display !== 'none') {
                    const waitingRoom = document.getElementById('waitingRoom');
                    const videoGrid = document.getElementById('videoGrid');
                    if (waitingRoom && videoGrid) {
                        waitingRoom.style.display = 'none';
                        videoGrid.style.display = 'block';
                    }
                    const remoteNameEl = document.getElementById('remoteParticipantName');
                    if (remoteNameEl) {
                        remoteNameEl.textContent = data.counselor_name || data.student_name || 'Participant';
                    }
                    await startCallFlow();
                }
            }
        } catch (e) {
        } finally {
            pollInFlight = false;
        }
    }

    function enqueueIceCandidate(candidate) {
        iceCandidateQueue.push(candidate);
        if (!iceSendTimeout) {
            iceSendTimeout = setTimeout(() => {
                iceSendTimeout = null;
                sendBufferedIceCandidates();
            }, 100);
        }
    }

    async function sendBufferedIceCandidates() {
        if (iceCandidateQueue.length === 0) return;
        const candidates = [...iceCandidateQueue];
        iceCandidateQueue = [];
        await sendSignal({ signal_type: 'ice_candidates', candidates: candidates });
    }

    function adjustSignalPollingSpeed(speedMs) {
        if (currentPollInterval === speedMs) return;
        currentPollInterval = speedMs;
        if (mainPoller) clearInterval(mainPoller);
        mainPoller = setInterval(pollUnifiedSession, speedMs);
    }

    function startPolling() {
        if (mainPoller) clearInterval(mainPoller);
        if (statusPoller) clearInterval(statusPoller);

        pollUnifiedSession();
        mainPoller = setInterval(pollUnifiedSession, 500);

        statusPoller = setInterval(async () => {
            if (!IS_STUDENT) return;
            const url = `/student/emergency-call/${CALL_ID}/status`;
            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                if (data.status === 'missed') {
                    const queueContainer = document.getElementById('queueBadgeContainer');
                    const waitingTitle   = document.getElementById('waitingTitle');
                    const waitingSubtext = document.getElementById('waitingSubtext');

                    if (queueContainer) queueContainer.style.display = 'none';
                    if (waitingTitle) {
                        waitingTitle.textContent = `Call Unanswered`;
                        waitingTitle.style.color = '#f87171';
                    }
                    if (waitingSubtext) {
                        waitingSubtext.innerHTML = `Your call request went unanswered as no counselor was available to respond.<br><br><div style="display:flex; gap:10px; justify-content:center; margin-top:14px;"><a href="/student/appointments" style="padding:10px 18px; border-radius:14px; background:#10b981; color:white; font-weight:800; text-decoration:none; font-size:0.9rem;">Schedule Appointment</a> <a href="/emergency" style="padding:10px 18px; border-radius:14px; background:rgba(239, 68, 68, 0.2); color:#ef4444; border:1px solid rgba(239, 68, 68, 0.4); font-weight:800; text-decoration:none; font-size:0.9rem;">Emergency Hotlines</a></div>`;
                    }
                } else if (data.status === 'pending') {
                    const queueContainer = document.getElementById('queueBadgeContainer');
                    const queueBadgeText = document.getElementById('queueBadgeText');
                    const waitingTitle   = document.getElementById('waitingTitle');
                    const waitingSubtext = document.getElementById('waitingSubtext');

                    if (data.counselors_online === false) {
                        if (queueContainer) queueContainer.style.display = 'inline-block';
                        if (queueBadgeText) {
                            queueBadgeText.innerHTML = `<i class="ph-bold ph-warning-octagon" style="font-size: 1.2rem; color: #ef4444;"></i> No Available Counselor Online`;
                            queueBadgeText.style.color = '#ef4444';
                        }
                        if (waitingTitle) {
                            waitingTitle.textContent = `No Counselor Currently Online`;
                            waitingTitle.style.color = '#f87171';
                        }
                        if (waitingSubtext) {
                            waitingSubtext.textContent = `There are currently no counselors online or using the system to receive emergency calls. Please schedule an appointment or contact emergency hotlines if urgent.`;
                        }
                    } else if (data.counselors_busy || data.queue_position > 1) {
                        if (queueContainer) queueContainer.style.display = 'inline-block';
                        if (queueBadgeText) {
                            queueBadgeText.innerHTML = `<i class="ph-bold ph-hourglass-high" style="font-size: 1.2rem;"></i> All Counselors Busy — Queue Position #${data.queue_position}`;
                            queueBadgeText.style.color = '#fbbf24';
                        }
                        if (waitingTitle) {
                            waitingTitle.textContent = `In Queue (#${data.queue_position} of ${data.total_queued})`;
                            waitingTitle.style.color = 'white';
                        }
                        if (waitingSubtext) {
                            waitingSubtext.textContent = `All counselors are currently in active sessions with other students. Please hold line — you will be connected automatically as soon as a counselor finishes.`;
                        }
                    } else if (data.queue_position === 1) {
                        if (queueContainer) queueContainer.style.display = 'none';
                        if (waitingTitle) {
                            waitingTitle.textContent = `Counselor Available — Connecting...`;
                            waitingTitle.style.color = 'white';
                        }
                        if (waitingSubtext) {
                            waitingSubtext.textContent = `A counselor is available! Notifying counselor of your emergency call request...`;
                        }
                    }
                }
            } catch (e) {}
        }, 3000);
    }

    let unreadChatCount = 0;
    function updateUnreadBadge() {
        const badge = document.getElementById('unreadChatBadge');
        if (!badge) return;
        if (unreadChatCount > 0) {
            badge.textContent = unreadChatCount > 99 ? '99+' : unreadChatCount;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    let captionTimeout = null;
    function showLiveCaption(speakerName, text, isSpeech = false) {
        const overlay = document.getElementById('liveSubtitlesOverlay');
        const speakerEl = document.getElementById('captionSpeaker');
        const textEl = document.getElementById('captionText');
        if (!overlay || !speakerEl || !textEl) return;

        speakerEl.textContent = speakerName;
        speakerEl.style.color = isSpeech ? '#10b981' : '#6366f1';
        textEl.textContent = text;
        overlay.style.display = 'flex';
        overlay.style.opacity = '1';
        if (captionTimeout) clearTimeout(captionTimeout);
        captionTimeout = setTimeout(() => {
            overlay.style.opacity = '0';
            setTimeout(() => { overlay.style.display = 'none'; }, 300);
        }, 4000);
    }

    function appendChatMessage(m) {
        const container = document.getElementById('chatMessages');
        if (!container) return;

        const isMe = String(m.sender_id) === CURRENT_USER_ID;
        const msgId = m.message_id ? Number(m.message_id) : null;

        if (msgId && renderedMsgIds.has(msgId)) {
            return;
        }

        if (m._tempId) {
            const existingOpt = document.getElementById(m._tempId);
            if (existingOpt) return;
        } else if (msgId && isMe) {
            const optWraps = container.querySelectorAll('.message-wrap[id^="opt_"]');
            for (const wrap of optWraps) {
                const rawText = wrap.dataset.rawText || wrap.querySelector('.message-bubble')?.textContent?.trim();
                if (rawText === String(m.message_text || '').trim()) {
                    wrap.removeAttribute('id');
                    wrap.dataset.msgId = msgId;
                    renderedMsgIds.add(msgId);
                    if (msgId > lastChatMsgId) lastChatMsgId = msgId;
                    return;
                }
            }
        }

        if (msgId) {
            renderedMsgIds.add(msgId);
            if (msgId > lastChatMsgId) lastChatMsgId = msgId;
        }

        const msgText = String(m.message_text || '');
        const isSpeechMsg = msgText.startsWith('[Speech');

        // ---- BULLETPROOF 1-ON-1 ROLE DETERMINATION ----
        // In an Emergency Video Call, there are exactly 2 participants: Counselor & Student.
        // My messages (isMe = true) are MY role (Counselor if IS_COUNSELOR, else Student).
        // Remote messages (isMe = false) are THE OTHER party's role (Student if IS_COUNSELOR, else Counselor).
        let roleLabel, roleClass, displayName;

        if (isMe) {
            roleLabel = IS_COUNSELOR ? 'Counselor' : 'Student';
            roleClass = IS_COUNSELOR ? 'role-counselor' : 'role-student';
            displayName = 'YOU';
        } else {
            roleLabel = IS_COUNSELOR ? 'Student' : 'Counselor';
            roleClass = IS_COUNSELOR ? 'role-student' : 'role-counselor';
            const remoteStudentName = "{{ $call->student->full_name }}";
            const remoteCounselorName = "{{ $call->counselor->full_name ?? 'Counselor' }}";
            displayName = m.sender_name || (IS_COUNSELOR ? remoteStudentName : remoteCounselorName);
        }

        // Show live floating caption overlay on top of video
        if (!m._tempId && !msgText.includes('webrtc_signal')) {
            const cleanText = isSpeechMsg ? msgText.replace(/^\[Speech:(?:Live:)?\s*[\w\s]*\]\s*/i, '').trim() : msgText;
            const captionHeader = isSpeechMsg 
                ? `🎤 ${displayName.toUpperCase()} (${roleLabel.toUpperCase()})` 
                : (isMe ? `YOU (${roleLabel.toUpperCase()})` : `${displayName.toUpperCase()} (${roleLabel.toUpperCase()})`);
            showLiveCaption(captionHeader, cleanText, isSpeechMsg);
        }

        // Increment unread count if message comes from remote user while chat sidebar is closed
        const sidebar = document.getElementById('callChatSidebar');
        const isClosed = sidebar && sidebar.style.right !== '0px';
        if (!isMe && isClosed && !m._tempId) {
            unreadChatCount++;
            updateUnreadBadge();
        }

        const wrap = document.createElement('div');
        wrap.className = 'message-wrap';
        wrap.style.alignSelf = isMe ? 'flex-end' : 'flex-start';
        wrap.dataset.rawText = msgText.trim();
        if (m._tempId) wrap.id = m._tempId;
        if (msgId) wrap.dataset.msgId = msgId;

        const meta = document.createElement('div');
        meta.className = 'message-meta';
        meta.style.display = 'flex';
        meta.style.alignItems = 'center';
        meta.style.marginBottom = '2px';
        meta.style.gap = '4px';
        meta.style.justifyContent = isMe ? 'flex-end' : 'flex-start';

        const badge = document.createElement('span');
        badge.className = `role-badge ${isMe ? 'role-me' : roleClass}`;
        badge.textContent = roleLabel;

        const nameSpan = document.createElement('span');
        nameSpan.style.fontWeight = '600';
        nameSpan.style.fontSize = '0.7rem';
        nameSpan.textContent = displayName;

        const timeSpan = document.createElement('span');
        timeSpan.style.opacity = '0.65';
        timeSpan.style.fontSize = '0.6rem';
        timeSpan.textContent = m.created_at || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        meta.appendChild(badge);
        meta.appendChild(nameSpan);
        meta.appendChild(timeSpan);

        const bubble = document.createElement('div');
        bubble.className = `message-bubble ${isMe ? 'msg-me' : 'msg-them'} ${isSpeechMsg ? 'msg-speech' : ''}`;
        
        if (isSpeechMsg) {
            const isLive = msgText.startsWith('[Speech:Live');
            const speakerTag = roleLabel.toUpperCase();
            const speechTag = document.createElement('span');
            speechTag.className = 'speech-tag';
            if (isLive) {
                bubble.className = `message-bubble ${isMe ? 'msg-me' : 'msg-them'} msg-speech-live`;
                speechTag.innerHTML = `🎤 ${speakerTag} (SPEAKING...)`;
            } else {
                speechTag.innerHTML = `🎤 ${speakerTag} VOICE TRANSCRIPTION`;
            }
            bubble.appendChild(speechTag);
            const cleanSpeech = msgText.replace(/^\[Speech:(?:Live:)?\s*[\w\s]*\]\s*/i, '').trim();
            bubble.appendChild(document.createTextNode(' ' + cleanSpeech));
        } else {
            bubble.textContent = msgText;
        }

        wrap.appendChild(meta);
        wrap.appendChild(bubble);
        container.appendChild(wrap);
        container.scrollTop = container.scrollHeight;
    }

    async function sendChatMessage(e) {
        e.preventDefault();
        const input = document.getElementById('chatInput');
        const msg = input.value.trim();
        if (!msg) return;

        const submitBtn = document.getElementById('chatForm')?.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;
        input.disabled = true;
        input.value = '';

        const sidebar = document.getElementById('callChatSidebar');
        if (sidebar && sidebar.style.right !== '0px') {
            sidebar.style.right = '0px';
            unreadChatCount = 0;
            updateUnreadBadge();
        }

        const tempId = `opt_${Date.now()}`;
        const optimisticData = {
            _tempId: tempId,
            sender_id: "{{ $user->user_id }}",
            sender_type: USER_TYPE,
            sender_name: "{{ $user->full_name }}",
            message_text: msg,
            created_at: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        };
        appendChatMessage(optimisticData);

        try {
            const res = await postCallMessage(msg);
            const serverData = await res.json().catch(() => ({}));
            if (!res.ok) {
                console.error('Chat send failed:', res.status, serverData);
                if (window.App) App.toast({ type: 'error', title: 'Message Failed', message: `Could not send: ${serverData.error || res.status}` });
            } else if (serverData.message_id) {
                renderedMsgIds.add(Number(serverData.message_id));
                const bubble = document.getElementById(tempId);
                if (bubble) bubble.dataset.msgId = serverData.message_id;
            }
        } catch (err) {
            console.error('Chat network error:', err);
            if (window.App) App.toast({ type: 'error', title: 'Network Error', message: 'Chat message could not be sent. Check your connection.' });
        } finally {
            if (submitBtn) submitBtn.disabled = false;
            input.disabled = false;
            input.focus();
        }
    }

    let speechRestartTimer = null;
    let speechIsRunning = false;
    let speechInterimBubbleId = null;
    let speechPendingText = '';
    let speechAutoCommitTimer = null;

    async function commitSpeech(textToCommit) {
        if (!textToCommit || !textToCommit.trim()) return;
        const cleanText = textToCommit.trim();
        speechPendingText = '';

        if (speechAutoCommitTimer) {
            clearTimeout(speechAutoCommitTimer);
            speechAutoCommitTimer = null;
        }

        if (speechInterimBubbleId) {
            const el = document.getElementById(speechInterimBubbleId);
            if (el) el.remove();
            speechInterimBubbleId = null;
        }

        const speakerRole = IS_COUNSELOR ? 'COUNSELOR' : 'STUDENT';
        const speakerLabel = IS_COUNSELOR ? 'Counselor' : 'Student';
        const tagged = `[Speech:${speakerLabel}] ${cleanText}`;
        const tempId = `opt_speech_${Date.now()}`;

        showLiveCaption(`🎤 YOU (${speakerRole})`, cleanText, true);

        try {
            appendChatMessage({
                _tempId: tempId,
                sender_id: CURRENT_USER_ID,
                sender_type: USER_TYPE,
                sender_name: "{{ $user->full_name }}",
                message_text: tagged,
                created_at: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            });

            const res = await postCallMessage(tagged);
            const serverData = await res.json().catch(() => ({}));
            if (serverData.message_id) {
                renderedMsgIds.add(Number(serverData.message_id));
                const bubble = document.getElementById(tempId);
                if (bubble) bubble.dataset.msgId = serverData.message_id;
            }
        } catch (err) {
            console.error('Speech transcript save failed', err);
        }
    }

    function safeStartSpeech() {
        if (!speechActive || !speechRecognition || !micOn) return;
        if (speechIsRunning) return;

        try {
            console.log('[SpeechRecognition] Starting engine...');
            speechRecognition.start();
            speechIsRunning = true;
        } catch (e) {
            console.warn('[SpeechRecognition] safeStartSpeech notice:', e?.message || e);
            speechIsRunning = false;
            if (speechRestartTimer) clearTimeout(speechRestartTimer);
            speechRestartTimer = setTimeout(() => {
                if (speechActive && speechRecognition && micOn && !speechIsRunning) {
                    try { speechRecognition.start(); speechIsRunning = true; } catch (err) {}
                }
            }, 400);
        }
    }

    function initSpeechRecognition() {
        if (speechRecognition) return;
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            console.error('[SpeechRecognition] Web Speech API not supported');
            return;
        }

        speechRecognition = new SpeechRecognition();
        speechRecognition.continuous = true;
        speechRecognition.interimResults = true;
        speechRecognition.maxAlternatives = 1;
        speechRecognition.lang = 'en-US';

        speechRecognition.onstart = () => {
            speechIsRunning = true;
            console.log('[SpeechRecognition] Engine STARTED successfully');
        };

        speechRecognition.onresult = async (event) => {
            if (!speechActive || !micOn || document.hidden) return;

            let interimText = '';
            let finalTranscript = '';

            for (let i = event.resultIndex; i < event.results.length; i++) {
                const result = event.results[i];
                const text = result[0]?.transcript?.trim() || '';
                if (result.isFinal) {
                    finalTranscript += (finalTranscript ? ' ' : '') + text;
                } else {
                    interimText += (interimText ? ' ' : '') + text;
                }
            }

            const speakerRole = IS_COUNSELOR ? 'COUNSELOR' : 'STUDENT';
            const speakerLabel = IS_COUNSELOR ? 'Counselor' : 'Student';

            if (interimText) {
                speechPendingText = interimText;
                showLiveCaption(`🎤 YOU (${speakerRole})`, interimText, true);

                if (!speechInterimBubbleId) {
                    speechInterimBubbleId = `speech_interim_${Date.now()}`;
                    appendChatMessage({
                        _tempId: speechInterimBubbleId,
                        sender_id: CURRENT_USER_ID,
                        sender_type: USER_TYPE,
                        sender_name: "{{ $user->full_name }}",
                        message_text: `[Speech:Live:${speakerLabel}] ${interimText}`,
                        created_at: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    });
                } else {
                    const el = document.getElementById(speechInterimBubbleId);
                    if (el) {
                        const bubbleEl = el.querySelector('.message-bubble');
                        if (bubbleEl) {
                            for (const n of bubbleEl.childNodes) {
                                if (n.nodeType === Node.TEXT_NODE) { n.textContent = ` ${interimText}`; break; }
                            }
                        }
                    }
                }

                if (speechAutoCommitTimer) clearTimeout(speechAutoCommitTimer);
                speechAutoCommitTimer = setTimeout(() => {
                    if (speechPendingText) {
                        commitSpeech(speechPendingText);
                        speechPendingText = '';
                    }
                }, 1500);
            }

            if (finalTranscript) {
                if (speechAutoCommitTimer) clearTimeout(speechAutoCommitTimer);
                commitSpeech(finalTranscript);
                speechPendingText = '';
            }
        };

        speechRecognition.onend = () => {
            speechIsRunning = false;
            console.log('[SpeechRecognition] Engine ended. speechActive:', speechActive, 'micOn:', micOn);
            if (speechActive && micOn && !document.hidden) {
                if (speechRestartTimer) clearTimeout(speechRestartTimer);
                speechRestartTimer = setTimeout(() => {
                    speechRecognition = null;
                    initSpeechRecognition();
                    safeStartSpeech();
                }, 300);
            }
        };

        speechRecognition.onerror = (event) => {
            speechIsRunning = false;
            console.warn('[SpeechRecognition] Error:', event.error);
            if (speechActive && micOn && event.error !== 'aborted' && !document.hidden) {
                if (speechRestartTimer) clearTimeout(speechRestartTimer);
                speechRestartTimer = setTimeout(() => {
                    speechRecognition = null;
                    initSpeechRecognition();
                    safeStartSpeech();
                }, 500);
            }
        };
    }

    function toggleSpeechCapture() {
        speechActive = !speechActive;
        const btn = document.getElementById('speechBtn');
        const indicator = document.getElementById('transcribingIndicator');

        if (speechActive) {
            if (!micOn) toggleMute();

            speechPendingText = '';
            speechInterimBubbleId = null;

            if (speechRecognition) {
                try { speechRecognition.stop(); } catch (_) {}
                speechRecognition = null;
            }
            initSpeechRecognition();
            safeStartSpeech();

            if (btn) {
                btn.style.setProperty('background', '#10b981', 'important');
                btn.style.setProperty('color', '#ffffff', 'important');
                btn.innerHTML = '<i class="ph-bold ph-waveform" style="font-size:1.4rem"></i>';
                btn.title = 'Speech transcription active — click again to stop';
            }
            if (indicator) indicator.style.display = 'flex';
            if (window.App) App.toast({ type: 'info', title: 'Speech Capture ON', message: 'Speech transcription active (Green).' });
        } else {
            speechActive = false;

            if (speechRecognition) {
                try { speechRecognition.stop(); } catch (_) {}
            }
            speechIsRunning = false;

            if (speechPendingText && speechPendingText.trim()) {
                commitSpeech(speechPendingText.trim());
                speechPendingText = '';
            }

            if (speechInterimBubbleId) {
                const el = document.getElementById(speechInterimBubbleId);
                if (el) el.remove();
                speechInterimBubbleId = null;
            }

            if (btn) {
                btn.style.removeProperty('background');
                btn.style.removeProperty('color');
                btn.innerHTML = '<i class="ph-bold ph-microphone-stage" style="font-size:1.4rem"></i>';
                btn.title = 'Click to start speech transcription';
            }
            if (indicator) indicator.style.display = 'none';
            if (window.App) App.toast({ type: 'info', title: 'Speech Capture OFF', message: 'Speech transcription stopped.' });
        }
    }

    // PTT stubs (unused but keep to avoid reference errors)
    function pttStart(e) {}
    function pttStop(e) {}
    function enableSpeechCaptureByDefault() {}

    function toggleMute() {
        micOn = !micOn;
        if (localStream) localStream.getAudioTracks().forEach(t => t.enabled = micOn);
        const btn = document.getElementById('micBtn');
        btn.classList.toggle('off', !micOn);
        btn.innerHTML = micOn ? '<i class="ph-bold ph-microphone"></i>' : '<i class="ph-bold ph-microphone-slash"></i>';
    }

    function toggleCam() {
        camOn = !camOn;
        if (localStream) localStream.getVideoTracks().forEach(t => t.enabled = camOn);
        const btn = document.getElementById('camBtn');
        btn.classList.toggle('off', !camOn);
        btn.innerHTML = camOn ? '<i class="ph-bold ph-video-camera"></i>' : '<i class="ph-bold ph-video-camera-slash"></i>';
    }

    function toggleChat() {
        const sidebar = document.getElementById('callChatSidebar');
        const opening = sidebar.style.right !== '0px';
        sidebar.style.right = opening ? '0px' : '-400px';
        if (opening) {
            unreadChatCount = 0;
            updateUnreadBadge();
            const container = document.getElementById('chatMessages');
            if (container) container.scrollTop = container.scrollHeight;
        }
    }

    function handleRemoteHangup(declined = false) {
        if (statusPoller) clearInterval(statusPoller);
        if (mainPoller) clearInterval(mainPoller);
        if (waitingTimerInt) clearInterval(waitingTimerInt);
        speechActive = false;
        if (speechRecognition) {
            try { speechRecognition.stop(); } catch (e) {}
        }
        
        // Stop camera/mic tracks immediately for fast cleanup
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
        }
        if (peerConnection) {
            try { peerConnection.close(); } catch (e) {}
        }

        const msg = declined ? 'No counselors are currently available.' : 'The session has been terminated by participant.';
        if (window.App) App.toast({ type: 'info', title: 'Session Ended', message: msg });
        
        setTimeout(() => {
            window.location.href = IS_STUDENT ? "{{ route('student.dashboard') }}" : "{{ route('counselor.dashboard') }}";
        }, 500); // Fast 500ms redirect for both users
    }

    async function confirmEndSession() {
        const btn = document.querySelector('.btn-end-session');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="ph-bold ph-circle-notch ph-spin"></i> Terminating...';
        }

        // 1. Immediately kill local media tracks & hardware connections
        speechActive = false;
        if (speechRecognition) {
            try { speechRecognition.stop(); } catch (e) {}
        }
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
        }
        if (peerConnection) {
            try { peerConnection.close(); } catch (e) {}
        }

        // 2. Dispatch termination signals with short timeout so UI never freezes
        const url = `/video-call/${CALL_ID}/terminate`;
        sendSignal({ signal_type: 'hangup' });

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 800);

        try {
            await fetch(url, { 
                method: 'POST', 
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                signal: controller.signal
            });
        } catch (e) {
        } finally {
            clearTimeout(timeoutId);
            window.location.href = IS_STUDENT ? "{{ route('student.dashboard') }}" : "{{ route('counselor.dashboard') }}";
        }
    }
</script>
@endpush
@endsection
