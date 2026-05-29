<div class="import-tab-content" style="padding:20px">
    <div class="imp-warn">
        <i class="fas fa-exclamation-triangle"></i>
        <div><strong>Warning:</strong> Importing SQL may overwrite existing data. Make sure you have a backup before importing.</div>
    </div>

    <form id="importForm" onsubmit="DatabaseManager.handleImport(event, this)">
        @csrf
        <div class="imp-drop-zone" id="impDropZone">
            <div class="imp-drop-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <div class="imp-drop-text">Drag & drop a .sql file here, or click to browse</div>
            <div class="imp-drop-sub">Supports .sql and .txt files · Max 50 MB</div>
            <input type="file" name="sql_file" class="import-file-input" accept=".sql,.txt" style="display:none">
        </div>

        {{-- File info (shown after selection) --}}
        <div class="imp-file-info" style="display:none">
            <div class="imp-file-card">
                <div class="imp-file-icon"><i class="fas fa-file-code"></i></div>
                <div class="imp-file-details">
                    <div class="imp-file-name"></div>
                    <div class="imp-file-meta"></div>
                </div>
                <button type="button" class="imp-file-remove" title="Remove file"><i class="fas fa-times"></i></button>
            </div>
        </div>

        <button type="submit" class="imp-btn-run" disabled>
            <i class="fas fa-upload"></i> Import Now
        </button>
    </form>

    {{-- Results --}}
    <div class="import-results"></div>
</div>

<style>
.imp-warn { display:flex; align-items:flex-start; gap:10px; background:var(--c-warning-light); border:1px solid var(--c-warning-border); border-radius:var(--card-radius,10px); padding:14px 16px; font-size:13px; color:var(--c-warning); margin-bottom:20px; line-height:1.5; }
.imp-warn i { color:var(--c-warning,var(--c-warning)); margin-top:2px; flex-shrink:0; }

.imp-drop-zone { border:2px dashed var(--input-border); border-radius:var(--card-radius,12px); padding:48px 24px; text-align:center; cursor:pointer; transition:all .2s; }
.imp-drop-zone:hover { border-color:var(--c-secondary,var(--c-secondary)); background:var(--c-secondary-light); }
.imp-drop-zone.dragover { border-color:var(--c-success,var(--c-success)); background:var(--c-success-light); border-style:solid; }
.imp-drop-zone.has-file { border-color:var(--c-success); background:var(--c-success-light); border-style:solid; }
.imp-drop-icon { font-size:42px; color:var(--text-faint); margin-bottom:12px; }
.imp-drop-zone:hover .imp-drop-icon { color:var(--c-secondary,var(--c-secondary)); }
.imp-drop-zone.dragover .imp-drop-icon { color:var(--c-success,var(--c-success)); }
.imp-drop-text { font-size:15px; color:var(--text-secondary); font-weight:500; margin-bottom:4px; }
.imp-drop-sub { font-size:12px; color:var(--text-faint); }

.imp-file-info { margin-top:16px; }
.imp-file-card { display:flex; align-items:center; gap:14px; padding:14px 18px; background:var(--table-header-bg,var(--table-header-bg)); border:1px solid var(--border-color,var(--border-color)); border-radius:var(--card-radius,10px); }
.imp-file-icon { width:44px; height:44px; background:linear-gradient(135deg,var(--c-secondary-light),var(--c-secondary-light)); border-radius:var(--card-radius,10px); display:flex; align-items:center; justify-content:center; font-size:20px; color:var(--c-secondary,var(--c-secondary)); flex-shrink:0; }
.imp-file-details { flex:1; min-width:0; }
.imp-file-name { font-size:14px; font-weight:600; color:var(--header-text,var(--text-heading)); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.imp-file-meta { font-size:12px; color:var(--text-faint); margin-top:2px; }
.imp-file-remove { background:none; border:none; color:var(--text-faint); cursor:pointer; padding:4px 8px; border-radius:6px; font-size:14px; }
.imp-file-remove:hover { color:var(--c-primary,var(--c-danger)); background:var(--c-danger-light); }

.imp-btn-run { background:linear-gradient(135deg,var(--c-danger),var(--c-primary-hover)); color:#fff; border:none; padding:12px 28px; border-radius:var(--card-radius,10px); font-size:14px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:8px; margin-top:16px; transition:all .2s; }
.imp-btn-run:hover:not(:disabled) { box-shadow:0 4px 12px rgba(220,38,38,.3); transform:translateY(-1px); }
.imp-btn-run:disabled { opacity:.45; cursor:not-allowed; }

.imp-result-header { padding:16px 20px; border-radius:var(--card-radius,10px); margin-bottom:12px; display:flex; align-items:center; gap:12px; font-size:14px; font-weight:600; }
.imp-result-header.ok { background:var(--c-success-light); border:1px solid var(--c-success-border); color:var(--c-success); }
.imp-result-header.err { background:var(--c-danger-light); border:1px solid var(--c-danger-border); color:var(--c-primary-hover); }
.imp-result-header.running { background:var(--c-secondary-light); border:1px solid var(--c-secondary-border); color:var(--c-secondary); }

.imp-stats { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:12px; }
.imp-stat { background:var(--card-bg,#fff); border:1px solid var(--border-color,var(--border-color)); border-radius:8px; padding:10px 16px; font-size:13px; color:var(--text-muted); display:flex; align-items:center; gap:8px; }
.imp-stat strong { font-size:16px; color:var(--header-text,var(--text-heading)); }
.imp-stat.ok strong { color:var(--c-success,var(--c-success)); }
.imp-stat.err strong { color:var(--c-primary,var(--c-danger)); }

.imp-log-wrap { background:var(--card-bg,#fff); border:1px solid var(--border-color,var(--border-color)); border-radius:var(--card-radius,10px); overflow:hidden; }
.imp-log-header { padding:12px 18px; background:var(--table-header-bg,var(--table-header-bg)); border-bottom:1px solid var(--border-color,var(--border-color)); display:flex; justify-content:space-between; align-items:center; }
.imp-log-title { font-size:13px; font-weight:600; color:var(--header-text,var(--text-heading)); }
.imp-log-filter { display:flex; gap:4px; }
.imp-log-filter button { padding:4px 12px; border-radius:6px; border:1px solid var(--border-color,var(--border-color)); background:var(--card-bg,#fff); font-size:12px; color:var(--text-muted); cursor:pointer; }
.imp-log-filter button:hover { background:var(--border-light,var(--border-light)); }
.imp-log-filter button.active { background:var(--text-heading); color:#fff; border-color:var(--header-text,var(--text-heading)); }

.imp-log { max-height:400px; overflow-y:auto; }
.imp-log-row { display:flex; align-items:flex-start; gap:10px; padding:8px 18px; border-bottom:1px solid var(--table-header-bg); font-size:13px; line-height:1.5; }
.imp-log-row:last-child { border-bottom:none; }
.imp-log-row i { margin-top:3px; font-size:11px; flex-shrink:0; }
.imp-log-num { color:var(--text-faint); font-size:11px; min-width:30px; text-align:right; font-weight:500; flex-shrink:0; }
.imp-log-msg { flex:1; color:var(--text-body); word-break:break-word; }
.imp-log-err { display:block; color:var(--c-primary-hover); font-size:12px; margin-top:4px; padding:6px 10px; background:var(--c-danger-light); border-radius:6px; border:1px solid var(--c-danger-border); font-family:var(--font-mono,'JetBrains Mono'),monospace; word-break:break-all; }
.imp-log-row.type-ok i { color:var(--c-success,var(--c-success)); }
.imp-log-row.type-err i { color:var(--c-primary,var(--c-danger)); }
.imp-log-row.type-info i { color:var(--c-secondary,var(--c-secondary)); }
.imp-log-row.type-warn i { color:var(--c-warning,var(--c-warning)); }
.imp-log-row.type-err { background:var(--card-bg); }
</style>
