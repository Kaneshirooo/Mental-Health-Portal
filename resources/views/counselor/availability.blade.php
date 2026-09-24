@extends('layouts.app')

@php 
    $fallbackDays = [
        ['id' => 1, 'name' => 'Monday'],
        ['id' => 2, 'name' => 'Tuesday'],
        ['id' => 3, 'name' => 'Wednesday'],
        ['id' => 4, 'name' => 'Thursday'],
        ['id' => 5, 'name' => 'Friday'],
    ];
    $finalDays = $fallbackDays;
@endphp

@push('styles')
<style>
    .schedule-stripe {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        padding: 1rem 1.5rem;
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: 18px;
        margin-bottom: 0.85rem;
        box-shadow: var(--shadow-sm);
        transition: all 0.25s ease;
    }
    .schedule-stripe:hover {
        border-color: var(--primary-light);
        transform: translateX(4px);
    }
    
    .day-indicator {
        font-size: 0.7rem;
        font-weight: 900;
        text-transform: uppercase;
        color: #ffffff;
        background: var(--primary);
        padding: 0.4rem 0.85rem;
        border-radius: 10px;
        min-width: 90px;
        text-align: center;
        letter-spacing: 0.05em;
        box-shadow: 0 4px 12px var(--primary-glow);
    }
    .time-val {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: var(--text);
        font-size: 1rem;
    }

    /* Slot Editor Card */
    .slot-card {
        background: var(--surface-2);
        border: 1.5px solid var(--border);
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1.25rem;
        display: grid;
        grid-template-columns: 1fr 140px 140px auto;
        gap: 1.25rem;
        align-items: end;
        transition: all 0.25s ease;
    }
    .slot-card:hover {
        border-color: var(--primary-light);
    }

    .day-selector {
        display: flex;
        gap: 0.35rem;
        background: var(--surface-solid);
        padding: 0.4rem;
        border-radius: 14px;
        border: 1.5px solid var(--border);
    }

    .day-opt {
        flex: 1;
        background: transparent;
        border: none;
        padding: 0.5rem 0;
        font-size: 0.75rem;
        font-weight: 900;
        border-radius: 10px;
        cursor: pointer;
        color: var(--text-dim);
        transition: all 0.2s ease;
    }
    .day-opt.active {
        background: var(--primary) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px var(--primary-glow);
    }

    .field-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 900;
        text-transform: uppercase;
        color: var(--text-dim);
        margin-bottom: 0.6rem;
        letter-spacing: 0.08em;
    }

    .time-input {
        width: 100%;
        padding: 0.85rem 1rem;
        border-radius: 12px;
        border: 1.5px solid var(--border);
        background: var(--surface-solid);
        color: var(--text);
        font-weight: 800;
        font-size: 0.9rem;
        outline: none;
        transition: all 0.2s ease;
    }
    .time-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-glow);
    }

    .btn-trash {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1.5px solid rgba(239, 68, 68, 0.2);
        width: 46px;
        height: 46px;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        transition: all 0.2s ease;
    }
    .btn-trash:hover {
        background: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
    }

    .btn-add {
        width: 100%;
        background: var(--surface-2);
        color: var(--primary);
        border: 2px dashed var(--primary-light);
        padding: 1.1rem;
        border-radius: 20px;
        font-weight: 900;
        font-size: 0.9rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        transition: all 0.25s ease;
    }
    .btn-add:hover {
        background: var(--primary-glow);
        border-color: var(--primary);
    }
</style>
@endpush

@section('content')
<main style="min-height: 100vh; padding: 2rem 0 5rem;">
<div class="container" style="max-width: 1180px; margin: 0 auto; padding: 0 1.5rem;">
    
    <header class="staggered" style="margin-bottom: 3.5rem;">
        <div style="font-weight: 800; color: var(--primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="ph-bold ph-clock" style="font-size: 1.1rem;"></i> Schedule Configuration
        </div>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.75rem; font-weight: 900; color: var(--text); margin: 0; letter-spacing: -0.04em;">Availability Management</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin-top: 0.5rem;">Configure your weekly clinical service windows and student appointment slots.</p>
    </header>

    <div style="display: grid; grid-template-columns: 360px 1fr; gap: 2.5rem;" class="staggered">
        
        <!-- Active Schedule Sidebar -->
        <aside id="activeScheduleAside">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 1.3rem; margin: 0; color: var(--text);">Active Schedule</h2>
                <span style="font-size: 0.72rem; font-weight: 900; background: var(--primary-glow); color: var(--primary); padding: 0.3rem 0.75rem; border-radius: 100px; text-transform: uppercase;">
                    {{ count($slots) }} Windows
                </span>
            </div>
            
            @if(count($slots) == 0)
                <div style="padding: 4rem 2rem; background: var(--surface-solid); border: 2px dashed var(--border); border-radius: 24px; text-align: center;">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.3;">📅</div>
                    <p style="font-weight: 800; color: var(--text-dim); margin: 0;">No service windows set.</p>
                </div>
            @else
                @foreach(collect($slots)->sortBy('day_of_week') as $s)
                <div class="schedule-stripe">
                    <div class="day-indicator">{{ ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][$s->day_of_week] }}</div>
                    <div class="time-val">{{ \Carbon\Carbon::parse($s->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($s->end_time)->format('g:i A') }}</div>
                </div>
                @endforeach
            @endif
        </aside>

        <!-- Slot Editor Panel -->
        <div style="background: var(--surface-solid); border: 1px solid var(--border); border-radius: 32px; padding: 2.75rem; box-shadow: var(--shadow-lg);">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                <div style="width: 44px; height: 44px; border-radius: 14px; background: var(--primary-glow); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="ph-bold ph-sliders-horizontal"></i>
                </div>
                <div>
                    <h2 style="font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 1.5rem; color: var(--text); margin: 0;">Refine Windows</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500; margin-top: 0.2rem;">Add or adjust individual clinical service hours.</p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('counselor.availability') }}">
                @csrf
                <div id="slotsContainer">
                    @foreach($slots as $idx => $s)
                    <div class="slot-card" id="slot-{{ $idx }}">
                        <div>
                            <label class="field-label">Institutional Day</label>
                            <input type="hidden" name="slots[{{ $idx }}][day]" value="{{ $s->day_of_week }}" id="hidden-day-{{ $idx }}">
                            <div class="day-selector" id="picker-{{ $idx }}">
                                @foreach($finalDays as $d)
                                <button type="button" class="day-opt {{ $s->day_of_week == $d['id'] ? 'active' : '' }}" onclick="selectDay({{ $idx }}, {{ $d['id'] }}, this)">{{ substr($d['name'], 0, 1) }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Start Time</label>
                            <input type="time" name="slots[{{ $idx }}][start]" value="{{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}" class="time-input" required>
                        </div>
                        <div>
                            <label class="field-label">End Time</label>
                            <input type="time" name="slots[{{ $idx }}][end]" value="{{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }}" class="time-input" required>
                        </div>
                        <button type="button" class="btn-trash" onclick="removeSlot('slot-{{ $idx }}')"><i class="ph-bold ph-trash"></i></button>
                    </div>
                    @endforeach
                </div>

                <button type="button" class="btn-add" onclick="addSlot()">
                    <i class="ph-bold ph-plus-circle" style="font-size: 1.25rem;"></i> Add Service Window
                </button>

                <div style="margin-top: 3rem; display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem;">
                    <button type="submit" class="btn-primary" id="saveAvailabilityBtn" style="padding: 1.1rem; border-radius: 16px; font-weight: 800; font-size: 0.95rem; text-transform: uppercase;">
                        Commit Changes
                    </button>
                    <a href="{{ route('counselor.dashboard') }}" class="btn-secondary" style="padding: 1.1rem; border-radius: 16px; font-weight: 800; font-size: 0.95rem; text-transform: uppercase; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>
</main>

@push('scripts')
<script>
const dayNames = @json($finalDays);
let slotIndex = {{ count($slots) }};

function addSlot() {
    const idx = slotIndex++;
    const container = document.getElementById('slotsContainer');
    const div = document.createElement('div');
    div.className = 'slot-card';
    div.id = 'slot-' + idx;
    
    div.innerHTML = `
        <div>
            <label class="field-label">Institutional Day</label>
            <input type="hidden" name="slots[${idx}][day]" value="${dayNames[0].id}" id="hidden-day-${idx}">
            <div class="day-selector" id="picker-${idx}">
                ${dayNames.map(d => `<button type="button" class="day-opt ${d.id === 1 ? 'active' : ''}" onclick="selectDay(${idx}, ${d.id}, this)">${d.name.charAt(0)}</button>`).join('')}
            </div>
        </div>
        <div>
            <label class="field-label">Start Time</label>
            <input type="time" name="slots[${idx}][start]" value="08:00" class="time-input" required>
        </div>
        <div>
            <label class="field-label">End Time</label>
            <input type="time" name="slots[${idx}][end]" value="17:00" class="time-input" required>
        </div>
        <button type="button" class="btn-trash" onclick="removeSlot('slot-${idx}')"><i class="ph-bold ph-trash"></i></button>
    `;
    container.appendChild(div);
    if (window.gsap) {
        gsap.from(div, { y: 20, opacity: 0, duration: 0.4, ease: "power2.out" });
    }
}

function selectDay(idx, dayId, btn) {
    document.getElementById('hidden-day-' + idx).value = dayId;
    btn.parentElement.querySelectorAll('.day-opt').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

function removeSlot(id) {
    const el = document.getElementById(id);
    if (window.gsap) {
        gsap.to(el, { opacity: 0, height: 0, marginBottom: 0, duration: 0.3, onComplete: () => el.remove() });
    } else {
        el.remove();
    }
}

$(document).ready(function() {
    const form = $('form[action*="availability"]');
    const btn = $('#saveAvailabilityBtn');

    form.on('submit', function(e) {
        e.preventDefault();
        
        btn.prop('disabled', true).html('<i class="ph ph-circle-notch animate-spin"></i> Synchronizing...');
        
        App.ajax({
            url: form.attr('action'),
            data: form.serialize(),
            success: async (res) => {
                App.toast({
                    type: 'success',
                    title: 'Database Updated',
                    message: res.message || 'Your clinical availability has been successfully synchronized.'
                });
                
                // Update sidebar without reload
                try {
                    await AjaxHelpers.refreshSection(window.location.href, '#activeScheduleAside');
                } catch (err) {
                    console.error("Failed to refresh schedule preview", err);
                }
            },
            complete: () => {
                btn.prop('disabled', false).html('Commit Changes');
            }
        });
    });

    if (window.gsap) {
        gsap.from('.staggered', { y: 30, opacity: 0, duration: 0.8, stagger: 0.1, ease: 'expo.out', clearProps: 'all' });
    }
});
</script>
@endpush
@endsection

