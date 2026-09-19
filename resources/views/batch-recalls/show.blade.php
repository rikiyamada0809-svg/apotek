<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 font-mono">{{ $batchRecall->recall_number }}</h1>
                @if($batchRecall->status === 'quarantined')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        <i class="fa-solid fa-lock text-[10px]"></i> Dikarantina
                    </span>
                @elseif($batchRecall->status === 'in_progress')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                        <i class="fa-solid fa-clock-rotate-left text-[10px]"></i> Proses Penarikan
                    </span>
                @elseif($batchRecall->status === 'returned_to_supplier')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                        <i class="fa-solid fa-truck-ramp-box text-[10px]"></i> Retur ke Supplier
                    </span>
                @elseif($batchRecall->status === 'destroyed')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                        <i class="fa-solid fa-fire text-[10px]"></i> Dimusnahkan
                    </span>
                @elseif($batchRecall->status === 'completed')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        <i class="fa-solid fa-circle-check text-[10px]"></i> Selesai
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                        {{ ucfirst($batchRecall->status) }}
                    </span>
                @endif
            </div>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('batch-recalls.index') }}" class="hover:text-slate-900">Batch Recall</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">{{ $batchRecall->recall_number }}</span>
            </nav>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('batch-recalls.index') }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
            </a>

            <a href="{{ route('batch-recalls.pdf', $batchRecall) }}" target="_blank" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-rose-600 text-xs font-semibold rounded-lg shadow-sm transition-colors">
                <i class="fa-solid fa-file-pdf mr-1"></i> Berita Acara (PDF)
            </a>

            <button type="button" onclick="document.getElementById('updateModal').classList.remove('hidden')" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                <i class="fa-solid fa-pen-to-square mr-1"></i> Update Progres Penarikan
            </button>
        </div>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <!-- 4 KPI Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Total Obat Terdampak</p>
            <p class="text-2xl font-bold text-slate-900">{{ number_format($batchRecall->affected_qty, 0) }} <span class="text-xs font-normal text-slate-400">Unit</span></p>
            <p class="text-[11px] text-slate-400 mt-1">Stok fisik + obat terjual</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Dikarantina di Gudang</p>
            <p class="text-2xl font-bold text-amber-600">{{ number_format($currentStock, 0) }} <span class="text-xs font-normal text-slate-400">Unit</span></p>
            <p class="text-[11px] text-amber-500 mt-1">Dibekukan dari POS</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Obat Terjual ke Pasien</p>
            <p class="text-2xl font-bold text-rose-600">{{ number_format($batchRecall->sold_qty, 0) }} <span class="text-xs font-normal text-slate-400">Unit</span></p>
            <p class="text-[11px] text-rose-400 mt-1">{{ count($affectedSaleItems) }} transaksi penjualan</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-center mb-1">
                <p class="text-xs text-slate-500 font-medium">Berhasil Ditarik</p>
                <span class="text-xs font-bold text-emerald-600 font-mono">{{ $batchRecall->recovery_percentage }}%</span>
            </div>
            <p class="text-2xl font-bold text-emerald-600">{{ number_format($batchRecall->recovered_qty, 0) }} <span class="text-xs font-normal text-slate-400">Unit</span></p>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
                <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ $batchRecall->recovery_percentage }}%"></div>
            </div>
        </div>
    </div>

    <!-- Recall Information & Batch Details -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Informasi Batch -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Identitas Obat & Batch</h2>
            <div>
                <div class="text-[10px] text-slate-400 uppercase font-bold">Nama Produk Obat</div>
                <div class="text-base font-bold text-slate-900">{{ $batchRecall->batch?->product?->name }}</div>
                <div class="text-xs font-mono text-slate-500">{{ $batchRecall->batch?->product?->product_code }}</div>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-xs">
                <div>
                    <span class="text-slate-400">No. Batch:</span>
                    <div class="font-mono font-bold text-rose-700">{{ $batchRecall->batch?->batch_number }}</div>
                </div>
                <div>
                    <span class="text-slate-400">Expired:</span>
                    <div class="font-semibold text-slate-800">{{ $batchRecall->batch?->expiry_date?->format('d/m/Y') }}</div>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100 text-xs">
                <span class="text-slate-400">Supplier / Produsen:</span>
                <div class="font-semibold text-slate-800">{{ $batchRecall->batch?->supplier?->name ?? '-' }}</div>
            </div>
        </div>

        <!-- Informasi Recall & Alasan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 md:col-span-2 space-y-3">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Instruksi & Alasan Penarikan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                    <span class="text-slate-400">Sumber Instruksi:</span>
                    <div class="font-bold text-slate-900">{{ $batchRecall->recall_source }}</div>
                </div>
                <div>
                    <span class="text-slate-400">Tanggal Instruksi:</span>
                    <div class="font-bold text-slate-900">{{ $batchRecall->recall_date?->format('d F Y') }}</div>
                </div>
                <div class="sm:col-span-2">
                    <span class="text-slate-400">Alasan Recall:</span>
                    <div class="font-semibold text-slate-800 bg-rose-50/60 p-2.5 rounded-lg border border-rose-100 mt-1">
                        {{ $batchRecall->recall_reason }}
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <span class="text-slate-400">Tindakan yang Diambil:</span>
                    <div class="text-slate-700 mt-0.5">{{ $batchRecall->action_taken ?? '-' }}</div>
                </div>
                @if($batchRecall->notes)
                    <div class="sm:col-span-2">
                        <span class="text-slate-400">Catatan:</span>
                        <div class="text-slate-600 mt-0.5 italic">{{ $batchRecall->notes }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Section: Pelanggan yang Perlu Dihubungi -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-rose-50/30">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-address-book text-rose-600"></i> Daftar Pasien / Pelanggan yang Perlu Dihubungi
                </h2>
                <p class="text-xs text-slate-500">Hubungi pasien untuk meminta pengembalian obat dari batch yang bermasalah.</p>
            </div>
            <span class="px-3 py-1 text-xs font-bold bg-white text-rose-700 border border-rose-200 rounded-full shadow-sm">
                {{ count($customerList) }} Pasien Teridentifikasi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Pasien / Pelanggan</th>
                        <th class="py-3 px-4">No. Telepon / WhatsApp</th>
                        <th class="py-3 px-4">Alamat</th>
                        <th class="py-3 px-4 text-center">Jumlah Beli</th>
                        <th class="py-3 px-4">Riwayat Faktur Penjualan</th>
                        <th class="py-3 px-4 text-center">Aksi Hubungi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customerList as $custData)
                        @php
                            $c = $custData['customer'];
                            $cleanPhone = preg_replace('/[^0-9]/', '', $c->phone ?? '');
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }
                            $waMessage = rawurlencode("Halo {$c->name}, kami dari Apotek Sehat Sentosa menginformasikan penarikan obat batch {$batchRecall->batch?->batch_number} ({$batchRecall->batch?->product?->name}). Mohon kesediaan Anda untuk mengembalikan obat tersebut ke apotek kami untuk proses penggantian.");
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 font-semibold text-slate-900">
                                {{ $c->name }}
                            </td>
                            <td class="py-3 px-4 font-mono text-xs text-slate-700">
                                {{ $c->phone ?: '-' }}
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-500 max-w-xs truncate">
                                {{ $c->address ?: '-' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded font-bold text-xs bg-rose-100 text-rose-800 font-mono">
                                    {{ number_format($custData['total_qty'], 0) }} Unit
                                </span>
                            </td>
                            <td class="py-3 px-4 text-xs">
                                @foreach($custData['invoices'] as $inv)
                                    <div class="font-mono text-[11px] text-slate-600">
                                        {{ $inv['invoice_number'] }} ({{ $inv['qty'] }} unit)
                                    </div>
                                @endforeach
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($c->phone)
                                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMessage }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors">
                                        <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs italic">Tanpa No. HP</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500 text-xs">
                                Tidak ada pelanggan terdaftar dalam sistem untuk transaksi batch ini.
                            </td>
                        </tr>
                    @endforelse

                    @if($unregisteredSoldQty > 0)
                        <tr class="bg-amber-50/50">
                            <td class="py-3 px-4 font-semibold text-amber-800 italic" colspan="3">
                                <i class="fa-solid fa-users text-amber-600 mr-1"></i> Penjualan Pasien Umum / Non-Member (Tanpa Data Kontak)
                            </td>
                            <td class="py-3 px-4 text-center font-bold font-mono text-amber-800 text-xs">
                                {{ number_format($unregisteredSoldQty, 0) }} Unit
                            </td>
                            <td class="py-3 px-4 text-xs text-amber-700 italic" colspan="2">
                                Pasien langsung datang ke apotek / penjualan OTC umum.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section: Riwayat Transaksi Penjualan Terdampak -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-receipt text-slate-600"></i> Riwayat Seluruh Transaksi Penjualan Batch Ini ({{ count($affectedSaleItems) }} Transaksi)
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. Faktur POS</th>
                        <th class="py-3 px-4">Tanggal & Waktu</th>
                        <th class="py-3 px-4">Kasir</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4 text-center">Jumlah Beli</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($affectedSaleItems as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 font-mono font-semibold text-slate-900">
                                {{ $item->sale?->invoice_number ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-600">
                                {{ $item->sale?->sale_date?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-600">
                                {{ $item->sale?->cashier?->name ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-xs font-medium text-slate-800">
                                {{ $item->sale?->customer?->name ?? 'Pasien Umum / Non-Member' }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-xs font-mono text-slate-900">
                                {{ number_format($item->quantity, 0) }} {{ $item->unit?->name ?? 'Unit' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-xs text-slate-800">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500 text-xs">
                                Belum ada obat dari batch ini yang terjual melalui kasir.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Update Progres Recall -->
    <div id="updateModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full p-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-packing text-rose-600"></i> Update Progres Penarikan Obat
                </h3>
                <button type="button" onclick="document.getElementById('updateModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('batch-recalls.update-progress', $batchRecall) }}">
                @csrf
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Obat yang Berhasil Ditarik (Recovered Qty) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="recovered_qty" value="{{ $batchRecall->recovered_qty }}" required
                               class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-rose-500 focus:border-rose-500 font-bold">
                        <span class="text-[11px] text-slate-400">Total obat terjual: {{ number_format($batchRecall->sold_qty, 0) }} Unit</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status Penarikan <span class="text-rose-500">*</span></label>
                        <select name="status" required class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-rose-500 focus:border-rose-500">
                            <option value="quarantined" {{ $batchRecall->status === 'quarantined' ? 'selected' : '' }}>Dikarantina (Quarantined)</option>
                            <option value="in_progress" {{ $batchRecall->status === 'in_progress' ? 'selected' : '' }}>Proses Penarikan Berjalan (In Progress)</option>
                            <option value="returned_to_supplier" {{ $batchRecall->status === 'returned_to_supplier' ? 'selected' : '' }}>Dikembalikan ke Supplier (Returned)</option>
                            <option value="destroyed" {{ $batchRecall->status === 'destroyed' ? 'selected' : '' }}>Dimusnahkan (Destroyed)</option>
                            <option value="completed" {{ $batchRecall->status === 'completed' ? 'selected' : '' }}>Selesai Seluruhnya (Completed)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tindakan Lanjutan / Penanganan Fisik</label>
                        <textarea name="action_taken" rows="2" placeholder="Contoh: Seluruh stok fisik dan 12 botol dari pasien telah diserahkan ke distributor..."
                                  class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-rose-500 focus:border-rose-500">{{ $batchRecall->action_taken }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-rose-500 focus:border-rose-500">{{ $batchRecall->notes }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('updateModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-lg shadow-md transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i> Simpan Progres
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
