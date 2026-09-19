<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Tambah Gudang Baru</h1>
        <nav class="flex text-xs text-slate-500 mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('warehouses.index') }}" class="hover:text-slate-900">Gudang</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 font-medium">Tambah</span>
        </nav>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <form method="POST" action="{{ route('warehouses.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Gudang <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Contoh: Gudang Utama Farmasi, Gudang Depo 1" required
                           class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-semibold text-slate-700 mb-1">Tipe Gudang <span class="text-red-500">*</span></label>
                    <select name="type" id="type" required
                            class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="main" @selected(old('type') == 'main')>Gudang Utama (Pusat Distribusi)</option>
                        <option value="branch" @selected(old('type') == 'branch')>Gudang Cabang / Depo Satelit</option>
                    </select>
                    @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="address" class="block text-sm font-semibold text-slate-700 mb-1">Alamat Gudang</label>
                    <textarea name="address" id="address" rows="3" placeholder="Jl. Farmasi Sehat No..."
                              class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">{{ old('address') }}</textarea>
                    @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <label for="is_active" class="text-sm font-medium text-slate-700">Gudang Aktif Beroperasi</label>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                        <i class="fa-solid fa-check mr-1"></i> Simpan Gudang
                    </button>
                    <a href="{{ route('warehouses.index') }}" class="px-6 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium text-slate-700 rounded-lg transition-colors">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
