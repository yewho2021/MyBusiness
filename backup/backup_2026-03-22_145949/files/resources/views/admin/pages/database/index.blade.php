@extends('admin.layouts.app')
@section('title', 'Database Manager')
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

        .page-sub {
            font-size: 13px;
            color: #64748b
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 20px
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 18px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            transition: all 0.2s;
            cursor: default
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px
        }

        .stat-icon.blue {
            background: #eff6ff;
            color: #3b82f6
        }

        .stat-icon.green {
            background: #f0fdf4;
            color: #22c55e
        }

        .stat-icon.purple {
            background: #eff6ff;
            color: #2563eb
        }

        .stat-val {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
            margin-bottom: 2px
        }

        .stat-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 500
        }

        .card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02)
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b
        }

        .nav-pills {
            display: flex;
            gap: 6px;
            flex-wrap: wrap
        }

        .nav-pill {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            color: #64748b;
            border: 1px solid #e2e8f0;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s
        }

        .nav-pill:hover {
            background: #f8fafc;
            color: #1e293b;
            border-color: #cbd5e1
        }

        .nav-pill.active {
            background: #dc2626;
            color: #fff;
            border-color: #2563eb;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2)
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th {
            text-align: left;
            padding: 12px 20px;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .5px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0
        }

        td {
            padding: 12px 20px;
            font-size: 13px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s
        }

        tr:hover td {
            background: #f8fafc;
            cursor: default
        }

        .tname {
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
            transition: color 0.2s
        }

        .tname:hover {
            color: #1d4ed8
        }

        .btn-xs {
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s
        }

        .btn-blue {
            background: #fff;
            color: #3b82f6;
            border-color: #bfdbfe
        }

        .btn-blue:hover {
            background: #eff6ff;
            color: #2563eb;
            border-color: #93c5fd
        }

        .btn-red {
            background: #fff;
            color: #3b82f6;
            border-color: #bfdbfe
        }

        .btn-red:hover {
            background: #eff6ff;
            color: #2563eb;
            border-color: #fca5a5
        }

        .btn-primary {
            background: #dc2626;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2)
        }

        .btn-primary:hover {
            background: #b91c1c;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.25);
            transform: translateY(-1px)
        }

        .search-box {
            padding: 10px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
            width: 280px;
            transition: all 0.2s;
            background: #f8fafc
        }

        .search-box:focus {
            outline: none;
            border-color: #2563eb;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1)
        }
    </style>
@endpush
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-database" style="color:#2563eb"></i> Database Manager</h1>
            <p class="page-sub">Database: <strong>{{ $dbName }}</strong></p>
        </div>
        <div class="nav-pills">
            <a href="{{ route('admin.database.index') }}" class="nav-pill active"><i class="fas fa-table"></i> Tables</a>
            <a href="{{ route('admin.database.query') }}" class="nav-pill"><i class="fas fa-terminal"></i> SQL Query</a>
            <a href="{{ route('admin.database.export') }}" class="nav-pill"><i class="fas fa-download"></i> Export</a>
            <a href="{{ route('admin.database.import') }}" class="nav-pill"><i class="fas fa-upload"></i> Import</a>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-table"></i></div>
            <div>
                <div class="stat-val">{{ count($tableList) }}</div>
                <div class="stat-label">Tables</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-hdd"></i></div>
            <div>
                <div class="stat-val">
                    {{ $totalSize >= 1048576 ? number_format($totalSize / 1048576, 1) . ' MB' : number_format($totalSize / 1024, 1) . ' KB' }}
                </div>
                <div class="stat-label">Database Size</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-server"></i></div>
            <div>
                <div class="stat-val">{{ config('database.connections.mysql.host') }}</div>
                <div class="stat-label">Server</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">All Tables</span>
            <input type="text" class="search-box" id="tableSearch" placeholder="Search tables..." onkeyup="filterTables()">
        </div>
        <table id="tableList">
            <thead>
                <tr>
                    <th><input type="checkbox" id="checkAll" onclick="toggleAll()"></th>
                    <th>Table Name</th>
                    <th>Engine</th>
                    <th>Rows</th>
                    <th>Size</th>
                    <th>Collation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tableList as $t)
                    <tr>
                        <td><input type="checkbox" class="tbl-check" value="{{ $t['name'] }}"></td>
                        <td><a href="{{ route('admin.database.table', $t['name']) }}" class="tname">{{ $t['name'] }}</a></td>
                        <td>{{ $t['engine'] }}</td>
                        <td>{{ number_format($t['rows']) }}</td>
                        <td>{{ $t['size'] >= 1048576 ? number_format($t['size'] / 1048576, 2) . ' MB' : number_format($t['size'] / 1024, 1) . ' KB' }}
                        </td>
                        <td style="font-size:11px;color:#94a3b8">{{ $t['collation'] }}</td>
                        <td>
                            <a href="{{ route('admin.database.table', $t['name']) }}" class="btn-xs btn-blue"><i
                                    class="fas fa-eye"></i> View</a>
                            <form method="POST" action="{{ route('admin.database.truncate', $t['name']) }}"
                                style="display:inline"
                                onsubmit="return confirm('TRUNCATE `{{ $t['name'] }}`? All data will be deleted!')">@csrf
                                <button class="btn-xs btn-red"><i class="fas fa-eraser"></i></button></form>
                            <form method="POST" action="{{ route('admin.database.drop', $t['name']) }}" style="display:inline"
                                onsubmit="return confirm('DROP `{{ $t['name'] }}`? This cannot be undone!')">@csrf
                                @method('DELETE')<button class="btn-xs btn-red"><i class="fas fa-trash"></i></button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(session('success'))
        <div id="toast"
            style="position:fixed;bottom:24px;right:24px;background:#22c55e;color:#fff;padding:12px 20px;border-radius:8px;font-size:13px;z-index:10000">
            <i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    <script>setTimeout(() => document.getElementById('toast')?.remove(), 4000)</script>@endif
@endsection
@push('scripts')
    <script>
        function filterTables() { const v = document.getElementById('tableSearch').value.toLowerCase(); document.querySelectorAll('#tableList tbody tr').forEach(r => { r.style.display = r.textContent.toLowerCase().includes(v) ? '' : 'none' }) }
        function toggleAll() { const c = document.getElementById('checkAll').checked; document.querySelectorAll('.tbl-check').forEach(cb => cb.checked = c) }
    </script>
@endpush