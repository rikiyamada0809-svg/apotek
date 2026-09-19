<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Inisiasi Penarikan Obat (Batch Recall)</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('batch-recalls.index') }}" class="hover:text-slate-900">Batch Recall</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Inisiasi Recall</span>
            </nav>
        </div>
        <a href="{{ route('batch-recalls.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif
    @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
            <div class="font-bold mb-1">Terdapat kesalahan input:</div>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 p-4 bg-rose-50/80 border border-rose-200 rounded-2xl flex items-start gap-4">
        <div class="w-10 h-10 bg-rose-100 text-rose-700 rounded-xl flex items-center justify-center text-lg shrink-0 mt-0.5">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-rose-900 mb-0.5">Perhatian: Prosedur Karantina Otomatis</h3>
            <p class="text-xs text-rose-700 leading-relaxed">
                Menginisiasi recall akan <strong>otomatis mengubah status batch menjadi 'recalled'</strong> dan <strong>membekukan stok fisik (stok tersedia = 0)</strong> di seluruh gudang/outlet. Obat dari batch ini tidak akan dapat dijual melalui Point of Sale (POS) maupun ditransfer antar gudang.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('batch-recalls.store') }}">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Left: Form Input -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Card 1: Batch Selection -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <h2 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-pills text-rose-600"></i> Pilih Batch Obat yang Ditarik
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Batch & Produk Obat <span class="text-rose-500">*</span></label>
                            <select name="batch_id" id="batchSelect" required
                                    class="w-full px-3 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-1 focus:ring-rose-500 focus:border-rose-500">
                                <option value="">-- Pilih Batch Obat --</option>
                                @foreach($batches as $b)
                                    <option value="{{ $b->id }}"
                                            data-product="{{ $b->product?->name }}"
                                            data-code="{{ $b->product?->product_code }}"
                                            data-batch="{{ $b->batch_number }}"
                                            data-exp="{{ $b->expiry_date?->format('d/m/Y') }}"
                                            data-stock="{{ $b->stocks->sum('quantity') }}"
                                            data-supplier="{{ $b->supplier?->name ?? 'Tidak ada supplier' }}"
                                            {{ (old('batch_id') == $b->id || $selectedBatchId == $b->id) ? 'selected' : '' }}>
                                        {{ $b->product?->name }} — No. Batch: {{ $b->batch_number }} (Stok: {{ $b->stocks->sum('quantity') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Instruksi Recall -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <h2 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-file-circle-exclamation text-rose-600"></i> Informasi & Alasan Penarikan
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Sumber Instruksi Recall <span class="text-rose-500">*</span></label>
                            <select name="recall_source" required
                                    class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-rose-500 focus:border-rose-500">
                                <option value="BPOM" {{ old('recall_source') === 'BPOM' ? 'selected' : '' }}>Badan POM RI (BPOM)</option>
                                <option value="Produsen" {{ old('recall_source') === 'Produsen' ? 'selected' : '' }}>Produsen / Industri Farmasi</option>
                                <option value="Kemenkes" {{ old('recall_source') === 'Kemenkes' ? 'selected' : '' }}>Kementerian Kesehatan RI</option>
                                <option value="Internal" {{ old('recall_source') === 'Internal' ? 'selected' : '' }}>Temuan Internal Apotek</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Instruksi / Ditemukan <span class="text-rose-500">*</span></label>
                            <input type="date" name="recall_date" value="{{ old('recall_date', now()->toDateString()) }}" required
                                   class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-rose-500 focus:border-rose-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penarikan Obat <span class="text-rose-500">*</span></label>
                            <input type="text" name="recall_reason" value="{{ old('recall_reason') }}" required
                                   placeholder="Contoh: Surat Edaran BPOM No. XX terkait cemaran zat kimia atau cacat kemasan"
                                   class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-rose-500 focus:border-rose-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tindakan Awal / Penanganan</label>
                            <input type="text" name="action_taken" value="{{ old('action_taken', 'Batch dikarantina di gudang, stok dinonaktifkan dari penjualan POS') }}"
                                   class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-rose-500 focus:border-rose-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
                            <textarea name="notes" rows="2" placeholder="Instruksi tambahan bagi staf farmasi..."
                                      class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-rose-500 focus:border-rose-500">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Preview Box -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sticky top-6">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">
                        Ringkasan Batch Terpilih
                    </h3>

                    <div id="batchPreviewEmpty" class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300"></i>
                        <p class="text-xs">Silakan pilih batch obat di samping untuk melihat rincian.</p>
                    </div>

                    <div id="batchPreviewContent" class="space-y-3 hidden">
                        <div>
                            <div class="text-[10px] text-slate-400 font-bold uppercase">Nama Produk Obat</div>
                            <div id="prevProduct" class="text-sm font-bold text-slate-900">-</div>
                            <div id="prevCode" class="text-xs font-mono text-slate-400">-</div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                            <div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">No. Batch</div>
                                <div id="prevBatch" class="text-xs font-mono font-bold text-rose-600">-</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">Tanggal Expired</div>
                                <div id="prevExp" class="text-xs font-semibold text-slate-700">-</div>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-100">
                            <div class="text-[10px] text-slate-400 font-bold uppercase">Stok Fisik Tersedia</div>
                            <div id="prevStock" class="text-base font-bold text-slate-900">-</div>
                        </div>

                        <div class="pt-2 border-t border-slate-100">
                            <div class="text-[10px] text-slate-400 font-bold uppercase">Distributor / Supplier</div>
                            <div id="prevSupplier" class="text-xs text-slate-700">-</div>
                        </div>
                    </div>

                    <div class="pt-5 border-t border-slate-100 mt-5">
                        <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm rounded-xl shadow-md transition-colors flex items-center justify-center gap-2">
                            <i class="fa-solid fa-ban"></i> Inisiasi Recall & Karantina
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const batchSelect = document.getElementById('batchSelect');
            const previewEmpty = document.getElementById('batchPreviewEmpty');
            const previewContent = document.getElementById('batchPreviewContent');

            function updatePreview() {
                const opt = batchSelect.options[batchSelect.selectedIndex];
                if (opt && opt.value) {
                    document.getElementById('prevProduct').textContent = opt.dataset.product;
                    document.getElementById('prevCode').textContent = opt.dataset.code;
                    document.getElementById('prevBatch').textContent = opt.dataset.batch;
                    document.getElementById('prevExp').textContent = opt.dataset.exp;
                    document.getElementById('prevStock').textContent = opt.dataset.stock + ' Unit';
                    document.getElementById('prevSupplier').textContent = opt.dataset.supplier;

                    previewEmpty.classList.add('hidden');
                    previewContent.classList.remove('hidden');
                } else {
                    previewEmpty.classList.remove('hidden');
                    previewContent.classList.add('hidden');
                }
            }

            batchSelect.addEventListener('change', updatePreview);
            updatePreview(); // initial check
        });
    </script>
</x-app-layout>
