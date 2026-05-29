<div class="history-tab-content" style="padding:20px">
    <div style="display:grid;grid-template-columns: 1fr 1fr; gap:20px">
        {{-- Bookmarks --}}
        <div class="card">
            <div class="card-header" style="background:#fff7ed">
                <span class="card-title"><i class="fas fa-star" style="color:#f59e0b"></i> Bookmarked Queries</span>
            </div>
            <div style="max-height:600px;overflow:auto">
                <table class="summary-table">
                    @forelse($bookmarks as $b)
                        <tr class="summary-row">
                            <td style="padding:12px">
                                <div style="font-weight:600;font-size:13px;color:#1e293b">{{ $b->title }}</div>
                                <code
                                    style="font-size:11px;color:#64748b;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;background:#f8fafc;padding:2px 4px;border-radius:3px;margin-top:4px">{{ $b->sql_query }}</code>
                            </td>
                            <td style="width:80px;text-align:right;padding:12px">
                                <button class="btn-xs btn-blue"
                                    onclick="DatabaseManager.useQuery(`{{ addslashes($b->sql_query) }}`)">Use</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="padding:40px;text-align:center;color:#94a3b8">No bookmarks yet</td>
                        </tr>
                    @endforelse
                </table>
            </div>
        </div>

        {{-- History --}}
        <div class="card">
            <div class="card-header" style="background:#f8fafc">
                <span class="card-title"><i class="fas fa-history"></i> Recent Execution History</span>
            </div>
            <div style="max-height:600px;overflow:auto">
                <table class="summary-table">
                    @forelse($recentHistory as $h)
                        <tr class="summary-row">
                            <td style="padding:12px">
                                <code
                                    style="font-size:11px;color:#334155;display:block;background:#f1f5f9;padding:4px 8px;border-radius:4px">{{ $h->sql_query }}</code>
                                <div style="font-size:10px;color:#94a3b8;margin-top:4px"><i class="far fa-clock"></i>
                                    {{ $h->created_at->diffForHumans() }}</div>
                            </td>
                            <td style="width:80px;text-align:right;padding:12px">
                                <button class="btn-xs btn-blue"
                                    onclick="DatabaseManager.useQuery(`{{ addslashes($h->sql_query) }}`)">Use</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="padding:40px;text-align:center;color:#94a3b8">No history recorded</td>
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
        border-bottom: 1px solid #f1f5f9;
    }

    .summary-row:hover td {
        background: #fafbfc;
    }
</style>