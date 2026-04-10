<?php
/**
 * Sidebar Component
 */
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user_type'] ?? 'guest';
?>

<aside class="sidebar glass">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="sidebar-logo-container">
                <img src="logo/system_logo.jpg" alt="Logo" class="sidebar-logo">
            </div>
            <div class="sidebar-title">
                PSU <span class="brand-accent">San Carlos</span><br>
                <span class="brand-subtitle">Mental Health Portal</span>
            </div>
        </div>
    </div>

    <!-- Emergency Help Button (Glass-morphic Red) -->
    <div class="emergency-container">
        <a href="emergency_contacts.php" class="btn-emergency">
            <i class="emergency-icon">🚨</i> <span>Get Help Now</span>
        </a>
    </div>

    <nav class="sidebar-nav" style="flex: 1; overflow-y: auto; padding: 0.5rem 0.6rem; display: flex; flex-direction: column; gap: 4px;">
        <style>
            .sidebar-link {
                display: flex;
                align-items: center;
                gap: 0.85rem;
                padding: 0.75rem 0.9rem;
                border-radius: 12px;
                color: #64748b;
                text-decoration: none;
                font-weight: 600;
                font-size: 0.88rem;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                border: 1px solid transparent;
            }
            .sidebar-link:hover {
                background: rgba(59, 130, 246, 0.05);
                color: #2563eb;
                transform: translateX(4px);
            }
            .sidebar-link.active {
                background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.1));
                color: #2563eb;
                border: 1px solid rgba(59, 130, 246, 0.15);
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.05);
            }
            .sidebar-link i {
                font-style: normal;
                font-size: 1.15rem;
                opacity: 0.85;
                transition: transform 0.2s;
            }
            .sidebar-link:hover i { transform: scale(1.1); }
            .sidebar-link.active i { opacity: 1; }
        </style>

        <?php if ($user_role === 'student'): ?>
            <a href="student_dashboard.php" class="sidebar-link <?php echo $current_page == 'student_dashboard.php' ? 'active' : ''; ?>">
                <i>📊</i> <span>Dashboard</span>
            </a>
            <a href="take_assessment.php" class="sidebar-link <?php echo $current_page == 'take_assessment.php' ? 'active' : ''; ?>">
                <i>📝</i> <span>Pre-Assessment</span>
            </a>
            <a href="anonymous_notes.php" class="sidebar-link <?php echo $current_page == 'anonymous_notes.php' ? 'active' : ''; ?>">
                <i>✉️</i> <span>Quick Note</span>
            </a>
            <a href="student_ai_chat.php" class="sidebar-link <?php echo $current_page == 'student_ai_chat.php' ? 'active' : ''; ?>">
                <i>✨</i> <span>Talk to Aria</span>
            </a>
            <a href="mood_journal.php" class="sidebar-link <?php echo $current_page == 'mood_journal.php' ? 'active' : ''; ?>">
                <i>📓</i> <span>Mood Journal</span>
            </a>
            <a href="student_appointments.php" class="sidebar-link <?php echo $current_page == 'student_appointments.php' ? 'active' : ''; ?>">
                <i>📅</i> <span>Appointments</span>
            </a>
            <a href="mindfulness.php" class="sidebar-link <?php echo $current_page == 'mindfulness.php' ? 'active' : ''; ?>">
                <i>🧘</i> <span>Mindfulness</span>
            </a>
            <a href="my_reports.php" class="sidebar-link <?php echo $current_page == 'my_reports.php' ? 'active' : ''; ?>">
                <i>📈</i> <span>My Progress</span>
            </a>

        <?php elseif ($user_role === 'counselor'): ?>
            <a href="counselor_dashboard.php" class="sidebar-link <?php echo $current_page == 'counselor_dashboard.php' ? 'active' : ''; ?>">
                <i>📊</i> <span>Dashboard</span>
            </a>
            <a href="counselor_appointments.php" class="sidebar-link <?php echo $current_page == 'counselor_appointments.php' ? 'active' : ''; ?>">
                <i>📅</i> <span>Appointments</span>
            </a>
            <a href="counselor_availability.php" class="sidebar-link <?php echo $current_page == 'counselor_availability.php' ? 'active' : ''; ?>">
                <i>⏰</i> <span>My Availability</span>
            </a>
            <a href="student_list.php" class="sidebar-link <?php echo $current_page == 'student_list.php' ? 'active' : ''; ?>">
                <i>👥</i> <span>Student Records</span>
            </a>
            <a href="counselor_ledger.php" class="sidebar-link <?php echo $current_page == 'counselor_ledger.php' ? 'active' : ''; ?>">
                <i>📖</i> <span>Activity Ledger</span>
            </a>

        <?php elseif ($user_role === 'admin'): ?>
            <a href="admin_dashboard.php" class="sidebar-link <?php echo $current_page == 'admin_dashboard.php' ? 'active' : ''; ?>">
                <i>🛡️</i> <span>Admin Panel</span>
            </a>
            <a href="head_counselor_manage.php" class="sidebar-link <?php echo $current_page == 'head_counselor_manage.php' ? 'active' : ''; ?>">
                <i>👨‍🏫</i> <span>Manage Counselors</span>
            </a>
            <a href="head_counselor_appointments.php" class="sidebar-link <?php echo $current_page == 'head_counselor_appointments.php' ? 'active' : ''; ?>">
                <i>📅</i> <span>All Schedules & Override</span>
            </a>
            <a href="admin_reports.php" class="sidebar-link <?php echo $current_page == 'admin_reports.php' ? 'active' : ''; ?>">
                <i>📋</i> <span>System Reports</span>
            </a>
            <a href="admin_dashboard.php#logs" class="sidebar-link">
                <i>📜</i> <span>Activity Logs</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-profile-block">
            <div class="user-avatar-initials">
                <?php
                $fn = $_SESSION['full_name'] ?? 'User';
                echo strtoupper(substr($fn, 0, 1) . substr(explode(' ', $fn)[1] ?? '', 0, 1));
                ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($fn); ?></div>
                <div class="user-role"><?php echo $user_role; ?></div>
            </div>
        </div>
        
        <div class="sidebar-actions">
            <?php
            $notif_count_sidebar = getNotificationCount($_SESSION['user_id'] ?? 0);
            ?>
            <a href="notifications.php" class="sidebar-link <?php echo $current_page == 'notifications.php' ? 'active' : ''; ?>" style="position:relative;">
                <i>🔔</i> <span>Notifications</span>
                <?php if ($notif_count_sidebar > 0): ?>
                <span class="notif-badge-count"><?php echo min($notif_count_sidebar, 9); ?></span>
                <?php else: ?>
                <span class="notif-badge-count" style="display:none;">0</span>
                <?php endif; ?>
            </a>
            
            <a href="profile.php" class="sidebar-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                <i>⚙️</i> <span>Settings</span>
            </a>
            
            <button id="themeToggle" class="sidebar-link theme-toggle-btn">
                <i id="themeIcon">🌙</i> <span id="themeLabel">Dark Mode</span>
            </button>
            
            <button onclick="openSignOutModal()" class="sidebar-link signout-btn">
                <i>🚪</i> <span style="font-weight: 700;">Sign Out</span>
            </button>
        </div>
    </div>
</aside>

<!-- Sign Out Confirmation Modal -->
<div id="signOutModal" class="signout-modal-overlay">
    <div class="signout-modal">
        <div class="signout-icon">🚪</div>
        <h3 class="signout-title">Sign Out?</h3>
        <p class="signout-text">You're about to leave your session. Any unsaved progress may be lost.</p>
        <div class="signout-actions">
            <button onclick="closeSignOutModal()" class="btn-stay">Stay</button>
            <a href="logout.php" class="btn-confirm-signout">Yes, Sign Out</a>
        </div>
    </div>
</div>

<style>
@keyframes signOutModalIn {
    from { transform: scale(0.9); opacity: 0; }
    to   { transform: scale(1);    opacity: 1; }
}
</style>

<script>
// ── Theme Toggle Logic ──
(function() {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const themeLabel = document.getElementById('themeLabel');
    const html = document.documentElement;

    // Check for saved theme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        updateUI(true);
    }

    themeToggle.addEventListener('click', () => {
        const isDark = html.classList.toggle('dark-mode');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        updateUI(isDark);
        console.log('Theme toggled. Dark mode:', isDark);
    });

    function updateUI(isDark) {
        themeIcon.textContent = isDark ? '☀️' : '🌙';
        themeLabel.textContent = isDark ? 'Light Mode' : 'Dark Mode';
    }
})();

function openSignOutModal() {
    const modal = document.getElementById('signOutModal');
    modal.style.display = 'flex';
}
function closeSignOutModal() {
    const modal = document.getElementById('signOutModal');
    modal.style.display = 'none';
}
// Close on backdrop click
document.getElementById('signOutModal').addEventListener('click', function(e) {
    if (e.target === this) closeSignOutModal();
});

// ── Prevent back navigation after logout ──
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});

// ── AJAX Notification Badge Polling (every 30 s) ──
(function pollNotifications() {
    const badge = document.querySelector('.notif-badge-count');
    if (!badge) return;
    setInterval(async () => {
        try {
            const res  = await fetch('notifications.php?ajax_count=1', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            const count = data.count || 0;
            badge.textContent = Math.min(count, 9);
            badge.style.display = count > 0 ? 'flex' : 'none';
        } catch (_) { /* ignore network errors */ }
    }, 30000);
})();
</script>

<header class="mobile-nav">
    <a href="index.php" class="sidebar-brand">
        <img src="logo/system_logo.jpg" alt="Logo" class="sidebar-logo" style="width: 28px; height: 28px;">
        <span style="font-size: 0.9rem; font-weight: 600;">Mental Portal</span>
    </a>
    <button class="nav-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>
</header>
