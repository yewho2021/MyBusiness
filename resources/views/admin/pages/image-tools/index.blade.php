@extends('admin.layouts.app')
@section('title', 'Image Tools')

@push('styles')
<style>
.page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px}
.page-header h1{font-size:24px;font-weight:700;color:var(--text-heading);margin-bottom:5px}
.page-header p{font-size:14px;color:var(--text-muted)}
.alert{padding:14px 18px;border-radius:var(--card-radius,10px);margin-bottom:18px;font-size:14px;font-weight:500;display:flex;align-items:center;gap:10px}
.alert-success{background:var(--c-success-light);color:var(--c-success);border:1px solid var(--c-success-border)}
.alert-danger{background:var(--c-danger-light);color:var(--c-danger);border:1px solid var(--c-danger-border)}
.tool-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:14px;margin-bottom:24px}
.tool-card{background:var(--card-bg);border:2px solid var(--border-color);border-radius:14px;padding:22px 16px;text-align:center;cursor:pointer;transition:all .25s}
.tool-card:hover{border-color:var(--c-primary);box-shadow:0 8px 24px rgba(220,38,38,.08);transform:translateY(-3px)}
.tool-card.active{border-color:var(--c-primary);background:var(--c-danger-light)}
.tool-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;margin:0 auto 10px}
.tool-icon.red{background:var(--c-danger-light);color:var(--c-danger)}
.tool-icon.blue{background:var(--c-secondary-light);color:var(--c-secondary)}
.tool-icon.green{background:var(--c-success-light);color:var(--c-success)}
.tool-icon.amber{background:var(--c-warning-light);color:var(--c-warning)}
.tool-icon.purple{background:var(--c-purple-light);color:var(--c-purple)}
.tool-icon.slate{background:var(--hover-bg);color:var(--text-secondary)}
.tool-name{font-size:13px;font-weight:700;color:var(--text-heading);margin-bottom:3px}
.tool-desc{font-size:11px;color:var(--text-faint);line-height:1.4}
.cat-label{font-size:var(--fs-xs);font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px}
.tool-panel{display:none;background:var(--card-bg);border-radius:14px;border:1px solid var(--border-color);box-shadow:0 1px 3px rgba(0,0,0,.04);margin-bottom:24px;overflow:hidden}
.tool-panel.show{display:block}
.tool-panel-header{padding:18px 22px;border-bottom:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center}
.tool-panel-header h2{font-size:16px;font-weight:700;color:var(--text-heading);display:flex;align-items:center;gap:10px}
.close-panel{width:30px;height:30px;border-radius:8px;background:var(--table-header-bg);border:1px solid var(--border-color);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-muted);font-size:16px}
.close-panel:hover{background:var(--c-danger-light);color:var(--c-danger)}
.tool-panel-body{padding:22px}
.upload-area{border:2px dashed var(--input-border);border-radius:var(--card-radius,12px);padding:36px 20px;text-align:center;cursor:pointer;transition:all .2s;background:var(--table-header-bg);margin-bottom:16px}
.upload-area:hover,.upload-area.dragover{border-color:var(--c-secondary);background:var(--c-secondary-light)}
.upload-area i.upload-icon{font-size:32px;color:var(--text-faint);margin-bottom:8px}
.upload-area p{font-size:13px;color:var(--text-muted);margin-bottom:4px}
.upload-area .hint{font-size:11px;color:var(--text-faint)}
.upload-area input[type="file"]{display:none}
.upload-area .preview-img{max-width:100%;max-height:280px;object-fit:contain;border-radius:8px}
.upload-area.has-preview{padding:12px}
.upload-area.has-preview .upload-icon,.upload-area.has-preview p,.upload-area.has-preview .hint{display:none}
.file-list{margin-bottom:14px}
.file-item{display:flex;align-items:center;gap:10px;padding:8px 12px;background:var(--table-header-bg);border-radius:8px;margin-bottom:4px;border:1px solid var(--border-color)}
.file-item i{color:var(--c-primary);font-size:16px}
.file-item .name{flex:1;font-size:12px;font-weight:500;color:var(--text-heading);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.file-item .size{font-size:11px;color:var(--text-faint)}
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:12px;font-weight:600;color:var(--text-body);margin-bottom:5px}
.form-control{width:100%;padding:9px 12px;border:1.5px solid var(--border-color);border-radius:8px;font-size:13px;color:var(--text-heading);background:var(--card-bg);box-sizing:border-box}
.form-control:focus{outline:none;border-color:var(--c-secondary);box-shadow:0 0 0 3px var(--focus-ring)}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:640px){.form-row{grid-template-columns:1fr}}
.form-hint{font-size:11px;color:var(--text-faint);margin-top:4px}
.range-wrap{display:flex;align-items:center;gap:10px}
.range-wrap input[type="range"]{flex:1;height:6px;-webkit-appearance:none;background:var(--border-color);border-radius:3px;outline:none}
.range-wrap input[type="range"]::-webkit-slider-thumb{-webkit-appearance:none;width:16px;height:16px;border-radius:50%;background:var(--c-primary);cursor:pointer}
.range-value{min-width:38px;text-align:center;font-size:13px;font-weight:700;color:var(--text-heading)}
.btn{padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:7px;transition:all .2s;width:100%;justify-content:center}
.btn-primary{background:var(--c-primary);color:var(--card-bg)}
.btn-primary:hover{opacity:.9}
.btn-outline{background:transparent;color:var(--text-secondary);border:1.5px solid var(--input-border);width:auto}
.info-table{width:100%;font-size:13px;border-collapse:collapse;margin-top:14px}
.info-table td{padding:8px 12px;border-bottom:1px solid var(--border-light)}
.info-table td:first-child{font-weight:600;color:var(--text-secondary);width:120px}
.info-table td:last-child{color:var(--text-heading)}
.base64-box{background:var(--code-bg);color:var(--code-text);border-radius:8px;padding:14px;font-family:var(--font-mono);font-size:11px;max-height:200px;overflow-y:auto;word-break:break-all;margin-top:10px}
.toast{position:fixed;bottom:24px;right:24px;padding:14px 22px;border-radius:var(--card-radius,10px);font-size:14px;font-weight:500;z-index:10000;box-shadow:0 8px 24px rgba(0,0,0,.15);display:none;align-items:center;gap:8px}
.toast.success{background:var(--c-success);color:var(--card-bg)}
.toast.error{background:var(--c-danger);color:var(--card-bg)}
.toast.show{display:flex}
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
        <h1>Image Tools</h1>
        <p>Every image tool you need — resize, crop, convert, filter, and more</p>
    </div>
</div>

{{-- ═══ TOOL GRID ═══ --}}
<div class="cat-label">Transform</div>
<div class="tool-grid">
    <div class="tool-card" onclick="openTool('resize')"><div class="tool-icon red"><i class="fas fa-expand-arrows-alt"></i></div><div class="tool-name">Resize</div><div class="tool-desc">Change dimensions</div></div>
    <div class="tool-card" onclick="openTool('crop')"><div class="tool-icon blue"><i class="fas fa-crop-alt"></i></div><div class="tool-name">Crop</div><div class="tool-desc">Cut out a region</div></div>
    <div class="tool-card" onclick="openTool('rotate')"><div class="tool-icon green"><i class="fas fa-sync-alt"></i></div><div class="tool-name">Rotate</div><div class="tool-desc">Turn 90/180/270°</div></div>
    <div class="tool-card" onclick="openTool('flip')"><div class="tool-icon green"><i class="fas fa-arrows-alt-h"></i></div><div class="tool-name">Flip / Mirror</div><div class="tool-desc">Horizontal or vertical</div></div>
</div>

<div class="cat-label">Optimize</div>
<div class="tool-grid">
    <div class="tool-card" onclick="openTool('compress')"><div class="tool-icon red"><i class="fas fa-compress-alt"></i></div><div class="tool-name">Compress</div><div class="tool-desc">Reduce file size</div></div>
    <div class="tool-card" onclick="openTool('batchresize')"><div class="tool-icon amber"><i class="fas fa-images"></i></div><div class="tool-name">Batch Resize</div><div class="tool-desc">Resize many at once</div></div>
    <div class="tool-card" onclick="openTool('stripexif')"><div class="tool-icon slate"><i class="fas fa-user-secret"></i></div><div class="tool-name">Strip EXIF</div><div class="tool-desc">Remove metadata</div></div>
</div>

<div class="cat-label">Convert</div>
<div class="tool-grid">
    <div class="tool-card" onclick="openTool('convert')"><div class="tool-icon blue"><i class="fas fa-exchange-alt"></i></div><div class="tool-name">Convert Format</div><div class="tool-desc">JPG↔PNG↔WEBP</div></div>
    <div class="tool-card" onclick="openTool('favicon')"><div class="tool-icon amber"><i class="fas fa-star"></i></div><div class="tool-name">Favicon</div><div class="tool-desc">Generate all sizes</div></div>
    <div class="tool-card" onclick="openTool('base64')"><div class="tool-icon purple"><i class="fas fa-code"></i></div><div class="tool-name">Base64 Encode</div><div class="tool-desc">Image → data URI</div></div>
</div>

<div class="cat-label">Adjust</div>
<div class="tool-grid">
    <div class="tool-card" onclick="openTool('adjust')"><div class="tool-icon amber"><i class="fas fa-sun"></i></div><div class="tool-name">Brightness</div><div class="tool-desc">Brightness & contrast</div></div>
    <div class="tool-card" onclick="openTool('greyscale')"><div class="tool-icon slate"><i class="fas fa-adjust"></i></div><div class="tool-name">Grayscale</div><div class="tool-desc">Black & white</div></div>
    <div class="tool-card" onclick="openTool('sepia')"><div class="tool-icon amber"><i class="fas fa-image"></i></div><div class="tool-name">Sepia</div><div class="tool-desc">Vintage warm tone</div></div>
    <div class="tool-card" onclick="openTool('invert')"><div class="tool-icon purple"><i class="fas fa-circle-notch"></i></div><div class="tool-name">Invert</div><div class="tool-desc">Negative colors</div></div>
    <div class="tool-card" onclick="openTool('colorize')"><div class="tool-icon red"><i class="fas fa-palette"></i></div><div class="tool-name">Colorize</div><div class="tool-desc">Tint with color</div></div>
</div>

<div class="cat-label">Effects</div>
<div class="tool-grid">
    <div class="tool-card" onclick="openTool('blur')"><div class="tool-icon blue"><i class="fas fa-water"></i></div><div class="tool-name">Blur</div><div class="tool-desc">Gaussian blur</div></div>
    <div class="tool-card" onclick="openTool('sharpen')"><div class="tool-icon green"><i class="fas fa-search-plus"></i></div><div class="tool-name">Sharpen</div><div class="tool-desc">Enhance detail</div></div>
    <div class="tool-card" onclick="openTool('pixelate')"><div class="tool-icon purple"><i class="fas fa-th"></i></div><div class="tool-name">Pixelate</div><div class="tool-desc">Mosaic effect</div></div>
</div>

<div class="cat-label">Overlay</div>
<div class="tool-grid">
    <div class="tool-card" onclick="openTool('watermark')"><div class="tool-icon purple"><i class="fas fa-copyright"></i></div><div class="tool-name">Watermark</div><div class="tool-desc">Add text overlay</div></div>
    <div class="tool-card" onclick="openTool('border')"><div class="tool-icon amber"><i class="fas fa-border-style"></i></div><div class="tool-name">Add Border</div><div class="tool-desc">Frame with color</div></div>
</div>

<div class="cat-label">Info</div>
<div class="tool-grid" style="margin-bottom:8px;">
    <div class="tool-card" onclick="openTool('info')"><div class="tool-icon blue"><i class="fas fa-info-circle"></i></div><div class="tool-name">Image Info</div><div class="tool-desc">Dimensions & EXIF</div></div>
</div>

{{-- ═══ TOOL PANELS ═══ --}}
@php
$tools = [
    ['id'=>'resize','title'=>'Resize Image','icon'=>'fa-expand-arrows-alt','fields'=>'resize'],
    ['id'=>'crop','title'=>'Crop Image','icon'=>'fa-crop-alt','fields'=>'crop'],
    ['id'=>'rotate','title'=>'Rotate Image','icon'=>'fa-sync-alt','fields'=>'rotate'],
    ['id'=>'flip','title'=>'Flip / Mirror','icon'=>'fa-arrows-alt-h','fields'=>'flip'],
    ['id'=>'compress','title'=>'Compress Image','icon'=>'fa-compress-alt','fields'=>'compress'],
    ['id'=>'batchresize','title'=>'Batch Resize','icon'=>'fa-images','fields'=>'batchresize','multi'=>true],
    ['id'=>'stripexif','title'=>'Strip EXIF Data','icon'=>'fa-user-secret','fields'=>''],
    ['id'=>'convert','title'=>'Convert Format','icon'=>'fa-exchange-alt','fields'=>'convert'],
    ['id'=>'favicon','title'=>'Favicon Generator','icon'=>'fa-star','fields'=>''],
    ['id'=>'base64','title'=>'Base64 Encode','icon'=>'fa-code','fields'=>''],
    ['id'=>'adjust','title'=>'Brightness & Contrast','icon'=>'fa-sun','fields'=>'adjust'],
    ['id'=>'greyscale','title'=>'Grayscale','icon'=>'fa-adjust','fields'=>''],
    ['id'=>'sepia','title'=>'Sepia Filter','icon'=>'fa-image','fields'=>''],
    ['id'=>'invert','title'=>'Invert Colors','icon'=>'fa-circle-notch','fields'=>''],
    ['id'=>'colorize','title'=>'Colorize','icon'=>'fa-palette','fields'=>'colorize'],
    ['id'=>'blur','title'=>'Blur Image','icon'=>'fa-water','fields'=>'slider_amount'],
    ['id'=>'sharpen','title'=>'Sharpen Image','icon'=>'fa-search-plus','fields'=>'slider_amount'],
    ['id'=>'pixelate','title'=>'Pixelate Image','icon'=>'fa-th','fields'=>'slider_size'],
    ['id'=>'watermark','title'=>'Watermark','icon'=>'fa-copyright','fields'=>'watermark'],
    ['id'=>'border','title'=>'Add Border','icon'=>'fa-border-style','fields'=>'border'],
    ['id'=>'info','title'=>'Image Info','icon'=>'fa-info-circle','fields'=>''],
];
@endphp

@foreach($tools as $tool)
<div class="tool-panel" id="panel-{{ $tool['id'] }}">
    <div class="tool-panel-header">
        <h2><i class="fas {{ $tool['icon'] }}" style="color:var(--c-primary);"></i> {{ $tool['title'] }}</h2>
        <button class="close-panel" onclick="closeTool()">×</button>
    </div>
    <div class="tool-panel-body">
        <form method="POST" action="{{ route('admin.image-tools.' . $tool['id']) }}" enctype="multipart/form-data">
            @csrf
            <div class="upload-area" id="upload-{{ $tool['id'] }}" onclick="document.getElementById('file-{{ $tool['id'] }}').click()">
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <p>{{ !empty($tool['multi']) ? 'Drop images here or click to browse' : 'Drop image here or click to browse' }}</p>
                <div class="hint">JPG, PNG, GIF, WEBP, BMP — max 20MB</div>
                <input type="file" id="file-{{ $tool['id'] }}" name="{{ !empty($tool['multi']) ? 'images[]' : 'image' }}" accept="image/*" {{ !empty($tool['multi']) ? 'multiple' : '' }}>
            </div>
            <div class="file-list" id="files-{{ $tool['id'] }}"></div>

            @if($tool['fields'] === 'resize')
            <div class="form-row">
                <div class="form-group"><label>Width (px)</label><input type="number" name="width" class="form-control" placeholder="800"></div>
                <div class="form-group"><label>Height (px)</label><input type="number" name="height" class="form-control" placeholder="Auto"></div>
            </div>
            <div class="form-group"><label>Mode</label>
                <select name="mode" class="form-control" style="width:200px;">
                    <option value="scale">Scale (keep ratio)</option>
                    <option value="fit">Fit (max bounds)</option>
                    <option value="exact">Exact (stretch)</option>
                </select>
            </div>
            @elseif($tool['fields'] === 'crop')
            <div class="form-row">
                <div class="form-group"><label>Width (px)</label><input type="number" name="width" class="form-control" required></div>
                <div class="form-group"><label>Height (px)</label><input type="number" name="height" class="form-control" required></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>X offset</label><input type="number" name="x" class="form-control" value="0"></div>
                <div class="form-group"><label>Y offset</label><input type="number" name="y" class="form-control" value="0"></div>
            </div>
            @elseif($tool['fields'] === 'rotate')
            <div class="form-row">
                <div class="form-group"><label>Angle</label>
                    <select name="angle" class="form-control"><option value="90">90° CW</option><option value="180">180°</option><option value="270">90° CCW</option><option value="0">Custom flip only</option></select>
                </div>
                <div class="form-group"><label>Flip</label>
                    <select name="flip" class="form-control"><option value="">None</option><option value="h">Horizontal</option><option value="v">Vertical</option><option value="both">Both</option></select>
                </div>
            </div>
            @elseif($tool['fields'] === 'flip')
            <div class="form-group"><label>Direction</label>
                <select name="direction" class="form-control" style="width:200px;">
                    <option value="horizontal">Horizontal (left ↔ right)</option>
                    <option value="vertical">Vertical (top ↔ bottom)</option>
                    <option value="both">Both</option>
                </select>
            </div>
            @elseif($tool['fields'] === 'compress')
            <div class="form-group"><label>Quality</label>
                <div class="range-wrap"><input type="range" name="quality" min="1" max="100" value="70" oninput="this.nextElementSibling.textContent=this.value+'%'"><span class="range-value">70%</span></div>
                <div class="form-hint">Lower = smaller file. Output is always JPG/WEBP.</div>
            </div>
            @elseif($tool['fields'] === 'batchresize')
            <div class="form-row">
                <div class="form-group"><label>Target Width (px)</label><input type="number" name="width" class="form-control" value="800" required></div>
                <div class="form-group"><label>Height (px, optional)</label><input type="number" name="height" class="form-control" placeholder="Auto (keep ratio)"></div>
            </div>
            @elseif($tool['fields'] === 'convert')
            <div class="form-group"><label>Output Format</label>
                <select name="format" class="form-control" style="width:200px;">
                    <option value="jpg">JPG</option><option value="png">PNG</option><option value="webp">WEBP</option><option value="gif">GIF</option><option value="bmp">BMP</option>
                </select>
            </div>
            @elseif($tool['fields'] === 'adjust')
            <div class="form-group"><label>Brightness</label><div class="range-wrap"><input type="range" name="brightness" min="-100" max="100" value="0" oninput="this.nextElementSibling.textContent=this.value"><span class="range-value">0</span></div></div>
            <div class="form-group"><label>Contrast</label><div class="range-wrap"><input type="range" name="contrast" min="-100" max="100" value="0" oninput="this.nextElementSibling.textContent=this.value"><span class="range-value">0</span></div></div>
            <div class="form-group"><label>Gamma</label><div class="range-wrap"><input type="range" name="gamma" min="1" max="50" value="10" oninput="this.nextElementSibling.textContent=(this.value/10).toFixed(1)" step="1"><span class="range-value">1.0</span></div><div class="form-hint">1.0 = no change</div></div>
            @elseif($tool['fields'] === 'colorize')
            <div class="form-row">
                <div class="form-group"><label>Red</label><div class="range-wrap"><input type="range" name="red" min="-100" max="100" value="0" oninput="this.nextElementSibling.textContent=this.value"><span class="range-value">0</span></div></div>
                <div class="form-group"><label>Green</label><div class="range-wrap"><input type="range" name="green" min="-100" max="100" value="0" oninput="this.nextElementSibling.textContent=this.value"><span class="range-value">0</span></div></div>
            </div>
            <div class="form-group"><label>Blue</label><div class="range-wrap"><input type="range" name="blue" min="-100" max="100" value="0" oninput="this.nextElementSibling.textContent=this.value"><span class="range-value">0</span></div></div>
            @elseif($tool['fields'] === 'slider_amount')
            <div class="form-group"><label>Amount</label><div class="range-wrap"><input type="range" name="amount" min="1" max="100" value="15" oninput="this.nextElementSibling.textContent=this.value"><span class="range-value">15</span></div></div>
            @elseif($tool['fields'] === 'slider_size')
            <div class="form-group"><label>Block Size (px)</label><div class="range-wrap"><input type="range" name="size" min="2" max="100" value="10" oninput="this.nextElementSibling.textContent=this.value+'px'"><span class="range-value">10px</span></div></div>
            @elseif($tool['fields'] === 'watermark')
            <div class="form-group"><label>Text</label><input type="text" name="text" class="form-control" placeholder="CONFIDENTIAL" required></div>
            <div class="form-row">
                <div class="form-group"><label>Position</label>
                    <select name="position" class="form-control"><option value="bottom-right">Bottom Right</option><option value="bottom-left">Bottom Left</option><option value="top-right">Top Right</option><option value="top-left">Top Left</option><option value="center">Center</option></select>
                </div>
                <div class="form-group"><label>Size</label><input type="number" name="size" class="form-control" value="36" min="10" max="200"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Color</label><input type="color" name="color" class="form-control" value="#ffffff" style="height:38px;padding:4px;"></div>
                <div class="form-group"><label>Opacity</label><div class="range-wrap"><input type="range" name="opacity" min="1" max="100" value="50" oninput="this.nextElementSibling.textContent=this.value+'%'"><span class="range-value">50%</span></div></div>
            </div>
            @elseif($tool['fields'] === 'border')
            <div class="form-row">
                <div class="form-group"><label>Border Width (px)</label><div class="range-wrap"><input type="range" name="width" min="1" max="200" value="20" oninput="this.nextElementSibling.textContent=this.value+'px'"><span class="range-value">20px</span></div></div>
                <div class="form-group"><label>Border Color</label><input type="color" name="color" class="form-control" value="#000000" style="height:38px;padding:4px;"></div>
            </div>
            @endif

            <button type="submit" class="btn btn-primary" id="btn-{{ $tool['id'] }}">
                <i class="fas {{ $tool['icon'] }}"></i> {{ $tool['title'] }}
            </button>
        </form>

        @if($tool['id'] === 'info')
        <div id="infoResult" style="display:none;"><table class="info-table"><tbody id="infoTableBody"></tbody></table></div>
        @endif

        @if($tool['id'] === 'base64')
        <div id="b64Result" style="display:none;margin-top:14px;">
            <div style="display:flex;gap:8px;margin-bottom:8px;">
                <button class="btn btn-outline" onclick="copyB64('html')"><i class="fas fa-copy"></i> Copy HTML</button>
                <button class="btn btn-outline" onclick="copyB64('css')"><i class="fas fa-copy"></i> Copy CSS</button>
                <button class="btn btn-outline" onclick="copyB64('raw')"><i class="fas fa-copy"></i> Copy Raw</button>
            </div>
            <div class="base64-box" id="b64Box"></div>
            <div style="font-size:12px;color:var(--text-faint);margin-top:6px;">Original: <span id="b64Orig"></span> → Encoded: <span id="b64Enc"></span></div>
        </div>
        @endif
    </div>
</div>
@endforeach

<div class="toast" id="toast"></div>
@endsection

@push('scripts')
<script>
var csrf='{{ csrf_token() }}';

function openTool(id){
    document.querySelectorAll('.tool-panel').forEach(function(p){p.classList.remove('show')});
    document.querySelectorAll('.tool-card').forEach(function(c){c.classList.remove('active')});
    document.getElementById('panel-'+id).classList.add('show');
    if(event&&event.currentTarget)event.currentTarget.classList.add('active');
    document.getElementById('panel-'+id).scrollIntoView({behavior:'smooth',block:'start'});
}
function closeTool(){
    document.querySelectorAll('.tool-panel').forEach(function(p){p.classList.remove('show')});
    document.querySelectorAll('.tool-card').forEach(function(c){c.classList.remove('active')});
}

// File upload preview
var toolIds=['resize','crop','rotate','flip','compress','batchresize','stripexif','convert','favicon','base64','adjust','greyscale','sepia','invert','colorize','blur','sharpen','pixelate','watermark','border','info'];
var multiTools=['batchresize'];
var fileStore={};

toolIds.forEach(function(id){
    var input=document.getElementById('file-'+id);
    var area=document.getElementById('upload-'+id);
    if(!input||!area)return;
    fileStore[id]=[];

    input.addEventListener('change',function(){
        if(multiTools.includes(id)){
            for(var i=0;i<input.files.length;i++){
                var f=input.files[i];
                var exists=fileStore[id].some(function(ef){return ef.name===f.name&&ef.size===f.size});
                if(!exists)fileStore[id].push(f);
            }
            rebuildInput(id);
        }
        updateList(id);
    });

    ['dragenter','dragover'].forEach(function(e){area.addEventListener(e,function(ev){ev.preventDefault();area.classList.add('dragover')})});
    ['dragleave','drop'].forEach(function(e){area.addEventListener(e,function(ev){ev.preventDefault();area.classList.remove('dragover')})});
    area.addEventListener('drop',function(ev){
        if(multiTools.includes(id)){
            for(var i=0;i<ev.dataTransfer.files.length;i++){
                var f=ev.dataTransfer.files[i];
                var exists=fileStore[id].some(function(ef){return ef.name===f.name&&ef.size===f.size});
                if(!exists)fileStore[id].push(f);
            }
            rebuildInput(id);
        }else{
            var dt=new DataTransfer();
            for(var i=0;i<ev.dataTransfer.files.length;i++)dt.items.add(ev.dataTransfer.files[i]);
            input.files=dt.files;
        }
        updateList(id);
    });
});

function rebuildInput(id){var input=document.getElementById('file-'+id);var dt=new DataTransfer();fileStore[id].forEach(function(f){dt.items.add(f)});input.files=dt.files;}
function removeFile(id,i){fileStore[id].splice(i,1);rebuildInput(id);updateList(id);}

function updateList(id){
    var input=document.getElementById('file-'+id);
    var list=document.getElementById('files-'+id);
    var area=document.getElementById('upload-'+id);
    var files=multiTools.includes(id)?fileStore[id]:Array.from(input.files);
    list.innerHTML='';
    files.forEach(function(f,i){
        var sz=f.size>=1048576?(f.size/1048576).toFixed(1)+' MB':(f.size/1024).toFixed(0)+' KB';
        var rm=multiTools.includes(id)?'<span class="remove" onclick="removeFile(\''+id+'\','+i+')" style="cursor:pointer;color:var(--text-faint);"><i class="fas fa-times"></i></span>':'';
        list.innerHTML+='<div class="file-item"><i class="fas fa-file-image"></i><span class="name">'+f.name+'</span><span class="size">'+sz+'</span>'+rm+'</div>';
    });

    // Show image preview for single-file tools
    if(!multiTools.includes(id)&&files.length>0){
        var file=files[0];
        if(file.type.startsWith('image/')){
            var reader=new FileReader();
            reader.onload=function(e){
                var existing=area.querySelector('.preview-img');
                if(existing)existing.remove();
                var img=document.createElement('img');
                img.src=e.target.result;
                img.className='preview-img';
                area.appendChild(img);
                area.classList.add('has-preview');
            };
            reader.readAsDataURL(file);
        }
    }else if(files.length===0){
        var existing=area.querySelector('.preview-img');
        if(existing)existing.remove();
        area.classList.remove('has-preview');
    }
}

// Info (AJAX)
var infoForm=document.querySelector('#panel-info form');
if(infoForm){infoForm.addEventListener('submit',function(e){
    e.preventDefault();
    var input=document.getElementById('file-info');
    if(!input||!input.files.length){showToast('Select an image.','error');return;}
    var fd=new FormData();fd.append('image',input.files[0]);fd.append('_token',csrf);
    document.getElementById('btn-info').innerHTML='<i class="fas fa-spinner fa-spin"></i> Analyzing...';
    fetch('{{ route("admin.image-tools.info") }}',{method:'POST',headers:{'Accept':'application/json'},body:fd})
    .then(function(r){return r.json()}).then(function(d){
        document.getElementById('btn-info').innerHTML='<i class="fas fa-info-circle"></i> Image Info';
        if(d.success){
            var rows=[['Filename',d.filename],['Dimensions',d.width+' × '+d.height+' px'],['File Size',d.size_human],['Format',d.extension.toUpperCase()],['MIME Type',d.mime_type]];
            if(d.exif)Object.keys(d.exif).forEach(function(k){rows.push([k,d.exif[k]])});
            var h='';rows.forEach(function(r){h+='<tr><td>'+r[0]+'</td><td>'+(r[1]||'—')+'</td></tr>'});
            document.getElementById('infoTableBody').innerHTML=h;
            document.getElementById('infoResult').style.display='block';
        }else{showToast(d.message||'Failed','error')}
    }).catch(function(){document.getElementById('btn-info').innerHTML='<i class="fas fa-info-circle"></i> Image Info';showToast('Failed','error')});
});}

// Base64 (AJAX)
var b64Data={};
var b64Form=document.querySelector('#panel-base64 form');
if(b64Form){b64Form.addEventListener('submit',function(e){
    e.preventDefault();
    var input=document.getElementById('file-base64');
    if(!input||!input.files.length){showToast('Select an image.','error');return;}
    var fd=new FormData();fd.append('image',input.files[0]);fd.append('_token',csrf);
    document.getElementById('btn-base64').innerHTML='<i class="fas fa-spinner fa-spin"></i> Encoding...';
    fetch('{{ route("admin.image-tools.base64") }}',{method:'POST',headers:{'Accept':'application/json'},body:fd})
    .then(function(r){return r.json()}).then(function(d){
        document.getElementById('btn-base64').innerHTML='<i class="fas fa-code"></i> Base64 Encode';
        if(d.success){
            b64Data=d;
            document.getElementById('b64Box').textContent=d.data_uri.substring(0,500)+'...';
            var origSz=d.original_size>=1024?(d.original_size/1024).toFixed(1)+' KB':d.original_size+' B';
            var encSz=d.encoded_size>=1024?(d.encoded_size/1024).toFixed(1)+' KB':d.encoded_size+' B';
            document.getElementById('b64Orig').textContent=origSz;
            document.getElementById('b64Enc').textContent=encSz;
            document.getElementById('b64Result').style.display='block';
        }else{showToast(d.message||'Failed','error')}
    }).catch(function(){document.getElementById('btn-base64').innerHTML='<i class="fas fa-code"></i> Base64 Encode';showToast('Failed','error')});
});}

function copyB64(type){
    var text=type==='html'?b64Data.html_tag:type==='css'?b64Data.css_bg:b64Data.data_uri;
    navigator.clipboard.writeText(text).then(function(){showToast('Copied!','success')});
}

function showToast(msg,type){
    var t=document.getElementById('toast');
    t.className='toast '+type+' show';
    t.innerHTML='<i class="fas fa-'+(type==='success'?'check-circle':'exclamation-circle')+'"></i> '+msg;
    setTimeout(function(){t.classList.remove('show')},3500);
}
</script>
@endpush
