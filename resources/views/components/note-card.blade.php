{{-- Shared full message card (same look + reply box on counselor and admin dashboards). Expects $note. --}}
<div class="note-card staggered">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; border-bottom:1px solid var(--border); padding-bottom:1.5rem;">
        <div style="display:flex; align-items:center; gap:1.1rem;">
            <div style="width:12px; height:12px; background:#10b981; border-radius:50%; box-shadow:0 0 12px #10b981;"></div>
            <div>
                <span style="font-weight:900; font-size:1.1rem; color:var(--text); letter-spacing:-0.01em;">
                    {{ $note->student?->full_name ?? 'Student' }}
                </span>
                <div style="font-size:0.7rem; font-weight:800; color:var(--text-dim); text-transform:uppercase; letter-spacing:0.05em; margin-top:0.15rem;">{{ $note->student?->roll_number ?? 'N/A' }}</div>
            </div>
        </div>
        <div style="display:flex;">
            <button onclick="speakMessage('note_content_{{ $note->note_id }}', this)"
                                    class="btn-icon" title="Read messages aloud">
                <i class="ph-bold ph-speaker-high"></i>
            </button>
        </div>
    </div>

    <div id="note_content_{{ $note->note_id }}" style="max-height:350px; overflow-y:auto; padding-right:1rem; margin-bottom:2.5rem; scroll-behavior:smooth;">
        @foreach ($note->messages as $msg)
        <div style="margin-bottom:2rem; display:flex; flex-direction:column; align-items: {{ ($msg->sender_type === 'student') ? 'flex-start' : 'flex-end' }};">
            <div style="font-weight:900; font-size:0.65rem; color:var(--text-dim); text-transform:uppercase; margin-bottom:0.4rem; opacity:0.6; letter-spacing:0.06em;">
                {{ ($msg->sender_type === 'student') ? 'Student' : 'Counselor' }}
            </div>
            <div class="{{ ($msg->sender_type === 'student') ? 'bubble-student' : 'bubble-counselor' }}">
                {{ $msg->message_text }}
            </div>
        </div>
        @endforeach
    </div>

    <form id="note_reply_form_{{ $note->note_id }}" onsubmit="submitReply({{ $note->note_id }}, event)" style="display:flex; flex-direction:column; gap:1.25rem; margin-top:auto; background:var(--surface-2); padding:1.75rem; border-radius:24px; border:1px solid var(--border);">
        @csrf
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <label style="font-weight:900; font-size:0.72rem; color:var(--text-dim); text-transform:uppercase; letter-spacing:0.1em;">Your Reply</label>
            <button type="button" onclick="suggestReply({{ $note->note_id }}, event)" class="btn-ai-assist">
                <i class="ph-bold ph-sparkle"></i> GPT-4o Assist
            </button>
        </div>
        <textarea name="message" id="reply_textarea_{{ $note->note_id }}" placeholder="Write a warm, supportive reply..." class="form-input-premium" style="height:100px; resize:none; border-radius:16px; padding:1.2rem; font-size:0.95rem; background:var(--surface-solid); border:1.5px solid var(--border); transition:all 0.3s ease;" required></textarea>
        <button type="submit" class="btn-primary" style="padding:1rem; font-weight:900; border-radius:16px; text-transform:uppercase; font-size:0.85rem; letter-spacing:0.05em; display:flex; align-items:center; justify-content:center; gap:0.6rem;">
            <span class="btn-text">Send Reply</span> <i class="ph-bold ph-paper-plane-tilt"></i>
        </button>
    </form>
</div>
