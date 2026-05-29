@extends('admin.layouts.app')
@section('title', 'Edit SMTP Config')

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">
        <a href="{{ route('admin.email-configs.index') }}" class="sc-back-link"><i class="fas fa-arrow-left"></i></a>
        Edit: {{ $config->name }}
    </h2>
</div>

@if(session('success'))
    <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
@endif
@if($errors->any())
    <x-alert type="danger" :dismissible="true">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</x-alert>
@endif

<form action="{{ route('admin.email-configs.update', $config->id) }}" method="POST">
    @csrf
    @method('PUT')
    <x-card title="SMTP Details">
        <x-form-group label="Scope" name="company_id" help="Leave empty for system-wide default">
            <select name="company_id" class="sc-select">
                <option value="">System Default (Global)</option>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}" {{ old('company_id', $config->company_id) == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                @endforeach
            </select>
        </x-form-group>
        <x-form-group label="Config Name" name="name" :required="true">
            <x-input name="name" :value="old('name', $config->name)" required />
        </x-form-group>
        <div class="sc-grid sc-grid--3">
            <x-form-group label="SMTP Host" name="host" :required="true">
                <x-input name="host" :value="old('host', $config->host)" required />
            </x-form-group>
            <x-form-group label="Port" name="port" :required="true">
                <x-input type="number" name="port" :value="old('port', $config->port)" required min="1" max="65535" />
            </x-form-group>
            <x-form-group label="Encryption" name="encryption" :required="true">
                <select name="encryption" class="sc-select" required>
                    @foreach(['tls', 'ssl', 'none'] as $enc)
                        <option value="{{ $enc }}" {{ old('encryption', $config->encryption) === $enc ? 'selected' : '' }}>{{ strtoupper($enc) }}</option>
                    @endforeach
                </select>
            </x-form-group>
        </div>
        <div class="sc-grid sc-grid--2">
            <x-form-group label="Username" name="username" :required="true">
                <x-input name="username" :value="old('username', $config->username)" required />
            </x-form-group>
            <x-form-group label="Password" name="password" help="Leave blank to keep current password">
                <x-input type="password" name="password" placeholder="Leave blank to keep current" />
            </x-form-group>
        </div>
        <div class="sc-grid sc-grid--3">
            <x-form-group label="From Name" name="from_name" :required="true">
                <x-input name="from_name" :value="old('from_name', $config->from_name)" required />
            </x-form-group>
            <x-form-group label="From Email" name="from_email" :required="true">
                <x-input type="email" name="from_email" :value="old('from_email', $config->from_email)" required />
            </x-form-group>
            <x-form-group label="Reply-To" name="reply_to">
                <x-input type="email" name="reply_to" :value="old('reply_to', $config->reply_to)" />
            </x-form-group>
        </div>
        <div class="sc-card-footer">
            <a href="{{ route('admin.email-configs.index') }}" class="sc-btn sc-btn--secondary">Cancel</a>
            <x-button type="submit">Save Changes</x-button>
        </div>
    </x-card>
</form>
@endsection
