@props(['id', 'title', 'size' => 'md'])
<div class="sc-modal-overlay" id="{{ $id }}">
    <div class="sc-modal sc-modal--{{ $size }}">
        <div class="sc-modal-header">
            <h3>{{ $title }}</h3>
            <button type="button" class="sc-modal-close" onclick="scCloseModal('{{ $id }}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="sc-modal-body">{{ $slot }}</div>
        @if(isset($footer))
        <div class="sc-modal-footer">{{ $footer }}</div>
        @endif
    </div>
</div>
