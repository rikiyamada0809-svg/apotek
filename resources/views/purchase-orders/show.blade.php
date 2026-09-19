<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900">{{ $purchaseOrder->po_number }}</h1>
                @php
                    $statusClasses = [
                        'draft' => 'bg-slate-100 text-slate-700 border-slate-200',
                        'pending_approval' => 'bg-amber-50 text-amber-800 border-amber-200',
                        'approved' => 'bg-blue-50 text-blue-800 border-blue-200',
                        'partially_received' => 'bg-purple-50 text-purple-800 border-purple-200',
                        'received' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                        'cancelled' => 'bg-rose-50 text-rose-800 border-rose-200',
                    ];
                    $statusLabels = [
                        'draft' => 'Draft',
                        'pending_approval' => 'Menunggu Persetujuan',
                        'approved' => 'Disetujui',
                        'partially_received' => 'Diterima Sebagian',
                        'received' => 'Diterima Lengkap',
                        'cancelled' => 'Dibatalkan',
                    ];
                @endphp
                <span class="px-3 py-1 text-xs font-semibold rounded-full border {{ $statusClasses[$purchaseOrder->status] ?? 'bg-slate-100 text-slate-600' }}">
                    {{ $statusLabels[$purchaseOrder->status] ?? ucfirst($purchaseOrder->status) }}
                </span>
            </div>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('purchase-orders.index') }}" class="hover:text-slate-900">Purchase Order</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Detail</span>
            </nav>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Cetak PDF -->
            <a href="{{ route('purchase-orders.print-pdf', $purchaseOrder) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                <i class="fa-solid fa-file-pdf text-rose-600"></i> Cetak PDF
            </a>

            <!-- Duplikat PO -->
            <a href="{{ route('purchase-orders.create', ['duplicate_from' => $purchaseOrder->id]) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                <i class="fa-solid fa-copy text-blue-600"></i> Duplikat PO
            </a>

            <!-- Approval button -->
            @if(in_array($purchaseOrder->status, ['draft', 'pending_approval']))
                <form method="POST" action="{{ route('purchase-orders.approve', $purchaseOrder) }}">
                    @csrf
                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menyetujui Purchase Order ini?')"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                        <i class="fa-solid fa-circle-check"></i> Setujui (Approve)
                    </button>
                </form>
            @endif

            <!-- Buat Penerimaan Barang (Goods Receipt) -->
            @if(in_array($purchaseOrder->status, ['approved', 'sent', 'partially_received']))
                <a href="{{ route('goods-receipts.create', ['po_id' => $purchaseOrder->id]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-dolly"></i> Terima Barang
                </a>
            @endif

            <!-- Buat Faktur Pembelian -->
            @if(in_array($purchaseOrder->status, ['approved', 'partially_received', 'received']))
                <a href="{{ route('purchase-invoices.create', ['po_id' => $purchaseOrder->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-receipt"></i> Buat Faktur
                </a>
            @endif

            <!-- Edit / Batal -->
            @if(in_array($purchaseOrder->status, ['draft', 'pending_approval']))
                <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                </a>

                <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}">
                    @csrf
                    <button type="submit" onclick="return confirm('Batalkan Purchase Order ini?')"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded-lg transition-colors">
                        <i class="fa-solid fa-ban"></i> Batal
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Info Supplier -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-truck-field text-emerald-600"></i> Informasi Supplier
            </h2>
            <div class="space-y-1.5">
                <div class="text-base font-bold text-slate-900">{{ $purchaseOrder->supplier->name ?? '-' }}</div>
                <div class="text-xs text-slate-500 flex items-center gap-2">
                    <i class="fa-solid fa-user w-3.5 text-slate-400"></i>
                    <span>Kontak: {{ $purchaseOrder->supplier->contact_person ?? '-' }}</span>
                </div>
                <div class="text-xs text-slate-500 flex items-center gap-2">
                    <i class="fa-solid fa-phone w-3.5 text-slate-400"></i>
                    <span>Telepon: {{ $purchaseOrder->supplier->phone ?? '-' }}</span>
                </div>
                <div class="text-xs text-slate-500 flex items-center gap-2">
                    <i class="fa-solid fa-location-dot w-3.5 text-slate-400"></i>
                    <span>Alamat: {{ $purchaseOrder->supplier->address ?? '-' }} ({{ $purchaseOrder->supplier->city ?? '-' }})</span>
                </div>
            </div>
        </div>

        <!-- Info Pengiriman & Gudang -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-warehouse text-emerald-600"></i> Gudang Tujuan & Jadwal
            </h2>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">Gudang Penerimaan:</span>
                    <span class="font-semibold text-slate-800">{{ $purchaseOrder->warehouse->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Tanggal Order:</span>
                    <span class="font-semibold text-slate-800">{{ $purchaseOrder->order_date->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Perkiraan Pengiriman:</span>
                    <span class="font-semibold text-slate-800">
                        {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d M Y') : 'Tidak ditentukan' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Info Pembuat & Persetujuan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-signature text-emerald-600"></i> Otorisasi & Audit
            </h2>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">Dibuat Oleh:</span>
                    <span class="font-semibold text-slate-800">{{ $purchaseOrder->creator->name ?? 'System' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Waktu Dibuat:</span>
                    <span class="font-medium text-slate-600">{{ $purchaseOrder->created_at->format('d M Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Disetujui Oleh:</span>
                    <span class="font-semibold text-slate-800">{{ $purchaseOrder->approver->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Waktu Persetujuan:</span>
                    <span class="font-medium text-slate-600">
                        {{ $purchaseOrder->approved_at ? $purchaseOrder->approved_at->format('d M Y H:i') : '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if($purchaseOrder->notes)
        <div class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600">
            <span class="font-bold text-slate-800">Catatan Pemesanan:</span> {{ $purchaseOrder->notes }}
        </div>
    @endif

    <!-- Tabel Rincian Item PO -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-table-list text-emerald-600"></i> Daftar Obat / Item Pesanan
            </h2>
            <span class="text-xs text-slate-500">{{ $purchaseOrder->items->count() }} item produk</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">#</th>
                        <th class="px-4 py-3 text-left font-semibold">Kode & Nama Produk</th>
                        <th class="px-4 py-3 text-left font-semibold">Satuan</th>
                        <th class="px-4 py-3 text-right font-semibold">Qty Order</th>
                        <th class="px-4 py-3 text-right font-semibold">Qty Diterima</th>
                        <th class="px-4 py-3 text-right font-semibold">Sisa (Outstanding)</th>
                        <th class="px-4 py-3 text-right font-semibold">Harga Beli</th>
                        <th class="px-4 py-3 text-right font-semibold">Diskon</th>
                        <th class="px-4 py-3 text-right font-semibold">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @foreach($purchaseOrder->items as $index => $item)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 text-slate-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-800">{{ $item->product->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $item->product->product_code ?? '' }}</div>
                                @if($item->notes)
                                    <div class="text-[11px] text-amber-600 italic mt-0.5">Catatan: {{ $item->notes }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $item->unit->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-800">{{ (float) $item->quantity_ordered }}</td>
                            <td class="px-4 py-3 text-right font-medium text-emerald-600">{{ (float) $item->quantity_received }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ $item->outstanding_quantity > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                {{ $item->outstanding_quantity }}
                            </td>
                            <td class="px-4 py-3 text-right text-slate-700">{{ format_rupiah($item->unit_price) }}</td>
                            <td class="px-4 py-3 text-right text-slate-500">{{ $item->discount > 0 ? format_rupiah($item->discount) : '-' }}</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900">{{ format_rupiah($item->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer Ringkasan Angka -->
        <div class="p-4 bg-slate-50/70 border-t border-slate-100 flex flex-col items-end">
            <div class="w-72 space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-slate-800">{{ format_rupiah($purchaseOrder->subtotal) }}</span>
                </div>
                @if($purchaseOrder->discount_amount > 0)
                    <div class="flex justify-between text-rose-600">
                        <span>Diskon Faktur:</span>
                        <span class="font-semibold">-{{ format_rupiah($purchaseOrder->discount_amount) }}</span>
                    </div>
                @endif
                @if($purchaseOrder->tax_amount > 0)
                    <div class="flex justify-between text-slate-600">
                        <span>PPN:</span>
                        <span class="font-semibold text-slate-800">+{{ format_rupiah($purchaseOrder->tax_amount) }}</span>
                    </div>
                @endif
                <div class="pt-2 border-t border-slate-200 flex justify-between text-sm font-bold text-slate-900">
                    <span>Grand Total:</span>
                    <span class="text-emerald-600">{{ format_rupiah($purchaseOrder->grand_total) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Penerimaan Barang (Goods Receipts) -->
    @if($purchaseOrder->goodsReceipts->count() > 0)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-dolly text-purple-600"></i> Riwayat Penerimaan Fisik Barang
                </h2>
                <span class="text-xs text-slate-500">{{ $purchaseOrder->goodsReceipts->count() }} penerimaan</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-semibold">No. Bukti Terima</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Tanggal Terima</th>
                            <th class="px-4 py-2.5 text-left font-semibold">No. Surat Jalan</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Penerima</th>
                            <th class="px-4 py-2.5 text-center font-semibold">Status</th>
                            <th class="px-4 py-2.5 text-center font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($purchaseOrder->goodsReceipts as $gr)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-2.5 font-semibold text-slate-900">
                                    <a href="{{ route('goods-receipts.show', $gr) }}" class="text-purple-600 hover:underline">
                                        {{ $gr->receipt_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $gr->receipt_date->format('d M Y') }}</td>
                                <td class="px-4 py-2.5 text-slate-700 font-medium">{{ $gr->delivery_note_number ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $gr->receiver->name ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ ucfirst($gr->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <a href="{{ route('goods-receipts.show', $gr) }}" class="text-slate-500 hover:text-slate-700">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
