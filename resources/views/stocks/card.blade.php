<x-app-layout>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Kartu Stok Digital</h1>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('stocks.index') }}" class="hover:text-slate-900">Inventori & Stok</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">Kartu Stok</span>
                </nav>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('stocks.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Stok
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-[#f83f37] hover:bg-red-600 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-print"></i> Cetak Kartu Stok
                </button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm print:hidden">
            <form method="GET" action="{{ route('stocks.card') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <!-- Pilih Produk -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Produk / Obat <span class="text-red-500">*</span></label>
                    <select name="product_id" required class="w-full text-xs border border-slate-200 rounded-lg p-2 focus:ring-1 focus:ring-red-500">
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" @selected($productId == $prod->id)>
                                {{ $prod->name }} ({{ $prod->product_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Gudang -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Gudang (Opsional)</label>
                    <select name="warehouse_id" class="w-full text-xs border border-slate-200 rounded-lg p-2 focus:ring-1 focus:ring-red-500">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" @selected($warehouseId == $wh->id)>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal Mulai -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-xs border border-slate-200 rounded-lg p-2 focus:ring-1 focus:ring-red-500">
                </div>

                <!-- Tanggal Selesai -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-xs border border-slate-200 rounded-lg p-2 focus:ring-1 focus:ring-red-500">
                </div>

                <!-- Action Button -->
                <div class="lg:col-span-5 flex justify-end gap-2 pt-2">
                    <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-magnifying-glass"></i> Tampilkan Kartu Stok
                    </button>
                    <a href="{{ route('stocks.card', ['product_id' => $productId]) }}" class="px-4 py-2 text-xs font-medium text-slate-500 hover:text-slate-800">
                        Reset Periode
                    </a>
                </div>
            </form>
        </div>

        @if($selectedProduct)
            <!-- Printable Header / Info Box -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between pb-6 border-b border-slate-100 gap-4">
                    <div>
                        <span class="text-[10px] font-bold tracking-widest text-[#f83f37] uppercase bg-red-50 px-2 py-0.5 rounded">Kartu Riwayat Mutasi Stok</span>
                        <h2 class="text-xl font-black text-slate-900 mt-1">{{ $selectedProduct->name }}</h2>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 mt-1">
                            <span>Kode: <strong class="text-slate-800 font-mono">{{ $selectedProduct->product_code }}</strong></span>
                            @if($selectedProduct->barcode)
                                <span>Barcode: <strong class="text-slate-800 font-mono">{{ $selectedProduct->barcode }}</strong></span>
                            @endif
                            @if($selectedProduct->generic_name)
                                <span>Generik: <em class="text-slate-700">{{ $selectedProduct->generic_name }}</em></span>
                            @endif
                            <span>Kategori: <strong class="text-slate-800">{{ $selectedProduct->category->name ?? '-' }}</strong></span>
                        </div>
                    </div>

                    <div class="text-left md:text-right text-xs">
                        <p class="text-slate-400 font-medium">Periode Pencatatan</p>
                        <p class="text-slate-900 font-bold text-sm">
                            {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                        </p>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Gudang: <strong>{{ $warehouseId ? ($warehouses->firstWhere('id', $warehouseId)?->name ?? 'Gudang Terpilih') : 'Semua Gudang' }}</strong>
                        </p>
                    </div>
                </div>

                <!-- Recapitulation KPI Banner -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-4 border-b border-slate-100">
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Saldo Awal</span>
                        <p class="text-lg font-black text-slate-800 mt-0.5">{{ number_format($initialBalance, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 bg-emerald-50 rounded-xl">
                        <span class="text-[10px] uppercase font-bold text-emerald-600 tracking-wider">Total Masuk (+)</span>
                        <p class="text-lg font-black text-emerald-700 mt-0.5">+{{ number_format($totalIn, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 bg-red-50 rounded-xl">
                        <span class="text-[10px] uppercase font-bold text-red-600 tracking-wider">Total Keluar (-)</span>
                        <p class="text-lg font-black text-red-700 mt-0.5">-{{ number_format($totalOut, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-xl">
                        <span class="text-[10px] uppercase font-bold text-blue-600 tracking-wider">Saldo Akhir</span>
                        <p class="text-lg font-black text-blue-800 mt-0.5">{{ number_format($finalBalance, 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Mutation Table -->
                <div class="overflow-x-auto mt-4">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2.5 text-center">#</th>
                                <th class="px-3 py-2.5">Tanggal & Jam</th>
                                <th class="px-3 py-2.5">Tipe Mutasi</th>
                                <th class="px-3 py-2.5">No. Referensi</th>
                                <th class="px-3 py-2.5">Batch & Exp</th>
                                <th class="px-3 py-2.5">Gudang</th>
                                <th class="px-3 py-2.5 text-right text-emerald-700">Masuk (+)</th>
                                <th class="px-3 py-2.5 text-right text-red-700">Keluar (-)</th>
                                <th class="px-3 py-2.5 text-right text-slate-900 font-bold">Saldo Akhir</th>
                                <th class="px-3 py-2.5">User (PIC)</th>
                                <th class="px-3 py-2.5">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <!-- Row Saldo Awal -->
                            <tr class="bg-slate-50/60 font-semibold text-slate-600">
                                <td class="px-3 py-2 text-center text-slate-400">&bull;</td>
                                <td class="px-3 py-2">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y 00:00') }}</td>
                                <td class="px-3 py-2" colspan="4">
                                    <span class="italic text-slate-500">Saldo Awal Sebelum Periode</span>
                                </td>
                                <td class="px-3 py-2 text-right text-slate-400">-</td>
                                <td class="px-3 py-2 text-right text-slate-400">-</td>
                                <td class="px-3 py-2 text-right font-black text-slate-900">{{ number_format($initialBalance, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-slate-400">Sistem</td>
                                <td class="px-3 py-2 text-slate-400">Bawaan saldo lalu</td>
                            </tr>

                            @forelse($mutations as $mut)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-3 py-2.5 text-center text-slate-400">{{ $loop->iteration }}</td>
                                    <td class="px-3 py-2.5 font-mono text-slate-600">
                                        {{ $mut->date->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-3 py-2.5">
                                        @if($mut->mutation_type === 'in')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                                <i class="fa-solid fa-arrow-down mr-1"></i> Masuk
                                            </span>
                                        @elseif($mut->mutation_type === 'out')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">
                                                <i class="fa-solid fa-arrow-up mr-1"></i> Keluar
                                            </span>
                                        @elseif($mut->mutation_type === 'adjustment')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">
                                                <i class="fa-solid fa-pen-to-square mr-1"></i> Penyesuaian
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                                {{ ucfirst($mut->mutation_type) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 font-mono text-slate-700">
                                        @if($mut->reference_type && $mut->reference_id)
                                            <span class="text-[11px] font-medium">{{ ucfirst(str_replace('_', ' ', $mut->reference_type)) }} #{{ $mut->reference_id }}</span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 font-mono text-slate-700">
                                        {{ $mut->batch_number }}
                                    </td>
                                    <td class="px-3 py-2.5 text-slate-600">
                                        {{ $mut->warehouse_name }}
                                    </td>
                                    <td class="px-3 py-2.5 text-right font-bold text-emerald-600">
                                        {{ $mut->qty_in > 0 ? '+'.number_format($mut->qty_in, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-3 py-2.5 text-right font-bold text-red-600">
                                        {{ $mut->qty_out > 0 ? '-'.number_format($mut->qty_out, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-3 py-2.5 text-right font-black text-slate-900">
                                        {{ number_format($mut->balance, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2.5 text-slate-600">
                                        {{ $mut->creator_name }}
                                    </td>
                                    <td class="px-3 py-2.5 text-slate-500 max-w-xs truncate" title="{{ $mut->notes }}">
                                        {{ $mut->notes ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-3 py-8 text-center text-slate-400">
                                        Tidak ada transaksi mutasi dalam rentang periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50 font-bold text-slate-800 border-t border-slate-200">
                            <tr>
                                <td colspan="6" class="px-3 py-3 text-right">TOTAL PERIODE INI:</td>
                                <td class="px-3 py-3 text-right text-emerald-600 font-extrabold">+{{ number_format($totalIn, 0, ',', '.') }}</td>
                                <td class="px-3 py-3 text-right text-red-600 font-extrabold">-{{ number_format($totalOut, 0, ',', '.') }}</td>
                                <td class="px-3 py-3 text-right text-slate-900 font-black text-sm">{{ number_format($finalBalance, 0, ',', '.') }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500 shadow-sm">
                <i class="fa-solid fa-clipboard-question text-4xl text-slate-300 mb-3 block"></i>
                <h3 class="text-sm font-bold text-slate-800">Belum Ada Produk Dipilih</h3>
                <p class="text-xs text-slate-500 mt-1">Silakan pilih produk pada filter di atas untuk melihat kartu riwayat stok.</p>
            </div>
        @endif
    </div>
</x-app-layout>
