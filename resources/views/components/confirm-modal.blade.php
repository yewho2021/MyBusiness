@props(['id', 'title' => 'Confirm Delete', 'message' => 'Are you sure you want to delete this item?'])
<x-modal :id="$id" :title="$title" size="sm">
    <p style="color:#475569;line-height:1.6;">{{ $message }}</p>
    <x-slot:footer>
        <x-button variant="secondary" onclick="scCloseModal('{{ $id }}')">Cancel</x-button>
        <form id="{{ $id }}_form" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <x-button variant="danger" type="submit" icon="fas fa-trash">Delete</x-button>
        </form>
    </x-slot:footer>
</x-modal>
