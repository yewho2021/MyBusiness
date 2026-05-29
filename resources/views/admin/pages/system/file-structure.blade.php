@extends('admin.layouts.app')
@section('title', 'File Structure')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
.page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; }
.page-header h2 { font-size:22px; font-weight:700; color:var(--header-text,var(--text-heading)); margin:0 0 4px; }
.page-desc { font-size:13px; color:var(--text-muted); margin:0; }
.header-actions { display:flex; gap:8px; }

.btn { padding:8px 14px; border-radius:7px; font-size:12px; font-weight:500; cursor:pointer; border:none; display:inline-flex; align-items:center; gap:6px; transition:all .15s; }
.btn-primary { background:var(--c-primary,var(--c-danger)); color:#fff; }
.btn-primary:hover { background:var(--c-primary-hover,var(--c-primary-hover)); }
.btn-outline { background:var(--card-bg,#fff); color:var(--text-body); border:1px solid var(--input-border); }
.btn-outline:hover { background:var(--hover-bg); }
.btn-sm { padding:6px 10px; font-size:11px; }

.panels { display:grid; grid-template-columns:1fr 1fr; gap:16px; height:calc(100vh - 190px); }
@media(max-width:1100px) { .panels { grid-template-columns:1fr; height:auto; } }

.panel { background:var(--card-bg,#fff); border-radius:var(--card-radius,10px); border:1px solid var(--border-color,var(--border-color)); display:flex; flex-direction:column; overflow:hidden; min-height:400px; }
.panel-header { padding:12px 16px; background:var(--table-header-bg,var(--table-header-bg)); border-bottom:1px solid var(--border-color,var(--border-color)); display:flex; justify-content:space-between; align-items:center; flex-shrink:0; gap:8px; }
.panel-title { font-size:13px; font-weight:700; color:var(--header-text,var(--text-heading)); display:flex; align-items:center; gap:8px; }
.panel-title i { color:var(--c-secondary,var(--c-secondary)); font-size:14px; }
.panel-actions { display:flex; gap:5px; flex-shrink:0; align-items:center; }
.panel-actions .sep { width:1px; height:20px; background:var(--border-color); margin:0 4px; }
.panel-meta { font-size:11px; color:var(--text-faint); display:flex; align-items:center; gap:5px; }
.panel-wrap { flex:1; position:relative; overflow:hidden; }

.panel-output {
    width:100%; height:100%;
    padding:16px;
    border:none; resize:none;
    font-family:'JetBrains Mono','Fira Code','Courier New',monospace;
    font-size:11px; line-height:1.7;
    color:var(--border-color); background:var(--code-bg);
    outline:none; white-space:pre; overflow:auto;
}
.panel-output::selection { background:var(--text-body); }

.panel-footer { padding:8px 16px; background:var(--text-heading); border-top:1px solid var(--text-body); display:flex; justify-content:space-between; align-items:center; font-size:11px; color:var(--text-muted); flex-shrink:0; }

.loading-mask { position:absolute; inset:0; background:rgba(15,23,42,.9); display:none; flex-direction:column; align-items:center; justify-content:center; gap:10px; z-index:10; color:var(--border-color); font-size:12px; }
.loading-mask.show { display:flex; }
.loading-mask i { font-size:24px; color:var(--c-danger); }

.toast { position:fixed; bottom:24px; right:24px; padding:10px 16px; border-radius:8px; color:#fff; font-size:12px; font-weight:500; display:flex; align-items:center; gap:7px; z-index:9999; box-shadow:0 4px 12px rgba(0,0,0,.15); animation:toastIn .3s ease; }
.toast-success { background:var(--c-success); }
@keyframes toastIn { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
.g-chev.open { transform:rotate(180deg); }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h2><i class="fas fa-sitemap" style="color:var(--c-secondary,var(--c-secondary));margin-right:6px"></i> File Structure</h2>
        <p class="page-desc">Project files & database schema — copy or download for documentation</p>
    </div>
    <div class="header-actions">
        <button class="btn btn-outline" onclick="refreshAll()" id="btnRefresh"><i class="fas fa-sync-alt"></i> Refresh All</button>
        <button class="btn btn-primary" onclick="downloadAll()"><i class="fas fa-download"></i> Download Both</button>
    </div>
</div>

<div class="panels">
    {{-- Panel 1: File Structure --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-folder-tree"></i> File Structure</div>
            <div class="panel-actions">
                <button class="btn btn-primary btn-sm" id="btnExportFiles" onclick="exportFiles()" title="Export project source code as .zip">
                    <i class="fas fa-file-archive"></i> Export Files
                </button>
                <div class="sep"></div>
                <button class="btn btn-outline btn-sm" onclick="refreshPanel('files')" title="Refresh"><i class="fas fa-sync-alt"></i></button>
                <button class="btn btn-outline btn-sm" onclick="copyPanel('files')" title="Copy"><i class="fas fa-copy"></i></button>
                <button class="btn btn-outline btn-sm" onclick="downloadPanel('files','file_structure')" title="Download .txt"><i class="fas fa-download"></i></button>
            </div>
        </div>
        <div class="panel-wrap">
            <div class="loading-mask" id="loading-files"><i class="fas fa-circle-notch fa-spin"></i><span>Scanning files...</span></div>
            <textarea class="panel-output" id="output-files" readonly spellcheck="false">{{ $fileOutput }}</textarea>
        </div>
        <div class="panel-footer">
            <span id="meta-files-lines"><i class="fas fa-align-left"></i> {{ substr_count($fileOutput, "\n") + 1 }} lines</span>
            <span><i class="fas fa-folder"></i> {{ base_path() }}</span>
        </div>
    </div>

    {{-- Panel 2: Database Schema --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-database"></i> Database Schema</div>
            <div class="panel-actions">
                <a href="{{ route('admin.file-structure.export-db', 'sql') }}" class="btn btn-outline btn-sm" style="text-decoration:none;" title="Export database as .sql" onclick="showToast('Generating .sql export...')">
                    <i class="fas fa-file-code"></i> .sql
                </a>
                <a href="{{ route('admin.file-structure.export-db', 'zip') }}" class="btn btn-primary btn-sm" style="text-decoration:none;" title="Export database as .zip" onclick="showToast('Generating .zip export...')">
                    <i class="fas fa-file-archive"></i> .zip
                </a>
                <div class="sep"></div>
                <button class="btn btn-outline btn-sm" onclick="refreshPanel('db')" title="Refresh"><i class="fas fa-sync-alt"></i></button>
                <button class="btn btn-outline btn-sm" onclick="copyPanel('db')" title="Copy"><i class="fas fa-copy"></i></button>
                <button class="btn btn-outline btn-sm" onclick="downloadPanel('db','database_schema')" title="Download .txt"><i class="fas fa-download"></i></button>
            </div>
        </div>
        <div class="panel-wrap">
            <div class="loading-mask" id="loading-db"><i class="fas fa-circle-notch fa-spin"></i><span>Reading database...</span></div>
            <textarea class="panel-output" id="output-db" readonly spellcheck="false">{{ $dbOutput }}</textarea>
        </div>
        <div class="panel-footer">
            <span id="meta-db-lines"><i class="fas fa-align-left"></i> {{ substr_count($dbOutput, "\n") + 1 }} lines</span>
            <span><i class="fas fa-database"></i> {{ config('database.connections.mysql.database') }}</span>
        </div>
    </div>
</div>

{{-- Export Log (appears when Export Files is clicked) --}}
<div id="exportLogSection" style="display:none;margin-top:16px;">
    <div class="panel" style="min-height:auto;">
        <div class="panel-header">
            <div class="panel-title"><i class="fas fa-file-archive" style="color:var(--c-success,var(--c-success))"></i> Export Log</div>
            <div class="panel-actions">
                <span id="exportStatus" style="font-size:12px;color:var(--text-muted);"></span>
                <button class="btn btn-outline btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('exportLog').value);showToast('Log copied!')" title="Copy log"><i class="fas fa-copy"></i></button>
                <a id="exportDownloadBtn" href="#" class="btn btn-primary btn-sm" style="display:none;text-decoration:none;" title="Download zip"><i class="fas fa-download"></i> Download .zip</a>
            </div>
        </div>
        <div class="panel-wrap" style="max-height:500px;">
            <textarea class="panel-output" id="exportLog" readonly spellcheck="false" style="height:500px;font-size:11.5px;line-height:1.65;"></textarea>
        </div>
        <div class="panel-footer">
            <span id="exportFooterInfo" style="color:var(--text-faint);"></span>
            <span id="exportFooterTime" style="color:var(--text-faint);"></span>
        </div>
    </div>
</div>

{{-- ═══ AI PROJECT GUIDE ═══ --}}
<div style="margin-top:20px;">

<div style="background:var(--card-bg,#fff);border:1px solid var(--card-border,var(--border-color));border-radius:var(--card-radius,10px);margin-bottom:16px;overflow:hidden;">
    <div onclick="var g=document.getElementById('guideAnalysis');g.style.display=g.style.display==='none'?'block':'none';this.querySelector('.g-chev').classList.toggle('open')" style="padding:16px 20px;cursor:pointer;display:flex;justify-content:space-between;align-items:center;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:10px;background:var(--c-secondary-light,var(--c-secondary-light));color:var(--c-secondary,var(--c-secondary));display:flex;align-items:center;justify-content:center;font-size:18px;"><i class="fas fa-robot"></i></div>
            <div>
                <div style="font-size:var(--fs-base,14px);font-weight:700;color:var(--text-heading);">AI Project Analysis & Patch Guide</div>
                <div style="font-size:var(--fs-xs,12px);color:var(--text-muted);margin-top:2px;">Copy this to Claude — attach your exported ZIP + DB dump for full analysis</div>
            </div>
        </div>
        <i class="fas fa-chevron-down g-chev" style="color:var(--text-muted);font-size:12px;transition:transform .3s;"></i>
    </div>
    <div id="guideAnalysis" style="display:none;border-top:1px solid var(--border-color);">
        <div style="padding:16px 20px;background:var(--table-header-bg,var(--table-header-bg));display:flex;gap:8px;border-bottom:1px solid var(--border-color);">
            <button class="btn btn-primary" onclick="copyGuideText('guideAnalysisText')"><i class="fas fa-copy"></i> Copy Full Guide</button>
            <button class="btn btn-outline" onclick="downloadGuideText('guideAnalysisText','AI_PROJECT_GUIDE.md')"><i class="fas fa-download"></i> Download .md</button>
        </div>
        <div style="padding:20px;">
            <pre id="guideAnalysisText" style="background:var(--code-bg);color:var(--border-color);border-radius:8px;padding:24px;font-family:var(--font-mono,'JetBrains Mono',monospace);font-size:var(--fs-xs,12px);line-height:1.8;overflow-x:auto;white-space:pre-wrap;max-height:none;overflow-y:auto;">
# Task: Deep Analysis of My {{ \App\Models\Configuration::get('portal_name', config('app.name', 'Admin Portal')) }}

Please help me perform a comprehensive analysis of my {{ \App\Models\Configuration::get('portal_name', config('app.name', 'Admin Portal')) }} project.

## Project Materials Provided
I have attached:
- Full Laravel project source code (zipped) — exported from File Structure page
- Database schema (SQL file) — exported from Database page

---

## Analysis Requirements

### 1. Codebase Review
- Unzip and review the entire Laravel project
- Go through the codebase file by file and line by line
- Understand the architecture, logic flow, and coding patterns

### 2. Architecture Understanding
- Overall system architecture (MVC, modules, services, traits)
- How controllers, models, and views interact
- Middleware chain, service classes, helpers
- Routing structure and flow (all routes in routes/admin.php)

### 3. Database Integration
Using the provided schema:
- Map database tables (tbl_ prefix) to Laravel models
- Identify relationships
- Highlight missing relationships or inconsistencies

### 4. Feature Mapping
- Identify key modules/features
- Explain how each works internally
- Trace important flows (login, CRUD, patch apply, version rollback)

### 5. Code Quality & Issues
- Bad practices, security risks, performance concerns
- Redundant or unused code
- Suggest improvements

### 6. Security Review
- Authentication (custom cookie-based, NOT Laravel Guards)
- Authorization (RBAC via tbl_admin_role_menu_access)
- Password handling, validation, data exposure

### 7. Improvement Suggestions
- Refactoring, structure, performance, best practices

---

## Output Format
1. Project Overview
2. Architecture Breakdown
3. Database Mapping & Relationships
4. Key Feature Analysis
5. Code Issues & Risks
6. Security Review
7. Improvement Recommendations

---

## Important
- Be detailed and technical
- Assume production system on cPanel shared hosting (PHP 8.2, MySQL)
- Focus on practical, actionable insights
- Do not skip parts of the codebase

---
---
---

# SYSTEM REFERENCE — MUST READ BEFORE MAKING ANY CHANGES

## ⚠ CRITICAL: Patch Planning Rule

Every patch MUST start with a detailed planning document (MD):

✓ What problem does this patch solve?
✓ What files need to change and why?
✓ Are there database schema changes?
✓ What edge cases or risks exist?
✓ Can this be safely rolled back?
✓ What should the changelog entry say?

This MD becomes the patch description.
It is AUTO-INSERTED into the Changelog when the patch is applied.
Think deeply before generating code — the MD is the blueprint.

---

## Zip Structure

patch-name.zip
├── app/
│   ├── Models/SomeModel.php
│   ├── Http/Controllers/Admin/SomeController.php
│   ├── Services/SomeService.php
│   └── Traits/SomeTrait.php
├── resources/
│   └── views/admin/pages/module/page.blade.php
├── routes/
│   └── admin.php
├── database/
│   └── patches/
│       └── 2026_MM_DD_patch_name.sql
└── bootstrap/
    └── app.php

Rule: Zip file paths = target paths relative to project root.

---

## Code Files

- File exists → Overwrite (original backed up to DB via gzcompress)
- File doesn't exist → Create (parent dirs auto-created)
- Blade cache auto-cleared after apply
- Every patch creates a version (v + YmdHis) for full rollback
- System auto-creates a Changelog entry with file list

### Blocked paths (auto-skipped):
.env, .htaccess, artisan, composer.json, composer.lock,
vendor/*, node_modules/*, storage/logs/*, .git/*

---

## SQL Patches

Any .sql file in the zip → parsed, pre-validated, then executed.
Naming: database/patches/YYYY_MM_DD_description.sql

### Full SQL Template:

-- =============================================
-- Module Name
-- Created: YYYY-MM-DD
-- =============================================

-- Schema changes
ALTER TABLE `tbl_example`
    ADD COLUMN `new_col` VARCHAR(255) NULL AFTER `existing_col`;

-- Config rows
INSERT INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`,
     `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('group', 'key', 'default', 'text', 'Label',
     'Description.', NULL, 'default', 1, 1);

-- Menu entry
INSERT INTO `tbl_admin_menus`
    (`group_id`, `parent_id`, `level`, `title`, `icon`,
     `route_name`, `permission_key`, `sort_order`, `is_active`,
     `created_at`, `updated_at`)
VALUES
    (1, NULL, 1, 'Module Name', 'fas fa-icon',
     'admin.module.index', 'module_perm', 60, 1, NOW(), NOW());

-- Role access (administrator = role_id 1)
INSERT INTO `tbl_admin_role_menu_access`
    (`role_id`, `menu_id`, `can_view`, `can_create`,
     `can_edit`, `can_delete`, `created_at`, `updated_at`)
SELECT 1, id, 1, 1, 1, 1, NOW(), NOW()
FROM `tbl_admin_menus`
WHERE `route_name` = 'admin.module.index'
AND id NOT IN (
    SELECT menu_id FROM `tbl_admin_role_menu_access`
    WHERE role_id = 1
) LIMIT 1;

-- Changelog (auto-linked to version after apply)
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`,
     `technical_info`, `created_at`)
VALUES (
    'office', 'X.X.X', 'Module Name',
    'Detailed description of what changed and why.',
    '{"features":["feature-1","feature-2"]}', NOW()
);

-- Cache clear (ALWAYS include these)
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';

---

## Key Table Schemas

### tbl_admin_menus
group_id, parent_id, level, title, icon, route_name,
permission_key, sort_order, is_active, created_at, updated_at

### tbl_admin_role_menu_access
role_id, menu_id, can_view, can_create, can_edit, can_delete,
created_at, updated_at
(Use can_view/can_create/can_edit/can_delete — NO has_access column)

### tbl_configuration
group, key, value, type, label, description, options,
default_value, sort_order, is_active

### tbl_changelog
id, version_id (FK to tbl_versions, nullable), app_type,
version, title, details, technical_info (JSON), created_at
- version_id auto-set when patch is applied
- If SQL INSERT creates entry, system auto-links it to the version

### tbl_versions
id, version_code (YmdHis unique), version_label,
type (patch/rollback/legacy), file_name, file_hash,
description, rollback_target_code, rollback_from_code,
rollback_chain (JSON), code_files, sql_files,
files_created, files_overwritten, files_restored, files_deleted,
sql_ok, sql_err, total_backup_bytes,
status (success/partial/failed),
admin_id, admin_name, applied_at, elapsed_ms, log (JSON)

### tbl_version_code
id, version_id (FK CASCADE), file_path (relative),
action (create/overwrite/sql),
content_before (LONGBLOB gzcompress),
content_after (LONGBLOB gzcompress),
size_before, size_after, hash_before, hash_after

---

## Versioning & Rollback

- Every patch → version (v + YmdHis) automatically
- File content → gzcompress → LONGBLOB (~75% reduction)
- Restore to any version via Restore button
- Rollback = undo versions in REVERSE order (newest first)
- Rollback creates new version (audit trail preserved)
- SQL patches stored for reference, CANNOT auto-rollback

### Rollback Example:
v1 → v2 → v3 → v4 → v5 (current)
Restore to v3:
  1. Undo v5 (write v5's content_before)
  2. Undo v4 (write v4's content_before)
  3. Record as v6 (type=rollback, target=v3)
Result: v1 → v2 → v3 → v4 → v5 → v6

---

## Changelog Integration (Auto)

- Every patch auto-creates a Changelog entry after apply
- If SQL already inserted a changelog → system links it (adds version_id)
- If no SQL changelog → system auto-generates with file list
- Changelog page shows linked versions with:
  - File change list (New/Modified/SQL badges with sizes)
  - Before/After code viewer modal (tabbed)
  - "View in Version History" link to System Patch

---

## Project Conventions

Auth:
  $request->attributes->get('admin') for current admin
  $request->attributes->get('admin_id') for ID
  NEVER Auth::user() or raw cookie

Routes:
  All in routes/admin.php
  Named: admin.{module}.{action}

CSS:
  Page-level styles in @@push('styles')
  All colors via CSS variables from tbl_configuration
  var(--c-primary), var(--c-success), var(--text-heading), etc.
  ZERO hardcoded colors

JavaScript:
  Native fetch() with CSRF token
  No jQuery, No Axios

Views:
  Extend admin.layouts.app
  @@push('styles') for page CSS
  @@push('scripts') for page JS

Database:
  No Laravel migrations — SQL patches only
  Table prefix: tbl_
  All new tables need FK constraints

Deployment:
  ZIP patches via System Patch module
  No composer install on server — vendor committed
  cPanel shared hosting, PHP 8.2, MySQL

Encryption:
  Crypt::encrypt() / Crypt::decrypt() for sensitive config

Models:
  Custom cookie-based auth (NOT Laravel Guards)
  Admin model with AdminRole relationship
  Configuration::get('key', 'default') for config values
  Configuration::clearCache() after config changes

Cache:
  Database-backed (cache table)
  Sidebar: sidebar_menu_{roleId} (auto-invalidated via trait)
  Dashboard: dashboard_cp_{roleId}
  InvalidatesMenuCache trait on menu/role models

Blade Safety:
  When displaying @@push or @@section as TEXT in Blade views,
  ALWAYS escape with @@@@ to prevent Blade from executing them.
  Unescaped @@push in display text WILL break the sidebar layout.
</pre>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
const GENERATE_URL = '{{ route("admin.file-structure.generate") }}';
const EXPORT_URL = '{{ route("admin.file-structure.export-ai") }}';
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

// ── Utility ──
function updateLineCount(panel) {
    const area = document.getElementById('output-' + panel);
    const count = area.value.split('\n').length;
    document.getElementById('meta-' + panel + '-lines').innerHTML = '<i class="fas fa-align-left"></i> ' + count.toLocaleString() + ' lines';
}

function copyPanel(panel) {
    const area = document.getElementById('output-' + panel);
    navigator.clipboard.writeText(area.value).then(() => showToast('Copied!')).catch(() => {
        area.select(); document.execCommand('copy'); showToast('Copied!');
    });
}

function downloadPanel(panel, filename) {
    const area = document.getElementById('output-' + panel);
    const blob = new Blob([area.value], { type:'text/plain' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename + '_' + new Date().toISOString().slice(0,10) + '.txt';
    a.click();
    URL.revokeObjectURL(a.href);
    showToast('Downloaded!');
}

function downloadAll() {
    downloadPanel('files', 'file_structure');
    setTimeout(() => downloadPanel('db', 'database_schema'), 300);
}

function refreshPanel(type) {
    const mask = document.getElementById('loading-' + type);
    mask.classList.add('show');
    console.log('[FileStructure] Refreshing panel:', type);

    fetch(GENERATE_URL + '?type=' + type, {
        headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' }
    })
    .then(r => { console.log('[FileStructure] Refresh response status:', r.status); return r.json(); })
    .then(data => {
        if (data.fileOutput) { document.getElementById('output-files').value = data.fileOutput; updateLineCount('files'); }
        if (data.dbOutput)   { document.getElementById('output-db').value = data.dbOutput; updateLineCount('db'); }
        showToast('Refreshed!');
    })
    .catch(err => { console.error('[FileStructure] Refresh error:', err); showToast('Error: ' + err.message); })
    .finally(() => mask.classList.remove('show'));
}

function refreshAll() {
    const btn = document.getElementById('btnRefresh');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
    console.log('[FileStructure] Refreshing all panels...');

    document.getElementById('loading-files').classList.add('show');
    document.getElementById('loading-db').classList.add('show');

    fetch(GENERATE_URL + '?type=both', {
        headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.fileOutput) { document.getElementById('output-files').value = data.fileOutput; updateLineCount('files'); }
        if (data.dbOutput)   { document.getElementById('output-db').value = data.dbOutput; updateLineCount('db'); }
        showToast('Both panels refreshed!');
    })
    .catch(err => { console.error('[FileStructure] RefreshAll error:', err); showToast('Error: ' + err.message); })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh All';
        document.getElementById('loading-files').classList.remove('show');
        document.getElementById('loading-db').classList.remove('show');
    });
}

function showToast(msg) {
    document.querySelectorAll('.toast').forEach(t => t.remove());
    const t = document.createElement('div');
    t.className = 'toast toast-success';
    t.innerHTML = '<i class="fas fa-check-circle"></i> ' + msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity='0'; t.style.transition='opacity .3s'; setTimeout(() => t.remove(), 300); }, 2500);
}

function fmtSize(b) { return b>=1048576?(b/1048576).toFixed(2)+' MB':b>=1024?(b/1024).toFixed(1)+' KB':b+' B'; }

// ── Export Files — main function ──
async function exportFiles() {
    const btn = document.getElementById('btnExportFiles');
    const section = document.getElementById('exportLogSection');
    const logEl = document.getElementById('exportLog');
    const statusEl = document.getElementById('exportStatus');
    const dlBtn = document.getElementById('exportDownloadBtn');
    const footerInfo = document.getElementById('exportFooterInfo');
    const footerTime = document.getElementById('exportFooterTime');

    const exportStart = performance.now();

    console.log('══════════════════════════════════════════');
    console.log('[Export] Starting export...');
    console.log('[Export] URL:', EXPORT_URL);
    console.log('[Export] CSRF:', CSRF ? CSRF.substring(0, 10) + '...' : 'MISSING!');
    console.log('══════════════════════════════════════════');

    // Reset UI
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Building...';
    dlBtn.style.display = 'none';
    logEl.value = '⏳ Building export zip — please wait...\n\n   This usually takes 5-30 seconds.\n   Check F12 Console for live progress.\n';
    statusEl.innerHTML = '<i class="fas fa-spinner fa-spin" style="color:var(--c-secondary,var(--c-secondary))"></i> Building...';
    footerInfo.textContent = '';
    footerTime.textContent = '';
    section.style.display = 'block';
    section.scrollIntoView({ behavior:'smooth', block:'nearest' });

    if (!CSRF) {
        const errMsg = 'CSRF token not found! Check if <meta name="csrf-token"> exists in layout.';
        console.error('[Export] FATAL:', errMsg);
        logEl.value = '✗ FATAL: ' + errMsg;
        statusEl.innerHTML = '<i class="fas fa-times-circle" style="color:var(--c-primary,var(--c-danger))"></i> Failed';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-archive"></i> Export Files';
        return;
    }

    let response;
    try {
        console.log('[Export] Sending POST request...');
        console.time('[Export] Server response time');

        response = await fetch(EXPORT_URL, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: '{}'
        });

        console.timeEnd('[Export] Server response time');
        console.log('[Export] Response status:', response.status, response.statusText);
        console.log('[Export] Response headers:', Object.fromEntries([...response.headers]));

    } catch (networkErr) {
        // Network-level failure (timeout, DNS, connection refused, etc.)
        const elapsed = Math.round(performance.now() - exportStart);
        console.error('[Export] NETWORK ERROR:', networkErr);
        console.error('[Export] Error type:', networkErr.constructor.name);
        console.error('[Export] Message:', networkErr.message);

        let errLog = '════════════════════════════════════════════\n';
        errLog += ' ✗ NETWORK ERROR\n';
        errLog += '════════════════════════════════════════════\n\n';
        errLog += ' Type: ' + networkErr.constructor.name + '\n';
        errLog += ' Message: ' + networkErr.message + '\n';
        errLog += ' Elapsed: ' + elapsed + 'ms\n\n';
        errLog += ' Possible causes:\n';
        errLog += '   • Request timed out (server took too long)\n';
        errLog += '   • Server returned a non-HTTP response\n';
        errLog += '   • Connection was interrupted\n';
        errLog += '   • CORS or CSP policy blocked the request\n\n';
        errLog += ' Check F12 → Network tab for details.\n';

        logEl.value = errLog;
        statusEl.innerHTML = '<i class="fas fa-times-circle" style="color:var(--c-primary,var(--c-danger))"></i> Network Error';
        footerTime.textContent = elapsed + 'ms';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-archive"></i> Export Files';
        return;
    }

    // ── Response received — check if it's valid ──
    const elapsed = Math.round(performance.now() - exportStart);

    if (!response.ok) {
        // Server returned an error status (4xx, 5xx)
        console.error('[Export] Server error! Status:', response.status);
        let errorBody = '';
        try {
            const contentType = response.headers.get('content-type') || '';
            console.log('[Export] Error response content-type:', contentType);

            if (contentType.includes('application/json')) {
                const errData = await response.json();
                console.error('[Export] Error JSON:', errData);

                // Server returned structured error with log
                if (errData.log && Array.isArray(errData.log)) {
                    errorBody = buildLogText(errData, elapsed, true);
                } else {
                    errorBody = '✗ Server Error ' + response.status + '\n\n';
                    errorBody += errData.error || errData.message || JSON.stringify(errData, null, 2);
                }
            } else {
                // HTML error page (Laravel exception, 500 page, etc.)
                const htmlBody = await response.text();
                console.error('[Export] HTML error body (first 500 chars):', htmlBody.substring(0, 500));

                errorBody = '════════════════════════════════════════════\n';
                errorBody += ' ✗ SERVER ERROR ' + response.status + ' ' + response.statusText + '\n';
                errorBody += '════════════════════════════════════════════\n\n';

                // Try to extract the error message from Laravel's error page
                const titleMatch = htmlBody.match(/<title>(.*?)<\/title>/i);
                const msgMatch = htmlBody.match(/class="exception-message[^"]*"[^>]*>([^<]+)/i) ||
                                 htmlBody.match(/<h1[^>]*>([^<]+)<\/h1>/i);

                if (titleMatch) errorBody += ' Page Title: ' + titleMatch[1].trim() + '\n';
                if (msgMatch) errorBody += ' Error: ' + msgMatch[1].trim() + '\n';
                errorBody += ' Elapsed: ' + elapsed + 'ms\n\n';
                errorBody += ' The server returned an HTML error page instead of JSON.\n';
                errorBody += ' Check Laravel logs: storage/logs/laravel.log\n';
                errorBody += ' Or check F12 → Network → click the failed request → Response tab.\n';
            }
        } catch (parseErr) {
            console.error('[Export] Could not parse error response:', parseErr);
            errorBody = '✗ Server returned status ' + response.status + ' and response could not be parsed.\n';
            errorBody += 'Parse error: ' + parseErr.message + '\n';
        }

        logEl.value = errorBody;
        statusEl.innerHTML = '<i class="fas fa-times-circle" style="color:var(--c-primary,var(--c-danger))"></i> Error ' + response.status;
        footerTime.textContent = elapsed + 'ms';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-archive"></i> Export Files';
        return;
    }

    // ── Parse successful response ──
    let data;
    try {
        data = await response.json();
        console.log('[Export] Response data:', data);
    } catch (jsonErr) {
        console.error('[Export] JSON parse error:', jsonErr);
        logEl.value = '✗ Server returned status 200 but response is not valid JSON.\n\n' +
                      'Parse error: ' + jsonErr.message + '\n\n' +
                      'This usually means the PHP script output unexpected content\n' +
                      '(e.g. a PHP warning/notice before the JSON).\n\n' +
                      'Check F12 → Network → click the request → Response tab.';
        statusEl.innerHTML = '<i class="fas fa-times-circle" style="color:var(--c-primary,var(--c-danger))"></i> Parse Error';
        footerTime.textContent = elapsed + 'ms';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-archive"></i> Export Files';
        return;
    }

    // ── Check success flag ──
    if (!data.success) {
        console.error('[Export] Export failed (success=false):', data);
        logEl.value = buildLogText(data, elapsed, true);
        statusEl.innerHTML = '<i class="fas fa-times-circle" style="color:var(--c-primary,var(--c-danger))"></i> Failed';
        footerTime.textContent = elapsed + 'ms';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-archive"></i> Export Files';
        return;
    }

    // ── SUCCESS — build detailed log ──
    console.log('[Export] ✓ Export successful!');
    console.log('[Export] Files:', data.file_count, '| Size:', data.total_size, '→', data.zip_size);
    console.log('[Export] Errors:', data.errors, '| Warnings:', data.warnings);
    console.log('[Export] Download URL:', data.download_url);

    logEl.value = buildLogText(data, elapsed, false);
    logEl.scrollTop = logEl.scrollHeight;

    // Status
    const errBadge = data.errors > 0 ? ' · <span style="color:var(--c-danger)">' + data.errors + ' errors</span>' : '';
    const warnBadge = data.warnings > 0 ? ' · <span style="color:var(--c-warning)">' + data.warnings + ' warnings</span>' : '';
    statusEl.innerHTML = '<i class="fas fa-check-circle" style="color:var(--c-success,var(--c-success))"></i> ' +
        data.file_count + ' files · ' + data.zip_size + errBadge + warnBadge;

    footerInfo.textContent = data.zip_name;
    footerTime.textContent = elapsed + 'ms';

    // Show download button
    dlBtn.href = data.download_url;
    dlBtn.style.display = 'inline-flex';
    dlBtn.onclick = function() { console.log('[Export] Download clicked:', data.download_url); };

    // Reset button
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-file-archive"></i> Export Files';
}

// Guide copy/download helpers
function copyGuideText(id) {
    var el = document.getElementById(id);
    var text = el.innerText || el.textContent;
    navigator.clipboard.writeText(text).then(function() { showToast('Guide copied to clipboard!'); }).catch(function() {
        var ta = document.createElement('textarea'); ta.value = text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); showToast('Guide copied!');
    });
}
function downloadGuideText(id, filename) {
    var el = document.getElementById(id);
    var text = el.innerText || el.textContent;
    var blob = new Blob([text], {type:'text/markdown'});
    var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = filename; a.click(); URL.revokeObjectURL(a.href);
    showToast('Downloaded ' + filename);
}

/**
 * Build formatted log text from server response.
 */
function buildLogText(data, elapsed, isError) {
    const icons = { phase:'═', ok:'✓', err:'✗', info:'●', skip:'○', warn:'⚠' };
    const now = new Date().toLocaleString();

    let log = '════════════════════════════════════════════════════════════\n';
    log += isError ? ' EXPORT FAILED\n' : ' EXPORT LOG\n';
    log += ' Time: ' + now + '\n';
    log += '════════════════════════════════════════════════════════════\n\n';

    if (data.error) {
        log += ' ✗ ERROR: ' + data.error + '\n\n';
    }

    if (data.log && Array.isArray(data.log)) {
        data.log.forEach(e => {
            const icon = icons[e.type] || '·';
            const ms = e.ms !== undefined ? ' [' + e.ms + 'ms]' : '';

            if (e.type === 'phase') {
                log += '\n ══ ' + e.msg + ' ══\n';
            } else {
                log += ' ' + icon + ' ' + e.msg + ms;
                if (e.desc) log += '\n   → ' + e.desc;
                log += '\n';
            }
        });
    }

    if (!isError && data.file_count) {
        log += '\n════════════════════════════════════════════════════════════\n';
        log += ' Summary\n';
        log += '────────────────────────────────────────────────────────────\n';
        log += ' Files: ' + data.file_count + '\n';
        log += ' Uncompressed: ' + data.total_size + '\n';
        log += ' Compressed: ' + data.zip_size + '\n';
        log += ' ZIP: ' + data.zip_name + '\n';
        log += ' Server time: ' + data.elapsed_ms + 'ms\n';
        log += ' Total time: ' + elapsed + 'ms (incl. network)\n';
        if (data.errors > 0) log += ' Errors: ' + data.errors + '\n';
        if (data.warnings > 0) log += ' Warnings: ' + data.warnings + '\n';
        log += '════════════════════════════════════════════════════════════\n';
    }

    return log;
}
</script>
@endpush
