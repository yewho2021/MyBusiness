<div class="query-tab-content" style="display:flex;flex-direction:column;height:100%">
    <div class="sql-editor-wrap" style="flex:0 0 auto;border-bottom:1px solid #e2e8f0">
        <div class="monaco-sql-wrap" style="height:200px;min-height:100px"></div>
    </div>
    <div class="toolbar">
        <div style="display:flex;gap:8px;align-items:center">
            <button type="button" class="tb-btn" onclick="DatabaseManager.bookmarkQuery(this)" title="Bookmark">
                <i class="fas fa-star" style="color:#f59e0b"></i> Bookmark
            </button>
            <button type="button" class="tb-btn" onclick="DatabaseManager.formatSql(this)" title="Format SQL">
                <i class="fas fa-indent"></i> Format
            </button>
            <button type="button" class="tb-btn" onclick="DatabaseManager.clearEditor(this)" title="Clear">
                <i class="fas fa-eraser"></i>
            </button>
            <span style="width:1px;height:20px;background:#e2e8f0;margin:0 4px"></span>
            <span class="execution-time" style="font-size:12px;color:#94a3b8"></span>
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
            <span>Double-click sidebar tables to explore</span>
        </div>
    </div>
</div>
