<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Edit Kategori</h1>
        <nav class="flex text-xs text-slate-500 mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('categories.index') }}" class="hover:text-slate-900">Kategori</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 font-medium">Edit: {{ $category->name }}</span>
        </nav>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required
                           class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="parent_id" class="block text-sm font-semibold text-slate-700 mb-1">Kategori Induk (Opsional)</label>
                    <select name="parent_id" id="parent_id"
                            class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">— Tidak Ada (Jadikan Kategori Utama) —</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                    @error('parent_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">{{ old('description', $category->description) }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $category->is_active))
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <label for="is_active" class="text-sm font-medium text-slate-700">Kategori Aktif</label>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                        <i class="fa-solid fa-check mr-1"></i> Perbarui Kategori
                    </button>
                    <a href="{{ route('categories.index') }}" class="px-6 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium text-slate-700 rounded-lg transition-colors">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
