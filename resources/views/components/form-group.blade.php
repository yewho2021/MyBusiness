@props(['label', 'name' => null, 'required' => false, 'help' => null])
<div class="sc-form-group">
    <label class="sc-label" @if($name) for="{{ $name }}" @endif>
        {{ $label }}@if($required)<span class="sc-required">*</span>@endif
    </label>
    {{ $slot }}
    @if($help)<p class="sc-form-help">{{ $help }}</p>@endif
    @if($name) @error($name)<p class="sc-form-error">{{ $message }}</p>@enderror @endif
</div>
