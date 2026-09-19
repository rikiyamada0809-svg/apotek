<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Laporan Stok & Gudang</h1>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('reports.index') }}" class="hover:text-slate-900">Laporan</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">Stok & Gudang</span>
                </nav>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.stock.pdf', request()->all()) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 shadow-sm transition">
                    <i class="fa-solid fa-file-pdf text-red-600"></i> Cetak PDF
                </a>
                <a href="{{ route('reports.stock.csv', request()->all()) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl hover:bg-emerald-700 shadow-sm transition">
                    <i class="fa-solid fa-file-excel"></i> Unduh CSV / Excel
                </a>
            </div>
        </div>

        <!-- Type Selector Tabs -->
        <div class="border-b border-slate-200">
            <nav class="flex space-x-2 overflow-x-auto pb-2 text-xs font-bold">
                <a href="{{ route('reports.stock', array_merge(request()->except('page'), ['type' => 'current'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'current' ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-coins mr-1.5"></i> Stok & Valuasi Aset
                </a>
                <a href="{{ route('reports.stock', array_merge(request()->except('page'), ['type' => 'mutation'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'mutation' ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-clipboard-list mr-1.5"></i> Mutasi Stok
                </a>
                <a href="{{ route('reports.stock', array_merge(request()->except('page'), ['type' => 'min_stock'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'min_stock' ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-triangle-exclamation mr-1.5 text-amber-300"></i> Stok Minimum / ROP
                </a>
                <a href="{{ route('reports.stock', array_merge(request()->except('page'), ['type' => 'expired'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'expired' ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-calendar-xmark mr-1.5"></i> Obat Expired
                </a>
                <a href="{{ route('reports.stock', array_merge(request()->except('page'), ['type' => 'dead_stock'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'dead_stock' ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-hourglass-end mr-1.5"></i> Dead Stock
                </a>
                <a href="{{ route('reports.stock', array_merge(request()->except('page'), ['type' => 'opname'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'opname' ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-clipboard-check mr-1.5"></i> Stok Opname
                </a>
                <a href="{{ route('reports.stock', array_merge(request()->except('page'), ['type' => 'destruction'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'destruction' ? 'bg-emerald-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-fire-burner mr-1.5 text-red-300"></i> Pemusnahan Obat
                </a>
            </nav>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <form method="GET" action="{{ route('reports.stock') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <input type="hidden" name="type" value="{{ $type }}">

                @if(!in_array($type, ['current', 'min_stock', 'dead_stock']))
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Tanggal Akhir</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-1 focus:ring-emerald-500">
                    </div>
                @endif

                @if(in_array($type, ['current', 'mutation', 'opname']))
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Pilih Gudang</label>
                        <select name="warehouse_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-1 focus:ring-emerald-500">
                            <option value="">Semua Gudang</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" @selected($warehouseId == $w->id)>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if(in_array($type, ['current', 'min_stock']))
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Kategori Obat</label>
                        <select name="category_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-1 focus:ring-emerald-500">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected($categoryId == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition">
                        <i class="fa-solid fa-filter mr-1"></i> Filter Laporan
                    </button>
                    <a href="{{ route('reports.stock', ['type' => $type]) }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition" title="Reset">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Metric Summary -->
        @if($type === 'current')
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Total Valuasi Aset Persediaan</div>
                    <div class="text-2xl font-black text-emerald-700 mt-1">Rp {{ number_format($totalValuation, 0, ',', '.') }}</div>
                    <div class="text-xs text-slate-400 mt-1">Berdasarkan HPP / harga beli</div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Total Kuantitas Fisik</div>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalStockUnits) }} Unit</div>
                    <div class="text-xs text-slate-400 mt-1">Seluruh sediaan di apotek</div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Total SKU Obat</div>
                    <div class="text-2xl font-black text-slate-800 mt-1">{{ number_format(count($items)) }} Produk</div>
                    <div class="text-xs text-slate-400 mt-1">Katalog aktif</div>
                </div>
            </div>
        @endif

        <!-- Data Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    @if($type === 'mutation')
                        <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Waktu</th>
                                <th class="py-3 px-4">Produk Obat</th>
                                <th class="py-3 px-4">Gudang</th>
                                <th class="py-3 px-4 text-center">Tipe Mutasi</th>
                                <th class="py-3 px-4 text-right">Kuantitas</th>
                                <th class="py-3 px-4">Keterangan / Ref</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($items as $m)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 text-slate-500">{{ $m->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 px-4 font-semibold text-slate-900">{{ $m->product?->name }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $m->warehouse?->name }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ in_array($m->mutation_type, ['in', 'purchase']) ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $m->mutation_type }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-slate-900">{{ number_format($m->quantity) }}</td>
                                    <td class="py-3 px-4 text-slate-500">{{ $m->notes ?: ($m->reference_type ? $m->reference_type.' #'.$m->reference_id : '-') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-8 text-center text-slate-400">Tidak ada riwayat mutasi stok pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    @elseif($type === 'expired')
                        <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Nama Produk</th>
                                <th class="py-3 px-4 text-center">No. Batch</th>
                                <th class="py-3 px-4 text-center">Tgl Expired</th>
                                <th class="py-3 px-4 text-right">Sisa Stok</th>
                                <th class="py-3 px-4 text-right">Harga Beli</th>
                                <th class="py-3 px-4 text-right">Estimasi Kerugian</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($items as $b)
                                @php
                                    $isExpired = \Carbon\Carbon::parse($b->expiry_date)->isPast();
                                    $loss = (float) $b->current_qty * (float) $b->purchase_price;
                                @endphp
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 font-semibold text-slate-900">{{ $b->product?->name }}</td>
                                    <td class="py-3 px-4 text-center font-mono text-slate-700">{{ $b->batch_number }}</td>
                                    <td class="py-3 px-4 text-center font-semibold {{ $isExpired ? 'text-red-600' : 'text-amber-600' }}">
                                        {{ \Carbon\Carbon::parse($b->expiry_date)->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-slate-900">{{ number_format($b->current_qty) }}</td>
                                    <td class="py-3 px-4 text-right text-slate-600">Rp {{ number_format($b->purchase_price, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-red-600">Rp {{ number_format($loss, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $isExpired ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $isExpired ? 'EXPIRED' : 'DEKAT EXPIRED' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-8 text-center text-slate-400">Tidak ada batch obat expired pada rentang tanggal ini.</td></tr>
                            @endforelse
                        </tbody>
                    @else
                        <!-- Default Current Stock & Valuation -->
                        <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Nama Produk Obat</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-right">Stok Fisik</th>
                                <th class="py-3 px-4 text-right">Harga Beli (HPP)</th>
                                <th class="py-3 px-4 text-right">Harga Jual</th>
                                <th class="py-3 px-4 text-right">Nilai Aset Persediaan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($items as $p)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 font-semibold text-slate-900">{{ $p->name }}</td>
                                    <td class="py-3 px-4 text-slate-500">{{ $p->category?->name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-slate-800">{{ number_format($p->filtered_stock ?? $p->stocks->sum('quantity')) }}</td>
                                    <td class="py-3 px-4 text-right text-slate-600">Rp {{ number_format($p->purchase_price, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right text-slate-800 font-medium">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-emerald-700">Rp {{ number_format($p->stock_valuation ?? ($p->stocks->sum('quantity') * (float) $p->purchase_price), 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-8 text-center text-slate-400">Tidak ada data persediaan obat.</td></tr>
                            @endforelse
                        </tbody>
                    @endif
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
