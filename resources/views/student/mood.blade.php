@extends('layouts.app')

@push('styles')
<style>
    .mood-selector {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0.75rem;
        margin: 1.75rem 0;
    }
    .mood-option {
        background: var(--surface-2);
        border: 2px solid transparent;
        border-radius: var(--radius);
        padding: 1.25rem 0.75rem;
        text-align: center;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }
    .mood-option .emoji {
        font-size: 2.25rem;
        filter: grayscale(100%);
        opacity: 0.3;
        transition: var(--transition);
    }
    .mood-option .label {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-dim);
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .mood-option:hover { 
        transform: translateY(-3px); 
        background: var(--surface-solid); 
        border-color: var(--primary-light); 
        box-shadow: var(--shadow-sm); 
    }
    .mood-option:hover .emoji { filter: grayscale(0%); opacity: 1; transform: scale(1.05); }
    
    .mood-option.selected {
        background: var(--surface-solid);
        border-color: var(--primary);
        box-shadow: 0 8px 20px var(--primary-glow);
        transform: translateY(-2px);
    }
    .mood-option.selected .emoji { filter: grayscale(0%); opacity: 1; transform: scale(1.15); }
    .mood-option.selected .label { color: var(--primary); }

    .timeline-container {
        position: relative;
        padding-left: 2rem;
    }
    .timeline-container::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, var(--primary) 0%, var(--surface-2) 100%);
        border-radius: 1px;
    }
    
    .timeline-entry {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .timeline-entry::before {
        content: '';
        position: absolute;
        left: -2rem;
        top: 20px;
        width: 10px;
        height: 10px;
        background: var(--surface-solid);
        border: 2px solid var(--primary);
        border-radius: 50%;
        transform: translateX(-50%);
        z-index: 2;
        box-shadow: 0 0 0 4px var(--surface-solid);
    }
    
    .entry-card {
        background: var(--surface-solid);
        padding: 1.5rem;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        transition: var(--transition);
        box-shadow: var(--shadow-sm);
    }
    .entry-card:hover { 
        border-color: var(--border-hover); 
        box-shadow: var(--shadow); 
    }
    
    .entry-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        border-bottom: 1px solid var(--border);
        padding-bottom: 0.75rem;
    }
    @media (max-width: 900px) {
        .mood-selector {
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 0.5rem !important;
        }
        .mood-grid-container {
            grid-template-columns: 1fr !important;
            gap: 1.5rem !important;
        }
        .mood-sticky-col {
            position: static !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1100px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem;">
        <div>
            <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Emotional Tracking</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Mood Journal</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">Track how you're feeling and spot patterns over time.</p>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary);">{{ $history->count() }}</div>
            <div style="font-size: 0.72rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.06em;">Entries</div>
        </div>
    </div>

    <!-- AJAX feedback banner -->
    <div id="moodAlert" style="display:none; padding: 0.85rem 1.25rem; border-radius: var(--radius-sm); font-weight: 600; font-size: 0.9rem; margin-bottom: 1.5rem; text-align: center;"></div>

    <!-- Aria's Insight Card -->
    <div id="ariaInsightCard" class="glass" style="border-radius: var(--radius); padding: 1.5rem 2rem; margin-bottom: 2.5rem; display: flex; gap: 1.5rem; align-items: center; box-shadow: var(--shadow-sm);">
        <div style="width: 54px; height: 54px; border-radius: 16px; background: var(--surface-2); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; box-shadow: 0 4px 12px var(--primary-glow); flex-shrink: 0;">✨</div>
        <div style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em;">Aria's Insight</h3>
                <button onclick="getAriaInsight()" id="insightRefreshBtn" style="background: none; border: none; color: var(--primary-light); cursor: pointer; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Refresh ✨</button>
            </div>
            <p id="ariaInsightText" style="color: var(--text); font-size: 0.95rem; line-height: 1.6; font-weight: 500; font-style: italic;">
                Logging your mood regularly helps Aria understand you better. Click 'Refresh' to see what Aria thinks of your recent progress.
            </p>
        </div>
    </div>

    <div class="mood-grid-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; align-items: start;">
        
        <!-- Mood Selector -->
        <div class="mood-sticky-col" style="background: var(--surface-solid); border-radius: var(--radius); padding: 2rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); position: sticky; top: 80px;">
            <div style="width: 44px; height: 44px; border-radius: var(--radius-sm); background: var(--primary-glow); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 1.25rem;">🧘</div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text);">How are you feeling?</h2>
            <p style="color: var(--text-muted); font-weight: 400; margin-bottom: 1.75rem; line-height: 1.6; font-size: 0.88rem;">Select your current mood. Your honesty helps build a clearer picture.</p>
            
            <form id="moodForm">
                @csrf
                <input type="hidden" name="mood_score" id="selected_mood_score" value="">
                
                <div class="mood-selector">
                    @php
                    $moodData = [
                        1 => ['emoji' => '😢', 'label' => 'Struggle'],
                        2 => ['emoji' => '😕', 'label' => 'Uneasy'],
                        3 => ['emoji' => '😐', 'label' => 'Neutral'],
                        4 => ['emoji' => '🙂', 'label' => 'Good'],
                        5 => ['emoji' => '😊', 'label' => 'Great']
                    ];
                    @endphp
                    @foreach ($moodData as $score => $m)
                    <div class="mood-option" onclick="selectMood({{ $score }}, this)">
                        <span class="emoji">{{ $m['emoji'] }}</span>
                        <span class="label">{{ $m['label'] }}</span>
                    </div>
                    @endforeach
                </div>

                <div style="margin-bottom: 1.75rem;">
                    <label style="display: block; font-weight: 600; font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.75rem;">Notes (Optional)</label>
                    <textarea name="note" placeholder="What's on your mind today?" style="width: 100%; padding: 1.25rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-family: inherit; font-size: 0.95rem; height: 120px; resize: none; background: var(--surface-2); transition: var(--transition); line-height: 1.6; color: var(--text);"></textarea>
                </div>

                <button type="submit" id="moodSubmitBtn" style="width: 100%; background: var(--primary); color: white; border: none; padding: 0.85rem; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); transition: var(--transition); font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <span id="btnText">Log Mood →</span>
                    <div id="btnLoader" style="display: none; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                </button>
            </form>
        </div>

        <!-- Timeline -->
        <div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: var(--text); margin-bottom: 1.5rem;">Your Timeline</h2>

            <div id="noEntries" style="display: {{ $history->isEmpty() ? 'block' : 'none' }}">
                <div class="empty-state" style="text-align: center; padding: 3rem 0;">
                    <span style="font-size: 3rem;">📖</span>
                    <h3 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem;">No entries yet</h3>
                    <p style="color: var(--text-dim); font-size: 0.88rem;">Log your first mood to start building your timeline.</p>
                </div>
            </div>

            <div class="timeline-container" id="timelineContainer">
                @foreach ($history as $entry)
                <div class="timeline-entry" id="entry-{{ $entry->log_id }}">
                    <div class="entry-card">
                        <div class="entry-header">
                            <span style="font-weight: 600; font-size: 0.9rem; color: var(--text);">{{ $entry->logged_at->format('F d, Y') }}</span>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                @if($entry->sentiment_label)
                                    @php
                                        $labelColors = [
                                            'Positive' => ['bg' => 'rgba(16, 185, 129, 0.1)', 'color' => '#10b981'],
                                            'Grateful' => ['bg' => 'rgba(16, 185, 129, 0.1)', 'color' => '#059669'],
                                            'Neutral' => ['bg' => 'rgba(100, 116, 139, 0.1)', 'color' => '#64748b'],
                                            'Anxious' => ['bg' => 'rgba(99, 102, 241, 0.1)', 'color' => '#6366f1'],
                                            'Overwhelmed' => ['bg' => 'rgba(139, 92, 246, 0.1)', 'color' => '#8b5cf6'],
                                            'Sad' => ['bg' => 'rgba(59, 130, 246, 0.1)', 'color' => '#3b82f6'],
                                            'Frustrated' => ['bg' => 'rgba(239, 68, 68, 0.1)', 'color' => '#ef4444'],
                                        ];
                                        $lc = $labelColors[$entry->sentiment_label] ?? ['bg' => 'var(--surface-2)', 'color' => 'var(--text-dim)'];
                                    @endphp
                                    <span class="sentiment-badge-{{ $entry->log_id }}" style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; background: {{ $lc['bg'] }}; color: {{ $lc['color'] }}; padding: 0.25rem 0.6rem; border-radius: 6px; border: 1px solid {{ $lc['color'] }}30;">
                                        {{ $entry->sentiment_label }}
                                    </span>
                                @endif
                                <span class="entry-emoji-{{ $entry->log_id }}" style="font-size: 1.5rem;">{{ $entry->mood_emoji }}</span>
                                <span style="font-size: 0.72rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; background: var(--surface-2); padding: 0.3rem 0.75rem; border-radius: var(--radius-sm); letter-spacing: 0.04em;">
                                    {{ $entry->logged_at->format('g:i A') }}
                                </span>
                                <button onclick="openEditModal({{ $entry->log_id }}, {{ $entry->mood_score }}, {{ json_encode($entry->note) }})"
                                        style="background: none; border: 1px solid var(--border); border-radius: 8px; padding: 0.3rem 0.65rem; cursor: pointer; color: var(--text-dim); font-size: 0.72rem; font-weight: 600; transition: var(--transition);" title="Edit entry">
                                    ✏️ Edit
                                </button>
                            </div>
                        </div>
                        <p class="entry-note-{{ $entry->log_id }}" style="color: var(--text); line-height: 1.6; font-size: 0.9rem; font-weight: 400; padding-left: 0.75rem; border-left: 3px solid var(--primary-light); {{ $entry->note ? '' : 'color: var(--text-dim); font-style: italic; font-weight: 400;' }}">
                            {{ $entry->note ?: 'No notes added.' }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <p>© {{ date('Y') }} PSU Mental Health Portal</p>
</footer>

{{-- Edit Mood Modal --}}
<div id="editMoodModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.65); backdrop-filter:blur(10px); align-items:center; justify-content:center;" onclick="closeEditModal(event)">
    <div style="background:var(--surface-solid); border:1px solid var(--border); border-radius:var(--radius-lg); padding:2.5rem; max-width:460px; width:90%; box-shadow:var(--shadow-lg); position:relative;" onclick="event.stopPropagation()">
        <h3 style="font-family:'Outfit',sans-serif; font-size:1.15rem; font-weight:700; color:var(--text); margin-bottom:1.75rem;">✏️ Edit Mood Entry</h3>
        <input type="hidden" id="editLogId">

        <div style="margin-bottom:1.5rem;">
            <label style="display:block; font-size:0.78rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.75rem;">Mood</label>
            <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:0.5rem;" id="editMoodSelector">
                @foreach([1=>'😢',2=>'😕',3=>'😐',4=>'🙂',5=>'😊'] as $s => $em)
                <div class="edit-mood-opt" data-score="{{ $s }}"
                     onclick="selectEditMood({{ $s }}, this)"
                     style="background:var(--surface-2); border:2px solid transparent; border-radius:var(--radius-sm); padding:0.85rem 0.5rem; text-align:center; cursor:pointer; transition:var(--transition);">
                    <div style="font-size:1.6rem;">{{ $em }}</div>
                    <div style="font-size:0.6rem; font-weight:600; color:var(--text-dim); text-transform:uppercase; margin-top:0.3rem;">{{ $s }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <div style="margin-bottom:1.75rem;">
            <label style="display:block; font-size:0.78rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.75rem;">Note (Optional)</label>
            <textarea id="editNote" rows="4" placeholder="Update your note…"
                      style="width:100%; padding:1rem; border-radius:var(--radius-sm); border:1.5px solid var(--border); font-family:inherit; font-size:0.95rem; resize:none; background:var(--surface-2); color:var(--text); line-height:1.6;"></textarea>
        </div>

        <div style="display:flex; gap:0.75rem;">
            <button onclick="closeEditModal()" style="flex:1; background:var(--surface-2); border:none; padding:0.8rem; border-radius:var(--radius-sm); font-weight:600; font-size:0.9rem; color:var(--text-muted); cursor:pointer;">Cancel</button>
            <button onclick="submitEditMood()" id="editSaveBtn" style="flex:2; background:var(--primary); border:none; padding:0.8rem; border-radius:var(--radius-sm); font-weight:700; font-size:0.9rem; color:white; cursor:pointer; box-shadow:0 4px 12px rgba(13,148,136,0.2);">Save Changes</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let editMoodScore = null;

function openEditModal(logId, score, note) {
    document.getElementById('editLogId').value = logId;
    document.getElementById('editNote').value = note || '';
    editMoodScore = score;
    document.querySelectorAll('.edit-mood-opt').forEach(opt => {
        const s = parseInt(opt.dataset.score);
        opt.style.borderColor = s === score ? 'var(--primary)' : 'transparent';
        opt.style.background  = s === score ? 'var(--surface-solid)' : 'var(--surface-2)';
    });
    document.getElementById('editMoodModal').style.display = 'flex';
}

function selectEditMood(score, el) {
    editMoodScore = score;
    document.querySelectorAll('.edit-mood-opt').forEach(opt => {
        opt.style.borderColor = 'transparent';
        opt.style.background  = 'var(--surface-2)';
    });
    el.style.borderColor = 'var(--primary)';
    el.style.background  = 'var(--surface-solid)';
}

function closeEditModal(e) {
    if (e && e.target !== document.getElementById('editMoodModal')) return;
    document.getElementById('editMoodModal').style.display = 'none';
}

async function submitEditMood() {
    const logId = document.getElementById('editLogId').value;
    const note  = document.getElementById('editNote').value;
    const btn   = document.getElementById('editSaveBtn');
    if (!editMoodScore) { showMoodAlert('Please select a mood.', false); return; }

    btn.disabled = true;
    btn.textContent = 'Saving…';

    const fd = new FormData();
    fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    fd.append('_method', 'PATCH');
    fd.append('mood_score', editMoodScore);
    fd.append('note', note);

    try {
        const res  = await fetch(`/student/mood/${logId}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        const data = await res.json();
        if (data.success) {
            // Update the card in-place
            const noteEl  = document.querySelector(`.entry-note-${logId}`);
            const emojiEl = document.querySelector(`.entry-emoji-${logId}`);
            if (noteEl)  noteEl.textContent  = data.note || 'No notes added.';
            if (emojiEl) emojiEl.textContent = data.emoji;
            document.getElementById('editMoodModal').style.display = 'none';
            showMoodAlert('✅ Entry updated!', true);
        } else {
            showMoodAlert('❌ ' + (data.message || 'Update failed.'), false);
        }
    } catch(err) {
        showMoodAlert('❌ Connection error.', false);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save Changes';
    }
}

function selectMood(score, el) {
    document.querySelectorAll('.mood-option').forEach(opt => opt.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('selected_mood_score').value = score;
}

// AJAX mood form submission
document.getElementById('moodForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const score = document.getElementById('selected_mood_score').value;
    if (!score) {
        showMoodAlert('Please select a mood first.', false);
        return;
    }
    const btn = document.getElementById('moodSubmitBtn');
    const btnText = document.getElementById('btnText');
    const btnLoader = document.getElementById('btnLoader');
    const note = document.querySelector('textarea[name="note"]').value;

    btn.disabled = true;
    btnText.textContent = 'Logging...';
    btnLoader.style.display = 'block';

    const fd = new FormData(this);

    try {
        const res = await fetch('{{ route("student.mood.store") }}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        const data = await res.json();

        if (data.success) {
            showMoodAlert('✅ Mood logged successfully!', true);
            document.getElementById('noEntries').style.display = 'none';
            
            // Prepend new timeline entry
            const container = document.getElementById('timelineContainer');
            
            let sentimentHtml = '';
            if (data.sentiment_label) {
                const colors = {
                    'Positive': {bg: 'rgba(16, 185, 129, 0.1)', color: '#10b981'},
                    'Grateful': {bg: 'rgba(16, 185, 129, 0.1)', color: '#059669'},
                    'Neutral': {bg: 'rgba(100, 116, 139, 0.1)', color: '#64748b'},
                    'Anxious': {bg: 'rgba(99, 102, 241, 0.1)', color: '#6366f1'},
                    'Overwhelmed': {bg: 'rgba(139, 92, 246, 0.1)', color: '#8b5cf6'},
                    'Sad': {bg: 'rgba(59, 130, 246, 0.1)', color: '#3b82f6'},
                    'Frustrated': {bg: 'rgba(239, 68, 68, 0.1)', color: '#ef4444'},
                };
                const c = colors[data.sentiment_label] || {bg: 'var(--surface-2)', color: 'var(--text-dim)'};
                sentimentHtml = `<span style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; background: ${c.bg}; color: ${c.color}; padding: 0.25rem 0.6rem; border-radius: 6px; border: 1px solid ${c.color}30; margin-right: 0.75rem;">${data.sentiment_label}</span>`;
            }

            const noteHtml = data.note
                ? `<p style="color:var(--text);line-height:1.6;font-size:0.9rem;font-weight:400;padding-left:0.75rem;border-left:3px solid var(--primary-light);">${data.note}</p>`
                : `<p style="color:var(--text-dim);font-style:italic;font-weight:400;font-size:0.88rem;">No notes added.</p>`;
            
            const entry = document.createElement('div');
            entry.className = 'timeline-entry';
            entry.style.opacity = '0';
            entry.style.transform = 'translateY(-20px)';
            entry.style.transition = 'opacity 0.4s, transform 0.4s';
            entry.innerHTML = `
                <div class="entry-card">
                    <div class="entry-header">
                        <span style="font-weight:600;font-size:0.9rem;color:var(--text);">${data.date}</span>
                        <div style="display:flex;align-items:center;gap:0.75rem;">
                            ${sentimentHtml}
                            <span class="entry-emoji-${data.log_id}" style="font-size:1.5rem;">${data.emoji}</span>
                            <span style="font-size:0.72rem;font-weight:600;color:var(--text-dim);text-transform:uppercase;background:var(--surface-2);padding:0.3rem 0.75rem;border-radius:var(--radius-sm);">${data.time}</span>
                            <button onclick="openEditModal(${data.log_id}, 0, '')"
                                    style="background:none;border:1px solid var(--border);border-radius:8px;padding:0.3rem 0.65rem;cursor:pointer;color:var(--text-dim);font-size:0.72rem;font-weight:600;" title="Edit entry">
                                ✏️ Edit
                            </button>
                        </div>
                    </div>
                    <p class="entry-note-${data.log_id}" style="color:var(--text);line-height:1.6;font-size:0.9rem;font-weight:400;padding-left:0.75rem;border-left:3px solid var(--primary-light);">${data.note || 'No notes added.'}</p>
                </div>`;
            container.insertBefore(entry, container.firstChild);
            requestAnimationFrame(() => {
                entry.style.opacity = '1';
                entry.style.transform = 'translateY(0)';
            });
            
            // Reset form
            document.querySelectorAll('.mood-option').forEach(opt => opt.classList.remove('selected'));
            document.getElementById('selected_mood_score').value = '';
            document.querySelector('textarea[name="note"]').value = '';
            
            // Auto-refresh insight
            getAriaInsight();
        } else {
            showMoodAlert('❌ ' + (data.error || 'Failed to save. Please try again.'), false);
        }
    } catch(err) {
        showMoodAlert('❌ Connection error. Please try again.', false);
    } finally {
        btn.disabled = false;
        btnText.textContent = 'Log Mood →';
        btnLoader.style.display = 'none';
    }
});

function showMoodAlert(msg, success) {
    const el = document.getElementById('moodAlert');
    el.style.display = 'block';
    el.style.background = success ? 'rgba(13, 148, 136, 0.1)' : 'rgba(239, 68, 68, 0.1)';
    el.style.color = success ? 'var(--primary)' : '#ef4444';
    el.style.border = success ? '1px solid rgba(13, 148, 136, 0.2)' : '1px solid rgba(239, 68, 68, 0.2)';
    el.textContent = msg;
    setTimeout(() => { el.style.display = 'none'; }, 4000);
}

async function getAriaInsight() {
    const text = document.getElementById('ariaInsightText');
    const btn = document.getElementById('insightRefreshBtn');
    
    text.style.opacity = '0.5';
    btn.disabled = true;
    btn.textContent = 'Seeking... ✨';

    try {
        const res = await fetch('{{ route("student.mood.insight") }}');
        const data = await res.json();
        
        if (data.success) {
            text.textContent = data.insight;
        } else {
            text.textContent = data.error || 'Aria needs a moment to gather her thoughts.';
        }
    } catch (err) {
        text.textContent = 'Could not connect to Aria right now.';
    } finally {
        text.style.opacity = '1';
        btn.disabled = false;
        btn.textContent = 'Refresh ✨';
    }
}

@if($history->isNotEmpty())
window.addEventListener('DOMContentLoaded', getAriaInsight);
@endif
</script>
@endpush
