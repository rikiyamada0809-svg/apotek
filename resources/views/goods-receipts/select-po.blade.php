<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Pilih Purchase Order untuk Penerimaan Barang</h1>
        <nav class="flex text-xs text-slate-500 mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('goods-receipts.index') }}" class="hover:text-slate-900">Penerimaan Barang</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 font-medium">Pilih PO</span>
        </nav>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <p class="text-sm text-slate-600">Pilih salah satu Purchase Order di bawah ini yang statusnya telah disetujui untuk mulai mencatat penerimaan fisik barang ke gudang:</p>
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
                        <th class="px-4 py-3 text-center font-semibold">Status PO</th>
                        <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($availableOrders as $order)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $order->po_number }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $order->order_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $order->supplier->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $order->warehouse->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ format_rupiah($order->grand_total) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $order->status === 'approved' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $order->status === 'approved' ? 'Disetujui' : 'Parsial Terima' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('goods-receipts.create', ['po_id' => $order->id]) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors">
                                    <i class="fa-solid fa-dolly"></i> Proses Terima
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-3xl mb-2"></i>
                                <p>Tidak ada Purchase Order yang siap diterima (seluruh PO belum disetujui atau sudah lengkap diterima).</p>
                                <div class="mt-3">
                                    <a href="{{ route('purchase-orders.create') }}" class="text-xs text-emerald-600 hover:underline font-semibold">
                                        + Buat Purchase Order Baru
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
