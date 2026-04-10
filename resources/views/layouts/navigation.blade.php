@php
    $user = Auth::user();
    // Normalize user type to lowercase string for robust comparison
    $userType = $user && $user->user_type ? strtolower($user->user_type->value ?? (string)$user->user_type) : '';
    $currentRoute = Route::currentRouteName();
@endphp

<aside class="sidebar glass" style="margin: 1rem; height: calc(100vh - 2rem); border-radius: 24px; border: 1px solid var(--glass-border); box-shadow: var(--shadow-lg); left: 0; top: 0; position: fixed;">
    <div class="sidebar-header" style="padding: 1.5rem 1.25rem;">
        <div class="sidebar-brand">
            <div class="sidebar-logo-container" style="position: relative;">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-emerald-500/20">
                    P
                </div>
                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-400 border-2 border-white dark:border-slate-900 rounded-full"></div>
            </div>
            <div class="sidebar-title" style="margin-left: 0.5rem;">
                <span style="font-weight: 800; font-size: 1.2rem; letter-spacing: -0.02em; color: var(--text);">PSU <span style="color: var(--primary);">Portal</span></span><br>
                <span style="font-size: 0.65rem; opacity: 0.5; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Mental Health System</span>
            </div>
        </div>
    </div>

    <!-- Emergency Help Button (Premium Style) -->
    <div class="emergency-container" style="padding: 0 1.25rem 1.5rem;">
        <a href="{{ route('emergency') }}" class="btn-emergency" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 0.85rem; border-radius: 16px; display: flex; align-items: center; justify-content: center; gap: 0.75rem; text-decoration: none; font-weight: 700; font-size: 0.85rem; border: 1px solid rgba(239, 68, 68, 0.2); transition: all 0.3s ease;">
            <span style="font-size: 1.1rem;">🚨</span> <span>Emergency Help</span>
        </a>
    </div>

    <nav class="sidebar-nav" style="flex: 1; overflow-y: auto; padding: 0 0.75rem; display: flex; flex-direction: column; gap: 4px;">
        @if($userType === 'student')
            <a href="{{ route('student.dashboard') }}" class="sidebar-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                <div class="link-icon">📊</div> <span>Dashboard</span>
            </a>
            <a href="{{ route('student.assessment') }}" class="sidebar-link {{ request()->routeIs('student.assessment') ? 'active' : '' }}">
                <div class="link-icon">📝</div> <span>Pre-Assessment</span>
            </a>
            <a href="{{ route('student.notes.index') }}" class="sidebar-link {{ request()->routeIs('student.notes.*') ? 'active' : '' }}">
                <div class="link-icon">✉️</div> <span>Quick Note</span>
            </a>
            <a href="{{ route('student.chat') }}" class="sidebar-link {{ request()->routeIs('student.chat') ? 'active' : '' }}">
                <div class="link-icon">✨</div> <span>Talk to Aria</span>
            </a>
            <a href="{{ route('student.mood') }}" class="sidebar-link {{ request()->routeIs('student.mood') ? 'active' : '' }}">
                <div class="link-icon">📓</div> <span>Mood Journal</span>
            </a>
            <a href="{{ route('student.appointments') }}" class="sidebar-link {{ request()->routeIs('student.appointments') ? 'active' : '' }}">
                <div class="link-icon">📅</div> <span>Appointments</span>
            </a>
            <a href="{{ route('student.mindfulness.index') }}" class="sidebar-link {{ request()->routeIs('student.mindfulness.*') ? 'active' : '' }}">
                <div class="link-icon">🧘</div> <span>Mindfulness</span>
            </a>
            <a href="{{ route('student.reports.index') }}" class="sidebar-link {{ request()->routeIs('student.reports.*') ? 'active' : '' }}">
                <div class="link-icon">📈</div> <span>My Progress</span>
            </a>

        @elseif($userType === 'counselor')
            <a href="{{ route('counselor.dashboard') }}" class="sidebar-link {{ request()->routeIs('counselor.dashboard') ? 'active' : '' }}">
                <div class="link-icon">📊</div> <span>Dashboard</span>
            </a>
            <a href="{{ route('counselor.appointments.index') }}" class="sidebar-link {{ request()->routeIs('counselor.appointments.*') ? 'active' : '' }}">
                <div class="link-icon">📅</div> <span>Appointments</span>
            </a>
            <a href="{{ route('counselor.availability') }}" class="sidebar-link {{ request()->routeIs('counselor.availability') ? 'active' : '' }}">
                <div class="link-icon">⏰</div> <span>My Availability</span>
            </a>
            <a href="{{ route('counselor.students.index') }}" class="sidebar-link {{ request()->routeIs('counselor.students.*') ? 'active' : '' }}">
                <div class="link-icon">👥</div> <span>Student Records</span>
            </a>
            <a href="{{ route('counselor.ledger.index') }}" class="sidebar-link {{ request()->routeIs('counselor.ledger.*') ? 'active' : '' }}">
                <div class="link-icon">📖</div> <span>Activity Ledger</span>
            </a>

        @elseif($userType === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <div class="link-icon">🛡️</div> <span>Admin Panel</span>
            </a>
            <a href="{{ route('admin.staff.index') }}" class="sidebar-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                <div class="link-icon">👨‍🏫</div> <span>Manage Counselors</span>
            </a>
            <a href="{{ route('counselor.availability') }}" class="sidebar-link {{ request()->routeIs('counselor.availability') ? 'active' : '' }}">
                <div class="link-icon">⏰</div> <span>My Availability</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <div class="link-icon">📋</div> <span>System Reports</span>
            </a>
        @endif
    </nav>

    <div class="sidebar-footer" style="padding: 1.25rem; background: rgba(0,0,0,0.02); border-radius: 0 0 24px 24px; border-top: 1px solid var(--border);">
        <div class="user-profile-block" style="display:flex; align-items:center; gap:0.85rem; margin-bottom:1.25rem;">
            <div class="user-avatar-initials" style="width:44px; height:44px; border-radius:14px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); display:flex; align-items:center; justify-content:center; font-weight:800; color:white; font-size:0.9rem; shadow: var(--shadow-sm);">
                @php
                    $nameParts = explode(' ', $user->full_name);
                    echo strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                @endphp
            </div>
            <div class="user-info" style="overflow:hidden;">
                <div class="user-name" style="font-weight:800; font-size:0.85rem; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $user->full_name }}</div>
                <div class="user-role" style="font-size:0.7rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.02em;">{{ $userType }}</div>
            </div>
        </div>
        
        <div class="sidebar-actions" style="display:flex; flex-direction:column; gap:6px;">
            <a href="{{ route('notifications.index') }}" class="sidebar-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" style="border-radius: 12px;">
                <div class="link-icon">🔔</div> <span>Notifications</span>
            </a>
            
            <button id="themeToggle" class="sidebar-link theme-toggle-btn w-full text-left" style="border-radius: 12px; background: transparent; border: none; width: 100%; cursor: pointer;">
                <div class="link-icon" id="themeIcon">🌙</div> <span id="themeLabel">Dark Mode</span>
            </button>
            
            <button onclick="openSignOutModal()" class="sidebar-link signout-btn" style="border-radius: 12px; background: rgba(239, 68, 68, 0.05); color: #ef4444; border: none; width: 100%; cursor: pointer; margin-top: 4px; display: flex; align-items: center; gap: 4px; text-align: left; padding: 0.75rem;">
                <div class="link-icon">🚪</div> <span style="font-weight: 800;">Sign Out</span>
            </button>
        </div>
    </div>
</aside>

<!-- Sign Out Confirmation Modal -->
<div id="signOutModal" class="signout-modal-overlay" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.45); backdrop-filter:blur(8px); align-items:center; justify-content:center;">
    <div class="signout-modal" style="background:var(--surface-solid); border:1px solid var(--border); border-radius:24px; padding:2rem; max-width:400px; width:90%; text-align:center; box-shadow:var(--shadow-lg);">
        <div class="signout-icon" style="font-size:2.5rem; margin-bottom:1rem;">🚪</div>
        <h3 class="signout-title" style="font-family:'Outfit',sans-serif; font-weight:800; font-size:1.25rem; margin-bottom:0.5rem;">Sign Out?</h3>
        <p class="signout-text" style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1.5rem;">You're about to leave your session. Any unsaved progress may be lost.</p>
        <div class="signout-actions" style="display:flex; gap:0.75rem;">
            <button onclick="closeSignOutModal()" class="btn-stay" style="flex:1; background:var(--surface-2); border:none; padding:0.75rem; border-radius:12px; font-weight:600; cursor:pointer;">Stay</button>
            <button onclick="performLogout()" class="w-full bg-red-500 text-white" style="flex:2; border:none; padding:0.75rem; border-radius:12px; font-weight:700; cursor:pointer; background:#ef4444; color:white;">Yes, Sign Out</button>
        </div>
        <!-- Hidden form for logout -->
        <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>
    </div>
</div>
