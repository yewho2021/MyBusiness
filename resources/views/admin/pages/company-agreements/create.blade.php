@extends('admin.layouts.app')
@section('title', 'New Agreement')

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">
        <a href="{{ route('admin.company-agreements.index') }}" class="sc-back-link"><i class="fas fa-arrow-left"></i></a>
        New Agreement Version
    </h2>
</div>

@if($errors->any())
    <x-alert type="danger" :dismissible="true">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </x-alert>
@endif

<form action="{{ route('admin.company-agreements.store') }}" method="POST">
    @csrf
    <x-card title="Agreement Details">
        <div class="sc-grid sc-grid--2">
            <x-form-group label="Version" name="version" :required="true" help="{{ $latestVersion ? 'Latest: v' . $latestVersion : 'First version' }}">
                <x-input name="version" :value="old('version')" placeholder="e.g. 1.0" required />
            </x-form-group>
            <x-form-group label="Title" name="title" :required="true">
                <x-input name="title" :value="old('title', 'Terms & Conditions')" required />
            </x-form-group>
        </div>
        <x-form-group label="Content" name="content" :required="true">
            <textarea name="content" class="sc-textarea" rows="20" required placeholder="Enter the full Terms & Conditions content. HTML is supported.">{{ old('content') }}</textarea>
        </x-form-group>
        <div class="sc-form-group">
            <label class="sc-checkbox-label">
                <input type="checkbox" name="is_active" {{ old('is_active') ? 'checked' : '' }}>
                <span>Set as active version (deactivates previous version)</span>
            </label>
        </div>
        <div class="sc-card-footer">
            <a href="{{ route('admin.company-agreements.index') }}" class="sc-btn sc-btn--secondary">Cancel</a>
            <x-button type="submit">Create Agreement</x-button>
        </div>
    </x-card>
</form>
@endsection
