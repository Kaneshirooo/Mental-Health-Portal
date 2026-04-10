@extends('layouts.app')

@push('styles')
<style>
    .counselor-card {
        display: grid;
        grid-template-columns: 1fr auto;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.25rem;
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        margin-bottom: .75rem;
        transition: box-shadow var(--transition);
    }
    .counselor-card:hover { box-shadow: var(--shadow-sm); border-color: var(--border-hover); }
    .counselor-info strong { display:block; font-size:1rem; color:var(--text); font-family: 'Outfit', sans-serif; font-weight: 700; }
    .counselor-meta { display:flex; gap:.75rem; margin-top:.35rem; flex-wrap:wrap; }
    .meta-chip { font-size:.78rem; background: var(--surface-2); border:1px solid var(--border);
                 border-radius:99px; padding:.2rem .65rem; color:var(--text-muted); font-weight: 600; }
    .delete-confirm { display:none; gap:.5rem; align-items:center; }
    .delete-confirm.show { display:flex; }
    .filter-row { display:flex; gap:.75rem; margin-bottom:1.25rem; flex-wrap:wrap; }
    .filter-row input { flex:1; min-width:180px; }
</style>
@endpush

@section('content')
<div class="container" style="padding-top: 2rem; padding-bottom: 4rem;">
    <div class="dashboard-header" style="margin-bottom: 2.5rem;">
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem;">👩‍⚕️ Manage Counselors</h1>
        <p style="color: var(--text-muted); font-weight: 500;">{{ count($counselors) }} counselor(s) registered in the system</p>
    </div>

    <div class="card" style="background: var(--surface-solid); padding: 2rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; margin:0; color: var(--text);">All Staff Members
                <span style="font-size:.9rem;font-weight:600;color:var(--text-muted)">(Active)</span>
            </h2>
            <button onclick="document.getElementById('addStaffModal').style.display='flex'" class="btn-primary btn-sm" style="display: flex; align-items: center; gap: 0.5rem;">
                <span>➕</span> Add New Counselor
            </button>
        </div>

        <!-- Search bar -->
        <form method="GET" class="filter-row">
            <input type="text" name="search" placeholder="🔍 Search by name or email…"
                   value="{{ $search }}" id="liveSearch" oninput="liveFilter()" class="form-input">
            <button type="submit" class="btn-primary btn-sm">Search</button>
            @if ($search)
                <a href="{{ route('admin.staff.index') }}" class="btn-secondary btn-sm" style="text-decoration:none">Clear</a>
            @endif
        </form>

        @if (count($counselors) == 0)
            <div class="empty-state" style="text-align: center; padding: 4rem 2rem;">
                <span style="font-size: 3rem;">👩‍⚕️</span>
                <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: var(--text-muted); margin: 1rem 0;">No counselors found</h2>
                <p style="color: var(--text-muted); font-weight: 500;">Register your first clinical staff member to begin session assignments.</p>
            </div>
        @else
            <div id="staffList">
                @foreach ($counselors as $c)
                <div class="counselor-card" data-name="{{ strtolower($c->full_name) }}" data-email="{{ strtolower($c->email) }}">
                    <div class="counselor-info">
                        <strong>{{ $c->full_name }}</strong>
                        <div class="counselor-meta">
                            <span class="meta-chip">📧 {{ $c->email }}</span>
                            @if ($c->department)
                                <span class="meta-chip">🏫 {{ $c->department }}</span>
                            @endif
                            <span class="meta-chip">📅 {{ $c->appointments_count }} appointment(s)</span>
                            <span class="meta-chip">🗓 Joined {{ $c->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
                        @if ($c->user_id !== auth()->id())
                        <button class="btn-sm" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); font-weight: 700;" onclick="confirmDel({{ $c->user_id }}, this)">🗑 Remove</button>
                        <span class="delete-confirm" id="dc-{{ $c->user_id }}">
                            <span style="font-size: 0.8rem; font-weight: 700; color: #ef4444;">Sure?</span>
                            <form method="POST" action="{{ route('admin.staff.destroy', $c->user_id) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm" style="background: #ef4444; color: white; border: none; font-weight: 700;">Yes, Remove</button>
                            </form>
                            <button class="btn-sm btn-secondary" onclick="cancelDel({{ $c->user_id }})">Cancel</button>
                        </span>
                        @else
                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--primary); text-transform: uppercase;">You (System)</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Simple Modal for Add Staff -->
<div id="addStaffModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(4px); z-index:1000; align-items:center; justify-content:center; padding: 2rem;">
    <div style="background: var(--surface-solid); width: 100%; max-width: 500px; border-radius: var(--radius); border: 1px solid var(--border); padding: 2.5rem; box-shadow: var(--shadow-lg);">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem;">Add New Staff</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem; font-weight: 500;">Register a new counselor into the clinical ecosystem.</p>
        
        <form method="POST" action="{{ route('admin.staff.store') }}">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label style="display:block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;">Full Name</label>
                <input type="text" name="full_name" required class="form-input" style="width: 100%;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display:block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;">Email Address</label>
                <input type="email" name="email" required class="form-input" style="width: 100%;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display:block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;">Department (Optional)</label>
                <input type="text" name="department" class="form-input" style="width: 100%;">
            </div>
            <div style="margin-bottom: 2rem;">
                <label style="display:block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;">Initial Password</label>
                <input type="password" name="password" required class="form-input" style="width: 100%;">
            </div>
            
            <div style="display:flex; gap:1rem;">
                <button type="submit" class="btn-primary" style="flex:1;">Register Staff →</button>
                <button type="button" onclick="document.getElementById('addStaffModal').style.display='none'" class="btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<footer class="footer">
    <p>© {{ date('Y') }} Mental Health Pre-Assessment System.</p>
</footer>

<script>
function confirmDel(id, btn) {
    btn.style.display = 'none';
    document.getElementById('dc-' + id).classList.add('show');
}
function cancelDel(id) {
    document.getElementById('dc-' + id).classList.remove('show');
    document.querySelector('[onclick="confirmDel(' + id + ', this)"]').style.display = '';
}
function liveFilter() {
    const q = document.getElementById('liveSearch').value.toLowerCase();
    document.querySelectorAll('.counselor-card').forEach(card => {
        card.style.display =
            card.dataset.name.includes(q) || card.dataset.email.includes(q) ? '' : 'none';
    });
}
</script>
@endsection
