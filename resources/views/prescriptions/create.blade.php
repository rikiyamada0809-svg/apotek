<x-app-layout>
    <div class="max-w-5xl mx-auto" x-data="prescriptionForm()">
        <!-- Header -->
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Input Resep Dokter Baru</h1>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('prescriptions.index') }}" class="hover:text-slate-900">Resep Obat</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">Input Resep</span>
                </nav>
            </div>
            <a href="{{ route('prescriptions.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors">
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

        <form method="POST" action="{{ route('prescriptions.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- Card 1: Informasi Dasar Resep & Pasien -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6 space-y-6">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">1. Data Dokter & Pasien</h2>
                        <p class="text-xs text-slate-500">Informasi resep resmi, dokter penulis resep, dan identitas pasien.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">No. Resep (Otomatis)</label>
                        <input type="text" value="{{ $prescriptionNumber }}" readonly 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-600 focus:outline-none cursor-not-allowed">
                        <p class="text-xs text-slate-400 mt-1">RX-YYYYMMDD-XXXX</p>
                    </div>

                    <div>
                        <label for="prescription_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Tanggal Resep <span class="text-red-500">*</span></label>
                        <input type="date" id="prescription_date" name="prescription_date" value="{{ old('prescription_date', now()->toDateString()) }}" required
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="doctor_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Dokter Penulis Resep</label>
                        <select id="doctor_id" name="doctor_id"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">-- Pilih Dokter (Opsional) --</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" @selected(old('doctor_id') == $doc->id)>{{ $doc->name }} ({{ $doc->specialization ?? 'Umum' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="customer_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Pasien / Pelanggan</label>
                        <select id="customer_id" name="customer_id"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">-- Pasien Umum (Tanpa Akun) --</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->id }}" @selected(old('customer_id', $selectedCustomerId) == $cust->id)>{{ $cust->name }} ({{ $cust->phone ?? 'No Telp -' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="diagnosis" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Diagnosis Medis</label>
                        <input type="text" id="diagnosis" name="diagnosis" value="{{ old('diagnosis') }}" placeholder="Contoh: ISPA, Hipertensi Grade 1, Dispepsia..."
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="image" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Foto / Scan Lembar Resep Asli</label>
                        <input type="file" id="image" name="image" accept="image/*"
                               class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG, WEBP (Maksimal 5MB).</p>
                    </div>

                    <div>
                        <label for="notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Catatan Tambahan / Alergi Pasien</label>
                        <textarea id="notes" name="notes" rows="2" placeholder="Catatan alergi obat, instruksi khusus pasien..."
                                  class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Card 2: Rincian Obat Resep -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-pills"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">2. Rincian Obat & Aturan Pakai</h2>
                            <p class="text-xs text-slate-500">Tambahkan obat-obatan yang tercantum dalam resep beserta dosis & aturan pakai.</p>
                        </div>
                    </div>
                    <button type="button" @click="addItem()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">
                        <i class="fa-solid fa-plus"></i> Tambah Obat
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-3 w-64">Nama Produk Obat <span class="text-red-500">*</span></th>
                                <th class="py-3 px-3 w-32">Satuan <span class="text-red-500">*</span></th>
                                <th class="py-3 px-3 w-24 text-right">Jumlah <span class="text-red-500">*</span></th>
                                <th class="py-3 px-3 w-32">Dosis</th>
                                <th class="py-3 px-3 w-36">Frekuensi</th>
                                <th class="py-3 px-3 w-48">Petunjuk / Aturan</th>
                                <th class="py-3 px-2 w-10 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-3">
                                        <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="onProductChange(index)" required
                                                class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            <option value="">-- Pilih Obat --</option>
                                            <template x-for="p in productList" :key="p.id">
                                                <option :value="p.id" x-text="p.name"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-3 px-3">
                                        <select :name="`items[${index}][unit_id]`" x-model="item.unit_id" required
                                                class="w-full px-2 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            <option value="">-- Satuan --</option>
                                            <template x-for="u in unitList" :key="u.id">
                                                <option :value="u.id" x-text="u.name"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <input type="number" step="any" min="0.1" :name="`items[${index}][quantity]`" x-model.number="item.quantity" required
                                               class="w-full px-2 py-1.5 text-xs text-right border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 font-semibold">
                                    </td>
                                    <td class="py-3 px-3">
                                        <input type="text" :name="`items[${index}][dosage]`" x-model="item.dosage" placeholder="Cth: 500mg"
                                               class="w-full px-2 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    </td>
                                    <td class="py-3 px-3">
                                        <input type="text" :name="`items[${index}][frequency]`" x-model="item.frequency" placeholder="Cth: 3x1 hari"
                                               class="w-full px-2 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    </td>
                                    <td class="py-3 px-3">
                                        <input type="text" :name="`items[${index}][instructions]`" x-model="item.instructions" placeholder="Cth: Sesudah makan, habiskan"
                                               class="w-full px-2 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    </td>
                                    <td class="py-3 px-2 text-center">
                                        <button type="button" @click="removeItem(index)" class="text-slate-400 hover:text-red-600 transition-colors p-1" title="Hapus">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('prescriptions.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-file-arrow-up"></i> Simpan Resep
                </button>
            </div>
        </form>
    </div>

    <script>
        function prescriptionForm() {
            const productList = @json($products);
            const unitList = @json($units);

            return {
                productList: productList,
                unitList: unitList,
                items: [
                    {
                        product_id: '',
                        unit_id: '',
                        quantity: 10,
                        dosage: '',
                        frequency: '3 x 1 hari',
                        instructions: 'Sesudah makan'
                    }
                ],

                addItem() {
                    this.items.push({
                        product_id: '',
                        unit_id: '',
                        quantity: 10,
                        dosage: '',
                        frequency: '3 x 1 hari',
                        instructions: 'Sesudah makan'
                    });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    } else {
                        alert('Minimal 1 item obat harus tercantum dalam resep.');
                    }
                },

                onProductChange(index) {
                    const item = this.items[index];
                    const prod = this.productList.find(p => p.id == item.product_id);
                    if (prod && prod.product_units && prod.product_units.length > 0) {
                        item.unit_id = prod.product_units[0].unit_id;
                    }
                }
            };
        }
    </script>
</x-app-layout>
