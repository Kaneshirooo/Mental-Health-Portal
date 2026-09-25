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
        display: none;
        align-items: center; justify-content: center;
        padding: 1rem;
    }
    .conflict-popup-overlay.is-open {
        display: flex;
    }

    @media (max-width: 768px) {
        /* Stack the 2-column grid on mobile */
        .appt-grid {
            grid-template-columns: 1fr !important;
            gap: 1.5rem !important;
        }
        /* Choice cards: 2 per row on phones */
        .choice-matrix {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        /* Calendar days shrink nicely */
        .calendar-day {
            font-size: 0.78rem !important;
        }
        /* Time slots grid narrower */
        .time-slots-grid {
            grid-template-columns: repeat(auto-fill, minmax(68px, 1fr)) !important;
        }
    }
    .conflict-popup-box {
        background: var(--surface-solid); border-radius: 24px; padding: 2.5rem; max-width: 400px; width: 100%;
        box-shadow: 0 25px 50px rgba(0,0,0,0.15); border: 1px solid var(--border);
        text-align: center;
    }

    /* Custom Calendar & Time Picker Styles */
    .calendar-container {
        margin-top: 1rem;
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: 1.5rem;
        padding: 1.5rem;
        overflow: hidden;
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.5rem;
        text-align: center;
    }

    .calendar-day-label {
        font-size: 0.7rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text);
        cursor: pointer;
        border-radius: 12px;
        transition: var(--transition);
    }

    .calendar-day:hover:not(.disabled) {
        background: var(--primary-glow);
        color: var(--primary);
    }

    .calendar-day.selected {
        background: var(--primary);
        color: white !important;
        box-shadow: 0 4px 12px rgba(13,148,136,0.3);
    }

    .calendar-day.disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    .calendar-day.today {
        border: 2px solid var(--primary-light);
    }

    .time-picker-container {
        margin-top: 2rem;
    }

    .time-slots-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .time-slot {
        padding: 0.75rem 0.5rem;
        text-align: center;
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text);
        cursor: pointer;
        transition: var(--transition);
    }

    .time-slot:hover:not(.disabled) {
        border-color: var(--primary);
        color: var(--primary);
        background: var(--primary-glow);
    }

    .time-slot.selected {
        background: var(--primary);
        color: white !important;
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(13,148,136,0.3);
    }

    .time-slot.disabled {
        opacity: 0.4;
        background: #f1f5f9;
        cursor: not-allowed;
        text-decoration: line-through;
    }

    .dark-mode .time-slot.disabled {
        background: rgba(255,255,255,0.05);
    }

    .selection-preview {
        margin-top: 1.5rem;
        padding: 1rem;
        background: var(--primary-glow);
        border-radius: 12px;
        border: 1px dashed var(--primary-light);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--primary);
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

    <div class="appt-grid" style="display:grid; grid-template-columns: 1.2fr 0.8fr; gap:3rem; align-items:start;">

        <!-- Left: Booking Form -->
        <div>
            <form method="POST" action="{{ route('student.appointments.book') }}" id="bookingForm">
                @csrf
                <h2 style="font-family:'Outfit',sans-serif; font-size:1.25rem; font-weight:800; color:var(--text); margin-bottom:1.5rem;">Schedule Your Session</h2>
                <div class="form-card">
                    <div style="margin-bottom:1.5rem;">
                        <label class="form-label">Select Date</label>
                        <div class="calendar-container">
                            <div class="calendar-header">
                                <button type="button" id="prevMonth" style="background:none; border:none; color:var(--text); cursor:pointer; font-size:1.1rem;"><i class="ph-bold ph-caret-left"></i></button>
                                <div id="currentMonth" style="font-weight:800; font-family:'Outfit',sans-serif; color:var(--text); text-transform:uppercase; letter-spacing:0.05em; font-size:0.85rem;">Month Year</div>
                                <button type="button" id="nextMonth" style="background:none; border:none; color:var(--text); cursor:pointer; font-size:1.1rem;"><i class="ph-bold ph-caret-right"></i></button>
                            </div>
                            <div class="calendar-grid" id="calendarGrid">
                                <!-- Day Labels -->
                                <div class="calendar-day-label">Sun</div>
                                <div class="calendar-day-label">Mon</div>
                                <div class="calendar-day-label">Tue</div>
                                <div class="calendar-day-label">Wed</div>
                                <div class="calendar-day-label">Thu</div>
                                <div class="calendar-day-label">Fri</div>
                                <div class="calendar-day-label">Sat</div>
                                <!-- Days will be injected here -->
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom:1.5rem;">
                        <label class="form-label">Select Time</label>
                        <div class="time-slots-grid" id="timeSlots">
                            <!-- Time slots will be injected here -->
                            <div style="grid-column: 1 / -1; text-align:center; padding:1.5rem; color:var(--text-muted); font-size:0.8rem; font-weight:600;">Please select a date first</div>
                        </div>
                    </div>

                    <input type="hidden" name="scheduled_at" id="scheduledAtInput" required>

                    <div id="selectionPreview" class="selection-preview" style="display:none; margin-bottom:1.5rem;">
                        <i class="ph-fill ph-calendar-check" style="font-size:1.2rem;"></i>
                        <div>
                            <div style="font-size:0.7rem; text-transform:uppercase; opacity:0.7;">Selected Schedule</div>
                            <div id="previewText">Month DD, YYYY at HH:MM AM/PM</div>
                        </div>
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
        <div id="appointmentsListContainer">
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
                                
                                @if ($a->status->isActionable())
                                    <form method="POST" action="{{ route('student.appointments.cancel', $a->appointment_id) }}" class="cancel-form" style="margin-top:0.75rem;">
                                        @csrf
                                        <button type="submit" style="background:transparent; border:1px solid #fee2e2; color:#dc2626; padding:0.35rem 0.75rem; border-radius:8px; font-size:0.7rem; font-weight:800; text-transform:uppercase; cursor:pointer; transition:var(--transition);" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                                            Cancel Appointment
                                        </button>
                                    </form>
                                @endif

                                @if ($a->status === \App\Enums\AppointmentStatus::COMPLETED && !$a->survey)
                                    <div style="margin-top: 1rem;">
                                        <a href="{{ route('student.survey.show', $a->appointment_id) }}" class="btn-primary" style="padding: 0.65rem 1.25rem; font-size: 0.85rem; border-radius: 12px; text-decoration: none; font-weight: 800; display: inline-flex; align-items: center; gap: 0.5rem;">
                                            Give Feedback <i class="ph-bold ph-arrow-right"></i>
                                        </a>
                                    </div>
                                @elseif ($a->status === \App\Enums\AppointmentStatus::COMPLETED && $a->survey)
                                    <div style="margin-top: 0.75rem; color: #10b981; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">
                                        <i class="ph-bold ph-check-circle"></i> Feedback Submitted
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

@push('modals')
<!-- Conflict Modal -->
<div id="conflictModal" class="conflict-popup-overlay">
    <div class="conflict-popup-box">
        <div style="font-size: 3rem; margin-bottom: 1rem;">⚠️</div>
        <h3 style="font-family:'Outfit',sans-serif; font-size:1.35rem; font-weight:800; color:#dc2626; margin-bottom:0.75rem;">Schedule Conflict</h3>
        <p id="conflictMsg" style="color:var(--text-dim); font-size:0.95rem; line-height:1.5; font-weight:600; margin-bottom:1.5rem;">The date and time you selected conflicts with an existing appointment. No counselor is available at that slot. Please choose a different date or time.</p>
        <button type="button" style="width:100%; padding:1rem; border-radius:14px; border:none; background:var(--primary); color:white; font-weight:800; cursor:pointer;" onclick="document.getElementById('conflictModal').classList.remove('is-open')">Choose another time</button>
    </div>
</div>
@endpush

<footer class="footer" style="padding:2.5rem; text-align:center; border-top:1px solid var(--border); margin-top:4rem;">
    <p style="color:var(--text-dim); font-weight:700; font-size:0.85rem;">© {{ date('Y') }} PSU Mental Health Portal. All appointments are confidential.</p>
</footer>

@push('scripts')
<script>
(function() {
    const calendarGrid = document.getElementById('calendarGrid');
    const currentMonthLabel = document.getElementById('currentMonth');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const timeSlotsGrid = document.getElementById('timeSlots');
    const scheduledAtInput = document.getElementById('scheduledAtInput');
    const selectionPreview = document.getElementById('selectionPreview');
    const previewText = document.getElementById('previewText');

    let currentDate = new Date();
    let selectedDate = null;
    let selectedTime = null;

    const renderCalendar = () => {
        // Clear previous days (keep labels)
        const labels = calendarGrid.querySelectorAll('.calendar-day-label');
        calendarGrid.innerHTML = '';
        labels.forEach(l => calendarGrid.appendChild(l));

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        currentMonthLabel.textContent = new Intl.DateTimeFormat('en-US', { month: 'long', year: 'numeric' }).format(currentDate);

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = new Date();
        today.setHours(0,0,0,0);

        // Padding
        for (let i = 0; i < firstDay; i++) {
            const div = document.createElement('div');
            calendarGrid.appendChild(div);
        }

        // Days
        for (let day = 1; day <= daysInMonth; day++) {
            const div = document.createElement('div');
            div.className = 'calendar-day';
            div.textContent = day;
            
            const dateObj = new Date(year, month, day);
            if (dateObj < today) {
                div.classList.add('disabled');
            } else {
                if (dateObj.getTime() === today.getTime()) div.classList.add('today');
                if (selectedDate && dateObj.getTime() === selectedDate.getTime()) div.classList.add('selected');
                
                div.onclick = () => {
                    document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
                    div.classList.add('selected');
                    selectedDate = dateObj;
                    selectedTime = null;
                    renderTimeSlots();
                    updateInput();
                };
            }
            calendarGrid.appendChild(div);
        }
    };

    const renderTimeSlots = () => {
        timeSlotsGrid.innerHTML = '';
        if (!selectedDate) return;

        const slots = [
            '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
            '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'
        ];

        const now = new Date();
        const isToday = selectedDate.toDateString() === now.toDateString();

        slots.forEach(slot => {
            const [hours, minutes] = slot.split(':');
            const slotDate = new Date(selectedDate);
            slotDate.setHours(parseInt(hours), parseInt(minutes), 0, 0);

            const div = document.createElement('div');
            div.className = 'time-slot';
            
            // Format to 12h
            const hourNum = parseInt(hours);
            const ampm = hourNum >= 12 ? 'PM' : 'AM';
            const displayHour = hourNum % 12 || 12;
            div.textContent = `${displayHour}:${minutes} ${ampm}`;

            // Disable past slots if today
            if (isToday && slotDate <= new Date(Date.now() + 10 * 60 * 1000)) {
                div.classList.add('disabled');
            } else {
                if (selectedTime === slot) div.classList.add('selected');
                div.onclick = () => {
                    document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
                    div.classList.add('selected');
                    selectedTime = slot;
                    updateInput();
                };
            }
            timeSlotsGrid.appendChild(div);
        });
    };

    const updateInput = () => {
        if (selectedDate && selectedTime) {
            const [hours, minutes] = selectedTime.split(':');
            const finalDate = new Date(selectedDate);
            finalDate.setHours(parseInt(hours), parseInt(minutes));
            
            // Format for datetime-local input value (YYYY-MM-DDTHH:mm)
            const pad = n => String(n).padStart(2, '0');
            const val = `${finalDate.getFullYear()}-${pad(finalDate.getMonth()+1)}-${pad(finalDate.getDate())}T${pad(finalDate.getHours())}:${pad(finalDate.getMinutes())}`;
            scheduledAtInput.value = val;

            // Update preview
            selectionPreview.style.display = 'flex';
            const dateString = new Intl.DateTimeFormat('en-US', { month: 'long', day: 'numeric', year: 'numeric' }).format(finalDate);
            const timeString = new Intl.DateTimeFormat('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }).format(finalDate);
            previewText.textContent = `${dateString} at ${timeString}`;
        } else {
            scheduledAtInput.value = '';
            selectionPreview.style.display = 'none';
        }
    };

    prevMonthBtn.onclick = () => { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(); };
    nextMonthBtn.onclick = () => { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(); };

    renderCalendar();

    // Booking Form — inject card directly from JSON, zero page fetch
    document.getElementById('bookingForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = this;
        const btn  = form.querySelector('button[type="submit"]');
        const orig = AjaxHelpers.startBtn(btn, 'Processing...');
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
                App.toast({ type: 'success', title: 'Requested', message: data.message });
                form.reset();
                selectedDate = null;
                selectedTime = null;
                updateInput();
                renderCalendar();
                
                // Inject card directly
                const container = document.getElementById('appointmentsListContainer');
                if (container && data.appointment) {
                    // Check if UI is showing "No appointments" state and clear it
                    const emptyState = container.querySelector('div[style*="border: 2px dashed"]');
                    if (emptyState) emptyState.remove();

                    // Build a list wrapper if it doesn't exist yet
                    let listWrapper = container.querySelector('.appt-list-wrapper');
                    if (!listWrapper) {
                        listWrapper = document.createElement('div');
                        listWrapper.className = 'appt-list-wrapper';
                        listWrapper.style.cssText = 'display:flex; flex-direction:column; gap:1rem;';
                        container.appendChild(listWrapper);
                    }

                    const card = AjaxHelpers.buildAppointmentCard(data.appointment);
                    listWrapper.prepend(card);
                    AjaxHelpers.flashRow(card);
                    if (window.gsap) gsap.from(card, { x: -20, opacity: 0, duration: 0.5, ease: 'expo.out' });
                }
            } else {
                // Only show the conflict modal for ACTUAL scheduling conflicts
                // (server explicitly flags this with conflict:true)
                if (data.conflict === true) {
                    const modal = document.getElementById('conflictModal');
                    if (modal) modal.classList.add('is-open');
                } else {
                    App.toast({ type: 'error', title: 'Booking Failed', message: data.error || data.message || 'Unable to book. Please try another time.' });
                }
            }
        } catch (err) {
            App.toast({ type: 'error', title: 'Error', message: 'Something went wrong. Please try again.' });
        } finally {
            AjaxHelpers.stopBtn(btn, orig);
        }
    });

    // Cancellation (Delegated) — animate card out
    document.addEventListener('submit', async function(e) {
        if (e.target.classList.contains('cancel-form')) {
            e.preventDefault();
            if (!confirm('Are you sure you want to cancel this appointment?')) return;

            const form = e.target;
            const btn  = form.querySelector('button[type="submit"]');
            const card = form.closest('.timeline-card');
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
                    App.toast({ type: 'success', title: 'Cancelled', message: data.message });
                    
                    // Instead of full page refresh, animate row removal OR update status internally
                    // Since it's cancelled, usually we just update the card visually. Let's dim it.
                    if (card) {
                        const statusBadge = card.querySelector('.appt-status');
                        if (statusBadge) {
                            statusBadge.className = 'appt-status status-cancelled';
                            statusBadge.textContent = 'cancelled';
                        }
                        const controls = card.querySelector('.cancel-form');
                        if (controls) controls.remove();
                        const icon = card.querySelector('div[style*="font-size:1.3rem"]');
                        if (icon) icon.textContent = '🚫';
                        card.style.opacity = '0.6';
                    }
                } else {
                    App.toast({ type: 'error', title: 'Error', message: data.error || data.message });
                    AjaxHelpers.stopBtn(btn, orig);
                }
            } catch (err) {
                App.toast({ type: 'error', title: 'Error', message: 'Failed to cancel appointment.' });
                AjaxHelpers.stopBtn(btn, orig);
            }
        }
    });
})();
</script>
@endpush
@endsection
