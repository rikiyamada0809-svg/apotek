<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900">{{ $goodsReceipt->receipt_number }}</h1>
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200">
                    {{ ucfirst($goodsReceipt->status) }}
                </span>
            </div>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('goods-receipts.index') }}" class="hover:text-slate-900">Penerimaan Barang</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Detail</span>
            </nav>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('goods-receipts.print-pdf', $goodsReceipt) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                <i class="fa-solid fa-file-pdf text-rose-600"></i> Cetak Bukti Terima
            </a>

            @if($goodsReceipt->purchaseOrder)
                <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-arrow-left"></i> Lihat PO Terkait
                </a>
            @endif
        </div>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Info Dokumen & Surat Jalan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-2 text-xs">
            <h2 class="font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-file-lines text-emerald-600"></i> Informasi Dokumen
            </h2>
            <div class="flex justify-between">
                <span class="text-slate-500">Tanggal Penerimaan:</span>
                <span class="font-semibold text-slate-800">{{ $goodsReceipt->receipt_date->format('d M Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">No. Surat Jalan:</span>
                <span class="font-semibold text-slate-800">{{ $goodsReceipt->delivery_note_number ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Referensi PO:</span>
                @if($goodsReceipt->purchaseOrder)
                    <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}" class="font-semibold text-emerald-600 hover:underline">
                        {{ $goodsReceipt->purchaseOrder->po_number }}
                    </a>
                @else
                    <span class="text-slate-400">-</span>
                @endif
            </div>
        </div>

        <!-- Info Supplier -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-2 text-xs">
            <h2 class="font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-truck-field text-emerald-600"></i> Supplier & Pengirim
            </h2>
            <div class="text-sm font-bold text-slate-900">{{ $goodsReceipt->supplier->name ?? '-' }}</div>
            <div class="text-slate-500">Kontak: {{ $goodsReceipt->supplier->contact_person ?? '-' }} ({{ $goodsReceipt->supplier->phone ?? '-' }})</div>
            <div class="text-slate-500">Kota: {{ $goodsReceipt->supplier->city ?? '-' }}</div>
        </div>

        <!-- Info Gudang & Petugas -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-2 text-xs">
            <h2 class="font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-warehouse text-emerald-600"></i> Lokasi & Petugas
            </h2>
            <div class="flex justify-between">
                <span class="text-slate-500">Gudang Penerima:</span>
                <span class="font-semibold text-slate-800">{{ $goodsReceipt->warehouse->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Diterima Oleh:</span>
                <span class="font-semibold text-slate-800">{{ $goodsReceipt->receiver->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Waktu Catat:</span>
                <span class="font-medium text-slate-600">{{ $goodsReceipt->created_at->format('d M Y H:i') }}</span>
            </div>
        </div>
    </div>

    @if($goodsReceipt->notes)
        <div class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600">
            <span class="font-bold text-slate-800">Catatan Penerimaan:</span> {{ $goodsReceipt->notes }}
        </div>
    @endif

    <!-- Tabel Rincian Item Fisik yang Diterima -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-emerald-600"></i> Rincian Barang Fisik & Batch yang Telah Masuk Stok
            </h2>
            <span class="text-xs text-slate-500">{{ $goodsReceipt->items->count() }} item</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">#</th>
                        <th class="px-4 py-3 text-left font-semibold">Nama Produk / Obat</th>
                        <th class="px-4 py-3 text-left font-semibold">No. Batch</th>
                        <th class="px-4 py-3 text-left font-semibold">Tgl Expired</th>
                        <th class="px-4 py-3 text-right font-semibold">Qty Diterima</th>
                        <th class="px-4 py-3 text-left font-semibold">Lokasi Rak</th>
                        <th class="px-4 py-3 text-center font-semibold">Kondisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @foreach($goodsReceipt->items as $index => $item)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 text-slate-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-800">{{ $item->product->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">Kode: {{ $item->product->product_code ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 font-mono font-bold text-slate-700">{{ $item->batch_number }}</td>
                            <td class="px-4 py-3 font-medium text-slate-700">
                                {{ $item->expiry_date->format('d M Y') }}
                                @if($item->expiry_date->isPast())
                                    <span class="ml-1 text-[10px] text-rose-600 font-bold">(Expired)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-600 text-sm">
                                {{ (float) $item->quantity_received }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                @if($item->storageLocation)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                        <i class="fa-solid fa-cubes text-[10px] text-slate-400"></i>
                                        {{ $item->storageLocation->code }}
                                    </span>
                                @else
                                    <span class="text-slate-400">Gudang Umum</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $item->condition === 'good' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $item->condition === 'good' ? 'Baik' : 'Rusak' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
