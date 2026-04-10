@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6 bg-[#0f172a]">
    <div class="text-center space-y-8 max-w-lg">
        <div class="inline-flex w-24 h-24 bg-red-500/10 border border-red-500/20 rounded-[2rem] items-center justify-center mb-4">
            <i class="ph ph-warning-octagon text-red-500 text-5xl"></i>
        </div>
        <h1 class="text-6xl font-black text-white tracking-tighter">404</h1>
        <h2 class="text-2xl font-bold text-gray-300">Identity Not Found</h2>
        <p class="text-gray-500 leading-relaxed italic">
            "The sequence you are seeking does not exist within the primary institutional directory. Verification protocols have been initiated."
        </p>
        <div class="pt-8">
            <a href="{{ url('/') }}" class="bg-white/10 hover:bg-white/20 text-white px-10 py-4 rounded-2xl font-bold transition-all inline-flex items-center gap-2">
                <i class="ph ph-arrow-left"></i>
                Return to Matrix
            </a>
        </div>
    </div>
</div>
@endsection
