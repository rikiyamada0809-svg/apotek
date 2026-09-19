<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 font-mono">{{ $stockTransfer->transfer_number }}</h1>
                @if($stockTransfer->status === 'draft')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        <i class="fa-solid fa-file-pen text-[10px]"></i> Draft Permintaan
                    </span>
                @elseif($stockTransfer->status === 'approved')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                        <i class="fa-solid fa-truck-fast text-[10px]"></i> Disetujui (Dalam Perjalanan)
                    </span>
                @elseif($stockTransfer->status === 'received')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        <i class="fa-solid fa-circle-check text-[10px]"></i> Selesai Diterima
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                        Dibatalkan
                    </span>
                @endif
            </div>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('stock-transfers.index') }}" class="hover:text-slate-900">Transfer Stok</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">{{ $stockTransfer->transfer_number }}</span>
            </nav>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('stock-transfers.index') }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
            </a>

            <!-- Print Surat Jalan PDF -->
            <a href="{{ route('stock-transfers.pdf', $stockTransfer) }}" target="_blank" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-rose-600 text-xs font-semibold rounded-lg shadow-sm transition-colors">
                <i class="fa-solid fa-file-pdf mr-1"></i> Surat Jalan (PDF)
            </a>

            <!-- Actions based on status -->
            @if($stockTransfer->status === 'draft')
                <form method="POST" action="{{ route('stock-transfers.approve', $stockTransfer) }}" class="inline" onsubmit="return confirm('Setujui transfer stok ini? Barang akan disiapkan untuk pengiriman.')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                        <i class="fa-solid fa-check mr-1"></i> Setujui Transfer
                    </button>
                </form>

                <form method="POST" action="{{ route('stock-transfers.cancel', $stockTransfer) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transfer stok ini?')">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-lg transition-colors">
                        <i class="fa-solid fa-xmark mr-1"></i> Batalkan
                    </button>
                </form>
            @elseif($stockTransfer->status === 'approved')
                <button type="button" onclick="document.getElementById('receiveModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                    <i class="fa-solid fa-dolly mr-1"></i> Terima Barang di Gudang Tujuan
                </button>

                <form method="POST" action="{{ route('stock-transfers.cancel', $stockTransfer) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transfer stok ini?')">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-lg transition-colors">
                        <i class="fa-solid fa-xmark mr-1"></i> Batalkan
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <!-- Transfer Info Header -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Rute Transfer Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 md:col-span-2">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Rute Pengiriman Transfer</h2>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 bg-slate-50 rounded-xl border border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 text-amber-700 rounded-lg flex items-center justify-center text-lg">
                        <i class="fa-solid fa-warehouse"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Gudang Asal (Pengirim)</div>
                        <div class="text-base font-bold text-slate-900">{{ $stockTransfer->fromWarehouse?->name }}</div>
                        <div class="text-xs text-slate-500 font-mono">{{ $stockTransfer->fromWarehouse?->code }}</div>
                    </div>
                </div>

                <div class="text-slate-400 text-xl hidden sm:block">
                    <i class="fa-solid fa-arrow-right-long text-emerald-500"></i>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-100 text-emerald-700 rounded-lg flex items-center justify-center text-lg">
                        <i class="fa-solid fa-warehouse"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Gudang Tujuan (Penerima)</div>
                        <div class="text-base font-bold text-slate-900">{{ $stockTransfer->toWarehouse?->name }}</div>
                        <div class="text-xs text-slate-500 font-mono">{{ $stockTransfer->toWarehouse?->code }}</div>
                    </div>
                </div>
            </div>

            @if($stockTransfer->notes)
                <div class="mt-4 text-xs text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-100">
                    <span class="font-bold text-slate-700">Catatan:</span> {{ $stockTransfer->notes }}
                </div>
            @endif
        </div>

        <!-- Audit Trail Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Riwayat & Logistik</h2>
            
            <div class="flex justify-between items-center text-xs pb-2 border-b border-slate-100">
                <span class="text-slate-500">Tanggal Transfer:</span>
                <span class="font-semibold text-slate-800">{{ $stockTransfer->transfer_date?->format('d/m/Y') }}</span>
            </div>

            <div class="flex justify-between items-center text-xs pb-2 border-b border-slate-100">
                <span class="text-slate-500">Dibuat Oleh:</span>
                <span class="font-semibold text-slate-800">{{ $stockTransfer->creator?->name ?? '-' }} ({{ $stockTransfer->created_at->format('d/m/Y H:i') }})</span>
            </div>

            <div class="flex justify-between items-center text-xs pb-2 border-b border-slate-100">
                <span class="text-slate-500">Disetujui Oleh:</span>
                <span class="font-semibold text-slate-800">
                    {{ $stockTransfer->approver?->name ?? 'Belum disetujui' }}
                </span>
            </div>

            <div class="flex justify-between items-center text-xs">
                <span class="text-slate-500">Diterima Oleh:</span>
                <span class="font-semibold text-slate-800">
                    @if($stockTransfer->receiver)
                        {{ $stockTransfer->receiver->name }} ({{ $stockTransfer->received_at?->format('d/m/Y H:i') }})
                    @else
                        <span class="text-slate-400">Belum diterima</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Transferred Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-pills text-emerald-600"></i> Rincian Barang Transfer ({{ $stockTransfer->items->count() }} Item)
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Kode & Nama Obat</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Batch & Exp Date</th>
                        <th class="py-3 px-4 text-center">Kuantitas Dikirim</th>
                        <th class="py-3 px-4 text-center">Kuantitas Diterima</th>
                        <th class="py-3 px-4">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($stockTransfer->items as $index => $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 text-center text-xs font-bold text-slate-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-900">{{ $item->product?->name }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $item->product?->product_code }}</div>
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-600">
                                {{ $item->product?->category?->name ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-xs">
                                <div class="font-mono font-semibold text-slate-800">{{ $item->batch?->batch_number ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500">Exp: {{ $item->batch?->expiry_date?->format('d/m/Y') ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded font-bold text-slate-900 bg-slate-100 text-xs">
                                    {{ number_format($item->quantity, 0) }} {{ $item->product?->baseUnit()?->unit?->name ?? 'Pcs' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($stockTransfer->status === 'received')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded font-bold text-emerald-800 bg-emerald-100 text-xs">
                                        {{ number_format($item->received_qty, 0) }} {{ $item->product?->baseUnit()?->unit?->name ?? 'Pcs' }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs italic">Menunggu Penerimaan</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-500">
                                {{ $item->notes ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Receive Modal Form -->
    <div id="receiveModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-check text-emerald-600"></i> Konfirmasi Penerimaan Transfer Stok
                </h3>
                <button type="button" onclick="document.getElementById('receiveModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <p class="text-xs text-slate-500 mb-4">
                Pastikan fisik barang sesuai dengan surat jalan. Menekan tombol konfirmasi akan otomatis memotong stok dari <strong>{{ $stockTransfer->fromWarehouse?->name }}</strong> dan menambahkan stok ke <strong>{{ $stockTransfer->toWarehouse?->name }}</strong> serta mencatat mutasi stok.
            </p>

            <form method="POST" action="{{ route('stock-transfers.receive', $stockTransfer) }}">
                @csrf
                <div class="space-y-3 mb-6">
                    @foreach($stockTransfer->items as $item)
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between gap-4">
                            <div>
                                <div class="font-semibold text-xs text-slate-900">{{ $item->product?->name }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">Batch: {{ $item->batch?->batch_number }} | Dikirim: {{ number_format($item->quantity, 0) }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-xs font-medium text-slate-600">Qty Diterima:</label>
                                <input type="number" step="0.01" min="0" name="received_qty[{{ $item->id }}]"
                                       value="{{ $item->quantity }}" required
                                       class="w-24 px-2 py-1 text-center font-bold text-slate-900 border border-slate-300 rounded focus:ring-1 focus:ring-emerald-500 text-xs">
                                <span class="text-xs text-slate-500">{{ $item->product?->baseUnit()?->unit?->name ?? 'Pcs' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('receiveModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-md transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i> Konfirmasi & Perbarui Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
