@props(['action', 'confirmText' => 'Apakah Anda yakin ingin menghapus data ini?'])

<form method="POST" action="{{ $action }}" class="inline" onsubmit="return confirm('{{ $confirmText }}')">
    @csrf
    @method('DELETE')
    <button type="submit" class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 transition-colors" title="Hapus">
        <i class="fa-solid fa-trash text-sm"></i>
    </button>
</form>
