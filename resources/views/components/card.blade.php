@props(['title' => null, 'padding' => true, 'class' => ''])
<div class="sc-card {{ $class }}">
    @if($title || isset($actions))
    <div class="sc-card-head">
        @if($title)<h3 class="sc-card-title">{{ $title }}</h3>@endif
        @if(isset($actions))<div class="sc-card-actions">{{ $actions }}</div>@endif
    </div>
    @endif
    <div class="sc-card-body {{ $padding ? '' : 'sc-no-pad' }}">
        {{ $slot }}
    </div>
    @if(isset($footer))
    <div class="sc-card-footer">{{ $footer }}</div>
    @endif
</div>
