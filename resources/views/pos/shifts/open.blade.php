<x-app-layout>
    <div class="max-w-xl mx-auto my-8">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-8 py-6 text-white text-center">
                <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-3 backdrop-blur-sm">
                    <i class="fa-solid fa-cash-register text-3xl text-white"></i>
                </div>
                <h1 class="text-2xl font-bold">Buka Shift Kasir</h1>
                <p class="text-xs text-emerald-100 mt-1">Masukkan modal awal kasir sebelum melayani transaksi POS</p>
            </div>

            @if(session('info'))
                <div class="m-6 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-blue-600 text-sm"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="m-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('shifts.store') }}" class="p-8 space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Gudang / Lokasi Apotek <span class="text-rose-500">*</span></label>
                    <select name="warehouse_id" required class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->address ?? 'Apotek Utama' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Modal Kas Awal (Rp) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-sm font-bold text-slate-400">Rp</span>
                        <input type="number" step="any" min="0" name="opening_cash" value="{{ old('opening_cash', 100000) }}" required
                               class="w-full pl-11 pr-4 py-2.5 text-base font-bold text-slate-900 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Uang kembalian yang ada di laci kasir saat buka shift.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan Shift (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Catatan shift (misal: Shift Pagi, kondisi laci kas, dll)..."
                              class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">{{ old('notes') }}</textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl transition-colors shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-key"></i> Mulai Shift Kasir & Buka POS
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
