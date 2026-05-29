@props(['value', 'label', 'icon' => null, 'color' => 'primary'])
<div class="sc-stat-card sc-stat--{{ $color }}">
    @if($icon)<div class="sc-stat-icon"><i class="{{ $icon }}"></i></div>@endif
    <div class="sc-stat-value">{{ $value }}</div>
    <div class="sc-stat-label">{{ $label }}</div>
</div>
