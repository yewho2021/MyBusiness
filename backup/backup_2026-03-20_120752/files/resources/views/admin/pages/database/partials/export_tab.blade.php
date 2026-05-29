<div class="export-tab-content" style="padding:20px">
    <form onsubmit="DatabaseManager.handleExport(event, this)">
        <div class="card">
            <div class="card-header"><span class="card-title">Select Tables to Export</span></div>
            <div class="form-body" style="padding:20px">
                <div class="check-actions" style="margin-bottom:12px;display:flex;gap:12px">
                    <button type="button" class="btn-xs btn-blue"
                        onclick="this.closest('form').querySelectorAll('.exp-check').forEach(c=>c.checked=true)">Select
                        All</button>
                    <button type="button" class="btn-xs btn-blue"
                        onclick="this.closest('form').querySelectorAll('.exp-check').forEach(c=>c.checked=false)">Deselect
                        All</button>
                </div>
                <div class="table-grid"
                    style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px;max-height:300px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:6px;padding:12px">
                    @foreach($tableList as $t)
                        <label
                            style="font-size:13px;display:flex;align-items:center;gap:8px;cursor:pointer;padding:4px 8px;border-radius:4px">
                            <input type="checkbox" name="tables[]" value="{{ $t['name'] }}" class="exp-check" checked
                                style="width:16px;height:16px">
                            {{ $t['name'] }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">Export Options</span></div>
            <div class="form-body" style="padding:20px">
                <div class="option-row" style="display:flex;gap:30px">
                    <label style="font-size:13px;display:flex;align-items:center;gap:10px;cursor:pointer">
                        <input type="checkbox" name="include_structure" value="1" checked
                            style="width:16px;height:16px"> Include table structure (CREATE)
                    </label>
                    <label style="font-size:13px;display:flex;align-items:center;gap:10px;cursor:pointer">
                        <input type="checkbox" name="include_data" value="1" checked style="width:16px;height:16px">
                        Include data (INSERT)
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-run" style="background:#4f46e5"><i class="fas fa-file-download"></i> Generate
            SQL Export</button>
    </form>
</div>