<?php
require_once 'config.php';

// ── Step 1: Catch any errors Google sent back ───────────────────────────────
if (isset($_GET['error'])) {
    redirect('login.php?error=' . urlencode('Google sign-in was cancelled or denied.'));
}

if (empty($_GET['code'])) {
    redirect('login.php?error=' . urlencode('No authorization code received from Google.'));
}

// ── Step 2: Exchange the code for tokens ────────────────────────────────────
$tokenData = [
    'code'          => $_GET['code'],
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
];

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($tokenData),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_SSL_VERIFYPEER => true,
]);
$tokenResponse = json_decode(curl_exec($ch), true);
curl_close($ch);

if (empty($tokenResponse['access_token'])) {
    redirect('login.php?error=' . urlencode('Failed to authenticate with Google. Please try again.'));
}

// ── Step 3: Fetch the user's Google profile ──────────────────────────────────
$ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $tokenResponse['access_token']],
    CURLOPT_SSL_VERIFYPEER => true,
]);
$googleUser = json_decode(curl_exec($ch), true);
curl_close($ch);

if (empty($googleUser['email'])) {
    redirect('login.php?error=' . urlencode('Could not retrieve your email from Google. Please try again.'));
}

$googleEmail = strtolower(trim($googleUser['email']));

// ── Domain enforcement: only @psu.edu.ph accounts allowed ───────────────────
$emailDomain = substr(strrchr($googleEmail, '@'), 1);
if ($emailDomain !== 'psu.edu.ph') {
    redirect('login.php?error=' . urlencode('Only PSU institutional accounts (@psu.edu.ph) are allowed to access this portal.'));
}

// ── Step 4: Look up the user in our database ────────────────────────────────
$stmt = $conn->prepare("SELECT user_id, password, user_type, full_name FROM users WHERE LOWER(email) = ?");
$stmt->bind_param("s", $googleEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // Email not registered in the portal
    redirect('login.php?error=' . urlencode('Your Google account (' . $googleEmail . ') is not registered in the system. Please contact your counselor or administrator.'));
}

$user = $result->fetch_assoc();

// Generate 6-digit OTP
$otp = rand(100000, 999999);

$_SESSION['temp_user'] = [
    'user_id'   => $user['user_id'],
    'user_type' => $user['user_type'],
    'full_name' => $user['full_name'],
    'email'     => $googleEmail
];
$_SESSION['otp_code'] = $otp;
$_SESSION['otp_expiry'] = time() + (10 * 60);

// Send OTP
if (sendOTP($googleEmail, $otp)) {
    redirect('verify_otp.php');
} else {
    redirect('login.php?error=' . urlencode('Failed to send verification code. Please try again.'));
}
