<div class="summary-tab-content">
    @php $totalRows = array_sum(array_column($tableList, 'rows')); $maxSize = max(array_column($tableList, 'size') ?: [1]); @endphp
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-table"></i></div>
            <div><div class="stat-val">{{ count($tableList) }}</div><div class="stat-label">Tables</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-hdd"></i></div>
            <div><div class="stat-val">{{ $totalSize >= 1048576 ? number_format($totalSize/1048576,1).' MB' : number_format($totalSize/1024,1).' KB' }}</div><div class="stat-label">Database Size</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fas fa-layer-group"></i></div>
            <div><div class="stat-val">{{ number_format($totalRows) }}</div><div class="stat-label">Total Rows</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-server"></i></div>
            <div><div class="stat-val" style="font-size:14px">{{ config('database.connections.mysql.host') }}</div><div class="stat-label">Server</div></div>
        </div>
    </div>

    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-table" style="color:var(--text-faint);margin-right:6px"></i> All Tables</span>
            <div style="display:flex;gap:8px;align-items:center">
                <input type="text" class="search-box" style="width:200px" placeholder="Filter tables..." onkeyup="DatabaseManager.filterSummaryTables(this.value)">
                <button class="btn-xs btn-blue" onclick="DatabaseManager.openQueryTab()"><i class="fas fa-plus"></i> New SQL</button>
                <button class="btn-xs btn-blue" onclick="DatabaseManager.openExportTab()"><i class="fas fa-download"></i> Export</button>
            </div>
        </div>

        {{-- Batch toolbar (hidden until tables selected) --}}
        <div id="summaryBatchBar" style="display:none;padding:8px 16px;background:var(--c-secondary-light);border-bottom:1px solid var(--c-secondary-border);display:none;align-items:center;gap:10px;font-size:13px">
            <span style="color:var(--c-secondary);font-weight:600"><i class="fas fa-check-square" style="margin-right:4px"></i> <span id="summaryBatchCount">0</span> selected</span>
            <div style="flex:1"></div>
            <button class="btn-xs btn-blue" onclick="summaryExportSelected()" style="padding:5px 12px"><i class="fas fa-download"></i> Export Selected</button>
            <button class="btn-xs" style="background:var(--c-warning-light);color:var(--c-warning);padding:5px 12px" onclick="summaryTruncateSelected()"><i class="fas fa-eraser"></i> Truncate</button>
            <button class="btn-xs btn-red" onclick="summaryDropSelected()" style="padding:5px 12px"><i class="fas fa-trash"></i> Drop</button>
        </div>

        <div style="overflow:auto;max-height:calc(100vh - 360px)">
            <table class="summary-table">
                <thead>
                    <tr>
                        <th style="width:36px;text-align:center"><input type="checkbox" id="summarySelectAll" style="width:15px;height:15px" onchange="summaryToggleAll(this.checked)"></th>
                        <th style="width:30px">#</th>
                        <th>Table Name</th>
                        <th style="text-align:right">Rows</th>
                        <th style="text-align:right">Size</th>
                        <th style="width:60px">Size</th>
                        <th>Engine</th>
                        <th>Collation</th>
                        <th style="width:80px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tableList as $i => $t)
                    <tr class="summary-row" oncontextmenu="DatabaseManager.showCtxMenu(event,'{{ $t['name'] }}')" data-table="{{ $t['name'] }}">
                        <td style="text-align:center"><input type="checkbox" class="summary-check" value="{{ $t['name'] }}" style="width:15px;height:15px" onchange="summaryUpdateBatch()"></td>
                        <td style="color:var(--hover-border);font-size:11px">{{ $i+1 }}</td>
                        <td>
                            <a href="javascript:void(0)" onclick="DatabaseManager.openTable('{{ $t['name'] }}')" class="tname">{{ $t['name'] }}</a>
                        </td>
                        <td style="text-align:right;font-family:'JetBrains Mono',monospace;font-size:11px">{{ number_format($t['rows']) }}</td>
                        <td style="text-align:right;font-family:'JetBrains Mono',monospace;font-size:11px;{{ $t['size'] > 524288 ? 'color:var(--c-danger);font-weight:600' : '' }}">{{ $t['size'] >= 1048576 ? number_format($t['size']/1048576,2).' MB' : number_format($t['size']/1024,1).' KB' }}</td>
                        <td>
                            <div style="width:50px;height:5px;background:var(--border-light);border-radius:3px;overflow:hidden">
                                <div style="height:100%;background:{{ $t['size'] > 524288 ? 'var(--c-danger)' : ($t['size'] > 65536 ? 'var(--c-warning)' : 'var(--c-secondary-border)') }};border-radius:3px;width:{{ max(2, round($t['size']/$maxSize*100)) }}%"></div>
                            </div>
                        </td>
                        <td><span style="font-size:10px;background:var(--border-light);padding:2px 8px;border-radius:4px;color:var(--text-muted)">{{ $t['engine'] }}</span></td>
                        <td style="font-size:11px;color:var(--text-faint)">{{ $t['collation'] }}</td>
                        <td>
                            <button class="btn-xs btn-blue" onclick="DatabaseManager.openTable('{{ $t['name'] }}')" title="View data"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function summaryToggleAll(checked) {
    document.querySelectorAll('.summary-check').forEach(c => { if (c.closest('.summary-row').style.display !== 'none') c.checked = checked; });
    summaryUpdateBatch();
}

function summaryUpdateBatch() {
    const checked = document.querySelectorAll('.summary-check:checked');
    const bar = document.getElementById('summaryBatchBar');
    const countEl = document.getElementById('summaryBatchCount');
    if (checked.length > 0) {
        bar.style.display = 'flex';
        countEl.textContent = checked.length;
    } else {
        bar.style.display = 'none';
    }
    const all = document.querySelectorAll('.summary-check');
    const selAll = document.getElementById('summarySelectAll');
    selAll.checked = checked.length === all.length;
    selAll.indeterminate = checked.length > 0 && checked.length < all.length;
}

function summaryExportSelected() {
    const tables = [...document.querySelectorAll('.summary-check:checked')].map(c => c.value);
    if (!tables.length) return;
    // Switch to Export tab with these tables pre-selected
    DatabaseManager.openExportTab();
    setTimeout(() => {
        document.querySelectorAll('.exp-check').forEach(c => c.checked = tables.includes(c.value));
        expUpdateEstimate();
    }, 200);
}

function summaryTruncateSelected() {
    const tables = [...document.querySelectorAll('.summary-check:checked')].map(c => c.value);
    if (!tables.length) return;
    if (!confirm('TRUNCATE ' + tables.length + ' table(s)? This will delete ALL data in:\n\n' + tables.join('\n') + '\n\nThis cannot be undone!')) return;
    tables.forEach(t => {
        fetch("{{ url('database/table') }}/" + encodeURIComponent(t) + "/truncate", {
            method:'POST', headers:{'X-CSRF-TOKEN':CSRF_TOKEN,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
        });
    });
    setTimeout(() => location.reload(), 500);
}

function summaryDropSelected() {
    const tables = [...document.querySelectorAll('.summary-check:checked')].map(c => c.value);
    if (!tables.length) return;
    const confirm1 = prompt('DROP ' + tables.length + ' table(s)? Type "DROP" to confirm:');
    if (confirm1 !== 'DROP') { DatabaseManager.toast('Drop cancelled.', 'info'); return; }
    tables.forEach(t => {
        fetch("{{ url('database/table') }}/" + encodeURIComponent(t) + "/drop", {
            method:'POST', headers:{'X-CSRF-TOKEN':CSRF_TOKEN,'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
            body:JSON.stringify({_method:'DELETE'})
        });
    });
    setTimeout(() => location.reload(), 500);
}
</script>
