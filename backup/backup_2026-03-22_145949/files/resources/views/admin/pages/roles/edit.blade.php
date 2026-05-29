@extends('admin.layouts.app')
@section('title', 'Edit Role')

@section('content')
<div class="page-header">
    <h2>Edit Role</h2>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul style="margin:0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3>Role Information</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-row">
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
                </div>
                <div class="form-group">
                    <label>Slug <span class="required">*</span></label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $role->slug) }}" required {{ $role->slug === 'administrator' ? 'readonly' : '' }}>
                    <small class="form-text">Unique identifier. Use lowercase, no spaces.</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Level <span class="required">*</span></label>
                    <input type="number" name="level" class="form-control" value="{{ old('level', $role->level) }}" required min="1" max="99">
                    <small class="form-text">Lower number = higher priority. Admin=1, Supervisor=2, Staff=3</small>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <div class="checkbox-wrapper">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" {{ $role->is_active ? 'checked' : '' }} {{ $role->slug === 'administrator' ? 'disabled' : '' }}>
                            <span>Active</span>
                        </label>
                        @if($role->slug === 'administrator')
                            <input type="hidden" name="is_active" value="1">
                            <small class="form-text">Administrator role cannot be deactivated.</small>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $role->description) }}</textarea>
            </div>
            
            <div class="form-actions">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .page-header h2 { font-size: 24px; font-weight: 600; color: #1e293b; }
    .btn { padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
    .btn-primary { background: #dc2626; color: #fff; }
    .btn-primary:hover { background: #b91c1c; }
    .btn-secondary { background: #e2e8f0; color: #475569; }
    .btn-secondary:hover { background: #cbd5e1; }
    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
    .alert-danger { background: #dbeafe; color: #991b1b; border: 1px solid #bfdbfe; }
    .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .card-header { padding: 20px; border-bottom: 1px solid #e2e8f0; }
    .card-header h3 { font-size: 16px; font-weight: 600; color: #1e293b; }
    .card-body { padding: 24px; }
    .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px; }
    @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 6px; }
    .form-control { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
    .form-control:focus { outline: none; border-color: #2563eb; }
    .form-control[readonly] { background: #f1f5f9; cursor: not-allowed; }
    .form-text { font-size: 12px; color: #9ca3af; margin-top: 4px; display: block; }
    .required { color: #2563eb; }
    .checkbox-wrapper { padding-top: 8px; }
    .checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
    .checkbox-label input { width: 18px; height: 18px; }
    .form-actions { display: flex; justify-content: flex-end; gap: 12px; padding-top: 20px; border-top: 1px solid #e2e8f0; margin-top: 20px; }
</style>
@endpush