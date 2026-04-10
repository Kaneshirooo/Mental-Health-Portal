@extends('layouts.app')

@php 
    $fallbackDays = [
        ['id' => 1, 'name' => 'Monday'],
        ['id' => 2, 'name' => 'Tuesday'],
        ['id' => 3, 'name' => 'Wednesday'],
        ['id' => 4, 'name' => 'Thursday'],
        ['id' => 5, 'name' => 'Friday'],
        ['id' => 6, 'name' => 'Saturday'],
        ['id' => 0, 'name' => 'Sunday'],
    ];
    $finalDays = isset($days) && is_array($days) && count($days) === 7 ? $days : $fallbackDays;
@endphp

@push('styles')
<style>
    .slot-row {
        display: grid;
        grid-template-columns: 1fr 120px 120px auto;
        gap: 1.5rem;
        align-items: end;
        padding: 1.5rem;
        background: var(--surface-2);
        border-radius: var(--radius);
        margin-bottom: 1rem;
        transition: var(--transition);
        border: 1px solid var(--border);
    }
    .slot-row:hover { background: var(--surface-solid); border-color: var(--border-hover); box-shadow: var(--shadow-sm); }
    
    .add-slot-btn {
        width: 100%;
        background: var(--surface-solid);
        border: 2px dashed var(--border);
        color: var(--primary);
        border-radius: var(--radius-sm);
        padding: 1rem;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        margin-top: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    .add-slot-btn:hover { border-color: var(--primary); background: #f0fdfa; transform: translateY(-1px); }
    
    .day-chip {
        background: #f0fdf4;
        color: #166534;
        padding: 0.35rem 0.85rem;
        border-radius: 99px;
        font-weight: 700;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border: 1px solid rgba(22, 163, 74, 0.2);
    }

    .schedule-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        margin-bottom: 0.5rem;
        transition: var(--transition);
    }
    .schedule-item:hover { transform: translateX(3px); border-color: var(--border-hover); }

    .availability-input {
        width: 100%;
        padding: 0.65rem 0.85rem;
        border-radius: 10px;
        border: 2px solid #cbd5e1;
        font-weight: 700;
        font-size: 0.88rem;
        font-family: inherit;
        background-color: #ffffff !important;
        color: #0f172a !important;
        transition: var(--transition-fast);
        color-scheme: light;
        cursor: pointer;
        min-height: 45px;
        line-height: normal;
    }
    .availability-input:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.1);
    }
    .dark-mode .availability-input {
        background-color: #1e293b !important;
        color: #f1f5f9 !important;
        border-color: rgba(255, 255, 255, 0.1);
        color-scheme: dark;
    }
    .availability-input option {
        background-color: #ffffff !important;
        color: #0f172a !important;
    }
    .dark-mode .availability-input option {
        background-color: #1e293b !important;
        color: #f1f5f9 !important;
    }

    .day-btn {
        flex: 1;
        padding: 0.5rem 0.25rem;
        background: var(--surface-solid);
        border: 2px solid var(--border);
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.7rem;
        color: var(--text-muted);
        cursor: pointer;
        transition: var(--transition-fast);
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .day-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-glow); }
    .day-btn.active {
        background: var(--primary) !important;
        color: white !important;
        border-color: var(--primary) !important;
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2);
    }
    .day-picker-container {
        display: flex;
        gap: 0.25rem;
        width: 100%;
    }
</style>
@endpush

@section('content')
<main class="main-content">
<div class="container" style="max-width: 1000px; padding-top: 1.5rem; padding-bottom: 3rem;">
    <div style="margin-bottom: 2rem;">
        <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Clinical Profile</div>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Manage Availability</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">Define your active clinical hours for student discovery and booking.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 2rem; align-items: start;">
        <!-- Current Schedule -->
        <div>
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.15rem; margin-bottom: 1.5rem; color: var(--text);">Active Hours</h2>
            @if(count($slots) == 0)
                <div style="padding: 2.5rem; background: var(--surface-2); border-radius: var(--radius); text-align: center; border: 1px dashed var(--border);">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">⏳</div>
                    <p style="font-weight: 600; color: var(--text-muted); font-size: 0.85rem;">No active hours defined yet.</p>
                </div>
            @else
                <div class="current-slots">
                    @php $dayMap = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']; @endphp
                    @foreach($slots as $s)
                    <div class="schedule-item">
                        <span class="day-chip">{{ $dayMap[$s->day_of_week] }}</span>
                        <span style="font-weight: 600; color: var(--text); font-size: 0.88rem;">
                            {{ \Carbon\Carbon::parse($s->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($s->end_time)->format('g:i A') }}
                        </span>
                    </div>
                    @endforeach
                </div>
            @endif

            <div style="margin-top: 1.5rem; padding: 1.25rem; background: #f0fdfa; border-radius: var(--radius-sm); border: 1px solid rgba(13, 148, 136, 0.1);">
                <div style="font-weight: 700; color: var(--primary); margin-bottom: 0.35rem; font-size: 0.8rem;">Clinical Tip</div>
                <p style="font-size: 0.78rem; color: var(--primary-dark); line-height: 1.5; font-weight: 400;">Consistency in your availability helps students build trust and ensures a stable support structure.</p>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="card" style="padding: 1.5rem; border-radius: var(--radius);">
            <h2 style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text);">Modify Schedule</h2>
            <form method="POST" action="{{ route('counselor.availability') }}" id="availForm">
                @csrf
                <div id="slotsContainer">
                    @foreach($slots as $idx => $s)
                    <div class="slot-row" id="slot-{{ $idx }}">
                        <div class="form-group" style="margin:0;">
                            <label style="font-weight: 800; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.75rem; display: block;">Day of Week Selection</label>
                            <input type="hidden" name="slots[{{ $idx }}][day]" value="{{ $s->day_of_week }}" id="hidden-day-{{ $idx }}">
                            <div class="day-picker-container" id="picker-{{ $idx }}">
                                @foreach($finalDays as $d)
                                    <button type="button" class="day-btn {{ $s->day_of_week == $d['id'] ? 'active' : '' }}" 
                                            onclick="selectDay({{ $idx }}, {{ $d['id'] }}, this)"
                                            title="{{ $d['name'] }}">
                                        {{ substr($d['name'], 0, 1) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group" style="margin:0">
                            <label style="font-weight: 800; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem; display: block;">Starting Time</label>
                            <input type="time" name="slots[{{ $idx }}][start]" value="{{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}" class="availability-input" required>
                        </div>
                        <div class="form-group" style="margin:0">
                            <label style="font-weight: 800; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem; display: block;">Ending Time</label>
                            <input type="time" name="slots[{{ $idx }}][end]" value="{{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }}" class="availability-input" required>
                        </div>
                        <button type="button" onclick="removeSlot('slot-{{ $idx }}')" style="background: #fff1f2; color: #e11d48; border: none; width: 32px; height: 32px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">✕</button>
                    </div>
                    @endforeach
                </div>

                <button type="button" class="add-slot-btn" onclick="addSlot()">+ Add Another Slot</button>

                <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
                    <button type="submit" class="btn-primary" style="flex: 2; padding: 0.85rem; border-radius: var(--radius-sm); font-weight: 600; background: var(--primary); border: none; color: white; cursor: pointer; font-size: 0.9rem;">Commit Changes</button>
                    <a href="{{ route('counselor.dashboard') }}" class="btn-secondary" style="flex: 1; padding: 0.85rem; border-radius: var(--radius-sm); font-weight: 700; border: 1.5px solid var(--border); text-align: center; text-decoration: none; color: var(--text-muted); background: var(--surface-solid); font-size: 0.9rem;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="footer">
    <p>© {{ date('Y') }} PSU Mental Health Portal</p>
</footer>

<script>
const dayNames = @json($finalDays);
let slotIndex = {{ count($slots) }};

function addSlot() {
    const idx = slotIndex++;
    const container = document.getElementById('slotsContainer');
    const div = document.createElement('div');
    div.className = 'slot-row';
    div.id = 'slot-' + idx;

    const dayPicker = dayNames.map(d => `
        <button type="button" class="day-btn ${d.id === 1 ? 'active' : ''}" 
                onclick="selectDay(${idx}, ${d.id}, this)"
                title="${d.name}">
            ${d.name.substring(0, 1)}
        </button>
    `).join('');

    div.innerHTML = `
        <div class="form-group" style="margin:0;">
            <label style="font-weight: 800; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.75rem; display: block;">Day of Week Selection</label>
            <input type="hidden" name="slots[${idx}][day]" value="${dayNames[0].id}" id="hidden-day-${idx}">
            <div class="day-picker-container" id="picker-${idx}">
                ${dayPicker}
            </div>
        </div>
        <div class="form-group" style="margin:0">
            <label style="font-weight: 800; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem; display: block;">From</label>
            <input type="time" name="slots[${idx}][start]" value="08:00" class="availability-input" required>
        </div>
        <div class="form-group" style="margin:0">
            <label style="font-weight: 800; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem; display: block;">To</label>
            <input type="time" name="slots[${idx}][end]" value="12:00" class="availability-input" required>
        </div>
        <button type="button" onclick="removeSlot('slot-${idx}')" style="background: #fff1f2; color: #e11d48; border: none; width: 32px; height: 32px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; margin-bottom: 0.5rem;">✕</button>
    `;
    container.appendChild(div);
    div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function selectDay(idx, dayId, btn) {
    const hidden = document.getElementById('hidden-day-' + idx);
    if (hidden) hidden.value = dayId;
    const container = document.getElementById('picker-' + idx);
    if (container) {
        container.querySelectorAll('.day-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }
}

function removeSlot(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}

window.addEventListener('DOMContentLoaded', function() {
    if (document.querySelectorAll('.slot-row').length === 0) addSlot();
});
</script>
</main>
@endsection
