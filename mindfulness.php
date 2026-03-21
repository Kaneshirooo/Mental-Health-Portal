<?php
require_once 'config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindfulness — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .zen-hero {
            background: white;
            border-radius: var(--radius-lg);
            padding: 3rem 2rem;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }
        
        .breathing-portal {
            width: 400px;
            height: 400px;
            margin: 3rem auto;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .zen-circle {
            width: 160px;
            height: 160px;
            background: white;
            border: 8px solid #f1f5f9;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 10;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
        }
        
        .zen-circle.expanding {
            width: 380px;
            height: 380px;
            border-color: var(--primary-light);
            box-shadow: 0 0 60px rgba(13, 148, 136, 0.15);
        }
        
        .luminous-glow {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 2s ease;
            border-radius: 50%;
        }
        
        .zen-circle.expanding .luminous-glow { opacity: 1; }
        
        .instruction-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            height: 3rem;
            margin-bottom: 1.5rem;
            font-family: 'Outfit', sans-serif;
        }

        .zen-btn {
            padding: 0.85rem 2.5rem;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
        }
        .zen-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(13, 148, 136, 0.25); }
        .zen-btn.active { background: #1e293b; }

        .science-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            margin-top: 2rem;
        }
        .science-card {
            background: var(--surface-2);
            padding: 1.75rem 1.25rem;
            border-radius: var(--radius);
            text-align: center;
            border: 1px solid transparent;
            transition: var(--transition);
        }
        .science-card:hover { background: white; border-color: var(--border-hover); transform: translateY(-3px); box-shadow: var(--shadow-sm); }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content reveal">
<div class="container" style="max-width: 1000px; padding-top: 1.5rem; padding-bottom: 3rem;">
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.5rem;">Mindfulness</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">Take a moment to breathe and find your calm.</p>
    </div>

    <!-- Main Bridge -->
    <div class="zen-hero">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; color: var(--text); margin-bottom: 0.5rem;">Breathing Exercise</h2>
        <p style="color: var(--text-muted); max-width: 500px; margin: 0 auto; line-height: 1.6; font-weight: 400; font-size: 0.9rem;">
            Follow the 4-7-8 breathing technique to calm your nervous system.
        </p>
        
        <div class="breathing-portal">
            <div class="luminous-glow" id="glow"></div>
            <div class="zen-circle" id="circle">
                <div id="statusLabel" style="font-weight: 800; color: var(--primary); font-size: 1.5rem;">READY</div>
            </div>
        </div>
        
        <div class="instruction-text" id="instruction">Deep stillness starts with one breath.</div>
        
        <div style="margin-top: 2rem;">
            <button class="zen-btn" id="startBtn" onclick="toggleBreathing()">INITIALIZE SESSION</button>
        </div>
    </div>

    <!-- Exercise Library -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2.5rem;">
        <!-- Grounding Exercise Card -->
        <div class="card" style="padding: 2.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #fff1f2; color: #e11d48; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">⚓</div>
                <span style="font-size: 0.65rem; font-weight: 800; color: #e11d48; text-transform: uppercase; letter-spacing: 0.05em; background: #fff1f2; padding: 0.25rem 0.6rem; border-radius: 20px;">Grounding</span>
            </div>
            <h3 style="font-family:'Outfit',sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">5-4-3-2-1 Technique</h3>
            <p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.6; margin-bottom: 1.75rem;">A sensory exercise to snap out of anxiety by focusing on your physical surroundings.</p>
            
            <div id="groundingSteps" style="background: var(--surface-2); border-radius: var(--radius-sm); padding: 1.25rem; margin-bottom: 1.5rem; min-height: 100px; display: flex; align-items: center; justify-content: center; text-align: center;">
                <div id="groundingContent">
                    <div style="font-weight: 700; color: var(--text); font-size: 0.95rem;">Ready to ground yourself?</div>
                </div>
            </div>
            
            <button class="zen-btn" onclick="nextGroundingStep()" id="groundingBtn" style="width: 100%; font-size: 0.8rem; padding: 0.75rem;">START EXERCISE</button>
        </div>

        <!-- Body Scan Card -->
        <div class="card" style="padding: 2.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">🧘</div>
                <span style="font-size: 0.65rem; font-weight: 800; color: #2563eb; text-transform: uppercase; letter-spacing: 0.05em; background: #eff6ff; padding: 0.25rem 0.6rem; border-radius: 20px;">Relaxation</span>
            </div>
            <h3 style="font-family:'Outfit',sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem;">Body Scan Meditation</h3>
            <p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.6; margin-bottom: 1.75rem;">A step-by-step guide to release tension from head to toe.</p>
            
            <div id="bodyScanSteps" style="background: var(--surface-2); border-radius: var(--radius-sm); padding: 1.25rem; margin-bottom: 1.5rem; min-height: 100px; display: flex; align-items: center; justify-content: center; text-align: center; border-left: 3px solid #3b82f6;">
                <div id="bodyScanContent">
                    <div style="font-weight: 700; color: var(--text); font-size: 0.95rem;">Ready to release tension?</div>
                </div>
            </div>
            
            <button class="btn-secondary" id="bodyScanBtn" style="width: 100%; border-color: var(--border); font-weight: 700;" onclick="nextBodyScanStep()">START GUIDE ↓</button>
        </div>
    </div>

    <!-- AI Personalized Session -->
    <div style="background: white; border-radius: var(--radius-lg); padding: 2.5rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); margin-top: 2.5rem;">
        <div style="display: flex; gap: 1.5rem; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 1.5rem;">
            <div style="width: 50px; height: 50px; border-radius: 14px; background: #faf5ff; color: #9333ea; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; border: 1px solid #f3e8ff;">✨</div>
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; color: var(--text); margin-bottom: 0.25rem;">AI Personalized Session</h2>
                <p style="color: var(--text-muted); font-size: 0.88rem; font-weight: 500;">Let Aria create a unique 1-minute mindfulness script based on your current mood.</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 320px 1fr; gap: 3rem; align-items: start;">
            <div>
                <label style="display:block; font-weight: 700; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1rem; letter-spacing: 0.05em;">How are you feeling?</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.5rem;">
                    <button class="mood-btn" onclick="setAiMood('stressed', this)">Stressed</button>
                    <button class="mood-btn" onclick="setAiMood('anxious', this)">Anxious</button>
                    <button class="mood-btn" onclick="setAiMood('sad', this)">Sad</button>
                    <button class="mood-btn" onclick="setAiMood('tired', this)">Tired</button>
                    <button class="mood-btn" onclick="setAiMood('neutral', this)">Neutral</button>
                    <button class="mood-btn" onclick="setAiMood('happy', this)">Happy</button>
                </div>
                <button class="zen-btn" id="aiGenBtn" onclick="generateAiSession()" style="width: 100%;" disabled>GENERATE SESSION ✨</button>
            </div>
            <div id="aiScriptBox" style="background: #fcfdfd; border: 1.5px dashed var(--border); border-radius: var(--radius); padding: 2rem; min-height: 200px; display: flex; align-items: center; justify-content: center; text-align: center; position: relative; transition: var(--transition);">
                <p id="aiScriptText" style="color: var(--text-dim); font-size: 0.95rem; font-weight: 500; font-style: italic; line-height: 1.7;">
                    Select your mood to begin your personalized session.
                </p>
            </div>
        </div>
    </div>

    <style>
        .mood-btn {
            background: var(--surface-2);
            border: 1.5px solid transparent;
            padding: 0.6rem;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-dim);
            cursor: pointer;
            transition: var(--transition);
        }
        .mood-btn:hover { background: white; border-color: var(--border-hover); }
        .mood-btn.selected { background: white; border-color: var(--primary); color: var(--primary); box-shadow: var(--shadow-sm); }
    </style>

    <div style="margin-top: 3rem;">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; text-align: center; color: var(--text);">Benefits of Mindfulness</h2>
        <div class="science-grid">
            <div class="science-card">
                <div style="font-size: 2rem; margin-bottom: 1rem;">🧠</div>
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem;">Reduce Stress</h3>
                <p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.6; font-weight: 400;">Regular breathing exercises help lower cortisol and calm your nervous system.</p>
            </div>
            <div class="science-card">
                <div style="font-size: 2rem; margin-bottom: 1rem;">✨</div>
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem;">Improve Focus</h3>
                <p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.6; font-weight: 400;">Deep breathing increases oxygen to the brain, enhancing clarity and concentration.</p>
            </div>
            <div class="science-card">
                <div style="font-size: 2rem; margin-bottom: 1rem;">🛡️</div>
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem;">Build Resilience</h3>
                <p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.6; font-weight: 400;">Regular practice builds emotional stamina to handle academic and personal challenges.</p>
            </div>
        </div>
    </div>
</div>
</main>

    <script>
        let isRunning = false;
        let timer;
        const circle = document.getElementById('circle');
        const glow = document.getElementById('glow');
        const instruct = document.getElementById('instruction');
        const statusLabel = document.getElementById('statusLabel');
        const btn = document.getElementById('startBtn');

        function toggleBreathing() {
            if (isRunning) {
                stop();
            } else {
                start();
            }
        }

        function start() {
            isRunning = true;
            btn.textContent = 'END SESSION';
            btn.classList.add('active');
            cycle();
            timer = setInterval(cycle, 12000); 
        }

        function stop() {
            isRunning = false;
            clearInterval(timer);
            btn.textContent = 'INITIALIZE SESSION';
            btn.classList.remove('active');
            circle.classList.remove('expanding');
            statusLabel.textContent = 'READY';
            instruct.textContent = 'Peace is within you.';
        }

        function cycle() {
            if (!isRunning) return;
            
            // Inhale (4s)
            circle.classList.add('expanding');
            statusLabel.textContent = 'INHALE';
            instruct.textContent = 'Fill your lungs slowly...';
            
            setTimeout(() => {
                if (!isRunning) return;
                statusLabel.textContent = 'HOLD';
                instruct.textContent = 'Pause and notice the silence.';
                
                setTimeout(() => {
                    if (!isRunning) return;
                    circle.classList.remove('expanding');
                    statusLabel.textContent = 'EXHALE';
                    instruct.textContent = 'Release all tension...';
                }, 4000);
            }, 4000);
        }

        let selectedAiMood = '';

        function setAiMood(mood, el) {
            document.querySelectorAll('.mood-btn').forEach(btn => btn.classList.remove('selected'));
            el.classList.add('selected');
            selectedAiMood = mood;
            document.getElementById('aiGenBtn').disabled = false;
        }

        async function generateAiSession() {
            const btn = document.getElementById('aiGenBtn');
            const text = document.getElementById('aiScriptText');
            const box = document.getElementById('aiScriptBox');
            
            if (!selectedAiMood) return;

            btn.disabled = true;
            btn.textContent = 'Aria is creating... ✨';
            text.style.opacity = '0.5';
            box.style.borderColor = 'var(--primary-light)';

            try {
                const res = await fetch('mindfulness_ai_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ mood: selectedAiMood })
                });
                const data = await res.json();

                if (data.success) {
                    text.innerHTML = data.script.replace(/\n/g, '<br>');
                    text.style.fontStyle = 'normal';
                    text.style.color = 'var(--text)';
                    box.style.borderStyle = 'solid';
                } else {
                    alert(data.error || 'Could not generate session.');
                }
            } catch (err) {
                alert('Connection error. Please try again.');
            } finally {
                btn.disabled = false;
                btn.textContent = 'GENERATE NEW SESSION ✨';
                text.style.opacity = '1';
            }
        }

        // ── Grounding Exercise Logic ──
        let groundingStep = 0;
        const groundingSteps = [
            { icon: '👀', title: 'SEE', text: 'Name 5 things you can see around you right now.', color: '#0ea5e9' },
            { icon: '🖐️', title: 'FEEL', text: 'Notice 4 things you can feel (e.g., your feet on the floor, the chair).', color: '#10b981' },
            { icon: '👂', title: 'HEAR', text: 'Listen for 3 distinct sounds in your environment.', color: '#f59e0b' },
            { icon: '👃', title: 'SMELL', text: 'Identify 2 things you can smell (or your favorite scents).', color: '#6366f1' },
            { icon: '👅', title: 'TASTE', text: 'Focus on 1 thing you can taste (or your favorite flavor).', color: '#f43f5e' }
        ];

        function nextGroundingStep() {
            const content = document.getElementById('groundingContent');
            const btn = document.getElementById('groundingBtn');
            
            if (groundingStep >= groundingSteps.length) {
                groundingStep = 0;
                content.innerHTML = '<div style="font-weight:700; color:var(--text); font-size:1.1rem; animation:fadeInUp 0.4s ease-out;">You are here. You are safe. ✨</div>';
                btn.textContent = 'START AGAIN';
                return;
            }

            const step = groundingSteps[groundingStep];
            content.style.opacity = '0';
            
            setTimeout(() => {
                content.innerHTML = `
                    <div style="font-size:2.5rem; margin-bottom:0.75rem; animation:fadeInUp 0.3s ease-out;">${step.icon}</div>
                    <div style="font-weight:800; color:${step.color}; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.35rem; animation:fadeInUp 0.4s ease-out;">Step ${groundingStep + 1}: ${step.title}</div>
                    <div style="font-weight:600; color:var(--text); font-size:1rem; line-height:1.4; animation:fadeInUp 0.5s ease-out;">${step.text}</div>
                `;
                content.style.transition = 'opacity 0.3s ease';
                content.style.opacity = '1';
                groundingStep++;
                btn.textContent = groundingStep >= groundingSteps.length ? 'FINISH' : `NEXT STEP (${5 - groundingStep} LEFT) →`;
            }, 200);
        }

        // ── Body Scan Logic ──
        let bodyStep = 0;
        const bodySteps = [
            { icon: '🦶', title: 'Feet & Toes', text: 'Wiggle your toes. Feel the weight of your feet. Release any tightness.' },
            { icon: '🦵', title: 'Legs & Knees', text: 'Scan your calves and thighs. Let them feel heavy and relaxed.' },
            { icon: '背', title: 'Back & Spines', text: 'Feel your back against the chair. Let your spine soften.' },
            { icon: '👕', title: 'Shoulders & Neck', text: 'Drop your shoulders away from your ears. Release the weight.' },
            { icon: '😊', title: 'Face & Jaw', text: 'Unclench your jaw. Soften the muscles around your eyes.' }
        ];

        function nextBodyScanStep() {
            const content = document.getElementById('bodyScanContent');
            const btn = document.getElementById('bodyScanBtn');
            
            if (bodyStep >= bodySteps.length) {
                bodyStep = 0;
                content.innerHTML = '<div style="font-weight:700; color:var(--text); font-size:1rem; animation:fadeInUp 0.4s ease-out;">Your body is at rest. Your mind is clear. 🌿</div>';
                btn.textContent = 'START AGAIN';
                return;
            }

            const step = bodySteps[bodyStep];
            content.style.opacity = '0';
            
            setTimeout(() => {
                content.innerHTML = `
                    <div style="font-size:2rem; margin-bottom:0.5rem; animation:fadeInUp 0.3s ease-out;">${step.icon}</div>
                    <div style="font-weight:800; color:#3b82f6; font-size:0.75rem; text-transform:uppercase; margin-bottom:0.25rem; animation:fadeInUp 0.4s ease-out;">${step.title}</div>
                    <div style="font-size:0.9rem; color:var(--text); font-weight:600; line-height:1.5; animation:fadeInUp 0.5s ease-out;">${step.text}</div>
                `;
                content.style.transition = 'opacity 0.3s ease';
                content.style.opacity = '1';
                bodyStep++;
                btn.textContent = bodyStep >= bodySteps.length ? 'FINISH' : 'NEXT AREA ↓';
            }, 200);
        }
    </script>
</body>
</html>
