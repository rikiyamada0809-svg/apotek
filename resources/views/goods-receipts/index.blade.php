<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Penerimaan Barang (Goods Receipt)</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Penerimaan Barang</span>
            </nav>
        </div>
        <a href="{{ route('goods-receipts.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Terima Barang dari PO
        </a>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <form method="GET" action="{{ route('goods-receipts.index') }}" class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Terima, No. SJ, atau No. PO..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-sm"></i>
                </div>
                <select name="supplier_id" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg transition-colors">Filter</button>
                @if(request()->hasAny(['search','supplier_id','start_date','end_date']))
                    <a href="{{ route('goods-receipts.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">No. Bukti Terima</th>
                        <th class="px-4 py-3 text-left font-semibold">Tanggal Terima</th>
                        <th class="px-4 py-3 text-left font-semibold">Referensi PO</th>
                        <th class="px-4 py-3 text-left font-semibold">Supplier</th>
                        <th class="px-4 py-3 text-left font-semibold">Surat Jalan</th>
                        <th class="px-4 py-3 text-left font-semibold">Penerima</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($goodsReceipts as $receipt)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                <a href="{{ route('goods-receipts.show', $receipt) }}" class="text-emerald-600 hover:text-emerald-700 hover:underline">
                                    {{ $receipt->receipt_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $receipt->receipt_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                @if($receipt->purchaseOrder)
                                    <a href="{{ route('purchase-orders.show', $receipt->purchaseOrder) }}" class="font-medium text-slate-700 hover:text-blue-600 hover:underline">
                                        {{ $receipt->purchaseOrder->po_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700 font-medium">{{ $receipt->supplier->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $receipt->delivery_note_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $receipt->receiver->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                    {{ ucfirst($receipt->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('goods-receipts.show', $receipt) }}" title="Lihat Detail" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('goods-receipts.print-pdf', $receipt) }}" target="_blank" title="Cetak PDF" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100">
                                        <i class="fa-solid fa-file-pdf text-sm"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-dolly text-3xl mb-2"></i>
                                <p>Belum ada bukti penerimaan barang.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($goodsReceipts->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $goodsReceipts->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
