@props(['type' => 'info', 'dismissible' => false])
<div class="sc-alert sc-alert--{{ $type }}">
    <div class="sc-alert-content">{{ $slot }}</div>
    @if($dismissible)
    <button type="button" class="sc-alert-close" onclick="this.closest('.sc-alert').remove()">
        <i class="fas fa-times"></i>
    </button>
    @endif
</div>
