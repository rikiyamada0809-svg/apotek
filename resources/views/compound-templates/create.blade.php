<x-app-layout>
    <div class="max-w-4xl mx-auto" x-data="templateForm()">
        <!-- Header -->
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Buat Template Racikan</h1>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('compound-templates.index') }}" class="hover:text-slate-900">Template Racikan</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">Buat Baru</span>
                </nav>
            </div>
            <a href="{{ route('compound-templates.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors">
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

        <form method="POST" action="{{ route('compound-templates.store') }}">
            @csrf

            <!-- Card 1: Informasi Template -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Nama Racikan / Formula <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Puyer Batuk Pilek Anak, Salep 2-4 Modifikasi" required
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="service_fee" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Biaya Jasa Racik Standar (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" id="service_fee" name="service_fee" value="{{ old('service_fee', 5000) }}" min="0" step="500" required
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="description" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Deskripsi / Indikasi</label>
                        <textarea id="description" name="description" rows="2" placeholder="Deskripsi peruntukan racikan..."
                                  class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label for="preparation_instructions" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Petunjuk & Cara Peracikan (SOP)</label>
                        <textarea id="preparation_instructions" name="preparation_instructions" rows="2" placeholder="Gerus halus dalam mortir, bagi rata menjadi..."
                                  class="w-full px-3.5 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('preparation_instructions') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Card 2: Komposisi Bahan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Komposisi Bahan per Unit / Bungkus</h2>
                        <p class="text-xs text-slate-500">Tentukan obat pembentuk dan kuantitas untuk 1 bungkus/wadah.</p>
                    </div>
                    <button type="button" @click="addItem()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
                        <i class="fa-solid fa-plus"></i> Tambah Bahan
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row items-center gap-3">
                            <div class="flex-1 w-full">
                                <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Obat Bahan</label>
                                <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="onProductChange(index)" required
                                        class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg bg-white focus:ring-1 focus:ring-indigo-500">
                                    <option value="">-- Pilih Obat --</option>
                                    <template x-for="p in productList" :key="p.id">
                                        <option :value="p.id" x-text="p.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="w-full sm:w-36">
                                <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Kuantitas / Bks</label>
                                <input type="number" step="any" min="0.001" :name="`items[${index}][quantity_per_unit]`" x-model.number="item.quantity_per_unit" required
                                       class="w-full px-3 py-2 text-xs text-right border border-slate-200 rounded-lg bg-white focus:ring-1 focus:ring-indigo-500 font-semibold">
                            </div>

                            <div class="w-full sm:w-32">
                                <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Satuan</label>
                                <select :name="`items[${index}][unit_id]`" x-model="item.unit_id" required
                                        class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg bg-white focus:ring-1 focus:ring-indigo-500">
                                    <option value="">-- Satuan --</option>
                                    <template x-for="u in unitList" :key="u.id">
                                        <option :value="u.id" x-text="u.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="w-full sm:w-44">
                                <label class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Catatan</label>
                                <input type="text" :name="`items[${index}][notes]`" x-model="item.notes" placeholder="Cth: DTM / pulveres"
                                       class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg bg-white focus:ring-1 focus:ring-indigo-500">
                            </div>

                            <div class="sm:pt-5">
                                <button type="button" @click="removeItem(index)" class="p-2 text-slate-400 hover:text-red-600 transition-colors" title="Hapus">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('compound-templates.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Template
                </button>
            </div>
        </form>
    </div>

    <script>
        function templateForm() {
            const productList = @json($products);
            const unitList = @json($units);

            return {
                productList: productList,
                unitList: unitList,
                items: [
                    { product_id: '', quantity_per_unit: 1, unit_id: '', notes: '' }
                ],

                addItem() {
                    this.items.push({ product_id: '', quantity_per_unit: 1, unit_id: '', notes: '' });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    } else {
                        alert('Minimal satu bahan obat harus tercantum dalam template racikan.');
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
