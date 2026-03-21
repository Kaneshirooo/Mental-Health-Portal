<?php
require_once 'config.php';

// If no temp user is in session, redirect to login
if (!isset($_SESSION['temp_user'])) {
    redirect('login.php');
}

$error = '';
$email = $_SESSION['temp_user']['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp_code'];

    if (time() > $_SESSION['otp_expiry']) {
        $error = "Verification code has expired. Please request a new one.";
    } elseif ($entered_otp == $_SESSION['otp_code']) {
        // Success! Finalize the login
        $temp_user = $_SESSION['temp_user'];
        
        // Security: Rotate session id to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['user_id']   = $temp_user['user_id'];
        $_SESSION['user_type'] = $temp_user['user_type'];
        $_SESSION['full_name'] = $temp_user['full_name'];
        $_SESSION['email']     = $temp_user['email'];

        // Clean up temp session variables
        unset($_SESSION['temp_user']);
        unset($_SESSION['otp_code']);
        unset($_SESSION['otp_expiry']);

        // Log the session
        $_SESSION['session_log_id'] = startSessionLog($temp_user['user_id']);

        // Redirect based on role
        if ($temp_user['user_type'] === 'admin') {
            redirect('admin_dashboard.php');
        } elseif ($temp_user['user_type'] === 'counselor') {
            redirect('counselor_dashboard.php');
        } else {
            redirect('student_dashboard.php');
        }
    } else {
        $error = "Incorrect verification code. Please try again.";
    }
}

// Handle Resend Request
if (isset($_GET['resend'])) {
    $new_otp = rand(100000, 999999);
    $_SESSION['otp_code'] = $new_otp;
    $_SESSION['otp_expiry'] = time() + (10 * 60);

    if (sendOTP($email, $new_otp)) {
        queueToast('A new verification code has been sent to your email.', 'info', 'Code Resent');
    } else {
        $error = "Failed to resend the code. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Login — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .otp-card {
            background: white;
            padding: 3rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 440px;
            max-width: 90vw;
            text-align: center;
            border: 1px solid var(--border);
            animation: cardFade 0.4s ease-out;
        }

        @keyframes cardFade {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .otp-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            display: inline-block;
            background: var(--primary-glow);
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 20px;
            color: var(--primary);
        }

        .otp-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text);
        }

        .otp-subtitle {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .otp-subtitle strong {
            color: var(--text);
        }

        .otp-input-group {
            margin-bottom: 2rem;
        }

        .otp-input {
            width: 100%;
            padding: 1rem;
            font-size: 2rem;
            letter-spacing: 0.75rem;
            text-align: center;
            border-radius: var(--radius-sm);
            border: 2px solid var(--border);
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            transition: var(--transition);
        }

        .otp-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-glow);
        }

        .btn-verify {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
            margin-bottom: 1.5rem;
        }

        .btn-verify:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(13, 148, 136, 0.25);
        }

        .resend-link {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .resend-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .resend-link a:hover {
            text-decoration: underline;
        }

        .error-msg {
            background: #fef2f2;
            color: #991b1b;
            padding: 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>

<div class="otp-card">
    <div class="otp-icon">✉️</div>
    <h1 class="otp-title">Verify Your Login</h1>
    <p class="otp-subtitle">
        We've sent a 6-digit verification code to<br>
        <strong><?php echo htmlspecialchars($email); ?></strong>
    </p>

    <?php if ($error): ?>
        <div class="error-msg">⚠️ <?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="otp-input-group">
            <input type="text" name="otp_code" class="otp-input" placeholder="000000" maxlength="6" required autofocus autocomplete="one-time-code">
        </div>
        <button type="submit" class="btn-verify">Verify & Sign In</button>
    </form>

    <div class="resend-link">
        Didn't receive the code? <a href="?resend=1">Resend Code</a>
    </div>
</div>

<?php include 'toast.php'; ?>

</body>
</html>
