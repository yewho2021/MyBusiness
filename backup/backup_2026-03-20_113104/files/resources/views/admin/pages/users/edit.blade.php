@extends('admin.layouts.app')
@section('title', 'Edit Admin')

@section('content')
<div class="page-header">
    <h2>Edit Admin</h2>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
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
        <h3>Admin Information</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.update', $admin->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-row">
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $admin->name) }}" required>
                </div>
                <div class="form-group">
                    <label>Username <span class="required">*</span></label>
                    <input type="text" name="username" class="form-control" value="{{ old('username', $admin->username) }}" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $admin->email) }}" required>
                </div>
                <div class="form-group">
                    <label>Role <span class="required">*</span></label>
                    <select name="role_id" class="form-control" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ $admin->role_id == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="form-row">
<div class="form-group">
    <label>New Password <span class="text-muted">(leave blank to keep current)</span></label>
    <input type="password" name="password" class="form-control" minlength="6">
</div>
</div>

<div class="form-row">
<div class="form-group">
    <label>Confirm Password</label>
    <input type="password" name="password_confirmation" class="form-control" minlength="6">
</div>
                <div class="form-group">
                    <label>Status</label>
                    <div class="checkbox-wrapper">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" {{ $admin->is_active ? 'checked' : '' }}>
                            <span>Active</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="form-info">
                <div class="info-item">
                    <span class="info-label">Created:</span>
                    <span class="info-value">{{ $admin->created_at ? $admin->created_at->format('M d, Y H:i') : 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Last Login:</span>
                    <span class="info-value">{{ $admin->datetime_lastlogin ? $admin->datetime_lastlogin->format('M d, Y H:i') : 'Never' }}</span>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
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
    .btn-primary { background: #4f46e5; color: #fff; }
    .btn-primary:hover { background: #4338ca; }
    .btn-secondary { background: #e2e8f0; color: #475569; }
    .btn-secondary:hover { background: #cbd5e1; }
    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
    .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .card-header { padding: 20px; border-bottom: 1px solid #e2e8f0; }
    .card-header h3 { font-size: 16px; font-weight: 600; color: #1e293b; }
    .card-body { padding: 24px; }
    .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px; }
    @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
    .form-group label { display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 6px; }
    .form-control { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
    .form-control:focus { outline: none; border-color: #4f46e5; }
    .required { color: #dc2626; }
    .text-muted { color: #9ca3af; font-weight: 400; font-size: 13px; }
    .checkbox-wrapper { padding-top: 8px; }
    .checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
    .checkbox-label input { width: 18px; height: 18px; }
    .form-info { display: flex; gap: 40px; padding: 16px 0; margin-bottom: 20px; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; }
    .info-item { display: flex; gap: 8px; }
    .info-label { color: #64748b; font-size: 14px; }
    .info-value { color: #1e293b; font-size: 14px; font-weight: 500; }
    .form-actions { display: flex; justify-content: flex-end; gap: 12px; padding-top: 20px; }
</style>
@endpush