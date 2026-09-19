<x-app-layout>
    <div class="space-y-6">
        @php
            $isExpired = $batch->isExpired();
            $isNear = $batch->isExpiringSoon(30);
            $daysRemaining = now()->startOfDay()->diffInDays($batch->expiry_date->startOfDay(), false);
        @endphp

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-slate-900">Batch {{ $batch->batch_number }}</h1>
                    @if($isExpired)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                            <i class="fa-solid fa-skull-crossbones mr-1"></i> Kadaluarsa
                        </span>
                    @elseif($isNear)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                            <i class="fa-solid fa-hourglass-half mr-1"></i> Dekat Kadaluarsa
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                            <i class="fa-solid fa-circle-check mr-1"></i> Aktif & Aman
                        </span>
                    @endif
                </div>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('stocks.index') }}" class="hover:text-slate-900">Inventori & Stok</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('batches.index') }}" class="hover:text-slate-900">Monitoring Batch</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">{{ $batch->batch_number }}</span>
                </nav>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('batches.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Batch
                </a>
                <a href="{{ route('stocks.card', ['product_id' => $batch->product_id, 'batch_id' => $batch->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-clipboard-list"></i> Kartu Stok Batch Ini
                </a>
            </div>
        </div>

        <!-- Expiry Warning Alert Banner -->
        @if($isExpired)
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-red-600 text-lg mt-0.5"></i>
                <div class="text-xs text-red-800">
                    <strong class="font-bold">PERINGATAN: Batch ini telah kadaluarsa sejak {{ $batch->expiry_date->format('d/m/Y') }} ({{ abs($daysRemaining) }} hari yang lalu).</strong>
                    <p class="mt-0.5 text-red-700">Obat dalam batch ini telah otomatis diblokir dari penjualan kasir POS untuk keselamatan pasien. Segera lakukan proses retur atau pemusnahan obat.</p>
                </div>
            </div>
        @elseif($isNear)
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-xl flex items-start gap-3">
                <i class="fa-solid fa-clock-rotate-left text-amber-600 text-lg mt-0.5"></i>
                <div class="text-xs text-amber-800">
                    <strong class="font-bold">PERHATIAN: Batch ini akan kadaluarsa dalam {{ $daysRemaining }} hari ({{ $batch->expiry_date->format('d/m/Y') }}).</strong>
                    <p class="mt-0.5 text-amber-700">Pastikan prioritas pengeluaran obat mengikuti kaidah FEFO (First-Expired, First-Out) sebelum tanggal kadaluarsa terlewati.</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom Kiri: Info Batch & Produk -->
            <div class="space-y-6">
                <!-- Info Batch -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-2">
                        <i class="fa-solid fa-barcode text-[#f83f37]"></i> Detail Identitas Batch
                    </h3>
                    
                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nomor Batch:</span>
                            <span class="font-mono font-bold text-slate-900">{{ $batch->batch_number }}</span>
                        </div>
                        @if($batch->barcode)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Barcode Batch:</span>
                                <span class="font-mono text-slate-700">{{ $batch->barcode }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-slate-500">Supplier:</span>
                            <span class="font-semibold text-slate-800">{{ $batch->supplier->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Tgl Kadaluarsa:</span>
                            <span class="font-mono font-bold {{ $isExpired ? 'text-red-600' : ($isNear ? 'text-amber-600' : 'text-slate-900') }}">
                                {{ $batch->expiry_date->format('d M Y') }}
                            </span>
                        </div>
                        @if($batch->manufacturing_date)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Tgl Produksi:</span>
                                <span class="font-mono text-slate-700">{{ $batch->manufacturing_date->format('d M Y') }}</span>
                            </div>
                        @endif
                        @if($batch->received_date)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Tgl Diterima:</span>
                                <span class="font-mono text-slate-700">{{ $batch->received_date->format('d M Y') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-slate-500">Harga Beli per Satuan:</span>
                            <span class="font-semibold text-slate-900">Rp {{ number_format($batch->purchase_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Qty Diterima Awal:</span>
                            <span class="font-semibold text-slate-800">{{ number_format($batch->received_qty, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-100 pt-2">
                            <span class="text-slate-700 font-bold">Sisa Stok Saat Ini:</span>
                            <span class="font-bold text-base {{ $batch->current_qty <= 0 ? 'text-slate-400' : 'text-emerald-600' }}">
                                {{ number_format($batch->current_qty, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Info Produk -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2 flex items-center gap-2">
                        <i class="fa-solid fa-pills text-blue-600"></i> Informasi Produk
                    </h3>

                    <div class="space-y-2 text-xs">
                        <div>
                            <p class="font-bold text-slate-900 text-sm">{{ $batch->product->name ?? '-' }}</p>
                            <p class="text-slate-500 text-[11px] font-mono">{{ $batch->product->product_code ?? '' }}</p>
                        </div>
                        @if($batch->product?->generic_name)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Nama Generik:</span>
                                <span class="text-slate-700 italic">{{ $batch->product->generic_name }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-slate-500">Kategori:</span>
                            <span class="text-slate-800 font-medium">{{ $batch->product->category->name ?? '-' }}</span>
                        </div>
                        @if($batch->product?->drug_class)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Golongan:</span>
                                <span class="text-slate-800 font-medium">{{ $batch->product->drug_class }}</span>
                            </div>
                        @endif
                        @if($batch->product?->manufacturer)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Pabrik / Produsen:</span>
                                <span class="text-slate-800">{{ $batch->product->manufacturer->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Sebaran Stok & Riwayat Mutasi -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Sebaran Lokasi Penyimpanan di Gudang -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-4 bg-slate-50/75 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-warehouse text-emerald-600"></i> Sebaran Stok di Gudang & Rak
                        </h3>
                        <span class="text-[11px] font-semibold text-slate-500">{{ $batch->stocks->count() }} Lokasi Penempatan</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-2.5">Gudang</th>
                                    <th class="px-4 py-2.5">Lokasi / Rak</th>
                                    <th class="px-4 py-2.5 text-right">Stok Fisik</th>
                                    <th class="px-4 py-2.5 text-right">Reserved</th>
                                    <th class="px-4 py-2.5 text-right font-bold text-slate-900">Tersedia</th>
                                    <th class="px-4 py-2.5 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($batch->stocks as $stk)
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="px-4 py-2.5 font-semibold text-slate-800">{{ $stk->warehouse->name ?? '-' }}</td>
                                        <td class="px-4 py-2.5 text-slate-600">
                                            <span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-[11px] mr-1">{{ $stk->storageLocation->code ?? '-' }}</span>
                                            {{ $stk->storageLocation->name ?? 'Penyimpanan Umum' }}
                                        </td>
                                        <td class="px-4 py-2.5 text-right text-slate-800">{{ number_format($stk->quantity, 0) }}</td>
                                        <td class="px-4 py-2.5 text-right text-slate-500">{{ number_format($stk->reserved_qty, 0) }}</td>
                                        <td class="px-4 py-2.5 text-right font-bold text-emerald-600">{{ number_format($stk->available_qty, 0) }}</td>
                                        <td class="px-4 py-2.5 text-center">
                                            @if($stk->status === 'available')
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Tersedia</span>
                                            @else
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Habis</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-slate-400">
                                            Batch ini belum dialokasikan ke gudang / rak fisik.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Riwayat Mutasi Batch -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-4 bg-slate-50/75 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-blue-600"></i> Riwayat Mutasi Batch Ini
                        </h3>
                        <span class="text-[11px] font-semibold text-slate-500">{{ $batch->stockMutations->count() }} Perubahan Stok</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-2.5">Tanggal</th>
                                    <th class="px-4 py-2.5">Jenis Mutasi</th>
                                    <th class="px-4 py-2.5">Gudang</th>
                                    <th class="px-4 py-2.5 text-right">Qty</th>
                                    <th class="px-4 py-2.5 text-right">Saldo Sesudah</th>
                                    <th class="px-4 py-2.5">User</th>
                                    <th class="px-4 py-2.5">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($batch->stockMutations as $mut)
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="px-4 py-2.5 font-mono text-slate-600">{{ $mut->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-2.5">
                                            @if($mut->mutation_type === 'in')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                                    Masuk
                                                </span>
                                            @elseif($mut->mutation_type === 'out')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">
                                                    Keluar
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                                    {{ ucfirst($mut->mutation_type) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-slate-700">{{ $mut->warehouse->name ?? '-' }}</td>
                                        <td class="px-4 py-2.5 text-right font-bold {{ $mut->mutation_type === 'in' ? 'text-emerald-600' : 'text-red-600' }}">
                                            {{ $mut->mutation_type === 'in' ? '+' : '-' }}{{ number_format($mut->quantity, 0) }}
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-black text-slate-800">
                                            {{ number_format($mut->stock_after, 0) }}
                                        </td>
                                        <td class="px-4 py-2.5 text-slate-600">{{ $mut->creator->name ?? 'Sistem' }}</td>
                                        <td class="px-4 py-2.5 text-slate-500 max-w-xs truncate" title="{{ $mut->notes }}">{{ $mut->notes ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-slate-400">
                                            Belum ada catatan mutasi untuk batch ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
