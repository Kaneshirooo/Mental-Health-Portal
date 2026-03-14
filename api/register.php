<?php
require_once 'config.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email          = sanitize($_POST['email']);
    $password       = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name      = sanitize($_POST['full_name']);
    $roll_number    = sanitize($_POST['student_id']);
    $user_type      = 'student'; // Only students can self-register
    $date_of_birth  = sanitize($_POST['date_of_birth']);
    $gender         = sanitize($_POST['gender']);
    $contact_number = sanitize($_POST['contact_number']);
    $department     = sanitize($_POST['department']);

    if (empty($email) || empty($password) || empty($full_name)) {
        $error = 'Email, password, and full name are required';
        queueToast($error, 'warning', 'Missing Fields');
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
        queueToast($error, 'warning', 'Validation Error');
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();

        if ($check_stmt->get_result()->num_rows > 0) {
            $error = 'Email already registered';
            queueToast($error, 'error', 'Email Taken');
        } else {
            $hashed_password = hashPassword($password);
            $insert_query = "INSERT INTO users (email, password, full_name, roll_number, user_type, date_of_birth, gender, contact_number, department)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("sssssssss", $email, $hashed_password, $full_name, $roll_number, $user_type, $date_of_birth, $gender, $contact_number, $department);

            if ($insert_stmt->execute()) {
                $success = 'Registration successful! You can now log in.';
                queueToast('Account created! You can now login.', 'success', 'Welcome Aboard 🎉', 6000);
            } else {
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
    <link rel="stylesheet" href="styles.css">
    <?php require_once 'pwa_head.php'; ?>
    <style>
        body {
            background: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(67, 56, 202, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(3, 105, 161, 0.05) 0px, transparent 50%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
            font-family: 'Inter', sans-serif;
        }

        .auth-card {
            display: flex;
            width: 1200px;
            max-width: 100%;
            height: 850px;
            max-height: 95vh;
            background: white;
            border-radius: 40px;
            overflow: hidden;
            box-shadow: 0 40px 100px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
            animation: cardFade 1s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardFade {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .auth-hero {
            flex: 1;
            background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%);
            padding: 5rem;
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
            opacity: 0.1;
            background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0);
            background-size: 32px 32px;
        }

        .auth-form-area {
            flex: 1.3;
            padding: 4rem 5rem;
            display: flex;
            flex-direction: column;
            background: white;
            overflow-y: auto;
        }

        .form-section-label {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--primary);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .form-section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #f1f5f9;
        }

        .input-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem; /* Adjusted gap for better spacing */
        }

        .input-group { margin-bottom: 1.75rem; }
        .input-group label { display: block; font-weight: 700; font-size: 0.85rem; margin-bottom: 0.75rem; color: #475569; }
        .input-group input, .input-group select { 
            width: 100%; 
            padding: 1.1rem 1.25rem; 
            border-radius: 16px; 
            border: 1.5px solid #e2e8f0; 
            background: #f8fafc;
            transition: all 0.3s ease; 
            font-size: 1rem;
            color: #1e293b;
        }
        .input-group input:focus, .input-group select:focus { 
            border-color: var(--primary); 
            background: white;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); 
            outline: none; 
        }
        
        .btn-auth {
            background: var(--primary);
            color: white;
            padding: 1.25rem;
            border-radius: 50px;
            font-weight: 800;
            font-size: 1.1rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(67, 56, 202, 0.2);
            margin-top: 2rem;
        }
        .btn-auth:hover { transform: translateY(-2px); box-shadow: 0 15px 35px rgba(67, 56, 202, 0.3); background: var(--primary-dark); }

        /* Alert styles */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
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
                height: auto; 
                max-height: none; 
                border-radius: 24px;
            }
            .auth-hero { 
                padding: 3rem; 
                border-radius: 24px 24px 0 0;
            }
            .auth-form-area { 
                padding: 3rem; 
                border-radius: 0 0 24px 24px;
            }
            .input-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-hero">
            <div class="hero-pattern"></div>
            <div style="position: relative; z-index: 10;">
                <img src="logo/system_logo.jpg" alt="Logo" style="width: 80px; height: 80px; border-radius: 20px; border: 4px solid white; box-shadow: 0 20px 40px rgba(0,0,0,0.2); margin-bottom: 3rem;">
                <h1 style="font-family: 'Outfit', sans-serif; font-size: 3.5rem; font-weight: 800; line-height: 1.1; margin-bottom: 1.5rem;">Join the<br>Circle of Care.</h1>
                <p style="font-size: 1.25rem; opacity: 0.8; line-height: 1.6; max-width: 400px; font-weight: 500;">Embark on your journey to mental wellness with professional support and clinical insights.</p>
                <div style="margin-top: 4rem; padding-top: 3rem; border-top: 1px solid rgba(255,255,255,0.1); display: flex; gap: 2rem;">
                    <div>
                        <div style="font-size: 1.5rem; font-weight: 800;">100%</div>
                        <div style="font-size: 0.75rem; opacity: 0.6; text-transform: uppercase;">Private</div>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: 800;">24/7</div>
                        <div style="font-size: 0.75rem; opacity: 0.6; text-transform: uppercase;">Support</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="auth-form-area">
            <div>
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 800; color: #1e293b; margin-bottom: 0.5rem;">Create Student Account</h2>
                <p style="color: #64748b; margin-bottom: 3rem; font-weight: 600;">Already a member? <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 800;">Sign in here</a></p>

                <?php if ($success): ?>
                    <div class="alert alert-success">✨ <?php echo $success; ?> <a href="login.php" style="font-weight:800;color:var(--primary);">Go to Login →</a></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-section-label">Identity Details</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="input-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" placeholder="John Doe" required>
                        </div>
                        <div class="input-group">
                            <label>Student ID</label>
                            <input type="text" name="roll_number" placeholder="ID Number" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
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
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
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
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="input-group">
                            <label>Create Password</label>
                            <input type="password" name="password" required placeholder="Min. 6 characters">
                        </div>
                        <div class="input-group">
                            <label>Verify Password</label>
                            <input type="password" name="confirm_password" required placeholder="Repeat password">
                        </div>
                    </div>

                    <button type="submit" class="btn-auth" style="width: 100%;">Finalize Registration</button>
                    
                    <p style="text-align: center; margin-top: 2rem; font-size: 0.8rem; color: #94a3b8; line-height: 1.6;">
                        By registering, you agree to our <strong>Terms of Service</strong> and <strong>Clinical Privacy Policy</strong>. Your data is encrypted and handled only by authorized practitioners.
                    </p>
                </form>
            </div>
        </div>
    </div>
<?php include 'toast.php'; ?>
</body>
</html>
