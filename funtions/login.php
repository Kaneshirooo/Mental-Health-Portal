<?php
require_once 'config.php';

// AJAX login handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    $email    = trim($_POST['email'] ?? '');   // trim only — prepared stmt handles SQL safety
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember_me']);

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Email and password are required']);
        exit;
    }

    $query = "SELECT user_id, password, user_type, full_name FROM users WHERE email = ?";
    $stmt  = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (verifyPassword($password, $user['password'])) {
            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);

            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email']     = htmlspecialchars($email);

            if ($remember) {
                setcookie('rm_email', $email, time() + (86400 * 30), '/');
            } else {
                setcookie('rm_email', '', time() - 3600, '/');
            }

            $_SESSION['session_log_id'] = startSessionLog($user['user_id']);

            $redirect = match($user['user_type']) {
                'student'  => 'student_dashboard.php',
                'counselor'=> 'counselor_dashboard.php',
                default    => 'admin_dashboard.php',
            };
            echo json_encode(['success' => true, 'redirect' => $redirect]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Incorrect password. Please try again.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No account found with that email.']);
    }
    exit;
}

// Pre-fill from remember-me cookie
$remembered_email = $_COOKIE['rm_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background: #f0f2f5;
            background-image: 
                radial-gradient(at 0% 0%, rgba(67, 56, 202, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(3, 105, 161, 0.08) 0px, transparent 50%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .login-card {
            display: flex;
            width: 1000px;
            height: 650px;
            background: var(--surface-solid);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            animation: cardAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardAppear {
            from { transform: scale(0.9) translateY(20px); opacity: 0; }
            to { transform: scale(1) translateY(0); opacity: 1; }
        }

        .login-hero {
            flex: 1.25;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 60%, var(--primary-light) 100%);
            padding: 3.5rem;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            position: relative;
            overflow: hidden;
        }

        .login-hero::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }

        .login-form-area {
            flex: 1;
            padding: 4.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }

        .login-logo-container {
            width: 72px;
            height: 72px;
            background: white;
            border-radius: 18px;
            padding: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .login-logo-container:hover { transform: scale(1.05) rotate(5deg); }

        .login-logo {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 4px;
        }

        .auth-feature-list {
            margin-top: 3rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .auth-feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.05rem;
            opacity: 0.9;
        }

        .auth-feature-item i { font-size: 1.5rem; }

        .input-group { margin-bottom: 1.75rem; }
        .input-group label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.6rem; color: var(--text); }
        .input-group input { width: 100%; padding: 1rem 1.25rem; border-radius: 12px; border: 1.5px solid var(--border); transition: var(--transition); font-size: 1rem; }
        .input-group input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-glow); outline: none; }

        .btn-premium {
            background: var(--primary);
            color: white;
            padding: 1rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            border: none;
            cursor: pointer;
            width: 100%;
            transition: var(--transition);
            box-shadow: 0 10px 20px -5px rgba(67, 56, 202, 0.4);
            margin-top: 1rem;
        }

        .btn-premium:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(67, 56, 202, 0.5);
        }

        @media (max-width: 1024px) {
            .login-card { width: 95vw; height: auto; flex-direction: column; }
            .login-hero { padding: 3rem; }
            .login-form-area { padding: 3rem; }
        }
    </style>
    <?php require_once 'pwa_head.php'; ?>
</head>
<body>
<?php require_once 'pwa_banner.php'; ?>

<div class="login-card">
    <div class="login-hero">
        <div class="login-logo-container">
            <img src="logo/system_logo.jpg" alt="PSU Logo" class="login-logo">
        </div>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.5rem; font-weight: 800; line-height: 1.1; margin-bottom: 1.25rem;">
            Your Journey to <br><span style="color: #a7f3d0; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">Wellness</span> Starts Here.
        </h1>
        <p style="font-size: 1.25rem; opacity: 1; max-width: 400px; line-height: 1.6; font-weight: 500;">
            Secure, confidential, and compassionate mental health support for the PSU community.
        </p>

        <div class="auth-feature-list">
            <div style="font-size: 0.7rem; font-weight: 900; letter-spacing: 0.15em; text-transform: uppercase; opacity: 0.5; margin-bottom: 0.5rem;">Portal Access</div>

            <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                <div style="display: flex; align-items: center; gap: 0.9rem; background: rgba(255,255,255,0.12); border-radius: 14px; padding: 0.75rem 1rem;">
                    <span style="font-size: 1.2rem;">🎓</span>
                    <div>
                        <div style="font-weight: 800; font-size: 0.9rem;">Student</div>
                        <div style="font-size: 0.72rem; opacity: 0.7;">Assessments · AI Chat · Journals</div>
                    </div>
                    <span style="margin-left: auto; font-size: 0.65rem; background: rgba(167,243,208,0.3); color: #a7f3d0; padding: 0.2rem 0.6rem; border-radius: 50px; font-weight: 800;">ACTIVE</span>
                </div>

                <div style="display: flex; align-items: center; gap: 0.9rem; background: rgba(255,255,255,0.12); border-radius: 14px; padding: 0.75rem 1rem;">
                    <span style="font-size: 1.2rem;">🩺</span>
                    <div>
                        <div style="font-weight: 800; font-size: 0.9rem;">Counselor</div>
                        <div style="font-size: 0.72rem; opacity: 0.7;">Records · Appointments · Reports</div>
                    </div>
                    <span style="margin-left: auto; font-size: 0.65rem; background: rgba(167,243,208,0.3); color: #a7f3d0; padding: 0.2rem 0.6rem; border-radius: 50px; font-weight: 800;">ACTIVE</span>
                </div>


            </div>

            <div style="margin-top: 1rem; display: flex; align-items: center; gap: 0.6rem; opacity: 0.55; font-size: 0.8rem; font-weight: 600;">
                <span style="width: 8px; height: 8px; background: #fbbf24; border-radius: 50%; display: inline-block;"></span>
                Not logged in — please sign in to continue
            </div>
        </div>
    </div>

    <div class="login-form-area">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.5rem;">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 800; color: #0f172a; margin: 0;">
                Welcome Back
            </h2>
            <button id="installAppBtn" onclick="triggerInstall()" style="
                display: flex;
                align-items: center;
                gap: 0.5rem;
                background: #f0f4ff;
                border: 1.5px solid #c7d2fe;
                color: #4338ca;
                padding: 0.5rem 1rem;
                border-radius: 50px;
                font-weight: 700;
                font-size: 0.8rem;
                cursor: pointer;
                transition: all 0.2s;
                white-space: nowrap;
            " onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#f0f4ff'">
                📲 Install App
            </button>
        </div>
        <p style="color: #475569; margin-bottom: 2.5rem; font-weight: 500; margin-top: 0.5rem;">Please sign in to your dashboard</p>

        <!-- Install Guide Modal -->
        <div id="installModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.55); backdrop-filter:blur(8px); align-items:center; justify-content:center;">
            <div style="background:white; border-radius:28px; padding:2.5rem; max-width:380px; width:90%; box-shadow:0 40px 80px rgba(0,0,0,0.2); animation: cardAppear 0.3s ease;">
                <div style="text-align:center; margin-bottom:1.75rem;">
                    <div style="width:60px; height:60px; border-radius:16px; overflow:hidden; margin:0 auto 1rem; border:2px solid #e0e7ff;">
                        <img src="logo/system_logo.jpg" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <h3 style="font-family:'Outfit',sans-serif; font-weight:800; font-size:1.3rem; color:#0f172a; margin:0 0 0.25rem;">Install MH Portal</h3>
                    <p style="color:#64748b; font-size:0.85rem; font-weight:500; margin:0;">Add to your home screen for quick access</p>
                </div>

                <!-- Android Chrome Steps -->
                <div id="androidSteps" style="background:#f8fafc; border-radius:16px; padding:1.25rem; margin-bottom:1rem;">
                    <div style="font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; color:#4338ca; margin-bottom:0.75rem;">📱 Android (Chrome)</div>
                    <ol style="margin:0; padding-left:1.25rem; color:#334155; font-size:0.85rem; font-weight:600; line-height:1.9;">
                        <li>Tap the <strong>⋮ menu</strong> (top right)</li>
                        <li>Tap <strong>"Add to Home screen"</strong></li>
                        <li>Tap <strong>"Add"</strong> to confirm</li>
                    </ol>
                </div>

                <!-- iOS Safari Steps -->
                <div style="background:#f8fafc; border-radius:16px; padding:1.25rem; margin-bottom:1.5rem;">
                    <div style="font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; color:#4338ca; margin-bottom:0.75rem;">🍎 iPhone (Safari)</div>
                    <ol style="margin:0; padding-left:1.25rem; color:#334155; font-size:0.85rem; font-weight:600; line-height:1.9;">
                        <li>Tap the <strong>Share 🔗</strong> button (bottom bar)</li>
                        <li>Scroll down → tap <strong>"Add to Home Screen"</strong></li>
                        <li>Tap <strong>"Add"</strong> on top right</li>
                    </ol>
                </div>

                <button onclick="closeInstallModal()" style="width:100%; background:#4338ca; color:white; border:none; padding:0.85rem; border-radius:14px; font-weight:700; font-size:0.95rem; cursor:pointer;">Got it!</button>
            </div>
        </div>

        <!-- AJAX error/success alert -->
        <div id="loginAlert" style="display:none; margin-bottom: 2.25rem; padding: 1rem; border-radius: 12px; display: none; align-items: center; gap: 0.75rem;"></div>

        <form id="loginForm" novalidate>
            <div class="input-group">
                <label for="email" style="color: #1e293b;">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($remembered_email); ?>" placeholder="name@psu.edu" required autofocus style="color: #1e293b; font-weight: 500;">
            </div>

            <div class="input-group">
                <label for="password" style="color: #1e293b;">Password</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" placeholder="••••••••" required style="color: #1e293b; font-weight: 500;">
                    <button type="button" class="pw-toggle" id="pwToggle" style="top: 50%; right: 1.25rem; background: none; border: none; font-size: 1.2rem; transform: translateY(-50%); position: absolute; cursor: pointer; color: #64748b;">👁</button>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; font-size: 0.95rem;">
                <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; color: #334155; font-weight: 500;">
                    <input type="checkbox" id="remember_me" name="remember_me" <?php echo $remembered_email ? 'checked' : ''; ?> style="width: auto; cursor: pointer;"> Remember me
                </label>
                <a href="#" style="color: var(--primary); font-weight: 700; text-decoration: none;">Forgot password?</a>
            </div>

            <button type="submit" class="btn-premium" id="loginBtn">Sign In to Dashboard →</button>
        </form>

        <div style="margin-top: 2.5rem; text-align: center; color: #475569; font-size: 1rem; font-weight: 500;">
            New to the portal? <a href="register.php" style="color: var(--primary); font-weight: 800; text-decoration: none; border-bottom: 2px solid var(--primary-glow);">Create an Account</a>
        </div>
        
    </div>
</div>

<script>
// Password visibility toggle
document.getElementById('pwToggle').addEventListener('click', function() {
    const pw = document.getElementById('password');
    if (pw.type === 'password') { pw.type = 'text'; this.textContent = '🙈'; }
    else { pw.type = 'password'; this.textContent = '👁'; }
});

// ── Remember Me: restore password from localStorage ──
const RM_KEY = 'portal_rm_pw';
const emailField    = document.getElementById('email');
const passwordField = document.getElementById('password');
const rememberBox   = document.getElementById('remember_me');

// Pre-fill password if remember-me was previously checked
const savedPw = localStorage.getItem(RM_KEY);
if (savedPw && rememberBox.checked) {
    passwordField.value = savedPw;
}

// ── AJAX Login Form ──
const loginForm = document.getElementById('loginForm');
const loginBtn  = document.getElementById('loginBtn');
const loginAlert = document.getElementById('loginAlert');

function showAlert(msg, isError) {
    loginAlert.style.display = 'flex';
    loginAlert.style.background = isError ? '#fef2f2' : '#ecfdf5';
    loginAlert.style.border     = isError ? '1px solid #fee2e2' : '1px solid #bbf7d0';
    loginAlert.style.color      = isError ? '#991b1b' : '#065f46';
    loginAlert.innerHTML = (isError ? '⚠️ ' : '✅ ') + msg;
}

loginForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    const email    = emailField.value.trim();
    const password = passwordField.value;
    const remember = rememberBox.checked;

    if (!email || !password) {
        showAlert('Please enter your email and password.', true);
        return;
    }

    loginBtn.disabled = true;
    loginBtn.textContent = 'Signing in…';
    loginAlert.style.display = 'none';

    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);
    if (remember) formData.append('remember_me', '1');

    try {
        const res = await fetch('login.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            // Save or clear password from localStorage
            if (remember) {
                localStorage.setItem(RM_KEY, password);
            } else {
                localStorage.removeItem(RM_KEY);
            }
            showAlert('Login successful! Redirecting…', false);
            setTimeout(() => { window.location.href = data.redirect; }, 600);
        } else {
            showAlert(data.error || 'Login failed. Please try again.', true);
            loginBtn.disabled = false;
            loginBtn.textContent = 'Sign In to Dashboard →';
        }
    } catch (err) {
        showAlert('Connection error. Please try again.', true);
        loginBtn.disabled = false;
        loginBtn.textContent = 'Sign In to Dashboard →';
    }
});

// ── PWA Install Button ──
let _pwaPrompt = null;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    _pwaPrompt = e;
});

function triggerInstall() {
    if (_pwaPrompt) {
        // Native browser install prompt available
        _pwaPrompt.prompt();
        _pwaPrompt.userChoice.then(() => { _pwaPrompt = null; });
    } else {
        // Fallback: show manual guide modal
        openInstallModal();
    }
}

function openInstallModal() {
    const modal = document.getElementById('installModal');
    modal.style.display = 'flex';
}

function closeInstallModal() {
    const modal = document.getElementById('installModal');
    modal.style.display = 'none';
}

// Close modal on backdrop click
document.getElementById('installModal').addEventListener('click', function(e) {
    if (e.target === this) closeInstallModal();
});
</script>
</body>
</html>
