@extends('admin.layouts.app')
@section('title', 'Admin List')

@push('styles')
<style>
.usr-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.usr-title{font-size:var(--fs-h2,20px);font-weight:700;color:var(--text-heading)}
.usr-tools{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.usr-search{padding:8px 14px;border-radius:var(--input-radius,8px);border:1px solid var(--input-border);font-size:var(--fs-sm);width:200px;outline:none;color:var(--text-body);background:var(--card-bg)}
.usr-search:focus{border-color:var(--c-secondary);box-shadow:0 0 0 3px var(--focus-ring)}
.usr-select{padding:8px 12px;border-radius:var(--input-radius,8px);border:1px solid var(--input-border);font-size:var(--fs-sm);color:var(--text-body);background:var(--card-bg);outline:none}
.usr-card{background:var(--card-bg);border:1px solid var(--card-border,var(--border-color));border-radius:var(--card-radius,12px);overflow:hidden}
.usr-tbl{width:100%;border-collapse:collapse}
.usr-tbl th{text-align:left;padding:12px 16px;background:var(--table-header-bg);font-weight:600;color:var(--text-secondary);font-size:var(--fs-sm);border-bottom:2px solid var(--border-color)}
.usr-tbl td{padding:12px 16px;border-bottom:1px solid var(--border-light);font-size:var(--fs-sm);color:var(--text-body);vertical-align:middle}
.usr-tbl tr:hover{background:var(--hover-bg)}
.usr-avatar{width:36px;height:36px;background:var(--c-primary);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--card-bg);font-weight:600;font-size:14px;flex-shrink:0}
.usr-name{display:flex;align-items:center;gap:12px}
.usr-badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:6px;font-size:var(--fs-xs);font-weight:600}
.usr-badge-active{background:var(--c-success-light);color:var(--c-success)}
.usr-badge-inactive{background:var(--c-danger-light);color:var(--c-danger)}
.usr-badge-role{background:var(--c-secondary-light);color:var(--c-secondary)}
.usr-actions{display:flex;gap:4px}
.usr-btn{padding:6px 10px;border-radius:var(--btn-radius,8px);font-size:var(--fs-xs);cursor:pointer;border:1px solid var(--border-color);background:var(--card-bg);color:var(--text-secondary);transition:all .15s;text-decoration:none;display:inline-flex;align-items:center;gap:4px}
.usr-btn:hover{background:var(--hover-bg);border-color:var(--hover-border)}
.usr-btn-danger{color:var(--c-danger);border-color:var(--c-danger-border)}
.usr-btn-danger:hover{background:var(--c-danger-light)}
.usr-btn-primary{background:var(--c-primary);color:var(--card-bg);border-color:var(--c-primary)}
.usr-btn-primary:hover{background:var(--c-primary-hover)}
.usr-pager{padding:16px;display:flex;justify-content:center}
.usr-empty{padding:60px;text-align:center;color:var(--text-muted)}
.usr-empty i{font-size:48px;display:block;margin-bottom:16px;opacity:.4}
</style>
@endpush

@section('content')
<div class="usr-head">
    <h2 class="usr-title">Admin Users</h2>
    <div class="usr-tools">
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <input type="text" name="search" class="usr-search" placeholder="Search admins..." value="{{ request('search') }}">
            <select name="role" class="usr-select" onchange="this.form.submit()">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
            <select name="status" class="usr-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @if(request('search') || request('role') || request('status'))
                <a href="{{ route('admin.users.index') }}" class="usr-btn" title="Clear filters"><i class="fas fa-times"></i></a>
            @endif
        </form>
        <button class="usr-btn usr-btn-primary" onclick="scOpenModal('createAdminModal')"><i class="fas fa-plus"></i> Add Admin</button>
    </div>
</div>

@if(session('success'))
    <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="danger" :dismissible="true">{{ session('error') }}</x-alert>
@endif
@if($errors->any())
    <x-alert type="danger" :dismissible="true">
        <strong>Please fix the following errors:</strong>
        <ul style="margin:8px 0 0;padding-left:20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
    <script>document.addEventListener('DOMContentLoaded',function(){scOpenModal('createAdminModal');});</script>
@endif

<div class="usr-card">
    @if($admins->isEmpty())
        <div class="usr-empty"><i class="fas fa-users"></i><p>No admin users found.</p></div>
    @else
        <div style="overflow-x:auto;">
            <table class="usr-tbl">
                <thead>
                    <tr>
                        <th style="width:50px;">ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($admins as $admin)
                    <tr>
                        <td style="color:var(--text-faint);">{{ $admin->id }}</td>
                        <td>
                            <div class="usr-name">
                                <div class="usr-avatar">{{ strtoupper(mb_substr($admin->name, 0, 1)) }}</div>
                                <span style="font-weight:600;color:var(--text-heading);">{{ $admin->name }}</span>
                            </div>
                        </td>
                        <td><span style="font-family:var(--font-mono);color:var(--text-secondary);">{{ $admin->username }}</span></td>
                        <td>{{ $admin->email }}</td>
                        <td><span class="usr-badge usr-badge-role">{{ $admin->role->name ?? 'No Role' }}</span></td>
                        <td>
                            <span class="usr-badge {{ $admin->is_active ? 'usr-badge-active' : 'usr-badge-inactive' }}">
                                {{ $admin->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="color:var(--text-faint);font-size:var(--fs-xs);">
                            {{ $admin->datetime_lastlogin ? $admin->datetime_lastlogin->format('d M Y, H:i') : 'Never' }}
                        </td>
                        <td>
                            <div class="usr-actions">
                                <a href="{{ route('admin.users.edit', $admin->getRouteToken()) }}" class="usr-btn" title="Edit"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('admin.users.toggle-status', $admin->getRouteToken()) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="usr-btn" title="{{ $admin->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $admin->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $admin->getRouteToken()) }}" style="display:inline;" onsubmit="return confirm('Delete this admin?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="usr-btn usr-btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($admins->hasPages())
            <div class="usr-pager">{{ $admins->links('pagination::simple-bootstrap-4') }}</div>
        @endif
    @endif
</div>

{{-- Create Modal --}}
<x-modal id="createAdminModal" title="Add New Admin" size="md">
    <form id="createAdminForm" action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        <x-form-group label="Name" name="name" :required="true">
            <x-input name="name" required />
        </x-form-group>
        <x-form-group label="Email" name="email" :required="true">
            <x-input type="email" name="email" required />
        </x-form-group>
        <x-form-group label="Username" name="username" :required="true">
            <x-input name="username" required />
        </x-form-group>
        <x-form-group label="Password" name="password" :required="true">
            <x-input type="password" name="password" required minlength="8" />
        </x-form-group>
        <x-form-group label="Confirm Password" name="password_confirmation" :required="true">
            <x-input type="password" name="password_confirmation" required minlength="8" />
        </x-form-group>
        <x-form-group label="Role" name="role_id" :required="true">
            <x-select name="role_id" :options="$roles->pluck('name', 'id')->toArray()" placeholder="Select Role" required />
        </x-form-group>
        <div class="sc-form-group">
            <label class="sc-checkbox-label">
                <input type="checkbox" name="is_active" checked>
                <span>Active</span>
            </label>
        </div>
        <x-slot:footer>
            <x-button variant="secondary" onclick="scCloseModal('createAdminModal')">Cancel</x-button>
            <x-button type="submit" form="createAdminForm">Create Admin</x-button>
        </x-slot:footer>
    </form>
</x-modal>
@endsection
