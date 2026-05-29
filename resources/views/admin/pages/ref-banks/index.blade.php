@extends('admin.layouts.app')
@section('title', 'Banks')

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">Banks</h2>
    <x-button icon="fas fa-plus" onclick="scOpenModal('createBankModal')">Add Bank</x-button>
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
                    <th>Bank Name</th>
                    <th>SWIFT Code</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banks as $bank)
                <tr>
                    <td>{{ $bank->id }}</td>
                    <td>{{ $bank->name }}</td>
                    <td><code>{{ $bank->swift_code ?? '-' }}</code></td>
                    <td>
                        <x-badge :variant="$bank->status === 'active' ? 'active' : 'inactive'">
                            {{ ucfirst($bank->status) }}
                        </x-badge>
                    </td>
                    <td>
                        <div class="sc-actions">
                            <a href="{{ route('admin.ref-banks.edit', $bank->id) }}" class="sc-btn sc-btn--icon sc-btn--edit" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.ref-banks.toggle-status', $bank->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="sc-btn sc-btn--icon sc-btn--toggle" title="{{ $bank->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $bank->status === 'active' ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <button type="button" class="sc-btn sc-btn--icon sc-btn--delete" title="Delete"
                                onclick="scConfirmDelete('deleteBankModal', '{{ route('admin.ref-banks.destroy', $bank->id) }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="sc-text-center sc-text-muted">No banks found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

{{-- Create Modal --}}
<x-modal id="createBankModal" title="Add New Bank" size="md">
    <form action="{{ route('admin.ref-banks.store') }}" method="POST">
        @csrf
        <x-form-group label="Bank Name" name="name" :required="true">
            <x-input name="name" placeholder="e.g. Maybank (Malayan Banking Berhad)" required />
        </x-form-group>
        <x-form-group label="SWIFT Code" name="swift_code">
            <x-input name="swift_code" placeholder="e.g. MBBEMYKL" />
        </x-form-group>
        <x-slot:footer>
            <x-button variant="secondary" onclick="scCloseModal('createBankModal')">Cancel</x-button>
            <x-button type="submit">Add Bank</x-button>
        </x-slot:footer>
    </form>
</x-modal>

{{-- Delete Confirmation Modal --}}
<x-confirm-modal id="deleteBankModal" title="Delete Bank" message="Are you sure you want to delete this bank? This action cannot be undone." />
@endsection
