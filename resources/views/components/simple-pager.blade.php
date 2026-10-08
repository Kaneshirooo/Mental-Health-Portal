@if($paginator->total() > 0)
    <div style="text-align: center; margin-top: 1.75rem; font-size: 0.95rem;">
        @if(!$paginator->onFirstPage())
            <a href="{{ $paginator->previousPageUrl() }}" style="color: #1a73e8; text-decoration: none; margin: 0 0.45rem;">← Prev</a>
        @endif
        @for($i = 1; $i <= $paginator->lastPage(); $i++)
            @if($i === $paginator->currentPage())
                <span style="color: #202124; font-weight: 800; margin: 0 0.45rem;">{{ $i }}</span>
            @else
                <a href="{{ $paginator->url($i) }}" style="color: #1a73e8; text-decoration: none; margin: 0 0.45rem;">{{ $i }}</a>
            @endif
        @endfor
        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" style="color: #1a73e8; text-decoration: none; margin: 0 0.45rem;">Next →</a>
        @endif
        <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-dim, #64748b); margin-top: 0.5rem;">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }} ({{ $paginator->total() }} {{ $label ?? 'records' }})</div>
    </div>
@endif
