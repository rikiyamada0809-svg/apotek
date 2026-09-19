<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Buat Purchase Order Baru</h1>
        <nav class="flex text-xs text-slate-500 mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('purchase-orders.index') }}" class="hover:text-slate-900">Purchase Order</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 font-medium">Buat Baru</span>
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

    <form method="POST" action="{{ route('purchase-orders.store') }}"
          x-data="{
              productsData: {{ Js::from($products) }},
              items: {{ Js::from(old('items', $prefilledData['items'] ?? [
                  ['product_id' => '', 'unit_id' => '', 'quantity_ordered' => 1, 'unit_price' => 0, 'discount' => 0, 'notes' => '']
              ])) }},
              discountAmount: {{ (float) old('discount_amount', $prefilledData['discount_amount'] ?? 0) }},
              taxRate: 11,
              addItem() {
                  this.items.push({
                      product_id: '',
                      unit_id: '',
                      quantity_ordered: 1,
                      unit_price: 0,
                      discount: 0,
                      notes: ''
                  });
              },
              removeItem(index) {
                  if (this.items.length > 1) {
                      this.items.splice(index, 1);
                  } else {
                      alert('Minimal harus ada 1 item obat dalam pesanan.');
                  }
              },
              onProductChange(index) {
                  const pid = this.items[index].product_id;
                  const prod = this.productsData.find(p => p.id == pid);
                  if (prod && prod.product_units && prod.product_units.length > 0) {
                      const baseUnit = prod.product_units.find(u => u.is_base_unit) || prod.product_units[0];
                      this.items[index].unit_id = baseUnit.unit_id;
                      this.items[index].unit_price = baseUnit.purchase_price || 0;
                  }
              },
              onUnitChange(index) {
                  const pid = this.items[index].product_id;
                  const uid = this.items[index].unit_id;
                  const prod = this.productsData.find(p => p.id == pid);
                  if (prod && prod.product_units) {
                      const matched = prod.product_units.find(u => u.unit_id == uid);
                      if (matched) {
                          this.items[index].unit_price = matched.purchase_price || 0;
                      }
                  }
              },
              getProductUnits(productId) {
                  const prod = this.productsData.find(p => p.id == productId);
                  return prod ? prod.product_units : [];
              },
              itemSubtotal(item) {
                  const q = parseFloat(item.quantity_ordered) || 0;
                  const p = parseFloat(item.unit_price) || 0;
                  const d = parseFloat(item.discount) || 0;
                  return Math.max(0, (q * p) - d);
              },
              get subtotal() {
                  return this.items.reduce((sum, item) => sum + this.itemSubtotal(item), 0);
              },
              get taxAmount() {
                  const taxable = Math.max(0, this.subtotal - (parseFloat(this.discountAmount) || 0));
                  return (taxable * (parseFloat(this.taxRate) || 0)) / 100;
              },
              get grandTotal() {
                  return Math.max(0, this.subtotal - (parseFloat(this.discountAmount) || 0) + this.taxAmount);
              },
              formatRupiah(amount) {
                  return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
              }
          }" class="space-y-6">
        @csrf

        <!-- 1. Header Informasi PO -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
            <h2 class="text-base font-semibold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-file-invoice text-emerald-600"></i> Informasi Purchase Order
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Supplier / Distributor <span class="text-rose-500">*</span></label>
                    <select name="supplier_id" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $prefilledData['supplier_id'] ?? '') == $supplier->id)>
                                {{ $supplier->name }} ({{ $supplier->city ?? 'Nasional' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Gudang Tujuan Penerimaan <span class="text-rose-500">*</span></label>
                    <select name="warehouse_id" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $prefilledData['warehouse_id'] ?? '') == $warehouse->id)>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Order <span class="text-rose-500">*</span></label>
                    <input type="date" name="order_date" value="{{ old('order_date', now()->toDateString()) }}" required
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Perkiraan Tiba (Opsional)</label>
                    <input type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date') }}"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan Pemesanan / Instruksi Pengiriman</label>
                <textarea name="notes" rows="2" placeholder="Catatan tambahan untuk supplier..."
                          class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">{{ old('notes', $prefilledData['notes'] ?? '') }}</textarea>
            </div>
        </div>

        <!-- 2. Rincian Obat / Produk yang Dipesan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-pills text-emerald-600"></i> Rincian Item Obat
                </h2>
                <button type="button" @click="addItem()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold rounded-lg transition-colors">
                    <i class="fa-solid fa-plus"></i> Tambah Obat
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold w-1/3">Produk / Obat <span class="text-rose-500">*</span></th>
                            <th class="px-3 py-2 text-left font-semibold w-36">Satuan <span class="text-rose-500">*</span></th>
                            <th class="px-3 py-2 text-right font-semibold w-28">Kuantitas <span class="text-rose-500">*</span></th>
                            <th class="px-3 py-2 text-right font-semibold w-36">Harga Beli <span class="text-rose-500">*</span></th>
                            <th class="px-3 py-2 text-right font-semibold w-28">Diskon</th>
                            <th class="px-3 py-2 text-right font-semibold w-36">Subtotal</th>
                            <th class="px-3 py-2 text-center font-semibold w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="align-top">
                                <!-- Pilih Produk -->
                                <td class="px-3 py-2">
                                    <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" @change="onProductChange(index)" required
                                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                        <option value="">-- Pilih Obat / Produk --</option>
                                        <template x-for="p in productsData" :key="p.id">
                                            <option :value="p.id" x-text="p.name + ' (' + (p.product_code || '-') + ')'"></option>
                                        </template>
                                    </select>
                                    <input type="text" :name="'items[' + index + '][notes]'" x-model="item.notes" placeholder="Catatan item (opsional)..."
                                           class="w-full mt-1 px-2 py-1 text-[11px] text-slate-500 border border-slate-200 rounded">
                                </td>

                                <!-- Pilih Satuan -->
                                <td class="px-3 py-2">
                                    <select :name="'items[' + index + '][unit_id]'" x-model="item.unit_id" @change="onUnitChange(index)" required
                                            class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                        <option value="">-- Satuan --</option>
                                        <template x-for="u in getProductUnits(item.product_id)" :key="u.unit_id">
                                            <option :value="u.unit_id" x-text="u.unit ? u.unit.name : 'Satuan ' + u.unit_id"></option>
                                        </template>
                                    </select>
                                </td>

                                <!-- Kuantitas -->
                                <td class="px-3 py-2 text-right">
                                    <input type="number" step="any" min="0.01" :name="'items[' + index + '][quantity_ordered]'" x-model.number="item.quantity_ordered" required
                                           class="w-full text-right px-2 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>

                                <!-- Harga Satuan -->
                                <td class="px-3 py-2 text-right">
                                    <input type="number" step="any" min="0" :name="'items[' + index + '][unit_price]'" x-model.number="item.unit_price" required
                                           class="w-full text-right px-2 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>

                                <!-- Diskon Item -->
                                <td class="px-3 py-2 text-right">
                                    <input type="number" step="any" min="0" :name="'items[' + index + '][discount]'" x-model.number="item.discount"
                                           class="w-full text-right px-2 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>

                                <!-- Subtotal per Item -->
                                <td class="px-3 py-2 text-right font-semibold text-slate-800 pt-3">
                                    <span x-text="formatRupiah(itemSubtotal(item))"></span>
                                </td>

                                <!-- Hapus Item -->
                                <td class="px-3 py-2 text-center pt-2">
                                    <button type="button" @click="removeItem(index)" class="text-slate-400 hover:text-rose-500 p-1 rounded transition-colors">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Kalkulasi Ringkasan Biaya -->
            <div class="pt-4 border-t border-slate-100 flex flex-col md:flex-row justify-between items-start gap-4">
                <button type="button" @click="addItem()" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-semibold rounded-lg transition-colors border border-slate-200">
                    <i class="fa-solid fa-plus"></i> Tambah Item Lainnya
                </button>

                <div class="w-full md:w-80 bg-slate-50 rounded-xl p-4 space-y-3 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal Item:</span>
                        <span class="font-semibold text-slate-800" x-text="formatRupiah(subtotal)"></span>
                    </div>

                    <div class="flex justify-between items-center text-slate-600">
                        <span>Diskon Faktur (Rp):</span>
                        <input type="number" step="any" min="0" name="discount_amount" x-model.number="discountAmount"
                               class="w-32 px-2 py-1 text-xs text-right border border-slate-200 rounded focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div class="flex justify-between items-center text-slate-600">
                        <span>PPN (%):</span>
                        <input type="number" step="any" min="0" x-model.number="taxRate"
                               class="w-20 px-2 py-1 text-xs text-right border border-slate-200 rounded focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div class="flex justify-between text-slate-600">
                        <span>Nominal PPN:</span>
                        <span class="font-medium text-slate-700" x-text="formatRupiah(taxAmount)"></span>
                        <input type="hidden" name="tax_amount" :value="taxAmount">
                    </div>

                    <div class="pt-2 border-t border-slate-200 flex justify-between items-center text-base font-bold text-slate-900">
                        <span>Grand Total:</span>
                        <span class="text-emerald-600" x-text="formatRupiah(grandTotal)"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Form -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('purchase-orders.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-lg transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm inline-flex items-center gap-2">
                <i class="fa-solid fa-paper-plane"></i> Simpan Purchase Order
            </button>
        </div>
    </form>
</x-app-layout>
