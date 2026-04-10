@extends('layouts.app')

@section('content')
<div class="p-8 max-w-7xl mx-auto">
    <!-- Header -->
    <header class="mb-12 lg:flex justify-between items-end">
        <div>
            <h1 class="text-5xl font-black tracking-tighter text-white mb-4 italic uppercase">Dialog Vault</h1>
            <p class="text-gray-400 text-lg">Managing anonymous student inquiries and clinical outreach.</p>
        </div>
        <div class="mt-8 lg:mt-0">
            <div class="bg-blue-600/10 border border-blue-500/20 px-8 py-4 rounded-[2rem] flex items-center gap-4 shadow-lg shadow-blue-500/5">
                <i class="ph ph-fingerprint text-blue-400 text-2xl"></i>
                <div>
                    <p class="text-[10px] font-black italic uppercase tracking-widest text-blue-400">Security Status</p>
                    <p class="text-sm font-bold text-white">Identity Masking Active</p>
                </div>
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Dashboard / Feed -->
        <div class="lg:col-span-2 space-y-8">
            <div class="flex items-center gap-4 mb-2">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-500">Active Sequences</span>
                <div class="h-px flex-1 bg-white/5"></div>
            </div>

            @forelse($notes as $note)
                <div class="bg-[#1a1a1a] border border-white/10 rounded-[3rem] overflow-hidden shadow-2xl transition-all hover:border-blue-500/30">
                    <!-- Identity Header -->
                    <div class="px-10 py-8 border-b border-white/5 flex justify-between items-center bg-white/5">
                        <div class="flex items-center gap-6">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600/20 to-indigo-600/20 border border-white/10 flex items-center justify-center">
                                <i class="ph ph-student text-blue-400 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-white uppercase tracking-widest mb-1">Identity Vector: Anonymous</p>
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest italic">Protocol Path #{{ str_pad($note->note_id, 4, '0', STR_PAD_LEFT) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-[10px] font-bold text-gray-600 mr-4">{{ $note->created_at->diffForHumans() }}</span>
                            <form action="{{ route('counselor.notes.status', $note) }}" method="POST">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-[10px] font-black uppercase tracking-widest text-gray-400 focus:ring-0 outline-none cursor-pointer hover:bg-white/10 transition-all">
                                    <option value="new" {{ $note->status === 'new' ? 'selected' : '' }}>New Inquiry</option>
                                    <option value="read" {{ $note->status === 'read' ? 'selected' : '' }}>Under Review</option>
                                    <option value="replied" {{ $note->status === 'replied' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ $note->status === 'closed' ? 'selected' : '' }}>Archive Sequence</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <!-- Conversation -->
                    <div class="p-10 space-y-10 bg-white/[0.02]">
                        @foreach($note->messages as $msg)
                            <div class="flex flex-col {{ $msg->sender_type === 'counselor' ? 'items-end' : 'items-start' }}">
                                <div class="flex items-center gap-3 mb-4 {{ $msg->sender_type === 'counselor' ? 'flex-row-reverse' : '' }}">
                                    <span class="text-[9px] font-black uppercase tracking-[0.2em] {{ $msg->sender_type === 'counselor' ? 'text-blue-400' : 'text-gray-500' }}">
                                        {{ $msg->sender_type === 'counselor' ? 'Counselor Branch' : 'Origin Point' }}
                                    </span>
                                    <span class="text-[8px] font-bold text-gray-650 italic">{{ $msg->created_at->format('M d, g:i A') }}</span>
                                </div>
                                <div class="max-w-[80%] px-8 py-6 rounded-[2rem] text-sm leading-relaxed shadow-xl {{ $msg->sender_type === 'counselor' ? 'bg-blue-600 text-white rounded-tr-none shadow-blue-600/10' : 'bg-[#222222] border border-white/10 text-gray-200 rounded-tl-none' }}">
                                    {!! nl2br(e($msg->message_text)) !!}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Reply Area -->
                    @if($note->status !== 'closed')
                        <div class="px-10 py-10 border-t border-white/5 bg-[#1a1a1a]">
                            <form action="{{ route('counselor.notes.reply', $note) }}" method="POST">
                                @csrf
                                <div class="relative group">
                                    <textarea name="message" required placeholder="Formulate clinical response protocol..." class="w-full bg-white/5 border border-white/10 rounded-[2rem] px-8 py-6 text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all h-32 resize-none text-sm placeholder:italic placeholder:text-gray-600"></textarea>
                                    <button type="submit" class="absolute bottom-6 right-6 bg-blue-600 hover:bg-blue-500 text-white font-black px-10 py-4 rounded-2xl shadow-xl shadow-blue-600/20 transition-all active:scale-95 flex items-center gap-3 uppercase text-[10px] tracking-widest">
                                        Transmit Reply
                                        <i class="ph ph-paper-plane-right-bold"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-40 text-center bg-white/5 border-2 border-dashed border-white/10 rounded-[4rem]">
                    <div class="w-24 h-24 bg-white/5 rounded-full mx-auto mb-8 flex items-center justify-center border border-white/5">
                        <i class="ph ph-empty text-gray-700 text-5xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-500 tracking-tight">System Idle</h3>
                    <p class="text-gray-600 font-medium">No active anonymous transmission sequences detected.</p>
                </div>
            @endforelse
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-8">
            <div class="bg-indigo-600/10 border border-indigo-500/20 p-10 rounded-[3rem] backdrop-blur-xl">
                <i class="ph ph-info text-indigo-400 text-3xl mb-6"></i>
                <h3 class="text-xl font-bold mb-4">Clinical Protocol</h3>
                <p class="text-gray-400 leading-relaxed text-sm mb-6 italic">"Anonymous support channels empower students who may hesitate to seek traditional care. Ensure responses are empathetic, professional, and prioritize high-risk detection."</p>
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase">Response SLA: 24 Hours</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                        <p class="text-[10px] font-bold text-gray-500 uppercase">Masked Identity Verification</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/5 border border-white/10 p-10 rounded-[3rem]">
                <h3 class="text-xs font-black uppercase mb-8 tracking-[0.2em] text-gray-500">Registry Metrics</h3>
                <div class="space-y-6">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-400 italic">Total Sequenced</span>
                        <span class="text-lg font-black text-white">{{ $notes->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-400 italic">Unprocessed (New)</span>
                        <span class="text-lg font-black text-blue-400">{{ $notes->where('status', 'new')->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-400 italic">Historical Archive</span>
                        <span class="text-lg font-black text-gray-600">{{ $notes->where('status', 'closed')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
