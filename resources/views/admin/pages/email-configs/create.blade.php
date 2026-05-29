@extends('admin.layouts.app')
@section('title', 'Add SMTP Config')

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">
        <a href="{{ route('admin.email-configs.index') }}" class="sc-back-link"><i class="fas fa-arrow-left"></i></a>
        Add SMTP Configuration
    </h2>
</div>

@if($errors->any())
    <x-alert type="danger" :dismissible="true">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</x-alert>
@endif

<form action="{{ route('admin.email-configs.store') }}" method="POST">
    @csrf
    <x-card title="SMTP Details">
        <x-form-group label="Scope" name="company_id" help="Leave empty for system-wide default">
            <select name="company_id" class="sc-select">
                <option value="">System Default (Global)</option>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}" {{ old('company_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                @endforeach
            </select>
        </x-form-group>
        <x-form-group label="Config Name" name="name" :required="true">
            <x-input name="name" :value="old('name')" placeholder="e.g. Main SMTP" required />
        </x-form-group>
        <div class="sc-grid sc-grid--3">
            <x-form-group label="SMTP Host" name="host" :required="true">
                <x-input name="host" :value="old('host')" placeholder="e.g. smtp.gmail.com" required />
            </x-form-group>
            <x-form-group label="Port" name="port" :required="true">
                <x-input type="number" name="port" :value="old('port', 587)" required min="1" max="65535" />
            </x-form-group>
            <x-form-group label="Encryption" name="encryption" :required="true">
                <select name="encryption" class="sc-select" required>
                    <option value="tls" {{ old('encryption', 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ old('encryption') === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="none" {{ old('encryption') === 'none' ? 'selected' : '' }}>None</option>
                </select>
            </x-form-group>
        </div>
        <div class="sc-grid sc-grid--2">
            <x-form-group label="Username" name="username" :required="true">
                <x-input name="username" :value="old('username')" required />
            </x-form-group>
            <x-form-group label="Password" name="password" :required="true">
                <x-input type="password" name="password" required />
            </x-form-group>
        </div>
        <div class="sc-grid sc-grid--3">
            <x-form-group label="From Name" name="from_name" :required="true">
                <x-input name="from_name" :value="old('from_name')" placeholder="e.g. MyBusiness" required />
            </x-form-group>
            <x-form-group label="From Email" name="from_email" :required="true">
                <x-input type="email" name="from_email" :value="old('from_email')" placeholder="e.g. noreply@mybusiness.com.my" required />
            </x-form-group>
            <x-form-group label="Reply-To" name="reply_to">
                <x-input type="email" name="reply_to" :value="old('reply_to')" placeholder="Optional" />
            </x-form-group>
        </div>
        <div class="sc-card-footer">
            <a href="{{ route('admin.email-configs.index') }}" class="sc-btn sc-btn--secondary">Cancel</a>
            <x-button type="submit">Create Config</x-button>
        </div>
    </x-card>
</form>
@endsection
