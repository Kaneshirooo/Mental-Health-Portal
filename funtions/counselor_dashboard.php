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
        } else {
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
    $msg_res = $conn->query("SELECT sender_type, message_text, created_at FROM anonymous_note_messages WHERE note_id = {$row['note_id']} ORDER BY created_at ASC");
    $row['conversation'] = $msg_res->fetch_all(MYSQLI_ASSOC);
    $anon_notes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinical Command Center — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <?php require_once 'pwa_head.php'; ?>
    <style>
        .stats-matrix {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 2rem;
            margin-bottom: 6rem;
        }
        .stat-card-clinical {
            background: white;
            border-radius: 40px;
            padding: 3rem 2.5rem;
            border: 1.5px solid var(--border);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        .stat-card-clinical:hover { transform: translateY(-5px); box-shadow: var(--shadow); border-color: var(--primary-light); }
        
        .intervention-registry {
            background: white;
            border-radius: 48px;
            padding: 4rem;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            margin-bottom: 6rem;
        }
        .registry-table th { 
            padding: 2rem;
            font-size: 0.8rem;
            font-weight: 800;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            background: #f8fafc;
            border-bottom: 2px solid var(--border);
        }
        .registry-table td { 
            padding: 2rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.95rem;
            font-weight: 600;
        }
        
        .dialog-vault {
            background: #f8fafc;
            border-radius: 40px;
            padding: 3.5rem;
            border: 1.5px solid var(--border);
            margin-bottom: 3rem;
        }
        .message-bubble-counselor {
            padding: 1.5rem 2rem;
            border-radius: 24px;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            max-width: 85%;
            font-weight: 600;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<main class="main-content">

<div class="container" style="max-width: 1550px; padding-top: 6rem; padding-bottom: 10rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 6rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1.5rem;">Institutional Oversight</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.8rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 1rem; letter-spacing: -0.02em;">Clinical Command Center</h1>
            <p style="color: var(--text-dim); font-size: 1.35rem; font-weight: 600;">System is processing. <?php echo count($anon_notes); ?> dialogues require synthesis.</p>
        </div>
        <div style="display: flex; gap: 2rem;">
            <div style="text-align: right; padding: 0 3rem; border-right: 2px solid var(--border);">
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--risk-critical); line-height: 1;"><?php echo $stats['critical_risk'] ?? 0; ?></div>
                <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em; margin-top: 0.5rem;">CRITICAL CASES</div>
            </div>
            <a href="counselor_appointments.php" style="padding: 1.5rem 3rem; border-radius: 60px; background: var(--primary); color: white; font-weight: 800; text-decoration: none; box-shadow: 0 20px 40px rgba(79, 70, 229, 0.2); font-size: 0.9rem;">VIEW SCHEDULE MATRIX</a>
        </div>
    </div>

    <!-- Stats Matrix -->
    <div class="stats-matrix">
        <div class="stat-card-clinical">
            <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1.5rem;">TOTAL ENROLLMENT</div>
            <div style="font-size: 3rem; font-weight: 800; color: var(--text); line-height: 1;"><?php echo $stats['total_students'] ?? 0; ?></div>
        </div>
        <div class="stat-card-clinical" style="background: #f0fdf4; border-color: #dcfce7;">
            <div style="font-size: 0.8rem; font-weight: 800; color: #16a34a; text-transform: uppercase; margin-bottom: 1.5rem;">STEADY STATE</div>
            <div style="font-size: 3rem; font-weight: 800; color: #16a34a; line-height: 1;"><?php echo $stats['low_risk'] ?? 0; ?></div>
        </div>
        <div class="stat-card-clinical" style="background: #fefce8; border-color: #fef08a;">
            <div style="font-size: 0.8rem; font-weight: 800; color: #ca8a04; text-transform: uppercase; margin-bottom: 1.5rem;">MODERATE LOAD</div>
            <div style="font-size: 3rem; font-weight: 800; color: #ca8a04; line-height: 1;"><?php echo $stats['moderate_risk'] ?? 0; ?></div>
        </div>
        <div class="stat-card-clinical" style="background: #fff7ed; border-color: #ffedd5;">
            <div style="font-size: 0.8rem; font-weight: 800; color: #ea580c; text-transform: uppercase; margin-bottom: 1.5rem;">HIGH PRIORITY</div>
            <div style="font-size: 3rem; font-weight: 800; color: #ea580c; line-height: 1;"><?php echo $stats['high_risk'] ?? 0; ?></div>
        </div>
        <div class="stat-card-clinical" style="background: #fef2f2; border-color: #fee2e2;">
            <div style="font-size: 0.8rem; font-weight: 800; color: #dc2626; text-transform: uppercase; margin-bottom: 1.5rem;">CRITICAL INTERVENTION</div>
            <div style="font-size: 3rem; font-weight: 800; color: #dc2626; line-height: 1;"><?php echo $stats['critical_risk'] ?? 0; ?></div>
        </div>
    </div>

    <!-- Intervention Registry -->
    <div class="intervention-registry">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 2rem;">
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem;">Priority Intervention Registry</h2>
                <p style="color: var(--text-dim); font-weight: 600; font-size: 1.1rem;">Immediate clinical action required for elevated risk trajectories.</p>
            </div>
            <a href="student_list.php" style="font-weight: 800; color: var(--primary); text-decoration: none; font-size: 0.95rem;">COMPLETE REGISTRY →</a>
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
                        <td colspan="5" style="padding: 6rem; text-align: center;">
                            <div style="font-size: 4rem; margin-bottom: 2rem;">✨</div>
                            <div style="font-weight: 800; color: var(--text-dim); font-size: 1.25rem;">All priority cases have been successfully synthesized.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while ($student = $risk_result->fetch_assoc()): ?>
                    <tr onmouseover="this.style.background='#fcfdfe';" onmouseout="this.style.background='white';" style="transition: var(--transition);">
                        <td>
                            <div style="display: flex; align-items: center; gap: 1.5rem;">
                                <div style="width: 50px; height: 50px; border-radius: 16px; background: #f1f5f9; color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; border: 1px solid var(--border);">
                                    <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div style="font-weight: 800; color: var(--text); font-size: 1.1rem;"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-dim); font-weight: 700;"><?php echo $student['roll_number']; ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="flex: 1; height: 8px; background: #f1f5f9; border-radius: 10px; overflow: hidden; max-width: 150px;">
                                    <div style="width: <?php echo $student['overall_score']; ?>%; height: 100%; background: <?php echo ($student['risk_level']==='Critical') ? '#dc2626' : 'var(--primary)'; ?>;"></div>
                                </div>
                                <span style="font-weight: 800; color: var(--text);"><?php echo $student['overall_score']; ?>%</span>
                            </div>
                        </td>
                        <td>
                            <span style="padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; background: <?php echo ($student['risk_level']==='Critical') ? '#fee2e2' : '#fff7ed'; ?>; color: <?php echo ($student['risk_level']==='Critical') ? '#dc2626' : '#ea580c'; ?>; border: 1px solid rgba(220, 38, 38, 0.1);">
                                <?php echo $student['risk_level']; ?>
                            </span>
                        </td>
                        <td style="color: var(--text-dim);"><?php echo date('M d, Y', strtotime($student['assessment_date'])); ?></td>
                        <td style="text-align: center;">
                            <a href="student_profile.php?user_id=<?php echo $student['user_id']; ?>" style="display: inline-block; padding: 1rem 2rem; border-radius: 14px; background: white; border: 1.5px solid var(--border); text-decoration: none; font-weight: 800; color: var(--primary); font-size: 0.9rem; transition: var(--transition);" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='var(--primary-glow)';" onmouseout="this.style.borderColor='var(--border)'; this.style.background='white';">ANALYZE CASE</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Reflections Synthesis -->
    <div style="background: white; border-radius: 48px; border: 1px solid var(--border); padding: 4rem; box-shadow: var(--shadow);">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 800; color: var(--text); margin-bottom: 4rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 2rem;">Anonymous Dialogue Synthesis</h2>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 3rem;">
            <?php foreach ($anon_notes as $note): ?>
            <div class="dialog-vault">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; border-bottom: 1.5px solid #e2e8f0; padding-bottom: 2rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 12px; height: 12px; border-radius: 50%; background: var(--primary); box-shadow: 0 0 10px var(--primary);"></div>
                        <span style="font-weight: 800; font-size: 0.9rem; letter-spacing: 0.1em;">ENCRYPTED ID #<?php echo str_pad($note['note_id'], 4, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div style="display: flex; gap: 0.75rem;">
                        <span style="padding: 0.6rem 1.2rem; border-radius: 50px; background: white; border: 1px solid var(--border); font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">
                            <?php echo $note['status']; ?>
                        </span>
                        <a href="?archive_note=<?php echo $note['note_id']; ?>" style="text-decoration: none; padding: 0.6rem 1.2rem; border-radius: 50px; background: #f1f5f9; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">ARCHIVE</a>
                    </div>
                </div>

                <div style="max-height: 400px; overflow-y: auto; padding-right: 1.5rem; margin-bottom: 3rem;" class="custom-scroll">
                    <?php foreach ($note['conversation'] as $msg): ?>
                    <div style="margin-bottom: 2rem; display: flex; flex-direction: column; align-items: <?php echo ($msg['sender_type']==='student')?'flex-start':'flex-end'; ?>;">
                        <div style="font-weight: 800; font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.05em;">
                            <?php echo ($msg['sender_type']==='student')?'Patient Sanctuary':'Dr. '.explode(' ', $_SESSION['full_name'])[0]; ?>
                        </div>
                        <div class="message-bubble-counselor" style="<?php echo ($msg['sender_type']==='student')?'background: white; border: 1.5px solid var(--border); color: var(--text); border-bottom-left-radius: 4px;':'background: var(--primary); color: white; border-bottom-right-radius: 4px;'; ?>">
                            <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <form method="POST" style="display: flex; gap: 1rem;">
                    <input type="hidden" name="reply_note_submit" value="1">
                    <input type="hidden" name="reply_note_id" value="<?php echo $note['note_id']; ?>">
                    <textarea name="reply_text" placeholder="Formulate clinical synthesis..." style="flex: 1; padding: 1.5rem 2rem; border-radius: 24px; border: 2px solid var(--border); font-weight: 600; background: white; font-family: inherit; font-size: 0.95rem; line-height: 1.6; resize: none; height: 80px;" required></textarea>
                    <button type="submit" style="background: var(--primary); color: white; border: none; padding: 0 3rem; border-radius: 24px; font-weight: 800; cursor: pointer; transition: var(--transition);" onmouseover="this.style.transform='scale(1.02)';" onmouseout="this.style.transform='scale(1)';">TRANSMIT</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<footer class="footer" style="padding: 4rem; text-align: center; border-top: 1px solid var(--border); margin-top: 4rem;">
    <p style="color: var(--text-dim); font-weight: 700; font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase;">© <?php echo date('Y'); ?> Mental Health Clinical Command. High-Fidelity Professional Oversight.</p>
</footer>

</main>
</body>
</html>
