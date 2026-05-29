@extends('admin.layouts.app')
@section('title', 'Email Templates')

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">Email Templates</h2>
    <a href="{{ route('admin.email-templates.create') }}" class="sc-btn sc-btn--primary"><i class="fas fa-plus"></i> Add Template</a>
</div>

@if(session('success'))
    <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="danger" :dismissible="true">{{ session('error') }}</x-alert>
@endif

{{-- Filter --}}
<x-card>
    <form action="{{ route('admin.email-templates.index') }}" method="GET" class="sc-grid sc-grid--3" style="align-items: end;">
        <x-form-group label="Scope" name="scope">
            <select name="scope" class="sc-select">
                <option value="">All Templates</option>
                <option value="global" {{ ($filters['scope'] ?? '') === 'global' ? 'selected' : '' }}>System Defaults Only</option>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}" {{ ($filters['scope'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                @endforeach
            </select>
        </x-form-group>
        <div>
            <x-button type="submit" icon="fas fa-filter">Filter</x-button>
            <a href="{{ route('admin.email-templates.index') }}" class="sc-btn sc-btn--secondary">Clear</a>
        </div>
    </form>
</x-card>

<x-card :padding="false">
    <div class="sc-overflow-x">
        <table class="sc-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Slug</th>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Scope</th>
                    <th>SMTP</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $tpl)
                <tr>
                    <td>{{ $tpl->id }}</td>
                    <td><code>{{ $tpl->slug }}</code></td>
                    <td>{{ $tpl->name }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($tpl->subject, 40) }}</td>
                    <td>
                        @if($tpl->isGlobal())
                            <x-badge variant="info">System Default</x-badge>
                        @else
                            <x-badge variant="secondary">{{ $tpl->company->company_name ?? '-' }}</x-badge>
                        @endif
                    </td>
                    <td>{{ $tpl->smtp->name ?? 'Default' }}</td>
                    <td>
                        <x-badge :variant="$tpl->status === 'active' ? 'active' : 'inactive'">{{ ucfirst($tpl->status) }}</x-badge>
                    </td>
                    <td>
                        <div class="sc-actions">
                            <a href="{{ route('admin.email-templates.preview', $tpl->id) }}" class="sc-btn sc-btn--icon" title="Preview" target="_blank"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.email-templates.edit', $tpl->id) }}" class="sc-btn sc-btn--icon sc-btn--edit" title="Edit"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.email-templates.toggle-status', $tpl->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="sc-btn sc-btn--icon sc-btn--toggle" title="{{ $tpl->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $tpl->status === 'active' ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <button type="button" class="sc-btn sc-btn--icon sc-btn--delete" title="Delete"
                                onclick="scConfirmDelete('deleteModal', '{{ route('admin.email-templates.destroy', $tpl->id) }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="sc-text-center sc-text-muted">No templates found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

<x-confirm-modal id="deleteModal" title="Delete Template" message="Are you sure you want to delete this email template?" />
@endsection
