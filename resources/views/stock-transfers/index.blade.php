<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Transfer Stok Multi-Gudang</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('stocks.index') }}" class="hover:text-slate-900">Inventori</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Transfer Stok</span>
            </nav>
        </div>
        <a href="{{ route('stock-transfers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Buat Transfer Baru
        </a>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Transfer</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total']) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Draft Permintaan</p>
                <p class="text-2xl font-bold text-amber-600">{{ number_format($stats['draft']) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-truck-fast"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Disetujui / Siap Kirim</p>
                <p class="text-2xl font-bold text-indigo-600">{{ number_format($stats['approved']) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Selesai Diterima</p>
                <p class="text-2xl font-bold text-emerald-600">{{ number_format($stats['received']) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-100">
            <form method="GET" action="{{ route('stock-transfers.index') }}" class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari No. Transfer..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-sm"></i>
                </div>

                <select name="status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                    <option value="received" {{ $status === 'received' ? 'selected' : '' }}>Diterima (Received)</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>

                <select name="from_warehouse_id" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Gudang Asal</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $fromWarehouseId == $wh->id ? 'selected' : '' }}>Dari: {{ $wh->name }}</option>
                    @endforeach
                </select>

                <select name="to_warehouse_id" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Gudang Tujuan</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $toWarehouseId == $wh->id ? 'selected' : '' }}>Ke: {{ $wh->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg transition-colors">
                    Filter
                </button>
                @if($search || $status || $fromWarehouseId || $toWarehouseId)
                    <a href="{{ route('stock-transfers.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-lg transition-colors">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. Transfer</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Gudang Asal</th>
                        <th class="py-3 px-4">Gudang Tujuan</th>
                        <th class="py-3 px-4 text-center">Jumlah Item</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Dibuat Oleh</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transfers as $transfer)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                <a href="{{ route('stock-transfers.show', $transfer) }}" class="text-emerald-600 hover:underline">
                                    {{ $transfer->transfer_number }}
                                </a>
                            </td>
                            <td class="py-3 px-4 text-slate-600 text-xs">
                                {{ $transfer->transfer_date?->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4 text-xs font-medium text-slate-800">
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-warehouse text-slate-400 text-xs"></i>
                                    {{ $transfer->fromWarehouse?->name }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-xs font-medium text-slate-800">
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-arrow-right text-emerald-500 text-xs"></i>
                                    {{ $transfer->toWarehouse?->name }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center text-xs font-bold text-slate-700">
                                {{ $transfer->items->count() }} Item
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($transfer->status === 'draft')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-file-pen text-[10px]"></i> Draft
                                    </span>
                                @elseif($transfer->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        <i class="fa-solid fa-check text-[10px]"></i> Disetujui
                                    </span>
                                @elseif($transfer->status === 'received')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i> Diterima
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        Dibatalkan
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-600">
                                {{ $transfer->creator?->name ?? 'System' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('stock-transfers.show', $transfer) }}" class="p-1.5 text-slate-400 hover:text-emerald-600 rounded transition-colors" title="Lihat Detail">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('stock-transfers.pdf', $transfer) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-rose-600 rounded transition-colors" title="Surat Jalan (PDF)">
                                        <i class="fa-solid fa-file-pdf text-sm"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-500">
                                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                                    <i class="fa-solid fa-truck-arrow-right"></i>
                                </div>
                                <p class="font-semibold text-slate-700">Belum ada data transfer stok</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol di atas untuk membuat transfer obat antar gudang.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
