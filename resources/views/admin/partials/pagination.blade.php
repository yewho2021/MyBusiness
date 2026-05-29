{{-- 
    Reusable pagination component.
    Usage: @include('admin.partials.pagination', ['paginator' => $items])
    Optional: 'showInfo' => true (default true) — shows "Showing X to Y of Z"
--}}
@if($paginator->hasPages())
<div class="pagination-wrap">
    @if($showInfo ?? true)
    <div class="pagination-info">
        Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
    </div>
    @endif
    <div class="pagination-links">
        {{-- Previous --}}
        @if($paginator->onFirstPage())
            <span class="pg-btn disabled"><i class="fas fa-chevron-left"></i></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pg-btn"><i class="fas fa-chevron-left"></i></a>
        @endif

        {{-- Page Numbers --}}
        @foreach($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
            @if($page == $paginator->currentPage())
                <span class="pg-btn active">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="pg-btn">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Next --}}
        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pg-btn"><i class="fas fa-chevron-right"></i></a>
        @else
            <span class="pg-btn disabled"><i class="fas fa-chevron-right"></i></span>
        @endif
    </div>
</div>

<style>
.pagination-wrap { padding:18px 22px; display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-light); flex-wrap:wrap; gap:12px; }
.pagination-info { font-size:13px; color:var(--text-muted); }
.pagination-links { display:flex; gap:4px; }
.pg-btn { display:inline-flex; align-items:center; justify-content:center; min-width:36px; height:36px; padding:0 10px; border-radius:8px; font-size:14px; font-weight:500; color:var(--text-body); background:#fff; border:1px solid var(--border-color); text-decoration:none; transition:all .15s; cursor:pointer; }
.pg-btn:hover:not(.disabled):not(.active) { background:var(--table-header-bg); border-color:var(--input-border); }
.pg-btn.active { background:var(--c-danger); color:#fff; border-color:var(--c-danger); }
.pg-btn.disabled { color:var(--input-border); cursor:not-allowed; }
</style>
@endif
