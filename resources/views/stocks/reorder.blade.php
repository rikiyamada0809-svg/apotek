<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Rekomendasi Pemesanan Ulang (Auto-Reorder)</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('stocks.index') }}" class="hover:text-slate-900">Inventori</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Auto-Reorder & ROP</span>
            </nav>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-file-invoice text-slate-500"></i> Lihat Daftar PO
            </a>
        </div>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Perlu Reorder (≤ ROP)</p>
                <p class="text-2xl font-bold text-amber-600">{{ number_format($stats['total_reorder_items']) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-boxes-packing"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Stok Habis (0 Unit)</p>
                <p class="text-2xl font-bold text-rose-600">{{ number_format($stats['out_of_stock_count']) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Estimasi Anggaran</p>
                <p class="text-lg font-bold text-emerald-600">Rp {{ number_format($stats['total_estimated_budget'], 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-truck-field"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Supplier Terkait</p>
                <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['suppliers_count']) }}</p>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6">
        <form method="GET" action="{{ route('stocks.reorder') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Gudang / Outlet</label>
                <select name="warehouse_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $selectedWarehouseId == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }} ({{ $wh->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Status Ketersediaan</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="" {{ empty($statusFilter) ? 'selected' : '' }}>Semua Rekomendasi (≤ ROP)</option>
                    <option value="out_of_stock" {{ $statusFilter === 'out_of_stock' ? 'selected' : '' }}>Hanya Stok Kosong (0)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Kategori Obat</label>
                <select name="category_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Cari Produk / Kode</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama atau kode obat..."
                           class="w-full pl-8 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-2.5 top-2.5 text-xs"></i>
                </div>
            </div>
        </form>
    </div>

    <!-- Reorder Items Form & Table -->
    <form method="POST" action="{{ route('stocks.reorder.generate-po') }}" id="reorderForm">
        @csrf
        <input type="hidden" name="warehouse_id" value="{{ $selectedWarehouseId }}">

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="selectAll" class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500">
                    <label for="selectAll" class="text-sm font-semibold text-slate-700">Pilih Semua ({{ count($reorderItems) }} Obat)</label>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                        <i class="fa-solid fa-file-circle-plus"></i> Buat Draft Purchase Order (PO)
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-10">Pilih</th>
                            <th class="py-3 px-4">Kode & Nama Obat</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4 text-center">Stok Saat Ini</th>
                            <th class="py-3 px-4 text-center">Min / ROP / Max</th>
                            <th class="py-3 px-4 text-center w-36">Qty Order Rekomendasi</th>
                            <th class="py-3 px-4 text-right">Harga Beli</th>
                            <th class="py-3 px-4 text-right">Estimasi Total</th>
                            <th class="py-3 px-4">Supplier Utama</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reorderItems as $item)
                            <tr class="hover:bg-slate-50/80 transition-colors {{ $item->is_out_of_stock ? 'bg-rose-50/30' : '' }}">
                                <td class="py-3 px-4 text-center">
                                    <input type="checkbox" name="selected_products[]" value="{{ $item->product_id }}"
                                           class="item-checkbox w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500"
                                           {{ $item->is_below_rop ? 'checked' : '' }}>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-900">{{ $item->product_name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $item->product_code }}</div>
                                </td>
                                <td class="py-3 px-4 text-slate-600 text-xs">
                                    {{ $item->category_name }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($item->is_out_of_stock)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700">
                                            <i class="fa-solid fa-triangle-exclamation"></i> 0 {{ $item->base_unit }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                            {{ number_format($item->current_stock, 0) }} {{ $item->base_unit }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center text-xs font-mono">
                                    <span class="text-slate-500">{{ number_format($item->min_stock, 0) }}</span> /
                                    <span class="font-bold text-amber-600">{{ number_format($item->reorder_point, 0) }}</span> /
                                    <span class="text-slate-500">{{ number_format($item->max_stock, 0) }}</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <input type="number" step="1" min="1" name="quantities[{{ $item->product_id }}]"
                                               value="{{ ceil($item->suggested_qty) }}"
                                               class="w-20 px-2 py-1 text-center font-bold text-slate-800 border border-slate-300 rounded focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                                        <span class="text-xs text-slate-500">{{ $item->base_unit }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-xs">
                                    Rp {{ number_format($item->purchase_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-semibold text-slate-800 text-xs">
                                    Rp {{ number_format($item->estimated_cost, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="inline-flex items-center gap-1.5 text-slate-700">
                                        <i class="fa-solid fa-truck text-slate-400 text-[10px]"></i>
                                        {{ $item->supplier_name }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-500">
                                    <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                                        <i class="fa-solid fa-circle-check"></i>
                                    </div>
                                    <p class="font-semibold text-slate-700 text-base">Stok Dalam Kondisi Aman!</p>
                                    <p class="text-xs text-slate-400 mt-1">Tidak ada produk yang berada di bawah titik pemesanan ulang (ROP) untuk filter ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('selectAll');
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    itemCheckboxes.forEach(cb => cb.checked = selectAll.checked);
                });
            }
        });
    </script>
</x-app-layout>
