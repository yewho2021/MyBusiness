<div class="history-tab-content" style="padding:20px">
    <div style="display:grid;grid-template-columns: 1fr 1fr; gap:20px">
        {{-- Bookmarks --}}
        <div class="card">
            <div class="card-header" style="background:var(--c-warning-light)">
                <span class="card-title"><i class="fas fa-star" style="color:var(--c-warning)"></i> Bookmarked Queries</span>
            </div>
            <div style="max-height:600px;overflow:auto">
                <table class="summary-table">
                    @forelse($bookmarks as $b)
                        <tr class="summary-row">
                            <td style="padding:12px">
                                <div style="font-weight:600;font-size:13px;color:var(--header-text,var(--text-heading))">{{ $b->title }}</div>
                                <code
                                    style="font-size:11px;color:var(--text-muted);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;background:var(--table-header-bg,var(--table-header-bg));padding:2px 4px;border-radius:3px;margin-top:4px">{{ $b->sql_query }}</code>
                            </td>
                            <td style="width:80px;text-align:right;padding:12px">
                                <button class="btn-xs btn-blue"
                                    onclick="DatabaseManager.useQuery(`{{ addslashes($b->sql_query) }}`)">Use</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="padding:40px;text-align:center;color:var(--text-faint)">No bookmarks yet</td>
                        </tr>
                    @endforelse
                </table>
            </div>
        </div>

        {{-- History --}}
        <div class="card">
            <div class="card-header" style="background:var(--table-header-bg,var(--table-header-bg))">
                <span class="card-title"><i class="fas fa-history"></i> Recent Execution History</span>
            </div>
            <div style="max-height:600px;overflow:auto">
                <table class="summary-table">
                    @forelse($recentHistory as $h)
                        <tr class="summary-row">
                            <td style="padding:12px">
                                <code
                                    style="font-size:11px;color:var(--text-body);display:block;background:var(--border-light,var(--border-light));padding:4px 8px;border-radius:4px">{{ $h->sql_query }}</code>
                                <div style="font-size:10px;color:var(--text-faint);margin-top:4px"><i class="far fa-clock"></i>
                                    {{ $h->created_at->diffForHumans() }}</div>
                            </td>
                            <td style="width:80px;text-align:right;padding:12px">
                                <button class="btn-xs btn-blue"
                                    onclick="DatabaseManager.useQuery(`{{ addslashes($h->sql_query) }}`)">Use</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="padding:40px;text-align:center;color:var(--text-faint)">No history recorded</td>
                        </tr>
                    @endforelse
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .summary-table {
        width: 100%;
        border-collapse: collapse;
    }

    .summary-row td {
        border-bottom: 1px solid var(--border-light,var(--border-light));
    }

    .summary-row:hover td {
        background: var(--hover-bg);
    }
</style>