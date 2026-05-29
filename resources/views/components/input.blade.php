@props(['type' => 'text', 'name', 'value' => null, 'placeholder' => null])
<input type="{{ $type }}"
       name="{{ $name }}"
       id="{{ $name }}"
       value="{{ old($name, $value) }}"
       @if($placeholder) placeholder="{{ $placeholder }}" @endif
       {{ $attributes->merge(['class' => 'sc-input']) }}>
