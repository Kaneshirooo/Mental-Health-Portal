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
<div class="container" style="padding-top: 2rem; padding-bottom: 4rem;">
    <div class="dashboard-header" style="margin-bottom: 2.5rem;">
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem;">👥 Manage Users</h1>
        <p style="color: var(--text-muted); font-weight: 500;">{{ count($users) }} user(s) shown in the directory</p>
    </div>

    <!-- Add user form -->
    <div class="card" style="background: var(--surface-solid); padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm); margin-bottom: 2rem;">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; color: var(--text); margin-bottom: 1.5rem;">Add New User</h2>
        <form method="POST" action="{{ route('admin.users.store') }}" style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem 1.5rem">
            @csrf
            <div class="form-group">
                <label style="display:block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;">Full Name *</label>
                <input type="text" name="full_name" required placeholder="John Doe" class="form-input" style="width: 100%;">
            </div>
            <div class="form-group">
                <label style="display:block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;">Email *</label>
                <input type="email" name="email" required placeholder="user@example.com" class="form-input" style="width: 100%;">
            </div>
            <div class="form-group">
                <label style="display:block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;">Password *</label>
                <input type="password" name="password" required placeholder="Min. 6 chars" class="form-input" style="width: 100%;">
            </div>
            <div class="form-group">
                <label style="display:block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;">Role *</label>
                <select name="user_type" required class="form-input" style="width: 100%;">
                    <option value="">Select Role</option>
                    <option value="student">Student Account</option>
                    <option value="counselor">Clinical Counselor</option>
                    <option value="admin">System Architect</option>
                </select>
            </div>
            <div style="grid-column:1/-1; padding-top: 0.5rem;">
                <button type="submit" class="btn-primary" style="padding: 0.85rem 2rem;">➕ Add User</button>
            </div>
        </form>
    </div>

    <!-- User list with filters -->
    <div class="card" style="background: var(--surface-solid); padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; color: var(--text); margin-bottom: 1.5rem;">All Users</h2>

        <!-- Search & role filter toolbar -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="filter-bar" style="margin-bottom:2rem">
            <input type="text" name="search" id="liveSearch"
                   placeholder="🔍 Search by name or email identity…"
                   value="{{ request('search') }}"
                   oninput="liveFilter()" class="form-input">
            
            <select name="role_filter" onchange="this.form.submit()" class="form-input" style="width: auto; min-width: 160px;">
                <option value="">All Architectures</option>
                @foreach (['student','counselor','admin'] as $r)
                    <option value="{{ $r }}" {{ request('role_filter') === $r ? 'selected' : '' }}>
                        {{ ucfirst($r) }}
                    </option>
                @endforeach
            </select>
            
            <button type="submit" class="btn-primary btn-sm">Filter</button>
            @if (request('search') || request('role_filter'))
                <a href="{{ route('admin.users.index') }}" class="btn-secondary btn-sm" style="text-decoration:none">Clear</a>
            @endif
        </form>

        @if (count($users) > 0)
        <div class="table-wrapper">
            <table class="table" style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border);">
                        <th style="padding: 1rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Identity</th>
                        <th style="padding: 1rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Email Vector</th>
                        <th style="padding: 1rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Architecture</th>
                        <th style="padding: 1rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Provisioned</th>
                        <th style="padding: 1rem; text-align: right; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800;">Control</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @foreach ($users as $user)
                    <tr class="user-row" style="border-bottom: 1px solid var(--border);"
                        data-name="{{ strtolower($user?->full_name ?? '') }}"
                        data-email="{{ strtolower($user?->email ?? '') }}">
                        <td style="padding: 1.25rem 1rem;">
                            <strong style="font-family: 'Outfit', sans-serif; font-weight: 700; color: var(--text);">{{ $user?->full_name ?? 'N/A' }}</strong>
                        </td>
                        <td style="padding: 1.25rem 1rem; color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">{{ $user?->email ?? '' }}</td>
                        <td style="padding: 1.25rem 1rem;">
                            <span class="role-chip role-{{ $user?->user_type ?? 'unknown' }}">
                                {{ $user?->user_type ?? 'NONE' }}
                            </span>
                        </td>
                        <td style="padding: 1.25rem 1rem; color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">{{ $user?->created_at ? $user?->created_at?->format('M d, Y') : 'N/A' }}</td>
                        <td style="padding: 1.25rem 1rem; text-align: right;">
                            @if (($user?->user_id ?? 0) !== auth()->id())
                                <button class="btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); font-weight: 700;" onclick="confirmDelete({{ $user?->user_id ?? 0 }}, this)">Delete</button>
                                <span class="delete-confirm" id="dc-{{ $user?->user_id ?? 0 }}">
                                    <span style="font-size: 0.8rem; font-weight: 700; color: #ef4444;">Sure?</span>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user?->user_id ?? 0) }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-sm" style="background: #ef4444; color: white; border: none; font-weight: 700;">Yes</button>
                                    </form>
                                    <button class="btn-sm btn-secondary" onclick="cancelDelete({{ $user?->user_id ?? 0 }})">No</button>
                                </span>
                            @else
                                <span style="font-size:.75rem; font-weight:700; color:var(--primary); text-transform: uppercase; letter-spacing: 0.05em;">Current Core</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div style="text-align: center; padding: 4rem 1rem;">
                <p style="color: var(--text-muted); font-weight: 700;">No users found matching your criteria.</p>
            </div>
        @endif
    </div>
</div>

<footer class="footer">
    <p>© {{ date('Y') }} Mental Health Pre-Assessment System.</p>
</footer>

<script>
function liveFilter() {
    const q = document.getElementById('liveSearch').value.toLowerCase();
    document.querySelectorAll('.user-row').forEach(row => {
        row.style.display =
            row.dataset.name.includes(q) || row.dataset.email.includes(q) ? '' : 'none';
    });
}
function confirmDelete(id, btn) {
    btn.style.display = 'none';
    document.getElementById('dc-' + id).classList.add('show');
}
function cancelDelete(id) {
    document.getElementById('dc-' + id).classList.remove('show');
    document.querySelector('[onclick="confirmDelete(' + id + ', this)"]').style.display = '';
}
</script>
@endsection
