<?php
require_once 'config.php';
requireStudent();

$user_id = $_SESSION['user_id'];

// AJAX handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && isset($_POST['log_mood'])) {
    header('Content-Type: application/json');
    $mood_score = (int)$_POST['mood_score'];
    $note       = trim($_POST['note'] ?? '');

    $emojis = [
        1 => '😢', 2 => '😕', 3 => '😐', 4 => '🙂', 5 => '😊'
    ];
    $mood_emoji = $emojis[$mood_score] ?? '😐';

    $stmt = $conn->prepare("INSERT INTO mood_logs (student_id, mood_score, mood_emoji, note) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $mood_score, $mood_emoji, $note);

    if ($stmt->execute()) {
        logActivity($user_id, "Student logged mood: $mood_emoji");
        echo json_encode(['success' => true, 'emoji' => $mood_emoji, 'note' => htmlspecialchars($note), 'time' => date('g:i A'), 'date' => date('F d, Y')]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save. Please try again.']);
    }
    exit;
}


// Fetch mood history
$history_stmt = $conn->prepare("SELECT * FROM mood_logs WHERE student_id = ? ORDER BY logged_at DESC LIMIT 30");
$history_stmt->bind_param("i", $user_id);
$history_stmt->execute();
$history = $history_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interior Ledger — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <?php require_once 'pwa_head.php'; ?>
    <style>
        .mood-selector {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1.5rem;
            margin: 4rem 0;
        }
        .mood-option {
            background: #f8fafc;
            border: 2px solid transparent;
            border-radius: 32px;
            padding: 3rem 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }
        .mood-option .emoji {
            font-size: 4rem;
            filter: grayscale(100%);
            opacity: 0.3;
            transition: var(--transition);
        }
        .mood-option .label {
            font-size: 0.85rem;
            font-weight: 800;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .mood-option:hover { 
            transform: translateY(-12px); 
            background: white; 
            border-color: var(--primary-light); 
            box-shadow: 0 20px 40px rgba(0,0,0,0.05); 
        }
        .mood-option:hover .emoji { filter: grayscale(0%); opacity: 1; transform: scale(1.1); }
        
        .mood-option.selected {
            background: white;
            border-color: var(--primary);
            box-shadow: 0 30px 60px rgba(79, 70, 229, 0.15);
            transform: translateY(-8px);
        }
        .mood-option.selected .emoji { filter: grayscale(0%); opacity: 1; transform: scale(1.25); }
        .mood-option.selected .label { color: var(--primary); }

        .timeline-container {
            position: relative;
            padding-left: 3rem;
        }
        .timeline-container::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--primary) 0%, #f1f5f9 100%);
            border-radius: 1px;
        }
        
        .timeline-entry {
            position: relative;
            margin-bottom: 4rem;
        }
        .timeline-entry::before {
            content: '';
            position: absolute;
            left: -3rem;
            top: 25px;
            width: 14px;
            height: 14px;
            background: white;
            border: 3px solid var(--primary);
            border-radius: 50%;
            transform: translateX(-50%);
            z-index: 2;
            box-shadow: 0 0 0 6px white;
        }
        
        .entry-card {
            background: white;
            padding: 3rem;
            border-radius: 40px;
            border: 1px solid var(--border);
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }
        .entry-card:hover { 
            border-color: var(--primary-light); 
            transform: scale(1.02); 
            box-shadow: var(--shadow); 
        }
        
        .entry-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1.5px solid #f1f5f9;
            padding-bottom: 1.5rem;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1200px; padding-top: 5rem; padding-bottom: 8rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 6rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem;">Interior Diagnostic Mirror</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.5rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 0.75rem;">Interior Ledger</h1>
            <p style="color: var(--text-dim); font-size: 1.25rem; font-weight: 600;">Mapping your emotional trajectory with clinical precision.</p>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary); letter-spacing: -0.05em;"><?php echo count($history); ?></div>
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em;">Total Reflections</div>
        </div>
    </div>

    <!-- AJAX feedback banner -->
    <div id="moodAlert" style="display:none; padding: 1.5rem 3rem; border-radius: 24px; font-weight: 800; font-size: 1rem; margin-bottom: 4rem; text-align: center;"></div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6rem; align-items: start;">
        
        <!-- Registration Phase -->
        <div style="background: white; border-radius: 48px; padding: 5rem 4rem; border: 1px solid var(--border); box-shadow: var(--shadow); position: sticky; top: 100px;">
            <div style="width: 60px; height: 60px; border-radius: 20px; background: var(--primary-glow); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 2.5rem;">🧘</div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--text);">How are you feeling?</h2>
            <p style="color: var(--text-dim); font-weight: 600; margin-bottom: 4rem; line-height: 1.8; font-size: 1.05rem;">Select the state that aligns with your current spirit. Your honesty is the foundation of resilience.</p>
            
            <form id="moodForm" novalidate>
                <input type="hidden" name="log_mood" value="1">
                <input type="hidden" name="mood_score" id="selected_mood_score" value="">
                
                <div class="mood-selector">
                    <?php
                    $moods = [
                        1 => ['emoji' => '😢', 'label' => 'Struggle'],
                        2 => ['emoji' => '😕', 'label' => 'Unease'],
                        3 => ['emoji' => '😐', 'label' => 'Neutral'],
                        4 => ['emoji' => '🙂', 'label' => 'Steady'],
                        5 => ['emoji' => '😊', 'label' => 'Radiant']
                    ];
                    foreach ($moods as $score => $m):
                    ?>
                    <div class="mood-option" onclick="selectMood(<?php echo $score; ?>, this)">
                        <span class="emoji"><?php echo $m['emoji']; ?></span>
                        <span class="label"><?php echo $m['label']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-bottom: 4rem;">
                    <label style="display: block; font-weight: 800; font-size: 0.85rem; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1.5rem; letter-spacing: 0.1em;">Contextual Analysis (Optional)</label>
                    <textarea name="note" placeholder="What's on your mind today?" style="width: 100%; padding: 2rem; border-radius: 32px; border: 1.5px solid var(--border); font-family: inherit; font-size: 1.1rem; height: 180px; resize: none; background: #f8fafc; transition: var(--transition); line-height: 1.6;"></textarea>
                </div>

                <button type="submit" id="moodSubmitBtn" style="width: 100%; background: var(--primary); color: white; border: none; padding: 1.75rem; border-radius: 60px; font-weight: 800; cursor: pointer; box-shadow: 0 20px 40px rgba(67, 56, 202, 0.2); transition: var(--transition); letter-spacing: 0.05em; font-size: 1.1rem;">COMMIT REFLECTION</button>
            </form>
        </div>

        <!-- Archival Phase -->
        <div>
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 4rem;">
                <div style="width: 12px; height: 32px; background: var(--primary); border-radius: 4px;"></div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 800; color: var(--primary-dark);">Diagnostic Trajectory</h2>
            </div>

            <?php if (empty($history)): ?>
                <div style="padding: 10rem 4rem; text-align: center; background: #f8fafc; border-radius: 48px; border: 3px dashed var(--border);">
                    <div style="font-size: 6rem; margin-bottom: 3rem; opacity: 0.2;">📖</div>
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 800; color: var(--text-dim); margin-bottom: 1.5rem;">Your ledger is currently silent.</h3>
                    <p style="color: var(--text-dim); font-size: 1.1rem; font-weight: 600;">Commit your first reflection to begin mapping your trajectory.</p>
                </div>
            <?php else: ?>
                <div class="timeline-container">
                    <?php foreach ($history as $entry): ?>
                    <div class="timeline-entry">
                        <div class="entry-card">
                            <div class="entry-header">
                                <span style="font-weight: 800; font-size: 1.1rem; color: var(--text);"><?php echo date('F d, Y', strtotime($entry['logged_at'])); ?></span>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span style="font-size: 2rem;"><?php echo $entry['mood_emoji']; ?></span>
                                    <span style="font-size: 0.85rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; background: #f1f5f9; padding: 0.5rem 1rem; border-radius: 12px; letter-spacing: 0.05em;">
                                        <?php echo date('g:i A', strtotime($entry['logged_at'])); ?>
                                    </span>
                                </div>
                            </div>
                            <?php if ($entry['note']): ?>
                                <p style="color: var(--text); line-height: 1.8; font-size: 1.15rem; font-weight: 500; padding: 0 1rem; border-left: 4px solid var(--primary-light);">
                                    <?php echo htmlspecialchars($entry['note']); ?>
                                </p>
                            <?php else: ?>
                                <p style="color: var(--text-dim); font-style: italic; font-weight: 500;">No contextual notes archived.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="footer" style="padding: 4rem; text-align: center; border-top: 1px solid var(--border); margin-top: 4rem;">
    <p style="color: var(--text-dim); font-weight: 700; font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase;">© <?php echo date('Y'); ?> Mental Health Clinical Ecosystem. High-Fidelity Mood Stewardship.</p>
</footer>

</main>

<script>
function selectMood(score, el) {
    document.querySelectorAll('.mood-option').forEach(opt => opt.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('selected_mood_score').value = score;
}

// AJAX mood form submission
document.getElementById('moodForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const score = document.getElementById('selected_mood_score').value;
    if (!score) {
        showMoodAlert('⚠️ Please select a mood first.', false);
        return;
    }
    const btn  = document.getElementById('moodSubmitBtn');
    const note = document.querySelector('textarea[name="note"]').value;
    btn.disabled = true;
    btn.textContent = 'Saving…';

    const fd = new FormData();
    fd.append('log_mood', '1');
    fd.append('mood_score', score);
    fd.append('note', note);

    try {
        const res  = await fetch('mood_journal.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        const data = await res.json();

        if (data.success) {
            showMoodAlert('✨ Interior Reflection Committed. Stay Present.', true);
            // Prepend new timeline entry
            const container = document.querySelector('.timeline-container');
            if (container) {
                const noteHtml = data.note
                    ? `<p style="color:var(--text);line-height:1.8;font-size:1.15rem;font-weight:500;padding:0 1rem;border-left:4px solid var(--primary-light);">${data.note}</p>`
                    : `<p style="color:var(--text-dim);font-style:italic;font-weight:500;">No contextual notes archived.</p>`;
                const entry = document.createElement('div');
                entry.className = 'timeline-entry';
                entry.style.opacity = '0';
                entry.style.transform = 'translateY(-20px)';
                entry.style.transition = 'opacity 0.4s, transform 0.4s';
                entry.innerHTML = `
                    <div class="entry-card">
                        <div class="entry-header">
                            <span style="font-weight:800;font-size:1.1rem;color:var(--text);">${data.date}</span>
                            <div style="display:flex;align-items:center;gap:1rem;">
                                <span style="font-size:2rem;">${data.emoji}</span>
                                <span style="font-size:0.85rem;font-weight:800;color:var(--text-dim);text-transform:uppercase;background:#f1f5f9;padding:0.5rem 1rem;border-radius:12px;">${data.time}</span>
                            </div>
                        </div>
                        ${noteHtml}
                    </div>`;
                container.insertBefore(entry, container.firstChild);
                requestAnimationFrame(() => {
                    entry.style.opacity = '1';
                    entry.style.transform = 'translateY(0)';
                });
            }
            // Reset form
            document.querySelectorAll('.mood-option').forEach(opt => opt.classList.remove('selected'));
            document.getElementById('selected_mood_score').value = '';
            document.querySelector('textarea[name="note"]').value = '';
        } else {
            showMoodAlert('❌ ' + (data.error || 'Failed to save. Please try again.'), false);
        }
    } catch(err) {
        showMoodAlert('❌ Connection error. Please try again.', false);
    } finally {
        btn.disabled = false;
        btn.textContent = 'COMMIT REFLECTION';
    }
});

function showMoodAlert(msg, success) {
    const el = document.getElementById('moodAlert');
    el.style.display = 'block';
    el.style.background = success ? '#ecfdf5' : '#fef2f2';
    el.style.color      = success ? '#059669' : '#991b1b';
    el.style.border     = success ? '1px solid #d1fae5' : '1px solid #fee2e2';
    el.textContent = msg;
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => { el.style.display = 'none'; }, 4000);
}
</script>

</body>
</html>
