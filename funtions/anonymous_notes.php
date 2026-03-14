<?php
require_once 'config.php';
requireStudent();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle starting a NEW note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_new_note'])) {
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
    <title>Resilient Reflections — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <?php require_once 'pwa_head.php'; ?>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1400px; padding-top: 5rem; padding-bottom: 8rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 6rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem;">Secure Identity Mirror</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.5rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 0.75rem;">Resilient Reflections</h1>
            <p style="color: var(--text-dim); font-size: 1.25rem; font-weight: 600;">Share the weight of your thoughts in complete clinical anonymity.</p>
        </div>
        <div style="display: flex; gap: 1.5rem;">
            <div style="padding: 1rem 2.5rem; border-radius: 50px; background: #ecfdf5; color: #059669; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; gap: 0.75rem; border: 1px solid rgba(5, 150, 105, 0.1);">
                <span style="font-size: 1.1rem;">🛡️</span> Identity Encrypted
            </div>
            <div style="padding: 1rem 2.5rem; border-radius: 50px; background: #f5f3ff; color: var(--primary); font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; gap: 0.75rem; border: 1px solid var(--border);">
                <span style="font-size: 1.1rem;">👨‍⚕️</span> Clinical Support Queue Active
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 480px 1fr; gap: 5rem; align-items: start;">
        
        <!-- Clinical Forge Input -->
        <div style="background: white; border-radius: 48px; padding: 5rem 4rem; border: 1px solid var(--border); box-shadow: var(--shadow); position: sticky; top: 100px;">
            <div style="width: 60px; height: 60px; border-radius: 20px; background: var(--primary-glow); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 2.5rem;">✍️</div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--text);">New Reflection</h2>
            <p style="color: var(--text-dim); font-weight: 600; margin-bottom: 4rem; line-height: 1.8; font-size: 1.05rem;">Release your thoughts into the sanctuary. Our clinical team will analyze and respond with tailored support protocols shortly.</p>
            


            <form method="POST">
                <input type="hidden" name="send_new_note" value="1">
                <div style="position: relative; margin-bottom: 2rem;">
                    <textarea name="message" placeholder="What's weighing on you today?" required style="width: 100%; padding: 2.5rem; border-radius: 32px; border: 1px solid var(--border); font-family: inherit; font-size: 1.15rem; height: 350px; resize: none; background: #f8fafc; transition: var(--transition); line-height: 1.6;"></textarea>
                    <div style="position: absolute; bottom: 20px; right: 20px; font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Secure Link Active</div>
                </div>
                <button type="submit" style="width: 100%; background: var(--primary); color: white; border: none; padding: 1.5rem; border-radius: 50px; font-weight: 800; cursor: pointer; box-shadow: 0 15px 35px rgba(67, 56, 202, 0.2); transition: var(--transition); letter-spacing: 0.05em; font-size: 1.1rem;">SEND ANONYMOUSLY</button>
            </form>
        </div>

        <!-- Reflection Ledger -->
        <div>
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 4rem;">
                <div style="width: 12px; height: 32px; background: var(--primary); border-radius: 4px;"></div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 800; color: var(--primary-dark);">Diagnostic Dialogues</h2>
            </div>
            
            <?php if (empty($all_notes)): ?>
                <div style="padding: 10rem 4rem; text-align: center; background: #f8fafc; border-radius: 48px; border: 3px dashed var(--border);">
                    <div style="font-size: 6rem; margin-bottom: 3rem; opacity: 0.2;">🏮</div>
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 800; color: var(--text-dim); margin-bottom: 1.5rem;">Your sanctuary is currently silent.</h3>
                    <p style="color: var(--text-dim); font-size: 1.1rem; font-weight: 600;">Release your first reflection to begin an anonymous clinical dialogue.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 4rem;">
                    <?php foreach ($all_notes as $note): ?>
                        <div style="background: white; border-radius: 48px; padding: 4rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); transition: var(--transition);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4rem; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 2.5rem;">
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 10px; height: 10px; border-radius: 50%; background: var(--primary);"></div>
                                        <span style="font-weight: 800; font-size: 0.9rem; color: var(--primary); letter-spacing: 0.15em; text-transform: uppercase;">DIALOGUE #<?php echo str_pad($note['note_id'], 4, '0', STR_PAD_LEFT); ?></span>
                                    </div>
                                    <span style="font-size: 1rem; font-weight: 600; color: var(--text-dim);">Archived on <?php echo date('F d, Y', strtotime($note['created_at'])); ?></span>
                                </div>
                                <span style="padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 800; font-size: 0.75rem; background: <?php echo $note['status'] === 'replied' ? '#ecfdf5' : ($note['status'] === 'new' ? '#fffbeb' : '#f1f5f9'); ?>; color: <?php echo $note['status'] === 'replied' ? '#059669' : ($note['status'] === 'new' ? '#d97706' : '#64748b'); ?>; border: 1px solid currentColor; opacity: 0.8;">
                                    <?php echo strtoupper($note['status'] === 'replied' ? 'CLINICAL RESPONSE READY' : ($note['status'] === 'new' ? 'PENDING ANALYSIS' : $note['status'])); ?>
                                </span>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 2.5rem; margin-bottom: 4rem;">
                                <?php foreach ($note['messages'] as $msg): ?>
                                    <div style="max-width: 85%; align-self: <?php echo $msg['sender_type'] === 'student' ? 'flex-end' : 'flex-start'; ?>;">
                                        <div style="display: flex; flex-direction: column; gap: 0.75rem; align-items: <?php echo $msg['sender_type'] === 'student' ? 'flex-end' : 'flex-start'; ?>;">
                                            <div style="font-size: 0.75rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em;">
                                                <?php echo $msg['sender_type'] === 'student' ? 'Identity Protected' : 'Clinical Directives'; ?>
                                                · <?php echo date('g:i A', strtotime($msg['created_at'])); ?>
                                            </div>
                                            <div style="padding: 2.5rem; border-radius: <?php echo $msg['sender_type'] === 'student' ? '40px 40px 10px 40px' : '40px 40px 40px 10px'; ?>; background: <?php echo $msg['sender_type'] === 'student' ? 'linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%)' : '#f8fafc'; ?>; color: <?php echo $msg['sender_type'] === 'student' ? 'white' : 'var(--text)'; ?>; font-weight: 500; line-height: 1.8; font-size: 1.1rem; border: <?php echo $msg['sender_type'] === 'student' ? 'none' : '1px solid var(--border)'; ?>; box-shadow: <?php echo $msg['sender_type'] === 'student' ? '0 15px 30px rgba(67, 56, 202, 0.15)' : 'none'; ?>;">
                                                <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($note['status'] !== 'closed'): ?>
                                <div style="border-top: 1.5px solid #f1f5f9; padding-top: 3.5rem;">
                                    <form method="POST" style="display: flex; gap: 1.5rem; align-items: center;">
                                        <input type="hidden" name="send_reply" value="1">
                                        <input type="hidden" name="note_id" value="<?php echo $note['note_id']; ?>">
                                        <div style="flex: 1; position: relative;">
                                            <input type="text" name="reply_text" placeholder="Send an anonymous follow-up..." required style="width: 100%; padding: 1.75rem 3rem; border-radius: 60px; border: 1.5px solid var(--border); font-weight: 600; background: #f8fafc; font-size: 1.05rem; transition: var(--transition);">
                                        </div>
                                        <button type="submit" style="background: var(--primary); color: white; border: none; padding: 1.75rem 4rem; border-radius: 60px; font-weight: 800; cursor: pointer; box-shadow: 0 10px 20px rgba(67, 56, 202, 0.15); transition: var(--transition); font-size: 1rem; letter-spacing: 0.05em;">SEND</button>
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

<footer class="footer" style="padding: 4rem; text-align: center; border-top: 1px solid var(--border); margin-top: 4rem;">
    <p style="color: var(--text-dim); font-weight: 700; font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase;">© <?php echo date('Y'); ?> Mental Health Clinical Ecosystem. High-Fidelity Anonymity Mapping.</p>
</footer>

</main>
<?php include 'toast.php'; ?>
</body>
</html>
