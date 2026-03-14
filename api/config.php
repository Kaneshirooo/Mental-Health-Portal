<?php
// Helper to get environment variables reliably in Vercel
function get_env_var($name, $default = null) {
    return $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name) ?? $default;
}

// Database Configuration
define('DB_HOST', get_env_var('DB_HOST', ''));
define('DB_USER', get_env_var('DB_USER', ''));
define('DB_PASS', get_env_var('DB_PASS', ''));
define('DB_NAME', get_env_var('DB_NAME', ''));

// Site Configuration
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('SITE_URL', "$protocol://$host");
define('SITE_TITLE', 'Mental Health Pre-Assessment System');

// Session Configuration
ini_set('session.gc_maxlifetime', 3600);
session_start();

// Connect to Database
// Disable exception mode so CREATE TABLE failures are non-fatal
mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    $host_used = DB_HOST ?: '(empty)';
    die("Database Connection Error (Host: $host_used): " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Re-enable strict error reporting (but catch FK errors below)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ── Ensure required tables exist (safe on every request) ──
// These CREATE TABLE IF NOT EXISTS statements keep new features working
// even if the SQL file wasn't re-imported. FK errors are caught silently
// so an old MyISAM-based install doesn't crash the application.
try {
    // Temporarily disable FK checks so we can create tables in any order
    $conn->query("SET foreign_key_checks = 0");

    $conn->query("CREATE TABLE IF NOT EXISTS users (
        user_id INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        roll_number VARCHAR(50),
        user_type ENUM('student', 'counselor', 'admin') DEFAULT 'student',
        contact_number VARCHAR(15),
        department VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS session_logs (
        log_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        logout_time TIMESTAMP NULL,
        activity TEXT,
        CONSTRAINT fk_session_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS appointments (
        appointment_id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        counselor_id INT NOT NULL,
        scheduled_at DATETIME NOT NULL,
        duration_min INT NOT NULL DEFAULT 30,
        status ENUM('requested','confirmed','declined','cancelled','completed') NOT NULL DEFAULT 'requested',
        reason TEXT,
        counselor_message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_student_date (student_id, scheduled_at),
        KEY idx_counselor_date (counselor_id, scheduled_at),
        CONSTRAINT fk_appt_student FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
        CONSTRAINT fk_appt_counselor FOREIGN KEY (counselor_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS ai_preassessments (
        pre_id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        conversation_transcript MEDIUMTEXT,
        form_answers TEXT,
        ai_report TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_student_created (student_id, created_at),
        CONSTRAINT fk_pre_student FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS counselor_availability (
        availability_id INT PRIMARY KEY AUTO_INCREMENT,
        counselor_id INT NOT NULL,
        day_of_week TINYINT NOT NULL COMMENT '0=Sun,1=Mon,...,6=Sat',
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_counselor_day (counselor_id, day_of_week),
        CONSTRAINT fk_avail_counselor FOREIGN KEY (counselor_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS notifications (
        notification_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type VARCHAR(50) NOT NULL DEFAULT 'system',
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_notif_user (user_id),
        CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("SET foreign_key_checks = 1");
} catch (mysqli_sql_exception $e) {
    // Non-fatal: table may already exist or FK engine mismatch on legacy install.
    $conn->query("SET foreign_key_checks = 1");
}

// ── Helper Functions ──────────────────────────────────

function redirect($url) {
    if (!preg_match('#^https?://#', $url)) {
        // Build base path from SCRIPT_NAME to handle subdirectories like /Capstone/
        $script = $_SERVER['SCRIPT_NAME'];
        $dir = rtrim(dirname($script), '/\\');
        $url = $dir . '/' . ltrim($url, '/');
    }
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isStudent() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student';
}

function isCounselor() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'counselor';
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function requireStudent() {
    requireLogin();
    if (!isStudent()) {
        redirect('unauthorized.php');
    }
}

function requireCounselor() {
    requireLogin();
    if (!isCounselor()) {
        redirect('unauthorized.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('unauthorized.php');
    }
}

/**
 * sanitize() — for DISPLAY only (HTML output escaping).
 * Do NOT use this to build SQL strings — always use prepared statements instead.
 * Previously this also called real_escape_string which caused double-encoding
 * (e.g. an apostrophe would become \&#039; when displayed).
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function getUserData($user_id) {
    global $conn;
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt  = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * calculateRiskLevel – each category is scored 0-20 (5 questions × 0-4).
 * We use the average of the three raw category scores (0-20 each) and
 * map to percentage bands.
 */
function calculateRiskLevel($depression, $anxiety, $stress) {
    // Each max = 20; average on a 0-20 scale
    $avg = ($depression + $anxiety + $stress) / 3;

    if ($avg >= 16) return 'Critical';   // ≥80 % of 20
    if ($avg >= 12) return 'High';       // ≥60 %
    if ($avg >=  8) return 'Moderate';   // ≥40 %
    return 'Low';
}

function startSessionLog($user_id) {
    global $conn;
    $activity = "User logged in";
    $stmt = $conn->prepare("INSERT INTO session_logs (user_id, login_time, activity) VALUES (?, NOW(), ?)");
    if ($stmt) {
        $stmt->bind_param("is", $user_id, $activity);
        $stmt->execute();
        return $stmt->insert_id;
    }
    return 0;
}

function endSessionLog($log_id) {
    global $conn;
    if (!$log_id) return;
    $stmt = $conn->prepare("UPDATE session_logs SET logout_time = NOW(), activity = CONCAT(activity, ' | Session ended') WHERE log_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $log_id);
        $stmt->execute();
    }
}

function logActivity($user_id, $activity) {
    global $conn;
    $query = "INSERT INTO session_logs (user_id, login_time, activity) VALUES (?, NOW(), ?)";
    $stmt  = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("is", $user_id, $activity);
        $stmt->execute();
    }
}

/**
 * getNotificationCount – returns the number of unread counselor notes
 * added since the student's last login (or total if no timestamp).
 */
function getNotificationCount($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0");
    if (!$stmt) return 0;
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)($row['cnt'] ?? 0);
}

function createNotification($user_id, $title, $message, $type = 'system') {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?,?,?,?)");
    if (!$stmt) return false;
    $stmt->bind_param("isss", $user_id, $title, $message, $type);
    return $stmt->execute();
}

// ── Toast Notification System ─────────────────────────
if (!isset($__toasts)) $__toasts = [];

function queueToast(string $message, string $type = 'success', string $title = '') {
    global $__toasts;
    if (empty($title)) {
        $titles = ['success' => 'Success', 'error' => 'Error', 'warning' => 'Warning', 'info' => 'Info'];
        $title = $titles[$type] ?? 'Notice';
    }
    $__toasts[] = ['msg' => $message, 'type' => $type, 'title' => $title];
}
?>
