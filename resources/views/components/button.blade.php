@props(['variant' => 'primary', 'size' => 'md', 'icon' => null, 'type' => 'button'])
<button type="{{ $type }}" {{ $attributes->merge(['class' => "sc-btn sc-btn--{$variant}" . ($size !== 'md' ? " sc-btn--{$size}" : '')]) }}>
    @if($icon)<i class="{{ $icon }}"></i> @endif{{ $slot }}
</button>
