<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize($_POST['full_name']);
    $roll_number = sanitize($_POST['student_id']);
    $user_type = 'student';
    $date_of_birth = sanitize($_POST['date_of_birth']);
    $gender = sanitize($_POST['gender']);
    $contact_number = sanitize($_POST['contact_number']);
    $department = sanitize($_POST['department']);

    if (empty($email) || empty($password) || empty($full_name)) {
        $error = 'Email, password, and full name are required';
        queueToast($error, 'warning', 'Missing Fields');
    }
    elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
        queueToast($error, 'warning', 'Validation Error');
    }
    elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $error = 'Password must be at least 8 characters long, contains an uppercase letter, and a special character';
        queueToast($error, 'warning', 'Security Requirement');
    }
    else {
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();

        if ($check_stmt->get_result()->num_rows > 0) {
            $error = 'Email already registered';
            queueToast($error, 'error', 'Email Taken');
        }
        else {
            $hashed_password = hashPassword($password);
            $insert_query = "INSERT INTO users (email, password, full_name, roll_number, user_type, date_of_birth, gender, contact_number, department)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("sssssssss", $email, $hashed_password, $full_name, $roll_number, $user_type, $date_of_birth, $gender, $contact_number, $department);

            if ($insert_stmt->execute()) {
                $success = 'Registration successful! You can now log in.';
                queueToast('Account created! You can now login.', 'success', 'Welcome Aboard 🎉', 6000);
            }
            else {
                $error = 'Registration failed. Please try again';
                queueToast($error, 'error', 'Registration Failed');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Mental Health Portal</title>
    <link rel="stylesheet" href="styles.css?v=2.3">
    <?php include 'theme_init.php'; ?>
    <style>
        body {
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            font-family: 'Inter', sans-serif;
        }

        .auth-card {
            display: flex;
            width: 1100px;
            max-width: 100%;
            max-height: 95vh;
            background: var(--surface-solid);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            animation: cardFade 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardFade {
            from { transform: scale(0.97); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .auth-hero {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 60%, #0ea5e9 100%);
            padding: 3.5rem;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .hero-pattern {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            opacity: 0.06;
            background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0);
            background-size: 28px 28px;
        }

        .auth-form-area {
            flex: 1.3;
            padding: 3rem 3.5rem;
            display: flex;
            flex-direction: column;
            background: var(--surface-solid);
            overflow-y: auto;
        }

        .form-section-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--primary);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .input-group { margin-bottom: 1.25rem; }
        .input-group label { display: block; font-weight: 600; font-size: 0.82rem; margin-bottom: 0.5rem; color: var(--text-muted); }
        .input-group input, .input-group select { 
            width: 100%; 
            padding: 0.85rem 1rem; 
            border-radius: var(--radius-sm); 
            border: 1.5px solid var(--border); 
            background: var(--surface-2);
            transition: var(--transition); 
            font-size: 0.95rem;
            color: var(--text);
            font-family: inherit;
        }
        .input-group input:focus, .input-group select:focus { 
            border-color: var(--primary); 
            background: var(--surface-solid);
            box-shadow: 0 0 0 3px var(--primary-glow); 
            outline: none; 
        }

        /* Password Visibility Toggle */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-wrapper input {
            padding-right: 3rem !important;
        }
        .password-toggle {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            padding: 0.4rem;
            cursor: pointer;
            color: var(--text-dim);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-fast);
            z-index: 10;
        }
        .password-toggle:hover {
            color: var(--primary);
            transform: scale(1.1);
        }
        .password-toggle svg {
            width: 1.25rem;
            height: 1.25rem;
            stroke-width: 2;
        }
        
        .btn-auth {
            background: var(--primary);
            color: white;
            padding: 0.85rem;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
            margin-top: 1.25rem;
        }
        .btn-auth:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(13, 148, 136, 0.25); background: var(--primary-dark); }

        .alert {
            padding: 0.85rem 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.25rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.9rem;
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fee2e2;
            color: #991b1b;
        }
        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        @media (max-width: 1024px) {
            .auth-card { 
                flex-direction: column; 
                max-height: none; 
            }
            .auth-hero { 
                padding: 2.5rem; 
            }
            .auth-form-area { 
                padding: 2.5rem; 
            }
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-hero">
            <div class="hero-pattern"></div>
            <div style="position: relative; z-index: 10;">
                <img src="logo/system_logo.jpg" alt="Logo" style="width: 56px; height: 56px; border-radius: 14px; border: 2px solid white; box-shadow: 0 8px 20px rgba(0,0,0,0.15); margin-bottom: 2rem;">
                <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 700; line-height: 1.15; margin-bottom: 1rem;">Join the<br>Circle of Care.</h1>
                <p style="font-size: 1.05rem; opacity: 0.85; line-height: 1.6; max-width: 380px; font-weight: 400;">Start your journey to mental wellness with professional support and guidance.</p>
                <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.15); display: flex; gap: 2.5rem;">
                    <div>
                        <div style="font-size: 1.3rem; font-weight: 700;">100%</div>
                        <div style="font-size: 0.7rem; opacity: 0.6; text-transform: uppercase;">Private</div>
                    </div>
                    <div>
                        <div style="font-size: 1.3rem; font-weight: 700;">24/7</div>
                        <div style="font-size: 0.7rem; opacity: 0.6; text-transform: uppercase;">Support</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="auth-form-area">
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Create Student Account</h2>
                <p style="color: var(--text-muted); margin-bottom: 2rem; font-weight: 400;">Already a member? <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Sign in here</a></p>

                <?php if ($success): ?>
                    <div class="alert alert-success">✨ <?php echo $success; ?> <a href="login.php" style="font-weight:600;color:var(--primary);margin-left:0.5rem;">Go to Login →</a></div>
                <?php
endif; ?>

                <form method="POST">
                    <div class="form-section-label">Identity Details</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="input-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" placeholder="John Doe" required>
                        </div>
                        <div class="input-group">
                            <label>Student ID</label>
                            <input type="text" name="student_id" placeholder="ID Number" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="input-group">
                            <label>Email Address</label>
                            <input type="email" name="email" placeholder="student@school.edu" required>
                        </div>
                        <div class="input-group">
                            <label>Contact Number</label>
                            <input type="tel" name="contact_number" placeholder="+63 9XX XXX XXXX">
                        </div>
                    </div>

                    <div class="form-section-label">Personal Profile</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="input-group">
                            <label>Date of Birth</label>
                            <input type="date" name="date_of_birth">
                        </div>
                        <div class="input-group">
                            <label>Gender</label>
                            <select name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Department</label>
                        <input type="text" name="department" placeholder="e.g. Engineering, Arts, Science">
                    </div>
                    <input type="hidden" name="user_type" value="student">

                    <div class="form-section-label">Security Credentials</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="input-group">
                            <label>Create Password</label>
                            <div class="password-wrapper">
                                <input type="password" name="password" id="password" required placeholder="Min. 6 characters">
                                <button type="button" class="password-toggle" data-target="password">
                                    <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Verify Password</label>
                            <div class="password-wrapper">
                                <input type="password" name="confirm_password" id="confirm_password" required placeholder="Repeat password">
                                <button type="button" class="password-toggle" data-target="confirm_password">
                                    <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-auth" style="width: 100%;">Create Account</button>
                    
                    <p style="text-align: center; margin-top: 1.25rem; font-size: 0.75rem; color: var(--text-dim); line-height: 1.5;">
                        By registering, you agree to our <strong>Terms of Service</strong> and <strong>Privacy Policy</strong>. Your data is encrypted and handled only by authorized practitioners.
                    </p>
                </form>
            </div>
        </div>
    </div>
<?php include 'toast.php'; ?>
<script>
document.querySelectorAll('.password-toggle').forEach(button => {
    button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = this.querySelector('svg');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        }
    });
});
</script>
</body>
</html>
