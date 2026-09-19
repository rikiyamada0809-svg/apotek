<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900">{{ $saleReturn->return_number }}</h1>
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
                        'completed' => 'Selesai Diproses',
                        'cancelled' => 'Dibatalkan',
                    ];
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClasses[$saleReturn->status] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ $statusLabels[$saleReturn->status] ?? ucfirst($saleReturn->status) }}
                </span>
            </div>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('sale-returns.index') }}" class="hover:text-slate-900">Retur Penjualan</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Detail Retur</span>
            </nav>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('sale-returns.print-pdf', $saleReturn) }}" target="_blank"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition-colors">
                <i class="fa-solid fa-file-pdf text-rose-600"></i> Cetak Bukti Retur
            </a>

            @if(in_array($saleReturn->status, ['draft', 'pending']))
                <form method="POST" action="{{ route('sale-returns.approve', $saleReturn) }}"
                      onsubmit="return confirm('Apakah Anda yakin ingin menyetujui dokumen retur ini?')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                        <i class="fa-solid fa-check"></i> Setujui Retur
                    </button>
                </form>
            @endif

            @if(in_array($saleReturn->status, ['draft', 'approved']))
                <form method="POST" action="{{ route('sale-returns.complete', $saleReturn) }}"
                      onsubmit="return confirm('Selesaikan proses retur? Obat dengan kondisi Baik & Restok akan otomatis dimasukkan kembali ke stok apotek.')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                        <i class="fa-solid fa-check-double"></i> Selesaikan Retur & Restok
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <!-- Main Grid Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Return & Customer Info -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-slate-800 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-emerald-600"></i> Informasi Dokumen Retur Pasien
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-500 block">Pasien / Konsumen:</span>
                    <span class="text-sm font-bold text-slate-800">{{ $saleReturn->customer->name ?? ($saleReturn->originalSale->customer_name ?? 'Umum / Pembeli Langsung') }}</span>
                    <span class="text-slate-500 block mt-0.5">{{ $saleReturn->customer->phone ?? '' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Referensi Struk Penjualan Asli:</span>
                    @if($saleReturn->originalSale)
                        <a href="{{ route('pos.receipt', $saleReturn->originalSale) }}" class="text-sm font-bold font-mono text-blue-600 hover:underline">
                            {{ $saleReturn->originalSale->invoice_number }}
                        </a>
                        <span class="text-slate-500 block mt-0.5">Tgl Pembelian: {{ \Carbon\Carbon::parse($saleReturn->originalSale->sale_date)->translatedFormat('d M Y H:i') }}</span>
                    @else
                        <span class="text-sm font-medium text-slate-700">-</span>
                    @endif
                </div>
                <div>
                    <span class="text-slate-500 block">Tanggal Retur:</span>
                    <span class="text-sm font-semibold text-slate-800">{{ \Carbon\Carbon::parse($saleReturn->return_date)->translatedFormat('d F Y') }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Solusi Pengembalian:</span>
                    @if($saleReturn->resolution_type === 'replacement')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700">Tukar Obat Baru</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700">
                            Refund Dana ({{ strtoupper($saleReturn->refund_method ?? 'cash') }})
                        </span>
                    @endif
                </div>
                <div class="sm:col-span-2 bg-slate-50 p-3 rounded-lg">
                    <span class="text-slate-500 block font-medium">Alasan Pengembalian:</span>
                    <span class="text-slate-800 font-semibold mt-1 block">{{ $saleReturn->reason }}</span>
                    @if($saleReturn->notes)
                        <span class="text-slate-500 text-[11px] block mt-1 italic">Catatan: {{ $saleReturn->notes }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Audit Trail & Financial Summary -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-800 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-blue-600"></i> Riwayat Proses
                </h2>
                <div class="space-y-3 text-xs">
                    <div class="flex items-start gap-2.5">
                        <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 shrink-0 mt-0.5">
                            <i class="fa-solid fa-user text-[10px]"></i>
                        </div>
                        <div>
                            <p class="font-medium text-slate-700">Diproses oleh: <span class="font-bold text-slate-900">{{ $saleReturn->processor->name ?? 'Kasir' }}</span></p>
                            <p class="text-[11px] text-slate-400">{{ $saleReturn->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @if($saleReturn->approver)
                        <div class="flex items-start gap-2.5">
                            <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0 mt-0.5">
                                <i class="fa-solid fa-check text-[10px]"></i>
                            </div>
                            <div>
                                <p class="font-medium text-slate-700">Disetujui oleh: <span class="font-bold text-slate-900">{{ $saleReturn->approver->name }}</span></p>
                                <p class="text-[11px] text-slate-400">{{ $saleReturn->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 bg-slate-50 p-4 rounded-xl">
                <span class="text-xs text-slate-500 block">Total Pengembalian:</span>
                <span class="text-2xl font-bold text-rose-600 font-mono">Rp {{ number_format($saleReturn->refund_amount, 0, ',', '.') }}</span>
                <p class="text-[11px] text-slate-400 mt-1">Total {{ $saleReturn->items->count() }} item obat dikembalikan</p>
            </div>
        </div>
    </div>

    <!-- Items Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-800">Daftar Obat Yang Diretur Pasien</h3>
            <span class="text-xs text-slate-500 font-medium">{{ $saleReturn->items->count() }} Produk</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 text-slate-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="p-3 text-left w-12">#</th>
                        <th class="p-3 text-left">Nama Produk Obat</th>
                        <th class="p-3 text-left">No. Batch</th>
                        <th class="p-3 text-center">Jumlah Retur</th>
                        <th class="p-3 text-right">Harga Satuan</th>
                        <th class="p-3 text-center">Kondisi Obat</th>
                        <th class="p-3 text-center">Status Restok</th>
                        <th class="p-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($saleReturn->items as $idx => $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-3 text-slate-400 text-xs font-mono">{{ $idx + 1 }}</td>
                            <td class="p-3">
                                <span class="font-semibold text-slate-800">{{ $item->product->name ?? 'Produk Obat' }}</span>
                                <span class="text-xs text-slate-400 block font-mono">{{ $item->product->sku ?? '' }}</span>
                            </td>
                            <td class="p-3 text-xs font-mono text-slate-700">
                                {{ $item->batch->batch_number ?? '-' }}
                            </td>
                            <td class="p-3 text-center font-semibold text-slate-800">
                                {{ number_format($item->quantity, 0) }}
                            </td>
                            <td class="p-3 text-right font-mono text-slate-600">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center">
                                @if($item->item_condition === 'good')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">Baik / Utuh</span>
                                @elseif($item->item_condition === 'damaged')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-100 text-rose-800">Rusak / Cacat</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">Kadaluarsa</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                @if($item->return_to_stock)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600">
                                        <i class="fa-solid fa-check"></i> Restok Masuk
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-400">
                                        <i class="fa-solid fa-xmark"></i> Tidak Direstok
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 text-right font-bold font-mono text-slate-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                    <tr>
                        <td colspan="7" class="p-3 text-right text-slate-700">Total Pengembalian:</td>
                        <td class="p-3 text-right text-rose-600 font-mono text-base">
                            Rp {{ number_format($saleReturn->refund_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
