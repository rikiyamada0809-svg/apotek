<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900">{{ $purchaseReturn->return_number }}</h1>
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
                        'sent' => 'Dikirim ke Supplier',
                        'completed' => 'Selesai / Resolved',
                        'cancelled' => 'Dibatalkan',
                    ];
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClasses[$purchaseReturn->status] ?? 'bg-slate-100 text-slate-700' }}">
                    {{ $statusLabels[$purchaseReturn->status] ?? ucfirst($purchaseReturn->status) }}
                </span>
            </div>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('purchase-returns.index') }}" class="hover:text-slate-900">Retur Pembelian</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Detail Retur</span>
            </nav>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('purchase-returns.print-pdf', $purchaseReturn) }}" target="_blank"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition-colors">
                <i class="fa-solid fa-file-pdf text-rose-600"></i> Cetak Surat Jalan Retur
            </a>

            @if(in_array($purchaseReturn->status, ['draft', 'pending']))
                <form method="POST" action="{{ route('purchase-returns.approve', $purchaseReturn) }}"
                      onsubmit="return confirm('Apakah Anda yakin ingin menyetujui dokumen retur ini?')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                        <i class="fa-solid fa-check"></i> Setujui Retur
                    </button>
                </form>
            @endif

            @if($purchaseReturn->status === 'approved')
                <form method="POST" action="{{ route('purchase-returns.send', $purchaseReturn) }}"
                      onsubmit="return confirm('Kirim barang retur ke supplier? Stok gudang dan batch obat akan otomatis DIKURANGI.')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Barang (Kurangi Stok)
                    </button>
                </form>
            @endif

            @if(in_array($purchaseReturn->status, ['approved', 'sent']))
                <form method="POST" action="{{ route('purchase-returns.resolve', $purchaseReturn) }}"
                      onsubmit="return confirm('Tandai retur telah selesai / diselesaikan oleh supplier?')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                        <i class="fa-solid fa-check-double"></i> Selesaikan Retur (Resolved)
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <!-- Main Grid Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Return & Supplier Info -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-slate-800 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-emerald-600"></i> Informasi Dokumen Retur
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-500 block">Supplier Tujuan:</span>
                    <span class="text-sm font-bold text-slate-800">{{ $purchaseReturn->supplier->name ?? '-' }}</span>
                    <span class="text-slate-500 block mt-0.5">{{ $purchaseReturn->supplier->phone ?? '' }}</span>
                    <span class="text-slate-500 block">{{ $purchaseReturn->supplier->address ?? '' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Referensi Purchase Order:</span>
                    @if($purchaseReturn->purchaseOrder)
                        <a href="{{ route('purchase-orders.show', $purchaseReturn->purchaseOrder) }}" class="text-sm font-bold text-blue-600 hover:underline">
                            {{ $purchaseReturn->purchaseOrder->po_number }}
                        </a>
                        <span class="text-slate-500 block mt-0.5">Gudang: {{ $purchaseReturn->purchaseOrder->warehouse->name ?? '-' }}</span>
                    @else
                        <span class="text-sm font-medium text-slate-700">-</span>
                    @endif
                </div>
                <div>
                    <span class="text-slate-500 block">Tanggal Retur:</span>
                    <span class="text-sm font-semibold text-slate-800">{{ \Carbon\Carbon::parse($purchaseReturn->return_date)->translatedFormat('d F Y') }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Solusi / Kompensasi:</span>
                    @if($purchaseReturn->resolution_type === 'replacement')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700">Penggantian Barang Baru</span>
                    @elseif($purchaseReturn->resolution_type === 'credit_note')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-purple-50 text-purple-700">Nota Kredit / Potong Faktur</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-700">Pengembalian Dana Kas</span>
                    @endif
                </div>
                <div class="sm:col-span-2 bg-slate-50 p-3 rounded-lg">
                    <span class="text-slate-500 block font-medium">Alasan Retur:</span>
                    <span class="text-slate-800 font-semibold mt-1 block">{{ $purchaseReturn->reason }}</span>
                    @if($purchaseReturn->notes)
                        <span class="text-slate-500 text-[11px] block mt-1 italic">Catatan: {{ $purchaseReturn->notes }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Audit Trail & Financial Summary -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-800 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-blue-600"></i> Riwayat Persetujuan
                </h2>
                <div class="space-y-3 text-xs">
                    <div class="flex items-start gap-2.5">
                        <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 shrink-0 mt-0.5">
                            <i class="fa-solid fa-user text-[10px]"></i>
                        </div>
                        <div>
                            <p class="font-medium text-slate-700">Dibuat oleh: <span class="font-bold text-slate-900">{{ $purchaseReturn->creator->name ?? 'Sistem' }}</span></p>
                            <p class="text-[11px] text-slate-400">{{ $purchaseReturn->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @if($purchaseReturn->approver)
                        <div class="flex items-start gap-2.5">
                            <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0 mt-0.5">
                                <i class="fa-solid fa-check text-[10px]"></i>
                            </div>
                            <div>
                                <p class="font-medium text-slate-700">Disetujui oleh: <span class="font-bold text-slate-900">{{ $purchaseReturn->approver->name }}</span></p>
                                <p class="text-[11px] text-slate-400">{{ $purchaseReturn->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 bg-slate-50 p-4 rounded-xl">
                <span class="text-xs text-slate-500 block">Total Nilai Retur:</span>
                <span class="text-2xl font-bold text-slate-900 font-mono">Rp {{ number_format($purchaseReturn->total_amount, 0, ',', '.') }}</span>
                <p class="text-[11px] text-slate-400 mt-1">Total {{ $purchaseReturn->items->count() }} item barang diretur</p>
            </div>
        </div>
    </div>

    <!-- Items Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-800">Daftar Obat Yang Diretur</h3>
            <span class="text-xs text-slate-500 font-medium">{{ $purchaseReturn->items->count() }} Produk</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 text-slate-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="p-3 text-left w-12">#</th>
                        <th class="p-3 text-left">Nama Produk Obat</th>
                        <th class="p-3 text-left">No. Batch / Exp Date</th>
                        <th class="p-3 text-center">Jumlah Retur</th>
                        <th class="p-3 text-right">Harga Satuan</th>
                        <th class="p-3 text-right">Subtotal</th>
                        <th class="p-3 text-left">Alasan Item</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($purchaseReturn->items as $idx => $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-3 text-slate-400 text-xs font-mono">{{ $idx + 1 }}</td>
                            <td class="p-3">
                                <span class="font-semibold text-slate-800">{{ $item->product->name ?? 'Produk Obat' }}</span>
                                <span class="text-xs text-slate-400 block font-mono">{{ $item->product->sku ?? '' }}</span>
                            </td>
                            <td class="p-3 text-xs">
                                @if($item->batch)
                                    <span class="font-mono font-medium text-slate-800">{{ $item->batch->batch_number }}</span>
                                    <span class="text-slate-400 block">ED: {{ \Carbon\Carbon::parse($item->batch->expiry_date)->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="p-3 text-center font-semibold text-slate-800">
                                {{ number_format($item->quantity, 0) }}
                            </td>
                            <td class="p-3 text-right font-mono text-slate-600">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-right font-bold font-mono text-slate-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-xs text-slate-600">
                                {{ $item->reason ?? '-' }}
                                @if($item->notes)
                                    <span class="block text-[11px] text-slate-400 italic">{{ $item->notes }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                    <tr>
                        <td colspan="5" class="p-3 text-right text-slate-700">Total Keseluruhan Retur:</td>
                        <td class="p-3 text-right text-emerald-600 font-mono text-base">
                            Rp {{ number_format($purchaseReturn->total_amount, 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
