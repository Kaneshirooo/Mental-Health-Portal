@extends('layouts.app')

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@endpush

@push('styles')
    <style>
        .appt-pill {
            font-size: .65rem;
            padding: .35rem .75rem;
            border-radius: var(--radius-sm);
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .appt-requested {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .appt-confirmed {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid rgba(22, 101, 52, 0.15);
        }

        .appt-declined,
        .appt-cancelled {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid rgba(153, 27, 27, 0.15);
        }

        .appt-completed {
            background: var(--surface-2);
            color: var(--text-muted);
            border: 1px solid var(--border);
        }

        .card:hover {
            border-color: var(--primary-light);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        #rescheduleModal, #editApptModal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
        }

        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        
        @keyframes success-pop {
            0% { transform: scale(0.85); opacity: 0; }
            70% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 10000;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .modal-glass {
            background: var(--surface-solid);
            border-radius: 32px;
            padding: 3rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
            border: 1px solid var(--border);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            animation: success-pop 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .btn-emergency-trigger {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-sm);
            font-weight: 800;
            font-size: 0.8rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            animation: pulse-red 2s infinite;
            transition: var(--transition);
        }
        .btn-emergency-trigger:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }
    </style>
@endpush

@section('content')
    <div class="container" style="max-width: 1320px; margin: 0 auto; padding: 2rem 1.5rem 4rem;">

        <header class="staggered" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3.5rem; flex-wrap: wrap; gap: 1.5rem;">
            <div>
                <div style="font-weight: 800; color: var(--primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph-bold ph-calendar-check" style="font-size: 1.1rem;"></i> Intake & Session Manager
                </div>
                <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.75rem; font-weight: 900; color: var(--text); letter-spacing: -0.04em; margin: 0;">Manage Schedule</h1>
                <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin-top: 0.5rem;">Active session management. {{ collect($upcoming)->count() }} slot{{ collect($upcoming)->count() !== 1 ? 's' : '' }} requiring attention.</p>
            </div>
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <div style="text-align: right; padding-right: 1.5rem; border-right: 1px solid var(--border);">
                    <div style="font-size: 1.65rem; font-weight: 900; color: var(--primary); font-family: 'Outfit', sans-serif;">{{ collect($upcoming)->count() }}</div>
                    <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em;">Upcoming</div>
                </div>
                <button onclick="window.print()" class="btn-secondary" style="padding: 0.85rem 1.5rem; border-radius: 14px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="ph-bold ph-printer" style="font-size: 1.1rem;"></i> Print Agenda
                </button>
                <button onclick="emergencyProtocol()" class="btn-emergency-trigger" id="emergencyBtn" style="padding: 0.85rem 1.5rem; border-radius: 14px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase;">
                    <i class="ph-bold ph-warning-circle" style="font-size: 1.1rem;"></i> Emergency Unavailable
                </button>
            </div>
        </header>

        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 900; margin-bottom: 1.5rem; color: var(--text);">
            Intake Queue</h2>
        @if (collect($upcoming)->isEmpty())
            <div
                style="padding: 4rem; text-align: center; background: var(--surface-2); border-radius: var(--radius); border: 1px dashed var(--border);">
                <div style="font-size: 3rem; margin-bottom: 1.5rem; opacity: 0.5;">🧘</div>
                <h3 style="color: var(--text-muted); font-weight: 700; font-size: 1.1rem;">Queue is clear</h3>
                <p style="color: var(--text-dim); font-size: 0.9rem; font-weight: 400;">No upcoming sessions scheduled at the
                    moment.</p>
            </div>
        @else
        <div id="intakeQueueGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 1.25rem;">
                @foreach ($upcoming as $a)
                    <div id="appt-card-{{ $a->appointment_id }}"
                        style="background: var(--surface-solid); border-radius: var(--radius); padding: 2rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); position: relative; transition: var(--transition);">
                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1.25rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div
                                    style="width: 48px; height: 48px; border-radius: var(--radius-sm); background: var(--surface-glow); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; border: 1px solid var(--border);">
                                    {{ strtoupper(substr($a->student->full_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: var(--text); font-size: 1rem; margin-bottom: 0.15rem;">
                                        {{ $a->student?->full_name ?? 'Unknown Student' }}</div>
                                    <div
                                        style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;">
                                        {{ $a->student?->roll_number ?? 'N/A' }} • {{ $a->student?->department ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <span class="appt-pill appt-{{ $a->status->value }}">{{ $a->status->label() }}</span>
                        </div>

                        <div
                            style="background: var(--surface-2); border-radius: var(--radius-sm); padding: 1.25rem; margin-bottom: 1.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                                <span style="font-size: 1.1rem;">🗓️</span>
                                <div style="font-weight: 700; font-size: 0.95rem; color: var(--primary);">
                                    {{ $a->scheduled_at->format('M d, Y @ g:i A') }}</div>
                            </div>
                            @if ($a->reason)
                                <p
                                    style="font-size: 0.88rem; line-height: 1.6; color: var(--text-muted); font-weight: 400; font-style: italic; border-left: 2px solid var(--border); padding-left: 1rem;">
                                    "{{ $a->reason }}"
                                </p>
                            @endif
                        </div>

                        <div id="card-alert-{{ $a->appointment_id }}"
                            style="display:none; padding:0.5rem 1rem; border-radius:var(--radius-sm); font-weight:600; font-size:0.75rem; margin-bottom:0.75rem;">
                        </div>
                        <div style="display: flex; gap: 0.75rem;">
                            @if ($a->status === 'requested')
                                <button onclick="apptAction('confirm', {{ $a->appointment_id }}, this)"
                                    style="flex:1; padding: 0.75rem; border-radius: var(--radius-sm); background: var(--primary); color: white; border: none; font-weight: 600; cursor: pointer; font-size: 0.85rem;">Confirm
                                    Session</button>
                            @else
                                <button onclick="apptAction('complete', {{ $a->appointment_id }}, this)"
                                    style="flex:1; padding: 0.75rem; border-radius: var(--radius-sm); background: #f0fdf4; color: #0d9488; border: 1px solid rgba(13, 148, 136, 0.2); font-weight: 600; cursor: pointer; font-size: 0.85rem;">Mark
                                    Completed</button>
                            @endif

                            <button
                                onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='block'?'none':'block'"
                                class="btn-secondary" style="padding: 0.75rem 1.25rem;">•••</button>
                            <div
                                style="display: none; position: absolute; bottom: 1.5rem; right: 1.5rem; background: var(--surface-solid); border-radius: var(--radius-sm); padding: 0.75rem; box-shadow: var(--shadow-lg); border: 1px solid var(--border); z-index: 10;">
                                <button
                                    onclick="openRescheduleModal({{ $a->appointment_id }}, '{{ $a->scheduled_at->format('Y-m-d\TH:i') }}')"
                                    style="width: 100%; padding: 0.6rem 1rem; border-radius: 6px; background: #f0fdf4; color: #16a34a; border: none; font-weight: 600; cursor: pointer; margin-bottom:0.4rem; font-size: 0.82rem; text-align: left;">Reschedule</button>
                                <button
                                    onclick="openEditModal({{ $a->appointment_id }}, '{{ $a->reason }}', {{ $a->duration_min }})"
                                    style="width: 100%; padding: 0.6rem 1rem; border-radius: 6px; background: var(--surface-2); color: var(--text); border: none; font-weight: 600; cursor: pointer; margin-bottom:0.4rem; font-size: 0.82rem; text-align: left;">Edit Booking</button>
                                <button onclick="apptAction('decline', {{ $a->appointment_id }}, this)"
                                    style="width: 100%; padding: 0.6rem 1rem; border-radius: 6px; background: #fff1f2; color: #e11d48; border: none; font-weight: 600; cursor: pointer; margin-bottom:0.4rem; font-size: 0.82rem; text-align: left;">Decline</button>
                                <button onclick="apptAction('cancel', {{ $a->appointment_id }}, this)"
                                    style="width: 100%; padding: 0.6rem 1rem; border-radius: 6px; background: var(--surface-2); color: var(--text-muted); border: none; font-weight: 600; cursor: pointer; font-size: 0.82rem; text-align: left;">Cancel</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <h2
            style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin-top: 3rem; margin-bottom: 1.5rem; color: var(--text);">
            Session Archive</h2>
        <div id="sessionArchiveTable" style="background: var(--surface-solid); border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; box-shadow: var(--shadow-sm);">
            <table style="width: 100%; border-collapse: collapse; text-align: left;" class="table">
                <thead>
                    <tr style="background: var(--surface-2); border-bottom: 1px solid var(--border);">
                        <th
                            style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase;">
                            Student Identity</th>
                        <th
                            style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase;">
                            Timing</th>
                        <th
                            style="padding: 1.25rem 1.5rem; font-size: 0.65rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase;">
                            Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($past as $a)
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 1.25rem 1.5rem;">
                                <div style="font-weight: 600; color: var(--text); font-size: 0.95rem;">
                                    {{ $a->student?->full_name ?? 'Unknown Student' }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-dim); font-weight: 500;">ID:
                                    {{ $a->student?->roll_number ?? 'N/A' }}</div>
                            </td>
                            <td style="padding: 1.25rem 1.5rem;">
                                <div style="font-weight: 600; color: var(--text); font-size: 0.9rem;">
                                    {{ $a->scheduled_at->format('M d, Y') }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-dim); font-weight: 500;">
                                    {{ $a->scheduled_at->format('g:i A') }}</div>
                            </td>
                            <td style="padding: 1.25rem 1.5rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span class="appt-pill appt-{{ $a->status->value }}">
                                        {{ $a->status->label() }}
                                    </span>
                                    @if($a->status->value === 'completed')
                                        <button onclick="showSurveyQR({{ $a->appointment_id }}, '{{ route('student.survey.show', $a->appointment_id) }}')" 
                                                class="btn-sm" style="font-size: 0.65rem; padding: 0.35rem 0.75rem; background: var(--primary-glow); border: 1px solid var(--primary-light); color: var(--primary); border-radius: 6px; font-weight: 700; cursor: pointer;">
                                            Scan Survey QR
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- QR Survey Modal -->
    <div id="qrModal" style="display:none; position:fixed; inset:0; z-index:10001; background:rgba(15,23,42,0.6); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:1.5rem;">
        <div style="background:var(--surface-solid); border-radius:32px; padding:3rem; max-width:450px; width:100%; text-align:center; border:1.5px solid var(--primary); box-shadow:0 25px 50px -12px rgba(0,0,0,0.2);">
            <h3 style="font-family:'Outfit',sans-serif; font-size:1.5rem; font-weight:800; color:var(--text); margin-bottom:0.5rem;">Client Satisfaction</h3>
            <p style="color:var(--text-dim); font-size:0.9rem; margin-bottom:2rem;">Please ask the student to scan this QR code to provide feedback on their session.</p>
            
            <div id="qrcode" style="display:inline-block; padding:2rem; background:white; border-radius:24px; margin-bottom:2rem; border:1px solid var(--border);"></div>
            
            <div style="margin-bottom:2rem;">
                <code id="qrLink" style="font-size:0.75rem; color:var(--text-dim); background:var(--surface-2); padding:0.5rem; border-radius:6px; word-break:break-all;"></code>
            </div>

            <button onclick="closeQRModal()" class="btn-primary" style="width:100%; padding:0.85rem; font-weight:700;">Done</button>
        </div>
    </div>


    <!-- Edit Booking modal -->
    <div id="editApptModal">
        <div
            style="background:var(--surface-solid); border-radius:var(--radius); padding:2.5rem; max-width:450px; width:90%; border: 1px solid var(--border); box-shadow: var(--shadow-lg);">
            <h3
                style="font-family:'Outfit',sans-serif; font-size:1.25rem; font-weight:800; margin-bottom:1.25rem; color:var(--text);">
                Edit Appointment Details</h3>
            <form id="editApptForm" onsubmit="submitEdit(event)">
                @csrf
                <input type="hidden" name="appointment_id" id="edit_appt_id">
                <input type="hidden" name="appt_action" value="edit">
                
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block; font-weight:700; font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.5rem;">Session Reason</label>
                    <input type="text" name="reason" id="edit_reason" required class="form-input" style="width:100%;">
                </div>

                <div style="margin-bottom:1.25rem;">
                    <label style="display:block; font-weight:700; font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.5rem;">Duration (Minutes)</label>
                    <select name="duration_min" id="edit_duration" class="form-input" style="width:100%;">
                        <option value="30">30 Minutes</option>
                        <option value="45">45 Minutes</option>
                        <option value="60">60 Minutes</option>
                        <option value="90">90 Minutes</option>
                    </select>
                </div>

                <div style="margin-bottom:2rem;">
                    <label style="display:block; font-weight:700; font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.5rem;">Counselor Notes / Internal</label>
                    <textarea name="counselor_message" id="edit_message" rows="3" class="form-input" style="width:100%; resize:none;" placeholder="Update session context..."></textarea>
                </div>

                <div style="display:flex; gap:0.75rem;">
                    <button type="button" onclick="closeEditModal()" class="btn-secondary" style="flex:1;">Cancel</button>
                    <button type="submit" class="btn-primary" style="flex:1;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reschedule modal -->
    <div id="rescheduleModal">
        <div
            style="background:var(--surface-solid); border-radius:var(--radius); padding:2.5rem; max-width:400px; width:90%; border: 1px solid var(--border); box-shadow: var(--shadow-lg);">
            <h3
                style="font-family:'Outfit',sans-serif; font-size:1.25rem; font-weight:800; margin-bottom:1.25rem; color:var(--text);">
                Reschedule Session</h3>
            <form id="rescheduleForm" onsubmit="submitReschedule(event)">
                @csrf
                <input type="hidden" name="appointment_id" id="res_appt_id">
                <input type="hidden" name="appt_action" value="reschedule">
                <div style="margin-bottom:1.25rem;">
                    <label
                        style="display:block; font-weight:700; font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.5rem;">New
                        Appointment Time</label>
                    <input type="datetime-local" id="res_datetime" name="scheduled_at" required class="form-input"
                        style="width:100%;">
                </div>
                <div style="margin-bottom:2rem;">
                    <label
                        style="display:block; font-weight:700; font-size:0.7rem; color:var(--text-muted); text-transform:uppercase; margin-bottom:0.5rem;">Note
                        to Student</label>
                    <textarea name="counselor_message" rows="3" class="form-input" style="width:100%; resize:none;"
                        placeholder="Optional explanation..."></textarea>
                </div>
                <div style="display:flex; gap:0.75rem;">
                    <button type="button" onclick="closeRescheduleModal()" class="btn-secondary"
                        style="flex:1;">Cancel</button>
                    <button type="submit" class="btn-primary" style="flex:1;">Reschedule</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Premium Emergency Confirmation Modal -->
    <div id="emergencyConfirmModal" style="display:none; position:fixed; inset:0; z-index:10000; background:rgba(15,23,42,0.6); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:1.5rem;">
        <div class="glass-premium" style="max-width:450px; width:100%; padding:2.5rem; border-radius:24px; text-align:center; box-shadow:0 25px 50px -12px rgba(220, 38, 38, 0.25); border:1px solid rgba(220, 38, 38, 0.2);">
            <div style="width:70px; height:70px; background:rgba(239, 68, 68, 0.1); color:#ef4444; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; margin:0 auto 1.5rem; animation: pulse-red 2s infinite;">
                <i class="ph-bold ph-warning-diamond"></i>
            </div>
            <h3 style="font-family:'Outfit',sans-serif; font-size:1.5rem; font-weight:800; color:var(--text); margin-bottom:1rem;">Activate Emergency Reassignment?</h3>
            <p style="color:var(--text-dim); line-height:1.6; font-size:0.95rem; margin-bottom:2rem;">
                This will automatically transfer <b>all</b> your upcoming sessions to available counselors. 
                Notifications will be sent to the assigned counselors and students immediately.
            </p>
            <div style="grid-template-columns:1fr 1fr; display:grid; gap:1rem;">
                <button onclick="closeEmergencyModal()" class="btn-secondary" style="padding:0.85rem; font-weight:700;">Cancel</button>
                <button onclick="triggerEmergencyProtocol()" class="btn-primary" style="background:#ef4444; border:none; padding:0.85rem; font-weight:700;">Yes, Activate Crisis Mode</button>
            </div>
        </div>
    </div>

    <!-- Complete Session Confirmation Modal -->
    <div id="completeConfirmModal" class="modal-overlay">
        <div class="modal-glass">
            <div style="width:64px; height:64px; border-radius:50%; background:rgba(16, 185, 129, 0.1); color:#10b981; display:flex; align-items:center; justify-content:center; font-size:2rem; margin:0 auto 1.5rem;">
                <i class="ph-bold ph-check-circle"></i>
            </div>
            <h3 style="font-family:'Outfit',sans-serif; font-size:1.5rem; font-weight:800; color:var(--text); margin-bottom:0.75rem;">Conclude Session?</h3>
            <p style="color:var(--text-dim); line-height:1.6; font-size:0.95rem; margin-bottom:2rem;">
                Marking this session as <b>Completed</b> will officially archive the intake and send a feedback link directly to the student.
            </p>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <button onclick="document.getElementById('completeConfirmModal').style.display='none'" class="btn-secondary" style="padding:0.85rem; font-weight:700;">Cancel</button>
                <button id="finalCompleteBtn" class="btn-primary" style="padding:0.85rem; font-weight:700;">Yes, Conclude</button>
            </div>
        </div>
    </div>
    </div>

    <footer class="footer">
        <p>© {{ date('Y') }} PSU Mental Health Portal</p>
    </footer>

    <script>
        function openRescheduleModal(id, time) {
            document.getElementById('res_appt_id').value = id;
            document.getElementById('res_datetime').value = time;
            document.getElementById('rescheduleModal').style.display = 'flex';
        }
        function closeRescheduleModal() {
            document.getElementById('rescheduleModal').style.display = 'none';
        }
        async function refreshAppointments(section) {
            // section: 'queue', 'archive', or 'all'
            const scrollY = window.scrollY;
            try {
                if (!section || section === 'queue' || section === 'all') {
                    await AjaxHelpers.refreshSection(window.location.href, '#intakeQueueGrid');
                }
                if (section === 'archive' || section === 'all') {
                    await AjaxHelpers.refreshSection(window.location.href, '#sessionArchiveTable');
                }
                window.scrollTo({ top: scrollY, behavior: 'instant' });
            } catch (err) {
                console.error("Refresh failed", err);
            }
        }

        async function submitReschedule(e) {
            e.preventDefault();
            const fd = new FormData(e.target);
            try {
                const res = await fetch("{{ route('counselor.appointments.action') }}", {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                });
                const data = await res.json();
                if (data.success) {
                    closeRescheduleModal();
                    await refreshAppointments();
                    App.toast({ type: 'success', title: 'Rescheduled', message: 'Session updated.' });
                } else {
                    App.toast({ type: 'error', title: 'Failed', message: data.error || 'Update failed' });
                }
            } catch (err) {
                App.toast({ type: 'error', title: 'Error', message: 'Network error' });
            }
        }

        function emergencyProtocol() {
            const modal = document.getElementById('emergencyConfirmModal');
            if (modal) modal.style.display = 'flex';
        }

        function closeEmergencyModal() {
            const modal = document.getElementById('emergencyConfirmModal');
            if (modal) modal.style.display = 'none';
        }

        async function triggerEmergencyProtocol() {
            closeEmergencyModal();
            const btn = document.getElementById('emergencyBtn');
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span>⏳ PROCESSING REASSIGNMENTS...</span>';
            btn.style.animation = 'none';

            try {
                const res = await fetch("{{ route('counselor.appointments.emergency') }}", {
                    method: 'POST',
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Content-Type': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    App.toast({ type: 'success', title: 'Emergency Protocol Active', message: data.message });
                    await refreshAppointments();
                } else {
                    App.toast({ type: 'error', title: 'Protocol Failed', message: data.error || 'Emergency protocol failed' });
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                App.toast({ type: 'error', title: 'Error', message: 'Network error. Protocol failed.' });
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        async function apptAction(action, apptId, btn) {
            if (action === 'complete') {
                const modal = document.getElementById('completeConfirmModal');
                const finalBtn = document.getElementById('finalCompleteBtn');
                modal.style.display = 'flex';
                
                finalBtn.onclick = async () => {
                    finalBtn.disabled = true;
                    finalBtn.textContent = 'Archiving...';
                    await performApptAction(action, apptId, btn);
                };
                return;
            }
            
            await performApptAction(action, apptId, btn);
        }

        async function performApptAction(action, apptId, btn) {
            const fd = new FormData();
            fd.append('appt_action', action);
            fd.append('appointment_id', apptId);
            fd.append('_token', "{{ csrf_token() }}");

            if(btn) btn.disabled = true;

            try {
                const res = await fetch("{{ route('counselor.appointments.action') }}", {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                });
                const data = await res.json();
                if (data.success) {
                    if (action === 'complete') {
                        document.getElementById('completeConfirmModal').style.display = 'none';
                        App.toast({ type: 'success', title: 'Completed', message: 'Session archived.' });
                        await refreshAppointments('all');
                    } else if (['decline', 'cancel'].includes(action)) {
                        App.toast({ type: 'info', title: 'Updated', message: 'Session ' + action + 'ed.' });
                        await refreshAppointments('queue');
                    } else {
                        // confirm
                        const card = document.getElementById('appt-card-' + apptId);
                        if (card) AjaxHelpers.flashRow(card);
                        await refreshAppointments('queue');
                    }
                }
            } catch (err) {
                App.toast({ type: 'error', title: 'Error', message: 'Network error' });
                if(btn) btn.disabled = false;
            }
        }

        let qrHandler = null;
        function showSurveyQR(id, url) {
            const qrContainer = document.getElementById('qrcode');
            const qrLink = document.getElementById('qrLink');
            qrContainer.innerHTML = '';
            qrLink.textContent = url;
            
            qrHandler = new QRCode(qrContainer, {
                text: url,
                width: 250,
                height: 250,
                colorDark : "#0f172a",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
            
            document.getElementById('qrModal').style.display = 'flex';
        }

        function closeQRModal() {
            document.getElementById('qrModal').style.display = 'none';
        }

        function openEditModal(id, reason, duration) {
            document.getElementById('edit_appt_id').value = id;
            document.getElementById('edit_reason').value = reason;
            document.getElementById('edit_duration').value = duration;
            document.getElementById('editApptModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editApptModal').style.display = 'none';
        }

        async function submitEdit(e) {
            e.preventDefault();
            const fd = new FormData(e.target);
            try {
                const res = await fetch("{{ route('counselor.appointments.action') }}", {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                });
                const data = await res.json();
                if (data.success) {
                    closeEditModal();
                    await refreshAppointments();
                    App.toast({ type: 'success', title: 'Updated', message: 'Booking details synchronized.' });
                } else {
                    App.toast({ type: 'error', title: 'Failed', message: data.error || 'Update failed' });
                }
            } catch (err) {
                App.toast({ type: 'error', title: 'Error', message: 'Network error' });
            }
        }
    </script>
@endsection