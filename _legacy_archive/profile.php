<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$user = getUserData($user_id);
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $full_name = sanitize($_POST['full_name']);
        $contact_number = substr(sanitize($_POST['contact_number']), 0, 15); // Truncate to DB limit
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, contact_number = ? WHERE user_id = ?");
        $update_stmt->bind_param("ssi", $full_name, $contact_number, $user_id);

        if ($update_stmt->execute()) {
            $_SESSION['full_name'] = $full_name;

            if (!empty($new_password)) {
                if (!verifyPassword($current_password, $user['password'])) {
                    $error = 'Current password is incorrect';
                    queueToast($error, 'error', 'Password Error');
                }
                elseif ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match';
                    queueToast($error, 'error', 'Password Error');
                }
                elseif (strlen($new_password) < 6) {
                    $error = 'Password must be at least 6 characters';
                    queueToast($error, 'error', 'Complexity Error');
                }
                else {
                    $hp = hashPassword($new_password);
                    $pwd = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $pwd->bind_param("si", $hp, $user_id);
                    $pwd->execute();
                    $success = 'Profile and password updated successfully';
                    queueToast($success, 'success', 'Vault Updated');
                }
            }
            else {
                $success = 'Profile details updated successfully';
                queueToast($success, 'success', 'Vault Updated');
            }
        }
        else {
            throw new Exception("Execution failed");
        }
    }
    catch (Exception $e) {
        $error = 'Update Failed: ' . $e->getMessage();
        queueToast($error, 'error', 'System Error');
    }

    $user = getUserData($user_id);
}

// Avatar initials
$initials = '';
foreach (explode(' ', $user['full_name']) as $part)
    $initials .= strtoupper($part[0] ?? '');
$initials = substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css?v=2.1">
    <?php include 'theme_init.php'; ?>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="main-content">

<div class="container" style="max-width: 1000px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <!-- Identity Header -->
    <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 2.5rem; background: var(--surface-solid); padding: 2rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm); position: relative; overflow: hidden;">
        <div style="width: 80px; height: 80px; border-radius: var(--radius); background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; font-family: 'Outfit', sans-serif; box-shadow: 0 8px 20px rgba(13, 148, 136, 0.2); position: relative; z-index: 1; flex-shrink: 0;">
            <?php echo $initials; ?>
        </div>
        <div style="position: relative; z-index: 1;">
            <div style="font-weight: 600; color: var(--primary); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.35rem;">Profile Settings</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 700; color: var(--text); margin-bottom: 0.5rem;"><?php echo htmlspecialchars($user['full_name']); ?></h1>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="padding: 0.35rem 0.85rem; border-radius: 20px; background: var(--surface-2); border: 1px solid var(--border); font-weight: 600; font-size: 0.78rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.4rem;">
                    📧 <?php echo htmlspecialchars($user['email']); ?>
                </div>
                <div style="padding: 0.35rem 0.85rem; border-radius: 20px; background: var(--primary); color: white; font-weight: 600; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em;">
                    <?php echo $user['user_type']; ?>
                </div>
            </div>
        </div>
    </div>



    <!-- Two-column: personal info left, password right -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">
        
        <!-- Personal info -->
        <div style="background: var(--surface-solid); border-radius: var(--radius); padding: 2rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 1.75rem; color: var(--text);">Personal Details</h2>
            <form method="POST" id="profileForm">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.5rem;">Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required style="width: 100%; padding: 0.85rem 1.25rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 500; font-family: inherit; font-size: 0.95rem; background: var(--surface-2);">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.5rem;">Contact Number</label>
                    <input type="tel" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>" placeholder="+63 9XX XXX XXXX" style="width: 100%; padding: 0.85rem 1.25rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 500; font-family: inherit; font-size: 0.95rem; background: var(--surface-2);">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                    <div style="background: var(--surface-2); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                        <div style="font-size: 0.68rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; margin-bottom: 0.35rem; letter-spacing: 0.04em;">Student ID</div>
                        <div style="font-weight: 600; color: var(--text); font-size: 0.9rem;"><?php echo htmlspecialchars($user['roll_number'] ?: 'N/A'); ?></div>
                    </div>
                    <div style="background: #f0fdf4; padding: 1rem; border-radius: var(--radius-sm); border: 1px solid #dcfce7;">
                        <div style="font-size: 0.68rem; font-weight: 600; color: #16a34a; text-transform: uppercase; margin-bottom: 0.35rem; letter-spacing: 0.04em;">Status</div>
                        <div style="font-weight: 600; color: #16a34a; font-size: 0.9rem;">Verified</div>
                    </div>
                </div>

                <input type="hidden" name="current_password" id="hidden_current" value="">
                <input type="hidden" name="new_password" id="hidden_new" value="">
                <input type="hidden" name="confirm_password" id="hidden_confirm" value="">

                <button type="submit" style="width: 100%; padding: 0.85rem; border-radius: var(--radius-sm); background: var(--primary); color: white; border: none; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); transition: var(--transition); font-size: 0.9rem;">Save Changes</button>
            </form>
        </div>

        <!-- Password change -->
        <div class="card" style="padding: 2rem; border-radius: var(--radius); background: var(--surface-solid); color: var(--text); border: 1px solid var(--border);">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.6rem; color: var(--text); font-size: 1.15rem;">
                <span style="background: var(--surface-2); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">🔐</span>
                Change Password
            </h2>
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label style="font-weight: 600; font-size: 0.78rem; color: var(--text-dim); display: block; margin-bottom: 0.5rem;">Current Password</label>
                <div style="position: relative;">
                    <input type="password" id="current_password" placeholder="Enter current password" style="width: 100%; padding: 0.85rem 3rem 0.85rem 1.25rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--surface-2); color: var(--text); font-family: inherit;"
                           oninput="syncHidden()">
                    <button type="button" onclick="togglePw('current_password',this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text); cursor: pointer; opacity: 0.5;">👁</button>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label style="font-weight: 600; font-size: 0.78rem; color: var(--text-dim); display: block; margin-bottom: 0.5rem;">New Password</label>
                <div style="position: relative;">
                    <input type="password" id="new_password" placeholder="Min. 6 characters" style="width: 100%; padding: 0.85rem 3rem 0.85rem 1.25rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--surface-2); color: var(--text); font-family: inherit;"
                           oninput="checkStrength(this.value);syncHidden()">
                    <button type="button" onclick="togglePw('new_password',this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text); cursor: pointer; opacity: 0.5;">👁</button>
                </div>
                <div style="height: 3px; background: var(--border); border-radius: 6px; margin-top: 0.75rem; overflow: hidden;">
                    <div id="strengthBar" style="width: 0%; height: 100%; transition: var(--transition);"></div>
                </div>
                <div id="strengthLabel" style="font-size: 0.68rem; font-weight: 600; text-transform: uppercase; margin-top: 0.35rem; color: var(--text-dim);">Password Strength</div>
            </div>

            <div class="form-group">
                <label style="font-weight: 600; font-size: 0.78rem; color: var(--text-dim); display: block; margin-bottom: 0.5rem;">Confirm Password</label>
                <div style="position: relative;">
                    <input type="password" id="confirm_password" placeholder="Repeat new password" style="width: 100%; padding: 0.85rem 3rem 0.85rem 1.25rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--surface-2); color: var(--text); font-family: inherit;"
                           oninput="checkMatch();syncHidden()">
                    <button type="button" onclick="togglePw('confirm_password',this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text); cursor: pointer; opacity: 0.5;">👁</button>
                </div>
                <div id="matchMsg" style="font-size: 0.72rem; margin-top: 0.5rem; font-weight: 600;"></div>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> PSU Mental Health Portal</p>
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
