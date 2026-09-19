<x-app-layout>
    <div class="max-w-5xl mx-auto" x-data="compoundForm()">
        <!-- Header -->
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Peracikan Obat Baru</h1>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('prescriptions.index') }}" class="hover:text-slate-900">Resep & Racikan</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">Form Racikan</span>
                </nav>
            </div>
            @if($selectedPrescription)
                <a href="{{ route('prescriptions.show', $selectedPrescription) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Resep
                </a>
            @else
                <a href="{{ route('prescriptions.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
            @endif
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

        @if($selectedPrescription)
            <!-- Info Resep Terkait -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-2xl flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-base">
                        <i class="fa-solid fa-file-prescription"></i>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-blue-800 uppercase tracking-wider">Terkait Resep Dokter</div>
                        <div class="text-sm font-bold text-slate-900">{{ $selectedPrescription->prescription_number }} &bull; Pasien: {{ $selectedPrescription->customer?->name ?? 'Pasien Umum' }}</div>
                        <div class="text-xs text-slate-600">Dokter: {{ $selectedPrescription->doctor?->name ?? 'Dokter' }}</div>
                    </div>
                </div>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-lg text-xs font-semibold">
                    {{ $selectedPrescription->items->count() }} Obat Resep
                </span>
            </div>
        @endif

        <form method="POST" action="{{ route('compounds.store') }}">
            @csrf

            @if($selectedPrescription)
                <input type="hidden" name="prescription_id" value="{{ $selectedPrescription->id }}">
            @endif

            <!-- Card 1: Data Racikan & Template -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6 space-y-5">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-mortar-pestle"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">1. Konfigurasi Racikan</h2>
                        <p class="text-xs text-slate-500">Pilih template racikan atau tentukan nama racikan dan jumlah kemasan yang dibuat.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">No. Racikan (Otomatis)</label>
                        <input type="text" value="{{ $compoundNumber }}" readonly 
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono text-slate-600 focus:outline-none cursor-not-allowed">
                        <p class="text-xs text-slate-400 mt-1">CMP-YYYYMMDD-XXXX</p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="template_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Gunakan Template Racikan (Opsional)</label>
                        <select id="template_id" name="template_id" x-model="selectedTemplateId" @change="applyTemplate()"
                                class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="">-- Buat Racikan Kustom (Tanpa Template) --</option>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}" @selected(request('template_id') == $tpl->id)>{{ $tpl->name }} (Jasa: Rp {{ number_format($tpl->service_fee, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Nama Obat Racikan <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" x-model="name" required placeholder="Contoh: Puyer Flu & Batuk No. 1"
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="total_packages" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Jumlah Bungkus / Kemasan <span class="text-red-500">*</span></label>
                        <input type="number" id="total_packages" name="total_packages" x-model.number="totalPackages" @input="calculateTotals()" min="1" required
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 font-bold">
                        <p class="text-xs text-slate-400 mt-1">Cth: 10 bungkus puyer / kapsul</p>
                    </div>

                    <div>
                        <label for="service_fee" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Biaya Jasa Racik (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" id="service_fee" name="service_fee" x-model.number="serviceFee" @input="calculateTotals()" min="0" step="500" required
                               class="w-full px-3.5 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 font-bold text-indigo-600">
                    </div>
                </div>
            </div>

            <!-- Card 2: Rincian Bahan Obat yang Digunakan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-pills"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">2. Komposisi Bahan Racikan</h2>
                            <p class="text-xs text-slate-500">Kebutuhan total bahan dihitung otomatis: Jumlah per Bungkus &times; Total Bungkus.</p>
                        </div>
                    </div>
                    <button type="button" @click="addItem()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
                        <i class="fa-solid fa-plus"></i> Tambah Bahan
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-3 w-64">Nama Bahan Obat <span class="text-red-500">*</span></th>
                                <th class="py-3 px-3 w-32">Satuan <span class="text-red-500">*</span></th>
                                <th class="py-3 px-3 w-28 text-right">Qty / Bks <span class="text-red-500">*</span></th>
                                <th class="py-3 px-3 w-28 text-right">Total Pakai</th>
                                <th class="py-3 px-3 w-32 text-right">Harga Satuan <span class="text-red-500">*</span></th>
                                <th class="py-3 px-3 w-36 text-right">Subtotal</th>
                                <th class="py-3 px-2 w-10 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-3">
                                        <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="onProductChange(index)" required
                                                class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <option value="">-- Pilih Obat --</option>
                                            <template x-for="p in productList" :key="p.id">
                                                <option :value="p.id" x-text="p.name"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-3 px-3">
                                        <select :name="`items[${index}][unit_id]`" x-model="item.unit_id" required
                                                class="w-full px-2 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <option value="">-- Satuan --</option>
                                            <template x-for="u in unitList" :key="u.id">
                                                <option :value="u.id" x-text="u.name"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <input type="number" step="any" min="0.001" :name="`items[${index}][quantity_per_package]`" x-model.number="item.quantity_per_package" @input="calculateTotals()" required
                                               class="w-full px-2 py-1.5 text-xs text-right border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold">
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono text-xs font-bold text-slate-800" x-text="(item.quantity_per_package * totalPackages).toFixed(2)"></td>
                                    <td class="py-3 px-3 text-right">
                                        <input type="number" step="any" min="0" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" @input="calculateTotals()" required
                                               class="w-full px-2 py-1.5 text-xs text-right border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500 font-semibold">
                                    </td>
                                    <td class="py-3 px-3 text-right text-xs font-bold text-indigo-600" x-text="formatRupiah((item.quantity_per_package * totalPackages) * (item.unit_price || 0))"></td>
                                    <td class="py-3 px-2 text-center">
                                        <button type="button" @click="removeItem(index)" class="text-slate-400 hover:text-red-600 transition-colors p-1" title="Hapus">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200 bg-slate-50 font-bold text-slate-800 text-xs">
                            <tr>
                                <td colspan="5" class="py-3 px-4 text-right">Total Biaya Bahan:</td>
                                <td class="py-3 px-4 text-right text-slate-900" x-text="formatRupiah(totalMaterialCost)"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="py-2 px-4 text-right">Biaya Jasa Racik:</td>
                                <td class="py-2 px-4 text-right text-indigo-600" x-text="formatRupiah(serviceFee)"></td>
                                <td></td>
                            </tr>
                            <tr class="bg-indigo-50/50 text-indigo-900 border-t border-indigo-100">
                                <td colspan="5" class="py-3 px-4 text-right text-sm">TOTAL HARGA RACIKAN:</td>
                                <td class="py-3 px-4 text-right text-sm font-bold text-indigo-700" x-text="formatRupiah(totalPrice)"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('prescriptions.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan & Buat Racikan
                </button>
            </div>
        </form>
    </div>

    <script>
        function compoundForm() {
            const productList = @json($products);
            const unitList = @json($units);
            const templateList = @json($templates);
            const initialTemplateId = '{{ request('template_id', '') }}';

            return {
                productList: productList,
                unitList: unitList,
                templateList: templateList,
                selectedTemplateId: initialTemplateId,
                name: '',
                totalPackages: 10,
                serviceFee: 5000,
                items: [
                    { product_id: '', unit_id: '', quantity_per_package: 1, unit_price: 1000 }
                ],
                totalMaterialCost: 0,
                totalPrice: 0,

                addItem() {
                    this.items.push({
                        product_id: '',
                        unit_id: '',
                        quantity_per_package: 1,
                        unit_price: 0
                    });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                        this.calculateTotals();
                    } else {
                        alert('Minimal 1 bahan harus dicantumkan dalam racikan obat.');
                    }
                },

                applyTemplate() {
                    if (!this.selectedTemplateId) return;
                    const tpl = this.templateList.find(t => t.id == this.selectedTemplateId);
                    if (!tpl) return;

                    this.name = tpl.name;
                    this.serviceFee = parseFloat(tpl.service_fee) || 5000;

                    if (tpl.items && tpl.items.length > 0) {
                        this.items = tpl.items.map(it => {
                            const prod = this.productList.find(p => p.id == it.product_id);
                            return {
                                product_id: it.product_id,
                                unit_id: it.unit_id,
                                quantity_per_package: parseFloat(it.quantity_per_unit) || 1,
                                unit_price: parseFloat(prod?.purchase_price) || 1000
                            };
                        });
                    }
                    this.calculateTotals();
                },

                onProductChange(index) {
                    const item = this.items[index];
                    const prod = this.productList.find(p => p.id == item.product_id);
                    if (prod) {
                        if (prod.product_units && prod.product_units.length > 0) {
                            item.unit_id = prod.product_units[0].unit_id;
                        }
                        item.unit_price = parseFloat(prod.purchase_price) || 0;
                    }
                    this.calculateTotals();
                },

                calculateTotals() {
                    let cost = 0;
                    const pkgs = parseInt(this.totalPackages) || 1;
                    this.items.forEach(it => {
                        const qPerPkg = parseFloat(it.quantity_per_package) || 0;
                        const price = parseFloat(it.unit_price) || 0;
                        cost += (qPerPkg * pkgs * price);
                    });
                    this.totalMaterialCost = cost;
                    this.totalPrice = cost + (parseFloat(this.serviceFee) || 0);
                },

                formatRupiah(amount) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(amount || 0);
                },

                init() {
                    if (this.selectedTemplateId) {
                        this.applyTemplate();
                    } else {
                        this.calculateTotals();
                    }
                }
            };
        }
    </script>
</x-app-layout>
