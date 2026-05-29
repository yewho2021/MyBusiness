@extends('admin.layouts.app')
@section('title', 'Admin List')

@section('content')
<div class="page-header">
    <h2>Admin Users</h2>
    <button class="btn btn-primary" onclick="openCreateModal()">
        <i class="fas fa-plus"></i> Add Admin
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                <tr>
                    <td>{{ $admin->id }}</td>
                    <td>
                        <div class="user-info-cell">
                            <div class="avatar">{{ strtoupper(substr($admin->name, 0, 1)) }}</div>
                            <span>{{ $admin->name }}</span>
                        </div>
                    </td>
                    <td>{{ $admin->username }}</td>
                    <td>{{ $admin->email }}</td>
                    <td>
                        <span class="badge badge-{{ $admin->role->slug ?? 'default' }}">
                            {{ $admin->role->name ?? 'No Role' }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge {{ $admin->is_active ? 'active' : 'inactive' }}">
                            {{ $admin->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>{{ $admin->datetime_lastlogin ? $admin->datetime_lastlogin->format('M d, Y H:i') : 'Never' }}</td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('admin.users.edit', $admin->id) }}" class="btn-icon btn-edit" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.users.toggle-status', $admin->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-icon btn-toggle" title="{{ $admin->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $admin->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.users.destroy', $admin->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this admin?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon btn-delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">No admins found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div class="modal-overlay" id="createModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add New Admin</h3>
            <button class="modal-close" onclick="closeCreateModal()">&times;</button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Username <span class="required">*</span></label>
                    <input type="text" name="username" class="form-control" required>
                </div>
<div class="form-group">
    <label>Password <span class="required">*</span></label>
    <input type="password" name="password" class="form-control" required minlength="6">
</div>
<div class="form-group">
    <label>Confirm Password <span class="required">*</span></label>
    <input type="password" name="password_confirmation" class="form-control" required minlength="6">
</div>
                <div class="form-group">
                    <label>Role <span class="required">*</span></label>
                    <select name="role_id" class="form-control" required>
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" checked>
                        <span>Active</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Admin</button>
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
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-danger { background: #dbeafe; color: #991b1b; border: 1px solid #bfdbfe; }
    .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .card-body { padding: 0; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; }
    .data-table th { background: #f8fafc; font-weight: 600; font-size: 13px; color: #64748b; text-transform: uppercase; }
    .data-table td { font-size: 14px; color: #334155; }
    .data-table tbody tr:hover { background: #f8fafc; }
    .user-info-cell { display: flex; align-items: center; gap: 12px; }
    .avatar { width: 36px; height: 36px; background: #dc2626; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; font-size: 14px; }
    .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
    .badge-administrator { background: #f3e8ff; color: #7c3aed; }
    .badge-supervisor { background: #fef3c7; color: #d97706; }
    .badge-staff { background: #dcfce7; color: #16a34a; }
    .badge-default { background: #f1f5f9; color: #64748b; }
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
    .status-badge.active { background: #dcfce7; color: #16a34a; }
    .status-badge.inactive { background: #dbeafe; color: #dc2626; }
    .action-buttons { display: flex; gap: 8px; }
    .btn-icon { width: 32px; height: 32px; border-radius: 6px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; }
    .btn-edit { background: #dbeafe; color: #2563eb; }
    .btn-edit:hover { background: #bfdbfe; }
    .btn-toggle { background: #fef3c7; color: #d97706; }
    .btn-toggle:hover { background: #fde68a; }
    .btn-delete { background: #fee2e2; color: #dc2626; }
    .btn-delete:hover { background: #fecaca; }
    .text-center { text-align: center; }
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; }
    .modal-overlay.show { display: flex; }
    .modal { background: #fff; border-radius: 12px; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid #e2e8f0; }
    .modal-header h3 { font-size: 18px; font-weight: 600; }
    .modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b; }
    .modal-body { padding: 20px; }
    .modal-footer { display: flex; justify-content: flex-end; gap: 12px; padding: 20px; border-top: 1px solid #e2e8f0; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 6px; }
    .form-control { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
    .form-control:focus { outline: none; border-color: #2563eb; }
    .required { color: #2563eb; }
    .checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
    .checkbox-label input { width: 18px; height: 18px; }
</style>
@endpush

@push('scripts')
<script>
    function openCreateModal() {
        document.getElementById('createModal').classList.add('show');
    }
    function closeCreateModal() {
        document.getElementById('createModal').classList.remove('show');
    }
    document.getElementById('createModal').addEventListener('click', function(e) {
        if (e.target === this) closeCreateModal();
    });
</script>
@endpush