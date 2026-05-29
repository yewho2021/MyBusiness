@extends('admin.layouts.app')
@section('title', 'SQL Query History')
@push('styles')
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--header-text,var(--text-heading))
        }

        .nav-pills {
            display: flex;
            gap: 6px;
            flex-wrap: wrap
        }

        .nav-pill {
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            color: var(--text-muted);
            border: 1px solid var(--border-color,var(--border-color));
            display: inline-flex;
            align-items: center;
            gap: 6px
        }

        .nav-pill:hover {
            background: var(--border-light);
            color: var(--text-body)
        }

        .nav-pill.active {
            background: var(--c-primary,var(--c-danger));
            color: #fff;
            border-color: var(--c-secondary,var(--c-secondary))
        }

        .card {
            background: var(--card-bg,#fff);
            border-radius: var(--card-radius,10px);
            border: 1px solid var(--border-color,var(--border-color));
            margin-bottom: 20px;
            overflow: hidden
        }

        .card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border-color,var(--border-color));
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--table-header-bg,var(--table-header-bg))
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--header-text,var(--text-heading));
            display: flex;
            align-items: center;
            gap: 8px
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th {
            text-align: left;
            padding: 12px 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            background: var(--table-header-bg,var(--table-header-bg));
            border-bottom: 1px solid var(--border-color,var(--border-color))
        }

        td {
            padding: 12px 20px;
            font-size: 14px;
            color: var(--text-body);
            border-bottom: 1px solid var(--border-light,var(--border-light))
        }

        tr:hover td {
            background: var(--hover-bg)
        }

        .sql-code {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: var(--c-secondary,var(--c-secondary));
            background: var(--c-purple-light);
            padding: 4px 8px;
            border-radius: 4px;
            display: block;
            max-width: 600px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap
        }

        .btn-restore {
            padding: 4px 10px;
            background: var(--c-primary,var(--c-danger));
            color: #fff;
            border-radius: 4px;
            font-size: 12px;
            text-decoration: none
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-history" style="color:var(--c-secondary,var(--c-secondary))"></i> Query Explorer</h1>
        </div>
        <div class="nav-pills">
            <a href="{{ route('admin.database.index') }}" class="nav-pill"><i class="fas fa-table"></i> Tables</a>
            <a href="{{ route('admin.database.query') }}" class="nav-pill"><i class="fas fa-terminal"></i> SQL Query</a>
            <a href="{{ route('admin.database.history') }}" class="nav-pill active"><i class="fas fa-history"></i> History &
                Bookmarks</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-star" style="color:var(--c-warning)"></i> Bookmarked Queries</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Query</th>
                        <th>Created</th>
                        <th style="width:100px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookmarks as $b)
                        <tr>
                            <td style="font-weight:600">{{ $b->title }}</td>
                            <td><code class="sql-code" title="{{ $b->sql_query }}">{{ $b->sql_query }}</code></td>
                            <td style="color:var(--text-faint);font-size:12px">{{ $b->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.database.query', ['sql' => $b->sql_query]) }}"
                                    class="btn-restore">Use</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center;padding:40px;color:var(--text-faint)">No bookmarks yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookmarks->hasPages())
            <div style="padding:15px;border-top:1px solid var(--border-color,var(--border-color))">{{ $bookmarks->links() }}</div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-clock" style="color:var(--text-muted)"></i> Recent History</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Query</th>
                        <th style="width:200px">Executed At</th>
                        <th style="width:100px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $h)
                        <tr>
                            <td><code class="sql-code" title="{{ $h->sql_query }}">{{ $h->sql_query }}</code></td>
                            <td style="color:var(--text-faint);font-size:12px">{{ $h->created_at->format('d M Y H:i:s') }}</td>
                            <td>
                                <a href="{{ route('admin.database.query', ['sql' => $h->sql_query]) }}"
                                    class="btn-restore">Use</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align:center;padding:40px;color:var(--text-faint)">No history recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($history->hasPages())
            <div style="padding:15px;border-top:1px solid var(--border-color,var(--border-color))">{{ $history->links() }}</div>
        @endif
    </div>
@endsection