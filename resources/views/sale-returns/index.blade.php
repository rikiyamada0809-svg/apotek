<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Retur Penjualan Pasien / Konsumen</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('pos.index') }}" class="hover:text-slate-900">Kasir & Penjualan</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Retur Penjualan</span>
            </nav>
        </div>
        <a href="{{ route('sale-returns.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Proses Retur Pasien
        </a>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-arrow-rotate-left"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Retur Penjualan</p>
                <p class="text-xl font-bold text-slate-800">{{ number_format($totalReturns) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Menunggu Persetujuan</p>
                <p class="text-xl font-bold text-amber-600">{{ number_format($pendingReturns) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Retur Selesai</p>
                <p class="text-xl font-bold text-emerald-600">{{ number_format($completedReturns) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Dana Direfund</p>
                <p class="text-xl font-bold text-rose-600">Rp {{ number_format($totalRefundAmount, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <form method="GET" action="{{ route('sale-returns.index') }}" class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Retur atau No. Struk Kasir..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-sm"></i>
                </div>
                <select name="customer_id" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Pelanggan</option>
                    @foreach($customers as $cust)
                        <option value="{{ $cust->id }}" @selected(request('customer_id') == $cust->id)>{{ $cust->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                    <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
                </select>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                @if(request()->hasAny(['search','customer_id','status','start_date','end_date']))
                    <a href="{{ route('sale-returns.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Retur</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Ref. Struk Penjualan</th>
                        <th class="px-4 py-3 text-left">Pelanggan / Pasien</th>
                        <th class="px-4 py-3 text-left">Solusi</th>
                        <th class="px-4 py-3 text-right">Nilai Retur</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($returns as $return)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                <a href="{{ route('sale-returns.show', $return) }}" class="text-emerald-600 hover:underline">
                                    {{ $return->return_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ \Carbon\Carbon::parse($return->return_date)->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                @if($return->originalSale)
                                    <a href="{{ route('pos.receipt', $return->originalSale) }}" class="text-blue-600 hover:underline font-mono">
                                        {{ $return->originalSale->invoice_number }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-800">
                                {{ $return->customer->name ?? ($return->originalSale->customer_name ?? 'Umum / Pasien Langsung') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($return->resolution_type === 'replacement')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">Tukar Obat</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-50 text-rose-700">Refund Uang</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">
                                Rp {{ number_format($return->refund_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusClasses = [
                                        'draft' => 'bg-slate-100 text-slate-700',
                                        'approved' => 'bg-blue-100 text-blue-700',
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'cancelled' => 'bg-rose-100 text-rose-700',
                                    ];
                                    $statusLabels = [
                                        'draft' => 'Draft',
                                        'approved' => 'Disetujui',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClasses[$return->status] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $statusLabels[$return->status] ?? ucfirst($return->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('sale-returns.show', $return) }}" title="Lihat Detail" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('sale-returns.print-pdf', $return) }}" target="_blank" title="Cetak Bukti Retur" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-arrow-rotate-left text-3xl mb-2"></i>
                                <p>Belum ada data retur penjualan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $returns->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
