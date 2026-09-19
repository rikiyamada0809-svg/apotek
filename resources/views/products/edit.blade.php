<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Edit Obat & Produk</h1>
        <nav class="flex text-xs text-slate-500 mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('products.index') }}" class="hover:text-slate-900">Obat & Produk</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 font-medium">Edit: {{ $product->name }}</span>
        </nav>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
            <p class="font-bold mb-1">Terdapat kesalahan input:</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $existingUnits = $product->productUnits->map(function($u) {
            return [
                'unit_id' => $u->unit_id,
                'conversion_factor' => (float)$u->conversion_factor,
                'is_base_unit' => (bool)$u->is_base_unit,
                'purchase_price' => (float)$u->purchase_price,
                'selling_price' => (float)$u->selling_price,
                'wholesale_price' => (float)$u->wholesale_price,
                'prescription_price' => (float)$u->prescription_price,
            ];
        })->toArray();

        if (empty($existingUnits)) {
            $existingUnits = [
                ['unit_id' => '', 'conversion_factor' => 1, 'is_base_unit' => true, 'purchase_price' => 0, 'selling_price' => 0, 'wholesale_price' => 0, 'prescription_price' => 0]
            ];
        }
    @endphp

    <div class="max-w-5xl">
        <form method="POST" action="{{ route('products.update', $product) }}" 
              x-data="{
                  units: {{ Js::from(old('units', $existingUnits)) }},
                  addUnit() {
                      this.units.push({
                          unit_id: '',
                          conversion_factor: 1,
                          is_base_unit: false,
                          purchase_price: 0,
                          selling_price: 0,
                          wholesale_price: 0,
                          prescription_price: 0
                      });
                  },
                  removeUnit(index) {
                      if (this.units.length > 1) {
                          this.units.splice(index, 1);
                      } else {
                          alert('Minimal harus ada satu satuan untuk produk.');
                      }
                  },
                  setBaseUnit(index) {
                      this.units.forEach((u, i) => {
                          u.is_base_unit = (i === index);
                          if (i === index) {
                              u.conversion_factor = 1;
                          }
                      });
                  }
              }" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- 1. Informasi Utama Produk -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-pills text-emerald-600"></i> Informasi Produk
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="product_code" class="block text-sm font-semibold text-slate-700 mb-1">Kode Produk <span class="text-red-500">*</span></label>
                        <input type="text" name="product_code" id="product_code" value="{{ old('product_code', $product->product_code) }}" required
                               class="w-full px-4 py-2 text-sm font-mono border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('product_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="barcode" class="block text-sm font-semibold text-slate-700 mb-1">Barcode / SKU</label>
                        <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $product->barcode) }}" placeholder="Scan atau input barcode..."
                               class="w-full px-4 py-2 text-sm font-mono border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('barcode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Produk / Obat <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="generic_name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Generik</label>
                        <input type="text" name="generic_name" id="generic_name" value="{{ old('generic_name', $product->generic_name) }}"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('generic_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="brand" class="block text-sm font-semibold text-slate-700 mb-1">Merk / Brand Dagang</label>
                        <input type="text" name="brand" id="brand" value="{{ old('brand', $product->brand) }}"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('brand') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="strength" class="block text-sm font-semibold text-slate-700 mb-1">Kekuatan Dosis</label>
                        <input type="text" name="strength" id="strength" value="{{ old('strength', $product->strength) }}"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('strength') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-slate-700 mb-1">Kategori</label>
                        <select name="category_id" id="category_id"
                                class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">— Pilih Kategori —</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="drug_class" class="block text-sm font-semibold text-slate-700 mb-1">Golongan Obat</label>
                        <select name="drug_class" id="drug_class"
                                class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">— Pilih Golongan —</option>
                            <option value="Bebas" @selected(old('drug_class', $product->drug_class) == 'Bebas')>Obat Bebas (Hijau)</option>
                            <option value="Bebas Terbatas" @selected(old('drug_class', $product->drug_class) == 'Bebas Terbatas')>Obat Bebas Terbatas (Biru)</option>
                            <option value="Keras" @selected(old('drug_class', $product->drug_class) == 'Keras')>Obat Keras (Merah / K)</option>
                            <option value="Narkotika" @selected(old('drug_class', $product->drug_class) == 'Narkotika')>Narkotika (Palang Merah)</option>
                            <option value="Psikotropika" @selected(old('drug_class', $product->drug_class) == 'Psikotropika')>Psikotropika</option>
                            <option value="Herbal" @selected(old('drug_class', $product->drug_class) == 'Herbal')>Herbal / Jamu</option>
                            <option value="Alkes" @selected(old('drug_class', $product->drug_class) == 'Alkes')>Alat Kesehatan & Medis</option>
                        </select>
                        @error('drug_class') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="form" class="block text-sm font-semibold text-slate-700 mb-1">Bentuk Sediaan</label>
                        <input type="text" name="form" id="form" value="{{ old('form', $product->form) }}"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('form') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="manufacturer_id" class="block text-sm font-semibold text-slate-700 mb-1">Pabrik / Produsen</label>
                        <select name="manufacturer_id" id="manufacturer_id"
                                class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">— Pilih Pabrik / Produsen —</option>
                            @foreach($manufacturers as $manufacturer)
                                <option value="{{ $manufacturer->id }}" @selected(old('manufacturer_id', $product->manufacturer_id) == $manufacturer->id)>{{ $manufacturer->name }}</option>
                            @endforeach
                        </select>
                        @error('manufacturer_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="storage_condition" class="block text-sm font-semibold text-slate-700 mb-1">Kondisi Penyimpanan</label>
                        <select name="storage_condition" id="storage_condition"
                                class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="Suhu Ruang (<30°C)" @selected(old('storage_condition', $product->storage_condition) == 'Suhu Ruang (<30°C)')>Suhu Ruang (<30°C)</option>
                            <option value="Suhu Sejuk (15-25°C)" @selected(old('storage_condition', $product->storage_condition) == 'Suhu Sejuk (15-25°C)')>Suhu Sejuk (15-25°C)</option>
                            <option value="Kulkas / Dingin (2-8°C)" @selected(old('storage_condition', $product->storage_condition) == 'Kulkas / Dingin (2-8°C)')>Kulkas / Dingin (2-8°C)</option>
                            <option value="Freezer / Beku (<0°C)" @selected(old('storage_condition', $product->storage_condition) == 'Freezer / Beku (<0°C)')>Freezer / Beku (<0°C)</option>
                        </select>
                        @error('storage_condition') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="min_order_qty" class="block text-sm font-semibold text-slate-700 mb-1">Min. Pemesanan (Order Qty)</label>
                        <input type="number" name="min_order_qty" id="min_order_qty" value="{{ old('min_order_qty', $product->min_order_qty) }}" min="1"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('min_order_qty') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi / Indikasi / Aturan Pakai</label>
                    <textarea name="description" id="description" rows="2"
                              class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">{{ old('description', $product->description) }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-wrap items-center gap-6 pt-2">
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="requires_prescription" value="0">
                        <input type="checkbox" name="requires_prescription" id="requires_prescription" value="1" @checked(old('requires_prescription', $product->requires_prescription))
                               class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="requires_prescription" class="text-sm font-semibold text-amber-700 flex items-center gap-1">
                            <i class="fa-solid fa-receipt text-xs"></i> Wajib Resep Dokter (R/)
                        </label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $product->is_active))
                               class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="is_active" class="text-sm font-semibold text-slate-700">Produk Aktif Dijual</label>
                    </div>
                </div>
            </div>

            <!-- 2. Multi-Satuan & Harga Bertingkat -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-tags text-emerald-600"></i> Satuan & Harga Bertingkat (Multi-Unit)
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Tentukan satuan dasar (terkecil), faktor konversi, harga beli, dan harga jual.</p>
                    </div>
                    <button type="button" @click="addUnit()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-semibold rounded-lg transition-colors">
                        <i class="fa-solid fa-plus text-[10px]"></i> Tambah Satuan
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-xs">
                            <tr>
                                <th class="px-3 py-2 text-center font-semibold w-16">Dasar?</th>
                                <th class="px-3 py-2 text-left font-semibold w-40">Satuan <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2 text-left font-semibold w-28">Konversi <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2 text-left font-semibold">Harga Beli (Rp) <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2 text-left font-semibold">Harga Jual (Rp) <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2 text-left font-semibold">Harga Grosir (Rp)</th>
                                <th class="px-3 py-2 text-left font-semibold">Harga Resep (Rp)</th>
                                <th class="px-3 py-2 text-center font-semibold w-12">Hapus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(unit, index) in units" :key="index">
                                <tr class="hover:bg-slate-50">
                                    <!-- Is Base Unit Radio -->
                                    <td class="px-3 py-2 text-center">
                                        <input type="radio" name="base_unit_selector" :checked="unit.is_base_unit" @change="setBaseUnit(index)"
                                               title="Tandai sebagai satuan terkecil / dasar" class="text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                                        <input type="hidden" :name="'units[' + index + '][is_base_unit]'" :value="unit.is_base_unit ? 1 : 0">
                                    </td>

                                    <!-- Unit Select -->
                                    <td class="px-3 py-2">
                                        <select :name="'units[' + index + '][unit_id]'" x-model="unit.unit_id" required
                                                class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500">
                                            <option value="">— Pilih Satuan —</option>
                                            @foreach($units as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->abbreviation }})</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <!-- Conversion Factor -->
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0.01" :name="'units[' + index + '][conversion_factor]'" x-model="unit.conversion_factor" :readonly="unit.is_base_unit" required
                                               class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500" :class="{'bg-slate-100 text-slate-500': unit.is_base_unit}">
                                    </td>

                                    <!-- Purchase Price -->
                                    <td class="px-3 py-2">
                                        <input type="number" step="1" min="0" :name="'units[' + index + '][purchase_price]'" x-model="unit.purchase_price" required
                                               class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500">
                                    </td>

                                    <!-- Selling Price -->
                                    <td class="px-3 py-2">
                                        <input type="number" step="1" min="0" :name="'units[' + index + '][selling_price]'" x-model="unit.selling_price" required
                                               class="w-full px-2.5 py-1.5 text-xs font-semibold text-emerald-700 border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500">
                                    </td>

                                    <!-- Wholesale Price -->
                                    <td class="px-3 py-2">
                                        <input type="number" step="1" min="0" :name="'units[' + index + '][wholesale_price]'" x-model="unit.wholesale_price"
                                               class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500">
                                    </td>

                                    <!-- Prescription Price -->
                                    <td class="px-3 py-2">
                                        <input type="number" step="1" min="0" :name="'units[' + index + '][prescription_price]'" x-model="unit.prescription_price"
                                               class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500">
                                    </td>

                                    <!-- Remove Button -->
                                    <td class="px-3 py-2 text-center">
                                        <button type="button" @click="removeUnit(index)" class="text-red-400 hover:text-red-600 p-1">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center gap-3">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-check mr-1"></i> Perbarui Produk
                </button>
                <a href="{{ route('products.index') }}" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-sm font-medium text-slate-700 rounded-lg transition-colors">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
