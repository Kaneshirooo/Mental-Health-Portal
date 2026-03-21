<?php
require_once 'config.php';
requireStudent();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle starting a NEW note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_new_note'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $message = trim($_POST['message'] ?? '');
    if (empty($message)) {
        $error = "Please enter a message.";
    } else {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO anonymous_notes (student_id, message, status) VALUES (?, ?, 'new')");
            $stmt->bind_param("is", $user_id, $message);
            $stmt->execute();
            $note_id = $conn->insert_id;

            $msg_stmt = $conn->prepare("INSERT INTO anonymous_note_messages (note_id, sender_type, message_text) VALUES (?, 'student', ?)");
            $msg_stmt->bind_param("is", $note_id, $message);
            $msg_stmt->execute();

            $conn->commit();
            $success = "Your note has been sent anonymously.";
            queueToast($success, 'success', 'Note Sent');
            logActivity($user_id, "Student sent a new anonymous quick note");
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to send note.";
            queueToast($error, 'error', 'Error');
        }
    }
}

// Handle REPLYING to an existing note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reply'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
    $note_id = intval($_POST['note_id']);
    $reply_text = trim($_POST['reply_text'] ?? '');
    
    if (empty($reply_text)) {
        $error = "Please enter a reply.";
    } else {
        $msg_stmt = $conn->prepare("INSERT INTO anonymous_note_messages (note_id, sender_type, message_text) VALUES (?, 'student', ?)");
        $msg_stmt->bind_param("is", $note_id, $reply_text);
        if ($msg_stmt->execute()) {
            $conn->query("UPDATE anonymous_notes SET status = 'new' WHERE note_id = $note_id");
            $success = "Your reply has been sent.";
            queueToast($success, 'success', 'Reply Sent');
            logActivity($user_id, "Student replied to anonymous note #$note_id");
        } else {
            $error = "Failed to send reply.";
            queueToast($error, 'error', 'Error');
        }
    }
}

// Fetch notes and their messages
$notes_query = "SELECT n.note_id, n.status, n.created_at 
                FROM anonymous_notes n 
                WHERE n.student_id = ? 
                ORDER BY n.created_at DESC";
$notes_stmt = $conn->prepare($notes_query);
$notes_stmt->bind_param("i", $user_id);
$notes_stmt->execute();
$notes_res = $notes_stmt->get_result();

$all_notes = [];
while ($note = $notes_res->fetch_assoc()) {
    $msg_query = "SELECT sender_type, message_text, created_at FROM anonymous_note_messages WHERE note_id = ? ORDER BY created_at ASC";
    $msg_stmt = $conn->prepare($msg_query);
    $msg_stmt->bind_param("i", $note['note_id']);
    $msg_stmt->execute();
    $note['messages'] = $msg_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $all_notes[] = $note;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Note — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content reveal">

<div class="container" style="max-width: 1100px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem;">
        <div>
            <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Anonymous Support</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Quick Note</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">Share what's on your mind, completely anonymously.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <div style="padding: 0.65rem 1.25rem; border-radius: var(--radius-sm); background: #ecfdf5; color: #059669; font-weight: 600; font-size: 0.82rem; display: flex; align-items: center; gap: 0.5rem; border: 1px solid rgba(5, 150, 105, 0.08);">
                🛡️ Identity Protected
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 380px 1fr; gap: 2.5rem; align-items: start;">
        
        <!-- New Note Form -->
        <div style="background: white; border-radius: var(--radius); padding: 2rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); position: sticky; top: 80px;">
            <div style="width: 44px; height: 44px; border-radius: var(--radius-sm); background: var(--primary-glow); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 1.25rem;">✍️</div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text);">New Note</h2>
            <p style="color: var(--text-muted); font-weight: 400; margin-bottom: 1.75rem; line-height: 1.6; font-size: 0.88rem;">Share your thoughts with a counselor. Your identity stays completely private.</p>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="send_new_note" value="1">
                <div style="margin-bottom: 1.25rem;">
                    <textarea name="message" placeholder="What's on your mind?" required style="width: 100%; padding: 1.25rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-family: inherit; font-size: 0.95rem; height: 200px; resize: none; background: var(--surface-2); transition: var(--transition); line-height: 1.6;"></textarea>
                </div>
                <button type="submit" style="width: 100%; background: var(--primary); color: white; border: none; padding: 0.85rem; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); transition: var(--transition); font-size: 0.9rem;">Send Anonymously →</button>
            </form>
        </div>

        <!-- Notes History -->
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: var(--text); margin: 0;">Conversations</h2>
                <span style="padding: 0.2rem 0.6rem; border-radius: 20px; background: var(--surface-2); font-size: 0.72rem; font-weight: 600; color: var(--text-dim);"><?php echo count($all_notes); ?></span>
            </div>
            
            <?php if (empty($all_notes)): ?>
                <div class="empty-state">
                    <span class="empty-icon">💭</span>
                    <h3 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem;">No notes yet</h3>
                    <p style="color: var(--text-dim); font-size: 0.88rem;">Write your first note to start an anonymous conversation with a counselor.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <?php foreach ($all_notes as $note): ?>
                        <div class="history-card" style="background: white; border-radius: var(--radius); padding: 1.75rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); transition: var(--transition);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <span style="font-weight: 600; font-size: 0.82rem; color: var(--text-dim);">Note #<?php echo str_pad($note['note_id'], 3, '0', STR_PAD_LEFT); ?></span>
                                    <span style="font-size: 0.78rem; color: var(--text-dim);">· <?php echo date('M d, Y', strtotime($note['created_at'])); ?></span>
                                </div>
                                <span class="status-badge" style="padding: 0.3rem 0.75rem; border-radius: 20px; font-weight: 600; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em; background: <?php echo $note['status'] === 'replied' ? '#ecfdf5' : '#fffbeb'; ?>; color: <?php echo $note['status'] === 'replied' ? '#059669' : '#d97706'; ?>; border: 1px solid <?php echo $note['status'] === 'replied' ? '#a7f3d0' : '#fde68a'; ?>;">
                                    <?php echo $note['status'] === 'replied' ? 'Replied' : 'Pending'; ?>
                                </span>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem;">
                                <?php foreach ($note['messages'] as $msg): ?>
                                    <div style="max-width: 90%; align-self: <?php echo $msg['sender_type'] === 'student' ? 'flex-end' : 'flex-start'; ?>;">
                                        <div style="font-size: 0.68rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.35rem;">
                                            <?php echo $msg['sender_type'] === 'student' ? 'You' : 'Counselor'; ?>
                                            · <?php echo date('g:i A', strtotime($msg['created_at'])); ?>
                                        </div>
                                        <div style="padding: 1rem 1.25rem; border-radius: <?php echo $msg['sender_type'] === 'student' ? 'var(--radius) var(--radius) 4px var(--radius)' : 'var(--radius) var(--radius) var(--radius) 4px'; ?>; background: <?php echo $msg['sender_type'] === 'student' ? 'var(--primary)' : 'var(--surface-2)'; ?>; color: <?php echo $msg['sender_type'] === 'student' ? 'white' : 'var(--text)'; ?>; font-weight: 400; line-height: 1.6; font-size: 0.9rem; border: <?php echo $msg['sender_type'] === 'student' ? 'none' : '1px solid var(--border)'; ?>;">
                                            <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($note['status'] !== 'closed'): ?>
                                <div style="border-top: 1px solid var(--border); padding-top: 1.25rem;">
                                    <form method="POST" style="display: flex; gap: 0.75rem; align-items: center;">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="send_reply" value="1">
                                        <input type="hidden" name="note_id" value="<?php echo $note['note_id']; ?>">
                                        <input type="text" name="reply_text" placeholder="Type a follow-up..." required style="flex: 1; padding: 0.75rem 1.25rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 400; background: var(--surface-2); font-size: 0.9rem; transition: var(--transition); font-family: inherit;">
                                        <button type="submit" style="background: var(--primary); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.15); transition: var(--transition); font-size: 0.85rem;">Send</button>
                                    </form>
                                </div>
                            <?php endif; ?>
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
<?php include 'toast.php'; ?>
</body>
</html>
