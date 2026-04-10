<?php
require_once 'config.php';

$error = '';

// Handle traditional login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $ip = $_SERVER['REMOTE_ADDR'];

    // Check if account is temporarily locked (5 failures in 15 mins)
    if (checkLoginBruteForce($ip)) {
        $error = "Too many failed attempts. Please try again in 15 minutes.";
    }
    else {
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (verifyPassword($password, $user['password'])) {
                // Successful login – (Optional: clear attempts if you want per-user)
                // Generate 6-digit OTP
                $otp = rand(100000, 999999);

                // Store temp data in session
                $_SESSION['temp_user'] = [
                    'user_id' => $user['user_id'],
                    'user_type' => $user['user_type'],
                    'full_name' => $user['full_name'],
                    'email' => $user['email']
                ];
                $_SESSION['otp_code'] = $otp;
                $_SESSION['otp_expiry'] = time() + (10 * 60); // 10 minutes

                // Send OTP Email
                if (sendOTP($user['email'], $otp)) {
                    redirect('verify_otp.php');
                }
                else {
                    $error = "Failed to send verification code. Please check your SMTP settings.";
                }
            }
            else {
                $error = "Invalid email or password.";
                registerLoginAttempt($ip);
            }
        }
        else {
            $error = "Invalid email or password.";
            registerLoginAttempt($ip);
        }
    }
}
// Build Google OAuth URL — restricted to @psu.edu.ph accounts only
$googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'access_type' => 'online',
    'prompt' => 'select_account',
    'hd' => 'psu.edu.ph',
]);

if (empty($error) && isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css?v=2.3">
    <?php include 'theme_init.php'; ?>
    <style>
        body {
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
            font-family: 'Inter', sans-serif;
        }

        .login-card {
            display: flex;
            width: 1000px;
            max-width: 100%;
            height: 620px;
            background: var(--surface-solid);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            animation: cardAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardAppear {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* ── Left Side (Hero) ── */
        .login-hero {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 50%, #0ea5e9 100%);
            padding: 3.5rem;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .hero-logo {
            width: 48px;
            height: 48px;
            background: var(--surface-solid);
            border-radius: 50%;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .hero-logo img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 600;
            line-height: 1.25;
            margin-bottom: 1.25rem;
        }
        .hero-title b { font-weight: 800; font-size: 2.25rem; display: block; }

        .hero-text {
            font-size: 1rem;
            opacity: 0.9;
            line-height: 1.6;
            font-weight: 400;
            max-width: 320px;
            margin-bottom: 3rem;
        }

        .portal-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            opacity: 0.6;
            margin-bottom: 1.25rem;
        }

        .portal-item {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 0.85rem 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .portal-icon { font-size: 1.2rem; filter: grayscale(1); }
        .portal-info { flex: 1; }
        .portal-name { font-weight: 700; font-size: 0.9rem; margin-bottom: 0.1rem; }
        .portal-tags { font-size: 0.65rem; opacity: 0.7; font-weight: 500; }
        .portal-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            font-size: 0.55rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* ── Right Side (Form) ── */
        .login-form-area {
            flex: 1.1;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--surface-solid);
        }

        .form-header { margin-bottom: 2.5rem; }
        .form-title { font-family: 'Outfit', sans-serif; font-size: 1.85rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem; }
        .form-subtitle { color: var(--text-muted); font-size: 1rem; font-weight: 400; }

        .input-group { margin-bottom: 1.5rem; }
        .input-group label { display: block; font-weight: 600; font-size: 0.82rem; margin-bottom: 0.6rem; color: var(--text); }
        .input-group input { 
            width: 100%; 
            padding: 0.95rem 1.1rem; 
            border-radius: 10px; 
            border: 1.5px solid var(--border); 
            background: var(--surface-2);
            transition: all 0.25s ease; 
            font-size: 0.95rem;
            color: var(--text);
            font-family: inherit;
        }
        .input-group input:focus { 
            border-color: var(--primary); 
            background: var(--surface-solid);
            box-shadow: 0 0 0 4px var(--primary-glow); 
            outline: none; 
        }

        .password-wrapper { position: relative; }
        .password-wrapper input { padding-right: 3rem !important; }
        .password-toggle {
            position: absolute; right: 0.75rem; top: 11px;
            background: none; border: none; padding: 0.4rem;
            cursor: pointer; color: #94a3b8;
            display: flex; align-items: center;
        }
        .password-toggle:hover { color: #0d9488; }
        .password-toggle svg { width: 1.2rem; height: 1.2rem; }

        .btn-sign-in {
            background: var(--primary);
            color: white;
            padding: 1rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: all 0.25s ease;
            width: 100%;
            margin-top: 1rem;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.15);
        }
        .btn-sign-in:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 6px 16px var(--primary-glow); }

        .or-divider {
            display: flex; align-items: center; gap: 1rem; margin: 2rem 0;
            color: #94a3b8; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em;
        }
        .or-divider::before, .or-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        .btn-google {
            display: flex; align-items: center; justify-content: center; gap: 0.75rem;
            width: 100%; padding: 0.9rem; border-radius: 12px;
            background: var(--surface-solid); border: 1.5px solid var(--border);
            color: var(--text); text-decoration: none; font-weight: 700; font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        .btn-google:hover { background: var(--surface-2); border-color: var(--border-hover); }
        .google-icon { width: 18px; height: 18px; }

        .signup-prompt { text-align: center; margin-top: 2rem; font-size: 0.85rem; color: var(--text-muted); }
        .signup-link { color: var(--primary); font-weight: 700; text-decoration: none; }
        .signup-link:hover { text-decoration: underline; color: var(--primary-light); }

        .error-alert {
            background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444;
            padding: 0.85rem 1rem; border-radius: 10px; margin-bottom: 2rem;
            font-size: 0.88rem; font-weight: 500; display: flex; align-items: center; gap: 0.75rem;
        }

        @media (max-width: 1024px) {
            .login-card { flex-direction: column; width: 100%; height: auto; border-radius: 0; }
            .login-hero { padding: 3rem 2rem; }
            .login-form-area { padding: 3rem 2rem; }
        }
    </style>
</head>
<body>

<div class="login-card">
    <!-- ── Hero ── -->
    <div class="login-hero">
        <div>
            <div class="hero-logo">
                <img src="logo/system_logo.jpg" alt="PSU Logo">
            </div>
            <h1 class="hero-title">Your Journey to Wellness <br><b>Starts Here.</b></h1>
            <p class="hero-text">Secure, confidential, and compassionate mental health support for the PSU community.</p>
        </div>

        <div>
            <div class="portal-label">Portal Access</div>
            
            <div class="portal-item">
                <span class="portal-icon">🎓</span>
                <div class="portal-info">
                    <div class="portal-name">Student</div>
                    <div class="portal-tags">Assessments - AI Chat - Journals</div>
                </div>
                <span class="portal-badge">Active</span>
            </div>

            <div class="portal-item">
                <span class="portal-icon">🩺</span>
                <div class="portal-info">
                    <div class="portal-name">Counselor</div>
                    <div class="portal-tags">Records - Appointments - Reports</div>
                </div>
                <span class="portal-badge">Active</span>
            </div>
        </div>
    </div>

    <!-- ── Form ── -->
    <div class="login-form-area">
        <div class="form-header">
            <h2 class="form-title">Welcome Back</h2>
            <p class="form-subtitle">Enter your institutional credentials to continue</p>
        </div>

        <?php if ($error): ?>
            <div class="error-alert">
                <span>⚠️</span>
                <span><?php echo $error; ?></span>
            </div>
        <?php
endif; ?>

        <form method="POST" id="loginForm">
            <div class="input-group">
                <label for="email">Institutional Email</label>
                <input type="email" id="email" name="email" placeholder="username@psu.edu.ph" required autofocus autocomplete="email">
            </div>
            
            <div class="input-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" class="password-toggle" id="togglePassword">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>
            
            <button type="submit" name="login" class="btn-sign-in">Sign In</button>
        </form>

        <div class="or-divider">Or sign in with</div>

        <a href="<?php echo $googleAuthUrl; ?>" class="btn-google">
            <svg class="google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            <span>Google Institutional Account</span>
        </a>

        <div class="signup-prompt">
            Don't have an account? <a href="register.php" class="signup-link">Sign up here</a>
        </div>
    </div>
</div>

<?php include 'toast.php'; ?>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', function() {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            this.querySelector('svg').innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
        } else {
            passwordInput.type = 'password';
            this.querySelector('svg').innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        }
    });

    // Auto-focus email input
    window.addEventListener('DOMContentLoaded', () => {
        const emailInput = document.getElementById('email');
        if (emailInput && !emailInput.value) emailInput.focus();
    });
</script>

</body>
</html>
