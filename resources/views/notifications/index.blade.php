@extends('layouts.app')

@push('styles')
<style>
    .notif-container {
        max-width: 850px;
        margin: 2rem auto 5rem;
        padding: 0 1.5rem;
    }
    .notif-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 3rem;
    }
    .notif-card {
        background: #ffffff !important;
        border: 1px solid var(--border) !important;
        border-radius: 20px;
        padding: 1.5rem 1.75rem;
        display: flex;
        gap: 1.25rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        opacity: 1 !important;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    }
    .notif-card.unread {
        background: #f0fdf4 !important;
        border-color: #86efac !important;
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.08) !important;
    }
    .dark-mode .notif-card {
        background: #1e293b !important;
        border-color: #334155 !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }
    .dark-mode .notif-card.unread {
        background: #064e3b !important;
        border-color: #059669 !important;
    }
    .notif-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }
    .notif-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.4rem;
        font-weight: 800;
    }
    .notif-title {
        color: #0f172a !important;
        font-weight: 800 !important;
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        margin: 0.15rem 0 0.4rem;
    }
    .dark-mode .notif-title {
        color: #f8fafc !important;
    }
    .notif-message {
        color: #334155 !important;
        font-size: 0.95rem;
        font-weight: 500;
        line-height: 1.6;
        margin-bottom: 1.25rem;
    }
    .dark-mode .notif-message {
        color: #cbd5e1 !important;
    }
    .notif-time {
        color: #64748b !important;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .dark-mode .notif-time {
        color: #94a3b8 !important;
    }
    .btn-action-small {
        background: #f1f5f9 !important;
        border: 1px solid #cbd5e1 !important;
        padding: 0.55rem 1.15rem;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: 800;
        color: #0f172a !important;
        cursor: pointer;
        transition: all 0.25s ease;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-action-small:hover {
        background: var(--primary) !important;
        color: #ffffff !important;
        border-color: var(--primary) !important;
    }
    .dark-mode .btn-action-small {
        background: #334155 !important;
        border-color: #475569 !important;
        color: #f8fafc !important;
    }
    .dark-mode .btn-action-small:hover {
        background: var(--primary) !important;
        color: #ffffff !important;
        border-color: var(--primary) !important;
    }
</style>
@endpush

@section('content')
<div class="notif-container">
    <!-- Header -->
    <header class="staggered" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3.5rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 0.5rem;">Clinical Monitoring</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.75rem; font-weight: 900; color: var(--text); letter-spacing: -0.04em; margin: 0;">Activity Feed</h1>
            <p style="color: var(--text-muted); font-size: 1.05rem; font-weight: 500; margin-top: 0.4rem;">Real-time stream of clinical alerts and system updates.</p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            @if($notifications->where('is_read', 0)->count() > 0)
                <button onclick="markAllRead()" class="btn-action-small" style="background: rgba(16, 185, 129, 0.12) !important; color: #059669 !important; border: 1px solid rgba(16, 185, 129, 0.3) !important;">Mark all read</button>
            @endif
            @if($notifications->count() > 0)
                <button onclick="clearAll()" class="btn-action-small" style="color: #dc2626 !important; background: rgba(239, 68, 68, 0.08) !important; border: 1px solid rgba(239, 68, 68, 0.2) !important;">Clear All</button>
            @endif
        </div>
    </header>

    @if($notifications->isEmpty())
        <div style="background: var(--surface-solid); border: 2px dashed var(--border); border-radius: 32px; padding: 6rem 2rem; text-align: center;" class="staggered">
            <i class="ph ph-bell-simple-slash" style="font-size: 4rem; color: var(--text-muted); opacity: 0.25; margin-bottom: 1.5rem; display: block;"></i>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text); margin: 0;">No active alerts</h3>
            <p style="color: var(--text-muted); font-weight: 600; margin-top: 0.5rem;">Your activity feed is currently clear.</p>
        </div>
    @else
        <div id="notifList" style="display: flex; flex-direction: column; gap: 1rem;">
            @foreach($notifications as $n)
                @php
                    $userRole = auth()->user() && auth()->user()->user_type ? strtolower(auth()->user()->user_type->value ?? (string)auth()->user()->user_type) : 'student';
                    $styleMap = [
                        'appointment' => ['icon' => 'ph-calendar-check', 'bg' => 'rgba(99, 102, 241, 0.12)', 'color' => '#4f46e5', 'label' => 'Appointment'],
                        'note'        => ['icon' => 'ph-chat-centered-text', 'bg' => 'rgba(139, 92, 246, 0.12)', 'color' => '#7c3aed', 'label' => 'Clinical Note'],
                        'mood_alert'  => ['icon' => 'ph-activity', 'bg' => 'rgba(239, 68, 68, 0.12)', 'color' => '#dc2626', 'label' => 'Mood Alert'],
                        'emergency'   => ['icon' => 'ph-phone-call', 'bg' => 'rgba(239, 68, 68, 0.15)', 'color' => '#dc2626', 'label' => 'Emergency Call'],
                        'assessment'  => ['icon' => 'ph-clipboard-text', 'bg' => 'rgba(16, 185, 129, 0.15)', 'color' => '#059669', 'label' => 'Assessment'],
                        'system'      => ['icon' => 'ph-bell', 'bg' => 'rgba(107, 114, 128, 0.12)', 'color' => '#4b5563', 'label' => 'System Alert'],
                    ];
                    $m = $styleMap[$n->type] ?? $styleMap['system'];

                    $contextUrl = '#';
                    if ($n->type === 'appointment') {
                        $contextUrl = $userRole === 'student' ? route('student.appointments') : route('counselor.appointments.index');
                    } elseif ($n->type === 'note') {
                        $contextUrl = $userRole === 'student' ? route('student.notes.index') : route('counselor.dashboard');
                    } elseif ($n->type === 'emergency') {
                        $contextUrl = $userRole === 'student' ? route('emergency') : route('counselor.emergency.calls.logs');
                    } elseif ($n->type === 'mood_alert') {
                        $contextUrl = $userRole === 'admin' ? route('admin.dashboard') : route('counselor.dashboard');
                    } elseif ($n->type === 'assessment') {
                        $contextUrl = $userRole === 'student' ? route('student.reports.index') : route('counselor.students.index');
                    }
                @endphp
                <div class="notif-card {{ !$n->is_read ? 'unread' : '' }}" id="notif-{{ $n->notification_id }}">
                    <div class="notif-icon-box" style="background: {{ $m['bg'] }}; color: {{ $m['color'] }};">
                        <i class="ph {{ $m['icon'] }}"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <span style="font-size: 0.72rem; font-weight: 850; color: {{ $m['color'] }}; text-transform: uppercase; letter-spacing: 0.1em;">{{ $m['label'] }}</span>
                                <h4 class="notif-title">{{ $n->title }}</h4>
                            </div>
                            <span class="notif-time">
                                {{ $n->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <p class="notif-message">
                            {{ $n->message }}
                        </p>
                        <div style="display: flex; gap: 0.75rem; align-items: center;">
                            @if(!$n->is_read)
                                <button onclick="markRead({{ $n->notification_id }})" class="btn-action-small" style="background: rgba(16, 185, 129, 0.12) !important; color: #059669 !important; border-color: rgba(16, 185, 129, 0.3) !important;">Mark Read</button>
                            @endif
                            @if($contextUrl !== '#')
                                <a href="{{ $contextUrl }}" class="btn-action-small">View Details</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.gsap) {
        gsap.from('.notif-header', { y: -20, opacity: 0, duration: 0.6, ease: "expo.out", clearProps: "all" });
        gsap.from('.notif-card', { y: 20, opacity: 0, duration: 0.6, stagger: 0.08, ease: "expo.out", delay: 0.1, clearProps: "all" });
    }
});

async function markRead(id) {
    try {
        const res = await fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            const card = document.getElementById(`notif-${id}`);
            card.classList.remove('unread');
            card.style.background = 'var(--surface-solid)';
            card.style.borderColor = 'var(--border)';
            const btn = Array.from(card.querySelectorAll('.btn-action-small')).find(b => b.textContent.toLowerCase().includes('mark read'));
            if (btn) btn.remove();
            if (window.updateNotifCount) window.updateNotifCount();
            App.toast({ type: 'success', title: 'Read', message: 'Notification marked as read.' });
        }
    } catch(e) {
        App.toast({ type: 'error', title: 'Error', message: 'Failed to update notification.' });
    }
}

async function markAllRead() {
    try {
        const res = await fetch('{{ route("notifications.read-all") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            document.querySelectorAll('.notif-card').forEach(card => {
                card.classList.remove('unread');
                card.style.background = 'var(--surface-solid)';
                card.style.borderColor = 'var(--border)';
                const btn = card.querySelector('.btn-action-small');
                if (btn && btn.textContent.toLowerCase().includes('mark read')) {
                    btn.remove();
                }
            });
            const allReadBtn = document.querySelector('button[onclick="markAllRead()"]');
            if (allReadBtn) allReadBtn.remove();
            if (window.updateNotifCount) window.updateNotifCount();
            App.toast({ type: 'success', title: 'Updated', message: 'All notifications marked as read.' });
        }
    } catch(e) {
        App.toast({ type: 'error', title: 'Error', message: 'Failed to update notifications.' });
    }
}

async function clearAll() {
    if (!confirm('Permanently clear all notifications?')) return;
    try {
        const res = await fetch('{{ route("notifications.clear") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            const list = document.getElementById('notifList');
            if (list) {
                gsap.to('.notif-card', {
                    x: 50, opacity: 0, stagger: 0.05, duration: 0.4, onComplete: () => {
                        list.innerHTML = `
                            <div style="background: var(--surface-solid); border: 2px dashed var(--border); border-radius: 32px; padding: 6rem 2rem; text-align: center;" class="staggered">
                                <i class="ph ph-bell-simple-slash" style="font-size: 4rem; color: var(--text-muted); opacity: 0.15; margin-bottom: 1.5rem; display: block;"></i>
                                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text-dim); margin: 0;">No active alerts</h3>
                                <p style="color: var(--text-muted); font-weight: 600; margin-top: 0.5rem;">Your activity feed is currently clear.</p>
                            </div>
                        `;
                        const headerBtns = document.querySelector('header div:last-child');
                        if (headerBtns) headerBtns.remove();
                    }
                });
            }
            if (window.updateNotifCount) window.updateNotifCount();
            App.toast({ type: 'success', title: 'Cleared', message: 'Activity feed cleared.' });
        }
    } catch(e) {
        App.toast({ type: 'error', title: 'Error', message: 'Failed to clear notifications.' });
    }
}
</script>
@endpush
@endsection
