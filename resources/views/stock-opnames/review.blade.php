<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">Review Selisih Stok Opname</h1>
                    <span class="px-2.5 py-0.5 text-xs font-mono bg-purple-100 text-purple-800 rounded-md font-bold">
                        {{ $stockOpname->opname_number }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Silakan tinjau dan masukkan alasan perbedaan antara jumlah fisik dan sistem untuk setiap item sebelum diajukan ke manajer.
                </p>
            </div>

            <a href="{{ route('stock-opnames.show', $stockOpname) }}" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-xl transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Detail
            </a>
        </div>

        @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
        @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                <div class="font-bold mb-1"><i class="fa-solid fa-circle-exclamation mr-1"></i> Terjadi kesalahan:</div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('stock-opnames.review.update', $stockOpname) }}">
            @csrf

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 bg-purple-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-purple-600"></i>
                        <span class="text-sm font-bold text-slate-900">Daftar Item Berselisih ({{ $stockOpname->items->count() }} item)</span>
                    </div>
                    <span class="text-xs text-slate-500">Seluruh alasan wajib diisi sebelum disetujui manajer</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($stockOpname->items as $index => $item)
                        @php
                            $diff = (float) $item->discrepancy;
                            $isDeficit = $diff < 0;
                        @endphp
                        <div class="p-5 hover:bg-slate-50/50 transition-colors" x-data="{ reason: '{{ addslashes($item->discrepancy_reason ?? '') }}' }">
                            <input type="hidden" name="reasons[{{ $index }}][id]" value="{{ $item->id }}">

                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-3">
                                <!-- Product & Batch Info -->
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400 font-mono">#{{ $index + 1 }}</span>
                                        <h3 class="text-sm font-bold text-slate-900">{{ $item->product?->name }}</h3>
                                        <span class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                                            {{ $item->product?->category?->name ?? 'Umum' }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-3">
                                        <span>Kode: <span class="font-mono text-slate-700">{{ $item->product?->product_code }}</span></span>
                                        @if($item->batch)
                                            <span>Batch: <span class="font-mono text-slate-700">{{ $item->batch->batch_number }}</span></span>
                                            @if($item->batch->expiry_date)
                                                <span>Exp: <span class="text-slate-700">{{ $item->batch->expiry_date->format('d/m/Y') }}</span></span>
                                            @endif
                                        @endif
                                        @if($item->storageLocation)
                                            <span>Lokasi: <span class="text-slate-700">{{ $item->storageLocation->name }}</span></span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Qty & Difference Badges -->
                                <div class="flex items-center gap-4 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                    <div class="text-right">
                                        <span class="text-[10px] text-slate-400 font-bold block uppercase">Sistem</span>
                                        <span class="text-xs font-semibold text-slate-700">{{ number_format((float) $item->system_qty, 2) }}</span>
                                    </div>
                                    <div class="text-slate-300">→</div>
                                    <div class="text-right">
                                        <span class="text-[10px] text-slate-400 font-bold block uppercase">Fisik</span>
                                        <span class="text-xs font-bold text-slate-900">{{ number_format((float) $item->physical_qty, 2) }}</span>
                                    </div>
                                    <div class="text-center pl-2 border-l border-slate-200">
                                        <span class="text-[10px] text-slate-400 font-bold block uppercase">Selisih</span>
                                        @if($diff > 0)
                                            <span class="text-xs font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-md">
                                                +{{ number_format($diff, 2) }}
                                            </span>
                                        @else
                                            <span class="text-xs font-bold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-md">
                                                {{ number_format($diff, 2) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Discrepancy Reason Input & Suggestions -->
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-700">
                                    Alasan Selisih <span class="text-rose-500">*</span>
                                </label>
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <input type="text" name="reasons[{{ $index }}][reason]" x-model="reason" required
                                           placeholder="Tuliskan penyebab selisih..."
                                           class="flex-1 px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    
                                    <!-- Quick Suggestions -->
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <button type="button" @click="reason = 'Barang rusak / kemasan pecah'"
                                                class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] rounded-md transition-colors">
                                            Rusak/Pecah
                                        </button>
                                        <button type="button" @click="reason = 'Selisih hitung kasir / transaksi'"
                                                class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] rounded-md transition-colors">
                                            Selisih Kasir
                                        </button>
                                        <button type="button" @click="reason = 'Barang hilang / tidak ditemukan'"
                                                class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] rounded-md transition-colors">
                                            Hilang
                                        </button>
                                        <button type="button" @click="reason = 'Penerimaan barang belum dicatat sistem'"
                                                class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] rounded-md transition-colors">
                                            Belum Input Terima
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500">
                            <i class="fa-solid fa-circle-check text-emerald-500 text-3xl mb-2"></i>
                            <p class="font-semibold text-slate-800">Semua Item Sesuai!</p>
                            <p class="text-xs text-slate-400 mt-1">Tidak ditemukan selisih antara perhitungan fisik dan data sistem.</p>
                        </div>
                    @endforelse
                </div>

                @if($stockOpname->items->isNotEmpty())
                    <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
                        <a href="{{ route('stock-opnames.show', $stockOpname) }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-check"></i> Simpan Alasan Review & Perbarui Status
                        </button>
                    </div>
                @endif
            </div>
        </form>
    </div>
</x-app-layout>
