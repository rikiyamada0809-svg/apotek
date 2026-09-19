<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Edit Lokasi Penyimpanan</h1>
        <nav class="flex text-xs text-slate-500 mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('warehouses.index') }}" class="hover:text-slate-900">Gudang</a>
            <span class="mx-2">/</span>
            <a href="{{ route('warehouses.show', $warehouse) }}" class="hover:text-slate-900">{{ $warehouse->name }}</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 font-medium">Edit Lokasi</span>
        </nav>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <form method="POST" action="{{ route('storage-locations.update', [$warehouse, $location]) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="code" class="block text-sm font-semibold text-slate-700 mb-1">Kode Lokasi <span class="text-red-500">*</span></label>
                    <input type="text" name="code" id="code" value="{{ old('code', $location->code) }}" required
                           class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Lokasi <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $location->name) }}" required
                           class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-semibold text-slate-700 mb-1">Tipe Penyimpanan <span class="text-red-500">*</span></label>
                    <select name="type" id="type" required
                            class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="rak" @selected(old('type', $location->type) == 'rak')>Rak Biasa</option>
                        <option value="kulkas" @selected(old('type', $location->type) == 'kulkas')>Kulkas / Cold Storage (2-8°C)</option>
                        <option value="lemari_narkotika" @selected(old('type', $location->type) == 'lemari_narkotika')>Lemari Khusus Narkotika / Psikotropika</option>
                    </select>
                    @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="temperature_range" class="block text-sm font-semibold text-slate-700 mb-1">Rentang Suhu (Opsional)</label>
                    <input type="text" name="temperature_range" id="temperature_range" value="{{ old('temperature_range', $location->temperature_range) }}"
                           class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('temperature_range') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $location->is_active))
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <label for="is_active" class="text-sm text-slate-700">Aktif</label>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                        <i class="fa-solid fa-check mr-1"></i> Perbarui Lokasi
                    </button>
                    <a href="{{ route('warehouses.show', $warehouse) }}" class="px-6 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium text-slate-700 rounded-lg transition-colors">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
