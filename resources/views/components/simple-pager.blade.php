@if($paginator->total() > 0)
    <div class="simple-pager-container" style="text-align: center; margin-top: 2rem; display: flex; flex-direction: column; align-items: center; gap: 0.65rem;">
        <nav aria-label="Pagination" style="display: inline-flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; justify-content: center;">
            @if(!$paginator->onFirstPage())
                <a href="{{ $paginator->previousPageUrl() }}" class="pager-nav-btn" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.45rem 0.9rem; border-radius: 12px; font-size: 0.82rem; font-weight: 700; text-decoration: none; background: var(--surface-2); color: var(--text); border: 1px solid var(--border); transition: all 0.2s ease;">
                    <span>← Prev</span>
                </a>
            @endif

            @for($i = 1; $i <= $paginator->lastPage(); $i++)
                @if($i === $paginator->currentPage())
                    <span class="pager-num-active" style="display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 38px; padding: 0 0.5rem; border-radius: 12px; font-size: 0.85rem; font-weight: 800; background: var(--primary); color: #ffffff; box-shadow: 0 3px 10px rgba(16, 185, 129, 0.25);">
                        {{ $i }}
                    </span>
                @else
                    <a href="{{ $paginator->url($i) }}" class="pager-num-link" style="display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 38px; padding: 0 0.5rem; border-radius: 12px; font-size: 0.85rem; font-weight: 700; text-decoration: none; background: var(--surface-solid); color: var(--text); border: 1px solid var(--border); transition: all 0.2s ease;">
                        {{ $i }}
                    </a>
                @endif
            @endfor

            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pager-nav-btn" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.45rem 0.9rem; border-radius: 12px; font-size: 0.82rem; font-weight: 700; text-decoration: none; background: var(--surface-2); color: var(--text); border: 1px solid var(--border); transition: all 0.2s ease;">
                    <span>Next →</span>
                </a>
            @endif
        </nav>
        <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-dim, #64748b);">
            Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }} ({{ $paginator->total() }} {{ $label ?? 'records' }})
        </div>
    </div>
@endif
