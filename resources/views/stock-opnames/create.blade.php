<x-app-layout>
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Buat Stok Opname Baru</h1>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('stock-opnames.index') }}" class="hover:text-slate-900">Stok Opname</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">Buat Baru</span>
                </nav>
            </div>
            <a href="{{ route('stock-opnames.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                <div class="font-bold mb-1"><i class="fa-solid fa-circle-exclamation mr-1"></i> Terjadi kesalahan input:</div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('stock-opnames.store') }}" x-data="{ scope: '{{ old('scope', 'full') }}' }">
            @csrf

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
                <!-- Info Header -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">No. Opname (Otomatis)</label>
                        <input type="text" value="{{ $opnameNumber }}" readonly 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-600 focus:outline-none cursor-not-allowed">
                        <p class="text-xs text-slate-400 mt-1">Nomor opname digenerate otomatis dengan format SO-YYYYMMDD-XXXX.</p>
                    </div>

                    <div>
                        <label for="opname_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Tanggal Opname <span class="text-red-500">*</span></label>
                        <input type="date" id="opname_date" name="opname_date" value="{{ old('opname_date', now()->toDateString()) }}" required
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <!-- Gudang Selection -->
                <div>
                    <label for="warehouse_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Pilih Gudang Target <span class="text-red-500">*</span></label>
                    <select id="warehouse_id" name="warehouse_id" required
                            class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" @selected(old('warehouse_id') == $wh->id)>{{ $wh->name }} ({{ $wh->code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Scope Selection -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Cakupan Opname (Scope) <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex items-start gap-3 p-3.5 border rounded-xl cursor-pointer transition-all"
                               :class="scope === 'full' ? 'border-emerald-500 bg-emerald-50/50 ring-1 ring-emerald-500' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="scope" value="full" x-model="scope" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="block text-sm font-semibold text-slate-900">Semua Obat (Full)</span>
                                <span class="text-xs text-slate-500">Semua stok obat aktif di gudang ini.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3.5 border rounded-xl cursor-pointer transition-all"
                               :class="scope === 'category' ? 'border-emerald-500 bg-emerald-50/50 ring-1 ring-emerald-500' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="scope" value="category" x-model="scope" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="block text-sm font-semibold text-slate-900">Kategori Tertentu</span>
                                <span class="text-xs text-slate-500">Pilih satu atau beberapa kategori.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3.5 border rounded-xl cursor-pointer transition-all"
                               :class="scope === 'location' ? 'border-emerald-500 bg-emerald-50/50 ring-1 ring-emerald-500' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="scope" value="location" x-model="scope" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="block text-sm font-semibold text-slate-900">Lokasi / Rak</span>
                                <span class="text-xs text-slate-500">Pilih rak / lokasi penyimpanan.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Category Selector -->
                <div x-show="scope === 'category'" x-transition class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Pilih Kategori Obat</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-48 overflow-y-auto pr-1">
                        @foreach($categories as $category)
                            <label class="flex items-center gap-2 p-2 bg-white border border-slate-200 rounded-lg text-xs cursor-pointer hover:border-slate-300">
                                <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                                       @checked(in_array($category->id, old('category_ids', [])))
                                       class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span class="text-slate-800 font-medium truncate">{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Location Selector -->
                <div x-show="scope === 'location'" x-transition class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Pilih Lokasi Rak / Penyimpanan</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-48 overflow-y-auto pr-1">
                        @foreach($locations as $loc)
                            <label class="flex items-center gap-2 p-2 bg-white border border-slate-200 rounded-lg text-xs cursor-pointer hover:border-slate-300">
                                <input type="checkbox" name="location_ids[]" value="{{ $loc->id }}"
                                       @checked(in_array($loc->id, old('location_ids', [])))
                                       class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span class="text-slate-800 font-medium truncate">{{ $loc->name }} ({{ $loc->warehouse?->name ?? 'Gudang' }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Freeze Stock Toggle -->
                <div class="p-4 bg-cyan-50/50 border border-cyan-200 rounded-xl flex items-start gap-3">
                    <input type="checkbox" id="freeze_stock" name="freeze_stock" value="1" @checked(old('freeze_stock'))
                           class="mt-1 rounded text-cyan-600 focus:ring-cyan-500">
                    <div>
                        <label for="freeze_stock" class="text-sm font-bold text-slate-900 cursor-pointer flex items-center gap-1.5">
                            <i class="fa-solid fa-snowflake text-cyan-600"></i> Bekukan Transaksi Stok (Freeze Stock)
                        </label>
                        <p class="text-xs text-slate-600 mt-1">
                            Jika diaktifkan, penjualan kasir (POS) dan mutasi stok keluar dari gudang ini akan diblokir sementara sampai pelaksanaan opname selesai untuk mencegah perbedaan data.
                        </p>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Catatan / Keterangan</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Contoh: Stok opname akhir bulan September..."
                              class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">{{ old('notes') }}</textarea>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('stock-opnames.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> Buat Draft Opname
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
