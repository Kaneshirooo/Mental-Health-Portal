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
    <?php require_once 'pwa_head.php'; ?>
    <style>
        .zen-hero {
            background: white;
            border-radius: 48px;
            padding: 6rem 3rem;
            text-align: center;
            margin-bottom: 4rem;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
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
            box-shadow: 0 0 80px rgba(79, 70, 229, 0.2);
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
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-dark);
            height: 4rem;
            margin-bottom: 3rem;
            font-family: 'Outfit', sans-serif;
        }

        .zen-btn {
            padding: 1.25rem 4rem;
            border-radius: 50px;
            font-weight: 800;
            font-size: 1.1rem;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            background: var(--primary);
            color: white;
            box-shadow: 0 15px 30px rgba(79, 70, 229, 0.2);
        }
        .zen-btn:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(79, 70, 229, 0.3); }
        .zen-btn.active { background: #1e293b; }

        .science-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-top: 4rem;
        }
        .science-card {
            background: #f8fafc;
            padding: 3rem 2rem;
            border-radius: 32px;
            text-align: center;
            border: 1px solid transparent;
            transition: var(--transition);
        }
        .science-card:hover { background: white; border-color: var(--primary-light); transform: translateY(-10px); box-shadow: var(--shadow-sm); }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">
<div class="container" style="max-width: 1100px; padding-top: 5rem; padding-bottom: 8rem;">
    <div style="text-align: center; margin-bottom: 5rem;">
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.5rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 1rem;"></h1>
        <p style="color: var(--text-dim); font-size: 1.25rem; font-weight: 600;">Silence the chaos. Honor the silence within.</p>
    </div>

    <!-- Main Bridge -->
    <div class="zen-hero">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 1rem;">Rhythmic Regulation</h2>
        <p style="color: var(--text-dim); max-width: 600px; margin: 0 auto; line-height: 1.6; font-weight: 500;">
            Harmonize your nervous system with the 4-7-8 clinical breathing cadence.
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

    <div style="margin-top: 6rem;">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 800; margin-bottom: 3.5rem; text-align: center;">Dimension of Calm</h2>
        <div class="science-grid">
            <div class="science-card">
                <div style="font-size: 3rem; margin-bottom: 2rem;">🧠</div>
                <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 1rem;">Cortisol Reduction</h3>
                <p style="color: var(--text-dim); font-size: 0.95rem; line-height: 1.6; font-weight: 600;">Rhythmic cycles inhibit the sympathetic nervous system, lowering physiological stress markers instantly.</p>
            </div>
            <div class="science-card">
                <div style="font-size: 3rem; margin-bottom: 2rem;">✨</div>
                <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 1rem;">Cognitive Clarity</h3>
                <p style="color: var(--text-dim); font-size: 0.95rem; line-height: 1.6; font-weight: 600;">Oxygenating the prefrontal cortex enhances executive function and situational awareness.</p>
            </div>
            <div class="science-card">
                <div style="font-size: 3rem; margin-bottom: 2rem;">🛡️</div>
                <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 1rem;">Emotional Fortitude</h3>
                <p style="color: var(--text-dim); font-size: 0.95rem; line-height: 1.6; font-weight: 600;">Regular practice builds the neurological resilience required to navigate high-pressure academic cycles.</p>
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
    </script>
</body>
</html>
