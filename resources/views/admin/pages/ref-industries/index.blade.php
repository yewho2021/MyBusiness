@extends('admin.layouts.app')
@section('title', 'Industries')

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">Industries</h2>
    <x-button icon="fas fa-plus" onclick="scOpenModal('createIndustryModal')">Add Industry</x-button>
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
                    <th>Industry Name</th>
                    <th>Subcategories</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($industries as $industry)
                <tr>
                    <td>{{ $industry->id }}</td>
                    <td>{{ $industry->name }}</td>
                    <td>{{ $industry->subcategories_count }} item(s)</td>
                    <td>{{ $industry->sort_order }}</td>
                    <td>
                        <x-badge :variant="$industry->status === 'active' ? 'active' : 'inactive'">
                            {{ ucfirst($industry->status) }}
                        </x-badge>
                    </td>
                    <td>
                        <div class="sc-actions">
                            <a href="{{ route('admin.ref-industries.edit', $industry->id) }}" class="sc-btn sc-btn--icon sc-btn--edit" title="Edit & Manage Subcategories">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.ref-industries.toggle-status', $industry->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="sc-btn sc-btn--icon sc-btn--toggle" title="{{ $industry->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $industry->status === 'active' ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <button type="button" class="sc-btn sc-btn--icon sc-btn--delete" title="Delete"
                                onclick="scConfirmDelete('deleteIndustryModal', '{{ route('admin.ref-industries.destroy', $industry->id) }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="sc-text-center sc-text-muted">No industries found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

{{-- Create Modal --}}
<x-modal id="createIndustryModal" title="Add New Industry" size="md">
    <form action="{{ route('admin.ref-industries.store') }}" method="POST">
        @csrf
        <x-form-group label="Industry Name" name="name" :required="true">
            <x-input name="name" placeholder="e.g. Technology & IT" required />
        </x-form-group>
        <x-form-group label="Sort Order" name="sort_order">
            <x-input type="number" name="sort_order" value="0" min="0" />
        </x-form-group>
        <x-slot:footer>
            <x-button variant="secondary" onclick="scCloseModal('createIndustryModal')">Cancel</x-button>
            <x-button type="submit">Add Industry</x-button>
        </x-slot:footer>
    </form>
</x-modal>

{{-- Delete Confirmation Modal --}}
<x-confirm-modal id="deleteIndustryModal" title="Delete Industry" message="Are you sure you want to delete this industry? This action cannot be undone." />
@endsection
