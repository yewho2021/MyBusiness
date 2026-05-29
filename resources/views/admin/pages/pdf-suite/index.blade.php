@extends('admin.layouts.app')
@section('title', 'PDF Suite')

@push('styles')
<style>
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
.page-header h1 { font-size: 24px; font-weight: 700; color: var(--header-text,var(--code-bg)); margin-bottom: 5px; }
.page-header p { font-size: 14px; color: var(--text-muted); }

.alert { padding: 14px 18px; border-radius: var(--card-radius,10px); margin-bottom: 18px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
.alert-success { background: var(--c-success-light); color: #15803d; border: 1px solid #86efac; }
.alert-danger { background: var(--c-danger-light); color: #b91c1c; border: 1px solid #fca5a5; }

/* ── Tool Grid (iLovePDF style) ── */
.tool-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 18px; margin-bottom: 28px; }
.tool-card { background: var(--card-bg,#fff); border: 2px solid var(--border-color); border-radius: 14px; padding: 28px 20px; text-align: center; cursor: pointer; transition: all .25s; }
.tool-card:hover { border-color: var(--c-primary,#dc2626); box-shadow: 0 8px 24px rgba(220,38,38,.1); transform: translateY(-3px); }
.tool-card.active { border-color: var(--c-primary,#dc2626); background: var(--c-danger-light); }
.tool-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 14px; }
.tool-icon.red { background: linear-gradient(135deg, var(--c-danger-light), #fee2e2); color: var(--c-primary,#dc2626); }
.tool-icon.blue { background: linear-gradient(135deg, var(--c-secondary-light), #dbeafe); color: var(--c-secondary,#2563eb); }
.tool-icon.green { background: linear-gradient(135deg, var(--c-success-light), #dcfce7); color: var(--c-success); }
.tool-icon.amber { background: linear-gradient(135deg, var(--c-warning-light), #fef3c7); color: var(--c-warning); }
.tool-icon.purple { background: var(--c-purple-light); color: var(--c-purple); }
.tool-icon.slate { background: linear-gradient(135deg, var(--table-header-bg), var(--border-color)); color: var(--text-secondary); }
.tool-name { font-size: 14px; font-weight: 700; color: var(--header-text,var(--code-bg)); margin-bottom: 4px; }
.tool-desc { font-size: 11px; color: var(--text-faint); line-height: 1.4; }

/* ── Tool Panel ── */
.tool-panel { display: none; background: var(--card-bg,#fff); border-radius: 14px; border: 1px solid var(--border-color,var(--border-color)); box-shadow: 0 1px 3px rgba(0,0,0,.04); margin-bottom: 24px; overflow: hidden; }
.tool-panel.show { display: block; }
.tool-panel-header { padding: 20px 24px; border-bottom: 1px solid var(--border-light,var(--border-light)); display: flex; justify-content: space-between; align-items: center; }
.tool-panel-header h2 { font-size: 18px; font-weight: 700; color: var(--header-text,var(--code-bg)); display: flex; align-items: center; gap: 10px; }
.tool-panel-header .close-panel { width: 32px; height: 32px; border-radius: 8px; background: var(--table-header-bg,var(--table-header-bg)); border: 1px solid var(--border-color,var(--border-color)); display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-muted); font-size: 16px; }
.tool-panel-header .close-panel:hover { background: var(--c-danger-light); color: var(--c-primary,#dc2626); }
.tool-panel-body { padding: 24px; }

/* ── Upload Area ── */
.upload-area { border: 2px dashed var(--input-border); border-radius: var(--card-radius,12px); padding: 40px 20px; text-align: center; cursor: pointer; transition: all .2s; background: var(--table-header-bg,var(--table-header-bg)); margin-bottom: 18px; }
.upload-area:hover, .upload-area.dragover { border-color: var(--c-secondary); background: var(--c-secondary-light); }
.upload-area i.upload-icon { font-size: 36px; color: var(--text-faint); margin-bottom: 10px; }
.upload-area p { font-size: 14px; color: var(--text-muted); margin-bottom: 4px; }
.upload-area .hint { font-size: 12px; color: var(--hover-border); }
.upload-area input[type="file"] { display: none; }

/* ── File List ── */
.file-list { margin-bottom: 18px; }
.file-item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; background: var(--table-header-bg,var(--table-header-bg)); border-radius: 8px; margin-bottom: 6px; border: 1px solid var(--border-color,var(--border-color)); }
.file-item i { color: var(--c-primary,#dc2626); font-size: 18px; }
.file-item .name { flex: 1; font-size: 13px; font-weight: 500; color: var(--header-text,var(--text-heading)); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.file-item .size { font-size: 12px; color: var(--text-faint); }
.file-item .remove { color: var(--text-faint); cursor: pointer; font-size: 14px; }
.file-item .remove:hover { color: var(--c-primary,#dc2626); }

/* ── Form Controls ── */
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--text-body); margin-bottom: 5px; }
.form-control { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border-color); border-radius: 8px; font-size: 14px; color: var(--header-text,var(--text-heading)); background: var(--card-bg,#fff); box-sizing: border-box; }
.form-control:focus { outline: none; border-color: var(--c-secondary); box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
@media(max-width:640px) { .form-row { grid-template-columns: 1fr; } }
.form-hint { font-size: 11px; color: var(--text-faint); margin-top: 4px; }

.range-wrap { display: flex; align-items: center; gap: 12px; }
.range-wrap input[type="range"] { flex: 1; height: 6px; -webkit-appearance: none; background: var(--border-color); border-radius: 3px; }
.range-wrap input[type="range"]::-webkit-slider-thumb { -webkit-appearance: none; width: 18px; height: 18px; border-radius: 50%; background: var(--c-primary,#dc2626); cursor: pointer; }
.range-value { min-width: 40px; text-align: center; font-size: 14px; font-weight: 700; color: var(--header-text,var(--text-heading)); }

.btn { padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 8px; transition: all .2s; }
.btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.btn-primary { background: linear-gradient(135deg, var(--c-primary) 0%, var(--c-primary-hover) 100%); color: #fff; }
.btn-block { width: 100%; justify-content: center; }
.btn-outline { background: transparent; color: var(--text-secondary); border: 1.5px solid var(--input-border); }

/* ── Text Result ── */
.text-result { background: var(--code-bg); border-radius: var(--card-radius,10px); padding: 18px; color: var(--border-color); font-family: monospace; font-size: 13px; line-height: 1.6; max-height: 400px; overflow-y: auto; white-space: pre-wrap; word-break: break-word; margin-top: 16px; }
.text-stats { display: flex; gap: 20px; padding: 12px 0; font-size: 13px; color: var(--text-muted); }
.text-stats span { font-weight: 600; color: var(--header-text,var(--text-heading)); }

/* ── Toast ── */
.toast { position: fixed; bottom: 24px; right: 24px; padding: 14px 22px; border-radius: var(--card-radius,10px); font-size: 14px; font-weight: 500; z-index: 10000; box-shadow: 0 8px 24px rgba(0,0,0,.15); display: none; align-items: center; gap: 8px; }
.toast.success { background: var(--c-success); color: #fff; }
.toast.error { background: var(--c-danger); color: #fff; }
.toast.show { display: flex; }
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
@endif

<div class="page-header">
    <div>
        <h1>PDF Suite</h1>
        <p>Every PDF tool you need — merge, split, compress, convert, and more</p>
    </div>
</div>

{{-- Tool Grid (categorized like iLovePDF) --}}
<div style="font-size:var(--fs-xs);font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;">Organize</div>
<div class="tool-grid">
    <div class="tool-card" onclick="openTool('merge')">
        <div class="tool-icon red"><i class="fas fa-layer-group"></i></div>
        <div class="tool-name">Merge PDF</div>
        <div class="tool-desc">Combine multiple PDFs into one</div>
    </div>
    <div class="tool-card" onclick="openTool('split')">
        <div class="tool-icon blue"><i class="fas fa-cut"></i></div>
        <div class="tool-name">Split PDF</div>
        <div class="tool-desc">Extract specific pages</div>
    </div>
    <div class="tool-card" onclick="openTool('rotate')">
        <div class="tool-icon green"><i class="fas fa-sync-alt"></i></div>
        <div class="tool-name">Rotate PDF</div>
        <div class="tool-desc">Rotate pages 90/180/270°</div>
    </div>
</div>

<div style="font-size:var(--fs-xs);font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;">Optimize</div>
<div class="tool-grid">
    <div class="tool-card" onclick="openTool('compress')">
        <div class="tool-icon red"><i class="fas fa-compress-alt"></i></div>
        <div class="tool-name">Compress PDF</div>
        <div class="tool-desc">Reduce file size</div>
    </div>
    <div class="tool-card" onclick="openTool('repair')">
        <div class="tool-icon amber"><i class="fas fa-wrench"></i></div>
        <div class="tool-name">Repair PDF</div>
        <div class="tool-desc">Fix corrupted files</div>
    </div>
    <div class="tool-card" onclick="openTool('flatten')">
        <div class="tool-icon slate"><i class="fas fa-eraser"></i></div>
        <div class="tool-name">Flatten PDF</div>
        <div class="tool-desc">Remove forms &amp; annotations</div>
    </div>
</div>

<div style="font-size:var(--fs-xs);font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;">Convert</div>
<div class="tool-grid">
    <div class="tool-card" onclick="openTool('jpgtopdf')">
        <div class="tool-icon green"><i class="fas fa-file-image"></i></div>
        <div class="tool-name">JPG to PDF</div>
        <div class="tool-desc">Convert images to PDF</div>
    </div>
    <div class="tool-card" onclick="openTool('pdftojpg')">
        <div class="tool-icon blue"><i class="fas fa-images"></i></div>
        <div class="tool-name">PDF to JPG</div>
        <div class="tool-desc">Convert pages to JPG</div>
    </div>
    <div class="tool-card" onclick="openTool('pdftopng')">
        <div class="tool-icon blue"><i class="fas fa-image"></i></div>
        <div class="tool-name">PDF to PNG</div>
        <div class="tool-desc">High-quality page images</div>
    </div>
    <div class="tool-card" onclick="openTool('htmltopdf')">
        <div class="tool-icon purple"><i class="fas fa-code"></i></div>
        <div class="tool-name">HTML to PDF</div>
        <div class="tool-desc">Convert HTML to PDF</div>
    </div>
</div>

<div style="font-size:var(--fs-xs);font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;">Edit</div>
<div class="tool-grid">
    <div class="tool-card" onclick="openTool('pagenumbers')">
        <div class="tool-icon amber"><i class="fas fa-list-ol"></i></div>
        <div class="tool-name">Page Numbers</div>
        <div class="tool-desc">Add page numbers</div>
    </div>
    <div class="tool-card" onclick="openTool('watermark')">
        <div class="tool-icon purple"><i class="fas fa-copyright"></i></div>
        <div class="tool-name">Watermark</div>
        <div class="tool-desc">Add text watermark</div>
    </div>
    <div class="tool-card" onclick="openTool('extract')">
        <div class="tool-icon slate"><i class="fas fa-align-left"></i></div>
        <div class="tool-name">Extract Text</div>
        <div class="tool-desc">Pull text from PDF</div>
    </div>
    <div class="tool-card" onclick="openTool('sign')">
        <div class="tool-icon green"><i class="fas fa-signature"></i></div>
        <div class="tool-name">Sign PDF</div>
        <div class="tool-desc">Add signature image</div>
    </div>
</div>

<div style="font-size:var(--fs-xs);font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;">Security</div>
<div class="tool-grid" style="margin-bottom:8px;">
    <div class="tool-card" onclick="openTool('protect')">
        <div class="tool-icon amber"><i class="fas fa-lock"></i></div>
        <div class="tool-name">Protect PDF</div>
        <div class="tool-desc">Add password protection</div>
    </div>
    <div class="tool-card" onclick="openTool('unlock')">
        <div class="tool-icon green"><i class="fas fa-unlock"></i></div>
        <div class="tool-name">Unlock PDF</div>
        <div class="tool-desc">Remove password</div>
    </div>
    <div class="tool-card" onclick="openTool('info')">
        <div class="tool-icon blue"><i class="fas fa-info-circle"></i></div>
        <div class="tool-name">PDF Info</div>
        <div class="tool-desc">View metadata &amp; details</div>
    </div>
</div>

{{-- ═══ TOOL PANELS ═══ --}}

@foreach([
    ['id'=>'merge','title'=>'Merge PDF','icon'=>'fa-layer-group','multi'=>true,'accept'=>'.pdf','fields'=>''],
    ['id'=>'split','title'=>'Split PDF','icon'=>'fa-cut','multi'=>false,'accept'=>'.pdf','fields'=>'split'],
    ['id'=>'rotate','title'=>'Rotate PDF','icon'=>'fa-sync-alt','multi'=>false,'accept'=>'.pdf','fields'=>'rotate'],
    ['id'=>'pagenumbers','title'=>'Add Page Numbers','icon'=>'fa-list-ol','multi'=>false,'accept'=>'.pdf','fields'=>'pagenumbers'],
    ['id'=>'watermark','title'=>'Watermark PDF','icon'=>'fa-copyright','multi'=>false,'accept'=>'.pdf','fields'=>'watermark'],
    ['id'=>'jpgtopdf','title'=>'JPG to PDF','icon'=>'fa-file-image','multi'=>true,'accept'=>'image/*','fields'=>'jpgtopdf'],
    ['id'=>'pdftojpg','title'=>'PDF to JPG','icon'=>'fa-images','multi'=>false,'accept'=>'.pdf','fields'=>'pdftojpg'],
    ['id'=>'pdftopng','title'=>'PDF to PNG','icon'=>'fa-image','multi'=>false,'accept'=>'.pdf','fields'=>'pdftopng'],
    ['id'=>'htmltopdf','title'=>'HTML to PDF','icon'=>'fa-code','multi'=>false,'accept'=>'','fields'=>'htmltopdf'],
    ['id'=>'extract','title'=>'Extract Text','icon'=>'fa-align-left','multi'=>false,'accept'=>'.pdf','fields'=>''],
    ['id'=>'compress','title'=>'Compress PDF','icon'=>'fa-compress-alt','multi'=>false,'accept'=>'.pdf','fields'=>'compress'],
    ['id'=>'repair','title'=>'Repair PDF','icon'=>'fa-wrench','multi'=>false,'accept'=>'.pdf','fields'=>''],
    ['id'=>'flatten','title'=>'Flatten PDF','icon'=>'fa-eraser','multi'=>false,'accept'=>'.pdf','fields'=>''],
    ['id'=>'sign','title'=>'Sign PDF','icon'=>'fa-signature','multi'=>false,'accept'=>'.pdf','fields'=>'sign'],
    ['id'=>'protect','title'=>'Protect PDF','icon'=>'fa-lock','multi'=>false,'accept'=>'.pdf','fields'=>'protect'],
    ['id'=>'unlock','title'=>'Unlock PDF','icon'=>'fa-unlock','multi'=>false,'accept'=>'.pdf','fields'=>'unlock'],
    ['id'=>'info','title'=>'PDF Info','icon'=>'fa-info-circle','multi'=>false,'accept'=>'.pdf','fields'=>''],
] as $tool)
<div class="tool-panel" id="panel-{{ $tool['id'] }}">
    <div class="tool-panel-header">
        <h2><i class="fas {{ $tool['icon'] }}" style="color:var(--c-primary,#dc2626);"></i> {{ $tool['title'] }}</h2>
        <button class="close-panel" onclick="closeTool()">×</button>
    </div>
    <div class="tool-panel-body">
        <form method="POST" action="{{ route('admin.pdf-suite.' . $tool['id']) }}" enctype="multipart/form-data">
            @csrf
            <div class="upload-area" id="upload-{{ $tool['id'] }}" @if($tool['id'] === 'htmltopdf') style="display:none;" @endif onclick="document.getElementById('file-{{ $tool['id'] }}').click()">
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <p>{{ $tool['multi'] ? 'Drop files here or click to browse' : 'Drop file here or click to browse' }}</p>
                <div class="hint">{{ str_contains($tool['accept'], 'image') ? 'JPG, PNG, GIF, WEBP' : 'PDF — max 50MB' }}</div>
                <input type="file" id="file-{{ $tool['id'] }}" name="{{ $tool['multi'] ? ($tool['id']==='jpgtopdf' ? 'images[]' : 'pdfs[]') : 'pdf' }}" accept="{{ $tool['accept'] }}" {{ $tool['multi'] ? 'multiple' : '' }}>
            </div>
            <div class="file-list" id="files-{{ $tool['id'] }}" @if($tool['id'] === 'htmltopdf') style="display:none;" @endif></div>

            @if($tool['fields'] === 'split')
            <div class="form-group">
                <label>Pages to Extract</label>
                <input type="text" name="pages" class="form-control" placeholder="e.g. 1,3,5-8 or all" value="all" required>
                <div class="form-hint">Use commas for individual pages, dashes for ranges</div>
            </div>
            @elseif($tool['fields'] === 'rotate')
            <div class="form-row">
                <div class="form-group">
                    <label>Rotation Angle</label>
                    <select name="angle" class="form-control">
                        <option value="90">90° Clockwise</option>
                        <option value="180">180°</option>
                        <option value="270">270° (90° Counter-clockwise)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Apply to Pages</label>
                    <input type="text" name="pages" class="form-control" value="all" placeholder="all or 1,3,5">
                </div>
            </div>
            @elseif($tool['fields'] === 'pagenumbers')
            <div class="form-row">
                <div class="form-group">
                    <label>Position</label>
                    <select name="position" class="form-control">
                        <option value="bottom-center">Bottom Center</option>
                        <option value="bottom-right">Bottom Right</option>
                        <option value="bottom-left">Bottom Left</option>
                        <option value="top-center">Top Center</option>
                        <option value="top-right">Top Right</option>
                        <option value="top-left">Top Left</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Format</label>
                    <select name="format" class="form-control">
                        <option value="number">1, 2, 3...</option>
                        <option value="page-of">Page 1 of N</option>
                        <option value="dash">- 1 -</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Start From</label>
                <input type="number" name="start" class="form-control" value="1" min="1" style="width:120px;">
            </div>
            @elseif($tool['fields'] === 'watermark')
            <div class="form-group">
                <label>Watermark Text</label>
                <input type="text" name="text" class="form-control" placeholder="e.g. CONFIDENTIAL" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Font Size</label>
                    <input type="number" name="size" class="form-control" value="40" min="10" max="120">
                </div>
                <div class="form-group">
                    <label>Opacity (%)</label>
                    <div class="range-wrap">
                        <input type="range" name="opacity" min="1" max="100" value="30" oninput="this.nextElementSibling.textContent=this.value+'%'">
                        <span class="range-value">30%</span>
                    </div>
                </div>
            </div>
            @elseif($tool['fields'] === 'jpgtopdf')
            <div class="form-group">
                <label>Orientation</label>
                <select name="orientation" class="form-control" style="width:200px;">
                    <option value="portrait">Portrait</option>
                    <option value="landscape">Landscape</option>
                </select>
            </div>
            @elseif($tool['fields'] === 'pdftojpg')
            <div class="form-group">
                <label>Quality (DPI)</label>
                <select name="quality" class="form-control" style="width:200px;">
                    <option value="72">72 DPI (fast, small)</option>
                    <option value="150" selected>150 DPI (balanced)</option>
                    <option value="300">300 DPI (high quality)</option>
                </select>
            </div>
            @elseif($tool['fields'] === 'compress')
            <div class="form-group">
                <label>Compression Level</label>
                <select name="quality" class="form-control" style="width:260px;">
                    <option value="screen">Maximum (screen quality — smallest file)</option>
                    <option value="ebook" selected>Balanced (ebook quality)</option>
                    <option value="printer">Less compression (printer quality)</option>
                    <option value="prepress">Minimum (prepress quality — best)</option>
                </select>
            </div>
            @elseif($tool['fields'] === 'protect')
            <div class="form-row">
                <div class="form-group">
                    <label>User Password (to open)</label>
                    <input type="text" name="user_password" class="form-control" placeholder="Required" required>
                </div>
                <div class="form-group">
                    <label>Owner Password (optional)</label>
                    <input type="text" name="owner_password" class="form-control" placeholder="Same as user if empty">
                </div>
            </div>
            @elseif($tool['fields'] === 'unlock')
            <div class="form-group">
                <label>PDF Password</label>
                <input type="text" name="password" class="form-control" placeholder="Enter the current password" required>
            </div>
            @elseif($tool['fields'] === 'pdftopng')
            <div class="form-group">
                <label>Quality (DPI)</label>
                <select name="quality" class="form-control" style="width:200px;">
                    <option value="72">72 DPI (fast, small)</option>
                    <option value="150" selected>150 DPI (balanced)</option>
                    <option value="300">300 DPI (high quality)</option>
                </select>
            </div>
            @elseif($tool['fields'] === 'sign')
            <div class="form-group">
                <label>Signature Image (PNG or JPG)</label>
                <input type="file" name="signature" accept=".png,.jpg,.jpeg" class="form-control" required>
                <div class="form-hint">Use a PNG with transparent background for best results</div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Page Number</label>
                    <input type="number" name="page" class="form-control" value="1" min="1" required>
                </div>
                <div class="form-group">
                    <label>Position</label>
                    <select name="position" class="form-control">
                        <option value="bottom-right">Bottom Right</option>
                        <option value="bottom-left">Bottom Left</option>
                        <option value="bottom-center">Bottom Center</option>
                        <option value="top-right">Top Right</option>
                        <option value="top-left">Top Left</option>
                        <option value="center">Center</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Signature Width (mm)</label>
                <div class="range-wrap">
                    <input type="range" name="width" min="30" max="200" value="80" oninput="this.nextElementSibling.textContent=this.value+'mm'">
                    <span class="range-value">80mm</span>
                </div>
            </div>
            @elseif($tool['fields'] === 'htmltopdf')
            
            
            <div class="form-group">
                <label>HTML Content</label>
                <textarea name="html" class="form-control" rows="10" placeholder="<h1>Hello World</h1><p>Paste your HTML here...</p>" required style="font-family:var(--font-mono);font-size:var(--fs-xs);"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Paper Size</label>
                    <select name="paper" class="form-control">
                        <option value="a4" selected>A4</option>
                        <option value="letter">Letter</option>
                        <option value="legal">Legal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Orientation</label>
                    <select name="orientation" class="form-control">
                        <option value="portrait">Portrait</option>
                        <option value="landscape">Landscape</option>
                    </select>
                </div>
            </div>
            @endif

            <button type="submit" class="btn btn-primary btn-block" id="btn-{{ $tool['id'] }}">
                <i class="fas {{ $tool['icon'] }}"></i> {{ $tool['title'] }}
            </button>
        </form>

        @if($tool['id'] === 'extract')
        <div id="extractResult" style="display:none;">
            <div class="text-stats">
                Pages: <span id="statPages">0</span> &nbsp;|&nbsp;
                Words: <span id="statWords">0</span> &nbsp;|&nbsp;
                Characters: <span id="statChars">0</span>
            </div>
            <div class="text-result" id="extractedText"></div>
            <button class="btn btn-outline" style="margin-top:12px;width:auto;" onclick="copyText()"><i class="fas fa-copy"></i> Copy Text</button>
        </div>
        @endif

        @if($tool['id'] === 'info')
        <div id="infoResult" style="display:none;margin-top:16px;">
            <div style="background:var(--table-header-bg,var(--table-header-bg));border-radius:10px;padding:18px;border:1px solid var(--border-color,var(--border-color));">
                <table style="width:100%;font-size:13px;border-collapse:collapse;">
                    <tbody id="infoTable"></tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endforeach

<div class="toast" id="toast"></div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// ── Tool Panel Toggle ──
function openTool(id) {
    document.querySelectorAll('.tool-panel').forEach(p => p.classList.remove('show'));
    document.querySelectorAll('.tool-card').forEach(c => c.classList.remove('active'));
    document.getElementById('panel-' + id).classList.add('show');
    event.currentTarget.classList.add('active');
    document.getElementById('panel-' + id).scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function closeTool() {
    document.querySelectorAll('.tool-panel').forEach(p => p.classList.remove('show'));
    document.querySelectorAll('.tool-card').forEach(c => c.classList.remove('active'));
}

// ── File Upload Preview ──
const toolIds = ['merge','split','rotate','pagenumbers','watermark','jpgtopdf','pdftojpg','pdftopng','htmltopdf','extract','compress','repair','flatten','sign','protect','unlock','info'];
const multiTools = ['merge', 'jpgtopdf']; // tools that accept multiple files
const fileStore = {}; // accumulated files per tool

toolIds.forEach(id => {
    const input = document.getElementById('file-' + id);
    const area = document.getElementById('upload-' + id);
    const list = document.getElementById('files-' + id);
    if (!input || !area) return;

    fileStore[id] = [];

    input.addEventListener('change', () => {
        if (multiTools.includes(id)) {
            // Accumulate files instead of replacing
            for (const f of input.files) {
                // Skip duplicates
                const exists = fileStore[id].some(ef => ef.name === f.name && ef.size === f.size);
                if (!exists) fileStore[id].push(f);
            }
            rebuildFileInput(id);
        }
        updateFileList(id);
    });

    ['dragenter','dragover'].forEach(e => area.addEventListener(e, ev => { ev.preventDefault(); area.classList.add('dragover'); }));
    ['dragleave','drop'].forEach(e => area.addEventListener(e, ev => { ev.preventDefault(); area.classList.remove('dragover'); }));
    area.addEventListener('drop', ev => {
        if (multiTools.includes(id)) {
            for (const f of ev.dataTransfer.files) {
                const exists = fileStore[id].some(ef => ef.name === f.name && ef.size === f.size);
                if (!exists) fileStore[id].push(f);
            }
            rebuildFileInput(id);
        } else {
            const dt = new DataTransfer();
            for (const f of ev.dataTransfer.files) dt.items.add(f);
            input.files = dt.files;
        }
        updateFileList(id);
    });
});

function rebuildFileInput(id) {
    const input = document.getElementById('file-' + id);
    const dt = new DataTransfer();
    fileStore[id].forEach(f => dt.items.add(f));
    input.files = dt.files;
}

function removeFile(id, index) {
    fileStore[id].splice(index, 1);
    rebuildFileInput(id);
    updateFileList(id);
}

function updateFileList(id) {
    const input = document.getElementById('file-' + id);
    const list = document.getElementById('files-' + id);
    const files = multiTools.includes(id) ? fileStore[id] : Array.from(input.files);
    list.innerHTML = '';
    files.forEach((f, i) => {
        const sz = f.size >= 1048576 ? (f.size/1048576).toFixed(1)+' MB' : (f.size/1024).toFixed(0)+' KB';
        const icon = f.type === 'application/pdf' ? 'fa-file-pdf' : 'fa-file-image';
        const removeBtn = multiTools.includes(id)
            ? '<span class="remove" onclick="removeFile(\'' + id + '\',' + i + ')"><i class="fas fa-times"></i></span>'
            : '';
        list.innerHTML += '<div class="file-item"><i class="fas ' + icon + '"></i><span class="name">' + f.name + '</span><span class="size">' + sz + '</span>' + removeBtn + '</div>';
    });
}

// ── Extract Text (AJAX instead of form submit) ──
document.querySelector('#panel-extract form').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('file-extract');
    if (!input.files.length) { showToast('Please select a PDF.', 'error'); return; }

    const fd = new FormData();
    fd.append('pdf', input.files[0]);
    fd.append('_token', csrfToken);

    document.getElementById('btn-extract').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Extracting...';

    fetch('{{ route("admin.pdf-suite.extract") }}', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: fd,
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('btn-extract').innerHTML = '<i class="fas fa-align-left"></i> Extract Text';
        if (data.success) {
            document.getElementById('extractResult').style.display = 'block';
            document.getElementById('extractedText').textContent = data.text || '(No text found)';
            document.getElementById('statPages').textContent = data.stats.pages;
            document.getElementById('statWords').textContent = data.stats.words.toLocaleString();
            document.getElementById('statChars').textContent = data.stats.chars.toLocaleString();
        } else {
            showToast(data.message || 'Extraction failed.', 'error');
        }
    })
    .catch(() => {
        document.getElementById('btn-extract').innerHTML = '<i class="fas fa-align-left"></i> Extract Text';
        showToast('Extraction failed.', 'error');
    });
});

function copyText() {
    const text = document.getElementById('extractedText').textContent;
    navigator.clipboard.writeText(text).then(() => showToast('Text copied!', 'success'));
}

// ── Info (AJAX) ──
const infoForm = document.querySelector('#panel-info form');
if (infoForm) {
    infoForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('file-info');
        if (!input || !input.files.length) { showToast('Please select a PDF.', 'error'); return; }

        const fd = new FormData();
        fd.append('pdf', input.files[0]);
        fd.append('_token', csrfToken);

        document.getElementById('btn-info').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing...';

        fetch('{{ route("admin.pdf-suite.info") }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('btn-info').innerHTML = '<i class="fas fa-info-circle"></i> PDF Info';
            if (data.success) {
                const rows = [
                    ['Filename', data.filename],
                    ['Pages', data.pages],
                    ['File Size', data.size_human],
                    ['Title', data.title],
                    ['Author', data.author],
                    ['Creator', data.creator],
                    ['Producer', data.producer],
                    ['Created', data.created],
                    ['Modified', data.modified],
                ];
                let h = '';
                rows.forEach(r => {
                    h += '<tr><td style="padding:8px 12px;font-weight:600;color:var(--text-secondary);width:120px;border-bottom:1px solid var(--border-light,var(--border-light));">' + r[0] + '</td>';
                    h += '<td style="padding:8px 12px;color:var(--text-heading);border-bottom:1px solid var(--border-light,var(--border-light));">' + (r[1] || '—') + '</td></tr>';
                });
                document.getElementById('infoTable').innerHTML = h;
                document.getElementById('infoResult').style.display = 'block';
            } else {
                showToast(data.message || 'Failed to read PDF info.', 'error');
            }
        })
        .catch(() => {
            document.getElementById('btn-info').innerHTML = '<i class="fas fa-info-circle"></i> PDF Info';
            showToast('Failed to read PDF info.', 'error');
        });
    });
}

// ── Toast ──
function showToast(msg, type) {
    const t = document.getElementById('toast');
    t.className = `toast ${type} show`;
    t.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
    setTimeout(() => t.classList.remove('show'), 3500);
}
</script>
@endpush
