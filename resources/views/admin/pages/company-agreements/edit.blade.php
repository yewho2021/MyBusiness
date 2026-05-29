@extends('admin.layouts.app')
@section('title', 'Edit Agreement')

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">
        <a href="{{ route('admin.company-agreements.index') }}" class="sc-back-link"><i class="fas fa-arrow-left"></i></a>
        Edit Agreement v{{ $agreement->version }}
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

<form action="{{ route('admin.company-agreements.update', $agreement->id) }}" method="POST">
    @csrf
    @method('PUT')
    <x-card title="Agreement Details">
        <div class="sc-grid sc-grid--2">
            <x-form-group label="Version" name="version" :required="true">
                <x-input name="version" :value="old('version', $agreement->version)" required />
            </x-form-group>
            <x-form-group label="Title" name="title" :required="true">
                <x-input name="title" :value="old('title', $agreement->title)" required />
            </x-form-group>
        </div>
        <x-form-group label="Content" name="content" :required="true">
            <textarea name="content" class="sc-textarea" rows="20" required>{{ old('content', $agreement->content) }}</textarea>
        </x-form-group>
        <div class="sc-form-group">
            <label class="sc-checkbox-label">
                <input type="checkbox" name="is_active" {{ old('is_active', $agreement->is_active) ? 'checked' : '' }}>
                <span>Active version {{ $agreement->is_active ? '(currently active)' : '(will deactivate current active version)' }}</span>
            </label>
        </div>
        <div class="sc-card-footer">
            <a href="{{ route('admin.company-agreements.index') }}" class="sc-btn sc-btn--secondary">Cancel</a>
            <x-button type="submit">Save Changes</x-button>
        </div>
    </x-card>
</form>
@endsection
