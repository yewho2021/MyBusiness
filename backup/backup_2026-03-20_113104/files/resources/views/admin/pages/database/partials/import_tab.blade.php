<div class="import-tab-content" style="padding:20px">
    <div class="warning-box"
        style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;font-size:13px;color:#92400e;margin-bottom:20px">
        <i class="fas fa-exclamation-triangle"></i> <strong>Warning:</strong> Importing SQL may overwrite existing data.
        Make sure you have a backup before importing.
    </div>

    <form onsubmit="DatabaseManager.handleImport(event, this)">
        <div class="card">
            <div class="card-header"><span class="card-title">Upload SQL File</span></div>
            <div class="form-body" style="padding:20px">
                <div class="upload-zone" onclick="this.nextElementSibling.click()"
                    style="border:2px dashed #d1d5db;border-radius:10px;padding:40px;text-align:center;cursor:pointer;transition:all .2s">
                    <i class="fas fa-cloud-upload-alt"
                        style="font-size:36px;color:#94a3b8;display:block;margin-bottom:12px"></i>
                    <p style="font-size:14px;color:#64748b;margin:0">Click to select .sql file</p>
                    <p class="sub" style="font-size:12px;color:#94a3b8;margin-top:4px">Max file size: 50MB</p>
                    <div class="file-name" style="margin-top:12px;font-size:13px;color:#4f46e5;font-weight:500"></div>
                </div>
                <input type="file" name="sql_file" class="file-input" accept=".sql,.txt" style="display:none"
                    onchange="DatabaseManager.showImportFileName(this)">
                <button type="submit" class="btn-run" style="margin-top:16px" disabled><i class="fas fa-upload"></i>
                    Import Now</button>
            </div>
        </div>
    </form>
    <div class="import-results" style="margin-top:20px"></div>
</div>