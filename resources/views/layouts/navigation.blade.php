@php
    $user = Auth::user();
    // Normalize user type to lowercase string for robust comparison
    $userType = $user && $user->user_type ? strtolower($user->user_type->value ?? (string)$user->user_type) : '';
    $currentRoute = Route::currentRouteName();
@endphp

<aside class="sidebar glass" id="sidebar" style="margin: 1rem; height: calc(100vh - 2rem); border-radius: 24px; border: 1px solid var(--glass-border); box-shadow: var(--shadow-lg); left: 0; top: 0; position: fixed;">
    <div class="sidebar-header" style="padding: 1.5rem 1.25rem;">
        <div class="sidebar-brand" style="display: flex; align-items: center;">
        <div class="sidebar-logo-container" style="position: relative; cursor: pointer; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: flex; align-items: center;" onmouseover="this.style.transform='scale(1.05) rotate(-1deg)'" onmouseout="this.style.transform='scale(1) rotate(0)'">
            <div class="w-14 h-14 rounded-xl bg-white flex items-center justify-center shadow-[0_8px_20px_rgba(0,0,0,0.08)] border border-slate-200 relative group overflow-hidden">
                <img src="{{ asset('logo/system_logo.jpg') }}" alt="PSU Logo" class="w-full h-full object-cover relative z-10 transition-transform duration-500 group-hover:scale-110">
            </div>
            <div class="absolute -bottom-1 -left-1 w-4 h-4 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full shadow-lg z-20"></div>
        </div>
            <div class="sidebar-title" style="margin-left: 0.85rem;">
                <span style="font-weight: 900; font-size: 1.4rem; letter-spacing: -0.04em; color: var(--text); line-height: 1.1;">PSU <span style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 900;">Portal</span></span><br>
                <span style="font-size: 0.65rem; opacity: 0.6; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; color: var(--text-dim);">Mental Health System</span>
            </div>
        </div>
    </div>

    <!-- Emergency Help Button (Premium Style) -->
    <div class="emergency-container" style="padding: 0 1.25rem 1.5rem;">
        <a href="{{ route('emergency') }}" class="btn-emergency" style="background: #dc2626; color: #ffffff; padding: 0.85rem; border-radius: 16px; display: flex; align-items: center; justify-content: center; gap: 0.75rem; text-decoration: none; font-weight: 800; font-size: 0.85rem; border: none; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25); transition: all 0.3s ease;">
            <i class="ph-bold ph-warning-octagon" style="font-size: 1.25rem;"></i> <span>Emergency Help</span>
        </a>
    </div>

    <nav class="sidebar-nav" style="flex: 1; overflow-y: auto; padding: 0 0.75rem; display: flex; flex-direction: column; gap: 4px;">
        @if($userType === 'student')
            <a href="{{ route('student.dashboard') }}" class="sidebar-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-squares-four"></i></div> <span>Dashboard</span>
            </a>
            <a href="{{ route('student.assessment') }}" class="sidebar-link {{ request()->routeIs('student.assessment') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-clipboard-text"></i></div> <span>Pre-Assessment</span>
            </a>
            <a href="{{ route('student.notes.index') }}" class="sidebar-link {{ request()->routeIs('student.notes.*') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-chat-centered-text"></i></div> <span>Quick Note</span>
            </a>
            <a href="{{ route('student.chat') }}" class="sidebar-link {{ request()->routeIs('student.chat') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-sparkle"></i></div> <span>Talk to Assistant</span>
            </a>
            <a href="{{ route('student.mood') }}" class="sidebar-link {{ request()->routeIs('student.mood') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-book-open"></i></div> <span>Mood Journal</span>
            </a>
            <a href="{{ route('student.appointments') }}" class="sidebar-link {{ request()->routeIs('student.appointments') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-calendar-blank"></i></div> <span>Appointments</span>
            </a>
            <a href="{{ route('student.mindfulness.index') }}" class="sidebar-link {{ request()->routeIs('student.mindfulness.*') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-wind"></i></div> <span>Mindfulness</span>
            </a>
            <a href="{{ route('student.reports.index') }}" class="sidebar-link {{ request()->routeIs('student.reports.*') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-presentation-chart"></i></div> <span>My Progress</span>
            </a>
            <a href="{{ route('student.profile.edit') }}" class="sidebar-link {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-user-circle"></i></div> <span>My Profile</span>
            </a>

        @elseif($userType === 'counselor')
            <a href="{{ route('counselor.dashboard') }}" class="sidebar-link {{ request()->routeIs('counselor.dashboard') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-squares-four"></i></div> <span>Dashboard</span>
            </a>
            <a href="{{ route('counselor.appointments.index') }}" class="sidebar-link no-magnetic {{ request()->routeIs('counselor.appointments.*') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-calendar-blank"></i></div> <span>Appointments</span>
            </a>
            <a href="{{ route('counselor.availability') }}" class="sidebar-link no-magnetic {{ request()->routeIs('counselor.availability') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-clock"></i></div> <span>My Availability</span>
            </a>
            <a href="{{ route('counselor.students.index') }}" class="sidebar-link {{ request()->routeIs('counselor.students.*') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-users"></i></div> <span>Student Records</span>
            </a>
            <a href="{{ route('counselor.emergency.calls.logs') }}" class="sidebar-link {{ request()->routeIs('counselor.emergency.calls.logs') || request()->routeIs('counselor.emergency.calls.history') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-phone-call"></i></div> <span>Call History</span>
            </a>


        @elseif($userType === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-shield-check"></i></div> <span>Admin Panel</span>
            </a>
            <a href="{{ route('admin.staff.index') }}" class="sidebar-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-headset"></i></div> <span>Manage Counselors</span>
            </a>
            <a href="{{ route('counselor.students.index') }}" class="sidebar-link {{ request()->routeIs('counselor.students.*') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-users"></i></div> <span>Student Records</span>
            </a>
            <a href="{{ route('counselor.appointments.index') }}" class="sidebar-link no-magnetic {{ request()->routeIs('counselor.appointments.*') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-calendar-blank"></i></div> <span>My Appointments</span>
            </a>
            <a href="{{ route('counselor.availability') }}" class="sidebar-link no-magnetic {{ request()->routeIs('counselor.availability') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-clock"></i></div> <span>My Availability</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-chart-pie"></i></div> <span>System Reports</span>
            </a>
            <a href="{{ route('counselor.emergency.calls.logs') }}" class="sidebar-link {{ request()->routeIs('counselor.emergency.calls.logs') || request()->routeIs('counselor.emergency.calls.history') ? 'active' : '' }}">
                <div class="link-icon"><i class="ph ph-phone-call"></i></div> <span>Call History</span>
            </a>
        @endif
    </nav>

    <div class="sidebar-footer" style="padding: 1.25rem; background: rgba(0,0,0,0.03); border-radius: 0 0 24px 24px; border-top: 1px solid var(--border);">
        <div class="user-profile-block" style="display:flex; align-items:center; gap:0.85rem; margin-bottom:1.25rem;">
            <div class="user-avatar-initials" style="width:44px; height:44px; border-radius:14px; overflow:hidden; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #ffffff; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:0.95rem; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);">
                @if($user->profile_picture)
                    <img src="{{ asset('storage/' . $user->profile_picture) }}" style="width:100%; height:100%; object-cover: cover;">
                @else
                    @php
                        $nameParts = explode(' ', $user->full_name);
                        echo strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                    @endphp
                @endif
            </div>
            <div class="user-info" style="overflow:hidden;">
                <div class="user-name" style="font-weight:800; font-size:0.85rem; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $user->full_name }}</div>
                <div class="user-role" style="font-size:0.7rem; color:var(--primary); font-weight:700; text-transform:uppercase; letter-spacing:0.04em;">{{ $userType }}</div>
            </div>
        </div>
        
        <div class="sidebar-actions" style="display:flex; flex-direction:column; gap:6px;">
            <a href="{{ route('notifications.index') }}" class="sidebar-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" style="border-radius: 12px; position: relative;">
                <div class="link-icon"><i class="ph ph-bell"></i></div> <span>Notifications</span>
                <span id="notifBadge" style="display: none; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: #ef4444; color: white; font-size: 10px; font-weight: 800; min-width: 18px; text-align: center; padding: 2px 4px; border-radius: 10px; border: 2px solid var(--surface-solid); box-shadow: 0 0 10px rgba(239, 68, 68, 0.3);">0</span>
            </a>
            
            <button id="themeToggle" class="sidebar-link theme-toggle-btn w-full text-left" style="border-radius: 12px; background: transparent; border: none; width: 100%; cursor: pointer;">
                <div class="link-icon" id="themeIcon"><i class="ph ph-moon"></i></div> <span id="themeLabel">Dark Mode</span>
            </button>
            
            <script>
                window.updateNotifCount = async function() {
                    try {
                        const res = await fetch("{{ route('notifications.count') }}");
                        const data = await res.json();
                        const badge = document.getElementById('notifBadge');
                        if (!badge) return;
                        if (data.count > 0) {
                            const isNew = badge.style.display === 'none' || badge.textContent !== String(data.count);
                            badge.textContent = data.count > 99 ? '99+' : data.count;
                            badge.style.display = 'block';
                            if (isNew && window.gsap) {
                                gsap.fromTo(badge, { scale: 0.5, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.4, ease: "back.out(2)" });
                            }
                        } else {
                            badge.style.display = 'none';
                        }
                    } catch (e) {}
                };
                document.addEventListener('DOMContentLoaded', () => {
                    window.updateNotifCount();
                    setInterval(window.updateNotifCount, 5000); // 5 seconds real-time polling
                });
            </script>
            
            <button onclick="openSignOutModal()" class="sidebar-link signout-btn" style="border-radius: 12px; background: rgba(239, 68, 68, 0.05); color: #ef4444; border: none; width: 100%; cursor: pointer; margin-top: 4px; display: flex; align-items: center; gap: 4px; text-align: left; padding: 0.75rem;">
                <div class="link-icon"><i class="ph ph-sign-out"></i></div> <span style="font-weight: 800;">Sign Out</span>
            </button>
        </div>
    </div>
</aside>

<!-- Mobile Top Navigation Bar -->
<div class="mobile-nav" id="mobileNav">
    <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" onclick="toggleSidebar()">
        <i class="ph ph-list" id="navToggleIcon"></i>
    </button>
    <div style="display: flex; align-items: center; gap: 0.5rem;">
        <img src="{{ asset('logo/system_logo.jpg') }}" alt="PSU Logo" style="width:28px; height:28px; border-radius:8px; object-fit:cover;">
        <span style="font-family:'Outfit',sans-serif; font-weight:800; font-size:0.95rem; color:var(--text);">PSU <span style="color:var(--primary);">Portal</span></span>
    </div>
    <a href="{{ route('emergency') }}" style="background:#dc2626; color:white; padding:0.4rem 0.75rem; border-radius:10px; font-size:0.72rem; font-weight:800; text-decoration:none; display:flex; align-items:center; gap:0.3rem;">
        <i class="ph-bold ph-warning-octagon"></i> SOS
    </a>
</div>

<!-- Mobile Sidebar Overlay -->
<div id="sidebarOverlay" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.5); backdrop-filter:blur(4px); z-index:999;" onclick="closeSidebar()"></div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const icon    = document.getElementById('navToggleIcon');
    const isOpen  = sidebar.classList.toggle('open');
    overlay.style.display = isOpen ? 'block' : 'none';
    icon.className = isOpen ? 'ph ph-x' : 'ph ph-list';
}
function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const icon    = document.getElementById('navToggleIcon');
    sidebar.classList.remove('open');
    overlay.style.display = 'none';
    icon.className = 'ph ph-list';
}
// Close sidebar when a nav link is clicked on mobile
document.addEventListener('DOMContentLoaded', () => {
    if (window.innerWidth <= 1024) {
        document.querySelectorAll('.sidebar-link, .btn-emergency').forEach(link => {
            link.addEventListener('click', closeSidebar);
        });
    }
});
</script>

<!-- Sign Out Confirmation Modal -->
<div id="signOutModal" class="signout-modal-overlay" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.45); backdrop-filter:blur(12px); align-items:center; justify-content:center;">
    <div class="signout-modal staggered" style="background:var(--surface-solid); border:1px solid var(--border); border-radius:32px; padding:3rem; max-width:420px; width:90%; text-align:center; box-shadow:0 30px 60px rgba(0,0,0,0.2);">
        <div style="width: 80px; height: 80px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem;">
            <i class="ph ph-door-open"></i>
        </div>
        <h3 style="font-family:'Outfit',sans-serif; font-weight:900; font-size:1.75rem; margin-bottom:0.75rem; color: var(--text); letter-spacing: -0.02em;">Terminate Session?</h3>
        <p style="color:var(--text-muted); font-size:1.05rem; margin-bottom:2.5rem; font-weight: 500; line-height: 1.6;">You are about to securely exit the clinical portal. Ensure all active protocols and notes are archived.</p>
        <div style="display:flex; gap:1.25rem;">
            <button onclick="closeSignOutModal()" style="flex:1; background:var(--surface-2); border:1.5px solid var(--border); padding:1rem; border-radius:16px; font-weight:800; cursor:pointer; color: var(--text); font-size: 0.9rem; text-transform: uppercase;">Stay</button>
            <button onclick="performLogout()" style="flex:2; border:none; padding:1rem; border-radius:16px; font-weight:900; cursor:pointer; background:#ef4444; color:white; font-size: 0.9rem; text-transform: uppercase; box-shadow: 0 10px 20px rgba(239, 68, 68, 0.25);">Confirm Exit</button>
        </div>
        <!-- Hidden form for logout -->
        <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>
    </div>
</div>

<!-- No Counselor Available Modal -->
<div id="noCounselorModal" class="no-counselor-modal-overlay" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.65); backdrop-filter:blur(14px); align-items:center; justify-content:center; animation: g-fade-in 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
    <div class="no-counselor-modal-card" style="background:var(--surface-solid); border:1px solid var(--border); border-radius:32px; padding:2.5rem 2.25rem; max-width:440px; width:90%; text-align:center; box-shadow:0 35px 80px rgba(0,0,0,0.35); position:relative; overflow:hidden; animation: g-toast-in 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #f59e0b, #ef4444);"></div>
        
        <div style="width: 76px; height: 76px; background: rgba(245, 158, 11, 0.12); color: #f59e0b; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0.5rem auto 1.5rem; font-size: 2.25rem; border: 2px solid rgba(245, 158, 11, 0.25); box-shadow: 0 0 25px rgba(245, 158, 11, 0.15);">
            <i class="ph-bold ph-headset"></i>
        </div>
        
        <h3 style="font-family:'Outfit',sans-serif; font-weight:900; font-size:1.5rem; margin-bottom:0.6rem; color: var(--text); letter-spacing: -0.02em;">No Counselor Available</h3>
        
        <p id="noCounselorModalMessage" style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1.75rem; font-weight: 500; line-height: 1.6;">
            No counselors are currently online or using the system. Please schedule an appointment or reach out to emergency hotlines.
        </p>

        <div style="display:flex; flex-direction:column; gap:0.75rem;">
            <a href="{{ route('student.appointments') }}" style="background:var(--primary); color:white; padding:0.85rem; border-radius:16px; font-weight:800; text-decoration:none; text-align:center; font-size:0.88rem; display:flex; align-items:center; justify-content:center; gap:0.5rem; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);">
                <i class="ph-bold ph-calendar-blank" style="font-size: 1.15rem;"></i> Schedule an Appointment
            </a>
            <a href="{{ route('emergency') }}" style="background:rgba(239, 68, 68, 0.08); color:#ef4444; border: 1.5px solid rgba(239, 68, 68, 0.25); padding:0.8rem; border-radius:16px; font-weight:800; text-decoration:none; text-align:center; font-size:0.85rem; display:flex; align-items:center; justify-content:center; gap:0.5rem;">
                <i class="ph-bold ph-phone-call" style="font-size: 1.15rem;"></i> Emergency Hotlines
            </a>
            <button onclick="closeNoCounselorModal()" style="background:var(--surface-2); border:1.5px solid var(--border); padding:0.75rem; border-radius:16px; font-weight:700; cursor:pointer; color: var(--text-dim); font-size: 0.85rem; margin-top: 0.25rem;">
                Close Window
            </button>
        </div>
    </div>
</div>

<?php if($userType === 'counselor' || $userType === 'admin'): ?>
{{-- ══════════════════════════════════════════════
     GLOBAL INCOMING CALL TOAST
══════════════════════════════════════════════ --}}
<div id="incomingCallOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(8px); z-index:99998; animation: g-fade-in 0.4s ease;"></div>
<div id="incomingCallToast" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); z-index:99999; width:400px; max-width:90%;">
    <div style="background:#fff; border-radius:24px; box-shadow:0 20px 60px rgba(0,0,0,0.18); border:2px solid #fca5a5; overflow:hidden; animation:g-toast-in 0.4s cubic-bezier(0.16,1,0.3,1);">
        <div style="height:3px; background:linear-gradient(90deg,#dc2626,#f87171); animation:g-call-pulse 1.2s ease-in-out infinite;"></div>
        <div style="padding:1.5rem;">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem;">
                <div style="width:44px; height:44px; border-radius:14px; background:#fee2e2; display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0; animation:g-pulse-ring 1.6s ease-out infinite;">📞</div>
                <div>
                    <p style="font-size:0.6rem; font-weight:900; text-transform:uppercase; letter-spacing:0.12em; color:#dc2626; margin:0;">Incoming Emergency Call</p>
                    <p id="gToastStudentName" style="font-size:0.95rem; font-weight:800; color:#0f172a; margin:0.1rem 0 0;">Student</p>
                    <p id="gToastRiskBadge" style="font-size:0.7rem; font-weight:700; color:#dc2626; margin:0;">⚠ High Risk</p>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.6rem;">
                <button id="gToastDeclineBtn" onclick="gDeclineCall()" style="background:#f1f5f9; border:1.5px solid #cbd5e1; color:#475569; padding:0.75rem; border-radius:14px; font-weight:800; font-size:0.78rem; cursor:pointer;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">Decline</button>
                <button id="gToastAcceptBtn" onclick="gAcceptCall()" style="background:#dc2626; border:none; color:white; padding:0.75rem; border-radius:14px; font-weight:800; font-size:0.78rem; cursor:pointer; box-shadow:0 6px 16px rgba(220,38,38,0.3);" onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">Accept Call</button>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<style>
@keyframes g-toast-in   { from{opacity:0;transform:translateY(30px) scale(0.9)} to{opacity:1;transform:translateY(0) scale(1)} }
@keyframes g-fade-in    { from{opacity:0} to{opacity:1} }
.g-focus-mode {
    max-width: 100vw !important;
    max-height: 100vh !important;
    width: 100vw !important;
    height: 100vh !important;
    margin: 0 !important;
    border-radius: 0 !important;
    position: fixed !important;
    inset: 0 !important;
    z-index: 99999999 !important;
}
</style>

<script>
(function() {
    /* ─── Global Constants ─── */
    const G_CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const G_RTC  = { iceServers:[{ urls:'stun:stun.l.google.com:19302' }] };
    
    /* ─── Shared UI Helpers ─── */
    window.gToggleFocusMode = function(boxId) {
        const box = document.getElementById(boxId);
        if (box) box.classList.toggle('g-focus-mode');
    };

    /* ══════════════════════════════════════════════
       COUNSELOR / ADMIN LOGIC
    ══════════════════════════════════════════════ */
    <?php if($userType === 'counselor' || $userType === 'admin'): ?>
    let g_callId = null;
    let g_pendingPoll = null;
    let g_msgPoll = null;
    let g_lastMsgId = 0;
    let g_activePendingId = null;
    let g_localStream = null;
    let g_peerConnection = null;
    let g_micOn = true;
    let g_camOn = true;
    let g_isClosing = false;
    let g_isMinimized = false;

    // Initialize: poll every 2500ms on standard pages, skip completely when on video call page
    if (!window.location.pathname.includes('/video-call/')) {
        g_pendingPoll = setInterval(pollForPendingCalls, 2500);
        pollForPendingCalls();
    }

    document.addEventListener('DOMContentLoaded', () => {
        // No-op, already started
    });

    async function pollForPendingCalls() {
        if (g_callId) return; 
        try {
            const res = await fetch("{{ route('counselor.emergency.calls.pending') }}", { headers:{'Accept':'application/json'} });
            const data = await res.json();
            const calls = data.calls ?? [];
            if (calls.length > 0) {
                const first = calls[0];
                showIncomingToast(first);
            } else {
                hideIncomingToast();
            }
        } catch(e) {}
    }

    function showIncomingToast(call) {
        g_activePendingId = call.call_id;
        const queueInfo = call.total_queued > 1 ? ` (Queue #${call.queue_position} of ${call.total_queued})` : '';
        document.getElementById('gToastStudentName').textContent = call.student_name + (call.roll_number !== 'N/A' ? ` — ${call.roll_number}` : '') + queueInfo;
        document.getElementById('gToastRiskBadge').textContent   = `⚠ ${call.risk_level} Risk — Score ${call.overall_score}`;
        document.getElementById('incomingCallOverlay').style.display = 'block';
        document.getElementById('incomingCallToast').style.display = 'block';
    }

    window.hideIncomingToast = function() {
        const el = document.getElementById('incomingCallToast');
        const overlay = document.getElementById('incomingCallOverlay');
        if (el) el.style.display = 'none';
        if (overlay) overlay.style.display = 'none';
        g_activePendingId = null;
    }

    window.gAcceptCall = async function(id) {
        const targetId = id || g_activePendingId;
        if (!targetId) return;
        
        const btn = document.getElementById('gToastAcceptBtn');
        if (btn) { btn.disabled = true; btn.textContent = 'Accepting…'; }

        try {
            const res = await fetch(`/counselor/emergency-calls/${targetId}/accept`, { 
                method:'POST', 
                headers:{
                    'X-CSRF-TOKEN':G_CSRF,
                    'Accept':'application/json',
                    'Content-Type':'application/json'
                } 
            });
            const data = await res.json();
            if (data.success) {
                window.location.href = `/counselor/video-call/${targetId}`;
            } else {
                throw new Error(data.error || 'Failed to accept call');
            }
        } catch(e) {
            console.error('Accept call error:', e);
            if (window.App?.toast) App.toast({ type:'error', title:'Accept Failed', message: e.message || 'Could not connect to the call.' });
            if (btn) { btn.disabled = false; btn.textContent = 'Accept Call'; }
        }
    };

    window.gDeclineCall = async function() {
        if (!g_activePendingId) return;
        try { fetch(`/counselor/emergency-calls/${g_activePendingId}/decline`, { method:'POST', headers:{'X-CSRF-TOKEN':G_CSRF} }); } catch(e){}
        window.hideIncomingToast();
    };
    <?php endif; ?>

    /* ─── Global Modal Helpers ─── */
    window.openNoCounselorModal = function(msg) {
        const modal = document.getElementById('noCounselorModal');
        const msgEl = document.getElementById('noCounselorModalMessage');
        if (msgEl && msg) msgEl.textContent = msg;
        if (modal) modal.style.display = 'flex';
    };

    window.closeNoCounselorModal = function() {
        const modal = document.getElementById('noCounselorModal');
        if (modal) modal.style.display = 'none';
    };

    /* ══════════════════════════════════════════════
       STUDENT LOGIC
    ══════════════════════════════════════════════ */
    <?php if($userType === 'student'): ?>
    let g_stdCallId = null;

    window.gStartCallStudent = async function() {
        if (g_stdCallId) return;
        const btn = event?.currentTarget;
        const originalText = btn ? btn.innerHTML : 'Call Counselor';
        if (btn) { 
            btn.disabled = true; 
            btn.innerHTML = '<i class="ph ph-circle-notch ph-spin"></i> Checking availability...'; 
        }
        try {
            const res = await fetch("{{ route('student.emergency.call.request') }}", {
                method: 'POST', 
                headers: {
                    'X-CSRF-TOKEN': G_CSRF,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            const data = await res.json();
            if (!data.success) { 
                const msg = data.message || 'No counselors are currently online or using the system.';
                window.openNoCounselorModal(msg);
                if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
                return; 
            }
            g_stdCallId = data.call_id;
            // Immediately redirect the student to their dedicated video call waiting room
            window.location.href = `/student/video-call/${g_stdCallId}`;
        } catch (e) { 
            console.error('Student call request error:', e);
            const errorMsg = 'No counselors are currently online or using the system. Please try again later or schedule an appointment.';
            window.openNoCounselorModal(errorMsg);
            if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
        }
    };
    <?php endif; ?>
})();
</script>
