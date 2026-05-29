@extends('admin.layouts.app')
@section('title', 'Export Database')
@push('styles')
<style>
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.page-title{font-size:22px;font-weight:700;color:#1e293b}
.nav-pills{display:flex;gap:6px;flex-wrap:wrap}
.nav-pill{padding:6px 14px;border-radius:6px;font-size:13px;text-decoration:none;color:#64748b;border:1px solid #e2e8f0;display:inline-flex;align-items:center;gap:6px}
.nav-pill:hover{background:#f1f5f9;color:#374151}.nav-pill.active{background:#4f46e5;color:#fff;border-color:#4f46e5}
.card{background:#fff;border-radius:10px;border:1px solid #e2e8f0;margin-bottom:20px;overflow:hidden}
.card-header{padding:14px 20px;border-bottom:1px solid #e2e8f0}
.card-title{font-size:15px;font-weight:600;color:#1e293b}
.form-body{padding:20px}
.form-group{margin-bottom:16px}
.form-label{display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px}
.table-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px;max-height:300px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:6px;padding:12px}
.table-grid label{font-size:13px;display:flex;align-items:center;gap:6px;cursor:pointer;padding:4px 8px;border-radius:4px}
.table-grid label:hover{background:#f1f5f9}
.table-grid input{width:16px;height:16px}
.check-actions{margin-bottom:8px;display:flex;gap:8px}
.check-actions button{background:none;border:none;color:#4f46e5;font-size:12px;cursor:pointer;text-decoration:underline}
.option-row{display:flex;gap:20px;flex-wrap:wrap}
.option-row label{font-size:13px;display:flex;align-items:center;gap:8px;cursor:pointer}
.option-row input{width:16px;height:16px}
.btn-export{background:#4f46e5;color:#fff;border:none;padding:10px 24px;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px}
.btn-export:hover{background:#4338ca}
</style>
@endpush
@section('content')
<div class="page-header">
<div><h1 class="page-title"><i class="fas fa-download" style="color:#4f46e5"></i> Export Database</h1></div>
<div class="nav-pills">
<a href="{{ route('admin.database.index') }}" class="nav-pill"><i class="fas fa-table"></i> Tables</a>
<a href="{{ route('admin.database.query') }}" class="nav-pill"><i class="fas fa-terminal"></i> SQL Query</a>
<a href="{{ route('admin.database.export') }}" class="nav-pill active"><i class="fas fa-download"></i> Export</a>
<a href="{{ route('admin.database.import') }}" class="nav-pill"><i class="fas fa-upload"></i> Import</a>
</div>
</div>

<form method="POST" action="{{ route('admin.database.export') }}">
@csrf
<div class="card">
<div class="card-header"><span class="card-title">Select Tables</span></div>
<div class="form-body">
<div class="check-actions">
<button type="button" onclick="document.querySelectorAll('.exp-check').forEach(c=>c.checked=true)">Select All</button>
<button type="button" onclick="document.querySelectorAll('.exp-check').forEach(c=>c.checked=false)">Deselect All</button>
</div>
<div class="table-grid">
@foreach($tableNames as $t)
<label><input type="checkbox" name="tables[]" value="{{ $t }}" class="exp-check" checked> {{ $t }}</label>
@endforeach
</div>
<p style="font-size:12px;color:#94a3b8;margin-top:8px">{{ count($tableNames) }} tables. Leave all checked to export everything.</p>
</div>
</div>

<div class="card">
<div class="card-header"><span class="card-title">Options</span></div>
<div class="form-body">
<div class="option-row">
<label><input type="checkbox" name="include_structure" value="1" checked> Include table structure (CREATE TABLE)</label>
<label><input type="checkbox" name="include_data" value="1" checked> Include data (INSERT INTO)</label>
</div>
</div>
</div>

<button type="submit" class="btn-export"><i class="fas fa-download"></i> Export as .sql</button>
</form>
@endsection
