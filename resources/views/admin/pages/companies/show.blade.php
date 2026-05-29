@extends('admin.layouts.app')
@section('title', $company->company_name)

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">
        <a href="{{ route('admin.companies.index') }}" class="sc-back-link"><i class="fas fa-arrow-left"></i></a>
        {{ $company->company_name }}
    </h2>
    <div>
        @php
            $statusVariant = match($company->status) {
                'active' => 'active',
                'pending' => 'warning',
                'suspended' => 'danger',
                default => 'inactive',
            };
        @endphp
        <x-badge :variant="$statusVariant" style="font-size: 0.9rem; padding: 0.3rem 0.75rem;">{{ ucfirst($company->status) }}</x-badge>
    </div>
</div>

@if(session('success'))
    <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="danger" :dismissible="true">{{ session('error') }}</x-alert>
@endif

<div class="sc-grid sc-grid--2">
    {{-- Company Info --}}
    <x-card title="Company Information">
        <table class="sc-table sc-table--detail">
            <tr><th>Code</th><td><code>{{ $company->code }}</code></td></tr>
            <tr><th>Company Name</th><td>{{ $company->company_name }}</td></tr>
            <tr><th>Contact Person</th><td>{{ $company->name }}</td></tr>
            <tr><th>Email</th><td>{{ $company->email }}</td></tr>
            <tr><th>Mobile</th><td>{{ $company->mobile_no }}</td></tr>
            <tr><th>Timezone</th><td>{{ $company->timezone }}</td></tr>
            <tr>
                <th>Setup Progress</th>
                <td>
                    @if($company->setup_step >= 3)
                        <x-badge variant="active">Complete ({{ $company->setup_step }}/3)</x-badge>
                    @else
                        <x-badge variant="warning">Step {{ $company->setup_step }}/3</x-badge>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Email Verified</th>
                <td>{{ $company->email_verified_at?->format('d M Y, h:iA') ?? 'Not verified' }}</td>
            </tr>
            <tr>
                <th>Mobile Verified</th>
                <td>{{ $company->mobile_verified_at?->format('d M Y, h:iA') ?? 'Not verified' }}</td>
            </tr>
            <tr><th>Created</th><td>{{ $company->created_at?->format('d M Y, h:iA') }}</td></tr>
            <tr><th>Updated</th><td>{{ $company->updated_at?->format('d M Y, h:iA') ?? '-' }}</td></tr>
        </table>
    </x-card>

    {{-- Agreement & Industries --}}
    <div>
        <x-card title="Agreement">
            @if($company->agreement)
                <p><strong>{{ $company->agreement->title }}</strong> (v{{ $company->agreement->version }})</p>
                <p class="sc-text-muted">Accepted: {{ $company->agreement_accepted_at?->format('d M Y, h:iA') ?? 'Not yet' }}</p>
            @else
                <p class="sc-text-muted">No agreement accepted.</p>
            @endif
        </x-card>

        <x-card title="Industries" style="margin-top: 1rem;">
            @if($company->industries->count())
                @foreach($company->industries as $sub)
                    <x-badge variant="info" style="margin: 0.15rem;">{{ $sub->industry->name }} &raquo; {{ $sub->name }}</x-badge>
                @endforeach
            @else
                <p class="sc-text-muted">No industries selected.</p>
            @endif
        </x-card>

        {{-- Quick Stats --}}
        <x-card title="Quick Stats" style="margin-top: 1rem;">
            <div class="sc-grid sc-grid--3">
                <div class="sc-text-center">
                    <div style="font-size: 1.5rem; font-weight: 700;">{{ $company->admins_count }}</div>
                    <div class="sc-text-muted">Admins</div>
                </div>
                <div class="sc-text-center">
                    <div style="font-size: 1.5rem; font-weight: 700;">{{ $company->partners_count }}</div>
                    <div class="sc-text-muted">Partners</div>
                </div>
                <div class="sc-text-center">
                    <div style="font-size: 1.5rem; font-weight: 700;">{{ $company->products_count }}</div>
                    <div class="sc-text-muted">Products</div>
                </div>
            </div>
        </x-card>
    </div>
</div>

{{-- Status Actions --}}
<x-card title="Actions" style="margin-top: 1.5rem;">
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        @if($company->status !== 'active')
        <form action="{{ route('admin.companies.update-status', $company->id) }}" method="POST" style="display:inline;">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="active">
            <x-button type="submit" variant="success" icon="fas fa-check-circle">Approve / Activate</x-button>
        </form>
        @endif

        @if($company->status !== 'suspended')
        <form action="{{ route('admin.companies.update-status', $company->id) }}" method="POST" style="display:inline;">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="suspended">
            <x-button type="submit" variant="warning" icon="fas fa-ban">Suspend</x-button>
        </form>
        @endif

        @if($company->status !== 'inactive')
        <form action="{{ route('admin.companies.update-status', $company->id) }}" method="POST" style="display:inline;">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="inactive">
            <x-button type="submit" variant="secondary" icon="fas fa-power-off">Deactivate</x-button>
        </form>
        @endif

        <button type="button" class="sc-btn sc-btn--danger"
            onclick="scConfirmDelete('deleteCompanyModal', '{{ route('admin.companies.destroy', $company->id) }}')">
            <i class="fas fa-trash"></i> Delete
        </button>
    </div>
</x-card>

{{-- Admin Users --}}
<x-card title="Admin Users ({{ $company->admins->count() }})" :padding="false" style="margin-top: 1.5rem;">
    <div class="sc-overflow-x">
        <table class="sc-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Owner</th>
                    <th>Last Login</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($company->admins as $admin)
                <tr>
                    <td>{{ $admin->name }}</td>
                    <td>{{ $admin->email }}</td>
                    <td><x-badge variant="info">{{ $admin->role->name ?? '-' }}</x-badge></td>
                    <td>{{ $admin->is_owner ? 'Yes' : '-' }}</td>
                    <td>{{ $admin->last_login_at?->format('d M Y, h:iA') ?? 'Never' }}</td>
                    <td>
                        <x-badge :variant="$admin->status === 'active' ? 'active' : 'inactive'">
                            {{ ucfirst($admin->status) }}
                        </x-badge>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="sc-text-center sc-text-muted">No admin users.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

{{-- Recent Partners --}}
<x-card title="Recent Partners ({{ $company->partners_count }} total)" :padding="false" style="margin-top: 1.5rem;">
    <div class="sc-overflow-x">
        <table class="sc-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Email</th>
                    <th>Referral Code</th>
                    <th>Status</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($company->partners as $partner)
                <tr>
                    <td>{{ $partner->name }}</td>
                    <td><x-badge variant="info">{{ ucfirst($partner->partner_type) }}</x-badge></td>
                    <td>{{ $partner->email }}</td>
                    <td><code>{{ $partner->referral_code ?? '-' }}</code></td>
                    <td>
                        @php
                            $pVariant = match($partner->status) {
                                'active' => 'active',
                                'pending' => 'warning',
                                'suspended' => 'danger',
                                'blacklisted' => 'danger',
                                default => 'inactive',
                            };
                        @endphp
                        <x-badge :variant="$pVariant">{{ ucfirst($partner->status) }}</x-badge>
                    </td>
                    <td>{{ $partner->created_at?->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="sc-text-center sc-text-muted">No partners yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

{{-- Recent Products --}}
<x-card title="Recent Products ({{ $company->products_count }} total)" :padding="false" style="margin-top: 1.5rem;">
    <div class="sc-overflow-x">
        <table class="sc-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>SKU</th>
                    <th>Price (RM)</th>
                    <th>Stock</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($company->products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td><x-badge variant="info">{{ ucfirst($product->type) }}</x-badge></td>
                    <td><code>{{ $product->sku ?? '-' }}</code></td>
                    <td>{{ number_format($product->base_price, 2) }}</td>
                    <td>{{ $product->manage_stock ? $product->stock_quantity : 'N/A' }}</td>
                    <td>
                        <x-badge :variant="$product->status === 'active' ? 'active' : 'inactive'">
                            {{ ucfirst($product->status) }}
                        </x-badge>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="sc-text-center sc-text-muted">No products yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

<x-confirm-modal id="deleteCompanyModal" title="Delete Company" message="Are you sure you want to delete this company? This will soft-delete the company and all associated data." />
@endsection
