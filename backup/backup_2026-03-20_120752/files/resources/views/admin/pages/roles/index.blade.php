@extends('admin.layouts.app')
@section('title', 'Role List')

@section('content')
<div class="page-header">
    <h2>Roles</h2>
    <button class="btn btn-primary" onclick="openCreateModal()">
        <i class="fas fa-plus"></i> Add Role
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
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Level</th>
                    <th>Admins</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr>
                    <td>{{ $role->id }}</td>
                    <td>
                        <span class="badge badge-{{ $role->slug }}">{{ $role->name }}</span>
                    </td>
                    <td><code>{{ $role->slug }}</code></td>
                    <td>{{ $role->description ?? '-' }}</td>
                    <td>{{ $role->level }}</td>
                    <td>{{ $role->admins_count }} user(s)</td>
                    <td>
                        <span class="status-badge {{ $role->is_active ? 'active' : 'inactive' }}">
                            {{ $role->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn-icon btn-edit" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.roles.toggle-status', $role->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-icon btn-toggle" title="{{ $role->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $role->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this role?');">
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
                    <td colspan="8" class="text-center">No roles found.</td>
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
            <h3>Add New Role</h3>
            <button class="modal-close" onclick="closeCreateModal()">&times;</button>
        </div>
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Manager">
                </div>
                <div class="form-group">
                    <label>Slug <span class="required">*</span></label>
                    <input type="text" name="slug" class="form-control" required placeholder="e.g. manager">
                    <small class="form-text">Unique identifier. Use lowercase, no spaces.</small>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Brief description of this role"></textarea>
                </div>
                <div class="form-group">
                    <label>Level <span class="required">*</span></label>
                    <input type="number" name="level" class="form-control" required min="1" max="99" value="5">
                    <small class="form-text">Lower number = higher priority. Admin=1, Supervisor=2, Staff=3</small>
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
                <button type="submit" class="btn btn-primary">Create Role</button>
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
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
.card-body { padding: 0; overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; }
    .data-table th { background: #f8fafc; font-weight: 600; font-size: 13px; color: #64748b; text-transform: uppercase; }
    .data-table td { font-size: 14px; color: #334155; }
    .data-table tbody tr:hover { background: #f8fafc; }
    code { background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 13px; color: #475569; }
    .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
    .badge-administrator { background: #ede9fe; color: #7c3aed; }
    .badge-supervisor { background: #fef3c7; color: #d97706; }
    .badge-staff { background: #dcfce7; color: #16a34a; }
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
    .status-badge.active { background: #dcfce7; color: #16a34a; }
    .status-badge.inactive { background: #fee2e2; color: #dc2626; }
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
    .form-control:focus { outline: none; border-color: #4f46e5; }
    .form-text { font-size: 12px; color: #9ca3af; margin-top: 4px; }
    .required { color: #dc2626; }
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