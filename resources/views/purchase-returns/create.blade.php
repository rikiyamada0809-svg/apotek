<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Buat Retur Pembelian</h1>
        <nav class="flex text-xs text-slate-500 mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('purchase-returns.index') }}" class="hover:text-slate-900">Retur Pembelian</a>
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

    <!-- PO Selection Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Referensi Purchase Order (PO) <span class="text-rose-500">*</span></label>
                <p class="text-xs text-slate-500">Pilih PO yang barangnya ingin diretur ke supplier.</p>
            </div>
            <div class="w-full sm:w-80">
                <select id="po_selector" onchange="if(this.value) window.location.href = '{{ route('purchase-returns.create') }}?po_id=' + this.value"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Purchase Order --</option>
                    @foreach($purchaseOrders as $po)
                        <option value="{{ $po->id }}" @selected(optional($selectedPo)->id == $po->id)>
                            {{ $po->po_number }} - {{ $po->supplier->name ?? 'Supplier' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($selectedPo)
            <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div class="bg-slate-50 p-3 rounded-lg">
                    <span class="text-slate-500 block">Supplier:</span>
                    <span class="font-semibold text-slate-800 text-sm">{{ $selectedPo->supplier->name ?? '-' }}</span>
                    <span class="text-slate-500 block mt-0.5">{{ $selectedPo->supplier->phone ?? '' }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-lg">
                    <span class="text-slate-500 block">Gudang Penyimpanan:</span>
                    <span class="font-semibold text-slate-800 text-sm">{{ $selectedPo->warehouse->name ?? '-' }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-lg">
                    <span class="text-slate-500 block">Tanggal PO:</span>
                    <span class="font-semibold text-slate-800 text-sm">{{ \Carbon\Carbon::parse($selectedPo->order_date)->translatedFormat('d F Y') }}</span>
                    <span class="text-slate-500 block mt-0.5">Status PO: <span class="uppercase font-bold text-emerald-600">{{ $selectedPo->status }}</span></span>
                </div>
            </div>
        @endif
    </div>

    @if($selectedPo)
        @php
            $availableBatches = collect();
            if ($selectedPo->goodsReceipts) {
                foreach ($selectedPo->goodsReceipts as $receipt) {
                    if ($receipt->items) {
                        foreach ($receipt->items as $rcptItem) {
                            if ($rcptItem->batch) {
                                $availableBatches->push($rcptItem->batch);
                            }
                        }
                    }
                }
            }
            $availableBatches = $availableBatches->unique('id');
        @endphp

        <form method="POST" action="{{ route('purchase-returns.store') }}" 
              x-data="{
                  items: [
                      @foreach($selectedPo->items as $idx => $item)
                      {
                          enabled: false,
                          product_id: '{{ $item->product_id }}',
                          product_name: '{{ addslashes($item->product->name ?? 'Obat') }}',
                          batch_id: '{{ $availableBatches->firstWhere('product_id', $item->product_id)->id ?? '' }}',
                          quantity: {{ $item->quantity_ordered ?? $item->quantity ?? 1 }},
                          max_qty: {{ $item->quantity_ordered ?? $item->quantity ?? 1 }},
                          unit_price: {{ $item->unit_price }},
                          unit_name: '{{ $item->unit->name ?? ($item->product->baseUnit->name ?? 'Satuan') }}',
                          reason: 'Barang rusak / cacat fisik',
                          notes: '',
                          get subtotal() {
                              return this.enabled ? (parseFloat(this.quantity) || 0) * (parseFloat(this.unit_price) || 0) : 0;
                          }
                      },
                      @endforeach
                  ],
                  get grandTotal() {
                      return this.items.reduce((sum, item) => sum + item.subtotal, 0);
                  },
                  get activeItemsCount() {
                      return this.items.filter(i => i.enabled).length;
                  }
              }"
              class="space-y-6">
            @csrf
            <input type="hidden" name="po_id" value="{{ $selectedPo->id }}">

            <!-- Header Info & Options -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-base font-semibold text-slate-800 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-emerald-600"></i> Informasi Retur
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">No. Retur</label>
                        <input type="text" value="{{ $returnNumber }}" readonly
                               class="w-full px-3 py-2 text-sm border border-slate-200 bg-slate-50 text-slate-600 rounded-lg font-mono font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Retur <span class="text-rose-500">*</span></label>
                        <input type="date" name="return_date" value="{{ old('return_date', now()->toDateString()) }}" required
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Bentuk Kompensasi / Solusi <span class="text-rose-500">*</span></label>
                        <select name="resolution_type" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 font-medium">
                            <option value="replacement" @selected(old('resolution_type') === 'replacement')>Penggantian Barang Baru (Replacement)</option>
                            <option value="credit_note" @selected(old('resolution_type') === 'credit_note')>Nota Kredit / Potong Faktur Tagihan (Credit Note)</option>
                            <option value="refund" @selected(old('resolution_type') === 'refund')>Pengembalian Dana Kas (Cash Refund)</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alasan Utama Retur <span class="text-rose-500">*</span></label>
                        <input type="text" name="reason" value="{{ old('reason', 'Barang rusak / cacat / tidak sesuai pesanan') }}" required
                               placeholder="Contoh: Kemasan pecah saat pengiriman, atau mendekati masa ED"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan Tambahan</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Keterangan opsional untuk supplier..."
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            <!-- Items to Return -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Daftar Obat Dari PO #{{ $selectedPo->po_number }}</h2>
                        <p class="text-xs text-slate-500">Centang baris obat yang akan diretur ke supplier.</p>
                    </div>
                    <div class="text-xs font-semibold text-slate-700">
                        Dipilih: <span x-text="activeItemsCount" class="text-emerald-600 font-bold"></span> obat
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100 text-slate-600 text-xs uppercase font-semibold">
                            <tr>
                                <th class="p-3 text-center w-12">Pilih</th>
                                <th class="p-3 text-left">Nama Produk Obat</th>
                                <th class="p-3 text-left">Batch Obat</th>
                                <th class="p-3 text-center w-28">Qty Retur</th>
                                <th class="p-3 text-right w-36">Harga Satuan (Rp)</th>
                                <th class="p-3 text-right w-36">Subtotal (Rp)</th>
                                <th class="p-3 text-left">Alasan Spesifik</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in items" :key="index">
                                <tr :class="item.enabled ? 'bg-emerald-50/40' : 'opacity-60 bg-white'" class="transition-colors">
                                    <td class="p-3 text-center">
                                        <input type="checkbox" x-model="item.enabled" class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
                                    </td>
                                    <td class="p-3">
                                        <input type="hidden" :name="item.enabled ? `items[${index}][product_id]` : ''" :value="item.product_id">
                                        <span class="font-semibold text-slate-800" x-text="item.product_name"></span>
                                        <span class="text-xs text-slate-500 block" x-text="'Satuan: ' + item.unit_name"></span>
                                    </td>
                                    <td class="p-3">
                                        <select :name="item.enabled ? `items[${index}][batch_id]` : ''" x-model="item.batch_id" :disabled="!item.enabled"
                                                class="text-xs border border-slate-200 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500 w-full">
                                            <option value="">-- Tanpa Batch / Default --</option>
                                            @foreach($availableBatches as $b)
                                                <option value="{{ $b->id }}">
                                                    {{ $b->batch_number }} (ED: {{ \Carbon\Carbon::parse($b->expiry_date)->format('d/m/Y') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-3 text-center">
                                        <input type="number" step="any" min="0.01" :max="item.max_qty" 
                                               :name="item.enabled ? `items[${index}][quantity]` : ''" 
                                               x-model.number="item.quantity" :disabled="!item.enabled"
                                               class="w-24 text-center px-2 py-1 text-sm border border-slate-200 rounded font-semibold focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                        <span class="text-[10px] text-slate-400 block mt-0.5" x-text="'Max: ' + item.max_qty"></span>
                                    </td>
                                    <td class="p-3 text-right">
                                        <input type="number" step="any" min="0" 
                                               :name="item.enabled ? `items[${index}][unit_price]` : ''" 
                                               x-model.number="item.unit_price" :disabled="!item.enabled"
                                               class="w-28 text-right px-2 py-1 text-sm border border-slate-200 rounded font-mono focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                    </td>
                                    <td class="p-3 text-right font-semibold font-mono text-slate-800">
                                        <span x-text="'Rp ' + item.subtotal.toLocaleString('id-ID')"></span>
                                    </td>
                                    <td class="p-3">
                                        <input type="text" :name="item.enabled ? `items[${index}][reason]` : ''" 
                                               x-model="item.reason" :disabled="!item.enabled"
                                               placeholder="Alasan retur item..."
                                               class="w-full text-xs px-2 py-1 border border-slate-200 rounded focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary -->
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-slate-500">
                        <i class="fa-solid fa-circle-info text-blue-500 mr-1"></i> Retur akan tersimpan dalam status <strong>Draft</strong>. Anda dapat mereview dan meminta persetujuan sebelum mengurangi stok gudang.
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="text-right">
                            <span class="text-xs text-slate-500 block">Total Estimasi Retur:</span>
                            <span class="text-lg font-bold text-slate-900 font-mono" x-text="'Rp ' + grandTotal.toLocaleString('id-ID')"></span>
                        </div>
                        <button type="submit" :disabled="activeItemsCount === 0"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-save"></i> Simpan Draft Retur
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center text-2xl mx-auto mb-3">
                <i class="fa-solid fa-hand-pointer"></i>
            </div>
            <h3 class="text-base font-semibold text-slate-700">Silakan Pilih Purchase Order (PO) Di Atas</h3>
            <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">Sistem akan memuat daftar obat, nomor batch yang diterima, dan harga pembelian untuk diproses retur.</p>
        </div>
    @endif
</x-app-layout>
