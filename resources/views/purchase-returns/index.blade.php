<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Retur Pembelian (Supplier)</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('purchase-orders.index') }}" class="hover:text-slate-900">Pembelian</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Retur Pembelian</span>
            </nav>
        </div>
        <a href="{{ route('purchase-returns.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Buat Retur Pembelian
        </a>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Retur</p>
                <p class="text-xl font-bold text-slate-800">{{ number_format($totalReturns) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Menunggu Persetujuan</p>
                <p class="text-xl font-bold text-amber-600">{{ number_format($pendingReturns) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-paper-plane"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Dikirim ke Supplier</p>
                <p class="text-xl font-bold text-purple-600">{{ number_format($sentReturns) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-check-circle"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Selesai / Resolved</p>
                <p class="text-xl font-bold text-emerald-600">{{ number_format($completedReturns) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <form method="GET" action="{{ route('purchase-returns.index') }}" class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Retur atau No. PO..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-sm"></i>
                </div>
                <select name="supplier_id" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                    <option value="sent" @selected(request('status') === 'sent')>Dikirim</option>
                    <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
                </select>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                @if(request()->hasAny(['search','supplier_id','status','start_date','end_date']))
                    <a href="{{ route('purchase-returns.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">No. Retur</th>
                        <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
                        <th class="px-4 py-3 text-left font-semibold">Supplier</th>
                        <th class="px-4 py-3 text-left font-semibold">Ref. PO</th>
                        <th class="px-4 py-3 text-left font-semibold">Solusi</th>
                        <th class="px-4 py-3 text-right font-semibold">Total Nilai</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($returns as $return)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                <a href="{{ route('purchase-returns.show', $return) }}" class="text-emerald-600 hover:underline">
                                    {{ $return->return_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ \Carbon\Carbon::parse($return->return_date)->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-slate-800 font-medium">
                                {{ $return->supplier->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                @if($return->purchaseOrder)
                                    <a href="{{ route('purchase-orders.show', $return->purchaseOrder) }}" class="text-blue-600 hover:underline">
                                        {{ $return->purchaseOrder->po_number }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($return->resolution_type === 'replacement')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">Ganti Barang</span>
                                @elseif($return->resolution_type === 'credit_note')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700">Nota Kredit / Potong Faktur</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-700">Refund Kas</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">
                                Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusClasses = [
                                        'draft' => 'bg-slate-100 text-slate-700',
                                        'approved' => 'bg-blue-100 text-blue-700',
                                        'sent' => 'bg-purple-100 text-purple-700',
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'cancelled' => 'bg-rose-100 text-rose-700',
                                    ];
                                    $statusLabels = [
                                        'draft' => 'Draft',
                                        'approved' => 'Disetujui',
                                        'sent' => 'Dikirim',
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
                                    <a href="{{ route('purchase-returns.show', $return) }}" title="Lihat Detail" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('purchase-returns.print-pdf', $return) }}" target="_blank" title="Cetak Surat Retur" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-truck-ramp-box text-3xl mb-2"></i>
                                <p>Belum ada data retur pembelian.</p>
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
