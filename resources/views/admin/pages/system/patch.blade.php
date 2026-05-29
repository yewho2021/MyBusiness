@extends('admin.layouts.app')
@section('title', 'System Patch')

@push('styles')
<style>
.sp-card{background:var(--card-bg,#fff);border:1px solid var(--card-border,var(--border-color));border-radius:var(--card-radius,12px);padding:var(--content-padding,24px);margin-bottom:20px}
.sp-h{font-size:var(--fs-h3,16px);font-weight:700;color:var(--text-heading);margin-bottom:18px;display:flex;align-items:center;gap:10px}
.sp-h i{color:var(--c-primary,var(--c-danger))}
.sp-drop{border:2px dashed var(--border-color);border-radius:var(--card-radius,12px);padding:50px 24px;text-align:center;cursor:pointer;transition:all .2s;background:var(--hover-bg)}
.sp-drop:hover,.sp-drop.over{border-color:var(--c-secondary,var(--c-secondary));background:var(--c-secondary-light,var(--c-secondary-light))}
.sp-file{display:flex;align-items:center;gap:16px;padding:16px 20px;background:var(--hover-bg);border:1px solid var(--border-color);border-radius:var(--card-radius,12px);margin-top:16px}
.sp-file-ico{width:48px;height:48px;border-radius:12px;background:var(--c-secondary-light);color:var(--c-secondary);display:flex;align-items:center;justify-content:center;font-size:20px}
.btn{padding:10px 22px;border-radius:var(--btn-radius,8px);font-size:var(--fs-sm,13px);font-weight:600;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:8px;transition:all .15s}
.btn:disabled{opacity:.5;cursor:not-allowed}
.btn-blue{background:var(--c-secondary,var(--c-secondary));color:#fff}.btn-blue:hover{opacity:.9}
.btn-green{background:var(--c-success,var(--c-success));color:#fff}.btn-green:hover{opacity:.9}
.btn-amber{background:var(--c-warning,var(--c-warning));color:#fff}.btn-amber:hover{opacity:.9}
.btn-red{background:var(--c-danger,var(--c-danger));color:#fff}.btn-red:hover{opacity:.9}
.btn-gray{background:transparent;color:var(--text-secondary);border:1px solid var(--border-color)}.btn-gray:hover{background:var(--hover-bg)}
.sp-sect{font-size:var(--fs-base,14px);font-weight:700;color:var(--text-heading);margin:20px 0 10px;display:flex;align-items:center;gap:8px}
.sp-tbl{width:100%;border-collapse:collapse}
.sp-tbl th{text-align:left;padding:10px 12px;background:var(--table-header-bg);font-weight:600;color:var(--text-secondary);border-bottom:2px solid var(--border-color);font-size:var(--fs-sm,13px)}
.sp-tbl td{padding:10px 12px;border-bottom:1px solid var(--border-light);font-size:var(--fs-sm,13px);color:var(--text-body)}
.mono{font-family:var(--font-mono,monospace);font-size:var(--fs-xs,12px)}
.tag{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:6px;font-size:var(--fs-xs,12px);font-weight:600}
.tag-green{background:var(--c-success-light);color:var(--c-success)}.tag-amber{background:var(--c-warning-light);color:var(--c-warning)}
.tag-blue{background:var(--c-secondary-light);color:var(--c-secondary)}.tag-purple{background:var(--c-purple-light);color:var(--c-purple)}
.tag-red{background:var(--c-danger-light);color:var(--c-danger)}.tag-gray{background:var(--hover-bg);color:var(--text-muted)}
/* Version History */
.vh{width:100%;border-collapse:collapse}
.vh th{text-align:left;padding:14px 16px;background:var(--table-header-bg);font-weight:600;color:var(--text-secondary);border-bottom:2px solid var(--border-color);font-size:var(--fs-sm,13px)}
.vh td{padding:14px 16px;border-bottom:1px solid var(--border-light);vertical-align:top;font-size:var(--fs-sm,13px);color:var(--text-body)}
.vh tr:hover{background:var(--hover-bg)}
.vh-code{font-family:var(--font-mono);font-weight:700;font-size:var(--fs-sm,13px);color:var(--text-heading)}
.vh-desc{font-size:var(--fs-xs,12px);color:var(--text-muted);margin-top:3px;max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.vh-btn{padding:7px 14px;border-radius:var(--btn-radius,8px);font-size:var(--fs-xs,12px);font-weight:600;cursor:pointer;border:none;transition:all .15s}
.vh-detail{background:var(--c-info-light);color:var(--c-info)}.vh-detail:hover{background:var(--c-info);color:#fff}
.vh-restore{background:var(--c-warning-light);color:var(--c-warning)}.vh-restore:hover{background:var(--c-warning);color:#fff}
/* Modal */
.mdl-bg{display:none;position:fixed;inset:0;background:var(--modal-backdrop,rgba(15,23,42,.6));z-index:9999;align-items:center;justify-content:center;padding:24px}
.mdl-bg.show{display:flex}
.mdl{background:var(--card-bg);border-radius:var(--card-radius,12px);width:100%;max-width:780px;max-height:85vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 25px 60px rgba(0,0,0,.3)}
.mdl-head{padding:20px 24px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center}
.mdl-head h3{font-size:var(--fs-h3,16px);font-weight:700;color:var(--text-heading);margin:0}
.mdl-x{background:none;border:none;font-size:24px;color:var(--text-muted);cursor:pointer;padding:4px 8px;line-height:1}.mdl-x:hover{color:var(--text-heading)}
.mdl-body{padding:24px;overflow-y:auto;flex:1}
.mdl-foot{padding:16px 24px;border-top:1px solid var(--border-color);display:flex;justify-content:flex-end;gap:10px}
/* Code viewer */
.code-tabs{display:flex;border-bottom:2px solid var(--border-color)}
.code-tab{padding:10px 20px;font-size:var(--fs-sm);font-weight:600;cursor:pointer;border:none;background:transparent;color:var(--text-muted);border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .15s}
.code-tab.on{color:var(--c-secondary);border-bottom-color:var(--c-secondary)}
.code-pre{background:var(--code-bg);color:var(--border-color);border-radius:0 0 8px 8px;padding:18px 22px;font-family:var(--font-mono);font-size:var(--fs-xs,12px);line-height:1.7;overflow-x:auto;white-space:pre-wrap;word-break:break-all;max-height:450px;overflow-y:auto}
.guide-body{display:none}.guide-body.open{display:block}
.chev{transition:transform .3s}.chev.open{transform:rotate(180deg)}
.log-ok{color:var(--c-success)}.log-err{color:var(--c-danger)}.log-warn{color:var(--c-warning)}.log-info{color:var(--text-muted)}
</style>
@endpush

@section('content')

{{-- UPLOAD --}}
<div class="sp-card">
    <div class="sp-h"><i class="fas fa-upload"></i> Upload Patch</div>
    <div class="sp-drop" id="drop" onclick="document.getElementById('inp').click()">
        <div style="font-size:32px;color:var(--c-secondary);opacity:.4;margin-bottom:12px;"><i class="fas fa-cloud-upload-alt"></i></div>
        <div style="font-size:var(--fs-base);font-weight:600;color:var(--text-heading);">Drop ZIP here or click to browse</div>
        <div style="font-size:var(--fs-sm);color:var(--text-muted);margin-top:4px;">Only .zip files are accepted</div>
    </div>
    <input type="file" id="inp" accept=".zip" style="display:none">
    <div id="fileCard" style="display:none;">
        <div class="sp-file">
            <div class="sp-file-ico"><i class="fas fa-file-archive"></i></div>
            <div style="flex:1;"><div id="fName" style="font-weight:700;font-size:var(--fs-base);color:var(--text-heading);"></div><div id="fSize" style="font-size:var(--fs-sm);color:var(--text-muted);margin-top:2px;"></div></div>
            <button class="btn btn-gray" onclick="clr()"><i class="fas fa-times"></i></button>
        </div>
        <div style="display:flex;gap:10px;margin-top:16px;">
            <button class="btn btn-blue" id="bPrev" disabled onclick="doPreview()"><i class="fas fa-bolt"></i> Preview & Apply</button>
            <button class="btn btn-green" id="bApply" style="display:none;" onclick="doApply()"><i class="fas fa-bolt"></i> Apply Patch</button>
        </div>
    </div>
</div>

{{-- PREVIEW --}}
<div id="secPrev" style="display:none;"><div class="sp-card"><div class="sp-h"><i class="fas fa-list-check"></i> Patch Preview</div><div id="prevBody"></div></div></div>

{{-- RESULT --}}
<div id="secRes" style="display:none;"></div>

{{-- VERSION HISTORY ERROR --}}
@if(isset($historyError) && $historyError)
<div class="sp-card" style="border-left:4px solid var(--c-danger);">
    <div class="sp-h"><i class="fas fa-exclamation-triangle" style="color:var(--c-danger);"></i> Version Query Error</div>
    <div style="background:var(--c-danger-light);border-radius:8px;padding:16px;font-family:var(--font-mono);font-size:var(--fs-sm);color:var(--c-danger);word-break:break-all;">{{ $historyError }}</div>
</div>
@endif

{{-- VERSION HISTORY --}}
@if(isset($history) && count($history) > 0)
<div class="sp-card">
    <div class="sp-h">
        <i class="fas fa-code-branch"></i> Version History ({{ count($history) }})
        @if($currentVersion)
            <span style="margin-left:auto;font-size:var(--fs-sm);font-weight:500;color:var(--text-muted);">Current: <span style="font-family:var(--font-mono);color:var(--c-primary);font-weight:700;">v{{ $currentVersion->version_code }}</span></span>
        @endif
    </div>
    <div style="overflow-x:auto;">
    <table class="vh">
        <thead><tr><th>Version</th><th>Type</th><th>Description</th><th style="text-align:center;">Files</th><th style="text-align:center;">Backup</th><th style="text-align:center;">Status</th><th>Date & Time</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody>
        @foreach($history as $idx => $v)
        <tr>
            <td><span class="vh-code">v{{ $v->version_code }}</span></td>
            <td>
                @if($v->type==='rollback')<span class="tag tag-purple"><i class="fas fa-undo" style="font-size:10px;"></i> Rollback</span>
                @elseif($v->type==='legacy')<span class="tag tag-gray"><i class="fas fa-archive" style="font-size:10px;"></i> Legacy</span>
                @else<span class="tag tag-green"><i class="fas fa-arrow-up" style="font-size:10px;"></i> Patch</span>@endif
            </td>
            <td>
                <div style="font-weight:600;color:var(--text-heading);">
                    @if($v->isRollback()) Restored to <span class="vh-code">v{{ $v->rollback_target_code }}</span>
                    @else {{ $v->file_name ?? '—' }} @endif
                </div>
                @if($v->description)<div class="vh-desc" title="{{ $v->description }}">{{ mb_strlen($v->description) > 60 ? mb_substr($v->description, 0, 60) . '...' : $v->description }}</div>@endif
            </td>
            <td style="text-align:center;">{{ $v->code_files }}@if($v->sql_files > 0)<span style="font-size:var(--fs-xs);color:var(--c-purple);"> +{{ $v->sql_files }}sql</span>@endif</td>
            <td style="text-align:center;font-family:var(--font-mono);font-size:var(--fs-xs);">{{ $v->total_backup_bytes > 0 ? $v->getBackupSizeHuman() : '—' }}</td>
            <td style="text-align:center;">
                @if($v->status==='success')<span class="tag tag-green"><i class="fas fa-check" style="font-size:10px;"></i> OK</span>
                @else<span class="tag tag-amber"><i class="fas fa-exclamation-triangle" style="font-size:10px;"></i> {{ ucfirst($v->status) }}</span>@endif
            </td>
            <td>
                <div style="font-size:var(--fs-sm);color:var(--text-heading);">{{ $v->applied_at?->format('d M Y') }}</div>
                <div style="font-size:var(--fs-xs);color:var(--text-muted);">{{ $v->applied_at?->format('H:i:s') }} · {{ $v->admin_name }} · {{ $v->elapsed_ms }}ms</div>
            </td>
            <td style="text-align:right;white-space:nowrap;">
                <button class="vh-btn vh-detail" onclick="openDetail('{{ $v->version_code }}')"><i class="fas fa-eye"></i> Detail</button>
                @if($idx > 0 && $v->canRestore())
                    <button class="vh-btn vh-restore" onclick="openRB('{{ $v->version_code }}')" style="margin-left:6px;"><i class="fas fa-undo"></i> Restore</button>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- GUIDE --}}
<div class="sp-card">
    <div onclick="document.getElementById('gBody').classList.toggle('open');this.querySelector('.chev').classList.toggle('open')" style="cursor:pointer;">
        <div class="sp-h" style="margin-bottom:0;"><i class="fas fa-book"></i> Patch Format Guide <i class="fas fa-chevron-down chev" style="margin-left:auto;font-size:12px;color:var(--text-muted);"></i></div>
        <span style="font-size:var(--fs-xs);color:var(--text-muted);margin-left:34px;">Click to expand — copy & paste to Claude for building patches</span>
    </div>
    <div id="gBody" class="guide-body">
        <div id="guideText" style="background:var(--hover-bg);border:1px solid var(--border-color);border-radius:8px;padding:24px;margin-top:16px;font-size:var(--fs-sm);line-height:1.8;color:var(--text-body);">

<div style="font-size:var(--fs-base);font-weight:700;color:var(--c-danger);margin-bottom:12px;">⚠ IMPORTANT: Before Building Any Patch</div>
<div style="background:var(--c-warning-light);border:1px solid var(--c-warning-border,var(--c-warning-border));border-radius:8px;padding:16px;margin-bottom:20px;">
Every patch <strong>MUST</strong> start with a detailed planning document (MD) that covers:<br><br>
<div style="padding-left:12px;">
    ✓ What problem does this patch solve?<br>
    ✓ What files need to change and why?<br>
    ✓ Are there database schema changes?<br>
    ✓ What edge cases or risks exist?<br>
    ✓ Can this be safely rolled back?<br>
    ✓ What should the changelog say?<br>
</div>
<br>
This MD document becomes the patch description and is <strong>auto-inserted into the Changelog</strong> as the patch details. Think deeply before generating code — the MD is the blueprint.
</div>

<div style="font-weight:700;color:var(--text-heading);margin-bottom:8px;font-size:var(--fs-base);">Zip Structure</div>
<pre style="font-family:var(--font-mono);font-size:var(--fs-xs);margin:0 0 16px;color:var(--text-secondary);background:var(--code-bg);padding:16px;border-radius:8px;color:var(--border-color);">patch-name.zip
├── app/
│   └── Http/Controllers/Admin/SomeController.php
├── resources/
│   └── views/admin/pages/module/page.blade.php
├── routes/
│   └── admin.php
├── database/
│   └── patches/
│       └── 2026_MM_DD_patch_name.sql
└── bootstrap/
    └── app.php</pre>
<strong>Rule:</strong> Zip file paths = target paths relative to project root.

<hr style="border:none;border-top:1px solid var(--border-color);margin:20px 0;">

<div style="font-weight:700;color:var(--text-heading);margin-bottom:8px;font-size:var(--fs-base);">Code Files</div>
<div style="padding-left:12px;margin-bottom:12px;">
    • File exists → <span class="tag tag-amber" style="font-size:11px;">Overwrite</span> (original content backed up to database via gzcompress)<br>
    • File doesn't exist → <span class="tag tag-green" style="font-size:11px;">Create</span> (parent dirs auto-created)<br>
    • Blade cache auto-cleared after apply<br>
    • Every patch creates a version (<span class="mono">v20260329HHMMSS</span>) for full rollback
</div>
<div style="font-weight:600;color:var(--text-heading);margin-bottom:4px;">Blocked paths (auto-skipped):</div>
<div style="padding-left:12px;color:var(--text-muted);">
    .env, .htaccess, artisan, composer.json, composer.lock, vendor/*, node_modules/*, storage/logs/*, .git/*
</div>

<hr style="border:none;border-top:1px solid var(--border-color);margin:20px 0;">

<div style="font-weight:700;color:var(--text-heading);margin-bottom:8px;font-size:var(--fs-base);">SQL Patches</div>
Any <span class="mono">.sql</span> file in the zip → parsed, pre-validated, then executed.<br><br>
<div style="font-weight:600;color:var(--text-heading);margin-bottom:4px;">Naming: <span class="mono">database/patches/YYYY_MM_DD_description.sql</span></div>
<pre style="font-family:var(--font-mono);font-size:var(--fs-xs);margin:8px 0 16px;background:var(--code-bg);padding:16px;border-radius:8px;color:var(--border-color);">-- =============================================
-- Module Name
-- Created: YYYY-MM-DD
-- =============================================

-- Schema changes
ALTER TABLE `tbl_example`
    ADD COLUMN `new_col` VARCHAR(255) NULL DEFAULT NULL AFTER `existing_col`;

-- Config rows
INSERT INTO `tbl_configuration`
    (`group`, `key`, `value`, `type`, `label`, `description`, `options`, `default_value`, `sort_order`, `is_active`)
VALUES
    ('group', 'key', 'default', 'text', 'Label', 'Description.', NULL, 'default', 1, 1);

-- Menu entry
INSERT INTO `tbl_admin_menus`
    (`group_id`, `parent_id`, `level`, `title`, `icon`, `route_name`, `permission_key`, `sort_order`, `is_active`, `created_at`, `updated_at`)
VALUES
    (1, NULL, 1, 'Module Name', 'fas fa-icon', 'admin.module.index', 'module_perm', 60, 1, NOW(), NOW());

-- Role access (administrator = role_id 1)
INSERT INTO `tbl_admin_role_menu_access` (`role_id`, `menu_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`, `updated_at`)
SELECT 1, id, 1, 1, 1, 1, NOW(), NOW()
FROM `tbl_admin_menus`
WHERE `route_name` = 'admin.module.index'
AND id NOT IN (SELECT menu_id FROM `tbl_admin_role_menu_access` WHERE role_id = 1)
LIMIT 1;

-- Changelog
INSERT INTO `tbl_changelog`
    (`app_type`, `version`, `title`, `details`, `technical_info`, `created_at`)
VALUES (
    'office', '1.XX.0', 'Module Name',
    'Description of changes.', '{"info":"value"}', NOW()
);

-- Cache clear
DELETE FROM `cache` WHERE `key` LIKE 'sidebar_menu_%';
DELETE FROM `cache` WHERE `key` LIKE 'dashboard_%';</pre>

<hr style="border:none;border-top:1px solid var(--border-color);margin:20px 0;">

<div style="font-weight:700;color:var(--text-heading);margin-bottom:8px;font-size:var(--fs-base);">Key Table Schemas</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
    <div><strong class="mono">tbl_admin_menus</strong><br><span style="color:var(--text-muted);font-size:var(--fs-xs);">group_id, parent_id, level, title, icon, route_name, permission_key, sort_order, is_active, created_at, updated_at</span></div>
    <div><strong class="mono">tbl_admin_role_menu_access</strong><br><span style="color:var(--text-muted);font-size:var(--fs-xs);">role_id, menu_id, can_view, can_create, can_edit, can_delete, created_at, updated_at</span></div>
    <div><strong class="mono">tbl_configuration</strong><br><span style="color:var(--text-muted);font-size:var(--fs-xs);">group, key, value, type, label, description, options, default_value, sort_order, is_active</span></div>
    <div><strong class="mono">tbl_changelog</strong><br><span style="color:var(--text-muted);font-size:var(--fs-xs);">app_type, version, title, details, technical_info, created_at</span></div>
    <div><strong class="mono">tbl_versions</strong><br><span style="color:var(--text-muted);font-size:var(--fs-xs);">version_code, type, file_name, file_hash, description, rollback_target_code, status, admin_id, applied_at, log</span></div>
    <div><strong class="mono">tbl_version_code</strong><br><span style="color:var(--text-muted);font-size:var(--fs-xs);">version_id, file_path, action, content_before (LONGBLOB gzcompress), content_after, size_before, size_after, hash_before, hash_after</span></div>
</div>

<hr style="border:none;border-top:1px solid var(--border-color);margin:20px 0;">

<div style="font-weight:700;color:var(--text-heading);margin-bottom:8px;font-size:var(--fs-base);">Versioning & Rollback</div>
<div style="padding-left:12px;">
    • Every patch creates a version (<span class="mono">v</span> + <span class="mono">YmdHis</span>) automatically<br>
    • All file content is backed up to DB (gzcompress, ~75% reduction) before overwriting<br>
    • You can restore to any previous version at any time via the <strong>Restore</strong> button<br>
    • Rollback undoes versions in reverse order (newest first), creating a new version record<br>
    • SQL patches are stored for reference but <strong>cannot be auto-rolled back</strong> — manual SQL reversal needed<br>
    • Rollback from a rollback is supported (every version stores before/after)
</div>

<hr style="border:none;border-top:1px solid var(--border-color);margin:20px 0;">

<div style="font-weight:700;color:var(--text-heading);margin-bottom:8px;font-size:var(--fs-base);">Project Conventions</div>
<div style="padding-left:12px;">
    <strong>Auth:</strong> Use <span class="mono">$request->attributes->get('admin')</span> for current admin. Never <span class="mono">Auth::user()</span> or raw cookie.<br>
    <strong>Routes:</strong> All in <span class="mono">routes/admin.php</span>, named <span class="mono">admin.{module}.{action}</span><br>
    <strong>CSS:</strong> Page-level in <span class="mono">@@push('styles')</span>. All colors via CSS variables from Configuration.<br>
    <strong>JS:</strong> Native <span class="mono">fetch()</span> with CSRF. No jQuery/Axios.<br>
    <strong>Views:</strong> Extend <span class="mono">admin.layouts.app</span>, use <span class="mono">@@push('styles')</span> and <span class="mono">@@push('scripts')</span><br>
    <strong>DB:</strong> No migrations — SQL patches only. Table prefix: <span class="mono">tbl_</span><br>
    <strong>Deploy:</strong> ZIP patches via System Patch. No <span class="mono">composer install</span> on server — vendor committed.<br>
    <strong>Encryption:</strong> <span class="mono">Crypt::encrypt()</span> / <span class="mono">Crypt::decrypt()</span> for sensitive config values.
</div>

        </div>
        <div style="display:flex;gap:8px;margin-top:12px;">
            <button class="btn btn-blue" onclick="copyGuide()"><i class="fas fa-copy"></i> Copy Guide</button>
            <button class="btn btn-gray" onclick="downloadGuide()"><i class="fas fa-download"></i> Download .md</button>
        </div>
    </div>
</div>

{{-- MODALS --}}
<div class="mdl-bg" id="mDetail"><div class="mdl"><div class="mdl-head"><h3 id="mdT">Version Detail</h3><button class="mdl-x" onclick="shut('mDetail')">&times;</button></div><div class="mdl-body" id="mdB"></div><div class="mdl-foot"><button class="btn btn-gray" onclick="shut('mDetail')">Close</button><a class="btn btn-blue" id="mdDL" href="#" style="text-decoration:none;display:none;"><i class="fas fa-download"></i> Download</a></div></div></div>
<div class="mdl-bg" id="mFile"><div class="mdl" style="max-width:940px;"><div class="mdl-head"><h3 id="mfT">File Viewer</h3><button class="mdl-x" onclick="shut('mFile')">&times;</button></div><div class="mdl-body" id="mfB"></div><div class="mdl-foot"><button class="btn btn-gray" onclick="shut('mFile')">Close</button></div></div></div>
<div class="mdl-bg" id="mRB"><div class="mdl"><div class="mdl-head"><h3><i class="fas fa-undo" style="color:var(--c-warning);margin-right:8px;"></i> Restore Version</h3><button class="mdl-x" onclick="shut('mRB')">&times;</button></div><div class="mdl-body" id="mrB"></div><div class="mdl-foot" id="mrF" style="display:none;"><button class="btn btn-gray" onclick="shut('mRB')">Cancel</button><button class="btn btn-red" id="mrOK" onclick="execRB()"><i class="fas fa-undo"></i> Confirm Restore</button></div></div></div>

@endsection

@push('scripts')
<script>
const T='{{ csrf_token() }}', U_PREV='{{ route("admin.system-patch.preview") }}', U_APPLY='{{ route("admin.system-patch.apply") }}',
      U_VER='{{ url("system-patch/version") }}', U_RBP='{{ route("admin.system-patch.rollback-preview") }}', U_RBX='{{ route("admin.system-patch.rollback-execute") }}';
let file=null, rbCode=null;
const $=id=>document.getElementById(id);
function open(id){$(id).classList.add('show')} function shut(id){$(id).classList.remove('show')}
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.mdl-bg.show').forEach(m=>m.classList.remove('show'))});
const drop=$('drop'),inp=$('inp');
drop.addEventListener('dragover',e=>{e.preventDefault();drop.classList.add('over')});
drop.addEventListener('dragleave',()=>drop.classList.remove('over'));
drop.addEventListener('drop',e=>{e.preventDefault();drop.classList.remove('over');pick(e.dataTransfer.files[0])});
inp.addEventListener('change',e=>{if(e.target.files[0])pick(e.target.files[0])});

function pick(f){if(!f||!f.name.endsWith('.zip')){toast('Only .zip files','err');return}file=f;$('fName').textContent=f.name;$('fSize').textContent=fmt(f.size);$('fileCard').style.display='block';drop.style.display='none';$('bPrev').disabled=false;$('bApply').style.display='none';$('secPrev').style.display='none';$('secRes').style.display='none'}
function clr(){file=null;inp.value='';$('fileCard').style.display='none';drop.style.display='block';$('secPrev').style.display='none';$('bApply').style.display='none'}

async function doPreview(){
    if(!file)return;const b=$('bPrev');b.disabled=true;b.innerHTML='<i class="fas fa-spinner fa-spin"></i> Analyzing...';$('bApply').style.display='none';
    const fd=new FormData();fd.append('patch_file',file);fd.append('_token',T);
    try{const r=await fetch(U_PREV,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}});const d=await r.json();if(!d.success){toast(d.error||'Failed','err');return}renderPrev(d)}
    catch(e){toast('Failed: '+e.message,'err');console.error(e)}
    finally{b.disabled=false;b.innerHTML='<i class="fas fa-search"></i> Preview Changes'}
}

function renderPrev(d){
    let h='<div style="font-size:var(--fs-sm);color:var(--text-muted);margin-bottom:14px;">'+d.total_entries+' entries · '+d.total_size+'</div>';

    // PATCH.md content — show if present, warn if missing
    if(d.patch_description){
        h+='<div style="background:var(--c-success-light);border:1px solid var(--c-success-border,var(--c-success-border));border-radius:10px;padding:16px 20px;margin-bottom:16px;">';
        h+='<div style="font-weight:700;color:var(--c-success);margin-bottom:8px;font-size:var(--fs-sm);display:flex;align-items:center;gap:8px;"><i class="fas fa-file-alt"></i> PATCH.md — Description</div>';
        h+='<div style="white-space:pre-wrap;font-size:var(--fs-sm);color:var(--text-body);line-height:1.7;max-height:200px;overflow-y:auto;">'+esc(d.patch_description)+'</div></div>';
    } else {
        h+='<div style="background:var(--c-warning-light);border:1px solid var(--c-warning-border,var(--c-warning-border));border-radius:10px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-size:var(--fs-sm);">';
        h+='<i class="fas fa-exclamation-triangle" style="color:var(--c-warning);font-size:16px;"></i>';
        h+='<span style="color:var(--text-body);"><strong>No PATCH.md found.</strong> Every patch should include a PATCH.md at the ZIP root with a description of changes. This content auto-inserts into the version history and changelog.</span></div>';
    }

    // Duplicate warning
    if(d.previous_apply)h+='<div style="padding:14px 18px;border-radius:10px;background:var(--c-info-light);border:1px solid var(--c-info-border,#bae6fd);margin-bottom:16px;display:flex;align-items:center;gap:10px;font-size:var(--fs-sm);"><i class="fas fa-info-circle" style="color:var(--c-info);font-size:18px;"></i><span>'+esc(d.previous_apply.message)+'</span></div>';

    // Code files
    if(d.code_files.length){h+='<div class="sp-sect"><i class="fas fa-file-code" style="color:var(--c-secondary);"></i> Code Files ('+d.code_files.length+')</div>';
    h+='<table class="sp-tbl"><thead><tr><th>File Path</th><th>Size</th><th>Action</th></tr></thead><tbody>';
    d.code_files.forEach(f=>{const b=f.action==='overwrite'?'<span class="tag tag-amber">Overwrite</span>':f.action.includes('new dir')?'<span class="tag tag-blue">New + Dir</span>':'<span class="tag tag-green">New</span>';
    h+='<tr><td class="mono">'+esc(f.path)+'</td><td>'+fmt(f.size)+'</td><td>'+b+'</td></tr>'});h+='</tbody></table>'}
    if(d.sql_files.length){h+='<div class="sp-sect"><i class="fas fa-database" style="color:var(--c-purple);"></i> SQL ('+d.sql_files.length+')</div>';
    d.sql_files.forEach(sf=>{const ok=sf.valid?'<i class="fas fa-check-circle" style="color:var(--c-success);"></i>':'<i class="fas fa-exclamation-triangle" style="color:var(--c-danger);"></i>';
    h+='<div style="background:var(--hover-bg);border:1px solid var(--border-color);border-radius:10px;padding:16px 20px;margin-bottom:14px;"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;"><span class="mono" style="font-weight:700;">'+ok+' '+esc(sf.path)+'</span><span style="font-size:var(--fs-xs);">'+sf.stmt_count+' stmt(s)</span></div>';
    if(sf.statements&&sf.statements.length){h+='<table class="sp-tbl"><thead><tr><th style="width:36px;">#</th><th>Statement</th><th style="width:80px;">Status</th></tr></thead><tbody>';
    sf.statements.forEach(st=>{const si=st.status==='ok'?'<span class="tag tag-green">OK</span>':st.status==='warn'?'<span class="tag tag-amber">Warn</span>':'<span class="tag tag-red">Error</span>';
    h+='<tr><td>'+st.num+'</td><td class="mono">'+esc(st.preview);if(st.error)h+='<div style="color:var(--c-danger);margin-top:4px;font-size:var(--fs-xs);">'+esc(st.error)+'</div>';
    h+='</td><td>'+si+'</td></tr>'});h+='</tbody></table>'}h+='</div>'})}
    if(d.skipped&&d.skipped.length){h+='<div class="sp-sect"><i class="fas fa-ban" style="color:var(--c-danger);"></i> Blocked ('+d.skipped.length+')</div><table class="sp-tbl"><thead><tr><th>File</th><th>Reason</th></tr></thead><tbody>';
    d.skipped.forEach(f=>{h+='<tr><td class="mono">'+esc(f.path)+'</td><td><span class="tag tag-red">'+esc(f.reason)+'</span></td></tr>'});h+='</tbody></table>'}
    $('prevBody').innerHTML=h;$('secPrev').style.display='block';

    // Check if all green: no SQL errors, no blocked files, no duplicate
    const allSqlOk=!d.sql_files.some(f=>!f.valid);
    const noBlocked=!d.skipped||d.skipped.length===0;
    const noDupe=!d.previous_apply;
    const allGreen=allSqlOk&&noBlocked&&noDupe;

    if(allGreen){
        // Auto-apply — skip the button, go straight to apply
        $('bApply').style.display='none';
        doApply();
    } else {
        // Has warnings — show Apply button for manual review
        const ab=$('bApply');ab.style.display='inline-flex';ab.disabled=!allSqlOk;
    }
}

async function doApply(){
    if(!file)return;
    const b=$('bApply');b.disabled=true;b.innerHTML='<i class="fas fa-spinner fa-spin"></i> Applying...';b.style.display='inline-flex';
    $('bPrev').disabled=true;$('bPrev').innerHTML='<i class="fas fa-spinner fa-spin"></i> Applying...';
    const fd=new FormData();fd.append('patch_file',file);fd.append('_token',T);
    try{const r=await fetch(U_APPLY,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}});const d=await r.json();showRes(d)}
    catch(e){showRes({success:false,summary:'Failed: '+e.message,log:[{type:'err',msg:e.message}]});console.error(e)}
}

function showRes(d){
    let h='<div class="sp-card"><div class="sp-h"><i class="fas '+(d.success?'fa-check-circle" style="color:var(--c-success);"':'fa-exclamation-circle" style="color:var(--c-danger);"')+'></i> '+esc(d.summary||'Done')+'</div>';
    if(d.version_code)h+='<div style="font-size:var(--fs-sm);color:var(--text-muted);margin-bottom:14px;">Version: <span style="font-family:var(--font-mono);font-weight:700;color:var(--c-primary);font-size:var(--fs-base);">v'+d.version_code+'</span></div>';
    if(d.log){h+='<div style="background:var(--code-bg);border-radius:var(--card-radius);padding:18px 22px;max-height:340px;overflow-y:auto;">';
    d.log.forEach(l=>{const c=l.type==='ok'?'log-ok':l.type==='err'?'log-err':l.type==='warn'?'log-warn':'log-info';
    const i=l.type==='ok'?'✓':l.type==='err'?'✗':l.type==='warn'?'⚠':'›';
    h+='<div class="'+c+'" style="font-family:var(--font-mono);font-size:var(--fs-xs);padding:3px 0;">'+i+' '+esc(l.msg)+'</div>'});h+='</div>'}
    if(d.success){
        h+='<div id="reloadBar" style="margin-top:16px;display:flex;align-items:center;gap:14px;">';
        h+='<div style="flex:1;height:6px;background:var(--border-color);border-radius:3px;overflow:hidden;"><div id="reloadProgress" style="width:0%;height:100%;background:var(--c-success);border-radius:3px;transition:width 0.1s linear;"></div></div>';
        h+='<span id="reloadText" style="font-size:var(--fs-sm);color:var(--text-muted);white-space:nowrap;"><i class="fas fa-sync-alt fa-spin"></i> Reloading in <strong>2s</strong></span>';
        h+='<button class="btn btn-outline" onclick="cancelReload()" style="padding:6px 14px;font-size:var(--fs-xs);">Cancel</button>';
        h+='</div>';
    } else {
        h+='<div style="margin-top:16px;"><button class="btn btn-blue" onclick="location.reload()" style="font-size:var(--fs-base);padding:12px 24px;"><i class="fas fa-sync-alt"></i> Refresh Page</button></div>';
    }
    h+='</div>';
    $('secRes').innerHTML=h;$('secRes').style.display='block';$('secPrev').style.display='none';$('bApply').style.display='none';
    if(d.success) startReloadCountdown(2000);
}

var _reloadTimer=null,_reloadRAF=null;
function startReloadCountdown(ms){
    var start=performance.now(),dur=ms;
    function tick(){
        var elapsed=performance.now()-start,pct=Math.min(elapsed/dur*100,100);
        var bar=$('reloadProgress');if(bar)bar.style.width=pct+'%';
        var txt=$('reloadText');
        if(txt){var left=Math.max(0,Math.ceil((dur-elapsed)/1000));txt.innerHTML='<i class="fas fa-sync-alt fa-spin"></i> Reloading in <strong>'+left+'s</strong>';}
        if(elapsed<dur){_reloadRAF=requestAnimationFrame(tick)}
    }
    _reloadRAF=requestAnimationFrame(tick);
    _reloadTimer=setTimeout(function(){location.reload()},ms);
}
function cancelReload(){
    if(_reloadTimer){clearTimeout(_reloadTimer);_reloadTimer=null;}
    if(_reloadRAF){cancelAnimationFrame(_reloadRAF);_reloadRAF=null;}
    var bar=$('reloadBar');
    if(bar)bar.innerHTML='<button class="btn btn-blue" onclick="location.reload()" style="font-size:var(--fs-base);padding:12px 24px;"><i class="fas fa-sync-alt"></i> Refresh Page</button>';
}

// VERSION DETAIL
async function openDetail(c){$('mdT').textContent='Version v'+c;$('mdB').innerHTML='<div style="text-align:center;padding:40px;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i><div style="margin-top:12px;">Loading...</div></div>';$('mdDL').style.display='none';open('mDetail');
try{const r=await fetch(U_VER+'/'+c,{headers:{'X-Requested-With':'XMLHttpRequest'}});if(!r.ok)throw new Error('HTTP '+r.status);const d=await r.json();renderDet(d,c)}catch(e){$('mdB').innerHTML='<div style="color:var(--c-danger);padding:20px;"><i class="fas fa-exclamation-circle"></i> '+esc(e.message)+'</div>';console.error(e)}}

function renderDet(d,c){const v=d.version;
let h='<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:var(--fs-sm);margin-bottom:20px;padding:18px;background:var(--hover-bg);border-radius:var(--card-radius);">';
h+='<div><span style="color:var(--text-muted);">Type:</span> <span class="tag '+(v.type==='rollback'?'tag-purple':'tag-green')+'">'+v.type+'</span></div>';
h+='<div><span style="color:var(--text-muted);">Status:</span> <span class="tag tag-green">'+v.status+'</span></div>';
h+='<div><span style="color:var(--text-muted);">Applied:</span> '+esc(v.applied_at||'—')+'</div>';
h+='<div><span style="color:var(--text-muted);">By:</span> '+esc(v.admin_name||'—')+'</div>';
h+='<div><span style="color:var(--text-muted);">Duration:</span> '+v.elapsed_ms+'ms</div>';
h+='<div><span style="color:var(--text-muted);">Backup:</span> '+v.backup_size+'</div>';
if(v.file_name)h+='<div style="grid-column:span 2;"><span style="color:var(--text-muted);">File:</span> '+esc(v.file_name)+'</div>';
if(v.rollback_target)h+='<div style="grid-column:span 2;"><span style="color:var(--text-muted);">Restored to:</span> <span class="vh-code">'+v.rollback_target+'</span></div>';
h+='</div>';
// PATCH.md description
if(v.description){
h+='<div style="font-size:var(--fs-xs);font-weight:700;color:var(--text-heading);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;display:flex;align-items:center;gap:6px;"><i class="fas fa-file-alt"></i> Description (PATCH.md)</div>';
h+='<div style="white-space:pre-wrap;font-size:var(--fs-sm);color:var(--text-body);line-height:1.7;background:var(--hover-bg);border-radius:8px;padding:16px;margin-bottom:16px;max-height:200px;overflow-y:auto;">'+esc(v.description)+'</div>';
}
if(d.files.length){h+='<table class="sp-tbl"><thead><tr><th>File Path</th><th>Before</th><th>After</th><th>Action</th><th style="width:50px;"></th></tr></thead><tbody>';
d.files.forEach(f=>{const cl=f.action==='create'?'tag-green':f.action==='sql'?'tag-purple':'tag-amber';
h+='<tr><td class="mono">'+esc(f.path)+'</td><td class="mono">'+f.size_before+'</td><td class="mono">'+f.size_after+'</td><td><span class="tag '+cl+'">'+f.action_label+'</span></td>';
h+='<td>'+((f.has_before||f.has_after)?'<button class="vh-btn vh-detail" onclick="openFile(\''+c+'\','+f.id+',\''+esc(f.path).replace(/'/g,"\\'")+'\')"><i class="fas fa-code"></i></button>':'')+'</td></tr>'});
h+='</tbody></table>'}
$('mdB').innerHTML=h;$('mdDL').href=U_VER+'/'+c+'/download';$('mdDL').style.display='inline-flex'}

// FILE VIEWER
async function openFile(c,fid,path){$('mfT').textContent=path;$('mfB').innerHTML='<div style="text-align:center;padding:40px;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i></div>';open('mFile');
try{const r=await fetch(U_VER+'/'+c+'/file/'+fid,{headers:{'X-Requested-With':'XMLHttpRequest'}});if(!r.ok)throw new Error('HTTP '+r.status);const d=await r.json();
let h='<div class="code-tabs">';if(d.before!==null)h+='<button class="code-tab on" onclick="ctab(this,\'cb\')">Before ('+fmt(d.size_before)+')</button>';
if(d.after!==null)h+='<button class="code-tab'+(d.before===null?' on':'')+'" onclick="ctab(this,\'ca\')">After ('+fmt(d.size_after)+')</button>';h+='</div>';
if(d.before!==null)h+='<div id="cb" class="code-pre">'+esc(d.before)+'</div>';
if(d.after!==null)h+='<div id="ca" class="code-pre" style="'+(d.before!==null?'display:none;':'')+'">'+esc(d.after)+'</div>';
$('mfB').innerHTML=h}catch(e){$('mfB').innerHTML='<div style="color:var(--c-danger);padding:20px;">'+esc(e.message)+'</div>';console.error(e)}}
function ctab(btn,id){btn.parentElement.querySelectorAll('.code-tab').forEach(t=>t.classList.remove('on'));btn.classList.add('on');btn.closest('.mdl-body').querySelectorAll('.code-pre').forEach(b=>b.style.display='none');$(id).style.display='block'}

// ROLLBACK
async function openRB(tc){rbCode=tc;$('mrB').innerHTML='<div style="text-align:center;padding:40px;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i><div style="margin-top:12px;">Calculating rollback plan...</div></div>';$('mrF').style.display='none';open('mRB');
try{const r=await fetch(U_RBP,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':T,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({target_code:tc})});
const d=await r.json();if(!d.success){$('mrB').innerHTML='<div style="color:var(--c-danger);padding:20px;font-size:var(--fs-sm);">'+esc(d.error)+'</div>';return}renderRB(d)}
catch(e){$('mrB').innerHTML='<div style="color:var(--c-danger);padding:20px;">'+esc(e.message)+'</div>';console.error(e)}}

function renderRB(d){let h='<div style="background:var(--c-warning-light);border:1px solid var(--c-warning-border,var(--c-warning-border));border-radius:10px;padding:18px 22px;margin-bottom:20px;"><div style="font-weight:700;color:var(--c-warning);margin-bottom:6px;font-size:var(--fs-base);"><i class="fas fa-exclamation-triangle"></i> Restore confirmation</div><div style="font-size:var(--fs-sm);color:var(--text-body);">Undo all changes from <strong>'+d.current_version+'</strong> back to <strong>'+d.target_version+'</strong>.</div></div>';
h+='<div class="sp-sect"><i class="fas fa-layer-group"></i> Versions to undo ('+d.versions_to_undo.length+')</div><table class="sp-tbl"><tbody>';
d.versions_to_undo.forEach(v=>{h+='<tr><td class="vh-code" style="width:150px;">'+v.code+'</td><td>'+esc(v.file_name||'—')+'</td><td style="text-align:right;">'+v.code_files+' files</td></tr>'});
h+='</tbody></table>';
h+='<div class="sp-sect"><i class="fas fa-file-alt"></i> Files affected ('+d.affected_files.length+')</div><div style="font-size:var(--fs-sm);margin-bottom:10px;"><span style="color:var(--c-success);">'+d.files_to_restore+' restored</span>';
if(d.files_to_delete>0)h+=' · <span style="color:var(--c-danger);">'+d.files_to_delete+' deleted</span>';h+='</div>';
h+='<div style="max-height:160px;overflow-y:auto;background:var(--hover-bg);border-radius:8px;padding:12px 16px;">';
d.affected_files.forEach(f=>{const i=f.action==='delete'?'<i class="fas fa-trash" style="color:var(--c-danger);font-size:10px;"></i>':'<i class="fas fa-undo" style="color:var(--c-success);font-size:10px;"></i>';
h+='<div class="mono" style="padding:3px 0;">'+i+' '+esc(f.path)+'</div>'});h+='</div>';
if(d.sql_warnings.length){h+='<div style="background:var(--c-danger-light);border:1px solid var(--c-danger-border,var(--c-danger-border));border-radius:10px;padding:18px 22px;margin-top:16px;"><div style="font-weight:700;color:var(--c-danger);margin-bottom:8px;font-size:var(--fs-sm);"><i class="fas fa-database"></i> SQL patches — NOT auto-rolled back</div>';
d.sql_warnings.forEach(sw=>{h+='<div class="mono" style="padding:2px 0;">'+sw.version+': '+esc(sw.file)+'</div>'});
h+='<div style="font-size:var(--fs-xs);color:var(--c-danger);margin-top:8px;">Database changes must be manually reversed.</div></div>'}
if(d.missing_backups.length){h+='<div style="background:var(--c-danger-light);border:1px solid var(--c-danger-border);border-radius:10px;padding:18px;margin-top:16px;"><div style="font-weight:700;color:var(--c-danger);"><i class="fas fa-times-circle"></i> Cannot proceed — missing backups: '+d.missing_backups.join(', ')+'</div></div>'}
$('mrB').innerHTML=h;if(d.can_proceed)$('mrF').style.display='flex'}

async function execRB(){if(!rbCode)return;const b=$('mrOK');b.disabled=true;b.innerHTML='<i class="fas fa-spinner fa-spin"></i> Restoring...';
try{const r=await fetch(U_RBX,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':T,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({target_code:rbCode})});
const d=await r.json();shut('mRB');showRes(d)}catch(e){$('mrB').innerHTML='<div style="color:var(--c-danger);padding:20px;">Failed: '+esc(e.message)+'</div>';b.disabled=false;b.innerHTML='<i class="fas fa-undo"></i> Confirm Restore';console.error(e)}}

function esc(s){if(!s)return '';const d=document.createElement('div');d.textContent=s;return d.innerHTML}
function fmt(b){if(b>=1048576)return(b/1048576).toFixed(2)+' MB';if(b>=1024)return(b/1024).toFixed(1)+' KB';return b+' B'}
function toast(m,t){document.querySelectorAll('.sp-toast').forEach(x=>x.remove());const e=document.createElement('div');e.className='sp-toast';
e.innerHTML='<i class="fas '+(t==='err'?'fa-times-circle':'fa-check-circle')+'"></i> '+m;
e.style.cssText='position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:var(--btn-radius,8px);background:'+(t==='err'?'var(--c-danger)':'var(--c-success)')+';color:#fff;font-size:var(--fs-sm);font-weight:600;display:flex;align-items:center;gap:8px;z-index:99999;box-shadow:var(--shadow-lg);';
document.body.appendChild(e);setTimeout(()=>{e.style.opacity='0';e.style.transition='opacity .3s';setTimeout(()=>e.remove(),300)},3000)}

function copyGuide(){const el=$('guideText');const text=el.innerText||el.textContent;navigator.clipboard.writeText(text).then(()=>toast('Guide copied to clipboard!')).catch(()=>{const ta=document.createElement('textarea');ta.value=text;document.body.appendChild(ta);ta.select();document.execCommand('copy');document.body.removeChild(ta);toast('Guide copied!')})}
function downloadGuide(){const el=$('guideText');const text=el.innerText||el.textContent;const blob=new Blob([text],{type:'text/markdown'});const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='PATCH_FORMAT_GUIDE.md';a.click();URL.revokeObjectURL(a.href);toast('Downloaded PATCH_FORMAT_GUIDE.md')}
</script>
@endpush
