<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$user = getUserData($user_id);
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $full_name      = sanitize($_POST['full_name']);
        $contact_number = substr(sanitize($_POST['contact_number']), 0, 15); // Truncate to DB limit
        $current_password = $_POST['current_password'] ?? '';
        $new_password   = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, contact_number = ? WHERE user_id = ?");
        $update_stmt->bind_param("ssi", $full_name, $contact_number, $user_id);

        if ($update_stmt->execute()) {
            $_SESSION['full_name'] = $full_name;

            if (!empty($new_password)) {
                if (!verifyPassword($current_password, $user['password'])) {
                    $error = 'Current password is incorrect';
                    queueToast($error, 'error', 'Password Error');
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match';
                    queueToast($error, 'error', 'Password Error');
                } elseif (strlen($new_password) < 6) {
                    $error = 'Password must be at least 6 characters';
                    queueToast($error, 'error', 'Complexity Error');
                } else {
                    $hp  = hashPassword($new_password);
                    $pwd = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $pwd->bind_param("si", $hp, $user_id);
                    $pwd->execute();
                    $success = 'Profile and password updated successfully';
                    queueToast($success, 'success', 'Vault Updated');
                }
            } else {
                $success = 'Profile details updated successfully';
                queueToast($success, 'success', 'Vault Updated');
            }
        } else {
            throw new Exception("Execution failed");
        }
    } catch (Exception $e) {
        $error = 'Update Failed: ' . $e->getMessage();
        queueToast($error, 'error', 'System Error');
    }

    $user = getUserData($user_id);
}

// Avatar initials
$initials = '';
foreach (explode(' ', $user['full_name']) as $part) $initials .= strtoupper($part[0] ?? '');
$initials = substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <?php require_once 'pwa_head.php'; ?>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1200px; padding-top: 5rem; padding-bottom: 8rem;">
    
    <!-- Identity Header -->
    <div style="display: flex; align-items: center; gap: 3.5rem; margin-bottom: 6rem; background: white; padding: 4rem; border-radius: 40px; border: 1.5px solid var(--border); box-shadow: var(--shadow-sm); position: relative; overflow: hidden;">
        <div style="position: absolute; right: -50px; top: -50px; font-size: 15rem; opacity: 0.03; font-weight: 800;">VAULT</div>
        <div style="width: 140px; height: 140px; border-radius: 40px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 3.5rem; font-weight: 800; font-family: 'Outfit', sans-serif; box-shadow: 0 25px 50px rgba(79, 70, 229, 0.25); position: relative; z-index: 1;">
            <?php echo $initials; ?>
        </div>
        <div style="position: relative; z-index: 1;">
            <div style="font-weight: 800; color: var(--primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem;">Clinical Identity Dashboard</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.5rem; font-weight: 800; color: var(--primary-dark); margin-bottom: 1rem;"><?php echo htmlspecialchars($user['full_name']); ?></h1>
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div style="padding: 0.75rem 1.75rem; border-radius: 50px; background: #f8fafc; border: 1px solid var(--border); font-weight: 800; font-size: 0.85rem; color: var(--text-dim); display: flex; align-items: center; gap: 0.75rem;">
                    <span>📧</span> <?php echo htmlspecialchars($user['email']); ?>
                </div>
                <div style="padding: 0.75rem 1.75rem; border-radius: 50px; background: var(--primary); color: white; font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">
                    <?php echo $user['user_type']; ?>
                </div>
            </div>
        </div>
    </div>



    <!-- Two-column: personal info left, password right -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: start;">
        
        <!-- Personal info -->
        <div style="background: white; border-radius: 40px; padding: 4rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 800; margin-bottom: 3rem; color: var(--text);">Identity Credentials</h2>
            <form method="POST" id="profileForm">
                <div style="margin-bottom: 2.5rem;">
                    <label style="display: block; font-weight: 800; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1rem;">Official Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required style="width: 100%; padding: 1.25rem 2rem; border-radius: 50px; border: 1.5px solid var(--border); font-weight: 600; font-family: inherit; font-size: 1rem; background: #f8fafc;">
                </div>

                <div style="margin-bottom: 2.5rem;">
                    <label style="display: block; font-weight: 800; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; margin-bottom: 1rem;">Clinical Contact</label>
                    <input type="tel" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>" placeholder="+63 9XX XXX XXXX" style="width: 100%; padding: 1.25rem 2rem; border-radius: 50px; border: 1.5px solid var(--border); font-weight: 600; font-family: inherit; font-size: 1rem; background: #f8fafc;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 4rem;">
                    <div style="background: #f8fafc; padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border);">
                        <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.5rem;">REGISTRATION ID</div>
                        <div style="font-weight: 800; color: var(--text);"><?php echo htmlspecialchars($user['roll_number'] ?: 'N/A'); ?></div>
                    </div>
                    <div style="background: #f0fdf4; padding: 1.5rem; border-radius: 20px; border: 1px solid #dcfce7;">
                        <div style="font-size: 0.7rem; font-weight: 800; color: #16a34a; text-transform: uppercase; margin-bottom: 0.5rem;">STATUS</div>
                        <div style="font-weight: 800; color: #16a34a;">Clinical Verified</div>
                    </div>
                </div>

                <input type="hidden" name="current_password" id="hidden_current" value="">
                <input type="hidden" name="new_password" id="hidden_new" value="">
                <input type="hidden" name="confirm_password" id="hidden_confirm" value="">

                <button type="submit" style="width: 100%; padding: 1.25rem; border-radius: 50px; background: var(--primary); color: white; border: none; font-weight: 800; cursor: pointer; box-shadow: 0 10px 25px rgba(79, 70, 229, 0.2); transition: var(--transition);">UPDATE VAULT IDENTITY</button>
            </form>
        </div>

        <!-- Password change -->
        <div class="card" style="padding: 2.5rem; border-radius: 32px; background: #2d3748; color: white;">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 800; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem; color: white;">
                <span style="background: rgba(255,255,255,0.1); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem;">🔐</span>
                Security Logic
            </h2>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: 700; font-size: 0.75rem; color: rgba(255,255,255,0.6);">Current Passkey</label>
                <div style="position: relative;">
                    <input type="password" id="current_password" placeholder="Verify old password" style="width: 100%; padding: 1rem 3.5rem 1rem 1.25rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: white;"
                           oninput="syncHidden()">
                    <button type="button" onclick="togglePw('current_password',this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: white; cursor: pointer; opacity: 0.5;">👁</button>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: 700; font-size: 0.75rem; color: rgba(255,255,255,0.6);">New Passkey</label>
                <div style="position: relative;">
                    <input type="password" id="new_password" placeholder="Complexity required" style="width: 100%; padding: 1rem 3.5rem 1rem 1.25rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: white;"
                           oninput="checkStrength(this.value);syncHidden()">
                    <button type="button" onclick="togglePw('new_password',this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: white; cursor: pointer; opacity: 0.5;">👁</button>
                </div>
                <div style="height: 4px; background: rgba(255,255,255,0.1); border-radius: 10px; margin-top: 1rem; overflow: hidden;">
                    <div id="strengthBar" style="width: 0%; height: 100%; transition: var(--transition);"></div>
                </div>
                <div id="strengthLabel" style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; margin-top: 0.5rem; color: rgba(255,255,255,0.4);">Security Level</div>
            </div>

            <div class="form-group">
                <label style="font-weight: 700; font-size: 0.75rem; color: rgba(255,255,255,0.6);">Confirm Logic</label>
                <div style="position: relative;">
                    <input type="password" id="confirm_password" placeholder="Exact match only" style="width: 100%; padding: 1rem 3.5rem 1rem 1.25rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: white;"
                           oninput="checkMatch();syncHidden()">
                    <button type="button" onclick="togglePw('confirm_password',this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: white; cursor: pointer; opacity: 0.5;">👁</button>
                </div>
                <div id="matchMsg" style="font-size: 0.75rem; margin-top: 0.75rem; font-weight: 600;"></div>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> Mental Health Pre-Assessment System.</p>
</footer>

<script>
function togglePw(id, btn) {
    const el = document.getElementById(id);
    el.type  = el.type === 'password' ? 'text' : 'password';
    btn.textContent = el.type === 'password' ? '👁' : '🙈';
}

function syncHidden() {
    document.getElementById('hidden_current').value = document.getElementById('current_password').value;
    document.getElementById('hidden_new').value     = document.getElementById('new_password').value;
    document.getElementById('hidden_confirm').value = document.getElementById('confirm_password').value;
}

function checkStrength(val) {
    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');
    let s = 0;
    if (val.length >= 6) s++;
    if (val.length >= 10) s++;
    if (/[A-Z]/.test(val)) s++;
    if (/[0-9]/.test(val)) s++;
    if (/[^A-Za-z0-9]/.test(val)) s++;
    const levels = [
        {w:'20%',color:'#ef4444',text:'Weak'},
        {w:'40%',color:'#f97316',text:'Fair'},
        {w:'60%',color:'#f59e0b',text:'Moderate'},
        {w:'80%',color:'#10b981',text:'Strong'},
        {w:'100%',color:'#059669',text:'Very Strong'},
    ];
    const l = levels[Math.min(s, 4)];
    bar.style.width = l.w; bar.style.background = l.color;
    label.textContent = l.text;
}

function checkMatch() {
    const pw  = document.getElementById('new_password').value;
    const cpw = document.getElementById('confirm_password').value;
    const msg = document.getElementById('matchMsg');
    if (!cpw) { msg.textContent = ''; return; }
    msg.textContent = pw === cpw ? '✓ Passwords match' : '✗ Do not match';
    msg.className   = 'match-msg ' + (pw === cpw ? 'match-ok' : 'match-fail');
}
</script>
</main>
<?php include 'toast.php'; ?>
</body>
</html>
