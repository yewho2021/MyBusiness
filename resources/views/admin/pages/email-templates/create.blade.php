@extends('admin.layouts.app')
@section('title', 'Add Email Template')

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">
        <a href="{{ route('admin.email-templates.index') }}" class="sc-back-link"><i class="fas fa-arrow-left"></i></a>
        Add Email Template
    </h2>
</div>

@if($errors->any())
    <x-alert type="danger" :dismissible="true">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</x-alert>
@endif

<form action="{{ route('admin.email-templates.store') }}" method="POST">
    @csrf
    <x-card title="Template Details">
        <div class="sc-grid sc-grid--3">
            <x-form-group label="Scope" name="company_id" help="NULL = system default">
                <select name="company_id" class="sc-select">
                    <option value="">System Default (Global)</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ old('company_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                    @endforeach
                </select>
            </x-form-group>
            <x-form-group label="SMTP Config" name="smtp_id">
                <select name="smtp_id" class="sc-select">
                    <option value="">Default</option>
                    @foreach($smtpConfigs as $smtp)
                        <option value="{{ $smtp->id }}" {{ old('smtp_id') == $smtp->id ? 'selected' : '' }}>{{ $smtp->name }}</option>
                    @endforeach
                </select>
            </x-form-group>
            <x-form-group label="Slug" name="slug" :required="true" help="e.g. company.verify_email">
                <x-input name="slug" :value="old('slug')" placeholder="e.g. company.registration.verify_email" required />
            </x-form-group>
        </div>
        <div class="sc-grid sc-grid--2">
            <x-form-group label="Template Name" name="name" :required="true">
                <x-input name="name" :value="old('name')" placeholder="e.g. Company Registration - Verify Email" required />
            </x-form-group>
            <x-form-group label="Subject" name="subject" :required="true">
                <x-input name="subject" :value="old('subject')" placeholder="e.g. Please verify your email address" required />
            </x-form-group>
        </div>
        <x-form-group label="Content (HTML/Blade)" name="content" :required="true">
            <textarea name="content" class="sc-textarea" rows="15" required placeholder="HTML email content with {{ '{{' }} $variable {{ '}}' }} placeholders">{{ old('content') }}</textarea>
        </x-form-group>
        <div class="sc-grid sc-grid--3">
            <x-form-group label="Override To" name="email_to" help="Leave blank to use recipient">
                <x-input name="email_to" :value="old('email_to')" />
            </x-form-group>
            <x-form-group label="CC" name="email_cc">
                <x-input name="email_cc" :value="old('email_cc')" />
            </x-form-group>
            <x-form-group label="BCC" name="email_bcc">
                <x-input name="email_bcc" :value="old('email_bcc')" />
            </x-form-group>
        </div>
        <div class="sc-card-footer">
            <a href="{{ route('admin.email-templates.index') }}" class="sc-btn sc-btn--secondary">Cancel</a>
            <x-button type="submit">Create Template</x-button>
        </div>
    </x-card>
</form>
@endsection
