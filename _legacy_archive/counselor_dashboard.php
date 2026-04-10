<?php
require_once 'config.php';
requireCounselor();

$counselor_id = $_SESSION['user_id'];

// Handle archiving (marking as read) - Must be before any output
if (isset($_GET['archive_note'])) {
    $nid = intval($_GET['archive_note']);
    $conn->query("UPDATE anonymous_notes SET status = 'read' WHERE note_id = $nid");
    redirect('counselor_dashboard.php');
}

// High-risk students (Critical first, then High)
$risk_result = $conn->query(
    "SELECT DISTINCT u.user_id, u.full_name, u.email, u.roll_number, u.department,
            a.overall_score, a.risk_level, a.assessment_date
     FROM users u
     JOIN assessment_scores a ON u.user_id = a.user_id
     WHERE a.risk_level IN ('High','Critical')
       AND a.assessment_date = (SELECT MAX(assessment_date) FROM assessment_scores WHERE user_id = u.user_id)
     ORDER BY FIELD(a.risk_level,'Critical','High'), a.overall_score DESC"
);

// Stats
$stats_result = $conn->query(
    "SELECT
       COUNT(DISTINCT user_id) as total_students,
       SUM(CASE WHEN risk_level='Low'      THEN 1 ELSE 0 END) as low_risk,
       SUM(CASE WHEN risk_level='Moderate' THEN 1 ELSE 0 END) as moderate_risk,
       SUM(CASE WHEN risk_level='High'     THEN 1 ELSE 0 END) as high_risk,
       SUM(CASE WHEN risk_level='Critical' THEN 1 ELSE 0 END) as critical_risk
     FROM assessment_scores
     WHERE assessment_date = (SELECT MAX(assessment_date) FROM assessment_scores AS z WHERE z.user_id = assessment_scores.user_id)"
);
$stats = $stats_result->fetch_assoc();
$total = max(1, $stats['total_students']);

// Handle anonymous note reply POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_note_submit'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed. Please refresh.");
    }
    $note_id = intval($_POST['reply_note_id']);
    $reply_text = sanitize($_POST['reply_text']);
    if ($note_id && $reply_text) {
        $stmt = $conn->prepare(
            "INSERT INTO anonymous_note_messages (note_id, sender_type, message_text) VALUES (?, 'counselor', ?)"
        );
        $stmt->bind_param("is", $note_id, $reply_text);
        if ($stmt->execute()) {
            $conn->query("UPDATE anonymous_notes SET status = 'replied' WHERE note_id = $note_id");
            $qn_msg = 'reply_success';
            logActivity($counselor_id, "Counselor replied to anonymous note #$note_id");
        }
        else {
            $qn_msg = 'reply_error';
        }
    }
}

// Fetch anonymous notes and their conversation history
$anon_notes_res = $conn->query(
    "SELECT n.note_id, n.status, n.created_at, 
            (SELECT message_text FROM anonymous_note_messages WHERE note_id = n.note_id ORDER BY created_at DESC LIMIT 1) as last_text,
            (SELECT sender_type FROM anonymous_note_messages WHERE note_id = n.note_id ORDER BY created_at DESC LIMIT 1) as last_sender
     FROM anonymous_notes n 
     WHERE n.status IN ('new', 'read', 'replied') 
     ORDER BY n.created_at DESC"
);

$anon_notes = [];
while ($row = $anon_notes_res->fetch_assoc()) {
    // SQLi FIX: Use prepared statement
    $note_id_val = (int)$row['note_id'];
    $msg_stmt = $conn->prepare("SELECT sender_type, message_text, created_at FROM anonymous_note_messages WHERE note_id = ? ORDER BY created_at ASC");
    $msg_stmt->bind_param("i", $note_id_val);
    $msg_stmt->execute();
    $msg_res = $msg_stmt->get_result();
    $row['conversation'] = $msg_res->fetch_all(MYSQLI_ASSOC);
    $anon_notes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counselor Dashboard — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css?v=2.3">
    <?php include 'theme_init.php'; ?>
    <style>
        .stats-matrix {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card-clinical {
            background: var(--surface-solid);
            border-radius: var(--radius);
            padding: 1.5rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        .stat-card-clinical:hover { transform: translateY(-3px); box-shadow: var(--shadow); border-color: var(--border-hover); }
        
        .intervention-registry {
            background: var(--surface-solid);
            border-radius: var(--radius);
            padding: 2rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }
        .registry-table th { 
            padding: 0.85rem 1.25rem;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: var(--surface-2);
            border-bottom: 1px solid var(--border);
        }
        .registry-table td { 
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .dialog-vault {
            background: var(--surface-2);
            border-radius: var(--radius);
            padding: 1.5rem;
            border: 1px solid var(--border);
            margin-bottom: 1.5rem;
        }
        .message-bubble-counselor {
            padding: 0.85rem 1rem;
            border-radius: var(--radius-sm);
            font-size: 0.88rem;
            line-height: 1.55;
            margin-bottom: 0.75rem;
            max-width: 85%;
            font-weight: 400;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<main class="main-content reveal">

<div class="container" style="max-width: 1200px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem;">
        <div>
            <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Counselor Portal</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Dashboard</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;"><?php echo count($anon_notes); ?> anonymous notes require attention.</p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <div style="text-align: right; padding-right: 1.25rem; border-right: 1px solid var(--border);">
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--risk-critical); line-height: 1;"><?php echo $stats['critical_risk'] ?? 0; ?></div>
                <div style="font-size: 0.68rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.04em; margin-top: 0.25rem;">Critical</div>
            </div>
            <a href="counselor_appointments.php" style="padding: 0.65rem 1.5rem; border-radius: var(--radius-sm); background: var(--primary); color: white; font-weight: 600; text-decoration: none; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); font-size: 0.85rem;">View Schedule</a>
        </div>
    </div>

    <!-- Stats Matrix -->
    <div class="stats-matrix">
        <div class="stat-card-clinical">
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.04em;">Total Students</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--text); line-height: 1;"><?php echo $stats['total_students'] ?? 0; ?></div>
        </div>
        <div class="stat-card-clinical" style="background: #f0fdf4; border-color: #dcfce7;">
            <div style="font-size: 0.72rem; font-weight: 600; color: #16a34a; text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.04em;">Low Risk</div>
            <div style="font-size: 2rem; font-weight: 700; color: #16a34a; line-height: 1;"><?php echo $stats['low_risk'] ?? 0; ?></div>
        </div>
        <div class="stat-card-clinical" style="background: #fefce8; border-color: #fef08a;">
            <div style="font-size: 0.72rem; font-weight: 600; color: #ca8a04; text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.04em;">Moderate</div>
            <div style="font-size: 2rem; font-weight: 700; color: #ca8a04; line-height: 1;"><?php echo $stats['moderate_risk'] ?? 0; ?></div>
        </div>
        <div class="stat-card-clinical" style="background: #fff7ed; border-color: #ffedd5;">
            <div style="font-size: 0.72rem; font-weight: 600; color: #ea580c; text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.04em;">High Risk</div>
            <div style="font-size: 2rem; font-weight: 700; color: #ea580c; line-height: 1;"><?php echo $stats['high_risk'] ?? 0; ?></div>
        </div>
        <div class="stat-card-clinical" style="background: #fef2f2; border-color: #fee2e2;">
            <div style="font-size: 0.8rem; font-weight: 800; color: #dc2626; text-transform: uppercase; margin-bottom: 1.5rem;">CRITICAL INTERVENTION</div>
            <div style="font-size: 3rem; font-weight: 800; color: #dc2626; line-height: 1;"><?php echo $stats['critical_risk'] ?? 0; ?></div>
        </div>
    </div>

    <!-- Intervention Registry -->
    <div class="intervention-registry">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1.25rem;">
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; color: var(--text); margin-bottom: 0.25rem;">Priority Intervention Queue</h2>
                <p style="color: var(--text-muted); font-weight: 400; font-size: 0.9rem;">Students requiring immediate clinical attention based on assessment risk.</p>
            </div>
            <a href="student_list.php" style="font-weight: 600; color: var(--primary); text-decoration: none; font-size: 0.85rem;">View All Records →</a>
        </div>

        <table class="registry-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: left;">Identity</th>
                    <th style="text-align: left;">Clinical Index</th>
                    <th style="text-align: left;">Risk Classification</th>
                    <th style="text-align: left;">Last Diagnostic</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($risk_result->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" style="padding: 4rem 2rem; text-align: center;">
                            <div style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.4;">✨</div>
                            <div style="font-weight: 600; color: var(--text-dim); font-size: 0.95rem;">All priority cases have been reviewed.</div>
                        </td>
                    </tr>
                <?php
else: ?>
                    <?php while ($student = $risk_result->fetch_assoc()): ?>
                    <tr style="transition: var(--transition);">
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.85rem;">
                                <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--surface-2); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; border: 1px solid var(--border); font-size: 0.85rem;">
                                    <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: var(--text); font-size: 0.95rem; margin-bottom: 0.1rem;"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 500;"><?php echo $student['roll_number']; ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="flex: 1; height: 6px; background: var(--surface-2); border-radius: 10px; overflow: hidden; max-width: 100px; border: 1px solid var(--border);">
                                    <div style="width: <?php echo $student['overall_score']; ?>%; height: 100%; background: <?php echo($student['risk_level'] === 'Critical') ? '#dc2626' : 'var(--primary)'; ?>;"></div>
                                </div>
                                <span style="font-weight: 700; color: var(--text); font-size: 0.85rem;"><?php echo $student['overall_score']; ?></span>
                            </div>
                        </td>
                        <td>
                            <span style="padding: 0.35rem 0.8rem; border-radius: 20px; font-weight: 700; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.04em; background: <?php echo($student['risk_level'] === 'Critical') ? '#fff1f2' : '#fff7ed'; ?>; color: <?php echo($student['risk_level'] === 'Critical') ? '#e11d48' : '#ea580c'; ?>; border: 1px solid transparent;">
                                <?php echo $student['risk_level']; ?>
                            </span>
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.85rem; font-weight: 500;"><?php echo date('M d', strtotime($student['assessment_date'])); ?></td>
                        <td style="text-align: right;">
                            <a href="student_profile.php?user_id=<?php echo $student['user_id']; ?>" style="display: inline-block; padding: 0.5rem 1rem; border-radius: var(--radius-sm); background: var(--surface-solid); border: 1.5px solid var(--border); text-decoration: none; font-weight: 600; color: var(--primary); font-size: 0.8rem; transition: var(--transition);">Manage Case</a>
                        </td>
                    </tr>
                    <?php
    endwhile; ?>
                <?php
endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Anonymous Notes -->
    <div style="background: var(--surface-solid); border-radius: var(--radius); border: 1px solid var(--border); padding: 2rem; box-shadow: var(--shadow-sm);">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; color: var(--text); margin-bottom: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 1.25rem;">Student Voice Feed</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(450px, 1fr)); gap: 1.5rem;">
            <?php foreach ($anon_notes as $note): ?>
            <div class="dialog-vault">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary);"></div>
                        <span style="font-weight: 700; font-size: 0.75rem; letter-spacing: 0.04em; color: var(--text-dim); text-transform: uppercase;">ID #<?php echo str_pad($note['note_id'], 4, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <span style="padding: 0.3rem 0.75rem; border-radius: 4px; background: var(--surface-solid); border: 1px solid var(--border); font-size: 0.65rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
                            <?php echo $note['status']; ?>
                        </span>
                        <a href="?archive_note=<?php echo $note['note_id']; ?>" style="text-decoration: none; padding: 0.3rem 0.75rem; border-radius: 4px; background: #fff1f2; font-size: 0.65rem; font-weight: 700; color: #e11d48; text-transform: uppercase;">Archive</a>
                    </div>
                </div>

                <div style="max-height: 250px; overflow-y: auto; padding-right: 0.75rem; margin-bottom: 1.5rem;" class="custom-scroll">
                    <?php foreach ($note['conversation'] as $msg): ?>
                    <div style="margin-bottom: 1.25rem; display: flex; flex-direction: column; align-items: <?php echo($msg['sender_type'] === 'student') ? 'flex-start' : 'flex-end'; ?>;">
                        <div style="font-weight: 600; font-size: 0.65rem; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.4rem; letter-spacing: 0.02em;">
                            <?php echo($msg['sender_type'] === 'student') ? 'Student' : 'Counselor'; ?>
                        </div>
                        <div class="message-bubble-counselor" style="<?php echo($msg['sender_type'] === 'student') ? 'background: var(--surface-solid); border: 1.5px solid var(--border); color: var(--text); border-bottom-left-radius: 2px;' : 'background: var(--primary); color: white; border-bottom-right-radius: 2px;'; ?>">
                            <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                        </div>
                    </div>
                    <?php
    endforeach; ?>
                </div>

                <form method="POST" style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="reply_note_submit" value="1">
                    <input type="hidden" name="reply_note_id" value="<?php echo $note['note_id']; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.2rem;">
                        <label style="font-weight: 700; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase;">Your Response</label>
                        <button type="button" onclick="suggestAIReply(this, <?php echo $note['note_id']; ?>)" style="background: none; border: none; color: var(--primary); font-weight: 700; font-size: 0.7rem; cursor: pointer; text-transform: uppercase;">Suggest AI Reply ✨</button>
                    </div>
                    <textarea name="reply_text" id="reply_text_<?php echo $note['note_id']; ?>" placeholder="Type clinical response..." style="width: 100%; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 500; background: var(--surface-solid); color: var(--text); font-family: inherit; font-size: 0.9rem; line-height: 1.5; resize: none; height: 70px;" required></textarea>
                    <button type="submit" style="background: var(--primary); color: white; border: none; padding: 0.65rem; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; transition: var(--transition); font-size: 0.85rem;">Send Reply →</button>
                </form>
            </div>
            <?php
endforeach; ?>
        </div>
    </div>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
</footer>

</main>

<script>
async function suggestAIReply(btn, noteId) {
    const textarea = document.getElementById('reply_text_' + noteId);
    if (!textarea) return;

    // Get the student message (the last message in the conversation from the student)
    const cards = btn.closest('.dialog-vault').querySelectorAll('.message-bubble-counselor');
    let lastStudentMsg = "";
    cards.forEach(card => {
        if (card.style.background === 'white' || card.style.background.includes('white')) {
            lastStudentMsg = card.textContent.trim();
        }
    });

    if (!lastStudentMsg) return;

    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Generating... ✨';
    
    try {
        const res = await fetch('counselor_ai_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'suggest_reply', note_text: lastStudentMsg })
        });
        const data = await res.json();
        
        if (data.success) {
            textarea.value = data.suggestion;
            textarea.focus();
        } else {
            alert(data.error || 'Could not suggest a reply.');
        }
    } catch (err) {
        alert('Connection error. Please try again.');
    } finally {
        btn.disabled = false;
        btn.textContent = originalText;
    }
}
</script>
</body>
</html>
