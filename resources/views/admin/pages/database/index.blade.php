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
            color: var(--header-text,var(--text-heading))
        }

        .page-sub {
            font-size: 13px;
            color: var(--text-muted)
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 20px
        }

        .stat-card {
            background: var(--card-bg,#fff);
            border-radius: var(--card-radius,12px);
            padding: 18px;
            border: 1px solid var(--border-color,var(--border-color));
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
            border-color: var(--hover-border)
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: var(--card-radius,12px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px
        }

        .stat-icon.blue {
            background: var(--c-secondary-light);
            color: var(--c-secondary)
        }

        .stat-icon.green {
            background: var(--c-success-light);
            color: var(--c-success)
        }

        .stat-icon.purple {
            background: var(--c-secondary-light);
            color: var(--c-secondary,var(--c-secondary))
        }

        .stat-val {
            font-size: 22px;
            font-weight: 700;
            color: var(--header-text,var(--text-heading));
            line-height: 1.2;
            margin-bottom: 2px
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500
        }

        .card {
            background: var(--card-bg,#fff);
            border-radius: var(--card-radius,12px);
            border: 1px solid var(--border-color,var(--border-color));
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02)
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color,var(--border-color));
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--header-text,var(--text-heading))
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
            color: var(--text-muted);
            border: 1px solid var(--border-color,var(--border-color));
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s
        }

        .nav-pill:hover {
            background: var(--table-header-bg,var(--table-header-bg));
            color: var(--header-text,var(--text-heading));
            border-color: var(--hover-border)
        }

        .nav-pill.active {
            background: var(--c-primary,var(--c-danger));
            color: #fff;
            border-color: var(--c-secondary,var(--c-secondary));
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
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            background: var(--table-header-bg,var(--table-header-bg));
            border-bottom: 1px solid var(--border-color,var(--border-color))
        }

        td {
            padding: 12px 20px;
            font-size: 13px;
            color: var(--text-body);
            border-bottom: 1px solid var(--border-light,var(--border-light));
            transition: background 0.2s
        }

        tr:hover td {
            background: var(--table-header-bg,var(--table-header-bg));
            cursor: default
        }

        .tname {
            font-weight: 600;
            color: var(--c-secondary,var(--c-secondary));
            text-decoration: none;
            transition: color 0.2s
        }

        .tname:hover {
            color: var(--c-secondary)
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
            background: var(--card-bg,#fff);
            color: var(--c-secondary);
            border-color: var(--c-secondary-border)
        }

        .btn-blue:hover {
            background: var(--c-secondary-light);
            color: var(--c-secondary,var(--c-secondary));
            border-color: var(--c-secondary-border)
        }

        .btn-red {
            background: var(--card-bg,#fff);
            color: var(--c-secondary);
            border-color: var(--c-secondary-border)
        }

        .btn-red:hover {
            background: var(--c-secondary-light);
            color: var(--c-secondary,var(--c-secondary));
            border-color: var(--c-danger-border)
        }

        .btn-primary {
            background: var(--c-primary,var(--c-danger));
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
            background: var(--c-primary-hover);
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.25);
            transform: translateY(-1px)
        }

        .search-box {
            padding: 10px 16px;
            border: 1px solid var(--border-color,var(--border-color));
            border-radius: 8px;
            font-size: 13px;
            width: 280px;
            transition: all 0.2s;
            background: var(--table-header-bg,var(--table-header-bg))
        }

        .search-box:focus {
            outline: none;
            border-color: var(--c-secondary,var(--c-secondary));
            background: var(--card-bg,#fff);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1)
        }
    </style>
@endpush
@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-database" style="color:var(--c-secondary,var(--c-secondary))"></i> Database Manager</h1>
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
                        <td style="font-size:11px;color:var(--text-faint)">{{ $t['collation'] }}</td>
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
            style="position:fixed;bottom:24px;right:24px;background:var(--c-success);color:#fff;padding:12px 20px;border-radius:8px;font-size:13px;z-index:10000">
            <i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    <script>setTimeout(() => document.getElementById('toast')?.remove(), 4000)</script>@endif
@endsection
@push('scripts')
    <script>
        function filterTables() { const v = document.getElementById('tableSearch').value.toLowerCase(); document.querySelectorAll('#tableList tbody tr').forEach(r => { r.style.display = r.textContent.toLowerCase().includes(v) ? '' : 'none' }) }
        function toggleAll() { const c = document.getElementById('checkAll').checked; document.querySelectorAll('.tbl-check').forEach(cb => cb.checked = c) }
    </script>
@endpush