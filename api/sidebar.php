<?php
/**
 * Sidebar Component
 */
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user_type'] ?? 'guest';
?>

<aside class="sidebar" style="background: white; border-right: 1px solid var(--border); box-shadow: var(--shadow-sm); z-index: 1000;">
    <div class="sidebar-header" style="padding: 2.5rem 2rem; border-bottom: 1px solid #f8fafc; margin-bottom: 2rem;">
        <a href="index.php" class="sidebar-brand" style="display: flex; align-items: center; gap: 1rem; text-decoration: none;">
            <div style="width: 45px; height: 45px; border-radius: 14px; overflow: hidden; box-shadow: 0 8px 16px rgba(0,0,0,0.05); border: 2px solid white;">
                <img src="logo/system_logo.jpg" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div class="sidebar-title" style="font-family: 'Outfit', sans-serif; font-weight: 800; color: var(--primary-dark); line-height: 1.1;">
                PSU <span style="color: var(--primary);">San Carlos </span><br>
                <span style="font-size: 0.65rem; font-weight: 800; opacity: 0.4; letter-spacing: 0.15em; text-transform: uppercase;">Mental Health</span>
            </div>
        </a>
    </div>

    <nav class="sidebar-nav" style="flex: 1; overflow-y: auto; padding-right: 4px;">
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
            <a href="admin_reports.php" class="sidebar-link <?php echo $current_page == 'admin_reports.php' ? 'active' : ''; ?>">
                <i>📋</i> <span>System Reports</span>
            </a>
            <a href="admin_dashboard.php#logs" class="sidebar-link">
                <i>📜</i> <span>Activity Logs</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer" style="margin-top: auto; padding: 2rem; border-top: 1px solid #f8fafc;">
        <div style="background: #f8fafc; padding: 1rem; border-radius: 20px; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 35px; height: 35px; border-radius: 10px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.75rem;">
                <?php 
                $fn = $_SESSION['full_name'] ?? 'User';
                echo strtoupper(substr($fn, 0, 1) . substr(explode(' ', $fn)[1] ?? '', 0, 1));
                ?>
            </div>
            <div style="overflow: hidden;">
                <div style="font-weight: 800; font-size: 0.8rem; color: var(--text); white-space: nowrap; text-overflow: ellipsis;"><?php echo htmlspecialchars($fn); ?></div>
                <div style="font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase;"><?php echo $user_role; ?></div>
            </div>
        </div>
        <?php
        $notif_count_sidebar = getNotificationCount($_SESSION['user_id'] ?? 0);
        ?>
        <a href="notifications.php" class="sidebar-link <?php echo $current_page == 'notifications.php' ? 'active' : ''; ?>" style="margin-bottom:0.5rem; position:relative;">
            <i>🔔</i> <span>Notifications</span>
            <?php if ($notif_count_sidebar > 0): ?>
            <span class="notif-badge-count" style="position:absolute; top:6px; right:10px; background:#dc2626; color:white; border-radius:50%; width:18px; height:18px; display:flex; align-items:center; justify-content:center; font-size:0.65rem; font-weight:900;"><?php echo min($notif_count_sidebar, 9); ?></span>
            <?php else: ?>
            <span class="notif-badge-count" style="position:absolute; top:6px; right:10px; background:#dc2626; color:white; border-radius:50%; width:18px; height:18px; display:none; align-items:center; justify-content:center; font-size:0.65rem; font-weight:900;">0</span>
            <?php endif; ?>
        </a>
        <a href="profile.php" class="sidebar-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>" style="margin-bottom:0.5rem;">
            <i>👤</i> <span>Settings</span>
        </a>
        <button onclick="openSignOutModal()" class="sidebar-link" style="width: 100%; background: #fff1f2; border: 1px solid #fecaca; cursor: pointer; text-align: left; color: #dc2626; font-family: inherit; font-size: inherit; padding: 0.85rem 1.25rem; border-radius: 14px; display: flex; align-items: center; gap: 0.75rem; transition: all 0.2s; margin-top: 0.5rem;">
            <i style="font-style: normal; font-size: 1.2rem;">🚪</i> <span style="font-weight: 800;">Sign Out</span>
        </button>
    </div>
</aside>

<!-- Sign Out Confirmation Modal -->
<div id="signOutModal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(8px); align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 32px; padding: 3.5rem 3rem; max-width: 420px; width: 90%; box-shadow: 0 40px 80px rgba(0,0,0,0.2); text-align: center; animation: signOutModalIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
        <div style="width: 72px; height: 72px; border-radius: 50%; background: #fef2f2; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; font-size: 2rem; border: 3px solid #fee2e2;">🚪</div>
        <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 0.75rem;">Sign Out?</h3>
        <p style="color: #64748b; font-size: 1rem; font-weight: 500; line-height: 1.6; margin-bottom: 2.5rem;">You're about to leave your session. Any unsaved progress may be lost.</p>
        <div style="display: flex; gap: 1rem;">
            <button onclick="closeSignOutModal()" style="flex: 1; background: #f1f5f9; border: none; padding: 1rem; border-radius: 14px; font-weight: 700; font-size: 1rem; color: #475569; cursor: pointer; transition: background 0.2s;">Stay</button>
            <a href="logout.php" style="flex: 1; background: #dc2626; color: white; border: none; padding: 1rem; border-radius: 14px; font-weight: 700; font-size: 1rem; cursor: pointer; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">Yes, Sign Out</a>
        </div>
    </div>
</div>

<style>
@keyframes signOutModalIn {
    from { transform: scale(0.85); opacity: 0; }
    to   { transform: scale(1);    opacity: 1; }
}
</style>

<script>
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
// If this page is served from the browser's back-forward cache (bfcache),
// force a fresh reload so PHP can detect the expired session and redirect.
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        // Page restored from bfcache — reload to re-run PHP session check
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
        <img src="logo/system_logo.jpg" alt="Logo" class="sidebar-logo" style="width: 32px; height: 32px;">
        <span style="font-size: 1rem; font-weight: 700;">Mental Portal</span>
    </a>
    <button class="nav-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>
</header>

<?php require_once __DIR__ . '/pwa_banner.php'; ?>
