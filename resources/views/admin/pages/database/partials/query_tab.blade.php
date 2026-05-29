<div class="query-tab-content" style="display:flex;flex-direction:column;height:100%">
    <div class="sql-editor-wrap" style="flex:0 0 auto;border-bottom:1px solid var(--border-color)">
        <div class="monaco-sql-wrap" style="height:200px;min-height:100px"></div>
    </div>
    <div class="toolbar">
        <div style="display:flex;gap:8px;align-items:center">
            <button type="button" class="tb-btn" onclick="DatabaseManager.bookmarkQuery(this)" title="Bookmark this query">
                <i class="fas fa-star" style="color:var(--c-warning)"></i> Bookmark
            </button>
            <button type="button" class="tb-btn" onclick="DatabaseManager.formatSql(this)" title="Format SQL (Ctrl+Shift+F)">
                <i class="fas fa-indent"></i> Format
            </button>
            <button type="button" class="tb-btn" onclick="DatabaseManager.clearEditor(this)" title="Clear editor">
                <i class="fas fa-eraser"></i>
            </button>
            <span style="width:1px;height:20px;background:var(--border-color);margin:0 4px"></span>
            <button type="button" class="tb-btn" onclick="DatabaseManager.runExplain(this)" title="Run EXPLAIN on current query">
                <i class="fas fa-project-diagram" style="color:var(--c-purple)"></i> Explain
            </button>
            <button type="button" class="tb-btn tb-btn-tpl" onclick="DatabaseManager.toggleTemplateDropdown(this)" title="Insert SQL template">
                <i class="fas fa-file-code" style="color:var(--c-info)"></i> Templates <i class="fas fa-caret-down" style="font-size:10px;color:var(--text-faint)"></i>
            </button>
            <span style="width:1px;height:20px;background:var(--border-color);margin:0 4px"></span>
            <button type="button" class="tb-btn" onclick="DatabaseManager.pinResults(this)" title="Pin current results to a new tab">
                <i class="fas fa-thumbtack" style="color:var(--c-warning)"></i> Pin
            </button>
            <span class="execution-time" style="font-size:12px;color:var(--text-faint)"></span>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
            <span class="shortcut-hints"><kbd>Ctrl</kbd>+<kbd>Enter</kbd> Run &nbsp; <kbd>Ctrl</kbd>+<kbd>Shift</kbd>+<kbd>F</kbd> Format</span>
            <button type="button" class="btn-run" onclick="DatabaseManager.runQuery(this)"><i class="fas fa-play"></i> Execute</button>
        </div>
    </div>
    <div class="query-results" style="flex:1;overflow:auto">
        <div class="empty-results">
            <i class="fas fa-terminal"></i>
            <p>Enter a SQL query and press Execute</p>
            <span>Type table name + dot for column auto-complete</span>
        </div>
    </div>
</div>
