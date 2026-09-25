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
<div class="container" style="max-width: 1300px; margin: 0 auto; padding: 2rem 1.5rem 4rem;">
    
    <!-- Header -->
    <header class="staggered" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3.5rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 0.5rem;">Personnel Oversight</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.75rem; font-weight: 900; color: var(--text); letter-spacing: -0.04em; margin: 0;">Clinical Staff</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin-top: 0.5rem;">Manage authorized counselors and clinical responders.</p>
        </div>
        <button onclick="document.getElementById('addStaffModal').style.display='flex'" class="btn-primary" style="padding: 1rem 2rem; border-radius: 16px; font-weight: 800; text-transform: uppercase; display: flex; align-items: center; gap: 0.75rem; font-size: 0.9rem;">
            <i class="ph ph-user-plus"></i> Add Staff Member
        </button>
    </header>

    <!-- Staff List Section -->
    <div class="staggered" style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 32px; padding: 3rem; box-shadow: 0 30px 60px rgba(0,0,0,0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; border-bottom: 1px solid var(--border); padding-bottom: 2rem;">
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--text); margin: 0;">Active Personnel</h2>
            
            <div style="display: flex; gap: 1rem; align-items: center;">
                <input type="text" id="liveSearch" placeholder="Filter staff..." oninput="liveFilter()" 
                       class="form-input-premium" style="width: 250px; background: var(--surface-2);">
            </div>
        </div>

        @if (count($counselors) == 0)
            <div style="text-align: center; padding: 6rem 2rem;">
                <i class="ph ph-stethoscope" style="font-size: 4rem; color: var(--text-muted); opacity: 0.15; margin-bottom: 1.5rem; display: block;"></i>
                <p style="color: var(--text-muted); font-weight: 800; font-size: 1.25rem;">No clinical staff registered.</p>
            </div>
        @else
            <div id="staffList" style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                @foreach ($counselors as $c)
                <div class="counselor-card staggered-row" 
                     data-name="{{ strtolower($c->full_name) }}" 
                     data-email="{{ strtolower($c->email) }}"
                     style="background: var(--surface-2); padding: 1.75rem 2rem; border-radius: 24px; display: flex; justify-content: space-between; align-items: center; border: 1px solid transparent; transition: all 0.3s ease;">
                    
                    <div style="display: flex; align-items: center; gap: 1.5rem;">
                        <div style="width: 56px; height: 56px; border-radius: 16px; background: var(--surface-solid); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-weight: 900; color: var(--primary); font-size: 1.25rem;">
                            {{ strtoupper(substr($c->full_name, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 800; color: var(--text); margin-bottom: 0.25rem;">{{ $c->full_name }}</div>
                            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                                <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); background: var(--surface-3); padding: 0.3rem 0.75rem; border-radius: 100px; display: flex; align-items: center; gap: 0.4rem; border: 1px solid var(--border);">
                                    <i class="ph ph-envelope-simple"></i> {{ $c->email }}
                                </span>
                                @if ($c->department)
                                <span style="font-size: 0.8rem; font-weight: 700; color: #6366f1; background: rgba(99, 102, 241, 0.08); padding: 0.3rem 0.75rem; border-radius: 100px; display: flex; align-items: center; gap: 0.4rem; border: 1px solid rgba(99, 102, 241, 0.15);">
                                    <i class="ph ph-buildings"></i> {{ $c->department }}
                                </span>
                                @endif
                                <span style="font-size: 0.8rem; font-weight: 700; color: #10b981; background: rgba(16, 185, 129, 0.08); padding: 0.3rem 0.75rem; border-radius: 100px; display: flex; align-items: center; gap: 0.4rem; border: 1px solid rgba(16, 185, 129, 0.15);">
                                    <i class="ph ph-calendar-check"></i> {{ $c->appointments_count }} Cases
                                </span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem; align-items: center;">
                        @if ($c->user_id !== auth()->id())
                        <button class="btn-sm" style="background: rgba(239, 68, 68, 0.08); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); font-weight: 800; text-transform: uppercase; padding: 0.5rem 1.25rem; border-radius: 12px; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='#ef4444'; this.style.color='white'" onmouseout="this.style.background='rgba(239, 68, 68, 0.08)'; this.style.color='#ef4444'" onclick="confirmDel({{ $c->user_id }}, this)">Remove</button>
                        <span class="delete-confirm" id="dc-{{ $c->user_id }}">
                            <span style="font-size: 0.75rem; font-weight: 800; color: #ef4444; margin-right: 0.5rem;">CONFIRM?</span>
                            <form method="POST" action="{{ route('admin.staff.destroy', $c->user_id) }}" class="delete-form" style="display: inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-sm" style="background: #ef4444; color: white; border: none; font-weight: 800; padding: 0.5rem 1rem; border-radius: 12px;">YES</button>
                            </form>
                            <button class="btn-sm" style="background: var(--surface-3); border: 1px solid var(--border); font-weight: 800; padding: 0.5rem 1rem; border-radius: 12px;" onclick="cancelDel({{ $c->user_id }})">NO</button>
                        </span>
                        @else
                        <div style="background: var(--surface-3); padding: 0.5rem 1rem; border-radius: 12px; font-weight: 800; font-size: 0.75rem; color: var(--primary); text-transform: uppercase; letter-spacing: 0.08em; border: 1px solid var(--border);">
                            Administrator
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Add Staff Modal -->
<div id="addStaffModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.65); backdrop-filter:blur(12px); align-items:center; justify-content:center;">
    <div class="staggered-row" style="background:var(--surface-solid); border: 1px solid var(--border); border-radius:32px; padding:2.5rem; max-width:500px; width:92%; position:relative; box-shadow:var(--shadow-lg);">
        <button onclick="document.getElementById('addStaffModal').style.display='none'" style="position:absolute; top:1.5rem; right:1.5rem; background:var(--surface-2); border:none; width:40px; height:40px; border-radius:12px; cursor:pointer; color:var(--text); display:flex; align-items:center; justify-content:center; transition:var(--transition);" onmouseover="this.style.background='var(--primary-glow)';this.style.color='var(--primary)'" onmouseout="this.style.background='var(--surface-2)';this.style.color='var(--text)'">
            <i class="ph ph-x" style="font-size:1.25rem;"></i>
        </button>
        
        <div style="margin-bottom: 2rem;">
            <div style="width: 56px; height: 56px; border-radius: 16px; background: rgba(16, 185, 129, 0.12); color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem;">
                <i class="ph-bold ph-user-plus"></i>
            </div>
            <h3 style="font-family:'Outfit',sans-serif; font-size:1.75rem; font-weight:900; color:var(--text); margin:0; line-height: 1.2;">Register Staff</h3>
            <p style="color:var(--text-muted); font-size:0.95rem; font-weight:500; margin-top:0.4rem;">Add a new clinical professional to the system to manage student cases.</p>
        </div>

        <form id="addStaffForm" method="POST" action="{{ route('admin.staff.store') }}" style="display: flex; flex-direction: column; gap: 1.25rem;">
            @csrf
            <div>
                <label style="display:block; font-size:0.82rem; font-weight:800; color:var(--text); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.5rem;">Full Name <span style="color:#ef4444">*</span></label>
                <div style="position: relative;">
                    <i class="ph ph-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.1rem;"></i>
                    <input type="text" name="full_name" required placeholder="e.g. Dr. Jane Doe" class="form-input-premium" style="width: 100%; padding-left: 2.75rem; background: var(--surface-2); border: 1.5px solid var(--border);">
                </div>
            </div>
            
            <div>
                <label style="display:block; font-size:0.82rem; font-weight:800; color:var(--text); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.5rem;">Email Address <span style="color:#ef4444">*</span></label>
                <div style="position: relative;">
                    <i class="ph ph-envelope" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.1rem;"></i>
                    <input type="email" name="email" required placeholder="staff@institution.edu" class="form-input-premium" style="width: 100%; padding-left: 2.75rem; background: var(--surface-2); border: 1.5px solid var(--border);">
                </div>
            </div>

            <div>
                <label style="display:block; font-size:0.82rem; font-weight:800; color:var(--text); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.5rem;">Temporary Password <span style="color:#ef4444">*</span></label>
                <div style="position: relative;">
                    <i class="ph ph-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.1rem;"></i>
                    <input type="password" name="password" required placeholder="Min. 6 characters" class="form-input-premium" style="width: 100%; padding-left: 2.75rem; background: var(--surface-2); border: 1.5px solid var(--border);">
                </div>
            </div>

            <div>
                <label style="display:block; font-size:0.82rem; font-weight:800; color:var(--text); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.5rem;">Department / Unit</label>
                <div style="position: relative;">
                    <i class="ph ph-buildings" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.1rem;"></i>
                    <input type="text" name="department" placeholder="e.g. Guidance Office" class="form-input-premium" style="width: 100%; padding-left: 2.75rem; background: var(--surface-2); border: 1.5px solid var(--border);">
                </div>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="button" onclick="document.getElementById('addStaffModal').style.display='none'" style="flex:1; padding:1rem; border-radius:14px; background:var(--surface-2); border:1px solid var(--border); color:var(--text); font-weight:800; cursor:pointer;" onmouseover="this.style.background='var(--surface-3)'" onmouseout="this.style.background='var(--surface-2)'">Cancel</button>
                <button type="submit" style="flex:1.5; padding:1rem; border-radius:14px; background:var(--primary); border:none; color:#ffffff; font-weight:800; cursor:pointer; box-shadow:0 4px 12px rgba(16,185,129,0.25);" onmouseover="this.style.opacity=0.9" onmouseout="this.style.opacity=1">Register Staff</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.gsap) {
        gsap.from('.staggered', { y: 40, opacity: 0, duration: 1, stagger: 0.15, ease: "expo.out", clearProps: "all" });
        gsap.from('.staggered-row', { x: -20, opacity: 0, duration: 0.8, stagger: 0.05, ease: "expo.out", delay: 0.4, clearProps: "all" });
    }
});

function confirmDel(id, btn) {
    btn.style.display = 'none';
    document.getElementById('dc-' + id).classList.add('show');
}
function cancelDel(id) {
    document.getElementById('dc-' + id).classList.remove('show');
    const btn = document.querySelector('[onclick="confirmDel(' + id + ', this)"]');
    if (btn) btn.style.display = '';
}
function liveFilter() {
    const q = document.getElementById('liveSearch').value.toLowerCase();
    document.querySelectorAll('.counselor-card').forEach(card => {
        card.style.display = card.dataset.name.includes(q) || card.dataset.email.includes(q) ? '' : 'none';
    });
}

// Add Staff AJAX — inject new card directly from JSON, no page fetch
document.getElementById('addStaffForm').addEventListener('submit', async function(e) {
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
            document.getElementById('addStaffModal').style.display = 'none';
            // Inject card directly from JSON — zero page fetch
            const staffList = document.getElementById('staffList');
            if (staffList && data.counselor) {
                const card = AjaxHelpers.buildStaffCard(data.counselor);
                staffList.prepend(card);
                AjaxHelpers.flashRow(card);
                if (window.gsap) gsap.from(card, { x: -20, opacity: 0, duration: 0.5, ease: 'expo.out' });
            }
        } else {
            App.toast({ type: 'error', title: 'Error', message: data.error || 'Failed to add staff.' });
        }
    } catch (err) {
        App.toast({ type: 'error', title: 'Error', message: 'Connection failed.' });
    } finally {
        AjaxHelpers.stopBtn(btn, orig);
    }
});

// Remove Staff AJAX (Delegated) — animate card out
document.addEventListener('submit', async function(e) {
    if (e.target.classList.contains('delete-form')) {
        e.preventDefault();
        const form = e.target;
        const btn  = form.querySelector('button[type="submit"]');
        const card = form.closest('.counselor-card');
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
                AjaxHelpers.removeRow(card);
            } else {
                App.toast({ type: 'error', title: 'Error', message: data.error || 'Failed to remove staff.' });
                AjaxHelpers.stopBtn(btn, orig);
            }
        } catch (err) {
            App.toast({ type: 'error', title: 'Error', message: 'Connection failed.' });
            AjaxHelpers.stopBtn(btn, orig);
        }
    }
});
</script>
@endpush
@endsection
