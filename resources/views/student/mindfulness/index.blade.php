@extends('layouts.app')

@push('styles')
<style>
@media (max-width: 768px) {
    .mindfulness-container {
        padding: 0.75rem !important;
    }
    .mindfulness-title {
        font-size: 2.25rem !important;
        margin-bottom: 0.5rem !important;
    }
    .glass-card {
        padding: 1rem !important;
        border-radius: 20px !important;
        margin-bottom: 1.25rem !important;
    }
    #circle {
        width: 130px !important;
        height: 130px !important;
    }
    #statusLabel {
        font-size: 1.1rem !important;
    }
    #instruction {
        font-size: 0.9rem !important;
        margin-bottom: 1.25rem !important;
        height: auto !important;
    }
}
</style>
@endpush

@section('content')
<div class="p-8 max-w-5xl mx-auto mindfulness-container">
    <!-- Header -->
    <div class="text-center mb-16">
        <h1 class="text-6xl font-black text-white tracking-tighter italic uppercase mb-4 mindfulness-title">Mindfulness</h1>
        <p class="text-gray-500 font-medium italic">"Deep stillness starts with one intentional breath."</p>
    </div>

    <!-- Aria Recommendation -->
    <div id="ariaRecCard" class="hidden glass-card p-8 mb-12 flex items-center gap-8 border-l-4 border-l-emerald-500 animate-fade-in" style="background: var(--surface-solid); border: 2px solid var(--border); border-left: 6px solid #10b981; border-radius: 32px; box-shadow: var(--shadow-lg);">
        <div class="w-16 h-16 bg-emerald-600/10 border border-emerald-500/20 rounded-3xl flex items-center justify-center text-3xl">✨</div>
        <div class="flex-1">
            <h3 style="font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.15em; color: #10b981; margin-bottom: 0.4rem;">Aria's Insight</h3>
            <p id="ariaRecText" style="color: var(--text-dim); font-weight: 600; font-style: italic; line-height: 1.6;"></p>
        </div>
    </div>

    <!-- Breathing Exercise (Premium Clinical Protocol) -->
    <div class="glass-card p-12 mb-12 text-center relative overflow-hidden" style="background: var(--surface-solid); border: 2px solid var(--border); border-radius: 48px; box-shadow: var(--shadow-lg);">
        <div class="absolute inset-0 bg-gradient-to-b from-emerald-600/5 to-transparent"></div>
        
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.8rem; font-weight: 900; color: var(--text); margin-bottom: 0.5rem; letter-spacing: -0.02em;">4-7-8 Respiratory Protocol</h2>
        <p style="color: var(--text-muted); font-weight: 600; font-size: 1rem; margin-bottom: 3rem;">Neutralize stress by recalibrating your autonomic nervous system.</p>

        <div class="flex justify-center mb-12 relative">
            <div id="circle" class="w-48 h-48 border-[6px] border-emerald-500/10 rounded-full flex flex-col items-center justify-center transition-all duration-[4s] bg-white/5 backdrop-blur-3xl shadow-2xl" style="border: 4px solid var(--border);">
                <span id="statusLabel" style="font-size: 1.8rem; font-weight: 900; color: #10b981; font-style: italic; text-transform: uppercase; letter-spacing: 0.05em;">Ready</span>
            </div>
        </div>

        <p id="instruction" style="font-size: 1.2rem; font-weight: 800; color: var(--text-dim); height: 2rem; margin-bottom: 3.5rem; font-style: italic;">Enter the zone of stillness.</p>
        
        <button id="startBtn" onclick="toggleBreathing()" class="btn-primary" style="padding: 1.25rem 3.5rem; border-radius: 50px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.08em; font-size: 0.8rem; background: #10b981; color: white; border: none; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);">
            Initialize Session
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        <!-- Grounding (Interactive Protocol) -->
        <div class="glass-card p-10 group hover:border-emerald-500/30 transition-all" style="background: var(--surface-solid); border: 2px solid var(--border); border-radius: 40px; box-shadow: var(--shadow-lg);">
            <div class="flex justify-between items-start mb-8">
                <div class="w-14 h-14 bg-emerald-600/10 rounded-2xl flex items-center justify-center text-3xl">⚓</div>
                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500 bg-emerald-500/10 px-4 py-2 rounded-full border border-emerald-500/20">Tactical Grounding</span>
            </div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 900; color: var(--text); margin-bottom: 0.5rem;">5-4-3-2-1 Sensory Protocol</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500; margin-bottom: 2rem;">Anchor your neurological state through physical verification.</p>
            
            <div id="groundingSteps" class="bg-white/5 border border-white/10 rounded-[2rem] p-8 mb-8 min-h-[220px] flex flex-col items-center justify-center text-center transition-all bg-emerald-50/10" style="border: 2px dashed var(--border);">
                <div id="groundingContent" style="color: var(--text-dim); font-weight: 700; font-style: italic; font-size: 1.1rem;">Initialize grounding protocol to stabilize awareness...</div>
                <div id="groundingInputs" style="display:none; width: 100%; margin-top: 1.5rem;">
                    <input type="text" id="groundingField" placeholder="Record what you sense..." style="width: 100%; padding: 1rem 1.5rem; border-radius: 16px; border: 1.5px solid var(--border); font-weight: 600; font-size: 0.95rem; background: white; color: var(--text); outline: none;">
                </div>
            </div>
            
            <button onclick="nextGroundingStep()" id="groundingBtn" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-5 rounded-2xl transition-all shadow-xl shadow-emerald-600/20 uppercase tracking-widest text-[10px]">
                Activate Protocol
            </button>
        </div>

        <!-- Daily Affirmations -->
        <div class="glass-card p-10 group hover:border-amber-500/30 transition-all" style="background: var(--surface-solid); border: 2px solid var(--border); border-radius: 40px; box-shadow: var(--shadow-lg);">
            <div class="flex justify-between items-start mb-8">
                <div class="w-14 h-14 bg-amber-500/10 rounded-2xl flex items-center justify-center text-3xl">🌟</div>
                <span class="text-[10px] font-black uppercase tracking-widest text-amber-600 bg-amber-500/10 px-4 py-2 rounded-full border border-amber-500/20">Daily Affirmation</span>
            </div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 900; color: var(--text); margin-bottom: 0.5rem;">Words of Strength</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500; margin-bottom: 2rem;">A reminder to be kind to yourself. Read it slowly.</p>

            <div id="affirmationBox" style="border: 2px solid rgba(245,158,11,0.25); background: rgba(245,158,11,0.04); border-radius: 2rem; padding: 2rem 2.25rem; margin-bottom: 1.5rem; min-height: 160px; display: flex; align-items: center; justify-content: center; text-align: center; position: relative; overflow: hidden; transition: all 0.4s ease;">
                <div id="affirmationGlow" style="position:absolute;inset:0;background:radial-gradient(ellipse at 50% 50%,rgba(245,158,11,0.08),transparent 70%);pointer-events:none;"></div>
                <p id="affirmationText" style="font-family:'Outfit',sans-serif; font-size:1.15rem; font-weight:800; color:var(--text); line-height:1.7; letter-spacing:-0.01em; position:relative; z-index:1;">Tap below to receive your affirmation.</p>
            </div>

            <div id="affirmationDots" style="display:flex;justify-content:center;gap:6px;margin-bottom:1.5rem;">
            </div>

            <div style="display:flex;gap:0.75rem;">
                <button onclick="nextAffirmation()" id="affirmationBtn" class="font-black py-5 rounded-2xl transition-all uppercase tracking-widest text-[10px]" style="flex:1;background:linear-gradient(135deg,#f59e0b,#d97706);color:white;border:none;cursor:pointer;box-shadow:0 8px 24px rgba(245,158,11,0.25);">
                    ✨ New Affirmation
                </button>
                <button onclick="holdThisThought()" id="holdBtn" title="Hold this thought" style="width:56px;height:56px;border-radius:18px;border:2px solid rgba(245,158,11,0.3);background:rgba(245,158,11,0.05);cursor:pointer;font-size:1.3rem;flex-shrink:0;transition:all 0.3s ease;" aria-label="Hold this thought">
                    🫁
                </button>
            </div>
        </div>
    </div>

    <!-- Affirmation Focus Overlay -->
    <div id="affirmationFocus" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.75);backdrop-filter:blur(12px);align-items:center;justify-content:center;padding:2rem;" onclick="closeFocus()">
        <div style="max-width:600px;text-align:center;" onclick="event.stopPropagation()">
            <div style="font-size:3rem;margin-bottom:1.5rem;">🌟</div>
            <p id="focusText" style="font-family:'Outfit',sans-serif;font-size:2rem;font-weight:900;color:white;line-height:1.5;letter-spacing:-0.02em;margin-bottom:2rem;"></p>
            <p style="color:rgba(255,255,255,0.5);font-size:0.85rem;font-weight:600;">Breathe in. Read it again. Let it settle.</p>
            <button onclick="closeFocus()" style="margin-top:2.5rem;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:white;padding:0.85rem 2.5rem;border-radius:50px;font-weight:800;font-size:0.85rem;cursor:pointer;">I've got this ✓</button>
        </div>
    </div>

    <!-- AI Session -->
    <div class="glass-card p-12 bg-gradient-to-br from-indigo-900/10 to-transparent">
        <div class="flex flex-col lg:flex-row gap-12 items-start">
            <div class="lg:w-1/3">
                <div class="w-16 h-16 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-3xl mb-8 flex items-center justify-center shadow-xl shadow-blue-500/20">
                    <i class="ph ph-sparkle text-white text-3xl"></i>
                </div>
                <h2 class="text-2xl font-bold mb-4">Aria AI Session</h2>
                <p class="text-gray-500 leading-relaxed font-medium mb-8">Generate a personalized 1-minute mindfulness script based on your current state.</p>
                
                <div class="grid grid-cols-2 gap-3 mb-8">
                    @foreach(['stressed', 'anxious', 'sad', 'tired', 'neutral', 'happy'] as $m)
                        <button onclick="setAiMood('{{ $m }}', this)" class="mood-btn bg-white/5 border border-white/10 p-3 rounded-xl text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-white hover:bg-white/10 transition-all">
                            {{ $m }}
                        </button>
                    @endforeach
                </div>
                <button id="aiGenBtn" onclick="generateAiSession()" disabled class="w-full bg-blue-600 hover:bg-blue-500 disabled:opacity-30 text-white font-black py-5 rounded-2xl transition-all shadow-xl shadow-blue-600/20 uppercase tracking-widest text-[10px]">
                    Generate Script ✨
                </button>
            </div>
            
            <div class="lg:w-2/3 w-full h-full min-h-[300px] bg-white/[0.02] border border-dashed border-white/20 rounded-[3rem] p-10 flex items-center justify-center text-center relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(59,130,246,0.05),transparent_70%)]"></div>
                <p id="aiScriptText" class="text-gray-500 italic font-medium leading-relaxed relative z-10">
                    Select your current affective state to initialize AI generation protocol.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
/* Default styles (Light Mode) */
.glass-card {
    background: rgba(0, 0, 0, 0.02);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 3.5rem;
    color: #1a202c;
}
.mood-btn {
    color: #4a5568 !important;
    background: rgba(0, 0, 0, 0.03) !important;
}
#bodyScanContent, #groundingContent, #aiScriptText {
    color: #4a5568 !important;
}
#statusLabel {
    color: #2563eb !important;
}
#instruction {
    color: #718096 !important;
}
h1.text-white, h2.text-white {
    color: #111827 !important;
}

/* Dark Mode Overrides */
.glass-card button, .p-8 button {
    background: #3b82f6 !important;
    color: white !important;
}
.dark-mode .glass-card button, .dark-mode .p-8 button {
    background: rgba(255, 255, 255, 0.05) !important;
    color: white !important;
}
.dark-mode h1.text-white, .dark-mode h2.text-white {
    color: white !important;
}
.dark-mode .mood-btn {
    color: #9ca3af !important;
    background: rgba(255, 255, 255, 0.05) !important;
}
.dark-mode #bodyScanContent, .dark-mode #groundingContent, .dark-mode #aiScriptText {
    color: #9ca3af !important;
}
.dark-mode #statusLabel {
    color: #3b82f6 !important;
}
.dark-mode #instruction {
    color: #6b7280 !important;
}

.mood-btn.selected {
    border-color: #3b82f6 !important;
    color: #3b82f6 !important;
    background: rgba(59, 130, 246, 0.1) !important;
}
#circle.expanding {
    width: 25rem;
    height: 25rem;
    border-color: rgba(59, 130, 246, 0.5);
    box-shadow: 0 0 100px rgba(59, 130, 246, 0.15);
}
@keyframes fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
<script>
    let isRunning = false;
    let timer;
    let cycleTimeouts = [];

    function toggleBreathing() {
        if (isRunning) stopBreathing();
        else startBreathing();
    }

    function startBreathing() {
        const startBtn = document.getElementById('startBtn');
        if (!startBtn) return;
        
        isRunning = true;
        startBtn.textContent = 'End Session Protocol';
        startBtn.classList.add('bg-red-500/20', 'text-red-400');
        
        cycle();
        timer = setInterval(cycle, 12000); 
    }

    function stopBreathing() {
        const startBtn = document.getElementById('startBtn');
        const circle = document.getElementById('circle');
        const statusLabel = document.getElementById('statusLabel');
        const instruct = document.getElementById('instruction');

        isRunning = false;
        if (timer) clearInterval(timer);
        cycleTimeouts.forEach(t => clearTimeout(t));
        cycleTimeouts = [];

        if (startBtn) {
            startBtn.textContent = 'Initialize Session';
            startBtn.classList.remove('bg-red-500/20', 'text-red-400');
        }
        if (circle) circle.classList.remove('expanding');
        if (statusLabel) statusLabel.textContent = 'Ready';
        if (instruct) instruct.textContent = 'Peak stillness attained.';
    }

    function cycle() {
        if (!isRunning) return;
        const circle = document.getElementById('circle');
        const statusLabel = document.getElementById('statusLabel');
        const instruct = document.getElementById('instruction');

        if (circle) circle.classList.add('expanding');
        if (statusLabel) statusLabel.textContent = 'Inhale';
        if (instruct) instruct.textContent = 'Oxidize your bloodstream slowly...';

        cycleTimeouts.push(setTimeout(() => {
            if (!isRunning) return;
            if (statusLabel) statusLabel.textContent = 'Hold';
            if (instruct) instruct.textContent = 'Stabilize the internal pressure.';
            
            cycleTimeouts.push(setTimeout(() => {
                if (!isRunning) return;
                if (circle) circle.classList.remove('expanding');
                if (statusLabel) statusLabel.textContent = 'Exhale';
                if (instruct) instruct.textContent = 'Purge all biological tension.';
            }, 4000));
        }, 4000));
    }

    let selectedMood = '';
    function setAiMood(m, el) {
        document.querySelectorAll('.mood-btn').forEach(b => b.classList.remove('selected'));
        el.classList.add('selected');
        selectedMood = m;
        const aiGenBtn = document.getElementById('aiGenBtn');
        if (aiGenBtn) aiGenBtn.disabled = false;
    }

    async function generateAiSession() {
        const btn = document.getElementById('aiGenBtn');
        const text = document.getElementById('aiScriptText');
        if (!btn || !text) return;

        btn.disabled = true;
        btn.textContent = 'Synthesizing...';
        
        try {
            const res = await fetch('{{ route("student.mindfulness.ai-session") }}', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ mood: selectedMood })
            });
            const data = await res.json();
            if(data.success) {
                text.innerHTML = data.script.replace(/\n/g, '<br>');
                text.className = 'text-white font-medium leading-relaxed italic animate-fade-in';
            } else {
                text.textContent = 'Aria is busy right now, but remember: just take one deep breath. ' + (data.error || '');
            }
        } catch(e) { 
            console.error(e); 
            text.textContent = 'Connection to the Zen garden was interrupted. Please try again.';
        }
        finally {
            btn.disabled = false;
            btn.textContent = 'Generate New Sequence ✨';
        }
    }

    /** Interactive Tactical Grounding **/
    let groundingStep = 0;
    let itemSubStep = 0;
    const gSteps = [
        { icon: '👀', t: 'Things You See', c: '#10b981', placeholder: 'Record an item you see...', count: 5 },
        { icon: '🖐️', t: 'Things You Feel', c: '#0d9488', placeholder: 'Record an item you feel...', count: 4 },
        { icon: '👂', t: 'Things You Hear', c: '#14b8a6', placeholder: 'Record an item you hear...', count: 3 },
        { icon: '👃', t: 'Things You Smell', c: '#0f766e', placeholder: 'Record an item you smell...', count: 2 },
        { icon: '👅', t: 'Thing You Taste', c: '#115e59', placeholder: 'Record an item you taste...', count: 1 }
    ];

    function nextGroundingStep() {
        const content = document.getElementById('groundingContent');
        const btn = document.getElementById('groundingBtn');
        const inputs = document.getElementById('groundingInputs');
        const field = document.getElementById('groundingField');
        if (!content || !btn) return;

        // Initialize protocol
        if (groundingStep === 0 && itemSubStep === 0 && inputs.style.display === 'none') {
            gsap.to(content, { opacity: 0, y: -20, duration: 0.3, onComplete: () => {
                inputs.style.display = 'block';
                content.style.opacity = '1';
                content.style.fontSize = '0.9rem';
                gsap.from(inputs, { y: 10, opacity: 0, duration: 0.4 });
                updateGroundingUI();
            }});
            return;
        }

        // Validate current input
        if (field.value.trim() === '') {
            gsap.to(field, { x: 10, duration: 0.1, yoyo: true, repeat: 3 });
            return;
        }

        // Add item chip effect (visual only)
        const s = gSteps[groundingStep];
        itemSubStep++;
        
        if (itemSubStep >= s.count) {
            itemSubStep = 0;
            groundingStep++;
            
            if (groundingStep >= gSteps.length) {
                // Finalize
                gsap.to(inputs, { opacity: 0, y: 10, duration: 0.3, onComplete: () => {
                    inputs.style.display = 'none';
                    content.innerHTML = `<div class="text-6xl mb-4">✨</div><div style="font-size: 1.25rem; font-weight: 800; color: var(--primary);">System Calibrated</div><div class="text-sm text-gray-500 mt-2">Awareness normalized. Heart rate stabilized.</div>`;
                    gsap.from(content, { scale: 0.9, opacity: 0, duration: 0.6, ease: "back.out(1.7)" });
                    btn.textContent = 'Restart Sensory Protocol';
                    groundingStep = 0;
                    itemSubStep = 0;
                }});
                return;
            }
        }

        field.value = '';
        updateGroundingUI();
        gsap.from(field, { scale: 0.98, duration: 0.3, ease: "expo.out" });
    }

    function updateGroundingUI() {
        const content = document.getElementById('groundingContent');
        const btn = document.getElementById('groundingBtn');
        const field = document.getElementById('groundingField');
        
        if (groundingStep >= gSteps.length) return;

        const s = gSteps[groundingStep];
        const remaining = s.count - itemSubStep;
        
        content.innerHTML = `
            <div class="text-5xl mb-4">${s.icon}</div>
            <div class="text-[11px] font-black uppercase tracking-[0.2em] mb-1" style="color: ${s.c}">${s.count} ${s.t}</div>
            <div class="flex justify-center gap-1 mb-4">
                ${Array.from({length: s.count}).map((_, i) => 
                    `<div style="width: 20px; height: 4px; border-radius: 2px; background: ${i < itemSubStep ? s.c : 'var(--border)'}; transition: all 0.4s;"></div>`
                ).join('')}
            </div>
        `;
        
        field.placeholder = s.placeholder;
        field.focus();
        btn.textContent = `Verify Item (${itemSubStep + 1}/${s.count})`;
        
        gsap.from(content, { y: 20, opacity: 0, duration: 0.5, ease: "back.out(1.7)" });
    }

    /** Daily Affirmations **/
    const affirmations = [
        "I am allowed to take up space and ask for help.",
        "My feelings are valid, even when I can't explain them.",
        "I don't have to have it all figured out right now.",
        "Progress, no matter how small, is still moving forward.",
        "I am more than my grades, my productivity, or my setbacks.",
        "Rest is not a reward — it is a right.",
        "I am doing the best I can with what I have today.",
        "My worth is not measured by how much I accomplish.",
        "Being kind to myself is not weakness — it is wisdom.",
        "It's okay to not be okay. The sun rises anyway.",
        "I have survived every difficult day so far. That is real strength.",
        "I deserve support, connection, and peace.",
    ];
    let affirmationIndex = -1;

    function buildDots() {
        const container = document.getElementById('affirmationDots');
        if (!container) return;
        container.innerHTML = affirmations.map((_, i) =>
            `<div class="aff-dot" data-i="${i}" style="width:7px;height:7px;border-radius:50%;background:rgba(245,158,11,0.25);transition:all 0.3s ease;cursor:pointer;" onclick="goToAffirmation(${i})"></div>`
        ).join('');
    }

    function updateDots() {
        document.querySelectorAll('.aff-dot').forEach((d, i) => {
            d.style.background = i === affirmationIndex
                ? '#f59e0b'
                : 'rgba(245,158,11,0.25)';
            d.style.transform = i === affirmationIndex ? 'scale(1.4)' : 'scale(1)';
        });
    }

    function goToAffirmation(index) {
        affirmationIndex = index;
        renderAffirmation();
    }

    function nextAffirmation() {
        affirmationIndex = (affirmationIndex + 1) % affirmations.length;
        renderAffirmation();
    }

    function renderAffirmation() {
        const text = document.getElementById('affirmationText');
        const box  = document.getElementById('affirmationBox');
        if (!text || !box) return;

        gsap.to(text, { opacity: 0, y: -12, scale: 0.96, duration: 0.22, onComplete: () => {
            text.textContent = affirmations[affirmationIndex];
            gsap.to(text, { opacity: 1, y: 0, scale: 1, duration: 0.45, ease: 'back.out(1.4)' });
        }});

        // Pulse the glow
        gsap.fromTo('#affirmationGlow', { opacity: 0 }, { opacity: 1, duration: 0.6, yoyo: true, repeat: 1 });
        gsap.to(box, { borderColor: 'rgba(245,158,11,0.5)', duration: 0.3, yoyo: true, repeat: 1 });

        updateDots();
    }

    function holdThisThought() {
        const current = affirmations[affirmationIndex];
        if (!current) { nextAffirmation(); return; }
        const focus = document.getElementById('affirmationFocus');
        const focusText = document.getElementById('focusText');
        if (!focus || !focusText) return;
        focusText.textContent = current;
        focus.style.display = 'flex';
        gsap.from('#affirmationFocus > div', { y: 30, opacity: 0, scale: 0.92, duration: 0.5, ease: 'back.out(1.4)' });
    }

    function closeFocus() {
        const focus = document.getElementById('affirmationFocus');
        if (focus) gsap.to(focus, { opacity: 0, duration: 0.3, onComplete: () => { focus.style.display = 'none'; focus.style.opacity = 1; } });
    }

    document.addEventListener('DOMContentLoaded', () => { buildDots(); nextAffirmation(); });

    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const res = await fetch('{{ route("student.mindfulness.recommendation") }}');
            const data = await res.json();
            if(data.success) {
                const recCard = document.getElementById('ariaRecCard');
                const recText = document.getElementById('ariaRecText');
                if (recCard) recCard.classList.remove('hidden');
                if (recText) recText.textContent = data.recommendation;
            }
        } catch(e) {}
    });
</script>
@endsection
