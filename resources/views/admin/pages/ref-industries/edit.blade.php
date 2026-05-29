@extends('admin.layouts.app')
@section('title', 'Edit Industry')

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">
        <a href="{{ route('admin.ref-industries.index') }}" class="sc-back-link"><i class="fas fa-arrow-left"></i></a>
        Edit Industry: {{ $industry->name }}
    </h2>
</div>

@if(session('success'))
    <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
@endif
@if($errors->any())
    <x-alert type="danger" :dismissible="true">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </x-alert>
@endif

{{-- Industry Details --}}
<x-card title="Industry Details">
    <form action="{{ route('admin.ref-industries.update', $industry->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="sc-grid sc-grid--2">
            <x-form-group label="Industry Name" name="name" :required="true">
                <x-input name="name" :value="old('name', $industry->name)" required />
            </x-form-group>
            <x-form-group label="Sort Order" name="sort_order">
                <x-input type="number" name="sort_order" :value="old('sort_order', $industry->sort_order)" min="0" />
            </x-form-group>
        </div>
        <div class="sc-card-footer">
            <a href="{{ route('admin.ref-industries.index') }}" class="sc-btn sc-btn--secondary">Cancel</a>
            <x-button type="submit">Save Changes</x-button>
        </div>
    </form>
</x-card>

{{-- Subcategories --}}
<div class="sc-page-header" style="margin-top: 2rem;">
    <h3 class="sc-page-title">Subcategories ({{ $industry->subcategories->count() }})</h3>
    <x-button icon="fas fa-plus" onclick="scOpenModal('createSubModal')">Add Subcategory</x-button>
</div>

<x-card :padding="false">
    <div class="sc-overflow-x">
        <table class="sc-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subcategory Name</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($industry->subcategories as $sub)
                <tr>
                    <td>{{ $sub->id }}</td>
                    <td>{{ $sub->name }}</td>
                    <td>{{ $sub->sort_order }}</td>
                    <td>
                        <x-badge :variant="$sub->status === 'active' ? 'active' : 'inactive'">
                            {{ ucfirst($sub->status) }}
                        </x-badge>
                    </td>
                    <td>
                        <div class="sc-actions">
                            <button type="button" class="sc-btn sc-btn--icon sc-btn--edit" title="Edit"
                                onclick="editSub({{ $sub->id }}, '{{ addslashes($sub->name) }}', {{ $sub->sort_order }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.ref-subcategories.toggle-status', $sub->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="sc-btn sc-btn--icon sc-btn--toggle" title="{{ $sub->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $sub->status === 'active' ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <button type="button" class="sc-btn sc-btn--icon sc-btn--delete" title="Delete"
                                onclick="scConfirmDelete('deleteSubModal', '{{ route('admin.ref-subcategories.destroy', $sub->id) }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="sc-text-center sc-text-muted">No subcategories found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

{{-- Create Subcategory Modal --}}
<x-modal id="createSubModal" title="Add Subcategory" size="md">
    <form action="{{ route('admin.ref-industries.subcategories.store', $industry->id) }}" method="POST">
        @csrf
        <x-form-group label="Subcategory Name" name="name" :required="true">
            <x-input name="name" placeholder="e.g. Software Development" required />
        </x-form-group>
        <x-form-group label="Sort Order" name="sort_order">
            <x-input type="number" name="sort_order" value="0" min="0" />
        </x-form-group>
        <x-slot:footer>
            <x-button variant="secondary" onclick="scCloseModal('createSubModal')">Cancel</x-button>
            <x-button type="submit">Add Subcategory</x-button>
        </x-slot:footer>
    </form>
</x-modal>

{{-- Edit Subcategory Modal --}}
<x-modal id="editSubModal" title="Edit Subcategory" size="md">
    <form id="editSubForm" method="POST">
        @csrf
        @method('PUT')
        <x-form-group label="Subcategory Name" name="name" :required="true">
            <x-input id="editSubName" name="name" required />
        </x-form-group>
        <x-form-group label="Sort Order" name="sort_order">
            <x-input type="number" id="editSubSort" name="sort_order" min="0" />
        </x-form-group>
        <x-slot:footer>
            <x-button variant="secondary" onclick="scCloseModal('editSubModal')">Cancel</x-button>
            <x-button type="submit">Save Changes</x-button>
        </x-slot:footer>
    </form>
</x-modal>

{{-- Delete Confirmation Modal --}}
<x-confirm-modal id="deleteSubModal" title="Delete Subcategory" message="Are you sure you want to delete this subcategory? This action cannot be undone." />
@endsection

@push('scripts')
<script>
function editSub(id, name, sortOrder) {
    document.getElementById('editSubForm').action = '/admin/ref-subcategories/' + id;
    document.getElementById('editSubName').value = name;
    document.getElementById('editSubSort').value = sortOrder;
    scOpenModal('editSubModal');
}
</script>
@endpush
