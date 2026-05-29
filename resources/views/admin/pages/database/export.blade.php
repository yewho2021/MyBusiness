@extends('admin.layouts.app')
@section('title', 'Export Database')
@push('styles')
<style>
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.page-title{font-size:22px;font-weight:700;color:var(--header-text,var(--text-heading))}
.nav-pills{display:flex;gap:6px;flex-wrap:wrap}
.nav-pill{padding:6px 14px;border-radius:6px;font-size:13px;text-decoration:none;color:var(--text-muted);border:1px solid var(--border-color,var(--border-color));display:inline-flex;align-items:center;gap:6px}
.nav-pill:hover{background:var(--border-light,var(--border-light));color:var(--text-body)}.nav-pill.active{background:var(--c-secondary,var(--c-secondary));color:#fff;border-color:var(--c-secondary,var(--c-secondary))}
.card{background:var(--card-bg,#fff);border-radius:var(--card-radius,10px);border:1px solid var(--border-color,var(--border-color));margin-bottom:20px;overflow:hidden}
.card-header{padding:14px 20px;border-bottom:1px solid var(--border-color,var(--border-color))}
.card-title{font-size:15px;font-weight:600;color:var(--header-text,var(--text-heading))}
.form-body{padding:20px}
.form-group{margin-bottom:16px}
.form-label{display:block;font-size:13px;font-weight:500;color:var(--text-body);margin-bottom:6px}
.table-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px;max-height:300px;overflow-y:auto;border:1px solid var(--border-color,var(--border-color));border-radius:6px;padding:12px}
.table-grid label{font-size:13px;display:flex;align-items:center;gap:6px;cursor:pointer;padding:4px 8px;border-radius:4px}
.table-grid label:hover{background:var(--border-light,var(--border-light))}
.table-grid input{width:16px;height:16px}
.check-actions{margin-bottom:8px;display:flex;gap:8px}
.check-actions button{background:none;border:none;color:var(--c-secondary,var(--c-secondary));font-size:12px;cursor:pointer;text-decoration:underline}
.option-row{display:flex;gap:20px;flex-wrap:wrap}
.option-row label{font-size:13px;display:flex;align-items:center;gap:8px;cursor:pointer}
.option-row input{width:16px;height:16px}
.btn-export{background:var(--c-primary,var(--c-danger));color:#fff;border:none;padding:10px 24px;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px}
.btn-export:hover{background:var(--c-primary-hover,var(--c-primary-hover))}
</style>
@endpush
@section('content')
<div class="page-header">
<div><h1 class="page-title"><i class="fas fa-download" style="color:var(--c-secondary,var(--c-secondary))"></i> Export Database</h1></div>
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
<p style="font-size:12px;color:var(--text-faint);margin-top:8px">{{ count($tableNames) }} tables. Leave all checked to export everything.</p>
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
