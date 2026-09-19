<x-app-layout>
    <div class="max-w-5xl mx-auto" x-data="drugDestructionForm()">
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Buat Berita Acara Pemusnahan Obat</h1>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('drug-destructions.index') }}" class="hover:text-slate-900">Pemusnahan Obat</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">Buat Baru</span>
                </nav>
            </div>
            <a href="{{ route('drug-destructions.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                <div class="font-bold mb-1"><i class="fa-solid fa-circle-exclamation mr-1"></i> Terjadi kesalahan input:</div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('drug-destructions.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- Card 1: Informasi Berita Acara & Saksi -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6 space-y-6">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">1. Data Berita Acara & Saksi</h2>
                        <p class="text-xs text-slate-500">Isi data pemusnahan resmi sesuai regulasi BPOM dan Kemenkes.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">No. Berita Acara (Otomatis)</label>
                        <input type="text" value="{{ $destructionNumber }}" readonly 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-600 focus:outline-none cursor-not-allowed">
                        <p class="text-xs text-slate-400 mt-1">DST-YYYYMMDD-XXXX</p>
                    </div>

                    <div>
                        <label for="destruction_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Tanggal Pemusnahan <span class="text-red-500">*</span></label>
                        <input type="date" id="destruction_date" name="destruction_date" value="{{ old('destruction_date', now()->toDateString()) }}" required
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div>
                        <label for="method" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Metode Pemusnahan <span class="text-red-500">*</span></label>
                        <select id="method" name="method" required
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                            <option value="">-- Pilih Metode --</option>
                            <option value="Incinerator / Pembakaran" @selected(old('method') == 'Incinerator / Pembakaran')>Incinerator / Pembakaran Suhu Tinggi</option>
                            <option value="Pengenceran & Pembuangan Limbah Cair" @selected(old('method') == 'Pengenceran & Pembuangan Limbah Cair')>Pengenceran & Pembuangan Limbah Cair (IPAL)</option>
                            <option value="Enkapsulasi & Penimbunan" @selected(old('method') == 'Enkapsulasi & Penimbunan')>Enkapsulasi & Penimbunan (Landfill Aman)</option>
                            <option value="Dihancurkan & Dilarutkan" @selected(old('method') == 'Dihancurkan & Dilarutkan')>Dihancurkan Fisik & Dilarutkan</option>
                            <option value="Pihak Ketiga Berizin" @selected(old('method') == 'Pihak Ketiga Berizin')>Diserahkan ke Pihak Ketiga / Pengolah B3 Berizin</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="witness_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Nama Saksi Pemusnahan <span class="text-red-500">*</span></label>
                        <input type="text" id="witness_name" name="witness_name" value="{{ old('witness_name') }}" placeholder="Contoh: Apt. Budi Santoso, S.Farm / Petugas Dinkes" required
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                    </div>

                    <div>
                        <label for="witness_role" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Jabatan / Instansi Saksi <span class="text-red-500">*</span></label>
                        <input type="text" id="witness_role" name="witness_role" value="{{ old('witness_role') }}" placeholder="Contoh: Apoteker Pendamping / Dinkes Kota / BPOM" required
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Catatan Tambahan / Lokasi Pemusnahan</label>
                        <textarea id="notes" name="notes" rows="2" placeholder="Catatan perihal pelaksanaan pemusnahan..."
                                  class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">{{ old('notes') }}</textarea>
                    </div>

                    <div>
                        <label for="document" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Lampiran Dokumen / Foto Berita Acara (Opsional)</label>
                        <input type="file" id="document" name="document" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                        <p class="text-xs text-slate-400 mt-1">Format: PDF, JPG, PNG (Maksimal 5MB).</p>
                    </div>
                </div>
            </div>

            <!-- Card 2: Daftar Obat yang Dimusnahkan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-pills"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">2. Daftar Obat yang Dimusnahkan</h2>
                            <p class="text-xs text-slate-500">Pilih batch obat yang kadaluarsa atau rusak serta jumlah yang akan dimusnahkan.</p>
                        </div>
                    </div>
                    <button type="button" @click="addItem()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition-colors">
                        <i class="fa-solid fa-plus"></i> Tambah Obat
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 w-64">Obat & No. Batch <span class="text-red-500">*</span></th>
                                <th class="py-3 px-4 w-28 text-center">Tgl Kadaluarsa</th>
                                <th class="py-3 px-4 w-24 text-right">Stok Ada</th>
                                <th class="py-3 px-4 w-28 text-right">Jumlah Musnah <span class="text-red-500">*</span></th>
                                <th class="py-3 px-4 w-32 text-right">Harga Beli</th>
                                <th class="py-3 px-4 w-36 text-right">Subtotal Kerugian</th>
                                <th class="py-3 px-4 w-44">Alasan <span class="text-red-500">*</span></th>
                                <th class="py-3 px-2 w-12 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4">
                                        <select :name="`items[${index}][batch_id]`" x-model="item.batch_id" @change="onBatchChange(index)" required
                                                class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500">
                                            <option value="">-- Pilih Batch --</option>
                                            <template x-for="b in availableBatches" :key="b.id">
                                                <option :value="b.id" x-text="`${b.product_name} (Batch: ${b.batch_number}, Sisa: ${b.current_qty})`"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-3 px-4 text-center text-xs" x-text="item.expiry_date || '-'"></td>
                                    <td class="py-3 px-4 text-right text-xs font-semibold" x-text="item.max_qty || 0"></td>
                                    <td class="py-3 px-4 text-right">
                                        <input type="number" step="any" min="0.01" :max="item.max_qty" :name="`items[${index}][quantity]`" x-model.number="item.quantity" @input="calculateTotals()" required
                                               class="w-full px-2 py-1 text-xs text-right border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500 font-semibold">
                                    </td>
                                    <td class="py-3 px-4 text-right text-xs text-slate-600" x-text="formatRupiah(item.unit_price || 0)"></td>
                                    <td class="py-3 px-4 text-right text-xs font-bold text-red-600" x-text="formatRupiah((item.quantity || 0) * (item.unit_price || 0))"></td>
                                    <td class="py-3 px-4">
                                        <select :name="`items[${index}][reason]`" x-model="item.reason" required
                                                class="w-full px-2 py-1 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500">
                                            <option value="Kadaluarsa / Expired">Kadaluarsa / Expired</option>
                                            <option value="Kemasan Rusak / Pecah">Kemasan Rusak / Pecah</option>
                                            <option value="Perubahan Fisik / Bau / Warna">Perubahan Fisik / Warna</option>
                                            <option value="Penarikan dari Peredaran (Recall)">Penarikan / Recall</option>
                                            <option value="Terkontaminasi">Terkontaminasi</option>
                                        </select>
                                    </td>
                                    <td class="py-3 px-2 text-center">
                                        <button type="button" @click="removeItem(index)" class="text-slate-400 hover:text-red-600 transition-colors p-1" title="Hapus Baris">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200 bg-slate-50 font-bold text-slate-800 text-xs">
                            <tr>
                                <td colspan="5" class="py-3 px-4 text-right">TOTAL ESTIMASI KERUGIAN:</td>
                                <td class="py-3 px-4 text-right text-sm text-red-600" x-text="formatRupiah(totalLoss)"></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('drug-destructions.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Draft Pemusnahan
                </button>
            </div>
        </form>
    </div>

    <script>
        function drugDestructionForm() {
            const rawBatches = @json($batches);
            const availableBatches = rawBatches.map(b => ({
                id: b.id,
                product_name: b.product?.name ?? 'Obat',
                batch_number: b.batch_number,
                expiry_date: b.expiry_date,
                current_qty: parseFloat(b.current_qty) || 0,
                purchase_price: parseFloat(b.purchase_price) || 0
            }));

            return {
                availableBatches: availableBatches,
                items: [
                    {
                        batch_id: '',
                        product_name: '',
                        expiry_date: '',
                        max_qty: 0,
                        quantity: 1,
                        unit_price: 0,
                        reason: 'Kadaluarsa / Expired',
                        notes: ''
                    }
                ],
                totalLoss: 0,

                addItem() {
                    this.items.push({
                        batch_id: '',
                        product_name: '',
                        expiry_date: '',
                        max_qty: 0,
                        quantity: 1,
                        unit_price: 0,
                        reason: 'Kadaluarsa / Expired',
                        notes: ''
                    });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                        this.calculateTotals();
                    } else {
                        alert('Minimal satu obat harus dimasukkan dalam berita acara pemusnahan.');
                    }
                },

                onBatchChange(index) {
                    const item = this.items[index];
                    const batch = this.availableBatches.find(b => b.id == item.batch_id);
                    if (batch) {
                        item.product_name = batch.product_name;
                        item.expiry_date = batch.expiry_date;
                        item.max_qty = batch.current_qty;
                        item.unit_price = batch.purchase_price;
                        item.quantity = Math.min(item.quantity || 1, batch.current_qty);
                    } else {
                        item.product_name = '';
                        item.expiry_date = '';
                        item.max_qty = 0;
                        item.unit_price = 0;
                    }
                    this.calculateTotals();
                },

                calculateTotals() {
                    let sum = 0;
                    this.items.forEach(it => {
                        const q = parseFloat(it.quantity) || 0;
                        const p = parseFloat(it.unit_price) || 0;
                        sum += (q * p);
                    });
                    this.totalLoss = sum;
                },

                formatRupiah(amount) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(amount || 0);
                },

                init() {
                    this.calculateTotals();
                }
            };
        }
    </script>
</x-app-layout>
