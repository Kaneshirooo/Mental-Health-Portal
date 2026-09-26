@extends('layouts.app')

@push('styles')
<style>
    .role-chip {
        padding: 0.2rem 0.65rem;
        border-radius: 99px;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .role-student { background: rgba(13, 148, 136, 0.1); color: #0d9488; border: 1px solid rgba(13, 148, 136, 0.2); }
    .role-counselor { background: rgba(37, 99, 235, 0.1); color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.2); }
    .role-admin { background: rgba(220, 38, 38, 0.1); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.2); }
    
    .delete-confirm { display:none; gap:.5rem; align-items:center; }
    .delete-confirm.show { display:inline-flex; }
    
    .filter-bar { display:flex; gap:.75rem; align-items:center; flex-wrap:wrap; }
    .filter-bar input { flex:1; min-width:200px; }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1300px; margin: 0 auto; padding: 2rem 1.5rem 4rem;">
    
    <!-- Header -->
    <header class="staggered" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3.5rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 0.5rem;">System Personnel</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.75rem; font-weight: 900; color: var(--text); letter-spacing: -0.04em; margin: 0;">User Management</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin-top: 0.5rem;">Manage authenticated identities and access protocols.</p>
        </div>
        <div style="background: var(--surface-2); padding: 0.75rem 1.5rem; border-radius: 100px; border: 1px solid var(--border); display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-size: 0.75rem; font-weight: 900; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em;">Total Registry: {{ count($users) }}</span>
        </div>
    </header>

    <!-- Provisioning Section (Add User) -->
    <div class="staggered" style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 32px; padding: 3rem; margin-bottom: 4rem; box-shadow: 0 20px 40px rgba(0,0,0,0.03);">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text); margin-bottom: 2rem;">Add New User</h2>
        
        <form id="addUserForm" method="POST" action="{{ route('admin.users.store') }}" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;">
            @csrf
            <div class="form-group">
                <label style="display:block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.75rem; letter-spacing: 0.05em;">Full Name</label>
                <input type="text" name="full_name" required placeholder="John Doe" class="form-input-premium" style="width: 100%;">
            </div>
            <div class="form-group">
                <label style="display:block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.75rem; letter-spacing: 0.05em;">Email Address</label>
                <input type="email" name="email" required placeholder="john@example.com" class="form-input-premium" style="width: 100%;">
            </div>
            <div class="form-group">
                <label style="display:block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.75rem; letter-spacing: 0.05em;">Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" required placeholder="••••••••" class="form-input-premium" style="width: 100%;">
                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label style="display:block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.75rem; letter-spacing: 0.05em;">Identity Role</label>
                <select name="user_type" required class="form-input-premium" style="width: 100%;">
                    <option value="">Select Role</option>
                    <option value="student">Student</option>
                    <option value="counselor">Counselor</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            <div style="grid-column: 1/-1; border-top: 1px solid var(--border); padding-top: 2rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn-primary" style="padding: 1rem 2.5rem; font-weight: 800; border-radius: 16px; text-transform: uppercase; font-size: 0.9rem;">Register User →</button>
            </div>
        </form>
    </div>

    <!-- User List Section -->
    <div id="usersContainer" class="staggered" style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 32px; padding: 3rem; box-shadow: 0 30px 60px rgba(0,0,0,0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; border-bottom: 1px solid var(--border); padding-bottom: 2rem;">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text); margin: 0;">System Registry</h2>
            
            <div style="display: flex; gap: 1rem; align-items: center;">
                <input type="text" id="liveSearch" placeholder="Filter users..." oninput="liveFilter()" 
                       class="form-input-premium" style="width: 250px; background: var(--surface-2);">
                
                <form id="filterForm" method="GET" action="{{ route('admin.users.index') }}" style="display:flex;">
                    <select name="role_filter" class="form-input-premium" style="background: var(--surface-2);">
                        <option value="">All Roles</option>
                        @foreach (['student','counselor','admin'] as $r)
                            <option value="{{ $r }}" {{ request('role_filter') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        @if (count($users) > 0)
        <div id="usersList" style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0 0.75rem;">
                <thead>
                    <tr style="text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.7rem; color: var(--text-dim); font-weight: 800;">
                        <th style="padding: 1.5rem; text-align: left;">Name & Identity</th>
                        <th style="padding: 1.5rem; text-align: left;">Email Address</th>
                        <th style="padding: 1.5rem; text-align: left;">Account Role</th>
                        <th style="padding: 1.5rem; text-align: left;">Date Added</th>
                        <th style="padding: 1.5rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @foreach ($users as $user)
                    <tr class="user-row staggered-row" 
                        style="background: var(--surface-2); border-radius: 20px; transition: all 0.3s ease;"
                        data-name="{{ strtolower($user?->full_name ?? '') }}"
                        data-email="{{ strtolower($user?->email ?? '') }}">
                        
                        <td style="padding: 1.5rem; border-top-left-radius: 20px; border-bottom-left-radius: 20px;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 44px; height: 44px; border-radius: 12px; background: var(--surface-solid); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-weight: 900; color: var(--primary);">
                                    {{ strtoupper(substr($user->full_name, 0, 1)) }}
                                </div>
                                <div style="font-weight: 800; color: var(--text);">{{ $user?->full_name ?? 'N/A' }}</div>
                            </div>
                        </td>
                        
                        <td style="padding: 1.5rem; color: var(--text-muted); font-size: 0.95rem; font-weight: 600;">{{ $user?->email ?? '' }}</td>
                        
                        <td style="padding: 1.5rem;">
                            @php
                                $roleStyles = [
                                    'student' => ['bg' => 'rgba(16, 185, 129, 0.08)', 'color' => '#10b981', 'label' => 'Student'],
                                    'counselor' => ['bg' => 'rgba(99, 102, 241, 0.08)', 'color' => '#6366f1', 'label' => 'Counselor'],
                                    'admin' => ['bg' => 'rgba(239, 68, 68, 0.08)', 'color' => '#ef4444', 'label' => 'Administrator'],
                                ];
                                $s = $roleStyles[$user->user_type->value] ?? ['bg' => 'var(--surface-3)', 'color' => 'var(--text-dim)', 'label' => $user->user_type->value];
                            @endphp
                            <span style="display: inline-flex; align-items: center; padding: 0.4rem 1rem; border-radius: 100px; background: {{ $s['bg'] }}; color: {{ $s['color'] }}; font-weight: 800; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em; border: 1px solid currentColor;">
                                {{ $s['label'] }}
                            </span>
                        </td>
                        
                        <td style="padding: 1.5rem; color: var(--text-muted); font-size: 0.95rem; font-weight: 600;">
                            {{ $user?->created_at ? $user?->created_at?->format('F d, Y') : '-' }}
                        </td>
                        
                        <td style="padding: 1.5rem; text-align: right; border-top-right-radius: 20px; border-bottom-right-radius: 20px;">
                            @if (($user?->user_id ?? 0) !== auth()->id())
                                <button class="btn-sm" style="background: rgba(239, 68, 68, 0.08); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); font-weight: 800; text-transform: uppercase; padding: 0.5rem 1rem; border-radius: 12px; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='#ef4444'; this.style.color='white'" onmouseout="this.style.background='rgba(239, 68, 68, 0.08)'; this.style.color='#ef4444'" onclick="confirmDelete({{ $user?->user_id ?? 0 }}, this)">Remove</button>
                                <span class="delete-confirm" id="dc-{{ $user?->user_id ?? 0 }}">
                                    <span style="font-size: 0.75rem; font-weight: 800; color: #ef4444; margin-right: 0.5rem;">CONFIRM?</span>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user?->user_id ?? 0) }}" class="delete-form" style="display: inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-sm" style="background: #ef4444; color: white; border: none; font-weight: 800; padding: 0.5rem 1rem; border-radius: 12px;">YES</button>
                                    </form>
                                    <button class="btn-sm" style="background: var(--surface-3); border: 1px solid var(--border); font-weight: 800; padding: 0.5rem 1rem; border-radius: 12px;" onclick="cancelDelete({{ $user?->user_id ?? 0 }})">NO</button>
                                </span>
                            @else
                                <span style="font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.1em;">Current Session</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div style="text-align: center; padding: 6rem 2rem;">
                <i class="ph ph-user-circle-plus" style="font-size: 4rem; color: var(--text-muted); opacity: 0.15; margin-bottom: 1.5rem; display: block;"></i>
                <p style="color: var(--text-muted); font-weight: 800; font-size: 1.25rem;">No users found in the registry.</p>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.gsap) {
        gsap.from('.staggered', { y: 40, opacity: 0, duration: 1, stagger: 0.15, ease: "expo.out", clearProps: "all" });
        gsap.from('.staggered-row', { x: -20, opacity: 0, duration: 0.8, stagger: 0.05, ease: "expo.out", delay: 0.4, clearProps: "all" });
    }
});

function liveFilter() {
    const q = document.getElementById('liveSearch').value.toLowerCase();
    document.querySelectorAll('.user-row').forEach(row => {
        row.style.display = row.dataset.name.includes(q) || row.dataset.email.includes(q) ? '' : 'none';
    });
}
function confirmDelete(id, btn) {
    btn.style.display = 'none';
    document.getElementById('dc-' + id).classList.add('show');
}
function cancelDelete(id) {
    document.getElementById('dc-' + id).classList.remove('show');
    const btn = document.querySelector('[onclick="confirmDelete(' + id + ', this)"]');
    if (btn) btn.style.display = '';
}

// Add User AJAX — inject new row directly without page fetch
document.getElementById('addUserForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const btn  = form.querySelector('button[type="submit"]');
    const orig = AjaxHelpers.startBtn(btn, 'Registering...');
    const fd   = new FormData(form);

    try {
        const res  = await fetch(form.action, {
            method: 'POST', body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            App.toast({ type: 'success', title: 'Registered', message: data.message });
            form.reset();
            // Inject row directly from JSON — zero page fetch
            const tbody = document.getElementById('tableBody');
            if (tbody && data.user) {
                const tr = AjaxHelpers.buildUserRow(data.user);
                tbody.prepend(tr);
                AjaxHelpers.flashRow(tr);
                if (window.gsap) gsap.from(tr, { x: -20, opacity: 0, duration: 0.5, ease: 'expo.out' });
            }
        } else {
            App.toast({ type: 'error', title: 'Error', message: data.message || 'Failed to add user.' });
        }
    } catch (err) {
        App.toast({ type: 'error', title: 'Error', message: 'Connection failed.' });
    } finally {
        AjaxHelpers.stopBtn(btn, orig);
    }
});

// Delete User AJAX (Delegated) — slide row out
document.addEventListener('submit', async function(e) {
    if (e.target.classList.contains('delete-form')) {
        e.preventDefault();
        const form = e.target;
        const btn  = form.querySelector('button[type="submit"]');
        const row  = form.closest('.user-row');
        const orig = AjaxHelpers.startBtn(btn);

        try {
            const res  = await fetch(form.action, {
                method: 'POST', body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                App.toast({ type: 'success', title: 'Removed', message: data.message });
                AjaxHelpers.removeRow(row);
            } else {
                App.toast({ type: 'error', title: 'Error', message: data.message || 'Failed to delete user.' });
                AjaxHelpers.stopBtn(btn, orig);
            }
        } catch (err) {
            App.toast({ type: 'error', title: 'Error', message: 'Connection failed.' });
            AjaxHelpers.stopBtn(btn, orig);
        }
    }
});

// Role Filter AJAX — swap only #usersList, preserve scroll
document.querySelector('#filterForm select').addEventListener('change', async function() {
    const val = this.value;
    const url = new URL(window.location.href);
    if (val) url.searchParams.set('role_filter', val);
    else url.searchParams.delete('role_filter');
    history.pushState({}, '', url);
    await AjaxHelpers.refreshSection(url.toString(), '#usersList');
});
</script>
@endsection
