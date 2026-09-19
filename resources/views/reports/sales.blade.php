<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Laporan Penjualan</h1>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('reports.index') }}" class="hover:text-slate-900">Laporan</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">Penjualan</span>
                </nav>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.sales.pdf', request()->all()) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 shadow-sm transition">
                    <i class="fa-solid fa-file-pdf text-red-600"></i> Cetak PDF
                </a>
                <a href="{{ route('reports.sales.csv', request()->all()) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl hover:bg-emerald-700 shadow-sm transition">
                    <i class="fa-solid fa-file-excel"></i> Unduh CSV / Excel
                </a>
            </div>
        </div>

        <!-- Type Selector Tabs -->
        <div class="border-b border-slate-200">
            <nav class="flex space-x-2 overflow-x-auto pb-2 text-xs font-bold">
                <a href="{{ route('reports.sales', array_merge(request()->except('page'), ['type' => 'daily'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'daily' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-calendar-day mr-1.5"></i> Penjualan Harian
                </a>
                <a href="{{ route('reports.sales', array_merge(request()->except('page'), ['type' => 'product'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'product' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-pills mr-1.5"></i> Per Produk
                </a>
                <a href="{{ route('reports.sales', array_merge(request()->except('page'), ['type' => 'gross_profit'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'gross_profit' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-chart-line mr-1.5 text-emerald-400"></i> Laba Kotor (Gross Profit)
                </a>
                <a href="{{ route('reports.sales', array_merge(request()->except('page'), ['type' => 'customer'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'customer' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-users mr-1.5"></i> Per Pelanggan
                </a>
                <a href="{{ route('reports.sales', array_merge(request()->except('page'), ['type' => 'prescription'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'prescription' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-file-prescription mr-1.5"></i> Resep Dokter
                </a>
                <a href="{{ route('reports.sales', array_merge(request()->except('page'), ['type' => 'payment_method'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'payment_method' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-credit-card mr-1.5"></i> Metode Bayar
                </a>
                <a href="{{ route('reports.sales', array_merge(request()->except('page'), ['type' => 'returns'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'returns' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-arrow-rotate-left mr-1.5"></i> Retur Penjualan
                </a>
            </nav>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <form method="GET" action="{{ route('reports.sales') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <input type="hidden" name="type" value="{{ $type }}">

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-1 focus:ring-red-500">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-1 focus:ring-red-500">
                </div>

                @if($type === 'daily')
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Kasir</label>
                        <select name="cashier_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-1 focus:ring-red-500">
                            <option value="">Semua Kasir</option>
                            @foreach($cashiers as $c)
                                <option value="{{ $c->id }}" @selected($cashierId == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if(in_array($type, ['daily', 'product', 'gross_profit']))
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Kategori Obat</label>
                        <select name="category_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-1 focus:ring-red-500">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected($categoryId == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition">
                        <i class="fa-solid fa-filter mr-1"></i> Filter Laporan
                    </button>
                    <a href="{{ route('reports.sales', ['type' => $type]) }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition" title="Reset">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Summary Metric Cards -->
        @if($type === 'gross_profit')
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Total Nilai Penjualan</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Total HPP / Harga Beli</div>
                    <div class="text-xl font-bold text-slate-600 mt-1">Rp {{ number_format($totalHpp, 0, ',', '.') }}</div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Total Laba Kotor</div>
                    <div class="text-xl font-bold text-emerald-600 mt-1">Rp {{ number_format($totalGrossProfit, 0, ',', '.') }}</div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Persentase Margin</div>
                    <div class="text-xl font-bold text-blue-600 mt-1">{{ number_format($grossMarginPercentage, 1) }}%</div>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Total Nominal</div>
                    <div class="text-2xl font-bold text-slate-900 mt-1">
                        Rp {{ number_format($totalRevenue ?? ($totalAmount ?? ($totalRefund ?? 0)), 0, ',', '.') }}
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Jumlah Baris / Data</div>
                    <div class="text-2xl font-bold text-slate-700 mt-1">
                        {{ number_format(count($items)) }} Data
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Periode</div>
                    <div class="text-sm font-bold text-slate-800 mt-2">
                        {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} &ndash; {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Data Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    @if($type === 'product')
                        <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Nama Produk</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-right">Kuantitas Terjual</th>
                                <th class="py-3 px-4 text-right">Total Nilai Penjualan (Rp)</th>
                                <th class="py-3 px-4 text-right">Rata-rata Harga Jual (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($items as $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 font-semibold text-slate-900">{{ $item->product?->name ?? 'Produk' }}</td>
                                    <td class="py-3 px-4 text-slate-500">{{ $item->product?->category?->name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-slate-800">{{ number_format($item->total_qty) }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-slate-900">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right text-slate-600">Rp {{ number_format($item->total_qty > 0 ? $item->total_revenue / $item->total_qty : 0, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-slate-400">Tidak ada data penjualan produk.</td></tr>
                            @endforelse
                        </tbody>
                    @elseif($type === 'gross_profit')
                        <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. Faktur & Waktu</th>
                                <th class="py-3 px-4">Produk</th>
                                <th class="py-3 px-4 text-center">Qty</th>
                                <th class="py-3 px-4 text-right">Harga Jual</th>
                                <th class="py-3 px-4 text-right">HPP / Beli</th>
                                <th class="py-3 px-4 text-right">Total Laba Kotor</th>
                                <th class="py-3 px-4 text-right">Margin (%)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($items as $item)
                                @php
                                    $salePrice = (float) $item->unit_price;
                                    $hpp = (float) ($item->batch?->purchase_price ?? $item->product?->purchase_price ?? 0);
                                    $profit = ($salePrice - $hpp) * (float) $item->quantity;
                                    $margin = $salePrice > 0 ? round((($salePrice - $hpp) / $salePrice) * 100, 1) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 font-mono text-slate-800">
                                        <div class="font-bold">{{ $item->sale?->invoice_number }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $item->sale?->sale_date?->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="py-3 px-4 font-medium text-slate-900">{{ $item->product?->name }}</td>
                                    <td class="py-3 px-4 text-center font-semibold">{{ $item->quantity }}</td>
                                    <td class="py-3 px-4 text-right">Rp {{ number_format($salePrice, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right text-slate-500">Rp {{ number_format($hpp, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-emerald-600">Rp {{ number_format($profit, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-semibold {{ $margin >= 20 ? 'text-emerald-600' : ($margin > 0 ? 'text-amber-600' : 'text-red-600') }}">{{ $margin }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-8 text-center text-slate-400">Tidak ada data laba kotor.</td></tr>
                            @endforelse
                        </tbody>
                    @elseif($type === 'payment_method')
                        <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Metode Pembayaran</th>
                                <th class="py-3 px-4 text-center">Frekuensi Transaksi</th>
                                <th class="py-3 px-4 text-right">Total Diterima (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($items as $pm)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 font-bold text-slate-900 uppercase">{{ $pm->payment_method }}</td>
                                    <td class="py-3 px-4 text-center font-semibold text-slate-700">{{ number_format($pm->count) }} x</td>
                                    <td class="py-3 px-4 text-right font-bold text-slate-900">Rp {{ number_format($pm->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-8 text-center text-slate-400">Tidak ada data pembayaran.</td></tr>
                            @endforelse
                        </tbody>
                    @else
                        <!-- Default Daily / Invoices -->
                        <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. Faktur</th>
                                <th class="py-3 px-4">Tanggal & Waktu</th>
                                <th class="py-3 px-4">Pelanggan</th>
                                <th class="py-3 px-4">Kasir</th>
                                <th class="py-3 px-4">Metode Bayar</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Total Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($items as $sale)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $sale->invoice_number }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $sale->sale_date?->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 px-4 font-medium text-slate-800">{{ $sale->customer?->name ?? 'Umum' }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $sale->cashier?->name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $sale->payments->pluck('payment_method')->join(', ') ?: 'Cash' }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">{{ $sale->status }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-slate-900">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-8 text-center text-slate-400">Tidak ada data transaksi penjualan pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    @endif
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
