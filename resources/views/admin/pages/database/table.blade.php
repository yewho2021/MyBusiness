@extends('admin.layouts.app')
@section('title', $table)
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

        .breadcrumb {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 4px
        }

        .breadcrumb a {
            color: var(--c-secondary,var(--c-secondary));
            text-decoration: none
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
            flex-wrap: wrap;
            gap: 8px
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--header-text,var(--text-heading))
        }

        .tabs {
            display: flex;
            gap: 2px;
            border-bottom: 1px solid var(--border-color,var(--border-color));
            padding: 0 16px;
            background: var(--table-header-bg,var(--table-header-bg));
        }

        .tab {
            padding: 12px 18px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            text-decoration: none;
            transition: all 0.2s;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
        }

        .tab:hover {
            color: var(--text-body);
            background: var(--border-light);
        }

        .tab.active {
            color: var(--c-secondary,var(--c-secondary));
            border-bottom-color: var(--c-secondary,var(--c-secondary));
            background: var(--card-bg,#fff);
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th {
            text-align: left;
            padding: 10px 16px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            background: var(--table-header-bg,var(--table-header-bg));
            border-bottom: 1px solid var(--border-color,var(--border-color));
            white-space: nowrap
        }

        td {
            padding: 10px 16px;
            font-size: 13px;
            color: var(--text-body);
            border-bottom: 1px solid var(--border-light,var(--border-light));
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            transition: background 0.2s;
        }

        tr:hover td {
            background: var(--table-header-bg,var(--table-header-bg));
        }

        .data-table-wrap {
            overflow-x: auto
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            padding: 20px
        }

        .info-item {
            background: var(--table-header-bg,var(--table-header-bg));
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-light);
        }

        .info-item label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            display: block;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .info-item span {
            font-size: 14px;
            font-weight: 600;
            color: var(--header-text,var(--text-heading))
        }

        .badge-pk {
            background: var(--c-warning-light);
            color: var(--c-warning);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600
        }

        .badge-null {
            background: var(--border-light);
            color: var(--text-faint);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 500;
        }

        .badge-ai {
            background: var(--c-secondary-light);
            color: var(--c-secondary);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 500;
        }

        .btn-xs {
            padding: 4px 10px;
            font-size: 11px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }

        .btn-red {
            background: var(--c-secondary-light);
            color: var(--c-secondary)
        }
        .btn-red:hover {
            background: var(--c-secondary-light);
        }

        .btn-blue {
            background: var(--c-secondary-light);
            color: var(--c-secondary)
        }
        .btn-blue:hover {
            background: var(--c-secondary-light);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--input-border);
            color: var(--text-body);
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px
        }

        .pager {
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: var(--text-muted);
            border-top: 1px solid var(--border-color);
            flex-wrap: wrap;
            gap: 12px;
            background: var(--table-header-bg,var(--table-header-bg));
        }

        .pager a,
        .pager-btn {
            color: var(--c-secondary,var(--c-secondary));
            text-decoration: none;
            padding: 6px 12px;
            border: 1px solid var(--border-color,var(--border-color));
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            background: var(--card-bg,#fff);
            transition: all 0.2s;
        }

        .pager a:hover,
        .pager-btn:hover {
            background: var(--border-light);
            border-color: var(--hover-border);
            color: var(--c-secondary);
        }

        .pager .current {
            background: var(--c-primary,var(--c-danger));
            color: #fff;
            border-color: var(--c-secondary,var(--c-secondary));
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .pager .disabled {
            color: var(--text-faint);
            pointer-events: none;
            background: var(--border-light);
            border-color: var(--border-color);
        }

        .pager-goto {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px
        }

        .pager-goto input {
            width: 50px;
            padding: 4px 6px;
            border: 1px solid var(--border-color,var(--border-color));
            border-radius: 4px;
            font-size: 12px;
            text-align: center
        }

        .pager-goto input:focus {
            outline: none;
            border-color: var(--c-secondary,var(--c-secondary))
        }

        .per-page-select {
            padding: 4px 8px;
            border: 1px solid var(--border-color,var(--border-color));
            border-radius: 4px;
            font-size: 12px;
            color: var(--text-body);
            background: var(--card-bg,#fff);
            cursor: pointer
        }

        .per-page-select:focus {
            outline: none;
            border-color: var(--c-secondary,var(--c-secondary))
        }

        .sql-box {
            background: var(--code-bg);
            color: var(--border-color);
            padding: 16px 20px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
            border-radius: 0 0 10px 10px
        }

        td.null-val {
            color: var(--input-border);
            font-style: italic
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

        /* Inline editing */
        td.editable {
            cursor: pointer;
            position: relative
        }

        td.editable:hover {
            background: var(--c-secondary-light) !important
        }

        td.editing {
            padding: 2px 4px;
            overflow: visible
        }

        td.editing input,
        td.editing textarea {
            width: 100%;
            padding: 6px 8px;
            border: 2px solid var(--c-danger);
            border-radius: 4px;
            font-size: 12px;
            font-family: var(--font-family);
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1)
        }

        td.editing textarea {
            min-height: 60px;
            resize: vertical;
            white-space: pre-wrap
        }

        td.edit-success {
            animation: flashGreen 1s ease
        }

        td.edit-error {
            animation: flashRed 1s ease
        }

        @keyframes flashGreen {
            0% {
                background: var(--c-success-light)
            }

            100% {
                background: transparent
            }
        }

        @keyframes flashRed {
            0% {
                background: var(--c-secondary-light)
            }

            100% {
                background: transparent
            }
        }

        .edit-hint {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--text-heading);
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            z-index: 10000;
            display: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15)
        }
    </style>
@endpush
@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb"><a href="{{ route('admin.database.index') }}">Database</a> &rsaquo; {{ $table }}</div>
            <h1 class="page-title">{{ $table }}</h1>
        </div>
        <div class="nav-pills">
            <a href="{{ route('admin.database.index') }}" class="nav-pill"><i class="fas fa-arrow-left"></i> Tables</a>
            <a href="{{ route('admin.database.query') }}?sql=SELECT * FROM `{{ $table }}` LIMIT 100" class="nav-pill"><i
                    class="fas fa-terminal"></i> Query</a>
            <form method="POST" action="{{ route('admin.database.truncate', $table) }}" style="display:inline"
                onsubmit="return confirm('TRUNCATE all data from `{{ $table }}`?')">@csrf<button class="nav-pill"
                    style="cursor:pointer"><i class="fas fa-eraser"></i> Truncate</button></form>
        </div>
    </div>

    {{-- Table Info --}}
    @if($tableInfo)
        <div class="card">
            <div class="info-grid">
                <div class="info-item"><label>Engine</label><span>{{ $tableInfo->Engine }}</span></div>
                <div class="info-item"><label>Rows</label><span>{{ number_format($totalRows) }}</span></div>
                <div class="info-item">
                    <label>Size</label><span>{{ number_format((($tableInfo->Data_length ?? 0) + ($tableInfo->Index_length ?? 0)) / 1024, 1) }}
                        KB</span></div>
                <div class="info-item"><label>Collation</label><span>{{ $tableInfo->Collation }}</span></div>
                <div class="info-item"><label>Auto Increment</label><span>{{ $tableInfo->Auto_increment ?? '--' }}</span></div>
                <div class="info-item"><label>Created</label><span>{{ $tableInfo->Create_time }}</span></div>
            </div>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="card">
        <div class="tabs">
            <a class="tab active" onclick="showTab('data')">Data ({{ number_format($totalRows) }})</a>
            <a class="tab" onclick="showTab('structure')">Structure ({{ count($columns) }})</a>
            <a class="tab" onclick="showTab('indexes')">Indexes ({{ count($indexes) }})</a>
            <a class="tab" onclick="showTab('sql')">SQL</a>
        </div>

        {{-- DATA TAB --}}
        <div id="tab-data">
            <div class="data-table-wrap">
                <table id="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            @if(!empty($rows))
                                @foreach(array_keys((array) $rows[0]) as $col)<th>{{ $col }}</th>@endforeach
                            @elseif(!empty($columns))
                                @foreach($columns as $col)<th>{{ $col->Field }}</th>@endforeach
                            @endif
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(empty($rows))
                            <tr>
                                <td colspan="{{ !empty($columns) ? count($columns) + 2 : 2 }}" style="text-align:center;padding:40px;color:var(--text-faint)">
                                    <i class="fas fa-inbox" style="font-size:30px;display:block;margin-bottom:8px"></i> No data
                                </td>
                            </tr>
                        @else
                            @foreach($rows as $i => $row)
                                @php $rowArr = (array) $row;
                                    $pk = array_key_first($rowArr);
                                $pkVal = $rowArr[$pk]; @endphp
                                <tr data-pk-col="{{ $pkColumn ?? $pk }}" data-pk-val="{{ $pkVal }}">
                                    <td style="color:var(--text-faint)">{{ $offset + $i + 1 }}</td>
                                    @foreach($rowArr as $colName => $val)
                                        <td class="editable" data-column="{{ $colName }}"
                                            data-original="{{ is_null($val) ? '' : $val }}" @if(is_null($val)) data-is-null="1" @endif>
                                            @if(is_null($val))<span
                                            class="null-val">NULL</span>@else{{ \Illuminate\Support\Str::limit((string) $val, 80) }}@endif
                                        </td>
                                    @endforeach
                                    <td>
                                        <form method="POST" action="{{ route('admin.database.delete-row', $table) }}"
                                            style="display:inline" onsubmit="return confirm('Delete this row?')">@csrf <input
                                                type="hidden" name="where" value="`{{ $pk }}`='{{ addslashes($pkVal) }}'"><input
                                                type="hidden" name="page" value="{{ $page }}"><button class="btn-xs btn-red"><i
                                                    class="fas fa-trash"></i></button></form>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($totalPages > 1)
                <div class="pager">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span>Showing {{ $offset + 1 }}-{{ min($offset + $perPage, $totalRows) }} of
                            {{ number_format($totalRows) }}</span>
                        <span style="color:var(--input-border)">|</span>
                        <label style="font-size:12px">Per page:</label>
                        <select class="per-page-select" onchange="changePerPage(this.value)">
                            @foreach([50, 100, 500, 1000] as $pp)
                                <option value="{{ $pp }}" @if($perPage == $pp) selected @endif>{{ $pp }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;gap:4px;align-items:center">
                        {{-- First & Prev --}}
                        @if($page > 1)
                            <a href="?page=1&perPage={{ $perPage }}" title="First"><i class="fas fa-angle-double-left"></i></a>
                            <a href="?page={{ $page - 1 }}&perPage={{ $perPage }}">&laquo;</a>
                        @else
                            <span class="pager-btn disabled"><i class="fas fa-angle-double-left"></i></span>
                            <span class="pager-btn disabled">&laquo;</span>
                        @endif

                        {{-- Page numbers --}}
                        @for($p = max(1, $page - 3); $p <= min($totalPages, $page + 3); $p++)
                            @if($p == $page)<span class="current">{{ $p }}</span>@else<a
                            href="?page={{ $p }}&perPage={{ $perPage }}">{{ $p }}</a>@endif
                        @endfor

                        {{-- Next & Last --}}
                        @if($page < $totalPages)
                            <a href="?page={{ $page + 1 }}&perPage={{ $perPage }}">&raquo;</a>
                            <a href="?page={{ $totalPages }}&perPage={{ $perPage }}" title="Last"><i
                                    class="fas fa-angle-double-right"></i></a>
                        @else
                            <span class="pager-btn disabled">&raquo;</span>
                            <span class="pager-btn disabled"><i class="fas fa-angle-double-right"></i></span>
                        @endif

                        {{-- Go to page --}}
                        <div class="pager-goto">
                            <span>Go to:</span>
                            <input type="number" min="1" max="{{ $totalPages }}" value="{{ $page }}"
                                onkeydown="if(event.key==='Enter')goToPage(this.value)" id="gotoInput">
                            <button class="pager-btn" onclick="goToPage(document.getElementById('gotoInput').value)"><i
                                    class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- STRUCTURE TAB --}}
        <div id="tab-structure" style="display:none">
            <table>
                <thead>
                    <tr>
                        <th>Column</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Default</th>
                        <th>Extra</th>
                        <th>Collation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($columns as $col)
                        <tr>
                            <td style="font-weight:600">{{ $col->Field }}</td>
                            <td style="font-family:monospace;font-size:12px;color:var(--text-muted)">{{ $col->Type }}</td>
                            <td>@if($col->Null === 'YES')<span class="badge-null">NULL</span>@else NO @endif</td>
                            <td>@if($col->Key === 'PRI')<span class="badge-pk">PK</span>@elseif($col->Key==='UNI')UNI
                            @elseif($col->Key==='MUL')IDX @endif</td>
                            <td style="color:var(--text-faint)">{{ $col->Default ?? 'none' }}</td>
                            <td>@if(str_contains($col->Extra, 'auto_increment'))<span class="badge-ai">AI</span>@else
                            {{ $col->Extra }} @endif</td>
                            <td style="font-size:11px;color:var(--text-faint)">{{ $col->Collation }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- INDEXES TAB --}}
        <div id="tab-indexes" style="display:none">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Column</th>
                        <th>Unique</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($indexes as $idx)
                        <tr>
                            <td style="font-weight:500">{{ $idx->Key_name }}</td>
                            <td>{{ $idx->Column_name }}</td>
                            <td>{{ $idx->Non_unique ? 'No' : 'Yes' }}</td>
                            <td>{{ $idx->Index_type }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- SQL TAB --}}
        <div id="tab-sql" style="display:none">
            <div class="sql-box">{{ $createSql }}</div>
        </div>
    </div>

    {{-- Edit hint bar --}}
    <div class="edit-hint" id="editHint">Click a cell to edit &bull; Press <b>Enter</b> to save &bull; <b>Esc</b> to cancel
    </div>

    @if(session('success'))
        <div id="toast"
            style="position:fixed;bottom:24px;right:24px;background:var(--c-success);color:#fff;padding:12px 20px;border-radius:8px;font-size:13px;z-index:10000">
            <i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    <script>setTimeout(() => document.getElementById('toast')?.remove(), 4000)</script>@endif
@endsection
@push('scripts')
    <script>
        const TABLE_NAME = @json($table);
        const PK_COLUMN = @json($pkColumn ?? array_key_first((array) ($rows[0] ?? new \stdClass)));
        const UPDATE_URL = "{{ route('admin.database.update-cell', '__TABLE__') }}".replace('__TABLE__', TABLE_NAME);
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

        function showTab(name) {
            document.querySelectorAll('[id^="tab-"]').forEach(t => t.style.display = 'none');
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + name).style.display = 'block';
            event.target.classList.add('active');
        }

        function changePerPage(val) {
            window.location.href = '?page=1&perPage=' + val;
        }

        function goToPage(p) {
            const perPage = new URLSearchParams(window.location.search).get('perPage') || {{ $perPage }};
            p = parseInt(p);
            if (p >= 1 && p <= {{ $totalPages }}) {
                window.location.href = '?page=' + p + '&perPage=' + perPage;
            }
        }

        // ============================
        // INLINE EDITING
        // ============================
        let currentEditCell = null;

        // Show hint on hover
        document.querySelectorAll('td.editable').forEach(cell => {
            cell.addEventListener('click', function (e) {
                if (currentEditCell === this) return;
                if (currentEditCell) cancelEdit();
                startEdit(this);
            });
        });

        function startEdit(cell) {
            currentEditCell = cell;
            const original = cell.dataset.original;
            const isNull = cell.dataset.isNull === '1';
            const column = cell.dataset.column;

            cell.classList.add('editing');

            // Decide between input and textarea based on content length
            const useTextarea = original.length > 80;

            if (useTextarea) {
                cell.innerHTML = `<textarea class="edit-input">${escapeHtml(original)}</textarea>`;
            } else {
                cell.innerHTML = `<input type="text" class="edit-input" value="${escapeHtml(original)}">`;
            }

            const input = cell.querySelector('.edit-input');
            input.focus();
            input.select();

            // Show hint
            document.getElementById('editHint').style.display = 'block';

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    saveEdit(cell, input.value);
                } else if (e.key === 'Escape') {
                    cancelEdit();
                }
            });

            input.addEventListener('blur', function () {
                // Small delay to allow click events on other elements
                setTimeout(() => {
                    if (currentEditCell === cell) {
                        saveEdit(cell, input.value);
                    }
                }, 150);
            });
        }

        function saveEdit(cell, newValue) {
            const original = cell.dataset.original;
            const column = cell.dataset.column;
            const row = cell.closest('tr');
            const pkCol = row.dataset.pkCol;
            const pkVal = row.dataset.pkVal;

            // If value unchanged, just cancel
            if (newValue === original) {
                cancelEdit();
                return;
            }

            cell.classList.remove('editing');
            cell.innerHTML = '<i class="fas fa-spinner fa-spin" style="color:var(--text-faint)"></i>';

            // AJAX save
            fetch(UPDATE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    column: column,
                    value: newValue,
                    pk_column: pkCol,
                    pk_value: pkVal
                })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        cell.dataset.original = newValue;
                        cell.dataset.isNull = (newValue === '' || newValue === null) ? '1' : '0';
                        cell.textContent = newValue || 'NULL';
                        if (!newValue) cell.classList.add('null-val'); else cell.classList.remove('null-val');
                        cell.classList.add('edit-success');
                        setTimeout(() => cell.classList.remove('edit-success'), 1000);
                        showToast('Updated: ' + column, 'success');
                    } else {
                        cell.textContent = original || 'NULL';
                        cell.classList.add('edit-error');
                        setTimeout(() => cell.classList.remove('edit-error'), 1000);
                        showToast('Error: ' + (data.message || 'Update failed'), 'error');
                    }
                })
                .catch(err => {
                    cell.textContent = original || 'NULL';
                    cell.classList.add('edit-error');
                    setTimeout(() => cell.classList.remove('edit-error'), 1000);
                    showToast('Network error', 'error');
                });

            currentEditCell = null;
            document.getElementById('editHint').style.display = 'none';
        }

        function cancelEdit() {
            if (!currentEditCell) return;
            const cell = currentEditCell;
            const original = cell.dataset.original;
            const isNull = cell.dataset.isNull === '1';

            cell.classList.remove('editing');
            if (isNull) {
                cell.innerHTML = '<span class="null-val">NULL</span>';
            } else {
                cell.textContent = original.length > 80 ? original.substring(0, 80) + '...' : original;
            }

            currentEditCell = null;
            document.getElementById('editHint').style.display = 'none';
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML.replace(/"/g, '&quot;');
        }

        function showToast(message, type) {
            const existing = document.getElementById('ajax-toast');
            if (existing) existing.remove();

            const bg = type === 'success' ? 'var(--c-success)' : 'var(--c-danger)';
            const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
            const toast = document.createElement('div');
            toast.id = 'ajax-toast';
            toast.style.cssText = `position:fixed;bottom:24px;right:24px;background:${bg};color:#fff;padding:12px 20px;border-radius:8px;font-size:13px;z-index:10000;box-shadow:0 4px 12px rgba(0,0,0,0.15)`;
            toast.innerHTML = `<i class="fas fa-${icon}"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        // Keyboard shortcut: Ctrl+Z to undo (cancel current edit)
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && currentEditCell) {
                cancelEdit();
            }
        });
    </script>
@endpush