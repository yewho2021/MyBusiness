<div class="export-tab-content" style="padding:20px">
    {{-- Format selector --}}
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px">
        <span style="font-size:14px;font-weight:600;color:var(--text-body)">Format:</span>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;color:var(--text-secondary)">
            <input type="radio" name="export_format" value="sql" checked style="width:15px;height:15px"> .sql
        </label>
        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;color:var(--text-secondary)">
            <input type="radio" name="export_format" value="zip" style="width:15px;height:15px"> .zip <span style="font-size:11px;color:var(--text-faint)">(compressed)</span>
        </label>
    </div>

    {{-- Table selection --}}
    <div class="card" style="margin-bottom:16px">
        <div class="card-header">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;font-weight:600;color:var(--text-heading)">
                <input type="checkbox" id="expSelectAll" checked style="width:16px;height:16px" onchange="expToggleAll(this.checked)">
                Select All ({{ count($tableList) }} tables)
            </label>
            <input type="text" class="search-box" style="width:180px" placeholder="Filter tables..." oninput="expFilterTables(this.value)">
        </div>
        <div style="max-height:320px;overflow-y:auto" id="expTableList">
            @php $maxSize = max(array_column($tableList, 'size') ?: [1]); @endphp
            @foreach($tableList as $t)
            <label class="exp-table-row" data-name="{{ strtolower($t['name']) }}" style="display:flex;align-items:center;gap:10px;padding:8px 16px;cursor:pointer;border-bottom:1px solid var(--table-header-bg);transition:background .1s">
                <input type="checkbox" class="exp-check" value="{{ $t['name'] }}" checked style="width:15px;height:15px;flex-shrink:0">
                <i class="fas fa-table" style="font-size:11px;color:var(--hover-border);flex-shrink:0"></i>
                <span style="flex:1;font-size:13px;font-weight:500;color:var(--text-heading);font-family:'JetBrains Mono',monospace;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $t['name'] }}</span>
                <span style="font-size:12px;color:var(--text-faint);min-width:60px;text-align:right">{{ number_format($t['rows']) }}</span>
                <span style="font-size:12px;color:{{ $t['size'] > 524288 ? 'var(--c-danger)' : 'var(--text-faint)' }};min-width:70px;text-align:right;font-weight:{{ $t['size'] > 524288 ? '600' : '400' }}">{{ $t['size'] >= 1048576 ? number_format($t['size']/1048576,2).' MB' : number_format($t['size']/1024,1).' KB' }}</span>
                <div style="width:50px;height:4px;background:var(--border-light);border-radius:2px;overflow:hidden;flex-shrink:0">
                    <div style="height:100%;background:{{ $t['size'] > 524288 ? 'var(--c-danger)' : 'var(--c-secondary-border)' }};border-radius:2px;width:{{ max(2, round($t['size']/$maxSize*100)) }}%"></div>
                </div>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Options --}}
    <div style="display:flex;gap:20px;margin-bottom:16px;align-items:center">
        <label style="font-size:13px;display:flex;align-items:center;gap:8px;cursor:pointer;color:var(--text-secondary)">
            <input type="checkbox" id="expStructure" checked style="width:15px;height:15px"> Include structure (CREATE)
        </label>
        <label style="font-size:13px;display:flex;align-items:center;gap:8px;cursor:pointer;color:var(--text-secondary)">
            <input type="checkbox" id="expData" checked style="width:15px;height:15px"> Include data (INSERT)
        </label>
        <span id="expEstimate" style="margin-left:auto;font-size:13px;color:var(--text-faint)"></span>
    </div>

    {{-- Build button --}}
    <button class="btn-run" style="background:linear-gradient(135deg,var(--c-danger),var(--c-primary-hover))" id="expBuildBtn" onclick="expBuildExport()">
        <i class="fas fa-cog"></i> Build Export
    </button>

    {{-- Log panel (hidden until build starts) --}}
    <div id="expLogSection" style="display:none;margin-top:16px">
        <div class="card" style="margin-bottom:0;overflow:hidden">
            <div class="card-header" style="background:var(--code-bg);border-color:var(--text-heading)">
                <span style="font-size:13px;font-weight:600;color:var(--border-color)"><i class="fas fa-terminal" style="color:var(--text-faint);margin-right:6px"></i> Export Log</span>
                <div style="display:flex;align-items:center;gap:8px">
                    <span id="expLogStatus" style="font-size:12px;color:var(--text-faint)"></span>
                    <button class="btn-xs" style="background:var(--text-heading);color:var(--text-faint);border:1px solid var(--text-body);font-size:11px" onclick="navigator.clipboard.writeText(document.getElementById('expLogOutput').value);DatabaseManager.toast('Log copied!','success')"><i class="fas fa-copy"></i></button>
                    <a id="expDownloadBtn" href="#" class="btn-xs btn-blue" style="display:none;text-decoration:none;background:var(--c-danger);color:#fff;border:none;padding:5px 12px"><i class="fas fa-download"></i> Download</a>
                </div>
            </div>
            <textarea id="expLogOutput" readonly spellcheck="false" style="width:100%;height:300px;border:none;resize:none;font-family:'JetBrains Mono',monospace;font-size:11.5px;line-height:1.65;color:var(--border-color);background:var(--code-bg);padding:14px;outline:none;white-space:pre;overflow:auto"></textarea>
            <div style="padding:8px 14px;background:var(--text-heading);display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted)">
                <span id="expFooterInfo"></span>
                <span id="expFooterTime"></span>
            </div>
        </div>
    </div>
</div>

<script>
function expToggleAll(checked) {
    document.querySelectorAll('.exp-check').forEach(c => { if (c.closest('.exp-table-row').style.display !== 'none') c.checked = checked; });
    expUpdateEstimate();
}

function expFilterTables(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.exp-table-row').forEach(row => {
        row.style.display = row.dataset.name.includes(q) ? 'flex' : 'none';
    });
}

function expUpdateEstimate() {
    const checked = document.querySelectorAll('.exp-check:checked');
    const el = document.getElementById('expEstimate');
    if (el) el.textContent = checked.length + ' table(s) selected';
}

// Update estimate on checkbox change
document.addEventListener('change', e => {
    if (e.target.classList.contains('exp-check')) {
        const all = document.querySelectorAll('.exp-check');
        const checked = document.querySelectorAll('.exp-check:checked');
        document.getElementById('expSelectAll').checked = checked.length === all.length;
        document.getElementById('expSelectAll').indeterminate = checked.length > 0 && checked.length < all.length;
        expUpdateEstimate();
    }
});

async function expBuildExport() {
    const btn = document.getElementById('expBuildBtn');
    const logSection = document.getElementById('expLogSection');
    const logEl = document.getElementById('expLogOutput');
    const statusEl = document.getElementById('expLogStatus');
    const dlBtn = document.getElementById('expDownloadBtn');
    const footerInfo = document.getElementById('expFooterInfo');
    const footerTime = document.getElementById('expFooterTime');
    const startTime = performance.now();

    // Gather selected tables
    const tables = [...document.querySelectorAll('.exp-check:checked')].map(c => c.value);
    if (tables.length === 0) { DatabaseManager.toast('No tables selected.', 'error'); return; }

    const format = document.querySelector('input[name="export_format"]:checked')?.value || 'sql';
    const includeStructure = document.getElementById('expStructure').checked;
    const includeData = document.getElementById('expData').checked;

    if (!includeStructure && !includeData) { DatabaseManager.toast('Select at least structure or data.', 'error'); return; }

    // UI setup
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Building...';
    dlBtn.style.display = 'none';
    logEl.value = '⏳ Building export — ' + tables.length + ' tables...\n';
    statusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Building...';
    footerInfo.textContent = '';
    footerTime.textContent = '';
    logSection.style.display = 'block';

    try {
        const res = await fetch("{{ route('admin.database.export-ajax') }}", {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' },
            body: JSON.stringify({ tables, format, include_structure: includeStructure, include_data: includeData })
        });

        if (!res.ok) {
            const ct = res.headers.get('content-type') || '';
            let errMsg = 'Server error ' + res.status;
            if (ct.includes('json')) { const d = await res.json(); errMsg = d.error || d.message || errMsg; }
            throw new Error(errMsg);
        }

        const data = await res.json();
        const elapsed = Math.round(performance.now() - startTime);

        if (!data.success) {
            logEl.value = buildExpLog(data, elapsed, true);
            statusEl.innerHTML = '<i class="fas fa-times-circle" style="color:var(--c-danger)"></i> Failed';
            return;
        }

        logEl.value = buildExpLog(data, elapsed, false);
        logEl.scrollTop = logEl.scrollHeight;

        statusEl.innerHTML = '<i class="fas fa-check-circle" style="color:var(--c-success)"></i> ' + data.table_count + ' tables · ' + data.file_size;
        footerInfo.textContent = data.file_name;
        footerTime.textContent = elapsed + 'ms';

        dlBtn.href = data.download_url;
        dlBtn.style.display = 'inline-flex';
        dlBtn.innerHTML = '<i class="fas fa-download"></i> Download .' + data.format;

    } catch(err) {
        const elapsed = Math.round(performance.now() - startTime);
        logEl.value = '✗ Export failed: ' + err.message + '\n\nElapsed: ' + elapsed + 'ms';
        statusEl.innerHTML = '<i class="fas fa-times-circle" style="color:var(--c-danger)"></i> Failed';
    } finally {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-cog"></i> Build Export';
    }
}

function buildExpLog(data, elapsed, isError) {
    const icons = { phase:'═', ok:'✓', err:'✗', info:'●', warn:'⚠' };
    let log = '════════════════════════════════════════════\n';
    log += isError ? ' EXPORT FAILED\n' : ' DATABASE EXPORT LOG\n';
    log += ' ' + new Date().toLocaleString() + '\n';
    log += '════════════════════════════════════════════\n\n';

    if (data.error) log += ' ✗ ' + data.error + '\n\n';

    if (data.log) {
        data.log.forEach(e => {
            const icon = icons[e.type] || '·';
            const ms = e.ms !== undefined ? ' [' + e.ms + 'ms]' : '';
            if (e.type === 'phase') { log += '\n ══ ' + e.msg + ' ══\n'; }
            else { log += ' ' + icon + ' ' + e.msg + ms + '\n'; }
        });
    }

    if (!isError && data.table_count) {
        log += '\n════════════════════════════════════════════\n';
        log += ' Tables: ' + data.table_count + ' | Rows: ' + (data.total_rows ? data.total_rows.toLocaleString() : '0') + '\n';
        log += ' File: ' + data.file_name + ' (' + data.file_size + ')\n';
        log += ' Server: ' + data.elapsed_ms + 'ms | Total: ' + elapsed + 'ms\n';
        log += '════════════════════════════════════════════\n';
    }
    return log;
}

// Init estimate
setTimeout(() => expUpdateEstimate(), 100);
</script>
