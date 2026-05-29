@extends('admin.layouts.app')
@section('title', 'Changelog')

@push('styles')
<style>
.cl-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.cl-h1{font-size:var(--fs-h2,20px);font-weight:700;color:var(--text-heading)}
.cl-sub{font-size:var(--fs-sm,13px);color:var(--text-muted);margin-top:4px}
.cl-tools{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.cl-search{padding:8px 14px;border-radius:var(--input-radius,8px);border:1px solid var(--input-border,var(--input-border));font-size:var(--fs-sm);width:200px;outline:none;color:var(--text-body);background:var(--card-bg)}
.cl-search:focus{border-color:var(--c-secondary);box-shadow:0 0 0 3px var(--focus-ring)}
.cl-filter{padding:7px 16px;border-radius:var(--btn-radius,8px);font-size:var(--fs-xs,12px);font-weight:600;cursor:pointer;border:1px solid var(--border-color);background:var(--card-bg);color:var(--text-secondary);text-decoration:none;transition:all .15s}
.cl-filter:hover{background:var(--hover-bg)}.cl-filter.on{background:var(--c-primary);color:#fff;border-color:var(--c-primary)}
/* Timeline */
.cl-timeline{position:relative}
.cl-entry{background:var(--card-bg);border:1px solid var(--card-border,var(--border-color));border-radius:var(--card-radius,12px);margin-bottom:16px;overflow:hidden;transition:box-shadow .2s}
.cl-entry:hover{box-shadow:var(--shadow-sm)}
.cl-entry-head{padding:16px 20px;cursor:pointer;display:flex;align-items:center;gap:14px;transition:background .1s}
.cl-entry-head:hover{background:var(--hover-bg)}
.cl-entry-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.cl-entry-icon.patch{background:var(--c-success-light);color:var(--c-success)}
.cl-entry-icon.rollback{background:var(--c-purple-light);color:var(--c-purple)}
.cl-entry-icon.legacy{background:var(--hover-bg);color:var(--text-muted)}
.cl-entry-body{flex:1;min-width:0}
.cl-entry-title{font-size:var(--fs-base,14px);font-weight:700;color:var(--text-heading);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cl-entry-meta{font-size:var(--fs-xs,12px);color:var(--text-muted);margin-top:3px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.cl-entry-right{text-align:right;flex-shrink:0}
.cl-vcode{font-family:var(--font-mono);font-weight:700;font-size:var(--fs-sm);color:var(--c-secondary)}
.cl-chev{color:var(--text-muted);font-size:12px;transition:transform .2s;flex-shrink:0}.cl-chev.open{transform:rotate(180deg)}
.tag{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:6px;font-size:var(--fs-xs,12px);font-weight:600}
.tag-green{background:var(--c-success-light);color:var(--c-success)}
.tag-purple{background:var(--c-purple-light);color:var(--c-purple)}
.tag-gray{background:var(--hover-bg);color:var(--text-muted)}
/* Detail */
.cl-detail{display:none;border-top:1px solid var(--border-color);padding:20px 20px 20px 74px}
.cl-detail.show{display:block}
.cl-desc{white-space:pre-wrap;line-height:1.7;font-size:var(--fs-sm);color:var(--text-body);background:var(--hover-bg);border-radius:8px;padding:16px;margin-bottom:16px}
.cl-section{font-size:var(--fs-xs);font-weight:700;color:var(--text-heading);text-transform:uppercase;letter-spacing:.5px;margin:16px 0 8px;display:flex;align-items:center;gap:6px}
/* File table */
.cl-ftbl{width:100%;border-collapse:collapse;font-size:var(--fs-sm,13px)}
.cl-ftbl th{text-align:left;padding:8px 12px;background:var(--table-header-bg);font-weight:600;color:var(--text-secondary);font-size:var(--fs-xs);border-bottom:2px solid var(--border-color)}
.cl-ftbl td{padding:8px 12px;border-bottom:1px solid var(--border-light);color:var(--text-body)}
.cl-fpath{font-family:var(--font-mono);font-size:var(--fs-xs);color:var(--text-heading);word-break:break-all}
.cl-fbtn{padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;border:none;background:var(--c-info-light);color:var(--c-info);transition:all .15s}
.cl-fbtn:hover{background:var(--c-info);color:#fff}
.cl-stats{display:flex;gap:16px;font-size:var(--fs-xs);color:var(--text-muted);margin-top:12px}
.cl-stats span{display:flex;align-items:center;gap:4px}
/* Modal */
.cl-mdl-bg{display:none;position:fixed;inset:0;background:var(--modal-backdrop,rgba(15,23,42,.6));z-index:9999;align-items:center;justify-content:center;padding:24px}
.cl-mdl-bg.show{display:flex}
.cl-mdl{background:var(--card-bg);border-radius:var(--card-radius,12px);width:100%;max-width:920px;max-height:85vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 25px 60px rgba(0,0,0,.3)}
.cl-mdl-head{padding:18px 24px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center}
.cl-mdl-head h3{font-size:var(--fs-h3,16px);font-weight:700;color:var(--text-heading);margin:0}
.cl-mdl-x{background:none;border:none;font-size:24px;color:var(--text-muted);cursor:pointer;padding:4px 8px;line-height:1}.cl-mdl-x:hover{color:var(--text-heading)}
.cl-mdl-body{padding:24px;overflow-y:auto;flex:1}
.cl-mdl-foot{padding:14px 24px;border-top:1px solid var(--border-color);display:flex;justify-content:flex-end}
.cl-tabs{display:flex;border-bottom:2px solid var(--border-color)}
.cl-tab{padding:10px 20px;font-size:var(--fs-sm);font-weight:600;cursor:pointer;border:none;background:transparent;color:var(--text-muted);border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .15s}
.cl-tab.on{color:var(--c-secondary);border-bottom-color:var(--c-secondary)}
.cl-code{background:var(--code-bg);color:var(--border-color);border-radius:0 0 8px 8px;padding:18px 22px;font-family:var(--font-mono);font-size:var(--fs-xs);line-height:1.7;overflow-x:auto;white-space:pre-wrap;word-break:break-all;max-height:450px;overflow-y:auto}
.cl-empty{padding:60px;text-align:center;color:var(--text-muted)}.cl-empty i{font-size:48px;display:block;margin-bottom:16px;opacity:.4}
.cl-pager{padding:16px 0;display:flex;justify-content:center}
</style>
@endpush

@section('content')
<div class="cl-head">
    <div>
        <h2 class="cl-h1">Changelog</h2>
        <p class="cl-sub">System updates, patches, and version history</p>
    </div>
    <div class="cl-tools">
        <form method="GET" style="display:flex;gap:8px;">
            <input type="text" name="q" class="cl-search" placeholder="Search versions..." value="{{ request('q') }}">
        </form>
        <a href="{{ route('admin.changelog.index') }}" class="cl-filter {{ !request('type') ? 'on' : '' }}">All</a>
        <a href="{{ route('admin.changelog.index', ['type' => 'patch']) }}" class="cl-filter {{ request('type') == 'patch' ? 'on' : '' }}">Patches</a>
        <a href="{{ route('admin.changelog.index', ['type' => 'rollback']) }}" class="cl-filter {{ request('type') == 'rollback' ? 'on' : '' }}">Rollbacks</a>
    </div>
</div>

@if($versions->isEmpty())
    <div style="background:var(--card-bg);border:1px solid var(--card-border);border-radius:var(--card-radius);"><div class="cl-empty"><i class="fas fa-code-branch"></i><p>No versions found.</p></div></div>
@else
    <div class="cl-timeline">
        @foreach($versions as $v)
        @php
            $files = $v->files;
            $created = $files->where('action', 'create');
            $modified = $files->where('action', 'overwrite');
            $sqlFiles = $files->where('action', 'sql');
            $iconClass = $v->type === 'rollback' ? 'rollback' : ($v->type === 'legacy' ? 'legacy' : 'patch');
            $icon = $v->type === 'rollback' ? 'fa-undo' : ($v->type === 'legacy' ? 'fa-archive' : 'fa-arrow-up');
        @endphp
        <div class="cl-entry">
            <div class="cl-entry-head" onclick="toggleEntry({{ $v->id }})">
                <div class="cl-entry-icon {{ $iconClass }}"><i class="fas {{ $icon }}"></i></div>
                <div class="cl-entry-body">
                    <div class="cl-entry-title">
                        @if($v->isRollback())
                            Restored to v{{ $v->rollback_target_code }}
                        @else
                            {{ $v->file_name ?? 'Patch ' . $v->getDisplayCode() }}
                        @endif
                    </div>
                    <div class="cl-entry-meta">
                        <span class="cl-vcode">{{ $v->getDisplayCode() }}</span>
                        @if($v->type === 'rollback')<span class="tag tag-purple">Rollback</span>
                        @elseif($v->type === 'legacy')<span class="tag tag-gray">Legacy</span>
                        @else<span class="tag tag-green">Patch</span>@endif
                        <span>{{ $v->applied_at?->format('d M Y, H:i:s') }}</span>
                        <span>{{ $v->admin_name }}</span>
                        <span>{{ $v->code_files }} files @if($v->sql_files > 0)+{{ $v->sql_files }}sql @endif</span>
                    </div>
                </div>
                <div class="cl-entry-right">
                    <div style="font-size:var(--fs-xs);color:var(--text-muted);">{{ $v->elapsed_ms }}ms</div>
                    @if($v->total_backup_bytes > 0)
                        <div style="font-size:var(--fs-xs);color:var(--text-muted);margin-top:2px;">{{ $v->getBackupSizeHuman() }}</div>
                    @endif
                </div>
                <i class="fas fa-chevron-down cl-chev" id="chev{{ $v->id }}"></i>
            </div>

            <div class="cl-detail" id="detail{{ $v->id }}">
                {{-- PATCH.md / Description --}}
                @if($v->description)
                    <div class="cl-section"><i class="fas fa-file-alt"></i> Description</div>
                    <div class="cl-desc">{{ $v->description }}</div>
                @endif

                {{-- Files Changed --}}
                @if($files->count() > 0)
                    <div class="cl-section"><i class="fas fa-code-branch"></i> Files Changed ({{ $files->count() }})</div>
                    <table class="cl-ftbl">
                        <thead><tr><th>File Path</th><th style="width:100px;text-align:center;">Size</th><th style="width:80px;text-align:center;">Action</th><th style="width:50px;"></th></tr></thead>
                        <tbody>
                        @foreach($files as $f)
                            <tr>
                                <td class="cl-fpath">{{ $f->file_path }}</td>
                                <td style="text-align:center;font-family:var(--font-mono);font-size:var(--fs-xs);">
                                    @if($f->size_before && $f->size_after)
                                        {{ $f->getSizeBeforeHuman() }} → {{ $f->getSizeAfterHuman() }}
                                    @elseif($f->size_after)
                                        {{ $f->getSizeAfterHuman() }}
                                    @else — @endif
                                </td>
                                <td style="text-align:center;">
                                    @if($f->action === 'create')<span class="tag tag-green">New</span>
                                    @elseif($f->action === 'sql')<span class="tag" style="background:var(--c-secondary-light);color:var(--c-secondary);">SQL</span>
                                    @else<span class="tag" style="background:var(--c-warning-light);color:var(--c-warning);">Modified</span>@endif
                                </td>
                                <td>
                                    @if($f->content_before !== null || $f->content_after !== null)
                                        <button class="cl-fbtn" onclick="event.stopPropagation();viewCode({{ $f->id }},'{{ addslashes($f->file_path) }}')"><i class="fas fa-code"></i></button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div class="cl-stats">
                        @if($created->count())<span><i class="fas fa-plus-circle" style="color:var(--c-success);"></i> {{ $created->count() }} new</span>@endif
                        @if($modified->count())<span><i class="fas fa-edit" style="color:var(--c-warning);"></i> {{ $modified->count() }} modified</span>@endif
                        @if($sqlFiles->count())<span><i class="fas fa-database" style="color:var(--c-secondary);"></i> {{ $sqlFiles->count() }} SQL</span>@endif
                        @if($v->total_backup_bytes > 0)<span><i class="fas fa-archive" style="color:var(--text-muted);"></i> {{ $v->getBackupSizeHuman() }} backed up</span>@endif
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="cl-pager">{{ $versions->links('pagination::simple-bootstrap-4') }}</div>
@endif

{{-- Code Viewer Modal --}}
<div class="cl-mdl-bg" id="codeModal">
    <div class="cl-mdl">
        <div class="cl-mdl-head">
            <h3 id="cmTitle">File Viewer</h3>
            <button class="cl-mdl-x" onclick="document.getElementById('codeModal').classList.remove('show')">&times;</button>
        </div>
        <div class="cl-mdl-body" id="cmBody"><div style="text-align:center;padding:40px;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i></div></div>
        <div class="cl-mdl-foot"><button style="padding:8px 18px;border-radius:var(--btn-radius);font-size:var(--fs-sm);font-weight:600;cursor:pointer;border:1px solid var(--border-color);background:var(--card-bg);color:var(--text-secondary);" onclick="document.getElementById('codeModal').classList.remove('show')">Close</button></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleEntry(id) {
    var d = document.getElementById('detail' + id);
    var c = document.getElementById('chev' + id);
    var isOpen = d.classList.contains('show');
    // Close all
    document.querySelectorAll('.cl-detail.show').forEach(function(el) { el.classList.remove('show'); });
    document.querySelectorAll('.cl-chev.open').forEach(function(el) { el.classList.remove('open'); });
    if (!isOpen) { d.classList.add('show'); c.classList.add('open'); }
}

async function viewCode(fileId, path) {
    document.getElementById('cmTitle').textContent = path;
    document.getElementById('cmBody').innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-muted);"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i></div>';
    document.getElementById('codeModal').classList.add('show');
    try {
        var res = await fetch('{{ route("admin.changelog.view-file") }}', {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({file_id: fileId})
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        var data = await res.json();
        var h = '<div class="cl-tabs">';
        if (data.before !== null) h += '<button class="cl-tab on" onclick="clTab(this,\'clB\')">Before (' + fmtSz(data.size_before) + ')</button>';
        if (data.after !== null) h += '<button class="cl-tab' + (data.before === null ? ' on' : '') + '" onclick="clTab(this,\'clA\')">After (' + fmtSz(data.size_after) + ')</button>';
        h += '</div>';
        if (data.before !== null) h += '<div id="clB" class="cl-code">' + escH(data.before) + '</div>';
        if (data.after !== null) h += '<div id="clA" class="cl-code" style="' + (data.before !== null ? 'display:none;' : '') + '">' + escH(data.after) + '</div>';
        document.getElementById('cmBody').innerHTML = h;
    } catch(e) { document.getElementById('cmBody').innerHTML = '<div style="color:var(--c-danger);padding:20px;">Failed: ' + escH(e.message) + '</div>'; }
}

function clTab(btn, id) {
    btn.parentElement.querySelectorAll('.cl-tab').forEach(function(t) { t.classList.remove('on'); });
    btn.classList.add('on');
    btn.closest('.cl-mdl-body').querySelectorAll('.cl-code').forEach(function(b) { b.style.display = 'none'; });
    document.getElementById(id).style.display = 'block';
}

function escH(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
function fmtSz(b) { if (!b) return '0B'; if (b >= 1048576) return (b/1048576).toFixed(2)+' MB'; if (b >= 1024) return (b/1024).toFixed(1)+' KB'; return b+' B'; }
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') document.getElementById('codeModal').classList.remove('show'); });
</script>
@endpush
