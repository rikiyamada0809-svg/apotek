<x-app-layout>
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">{{ $drugDestruction->destruction_number }}</h1>
                    @php
                        $statusBadge = match($drugDestruction->status) {
                            'draft' => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => 'Draft', 'icon' => 'fa-file-lines'],
                            'approved' => ['bg' => 'bg-blue-100 text-blue-700 border-blue-200', 'label' => 'Disetujui', 'icon' => 'fa-thumbs-up'],
                            'completed' => ['bg' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'label' => 'Selesai / Dimusnahkan', 'icon' => 'fa-circle-check'],
                            'cancelled' => ['bg' => 'bg-red-100 text-red-700 border-red-200', 'label' => 'Dibatalkan', 'icon' => 'fa-ban'],
                            default => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => ucfirst($drugDestruction->status), 'icon' => 'fa-circle-info']
                        };
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $statusBadge['bg'] }}">
                        <i class="fa-solid {{ $statusBadge['icon'] }}"></i>
                        {{ $statusBadge['label'] }}
                    </span>
                </div>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('drug-destructions.index') }}" class="hover:text-slate-900">Pemusnahan Obat</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">{{ $drugDestruction->destruction_number }}</span>
                </nav>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('drug-destructions.index') }}" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
                </a>

                <a href="{{ route('drug-destructions.pdf', $drugDestruction) }}" target="_blank" class="px-3.5 py-2 text-sm font-semibold text-slate-700 hover:text-slate-900 bg-white border border-slate-300 rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-file-pdf text-red-600"></i> Cetak Berita Acara (PDF)
                </a>

                @if(in_array($drugDestruction->status, ['draft', 'pending']))
                    <form method="POST" action="{{ route('drug-destructions.approve', $drugDestruction) }}" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui berita acara pemusnahan ini?');">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-check"></i> Setujui Berita Acara
                        </button>
                    </form>
                @endif

                @if($drugDestruction->status === 'approved')
                    <form method="POST" action="{{ route('drug-destructions.complete', $drugDestruction) }}" onsubmit="return confirm('PERHATIAN: Eksekusi pemusnahan akan otomatis memotong stok obat dan mencatat mutasi pemusnahan (disposal). Lanjutkan?');">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-fire-burner"></i> Eksekusi & Potong Stok
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-red-600 text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Card Informasi Utama -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="md:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i class="fa-solid fa-circle-info text-blue-500"></i> Detail Berita Acara Pemusnahan
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 uppercase">Tanggal Pelaksanaan</span>
                        <span class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($drugDestruction->destruction_date)->translatedFormat('d F Y') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 uppercase">Metode Pemusnahan</span>
                        <span class="font-medium text-slate-900">{{ $drugDestruction->method }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 uppercase">Total Kerugian</span>
                        <span class="font-bold text-red-600 text-base">Rp {{ number_format($drugDestruction->total_loss_amount, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 uppercase">Nama Saksi</span>
                        <span class="font-medium text-slate-900">{{ $drugDestruction->witness_name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 uppercase">Jabatan Saksi</span>
                        <span class="font-medium text-slate-900">{{ $drugDestruction->witness_role }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-500 uppercase">Penanggung Jawab</span>
                        <span class="font-medium text-slate-900">{{ $drugDestruction->executor?->name ?? '-' }}</span>
                    </div>
                </div>

                @if($drugDestruction->notes)
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <span class="block text-xs font-semibold text-slate-500 uppercase mb-1">Catatan / Keterangan</span>
                        <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-lg">{{ $drugDestruction->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Upload / Preview Dokumen Fisik -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                        <i class="fa-solid fa-paperclip text-emerald-500"></i> Lampiran Berita Acara
                    </h2>
                    @if($drugDestruction->document_path)
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl mb-4 text-center">
                            <i class="fa-solid fa-file-check text-3xl text-emerald-600 mb-2"></i>
                            <div class="text-xs font-semibold text-emerald-900">Dokumen Fisik Terlampir</div>
                            <div class="text-xs text-slate-500 mb-3">Telah diunggah scan bertandatangan</div>
                            <a href="{{ Storage::url($drugDestruction->document_path) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition">
                                <i class="fa-solid fa-eye"></i> Buka / Unduh Dokumen
                            </a>
                        </div>
                    @else
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl mb-4 text-center">
                            <i class="fa-solid fa-file-excel text-3xl text-amber-500 mb-2"></i>
                            <div class="text-xs font-semibold text-amber-900">Belum Ada Lampiran</div>
                            <div class="text-xs text-amber-700">Unggah foto atau scan dokumen berita acara fisik yang telah ditandatangani saksi.</div>
                        </div>
                    @endif
                </div>

                <!-- Form Upload Dokumen -->
                <form method="POST" action="{{ route('drug-destructions.upload-document', $drugDestruction) }}" enctype="multipart/form-data" class="pt-2">
                    @csrf
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        {{ $drugDestruction->document_path ? 'Ganti Dokumen' : 'Unggah Scan / Foto Bertandatangan' }}
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required
                               class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-lg transition shrink-0">
                            Unggah
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Item Pemusnahan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-red-500"></i>
                    <h3 class="font-bold text-slate-800">Daftar Obat & Rincian Kerugian</h3>
                </div>
                <span class="text-xs font-semibold text-slate-500">Total {{ $drugDestruction->items->count() }} item</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-6">Nama Obat & Kategori</th>
                            <th class="py-3.5 px-4 text-center">No. Batch</th>
                            <th class="py-3.5 px-4 text-center">Tgl Kadaluarsa</th>
                            <th class="py-3.5 px-4 text-right">Jumlah Musnah</th>
                            <th class="py-3.5 px-4 text-right">Harga Beli Satuan</th>
                            <th class="py-3.5 px-6 text-right">Nilai Kerugian</th>
                            <th class="py-3.5 px-6">Alasan Pemusnahan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($drugDestruction->items as $item)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 px-6">
                                    <div class="font-semibold text-slate-900">{{ $item->product?->name ?? 'Produk Dihapus' }}</div>
                                    <div class="text-xs text-slate-400">{{ $item->product?->category?->name ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-xs">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold">{{ $item->batch?->batch_number ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-4 text-center text-xs">
                                    <span class="{{ $item->batch && \Carbon\Carbon::parse($item->batch->expiry_date)->isPast() ? 'text-red-600 font-semibold' : 'text-slate-600' }}">
                                        {{ $item->batch?->expiry_date ? \Carbon\Carbon::parse($item->batch->expiry_date)->format('d/m/Y') : '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-800">
                                    {{ number_format($item->quantity, 0, ',', '.') }} {{ $item->product?->productUnits->first()?->unit?->name ?? 'Unit' }}
                                </td>
                                <td class="py-3 px-4 text-right text-xs text-slate-600">
                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-6 text-right font-bold text-red-600 text-xs">
                                    Rp {{ number_format($item->loss_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-6 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700 border border-red-100">
                                        {{ $item->reason }}
                                    </span>
                                    @if($item->notes)
                                        <div class="text-slate-400 text-xs mt-0.5">{{ $item->notes }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-slate-200 bg-slate-50 font-bold text-slate-800 text-xs">
                        <tr>
                            <td colspan="5" class="py-3.5 px-6 text-right">TOTAL NILAI KERUGIAN:</td>
                            <td class="py-3.5 px-6 text-right text-sm text-red-600 font-bold">
                                Rp {{ number_format($drugDestruction->total_loss_amount, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
