@extends('layouts.app')

@push('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #0d9488, #0f766e);
        border-radius: var(--radius);
        padding: 2rem 2.5rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    .page-header::after {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 220px; height: 220px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }

    .form-card {
        background: var(--surface-solid);
        border-radius: var(--radius);
        padding: 2rem;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
    }

    .form-label {
        display: block;
        font-weight: 600;
        font-size: 0.82rem;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
    }

    .appt-status {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.35rem 0.9rem; border-radius: 50px;
        font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;
    }
    .status-requested  { background:#fffbeb; color:#d97706; border:1px solid #fde68a; }
    .status-confirmed  { background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; }
    .status-completed  { background:#f5f3ff; color:#4f46e5; border:1px solid #c4b5fd; }
    .status-declined, .status-cancelled { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }

    .timeline-card {
        background: var(--surface-solid);
        border-radius: 20px;
        padding: 1.5rem 2rem;
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 1.5rem;
        transition: var(--transition);
    }
    .timeline-card:hover { border-color: var(--primary-light); box-shadow: var(--shadow-sm); }

    .conflict-popup-overlay {
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(6px);
        align-items: center; justify-content: center;
        padding: 1rem;
    }
    .conflict-popup-box {
        background: var(--surface-solid); border-radius: 24px; padding: 2.5rem; max-width: 400px; width: 100%;
        box-shadow: 0 25px 50px rgba(0,0,0,0.15); border: 1px solid var(--border);
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width:1100px; padding-top:1.5rem; padding-bottom:3rem;">

    <div class="page-header">
        <div style="font-size:0.72rem; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; opacity:0.8; margin-bottom:0.5rem;">Counseling Services</div>
        <h1 style="font-family:'Outfit',sans-serif; font-size:1.75rem; font-weight:700; margin-bottom:0.35rem;">Book an Appointment</h1>
        <p style="font-size:0.95rem; opacity:0.85; font-weight:400; max-width:400px;">Schedule a session with a guidance counselor. All appointments are confidential.</p>
    </div>

    <div style="display:grid; grid-template-columns:1fr 400px; gap:3rem; align-items:start;">

        <!-- Left: Booking Form -->
        <div>
            <form method="POST" action="{{ route('student.appointments.book') }}" id="bookingForm">
                @csrf
                <h2 style="font-family:'Outfit',sans-serif; font-size:1.25rem; font-weight:800; color:var(--text); margin-bottom:1.5rem;">Schedule Your Session</h2>
                <div class="form-card">
                    <div style="margin-bottom:1.5rem;">
                        <label class="form-label">Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduledAtInput" class="form-input" required style="width:100%;">
                        <div style="font-size:0.78rem; color:var(--text-dim); margin-top:0.5rem; font-weight:600;">Sessions are 30 minutes long. Please book at least 10 minutes in advance.</div>
                    </div>
                    <div style="margin-bottom:2rem;">
                        <label class="form-label">Reason for Visit <span style="opacity:0.5;">(Optional)</span></label>
                        <textarea name="reason" class="form-input" rows="4" style="resize:none; width:100%;" placeholder="Briefly describe what you'd like to discuss..."></textarea>
                    </div>
                    <button type="submit" style="width:100%; background:var(--primary); color:white; border:none; padding:0.85rem; border-radius:var(--radius-sm); font-weight:600; font-size:0.9rem; cursor:pointer; box-shadow:0 4px 12px rgba(13,148,136,0.2); transition:var(--transition);">
                        Request Appointment →
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: My Appointments -->
        <div>
            <h2 style="font-family:'Outfit',sans-serif; font-size:1.25rem; font-weight:800; color:var(--text); margin-bottom:1.5rem;">My Appointments</h2>
            @if ($appointments->isEmpty())
                <div style="text-align:center; padding:3rem 2rem; background:var(--surface-solid); border-radius:24px; border:2px dashed var(--border);">
                    <div style="font-size:2.5rem; margin-bottom:1rem; opacity:0.4;">📅</div>
                    <p style="font-weight:700; color:var(--text-dim); font-size:0.95rem;">No appointments yet.</p>
                    <p style="font-size:0.85rem; color:var(--text-dim); margin-top:0.4rem;">Book your first session using the form.</p>
                </div>
            @else
                <div style="display:flex; flex-direction:column; gap:1rem;">
                    @foreach ($appointments as $a)
                        <div class="timeline-card">
                            <div style="width:44px; height:44px; border-radius:14px; background:var(--primary-glow); display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0;">
                                @php
                                    $icons = ['requested' => '⏳', 'confirmed' => '✅', 'completed' => '🎓', 'declined' => '❌', 'cancelled' => '🚫'];
                                @endphp
                                {{ $icons[$a->status->value] ?? '📅' }}
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.25rem;">
                                    <div style="font-weight:800; color:var(--text); font-size:0.92rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $a->counselor->full_name }}</div>
                                    <div style="display:flex; align-items:center; gap:0.5rem;">
                                        <span class="appt-status status-{{ $a->status }}">{{ $a->status }}</span>
                                    </div>
                                </div>
                                <div style="font-size:0.8rem; color:var(--primary); font-weight:700;">
                                    {{ $a->scheduled_at->format('M d, Y • g:i A') }}
                                </div>
                                @if ($a->reason)
                                    <div style="font-size:0.78rem; color:var(--text-dim); margin-top:0.4rem; font-style:italic; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        "{{ $a->reason }}"
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Conflict Modal -->
@if (session('booking_conflict'))
<div id="conflictModal" class="conflict-popup-overlay" style="display: flex;">
    <div class="conflict-popup-box">
        <div style="font-size: 3rem; margin-bottom: 1rem;">⚠️</div>
        <h3 style="font-family:'Outfit',sans-serif; font-size:1.35rem; font-weight:800; color:#dc2626; margin-bottom:0.75rem;">Schedule conflict</h3>
        <p style="color:var(--text-dim); font-size:0.95rem; line-height:1.5; font-weight:600; margin-bottom:1.5rem;">The date and time you selected conflicts with another appointment. No counselor is available at that slot. Please choose a different date or time.</p>
        <button type="button" style="width:100%; padding:1rem; border-radius:14px; border:none; background:var(--primary); color:white; font-weight:800; cursor:pointer;" onclick="document.getElementById('conflictModal').style.display='none'">Choose another time</button>
    </div>
</div>
@endif

<footer class="footer" style="padding:2.5rem; text-align:center; border-top:1px solid var(--border); margin-top:4rem;">
    <p style="color:var(--text-dim); font-weight:700; font-size:0.85rem;">© {{ date('Y') }} PSU Mental Health Portal. All appointments are confidential.</p>
</footer>

<script>
(function() {
    const input = document.getElementById('scheduledAtInput');
    if (!input) return;
    const pad = n => String(n).padStart(2, '0');
    const d = new Date(Date.now() + 10 * 60 * 1000);
    input.min = d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate())
              + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
})();
</script>
@endsection
