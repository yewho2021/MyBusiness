@extends('admin.layouts.app')
@section('title', 'Edit Bank')

@section('content')
<div class="sc-page-header">
    <h2 class="sc-page-title">
        <a href="{{ route('admin.ref-banks.index') }}" class="sc-back-link"><i class="fas fa-arrow-left"></i></a>
        Edit Bank
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

<x-card title="Bank Details">
    <form action="{{ route('admin.ref-banks.update', $bank->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="sc-grid sc-grid--2">
            <x-form-group label="Bank Name" name="name" :required="true">
                <x-input name="name" :value="old('name', $bank->name)" required />
            </x-form-group>
            <x-form-group label="SWIFT Code" name="swift_code">
                <x-input name="swift_code" :value="old('swift_code', $bank->swift_code)" />
            </x-form-group>
        </div>
        <div class="sc-card-footer">
            <a href="{{ route('admin.ref-banks.index') }}" class="sc-btn sc-btn--secondary">Cancel</a>
            <x-button type="submit">Save Changes</x-button>
        </div>
    </form>
</x-card>
@endsection
