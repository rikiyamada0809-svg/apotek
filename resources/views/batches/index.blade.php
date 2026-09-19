<x-app-layout>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Manajemen Batch & FEFO</h1>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('stocks.index') }}" class="hover:text-slate-900">Inventori & Stok</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">Monitoring Batch</span>
                </nav>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('stocks.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                    <i class="fa-solid fa-boxes-stacked"></i> Ringkasan Stok
                </a>
                <a href="{{ route('goods-receipts.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-plus"></i> Terima Batch Baru
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-layer-group text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500">Total Lot / Batch</p>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($totalBatches) }}</h3>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-check text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500">Batch Aktif & Aman</p>
                    <h3 class="text-xl font-bold text-emerald-600 mt-0.5">{{ number_format($activeBatches) }}</h3>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-hourglass-half text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500">Dekat Kadaluarsa (&le; 30 Hari)</p>
                    <h3 class="text-xl font-bold text-amber-600 mt-0.5">{{ number_format($nearExpiryBatches) }}</h3>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500">Sudah Kadaluarsa</p>
                    <h3 class="text-xl font-bold text-red-600 mt-0.5">{{ number_format($expiredBatches) }}</h3>
                </div>
            </div>
        </div>

        <!-- Filter & Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                <form method="GET" action="{{ route('batches.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <!-- Search Input -->
                    <div class="relative lg:col-span-2">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Cari nomor batch, barcode, atau nama produk..."
                               class="w-full pl-9 pr-4 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-xs"></i>
                    </div>

                    <!-- Filter Status Expired -->
                    <select name="status" class="px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500">
                        <option value="">Semua Status FEFO</option>
                        <option value="active" @selected(request('status') == 'active')>Aktif & Aman</option>
                        <option value="expiring_soon" @selected(request('status') == 'expiring_soon')>Mendekati Kadaluarsa (&le; 30 Hari)</option>
                        <option value="expired" @selected(request('status') == 'expired')>Sudah Kadaluarsa</option>
                        <option value="depleted" @selected(request('status') == 'depleted')>Stok Habis (0)</option>
                    </select>

                    <!-- Filter Supplier -->
                    <select name="supplier_id" class="px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500">
                        <option value="">Semua Supplier</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" @selected(request('supplier_id') == $sup->id)>{{ $sup->name }}</option>
                        @endforeach
                    </select>

                    <div class="flex items-center gap-2 justify-end">
                        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-filter"></i> Filter
                        </button>
                        @if(request()->hasAny(['search', 'status', 'supplier_id', 'product_id']))
                            <a href="{{ route('batches.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Batches Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-center">Urutan FEFO</th>
                            <th class="px-4 py-3">Nomor Batch</th>
                            <th class="px-4 py-3">Produk / Obat</th>
                            <th class="px-4 py-3">Supplier</th>
                            <th class="px-4 py-3">Tgl Kadaluarsa</th>
                            <th class="px-4 py-3 text-center">Indikator Kadaluarsa</th>
                            <th class="px-4 py-3 text-right">Sisa Stok</th>
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
                                <td class="px-4 py-3 text-center font-mono font-bold text-slate-400">
                                    #{{ $loop->iteration + ($batches->currentPage() - 1) * $batches->perPage() }}
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('batches.show', $batch) }}" class="font-mono font-bold text-slate-900 hover:text-[#f83f37] transition-colors block">
                                        {{ $batch->batch_number }}
                                    </a>
                                    @if($batch->barcode)
                                        <div class="text-[11px] text-slate-400 font-mono"><i class="fa-solid fa-barcode mr-1"></i>{{ $batch->barcode }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-slate-900">{{ $batch->product->name ?? '-' }}</span>
                                    <span class="block text-[11px] text-slate-500 font-mono">{{ $batch->product->product_code ?? '' }}</span>
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
                                <td class="px-4 py-3 text-right font-bold {{ $batch->current_qty <= 0 ? 'text-slate-400' : 'text-slate-900' }}">
                                    {{ number_format($batch->current_qty, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('batches.show', $batch) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                                        <i class="fa-regular fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-layer-group text-3xl text-slate-300 mb-2 block"></i>
                                    Tidak ada data batch yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($batches->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $batches->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
