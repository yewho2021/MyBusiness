@extends('admin.layouts.app')
@section('title', 'Import Database')
@push('styles')
<style>
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.page-title{font-size:22px;font-weight:700;color:#1e293b}
.nav-pills{display:flex;gap:6px;flex-wrap:wrap}
.nav-pill{padding:6px 14px;border-radius:6px;font-size:13px;text-decoration:none;color:#64748b;border:1px solid #e2e8f0;display:inline-flex;align-items:center;gap:6px}
.nav-pill:hover{background:#f1f5f9;color:#374151}.nav-pill.active{background:#2563eb;color:#fff;border-color:#2563eb}
.card{background:#fff;border-radius:10px;border:1px solid #e2e8f0;margin-bottom:20px;overflow:hidden}
.card-header{padding:14px 20px;border-bottom:1px solid #e2e8f0}
.card-title{font-size:15px;font-weight:600;color:#1e293b}
.form-body{padding:20px}
.upload-zone{border:2px dashed #d1d5db;border-radius:10px;padding:40px;text-align:center;cursor:pointer;transition:all .2s}
.upload-zone:hover{border-color:#2563eb;background:#eff6ff}
.upload-zone i{font-size:36px;color:#94a3b8;display:block;margin-bottom:12px}
.upload-zone p{font-size:14px;color:#64748b;margin:0}
.upload-zone .sub{font-size:12px;color:#94a3b8;margin-top:4px}
.file-input{display:none}
.file-name{margin-top:12px;font-size:13px;color:#2563eb;font-weight:500}
.btn-import{background:#dc2626;color:#fff;border:none;padding:10px 24px;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px;margin-top:16px}
.btn-import:hover{background:#b91c1c}
.btn-import:disabled{background:#94a3b8;cursor:not-allowed}
.warning-box{background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;font-size:13px;color:#92400e;margin-bottom:20px}
.success-box{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;font-size:13px;color:#166534;margin-bottom:20px}
.error-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 16px;font-size:13px;color:#991b1b;margin-bottom:20px}
</style>
@endpush
@section('content')
<div class="page-header">
<div><h1 class="page-title"><i class="fas fa-upload" style="color:#2563eb"></i> Import SQL</h1></div>
<div class="nav-pills">
<a href="{{ route('admin.database.index') }}" class="nav-pill"><i class="fas fa-table"></i> Tables</a>
<a href="{{ route('admin.database.query') }}" class="nav-pill"><i class="fas fa-terminal"></i> SQL Query</a>
<a href="{{ route('admin.database.export') }}" class="nav-pill"><i class="fas fa-download"></i> Export</a>
<a href="{{ route('admin.database.import') }}" class="nav-pill active"><i class="fas fa-upload"></i> Import</a>
</div>
</div>

@if($result)<div class="success-box"><i class="fas fa-check-circle"></i> {{ $result }}</div>@endif
@if($error)<div class="error-box"><i class="fas fa-exclamation-circle"></i> {{ $error }}</div>@endif

<div class="warning-box">
<i class="fas fa-exclamation-triangle"></i> <strong>Warning:</strong> Importing SQL may overwrite existing data. Make sure you have a backup before importing.
</div>

<form method="POST" action="{{ route('admin.database.import') }}" enctype="multipart/form-data">
@csrf
<div class="card">
<div class="card-header"><span class="card-title">Upload SQL File</span></div>
<div class="form-body">
<div class="upload-zone" onclick="document.getElementById('sqlFile').click()">
<i class="fas fa-cloud-upload-alt"></i>
<p>Click to select .sql file</p>
<p class="sub">Max file size: 50MB</p>
<div class="file-name" id="fileName"></div>
</div>
<input type="file" name="sql_file" id="sqlFile" class="file-input" accept=".sql,.txt" onchange="showFileName(this)">
<button type="submit" class="btn-import" id="importBtn" disabled><i class="fas fa-upload"></i> Import</button>
</div>
</div>
</form>
@endsection
@push('scripts')
<script>
function showFileName(input){
if(input.files.length>0){
document.getElementById('fileName').textContent='Selected: '+input.files[0].name+' ('+Math.round(input.files[0].size/1024)+' KB)';
document.getElementById('importBtn').disabled=false;
}
}
</script>
@endpush
