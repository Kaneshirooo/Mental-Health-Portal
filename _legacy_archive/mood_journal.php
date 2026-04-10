<?php
require_once 'config.php';
requireStudent();

$user_id = $_SESSION['user_id'];

// AJAX handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && isset($_POST['log_mood'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'CSRF token validation failed. Please refresh and try again.']);
        exit;
    }
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
        
        // ── PROACTIVE COUNSELOR ALERT ─────────────────────
        if ($mood_score <= 2) {
            $student_name = $_SESSION['full_name'] ?? 'A student';
            $alert_title  = "Mood Alert: " . ($mood_score == 1 ? "Critical" : "Concerning");
            $alert_msg    = "$student_name just logged a $mood_emoji mood score ($mood_score/5) in their Mood Journal. " . 
                            ($note ? "Note: \"$note\"" : "No note was provided.");
            
            // Find assigned counselor(s) from accepted appointments
            $c_stmt = $conn->prepare("SELECT DISTINCT counselor_id FROM appointments WHERE student_id = ? AND status = 'accepted'");
            $c_stmt->bind_param("i", $user_id);
            $c_stmt->execute();
            $c_res = $c_stmt->get_result();
            
            $notified = false;
            while ($c_row = $c_res->fetch_assoc()) {
                createNotification($c_row['counselor_id'], $alert_title, $alert_msg, 'mood_alert');
                $notified = true;
            }
            
            // If no assigned counselor, notify the admin/head counselor
            if (!$notified) {
                $head_stmt = $conn->query("SELECT user_id FROM users WHERE user_type = 'admin' LIMIT 1");
                if ($h_row = $head_stmt->fetch_assoc()) {
                    createNotification($h_row['user_id'], "[Global] " . $alert_title, "Unassigned Student ($student_name): " . $alert_msg, 'mood_alert');
                }
            }
        }

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
    <title>Mood Journal — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css?v=2.2">
    <?php include 'theme_init.php'; ?>
    <style>
        .mood-selector {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.75rem;
            margin: 1.75rem 0;
        }
        .mood-option {
            background: var(--surface-2);
            border: 2px solid transparent;
            border-radius: var(--radius);
            padding: 1.25rem 0.75rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }
        .mood-option .emoji {
            font-size: 2.25rem;
            filter: grayscale(100%);
            opacity: 0.3;
            transition: var(--transition);
        }
        .mood-option .label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .mood-option:hover { 
            transform: translateY(-3px); 
            background: var(--surface-solid); 
            border-color: var(--primary-light); 
            box-shadow: var(--shadow-sm); 
        }
        .mood-option:hover .emoji { filter: grayscale(0%); opacity: 1; transform: scale(1.05); }
        
        .mood-option.selected {
            background: var(--surface-solid);
            border-color: var(--primary);
            box-shadow: 0 8px 20px var(--primary-glow);
            transform: translateY(-2px);
        }
        .mood-option.selected .emoji { filter: grayscale(0%); opacity: 1; transform: scale(1.15); }
        .mood-option.selected .label { color: var(--primary); }

        .timeline-container {
            position: relative;
            padding-left: 2rem;
        }
        .timeline-container::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--primary) 0%, var(--surface-2) 100%);
            border-radius: 1px;
        }
        
        .timeline-entry {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .timeline-entry::before {
            content: '';
            position: absolute;
            left: -2rem;
            top: 20px;
            width: 10px;
            height: 10px;
            background: var(--surface-solid);
            border: 2px solid var(--primary);
            border-radius: 50%;
            transform: translateX(-50%);
            z-index: 2;
            box-shadow: 0 0 0 4px var(--surface-solid);
        }
        
        .entry-card {
            background: var(--surface-solid);
            padding: 1.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }
        .entry-card:hover { 
            border-color: var(--border-hover); 
            box-shadow: var(--shadow); 
        }
        
        .entry-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.75rem;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content reveal">

<div class="container" style="max-width: 1100px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem;">
        <div>
            <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Emotional Tracking</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Mood Journal</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">Track how you're feeling and spot patterns over time.</p>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary);"><?php echo count($history); ?></div>
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.06em;">Entries</div>
        </div>
    </div>

    <!-- AJAX feedback banner -->
    <div id="moodAlert" style="display:none; padding: 0.85rem 1.25rem; border-radius: var(--radius-sm); font-weight: 600; font-size: 0.9rem; margin-bottom: 1.5rem; text-align: center;"></div>

    <!-- Aria's Insight Card -->
    <div id="ariaInsightCard" class="glass" style="border-radius: var(--radius); padding: 1.5rem 2rem; margin-bottom: 2.5rem; display: flex; gap: 1.5rem; align-items: center; box-shadow: var(--shadow-sm); animation: fade-up 0.6s ease-out;">
        <div style="width: 54px; height: 54px; border-radius: 16px; background: var(--surface-2); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; box-shadow: 0 4px 12px var(--primary-glow); flex-shrink: 0;">✨</div>
        <div style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em;">Aria's Insight</h3>
                <button onclick="getAriaInsight()" id="insightRefreshBtn" style="background: none; border: none; color: var(--primary-light); cursor: pointer; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Refresh ✨</button>
            </div>
            <p id="ariaInsightText" style="color: var(--text); font-size: 0.95rem; line-height: 1.6; font-weight: 500; font-style: italic;">
                Logging your mood regularly helps Aria understand you better. Click 'Refresh' to see what Aria thinks of your recent progress.
            </p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; align-items: start;">
        
        <!-- Mood Selector -->
        <div style="background: var(--surface-solid); border-radius: var(--radius); padding: 2rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); position: sticky; top: 80px;">
            <div style="width: 44px; height: 44px; border-radius: var(--radius-sm); background: var(--primary-glow); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 1.25rem;">🧘</div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text);">How are you feeling?</h2>
            <p style="color: var(--text-muted); font-weight: 400; margin-bottom: 1.75rem; line-height: 1.6; font-size: 0.88rem;">Select your current mood. Your honesty helps build a clearer picture.</p>
            
            <form id="moodForm" novalidate>
                <input type="hidden" name="log_mood" value="1">
                <input type="hidden" name="mood_score" id="selected_mood_score" value="">
                
                <div class="mood-selector">
                    <?php
                    $moods = [
                        1 => ['emoji' => '😢', 'label' => 'Struggle'],
                        2 => ['emoji' => '😕', 'label' => 'Uneasy'],
                        3 => ['emoji' => '😐', 'label' => 'Neutral'],
                        4 => ['emoji' => '🙂', 'label' => 'Good'],
                        5 => ['emoji' => '😊', 'label' => 'Great']
                    ];
                    foreach ($moods as $score => $m):
                    ?>
                    <div class="mood-option" onclick="selectMood(<?php echo $score; ?>, this)">
                        <span class="emoji"><?php echo $m['emoji']; ?></span>
                        <span class="label"><?php echo $m['label']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-bottom: 1.75rem;">
                    <label style="display: block; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.75rem;">Notes (Optional)</label>
                    <textarea name="note" placeholder="What's on your mind today?" style="width: 100%; padding: 1.25rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-family: inherit; font-size: 0.95rem; height: 120px; resize: none; background: var(--surface-2); transition: var(--transition); line-height: 1.6;"></textarea>
                </div>

                <button type="submit" id="moodSubmitBtn" style="width: 100%; background: var(--primary); color: white; border: none; padding: 0.85rem; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); transition: var(--transition); font-size: 0.9rem;">Log Mood →</button>
            </form>
        </div>

        <!-- Timeline -->
        <div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: var(--text); margin-bottom: 1.5rem;">Your Timeline</h2>

            <?php if (empty($history)): ?>
                <div class="empty-state">
                    <span class="empty-icon">📖</span>
                    <h3 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem;">No entries yet</h3>
                    <p style="color: var(--text-dim); font-size: 0.88rem;">Log your first mood to start building your timeline.</p>
                </div>
            <?php else: ?>
                <div class="timeline-container">
                    <?php foreach ($history as $entry): ?>
                    <div class="timeline-entry">
                        <div class="entry-card">
                            <div class="entry-header">
                                <span style="font-weight: 600; font-size: 0.9rem; color: var(--text);"><?php echo date('F d, Y', strtotime($entry['logged_at'])); ?></span>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <span style="font-size: 1.5rem;"><?php echo $entry['mood_emoji']; ?></span>
                                    <span style="font-size: 0.72rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; background: var(--surface-2); padding: 0.3rem 0.75rem; border-radius: var(--radius-sm); letter-spacing: 0.04em;">
                                        <?php echo date('g:i A', strtotime($entry['logged_at'])); ?>
                                    </span>
                                </div>
                            </div>
                            <?php if ($entry['note']): ?>
                                <p style="color: var(--text); line-height: 1.6; font-size: 0.9rem; font-weight: 400; padding-left: 0.75rem; border-left: 3px solid var(--primary-light);">
                                    <?php echo htmlspecialchars($entry['note']); ?>
                                </p>
                            <?php else: ?>
                                <p style="color: var(--text-dim); font-style: italic; font-weight: 400; font-size: 0.88rem;">No notes added.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
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
        showMoodAlert('Please select a mood first.', false);
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
            showMoodAlert('✅ Mood logged successfully!', true);
            // Prepend new timeline entry
            const container = document.querySelector('.timeline-container');
            if (container) {
                const noteHtml = data.note
                    ? `<p style="color:var(--text);line-height:1.6;font-size:0.9rem;font-weight:400;padding-left:0.75rem;border-left:3px solid var(--primary-light);">${data.note}</p>`
                    : `<p style="color:var(--text-dim);font-style:italic;font-weight:400;font-size:0.88rem;">No notes added.</p>`;
                const entry = document.createElement('div');
                entry.className = 'timeline-entry';
                entry.style.opacity = '0';
                entry.style.transform = 'translateY(-20px)';
                entry.style.transition = 'opacity 0.4s, transform 0.4s';
                entry.innerHTML = `
                    <div class="entry-card">
                        <div class="entry-header">
                            <span style="font-weight:600;font-size:0.9rem;color:var(--text);">${data.date}</span>
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <span style="font-size:1.5rem;">${data.emoji}</span>
                                <span style="font-size:0.72rem;font-weight:600;color:var(--text-dim);text-transform:uppercase;background:var(--surface-2);padding:0.3rem 0.75rem;border-radius:var(--radius-sm);">${data.time}</span>
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
        btn.textContent = 'Log Mood →';
    }
});

function showMoodAlert(msg, success) {
    const el = document.getElementById('moodAlert');
    el.style.display = 'block';
    el.style.background = success ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)';
    el.style.color      = success ? 'var(--primary)' : '#ef4444';
    el.style.border     = success ? '1px solid rgba(16, 185, 129, 0.2)' : '1px solid rgba(239, 68, 68, 0.2)';
    el.textContent = msg;
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => { el.style.display = 'none'; }, 4000);
}

async function getAriaInsight() {
    const text = document.getElementById('ariaInsightText');
    const btn = document.getElementById('insightRefreshBtn');
    
    text.style.opacity = '0.5';
    btn.disabled = true;
    btn.textContent = 'Seeking... ✨';

    try {
        const res = await fetch('student_mood_insight_api.php');
        const data = await res.json();
        
        if (data.success) {
            text.textContent = data.insight;
        } else {
            text.textContent = data.error || 'Aria needs a moment to gather her thoughts.';
        }
    } catch (err) {
        text.textContent = 'Could not connect to Aria right now.';
    } finally {
        text.style.opacity = '1';
        btn.disabled = false;
        btn.textContent = 'Refresh ✨';
    }
}

// Auto-load insight if history exists
window.addEventListener('DOMContentLoaded', () => {
    if (<?php echo count($history); ?> > 0) {
        getAriaInsight();
    }
});
</script>

</body>
</html>
