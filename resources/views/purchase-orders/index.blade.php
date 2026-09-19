<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Purchase Order (PO)</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Purchase Order</span>
            </nav>
        </div>
        <a href="{{ route('purchase-orders.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Buat Purchase Order
        </a>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <form method="GET" action="{{ route('purchase-orders.index') }}" class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. PO atau nama supplier..."
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
                    <option value="pending_approval" @selected(request('status') === 'pending_approval')>Menunggu Approval</option>
                    <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                    <option value="partially_received" @selected(request('status') === 'partially_received')>Diterima Sebagian</option>
                    <option value="received" @selected(request('status') === 'received')>Diterima Lengkap</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
                </select>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg transition-colors">Filter</button>
                @if(request()->hasAny(['search','supplier_id','status','start_date','end_date']))
                    <a href="{{ route('purchase-orders.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">No. PO</th>
                        <th class="px-4 py-3 text-left font-semibold">Tanggal Order</th>
                        <th class="px-4 py-3 text-left font-semibold">Supplier</th>
                        <th class="px-4 py-3 text-left font-semibold">Gudang Tujuan</th>
                        <th class="px-4 py-3 text-right font-semibold">Grand Total</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchaseOrders as $po)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                <a href="{{ route('purchase-orders.show', $po) }}" class="text-emerald-600 hover:text-emerald-700 hover:underline">
                                    {{ $po->po_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $po->order_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">{{ $po->supplier->name ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $po->supplier->phone ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $po->warehouse->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ format_rupiah($po->grand_total) }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusClasses = [
                                        'draft' => 'bg-slate-100 text-slate-700',
                                        'pending_approval' => 'bg-amber-100 text-amber-800',
                                        'approved' => 'bg-blue-100 text-blue-800',
                                        'partially_received' => 'bg-purple-100 text-purple-800',
                                        'received' => 'bg-emerald-100 text-emerald-800',
                                        'cancelled' => 'bg-rose-100 text-rose-800',
                                    ];
                                    $statusLabels = [
                                        'draft' => 'Draft',
                                        'pending_approval' => 'Pending Approval',
                                        'approved' => 'Disetujui',
                                        'partially_received' => 'Parsial Terima',
                                        'received' => 'Lengkap',
                                        'cancelled' => 'Dibatalkan',
                                    ];
                                @endphp
                                <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$po->status] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $statusLabels[$po->status] ?? ucfirst($po->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('purchase-orders.show', $po) }}" title="Lihat Detail" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('purchase-orders.print-pdf', $po) }}" target="_blank" title="Cetak PDF" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100">
                                        <i class="fa-solid fa-file-pdf text-sm"></i>
                                    </a>
                                    @if(in_array($po->status, ['draft', 'pending_approval']))
                                        <a href="{{ route('purchase-orders.edit', $po) }}" title="Edit PO" class="p-1.5 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-slate-100">
                                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-cart-shopping text-3xl mb-2"></i>
                                <p>Belum ada Purchase Order yang dibuat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchaseOrders->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
