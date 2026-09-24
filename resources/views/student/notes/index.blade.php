@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1100px; padding-top: 1.5rem; padding-bottom: 3rem;">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem;">
        <div>
            <div style="font-weight: 600; color: var(--primary); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem;">Clinical Support</div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem;">Clinical Quick Notes</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; font-weight: 400;">Securely share clinical context or concerns with your counselor.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <div style="padding: 0.65rem 1.25rem; border-radius: var(--radius-sm); background: var(--surface-2); color: var(--primary); font-weight: 600; font-size: 0.82rem; display: flex; align-items: center; gap: 0.5rem; border: 1px solid var(--border);">
                🔒 Encrypted & Confidential
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 380px 1fr; gap: 2.5rem; align-items: start;">
        
        <!-- New Note Form -->
        <div style="background: var(--surface-solid); border-radius: var(--radius); padding: 2rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); position: sticky; top: 80px;">
            <div style="font-size: 0.7rem; font-weight: 800; color: #ef4444; background: rgba(239, 68, 68, 0.08); padding: 0.75rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 3px solid #ef4444; line-height: 1.4;">
                ⚠️ <b>CLINICAL NOTE:</b> Information shared will be reviewed by the guidance staff for patient oversight under total confidentiality.
            </div>
            
            <div style="width: 44px; height: 44px; border-radius: var(--radius-sm); background: var(--primary-glow); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 1.25rem;">✍️</div>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text);">New Entry</h2>
            <p style="color: var(--text-muted); font-weight: 400; margin-bottom: 1.75rem; line-height: 1.6; font-size: 0.88rem;">Speak directly to clinical experts in a secure, confidential environment.</p>

            <form method="POST" action="{{ route('student.notes.store') }}" id="noteForm">
                @csrf
                <div style="margin-bottom: 1.25rem;">
                    <textarea name="message" placeholder="Share your clinical context or concerns..." required style="width: 100%; padding: 1.25rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-family: inherit; font-size: 0.95rem; height: 180px; resize: none; background: var(--surface-2); transition: var(--transition); line-height: 1.6; color: var(--text);"></textarea>
                </div>
                <button type="submit" id="noteSubmitBtn" style="width: 100%; background: var(--primary); color: white; border: none; padding: 0.85rem; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2); transition: var(--transition); font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <span id="noteBtnText">Send Clinical Note →</span>
                </button>
            </form>
        </div>

        <!-- Notes History -->
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: var(--text); margin: 0;">Conversations</h2>
                <span style="padding: 0.2rem 0.6rem; border-radius: 20px; background: var(--surface-2); font-size: 0.72rem; font-weight: 600; color: var(--text-dim);">{{ $notes->count() }}</span>
            </div>
            
            <div id="notesListContainer">
                @if ($notes->isEmpty())
                    <div class="empty-state" id="emptyState" style="text-align: center; padding: 3rem 0;">
                    <span style="font-size: 3rem;">💭</span>
                    <h3 style="font-family: 'Outfit', sans-serif; font-weight: 700; color: var(--text-muted); margin-bottom: 0.5rem;">No notes yet</h3>
                    <p style="color: var(--text-dim); font-size: 0.88rem;">Write your first note to start a secure dialog with your counselor.</p>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                    @foreach ($notes as $note)
                        <div class="history-card" style="background: var(--surface-solid); border-radius: var(--radius); padding: 1.75rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); transition: var(--transition);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <span style="font-weight: 600; font-size: 0.82rem; color: var(--text-dim);">Note #{{ str_pad($note->note_id, 3, '0', STR_PAD_LEFT) }}</span>
                                    <span style="font-size: 0.78rem; color: var(--text-dim);">· {{ $note->created_at->format('M d, Y') }}</span>
                                </div>
                                <span class="status-badge" style="padding: 0.3rem 0.75rem; border-radius: 20px; font-weight: 600; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em; background: var(--surface-2); color: {{ $note->status === 'replied' ? 'var(--primary)' : 'var(--text-muted)' }}; border: 1px solid var(--border);">
                                    {{ $note->status === 'replied' ? 'Replied' : 'Pending' }}
                                </span>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem;">
                                @foreach ($note->messages as $msg)
                                    <div style="max-width: 90%; align-self: {{ $msg->sender_type === 'student' ? 'flex-end' : 'flex-start' }};">
                                        <div style="font-size: 0.68rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.35rem;">
                                            {{ $msg->sender_type === 'student' ? 'You' : 'Counselor' }}
                                            · {{ $msg->created_at->format('g:i A') }}
                                        </div>
                                        <div style="padding: 1rem 1.25rem; border-radius: {{ $msg->sender_type === 'student' ? 'var(--radius) var(--radius) 4px var(--radius)' : 'var(--radius) var(--radius) var(--radius) 4px' }}; background: {{ $msg->sender_type === 'student' ? 'var(--primary)' : 'var(--surface-2)' }}; color: {{ $msg->sender_type === 'student' ? 'white' : 'var(--text)' }}; font-weight: 400; line-height: 1.6; font-size: 0.9rem; border: {{ $msg->sender_type === 'student' ? 'none' : '1px solid var(--border)' }};">
                                            {!! nl2br(e($msg->message_text)) !!}
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if ($note->status !== 'closed')
                                <div style="border-top: 1px solid var(--border); padding-top: 1.25rem;">
                                    <form method="POST" action="{{ route('student.notes.reply', $note->note_id) }}" class="reply-form" style="display: flex; gap: 0.75rem; align-items: center;">
                                        @csrf
                                        <input type="text" name="reply_text" placeholder="Type a follow-up..." required style="flex: 1; padding: 0.75rem 1.25rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border); font-weight: 400; background: var(--surface-solid); color: var(--text); font-size: 0.9rem; transition: var(--transition); font-family: inherit;">
                                        <button type="submit" style="background: var(--primary); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.15); transition: var(--transition); font-size: 0.85rem;">Send</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <p>© {{ date('Y') }} PSU Mental Health Portal</p>
</footer>
@push('scripts')
<script>
// Main Note Form
document.getElementById('noteForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const btn = document.getElementById('noteSubmitBtn');
    const text = document.getElementById('noteBtnText');
    const formData = new FormData(form);

    btn.disabled = true;
    text.textContent = 'Sending...';

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();

        if (data.success) {
            App.toast({ type: 'success', title: 'Note Sent', message: data.message });
            form.reset();
            
            // Reload list container or append - simpler to reload the specific list container via AJAX if possible, 
            // but here we will manually append to keep it "full AJAX" as requested.
            // For simplicity and to avoid complex HTML generation in JS, we can also use a small trick:
            // Fetch the current page HTML and extract the notes list part.
            const listRes = await fetch(window.location.href);
            const listHtml = await listRes.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(listHtml, 'text/html');
            const newList = doc.querySelector('#notesListContainer');
            if (newList) {
                document.querySelector('#notesListContainer').innerHTML = newList.innerHTML;
            }
            
            // Clear empty state if it was there
            const emptyState = document.getElementById('emptyState');
            if (emptyState) emptyState.style.display = 'none';
        } else {
            App.toast({ type: 'error', title: 'Error', message: data.error || 'Failed to send note.' });
        }
    } catch (error) {
        App.toast({ type: 'error', title: 'Connection Error', message: 'Please try again.' });
    } finally {
        btn.disabled = false;
        text.textContent = 'Send Clinical Note →';
    }
});

// Reply Forms (Delegated)
document.addEventListener('submit', async function(e) {
    if (e.target.classList.contains('reply-form')) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        const formData = new FormData(form);

        btn.disabled = true;
        btn.textContent = '...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            const data = await response.json();

            if (data.success) {
                App.toast({ type: 'success', title: 'Reply Sent', message: data.message });
                form.reset();
                
                // Fetch updated notes list
                const listRes = await fetch(window.location.href);
                const listHtml = await listRes.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(listHtml, 'text/html');
                const newList = doc.querySelector('#notesListContainer');
                if (newList) {
                    document.querySelector('#notesListContainer').innerHTML = newList.innerHTML;
                }
            } else {
                App.toast({ type: 'error', title: 'Error', message: data.error || 'Failed to send reply.' });
            }
        } catch (error) {
            App.toast({ type: 'error', title: 'Connection Error', message: 'Please try again.' });
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }
});
</script>
@endpush
@endsection
