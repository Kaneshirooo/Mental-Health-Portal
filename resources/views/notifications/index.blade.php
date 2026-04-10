@extends('layouts.app')

@section('content')
<div class="p-8 max-w-2xl mx-auto">
    <!-- Header -->
    <header class="mb-12 flex justify-between items-end">
        <div>
            <p class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-500 mb-4 italic">Activity Feed</p>
            <h1 class="text-5xl font-black text-white tracking-tighter italic uppercase">Notifications</h1>
            <p class="text-gray-600 font-medium mt-3">{{ $notifications->count() }} alert{{ $notifications->count() !== 1 ? 's' : '' }} in queue.</p>
        </div>
        @if($notifications->count() > 0)
        <button id="clearBtn" onclick="clearAll()" class="bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 text-red-400 font-black px-8 py-4 rounded-2xl transition-all uppercase tracking-widest text-[9px] flex items-center gap-3">
            <i class="ph ph-trash text-lg"></i>
            Clear Feed
        </button>
        @endif
    </header>

    @if($notifications->isEmpty())
        <div class="glass-card p-20 text-center">
            <div class="w-24 h-24 bg-white/5 rounded-full mx-auto mb-8 flex items-center justify-center border border-white/5">
                <i class="ph ph-bell-slash text-gray-700 text-5xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-600 italic">All Caught Up</h3>
            <p class="text-gray-700 font-medium text-sm mt-2">No pending notifications in the feed.</p>
        </div>
    @else
        <div id="notifList" class="space-y-4">
            @foreach($notifications as $n)
                @php
                    $icons = ['appointment' => 'ph-calendar', 'system' => 'ph-bell', 'note' => 'ph-envelope-simple'];
                    $icon = $icons[$n->type] ?? 'ph-bell';
                    $colors = ['appointment' => 'text-blue-400 bg-blue-400/10', 'system' => 'text-gray-400 bg-white/5', 'note' => 'text-indigo-400 bg-indigo-400/10'];
                    $color = $colors[$n->type] ?? 'text-gray-400 bg-white/5';
                @endphp
                <div class="notif-card glass-card p-6 flex items-start gap-5 {{ !$n->is_read ? 'border-blue-500/20 bg-blue-500/5' : '' }}">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 {{ $color }}">
                        <i class="ph {{ $icon }} text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start gap-4 mb-2">
                            <p class="font-bold text-white text-sm">{{ $n->title }}</p>
                            @if(!$n->is_read)
                                <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-1 shadow-[0_0_8px_rgba(59,130,246,0.6)]"></span>
                            @endif
                        </div>
                        <p class="text-gray-500 text-sm font-medium leading-relaxed mb-3">{{ $n->message }}</p>
                        <p class="text-[9px] font-black text-gray-700 uppercase tracking-widest">{{ $n->created_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
.glass-card {
    background: rgba(255,255,255,0.03);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 2rem;
}
</style>

<script>
async function clearAll() {
    if (!confirm('Clear all notifications?')) return;
    const btn = document.getElementById('clearBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-circle-notch animate-spin text-lg"></i> Clearing...';

    try {
        const res = await fetch('{{ route("notifications.clear") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            document.querySelectorAll('.notif-card').forEach(c => {
                c.style.transition = 'opacity 0.3s, transform 0.3s';
                c.style.opacity = '0';
                c.style.transform = 'translateY(-8px)';
            });
            setTimeout(() => location.reload(), 400);
        }
    } catch(e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="ph ph-trash text-lg"></i> Clear Feed';
    }
}
</script>
@endsection
