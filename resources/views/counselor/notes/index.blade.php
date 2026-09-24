@extends('layouts.app')

@push('styles')
<style>
    .note-card {
        background: var(--surface-solid);
        border: 1px solid var(--border);
        border-radius: 28px;
        overflow: hidden;
        box-shadow: var(--shadow-md);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .note-card:hover {
        border-color: var(--primary-light);
        box-shadow: var(--shadow-lg);
    }
    
    .msg-bubble-counselor {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: #ffffff;
        border-radius: 20px 20px 4px 20px;
        box-shadow: 0 8px 20px var(--primary-glow);
    }
    
    .msg-bubble-student {
        background: var(--surface-2);
        border: 1px solid var(--border);
        color: var(--text);
        border-radius: 20px 20px 20px 4px;
    }

    .status-select {
        background: var(--surface-2);
        border: 1.5px solid var(--border);
        color: var(--text);
        border-radius: 12px;
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        outline: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .status-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-glow);
    }
</style>
@endpush

@section('content')
<div class="container" style="max-width: 1280px; margin: 0 auto; padding: 2rem 1.5rem 4rem;">
    
    <!-- Header -->
    <header class="staggered" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3.5rem; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="font-weight: 800; color: var(--primary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="ph-bold ph-note-pencil" style="font-size: 1.1rem;"></i> Anonymous Transmissions
            </div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 2.75rem; font-weight: 900; color: var(--text); letter-spacing: -0.04em; margin: 0;">Clinical Quick Notes</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500; margin-top: 0.5rem;">Managing direct student inquiries and clinical outreach responses.</p>
        </div>
        <div>
            <div style="background: var(--surface-solid); border: 1px solid var(--border); padding: 1rem 1.5rem; border-radius: 20px; display: flex; align-items: center; gap: 1rem; box-shadow: var(--shadow-sm);">
                <div style="width: 42px; height: 42px; border-radius: 12px; background: var(--primary-glow); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="ph-bold ph-shield-check"></i>
                </div>
                <div>
                    <div style="font-size: 0.7rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.1em;">Security Protocol</div>
                    <div style="font-size: 0.95rem; font-weight: 900; color: var(--text);">Clinical Oversight</div>
                </div>
            </div>
        </div>
    </header>

    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 2.5rem;" class="staggered">
        
        <!-- Dashboard / Feed -->
        <div id="notesContainer" style="display: flex; flex-direction: column; gap: 2rem;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; color: var(--text-dim);">Active Sequences</span>
                <div style="height: 1px; flex: 1; background: var(--border);"></div>
            </div>

            @forelse($notes as $note)
                <div class="note-card">
                    <!-- Identity Header -->
                    <div style="padding: 1.75rem 2rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: var(--surface-2); flex-wrap: wrap; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 46px; height: 46px; border-radius: 14px; background: var(--surface-solid); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.25rem;">
                                <i class="ph-bold ph-student"></i>
                            </div>
                            <div>
                                <div style="font-weight: 900; color: var(--text); font-size: 1.05rem;">{{ $note->student->full_name }}</div>
                                <div style="font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.1rem;">
                                    Sequence #{{ str_pad($note->note_id, 4, '0', STR_PAD_LEFT) }}
                                </div>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-dim);">{{ $note->created_at->diffForHumans() }}</span>
                            <form action="{{ route('counselor.notes.status', $note) }}" method="POST" class="status-form">
                                @csrf
                                <select name="status" class="status-select">
                                    <option value="new" {{ $note->status === 'new' ? 'selected' : '' }}>New Inquiry</option>
                                    <option value="read" {{ $note->status === 'read' ? 'selected' : '' }}>Under Review</option>
                                    <option value="replied" {{ $note->status === 'replied' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ $note->status === 'closed' ? 'selected' : '' }}>Archive Sequence</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <!-- Conversation -->
                    <div style="padding: 2rem; display: flex; flex-direction: column; gap: 1.5rem; background: var(--surface-solid);">
                        @foreach($note->messages as $msg)
                            <div style="display: flex; flex-direction: column; align-items: {{ $msg->sender_type === 'counselor' ? 'flex-end' : 'flex-start' }};">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem; flex-direction: {{ $msg->sender_type === 'counselor' ? 'row-reverse' : 'row' }};">
                                    <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: {{ $msg->sender_type === 'counselor' ? 'var(--primary)' : 'var(--text-muted)' }};">
                                        {{ $msg->sender_type === 'counselor' ? 'Counselor Review' : 'Student Entry' }}
                                    </span>
                                    <span style="font-size: 0.65rem; font-weight: 600; color: var(--text-dim);">&bull; {{ $msg->created_at->format('M d, g:i A') }}</span>
                                </div>
                                <div class="{{ $msg->sender_type === 'counselor' ? 'msg-bubble-counselor' : 'msg-bubble-student' }}" style="max-width: 82%; padding: 1.25rem 1.5rem; font-size: 0.95rem; line-height: 1.6; font-weight: 500;">
                                    {!! nl2br(e($msg->message_text)) !!}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Reply Area -->
                    @if($note->status !== 'closed')
                        <div style="padding: 1.75rem 2rem; border-top: 1px solid var(--border); background: var(--surface-2);">
                            <form action="{{ route('counselor.notes.reply', $note) }}" method="POST" class="reply-form">
                                @csrf
                                <div style="position: relative;">
                                    <textarea name="message" required placeholder="Formulate clinical response protocol..." style="width: 100%; padding: 1.25rem 1.5rem; border-radius: 18px; border: 1.5px solid var(--border); background: var(--surface-solid); color: var(--text); outline: none; font-size: 0.95rem; font-weight: 500; min-height: 120px; resize: none; transition: all 0.2s;" onfocus="this.style.borderColor='var(--primary)';" onblur="this.style.borderColor='var(--border)';"></textarea>
                                    <button type="submit" class="btn-primary" style="position: absolute; bottom: 1rem; right: 1rem; padding: 0.65rem 1.5rem; border-radius: 12px; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.5rem;">
                                        <span>Transmit Reply</span>
                                        <i class="ph-bold ph-paper-plane-right"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div style="padding: 6rem 2rem; text-align: center; background: var(--surface-solid); border: 2px dashed var(--border); border-radius: 36px;">
                    <div style="width: 72px; height: 72px; border-radius: 24px; background: var(--surface-2); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2rem; color: var(--text-dim);">
                        <i class="ph ph-tray"></i>
                    </div>
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 900; color: var(--text); margin-bottom: 0.5rem;">System Idle</h3>
                    <p style="color: var(--text-muted); font-weight: 500; font-size: 0.95rem; margin: 0;">No active anonymous transmission sequences detected.</p>
                </div>
            @endforelse
        </div>

        <!-- Sidebar Info -->
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <div id="metricsContainer" style="display: flex; flex-direction: column; gap: 2rem;">
                <!-- Clinical Protocol Card -->
                <div style="background: var(--surface-solid); border: 1px solid var(--border); padding: 2rem; border-radius: 28px; box-shadow: var(--shadow-sm); position: relative; overflow: hidden;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(99,102,241,0.1); color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 1.25rem;">
                        <i class="ph-bold ph-info"></i>
                    </div>
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.2rem; font-weight: 900; color: var(--text); margin-bottom: 0.75rem;">Clinical Protocol</h3>
                    <p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.6; font-weight: 500; margin-bottom: 1.5rem; font-style: italic;">
                        "Quick Notes allow students to provide clinical context outside of sessions. Ensure responses are professional, empathetic, and documented for administrative responsibility."
                    </p>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <div style="width: 8px; height: 8px; border-radius: 50%; background: #6366f1;"></div>
                            <span style="font-size: 0.72rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Response SLA: 24 Hours</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary);"></div>
                            <span style="font-size: 0.72rem; font-weight: 800; color: var(--text-dim); text-transform: uppercase;">Direct Clinical Oversight</span>
                        </div>
                    </div>
                </div>

                <!-- Registry Metrics -->
                <div style="background: var(--surface-solid); border: 1px solid var(--border); padding: 2rem; border-radius: 28px; box-shadow: var(--shadow-sm);">
                    <h3 style="font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.15em; color: var(--text-dim); margin-bottom: 1.5rem;">Registry Metrics</h3>
                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Total Sequenced</span>
                            <span style="font-size: 1.25rem; font-weight: 900; color: var(--text);">{{ $notes->count() }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Unprocessed (New)</span>
                            <span style="font-size: 1.25rem; font-weight: 900; color: var(--primary);">{{ $notes->where('status', 'new')->count() }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Historical Archive</span>
                            <span style="font-size: 1.25rem; font-weight: 900; color: var(--text-dim);">{{ $notes->where('status', 'closed')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const refreshUI = async () => {
        const res = await fetch(window.location.href);
        const html = await res.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        const newFeed = doc.querySelector('#notesContainer');
        const newMetrics = doc.querySelector('#metricsContainer');
        
        if (newFeed) document.querySelector('#notesContainer').innerHTML = newFeed.innerHTML;
        if (newMetrics) document.querySelector('#metricsContainer').innerHTML = newMetrics.innerHTML;
    };

    // Status Change
    document.addEventListener('change', async function(e) {
        if (e.target.closest('.status-form')) {
            const form = e.target.closest('.status-form');
            const fd = new FormData(form);
            
            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await res.json();
                if (data.success) {
                    App.toast({ type: 'success', title: 'Status Updated', message: data.message });
                    await refreshUI();
                }
            } catch (err) {
                App.toast({ type: 'error', title: 'Error', message: 'Failed to update status.' });
            }
        }
    });

    // Reply Submission
    document.addEventListener('submit', async function(e) {
        if (e.target.classList.contains('reply-form')) {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            const originalHtml = btn.innerHTML;
            const fd = new FormData(form);

            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Transmitting...';

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await res.json();
                if (data.success) {
                    App.toast({ type: 'success', title: 'Sent', message: data.message });
                    await refreshUI();
                } else {
                    App.toast({ type: 'error', title: 'Error', message: data.message || 'Failed to send reply.' });
                }
            } catch (err) {
                App.toast({ type: 'error', title: 'Error', message: 'Connection failed.' });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        if (window.gsap) {
            gsap.from('.staggered', { y: 30, opacity: 0, duration: 0.8, stagger: 0.1, ease: 'expo.out', clearProps: 'all' });
        }
    });
})();
</script>
@endpush
@endsection

