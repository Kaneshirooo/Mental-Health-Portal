@extends('layouts.app')

@section('content')
<div class="p-8 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="text-center mb-16">
        <h1 class="text-6xl font-black text-white tracking-tighter italic uppercase mb-4">Mindfulness</h1>
        <p class="text-gray-500 font-medium italic">"Deep stillness starts with one intentional breath."</p>
    </div>

    <!-- Aria Recommendation -->
    <div id="ariaRecCard" class="hidden glass-card p-8 mb-12 flex items-center gap-8 border-l-4 border-l-blue-500 animate-fade-in">
        <div class="w-16 h-16 bg-blue-600/20 border border-blue-500/20 rounded-3xl flex items-center justify-center text-3xl">🪄</div>
        <div class="flex-1">
            <h3 class="text-xs font-black uppercase tracking-widest text-blue-400 mb-2">Aria's Insight</h3>
            <p id="ariaRecText" class="text-gray-300 font-medium italic"></p>
        </div>
    </div>

    <!-- Breathing Exercise -->
    <div class="glass-card p-12 mb-12 text-center relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-600/5 to-transparent"></div>
        
        <h2 class="text-2xl font-bold text-white mb-2 relative z-10">4-7-8 Breathing Technique</h2>
        <p class="text-gray-500 mb-12 relative z-10">Neutralize stress and recalibrate your nervous system.</p>

        <div class="flex justify-center mb-12 relative">
            <div id="circle" class="w-40 h-40 border-8 border-blue-600/20 rounded-full flex flex-col items-center justify-center transition-all duration-[4s] bg-white/5 backdrop-blur-3xl shadow-2xl">
                <span id="statusLabel" class="text-2xl font-black text-blue-500 italic uppercase">Ready</span>
            </div>
        </div>

        <p id="instruction" class="text-xl font-bold text-gray-400 h-8 mb-12 italic">Prepare to enter the zone of stillness.</p>
        
        <button id="startBtn" onclick="toggleBreathing()" class="bg-white/10 hover:bg-white/20 text-white font-black px-12 py-5 rounded-[2rem] border border-white/10 transition-all uppercase tracking-widest text-xs relative z-10 active:scale-95 shadow-xl">
            Initialize Session
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        <!-- Grounding -->
        <div class="glass-card p-10 group hover:border-blue-500/30 transition-all">
            <div class="flex justify-between items-start mb-8">
                <div class="w-12 h-12 bg-indigo-600/20 rounded-2xl flex items-center justify-center text-2xl">⚓</div>
                <span class="text-[9px] font-black uppercase tracking-widest text-indigo-400 bg-indigo-400/10 px-4 py-1.5 rounded-full">Cognitive Grounding</span>
            </div>
            <h3 class="text-xl font-bold mb-3">5-4-3-2-1 Protocol</h3>
            <p class="text-gray-500 text-sm leading-relaxed mb-10">Anchor yourself in the present moment through sensory verification.</p>
            
            <div id="groundingSteps" class="bg-white/5 border border-white/10 rounded-3xl p-8 mb-8 min-h-[160px] flex flex-col items-center justify-center text-center italic transition-all">
                <div id="groundingContent" class="text-gray-400 font-medium">Ready to begin tactical grounding?</div>
            </div>
            
            <button onclick="nextGroundingStep()" id="groundingBtn" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-black py-4 rounded-2xl transition-all shadow-lg shadow-indigo-600/20 uppercase tracking-widest text-[9px]">
                Activate Protocol
            </button>
        </div>

        <!-- Body Scan -->
        <div class="glass-card p-10 group hover:border-blue-500/30 transition-all">
            <div class="flex justify-between items-start mb-8">
                <div class="w-12 h-12 bg-blue-600/20 rounded-2xl flex items-center justify-center text-2xl">🧘</div>
                <span class="text-[9px] font-black uppercase tracking-widest text-blue-400 bg-blue-400/10 px-4 py-1.5 rounded-full">Somatic Release</span>
            </div>
            <h3 class="text-xl font-bold mb-3">Full Body Scan</h3>
            <p class="text-gray-500 text-sm leading-relaxed mb-10">Strategically release tension from your physical biological frame.</p>
            
            <div id="bodyScanSteps" class="bg-white/5 border border-white/10 rounded-3xl p-8 mb-8 min-h-[160px] flex flex-col items-center justify-center text-center italic transition-all">
                <div id="bodyScanContent" class="text-gray-400 font-medium">Ready for biological recalibration?</div>
            </div>
            
            <button onclick="nextBodyScanStep()" id="bodyScanBtn" class="w-full border border-white/10 hover:bg-white/5 text-white font-black py-4 rounded-2xl transition-all uppercase tracking-widest text-[9px]">
                Start guide
            </button>
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

    let groundingStep = 0;
    const gSteps = [
        { icon: '👀', t: '5 Things You See', c: '#0ea5e9' },
        { icon: '🖐️', t: '4 Things You Feel', c: '#10b981' },
        { icon: '👂', t: '3 Things You Hear', c: '#f59e0b' },
        { icon: '👃', t: '2 Things You Smell', c: '#6366f1' },
        { icon: '👅', t: '1 Thing You Taste', c: '#f43f5e' }
    ];

    function nextGroundingStep() {
        const content = document.getElementById('groundingContent');
        const btn = document.getElementById('groundingBtn');
        if (!content || !btn) return;

        if(groundingStep >= gSteps.length) {
            groundingStep = 0;
            content.textContent = 'Grounding complete. System normalized.';
            btn.textContent = 'Restart Sequence';
            return;
        }
        const s = gSteps[groundingStep];
        content.innerHTML = `<div class="text-4xl mb-4 py-2">${s.icon}</div><div class="text-[10px] font-black uppercase tracking-[0.2em] mb-2" style="color: ${s.c}">${s.t}</div>`;
        groundingStep++;
        btn.textContent = `Progress Sequence (${groundingStep}/5)`;
    }

    let bodyStep = 0;
    const bSteps = ['Feet & Toes', 'Legs & Knees', 'Torso & Back', 'Shoulders & Neck', 'Face & Mind'];
    function nextBodyScanStep() {
        const realContent = document.getElementById('bodyScanContent');
        const btn = document.getElementById('bodyScanBtn');
        if (!realContent || !btn) return;

        if(bodyStep >= bSteps.length) {
            bodyStep = 0;
            realContent.textContent = 'Somatic Release Cycle Complete.';
            btn.textContent = 'Restart Guide';
            return;
        }
        realContent.innerHTML = `<div class="text-2xl font-black italic uppercase text-blue-400 mb-2">${bSteps[bodyStep]}</div><div class="text-xs font-bold text-gray-500">Scan this region and release all potential energy.</div>`;
        bodyStep++;
        btn.textContent = `Next Region (${bodyStep}/5)`;
    }

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
