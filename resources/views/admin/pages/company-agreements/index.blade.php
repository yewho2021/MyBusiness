@extends('admin.layouts.app')
@section('title', 'Company Agreements')

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">Company Agreements (T&C)</h2>
    <a href="{{ route('admin.company-agreements.create') }}" class="sc-btn sc-btn--primary">
        <i class="fas fa-plus"></i> New Version
    </a>
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
                    <th>#</th>
                    <th>Version</th>
                    <th>Title</th>
                    <th>Companies</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agreements as $agreement)
                <tr>
                    <td>{{ $agreement->id }}</td>
                    <td><code>v{{ $agreement->version }}</code></td>
                    <td>{{ $agreement->title }}</td>
                    <td>{{ $agreement->companies_count }} accepted</td>
                    <td>
                        @if($agreement->is_active)
                            <x-badge variant="active">Active</x-badge>
                        @else
                            <x-badge variant="inactive">Inactive</x-badge>
                        @endif
                    </td>
                    <td>{{ $agreement->created_at?->format('d M Y, h:iA') ?? '-' }}</td>
                    <td>
                        <div class="sc-actions">
                            <a href="{{ route('admin.company-agreements.preview', $agreement->id) }}" class="sc-btn sc-btn--icon" title="Preview" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.company-agreements.edit', $agreement->id) }}" class="sc-btn sc-btn--icon sc-btn--edit" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.company-agreements.toggle-active', $agreement->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="sc-btn sc-btn--icon sc-btn--toggle" title="{{ $agreement->is_active ? 'Deactivate' : 'Set as Active' }}">
                                    <i class="fas fa-{{ $agreement->is_active ? 'ban' : 'check-circle' }}"></i>
                                </button>
                            </form>
                            <button type="button" class="sc-btn sc-btn--icon sc-btn--delete" title="Delete"
                                onclick="scConfirmDelete('deleteAgreementModal', '{{ route('admin.company-agreements.destroy', $agreement->id) }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="sc-text-center sc-text-muted">No agreements found. Create your first version.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

<x-confirm-modal id="deleteAgreementModal" title="Delete Agreement" message="Are you sure you want to delete this agreement version? This action cannot be undone." />
@endsection
