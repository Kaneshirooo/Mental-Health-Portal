@extends('layouts.app')

@push('styles')
<style>
    .pagination-wrapper { margin-top: 2rem; display: flex; justify-content: center; }
    .pagination-wrapper .pagination { display: flex; gap: 0.5rem; list-style: none; padding: 0; margin: 0; }
    .pagination-wrapper .page-item .page-link {
        padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid var(--border);
        background: var(--surface-solid); color: var(--text); text-decoration: none;
        font-weight: 700; font-size: 0.85rem; transition: all 0.2s ease;
    }
    .pagination-wrapper .page-item.active .page-link { background: var(--primary); color: white; border-color: var(--primary); }
    .pagination-wrapper .page-item.disabled .page-link { opacity: 0.5; cursor: not-allowed; }
    @media (max-width: 768px) {
        table.archived-table thead { display: none; }
        table.archived-table, table.archived-table tbody, table.archived-table tr, table.archived-table td { display: block; width: 100%; box-sizing: border-box; }
        table.archived-table tbody tr.student-row { border: 1px solid var(--border); border-radius: 18px; padding: 1rem; margin-bottom: 0.85rem; }
        table.archived-table tbody tr.student-row td { padding: 0.35rem 0 !important; border: none !important; text-align: left !important; border-radius: 0 !important; }
        table.archived-table tbody tr.student-row td:last-child .restore-btn { width: 100%; justify-content: center; min-height: 44px; }
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1320px; margin: 0 auto; padding: 2rem 1.5rem 4rem;">

    <header class="staggered" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="ph-bold ph-archive-box" style="font-size: 1.1rem;"></i> Archived Registry
            </div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.75rem; font-weight: 900; color: var(--text); letter-spacing: -0.04em; margin: 0;">Archived Students</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin-top: 0.5rem;">Records moved out of the active registry. Nothing is deleted — restore anyone at any time.</p>
        </div>
        <div style="text-align: right; padding-right: 1.5rem; border-right: 1px solid var(--border);">
            <div style="font-size: 1.65rem; font-weight: 900; color: var(--primary); font-family: 'Outfit', sans-serif;">{{ $archived->total() }}</div>
            <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em;">Archived</div>
        </div>
    </header>

    <div class="staggered" style="display: flex; gap: 1rem; align-items: center; margin-bottom: 2rem; flex-wrap: wrap;">
        <form method="GET" style="display: flex; flex: 1; min-width: 260px; gap: 0.75rem;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search archived students..." style="width: 100%; padding: 0.85rem 1.25rem; border-radius: 14px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text); font-size: 0.92rem; font-weight: 500; outline: none;">
        </form>
        <a href="{{ route('counselor.students.index') }}" style="padding: 0.85rem 1.25rem; border-radius: 14px; background: var(--surface-2); border: 1.5px solid var(--border); color: var(--text); font-weight: 800; font-size: 0.8rem; text-transform: uppercase; text-decoration: none; white-space: nowrap;">Back to Student Records</a>
    </div>

    @if($archived->isEmpty())
        <div class="staggered" style="padding: 5rem 2rem; text-align: center; background: var(--surface-solid); border: 1px dashed var(--border); border-radius: 32px;">
            <i class="ph-bold ph-archive-box" style="font-size: 3.5rem; color: var(--text-muted); opacity: 0.25; display: block; margin-bottom: 1.25rem;"></i>
            <h3 style="color: var(--text-muted); font-weight: 800; font-size: 1.15rem; margin: 0 0 0.5rem;">Nothing archived yet</h3>
            <p style="color: var(--text-dim); font-size: 0.92rem; font-weight: 500;">Students you archive from Student Records will appear here.</p>
        </div>
    @else
        <div class="staggered" style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 32px; padding: 2.5rem; box-shadow: var(--shadow-md);">
            <div style="overflow-x: auto;">
                <table class="archived-table" style="width: 100%; border-collapse: separate; border-spacing: 0 0.65rem;">
                    <thead>
                        <tr style="text-transform: uppercase; letter-spacing: 0.12em; font-size: 0.68rem; color: var(--text-dim); font-weight: 900;">
                            <th style="padding: 1rem 1.25rem; text-align: left;">Student</th>
                            <th style="padding: 1rem 1.25rem; text-align: left;">Student Number</th>
                            <th style="padding: 1rem 1.25rem; text-align: left;">Archived</th>
                            <th style="padding: 1rem 1.25rem; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="archiveTableBody">
                        @foreach($archived as $student)
                            @php
                                $s_name = $student->full_name ?? 'Unknown Student';
                                $initial = strtoupper(substr($s_name, 0, 1));
                            @endphp
                            <tr class="student-row" style="background: var(--surface-solid);">
                                <td style="padding: 1.25rem; border-top-left-radius: 18px; border-bottom-left-radius: 18px;">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 44px; height: 44px; border-radius: 14px; background: var(--surface-2); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-weight: 900; color: var(--primary); font-family: 'Outfit', sans-serif; font-size: 1.1rem; opacity: 0.65;">{{ $initial }}</div>
                                        <div style="display: flex; flex-direction: column;">
                                            <span style="font-weight: 800; color: var(--text); font-size: 0.98rem;">{{ $s_name }}</span>
                                            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">{{ $student->email ?? '' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1.25rem;">
                                    <span style="font-weight: 800; background: var(--surface-2); padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.75rem; color: var(--text-muted); border: 1px solid var(--border);">{{ $student->roll_number ?? '---' }}</span>
                                </td>
                                <td style="padding: 1.25rem; font-weight: 700; color: var(--text-muted); font-size: 0.88rem;">{{ $student->archived_at?->format('M d, Y') ?? '—' }}</td>
                                <td style="padding: 1.25rem; text-align: right; border-top-right-radius: 18px; border-bottom-right-radius: 18px;">
                                    <button type="button" class="restore-btn" data-id="{{ $student->user_id }}" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1.5px solid rgba(16, 185, 129, 0.3); font-weight: 800; font-size: 0.75rem; text-transform: uppercase; padding: 0.55rem 0.95rem; border-radius: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.2s ease;">
                                        <i class="ph-bold ph-arrow-counter-clockwise"></i> Restore
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($archived->hasPages())
                <div class="pagination-wrapper">
                    {{ $archived->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.restore-btn');
    if (!btn) return;

    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Restoring...';

    try {
        const res = await fetch(`/counselor/students/${btn.dataset.id}/restore`, {
            method: 'PATCH',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await res.json();

        if (data.success) {
            if (window.App && window.App.toast) {
                App.toast({ type: 'success', title: 'Restored', message: data.message });
            }
            const row = btn.closest('tr');
            if (row) row.remove();
            if (!document.querySelector('#archiveTableBody tr')) location.reload();
        } else {
            alert(data.error || 'Could not restore this student.');
        }
    } catch (err) {
        console.error(err);
        alert('An error occurred while restoring the student.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = original;
    }
});
</script>
@endpush
