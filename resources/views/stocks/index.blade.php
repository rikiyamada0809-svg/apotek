<x-app-layout>
    <div class="space-y-6" x-data="{ 
        settingModalOpen: false, 
        selectedProduct: null,
        minStock: 10,
        maxStock: 1000,
        reorderPoint: 15,
        safetyStock: 5,
        warehouseId: '{{ $warehouses->first()?->id ?? 1 }}',
        updateUrl: '',
        openSettingModal(product) {
            this.selectedProduct = product;
            this.minStock = product.min_stock ?? 10;
            this.maxStock = product.max_stock ?? 1000;
            this.reorderPoint = product.reorder_point ?? 15;
            this.updateUrl = '/stocks/' + product.id + '/settings';
            this.settingModalOpen = true;
        }
    }">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Manajemen Inventori & Stok</h1>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">Inventori & Stok</span>
                </nav>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('stocks.card') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-clipboard-list"></i> Kartu Stok Digital
                </a>
                <a href="{{ route('batches.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-calendar-check"></i> Monitoring Batch & FEFO
                </a>
            </div>
        </div>

        @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
        @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

        <!-- Summary KPI Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-pills text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500">Total SKU Produk</p>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($totalProducts) }}</h3>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-boxes-stacked text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500">Total Qty Stok Fisik</p>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($totalStockQty, 0, ',', '.') }}</h3>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500">Stok Kritis / Menipis</p>
                    <h3 class="text-xl font-bold text-red-600 mt-0.5">{{ number_format($lowStockCount) }}</h3>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-clock-rotate-left text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500">Batch Expired / &le; 30 Hari</p>
                    <h3 class="text-xl font-bold text-amber-600 mt-0.5">{{ number_format($expiringBatchesCount) }}</h3>
                </div>
            </div>
        </div>

        <!-- Main Card with Tab Navigation and Content -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <!-- Tabs -->
            <div class="border-b border-slate-200 bg-slate-50/75 px-4 sm:px-6 pt-3 flex flex-wrap gap-2">
                <a href="{{ route('stocks.index', array_merge(request()->except('page'), ['tab' => 'products'])) }}"
                   class="px-4 py-2.5 text-xs font-bold rounded-t-xl transition-colors border-b-2 flex items-center gap-2 {{ $tab === 'products' ? 'border-[#f83f37] text-[#f83f37] bg-white' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/60' }}">
                    <i class="fa-solid fa-table-list"></i>
                    <span>Stok Per Produk</span>
                </a>

                <a href="{{ route('stocks.index', array_merge(request()->except('page'), ['tab' => 'batches'])) }}"
                   class="px-4 py-2.5 text-xs font-bold rounded-t-xl transition-colors border-b-2 flex items-center gap-2 {{ $tab === 'batches' ? 'border-[#f83f37] text-[#f83f37] bg-white' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/60' }}">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Stok Per Batch (FEFO)</span>
                </a>

                <a href="{{ route('stocks.index', array_merge(request()->except('page'), ['tab' => 'warehouses'])) }}"
                   class="px-4 py-2.5 text-xs font-bold rounded-t-xl transition-colors border-b-2 flex items-center gap-2 {{ $tab === 'warehouses' ? 'border-[#f83f37] text-[#f83f37] bg-white' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/60' }}">
                    <i class="fa-solid fa-warehouse"></i>
                    <span>Stok Per Gudang & Lokasi</span>
                </a>
            </div>

            <!-- Filter Controls -->
            <div class="p-4 border-b border-slate-100 bg-white">
                <form method="GET" action="{{ route('stocks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <input type="hidden" name="tab" value="{{ $tab }}">

                    <!-- Search Input -->
                    <div class="relative lg:col-span-2">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="{{ $tab === 'batches' ? 'Cari batch number, produk, barcode...' : 'Cari nama obat, kode, barcode, generik...' }}"
                               class="w-full pl-9 pr-4 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-xs"></i>
                    </div>

                    @if($tab === 'products')
                        <!-- Filter Kategori -->
                        <select name="category_id" class="px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Filter Gudang -->
                    <select name="warehouse_id" class="px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" @selected(request('warehouse_id') == $wh->id)>{{ $wh->name }}</option>
                        @endforeach
                    </select>

                    <!-- Filter Status Stok -->
                    <select name="stock_status" class="px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500">
                        <option value="">Semua Status</option>
                        @if($tab === 'batches')
                            <option value="active" @selected(request('stock_status') == 'active')>Aktif & Aman</option>
                            <option value="expiring_soon" @selected(request('stock_status') == 'expiring_soon')>Dekat Expired (&le; 30 Hari)</option>
                            <option value="expired" @selected(request('stock_status') == 'expired')>Sudah Kadaluarsa</option>
                            <option value="depleted" @selected(request('stock_status') == 'depleted')>Stok Habis (0)</option>
                        @else
                            <option value="available" @selected(request('stock_status') == 'available')>Stok Aman</option>
                            <option value="low_stock" @selected(request('stock_status') == 'low_stock')>Stok Kritis / Menipis</option>
                            <option value="out_of_stock" @selected(request('stock_status') == 'out_of_stock')>Stok Habis (0)</option>
                        @endif
                    </select>

                    <div class="flex items-center gap-2 {{ $tab === 'products' ? 'lg:col-span-5' : '' }} justify-end">
                        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-filter"></i> Terapkan Filter
                        </button>
                        @if(request()->hasAny(['search', 'category_id', 'warehouse_id', 'stock_status']))
                            <a href="{{ route('stocks.index', ['tab' => $tab]) }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800 font-medium">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tab Content -->
            @if($tab === 'products')
                <!-- TAB PER PRODUK -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Kode & Barcode</th>
                                <th class="px-4 py-3">Nama Produk & Kategori</th>
                                <th class="px-4 py-3 text-center">Satuan Dasar</th>
                                <th class="px-4 py-3 text-right">Stok Fisik</th>
                                <th class="px-4 py-3 text-right">Reserved</th>
                                <th class="px-4 py-3 text-right">Tersedia</th>
                                <th class="px-4 py-3 text-center">Min / ROP</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($products as $product)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-4 py-3 text-slate-400">{{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</td>
                                    <td class="px-4 py-3">
                                        <span class="font-mono font-semibold text-slate-800">{{ $product->product_code }}</span>
                                        @if($product->barcode)
                                            <div class="text-[11px] text-slate-400 font-mono"><i class="fa-solid fa-barcode mr-1"></i>{{ $product->barcode }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('products.show', $product) }}" class="font-bold text-slate-900 hover:text-[#f83f37] transition-colors block">
                                            {{ $product->name }}
                                        </a>
                                        <div class="text-[11px] text-slate-500">
                                            <span>{{ $product->category->name ?? 'Tanpa Kategori' }}</span>
                                            @if($product->generic_name)
                                                <span class="mx-1">&bull;</span>
                                                <span class="italic">{{ $product->generic_name }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center font-medium text-slate-600">
                                        {{ $product->base_unit_name }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                        {{ number_format($product->calculated_total_qty, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-500">
                                        {{ number_format($product->calculated_reserved_qty, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-sm {{ $product->calculated_available_qty <= $product->min_stock ? 'text-red-600' : 'text-emerald-600' }}">
                                        {{ number_format($product->calculated_available_qty, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-[11px] text-slate-500">
                                        <span class="font-semibold text-slate-700">{{ $product->min_stock }}</span> / 
                                        <span>{{ $product->reorder_point }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($product->stock_status === 'out_of_stock')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-600 mr-1.5"></span> Habis
                                            </span>
                                        @elseif($product->stock_status === 'low_stock')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-600 mr-1.5"></span> Kritis / Menipis
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 mr-1.5"></span> Aman
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <!-- Kartu Stok -->
                                            <a href="{{ route('stocks.card', ['product_id' => $product->id]) }}" 
                                               class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors" 
                                               title="Buka Kartu Stok">
                                                <i class="fa-solid fa-clipboard-list"></i>
                                            </a>
                                            <!-- Edit Min/Max Stock Modal -->
                                            <button type="button" 
                                                    @click="openSettingModal({{ $product->toJson() }})"
                                                    class="p-1.5 text-slate-500 hover:text-[#f83f37] hover:bg-red-50 rounded-lg transition-colors"
                                                    title="Atur Batas Stok (Min/Max)">
                                                <i class="fa-solid fa-sliders"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-slate-500">
                                        <i class="fa-solid fa-box-open text-3xl text-slate-300 mb-2 block"></i>
                                        Tidak ada data stok produk yang sesuai dengan kriteria filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $products->links() }}
                    </div>
                @endif

            @elseif($tab === 'batches')
                <!-- TAB PER BATCH (FEFO) -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Nomor Batch & Barcode</th>
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3">Supplier</th>
                                <th class="px-4 py-3">Tgl Kadaluarsa</th>
                                <th class="px-4 py-3 text-center">Status Expired (FEFO)</th>
                                <th class="px-4 py-3 text-right">Sisa Stok</th>
                                <th class="px-4 py-3">Gudang & Lokasi</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($batches as $batch)
                                @php
                                    $isExpired = $batch->isExpired();
                                    $isNear = $batch->isExpiringSoon(30);
                                    $daysRemaining = now()->startOfDay()->diffInDays($batch->expiry_date->startOfDay(), false);
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors {{ $isExpired ? 'bg-red-50/30' : ($isNear ? 'bg-amber-50/20' : '') }}">
                                    <td class="px-4 py-3 text-slate-400">{{ $loop->iteration + ($batches->currentPage() - 1) * $batches->perPage() }}</td>
                                    <td class="px-4 py-3">
                                        <span class="font-mono font-bold text-slate-900">{{ $batch->batch_number }}</span>
                                        @if($batch->barcode)
                                            <div class="text-[11px] text-slate-400 font-mono"><i class="fa-solid fa-barcode mr-1"></i>{{ $batch->barcode }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-semibold text-slate-800">{{ $batch->product->name ?? '-' }}</span>
                                        <span class="block text-[11px] text-slate-500">{{ $batch->product->product_code ?? '' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $batch->supplier->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-mono font-semibold {{ $isExpired ? 'text-red-600' : ($isNear ? 'text-amber-600' : 'text-slate-800') }}">
                                        {{ $batch->expiry_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($isExpired)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">
                                                <i class="fa-solid fa-skull-crossbones mr-1"></i> Kadaluarsa ({{ abs($daysRemaining) }} hari lalu)
                                            </span>
                                        @elseif($isNear)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                                                <i class="fa-solid fa-hourglass-half mr-1"></i> {{ $daysRemaining }} hari lagi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                                <i class="fa-solid fa-circle-check mr-1"></i> Aman ({{ $daysRemaining }} hari)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold {{ $batch->current_qty <= 0 ? 'text-slate-400' : 'text-slate-800' }}">
                                        {{ number_format($batch->current_qty, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        @foreach($batch->stocks as $stk)
                                            <div class="text-[11px]">
                                                <span class="font-medium text-slate-800">{{ $stk->warehouse->name ?? '-' }}</span>: 
                                                {{ $stk->storageLocation->name ?? 'Umum' }} ({{ number_format($stk->quantity, 0) }})
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('batches.show', $batch) }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                                            <i class="fa-regular fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-slate-500">
                                        <i class="fa-solid fa-boxes-stacked text-3xl text-slate-300 mb-2 block"></i>
                                        Tidak ada data batch obat ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($batches->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $batches->links() }}
                    </div>
                @endif

            @elseif($tab === 'warehouses')
                <!-- TAB PER GUDANG & LOKASI -->
                <div class="p-6 space-y-6">
                    @forelse($warehouseStocks as $wh)
                        <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                            <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-red-100 text-[#f83f37] flex items-center justify-center font-bold">
                                        <i class="fa-solid fa-warehouse"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900">{{ $wh->name }}</h3>
                                        <p class="text-[11px] text-slate-500">{{ $wh->address ?? 'Alamat belum diatur' }}</p>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-200/70 text-slate-700">
                                    {{ $wh->storageLocations->count() }} Lokasi Rak
                                </span>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @forelse($wh->storageLocations as $loc)
                                    <div class="p-4 hover:bg-slate-50/50 transition-colors">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-xs text-slate-800">{{ $loc->name }}</span>
                                                <span class="text-[10px] font-mono bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">{{ $loc->code }}</span>
                                                @if($loc->type)
                                                    <span class="text-[10px] bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded">{{ ucfirst($loc->type) }}</span>
                                                @endif
                                            </div>
                                            <span class="text-xs text-slate-500 font-medium">
                                                {{ $loc->stocks->count() }} Item Stok
                                            </span>
                                        </div>

                                        @if($loc->stocks->count() > 0)
                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 mt-2">
                                                @foreach($loc->stocks as $stock)
                                                    <div class="bg-white border border-slate-100 rounded-lg p-2.5 shadow-sm text-xs">
                                                        <div class="font-semibold text-slate-900 truncate">{{ $stock->product->name ?? '-' }}</div>
                                                        <div class="flex items-center justify-between text-[11px] text-slate-500 mt-1">
                                                            <span>Batch: <strong class="font-mono text-slate-700">{{ $stock->batch->batch_number ?? '-' }}</strong></span>
                                                            <span class="font-bold text-emerald-600">{{ number_format($stock->quantity, 0) }} Qty</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-[11px] text-slate-400 italic">Lokasi rak ini masih kosong.</p>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-xs text-slate-400">
                                        Belum ada lokasi penyimpanan / rak di gudang ini.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400">
                            Tidak ada data gudang aktif.
                        </div>
                    @endforelse
                </div>
            @endif
        </div>

        <!-- MODAL PENGATURAN BATAS STOK (MIN/MAX/ROP) -->
        <div x-show="settingModalOpen" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4"
             x-transition x-cloak>
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100" @click.away="settingModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-[#f83f37]"></i> Atur Batas Stok Minimum
                    </h3>
                    <button @click="settingModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>

                <form :action="updateUrl" method="POST" class="space-y-4 mt-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Produk</label>
                        <p class="font-bold text-slate-900" x-text="selectedProduct?.name"></p>
                        <p class="text-slate-500 font-mono text-[11px]" x-text="selectedProduct?.product_code"></p>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Gudang</label>
                        <select name="warehouse_id" x-model="warehouseId" required class="w-full text-xs border border-slate-200 rounded-lg p-2 focus:ring-1 focus:ring-red-500">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Stok Minimum (Min)</label>
                            <input type="number" step="0.01" name="min_stock" x-model="minStock" required 
                                   class="w-full text-xs border border-slate-200 rounded-lg p-2 focus:ring-1 focus:ring-red-500">
                            <span class="text-[10px] text-slate-400">Trigger alert stok kritis</span>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Titik Pesan Kembali (ROP)</label>
                            <input type="number" step="0.01" name="reorder_point" x-model="reorderPoint" required 
                                   class="w-full text-xs border border-slate-200 rounded-lg p-2 focus:ring-1 focus:ring-red-500">
                            <span class="text-[10px] text-slate-400">Reorder point ke PO</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Stok Maksimum (Max)</label>
                            <input type="number" step="0.01" name="max_stock" x-model="maxStock" required 
                                   class="w-full text-xs border border-slate-200 rounded-lg p-2 focus:ring-1 focus:ring-red-500">
                            <span class="text-[10px] text-slate-400">Kapasitas penyimpanan</span>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Safety Stock</label>
                            <input type="number" step="0.01" name="safety_stock" x-model="safetyStock" 
                                   class="w-full text-xs border border-slate-200 rounded-lg p-2 focus:ring-1 focus:ring-red-500">
                            <span class="text-[10px] text-slate-400">Cadangan pengaman</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="settingModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-[#f83f37] hover:bg-red-600 text-white rounded-lg font-semibold">
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
