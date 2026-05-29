@extends('admin.layouts.app')
@section('title', 'Role List')

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">Roles</h2>
    <x-button icon="fas fa-plus" onclick="scOpenModal('createRoleModal')">Add Role</x-button>
</div>

@if(session('success'))
    <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="danger" :dismissible="true">{{ session('error') }}</x-alert>
@endif

<x-card :padding="false">
    <div class="sc-overflow-x">
        <table class="sc-table">
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
                    <td><x-badge :variant="$role->slug">{{ $role->name }}</x-badge></td>
                    <td><code>{{ $role->slug }}</code></td>
                    <td>{{ $role->description ?? '-' }}</td>
                    <td>{{ $role->level }}</td>
                    <td>{{ $role->admins_count }} user(s)</td>
                    <td>
                        <x-badge :variant="$role->is_active ? 'active' : 'inactive'">
                            {{ $role->is_active ? 'Active' : 'Inactive' }}
                        </x-badge>
                    </td>
                    <td>
                        <div class="sc-actions">
                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="sc-btn sc-btn--icon sc-btn--edit" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.roles.toggle-status', $role->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="sc-btn sc-btn--icon sc-btn--toggle" title="{{ $role->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $role->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <button type="button" class="sc-btn sc-btn--icon sc-btn--delete" title="Delete"
                                onclick="scConfirmDelete('deleteRoleModal', '{{ route('admin.roles.destroy', $role->id) }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="sc-text-center sc-text-muted">No roles found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

{{-- Create Modal --}}
<x-modal id="createRoleModal" title="Add New Role" size="md">
    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf
        <x-form-group label="Name" name="name" :required="true">
            <x-input name="name" placeholder="e.g. Manager" required />
        </x-form-group>
        <x-form-group label="Slug" name="slug" :required="true" help="Unique identifier. Use lowercase, no spaces.">
            <x-input name="slug" placeholder="e.g. manager" required />
        </x-form-group>
        <x-form-group label="Description" name="description">
            <textarea name="description" class="sc-textarea" rows="2" placeholder="Brief description of this role"></textarea>
        </x-form-group>
        <x-form-group label="Level" name="level" :required="true" help="Lower number = higher priority. Admin=1, Supervisor=2, Staff=3">
            <x-input type="number" name="level" value="5" required min="1" max="99" />
        </x-form-group>
        <div class="sc-form-group">
            <label class="sc-checkbox-label">
                <input type="checkbox" name="is_active" checked>
                <span>Active</span>
            </label>
        </div>
        <x-slot:footer>
            <x-button variant="secondary" onclick="scCloseModal('createRoleModal')">Cancel</x-button>
            <x-button type="submit">Create Role</x-button>
        </x-slot:footer>
    </form>
</x-modal>

{{-- Delete Confirmation Modal --}}
<x-confirm-modal id="deleteRoleModal" title="Delete Role" message="Are you sure you want to delete this role? This action cannot be undone." />
@endsection
