@extends('admin.layouts.app')
@section('title', 'SMTP Configs')

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">Email SMTP Configurations</h2>
    <a href="{{ route('admin.email-configs.create') }}" class="sc-btn sc-btn--primary"><i class="fas fa-plus"></i> Add Config</a>
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
                    <th>Name</th>
                    <th>Scope</th>
                    <th>Host</th>
                    <th>Port</th>
                    <th>From</th>
                    <th>Encryption</th>
                    <th>Templates</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($configs as $config)
                <tr>
                    <td>{{ $config->id }}</td>
                    <td><strong>{{ $config->name }}</strong></td>
                    <td>
                        @if($config->isGlobal())
                            <x-badge variant="info">System Default</x-badge>
                        @else
                            <x-badge variant="secondary">{{ $config->company->company_name ?? '-' }}</x-badge>
                        @endif
                    </td>
                    <td><code>{{ $config->host }}</code></td>
                    <td>{{ $config->port }}</td>
                    <td>{{ $config->from_email }}</td>
                    <td><x-badge variant="info">{{ strtoupper($config->encryption) }}</x-badge></td>
                    <td>{{ $config->templates_count }}</td>
                    <td>
                        <x-badge :variant="$config->status === 'active' ? 'active' : 'inactive'">{{ ucfirst($config->status) }}</x-badge>
                    </td>
                    <td>
                        <div class="sc-actions">
                            <a href="{{ route('admin.email-configs.edit', $config->id) }}" class="sc-btn sc-btn--icon sc-btn--edit" title="Edit"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.email-configs.toggle-status', $config->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="sc-btn sc-btn--icon sc-btn--toggle" title="{{ $config->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $config->status === 'active' ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <button type="button" class="sc-btn sc-btn--icon sc-btn--delete" title="Delete"
                                onclick="scConfirmDelete('deleteModal', '{{ route('admin.email-configs.destroy', $config->id) }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="sc-text-center sc-text-muted">No SMTP configs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

<x-confirm-modal id="deleteModal" title="Delete SMTP Config" message="Are you sure? Templates using this config will lose their SMTP link." />
@endsection
