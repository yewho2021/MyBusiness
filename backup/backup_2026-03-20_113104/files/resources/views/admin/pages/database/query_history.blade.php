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
            color: #1e293b
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
            color: #64748b;
            border: 1px solid #e2e8f0;
            display: inline-flex;
            align-items: center;
            gap: 6px
        }

        .nav-pill:hover {
            background: #f1f5f9;
            color: #374151
        }

        .nav-pill.active {
            background: #4f46e5;
            color: #fff;
            border-color: #4f46e5
        }

        .card {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
            overflow: hidden
        }

        .card-header {
            padding: 14px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
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
            color: #64748b;
            text-transform: uppercase;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0
        }

        td {
            padding: 12px 20px;
            font-size: 14px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9
        }

        tr:hover td {
            background: #fafbfc
        }

        .sql-code {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #4f46e5;
            background: #f5f3ff;
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
            background: #4f46e5;
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
            <h1 class="page-title"><i class="fas fa-history" style="color:#4f46e5"></i> Query Explorer</h1>
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
            <h3 class="card-title"><i class="fas fa-star" style="color:#f59e0b"></i> Bookmarked Queries</h3>
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
                            <td style="color:#94a3b8;font-size:12px">{{ $b->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.database.query', ['sql' => $b->sql_query]) }}"
                                    class="btn-restore">Use</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center;padding:40px;color:#94a3b8">No bookmarks yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookmarks->hasPages())
            <div style="padding:15px;border-top:1px solid #e2e8f0">{{ $bookmarks->links() }}</div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-clock" style="color:#64748b"></i> Recent History</h3>
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
                            <td style="color:#94a3b8;font-size:12px">{{ $h->created_at->format('d M Y H:i:s') }}</td>
                            <td>
                                <a href="{{ route('admin.database.query', ['sql' => $h->sql_query]) }}"
                                    class="btn-restore">Use</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align:center;padding:40px;color:#94a3b8">No history recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($history->hasPages())
            <div style="padding:15px;border-top:1px solid #e2e8f0">{{ $history->links() }}</div>
        @endif
    </div>
@endsection