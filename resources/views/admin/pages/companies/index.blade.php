@extends('admin.layouts.app')
@section('title', 'Companies')

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">Companies</h2>
</div>

@if(session('success'))
    <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="danger" :dismissible="true">{{ session('error') }}</x-alert>
@endif

{{-- Stats Cards --}}
<div class="sc-grid sc-grid--4" style="margin-bottom: 1.5rem;">
    <div class="sc-card sc-stat-card">
        <div class="sc-card-body">
            <div class="sc-stat-value">{{ $stats['total'] }}</div>
            <div class="sc-stat-label">Total Companies</div>
        </div>
    </div>
    <div class="sc-card sc-stat-card">
        <div class="sc-card-body">
            <div class="sc-stat-value" style="color: var(--c-success);">{{ $stats['active'] }}</div>
            <div class="sc-stat-label">Active</div>
        </div>
    </div>
    <div class="sc-card sc-stat-card">
        <div class="sc-card-body">
            <div class="sc-stat-value" style="color: var(--c-warning);">{{ $stats['pending'] }}</div>
            <div class="sc-stat-label">Pending</div>
        </div>
    </div>
    <div class="sc-card sc-stat-card">
        <div class="sc-card-body">
            <div class="sc-stat-value" style="color: var(--c-danger);">{{ $stats['suspended'] }}</div>
            <div class="sc-stat-label">Suspended</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<x-card>
    <form action="{{ route('admin.companies.index') }}" method="GET" class="sc-grid sc-grid--3" style="align-items: end;">
        <x-form-group label="Status" name="status">
            <x-select name="status" :options="['' => 'All Statuses', 'pending' => 'Pending', 'active' => 'Active', 'suspended' => 'Suspended', 'inactive' => 'Inactive']" :value="$filters['status'] ?? ''" />
        </x-form-group>
        <x-form-group label="Search" name="search">
            <x-input name="search" :value="$filters['search'] ?? ''" placeholder="Company name, code, email..." />
        </x-form-group>
        <div>
            <x-button type="submit" icon="fas fa-search">Filter</x-button>
            <a href="{{ route('admin.companies.index') }}" class="sc-btn sc-btn--secondary">Clear</a>
        </div>
    </form>
</x-card>

{{-- Company List --}}
<x-card :padding="false">
    <div class="sc-overflow-x">
        <table class="sc-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Company</th>
                    <th>Contact Person</th>
                    <th>Email</th>
                    <th>Setup</th>
                    <th>Partners</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                <tr>
                    <td><code>{{ $company->code }}</code></td>
                    <td><strong>{{ $company->company_name }}</strong></td>
                    <td>{{ $company->name }}</td>
                    <td>{{ $company->email }}</td>
                    <td>
                        @if($company->setup_step >= 3)
                            <x-badge variant="active">Complete</x-badge>
                        @else
                            <x-badge variant="warning">Step {{ $company->setup_step }}/3</x-badge>
                        @endif
                    </td>
                    <td>{{ $company->partners_count }}</td>
                    <td>{{ $company->products_count }}</td>
                    <td>
                        @php
                            $statusVariant = match($company->status) {
                                'active' => 'active',
                                'pending' => 'warning',
                                'suspended' => 'danger',
                                default => 'inactive',
                            };
                        @endphp
                        <x-badge :variant="$statusVariant">{{ ucfirst($company->status) }}</x-badge>
                    </td>
                    <td>{{ $company->created_at?->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.companies.show', $company->id) }}" class="sc-btn sc-btn--icon sc-btn--edit" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="sc-text-center sc-text-muted">No companies found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

@if($companies->hasPages())
<div style="margin-top: 1rem;">
    {{ $companies->appends($filters)->links() }}
</div>
@endif
@endsection
