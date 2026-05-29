@extends('admin.layouts.app')
@section('title', 'Edit Role')

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">Edit Role</h2>
    <a href="{{ route('admin.roles.index') }}" class="sc-btn sc-btn--secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

@if($errors->any())
    <x-alert type="danger">
        <ul style="margin:0;padding-left:20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif

<x-card title="Role Information">
    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
            <x-form-group label="Name" name="name" :required="true">
                <x-input name="name" :value="$role->name" required />
            </x-form-group>
            <x-form-group label="Slug" name="slug" :required="true" help="Unique identifier. Use lowercase, no spaces.">
                <x-input name="slug" :value="$role->slug" required :readonly="$role->slug === 'administrator'" />
            </x-form-group>
        </div>

        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
            <x-form-group label="Level" name="level" :required="true" help="Lower number = higher priority. Admin=1, Supervisor=2, Staff=3">
                <x-input type="number" name="level" :value="$role->level" required min="1" max="99" />
            </x-form-group>
            <div class="sc-form-group">
                <label class="sc-label">Status</label>
                <div style="padding-top:8px;">
                    <label class="sc-checkbox-label">
                        <input type="checkbox" name="is_active" {{ $role->is_active ? 'checked' : '' }} {{ $role->slug === 'administrator' ? 'disabled' : '' }}>
                        <span>Active</span>
                    </label>
                    @if($role->slug === 'administrator')
                        <input type="hidden" name="is_active" value="1">
                        <p class="sc-form-help">Administrator role cannot be deactivated.</p>
                    @endif
                </div>
            </div>
        </div>

        <x-form-group label="Description" name="description">
            <textarea name="description" class="sc-textarea" rows="3">{{ old('description', $role->description) }}</textarea>
        </x-form-group>

        <div style="display:flex;justify-content:flex-end;gap:12px;padding-top:20px;border-top:1px solid var(--border-color,var(--border-color));margin-top:20px;">
            <a href="{{ route('admin.roles.index') }}" class="sc-btn sc-btn--secondary">Cancel</a>
            <x-button type="submit" icon="fas fa-save">Save Changes</x-button>
        </div>
    </form>
</x-card>
@endsection

@push('styles')
<style>
@media (max-width: 768px) {
    [style*="grid-template-columns: repeat(2"] { grid-template-columns: 1fr !important; }
}
</style>
@endpush
