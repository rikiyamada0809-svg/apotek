<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Proses Retur Penjualan Pasien</h1>
        <nav class="flex text-xs text-slate-500 mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('sale-returns.index') }}" class="hover:text-slate-900">Retur Penjualan</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 font-medium">Buat Baru</span>
        </nav>
    </div>

    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif
    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif

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

    <!-- Search / Select Sale Invoice Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('sale-returns.create') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Cari Nomor Struk / Transaksi Penjualan <span class="text-rose-500">*</span></label>
                <p class="text-xs text-slate-500">Masukkan nomor struk (contoh: POS...) untuk memuat daftar obat yang dibeli konsumen.</p>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-96">
                <input type="text" name="invoice_number" value="{{ request('invoice_number', $invoiceNumber) }}" 
                       placeholder="No. Struk (Contoh: POS-2026...)" required
                       class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg transition-colors shrink-0">
                    <i class="fa-solid fa-magnifying-glass mr-1"></i> Cari
                </button>
            </div>
        </form>

        @if($selectedSale)
            <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                <div class="bg-slate-50 p-3 rounded-lg">
                    <span class="text-slate-500 block">No. Struk:</span>
                    <span class="font-bold font-mono text-slate-800 text-sm">{{ $selectedSale->invoice_number }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-lg">
                    <span class="text-slate-500 block">Pelanggan / Pasien:</span>
                    <span class="font-semibold text-slate-800 text-sm">{{ $selectedSale->customer->name ?? ($selectedSale->customer_name ?? 'Umum / Pasien Langsung') }}</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-lg">
                    <span class="text-slate-500 block">Tanggal Transaksi:</span>
                    <span class="font-semibold text-slate-800 text-sm">{{ \Carbon\Carbon::parse($selectedSale->sale_date)->translatedFormat('d F Y H:i') }}</span>
                    <span class="text-slate-400 block mt-0.5">({{ $daysPassed }} hari yang lalu)</span>
                </div>
                <div class="bg-slate-50 p-3 rounded-lg">
                    <span class="text-slate-500 block">Total Belanja Asli:</span>
                    <span class="font-bold text-emerald-600 text-sm font-mono">Rp {{ number_format($selectedSale->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>

            @if($isExpiredPolicy)
                <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500 text-xl shrink-0"></i>
                    <div>
                        <strong class="font-bold">Peringatan Batas Waktu Retur Melewati Batas Kebijakan:</strong>
                        <p class="mt-0.5">Transaksi ini telah berlangsung {{ $daysPassed }} hari yang lalu. Kebijakan apotek memperbolehkan pengembalian obat maksimal 7 hari dari tanggal pembelian.</p>
                    </div>
                </div>
            @endif
        @elseif(request('invoice_number'))
            <div class="mt-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg shrink-0"></i>
                <span>Nomor struk <strong>{{ request('invoice_number') }}</strong> tidak ditemukan dalam database penjualan.</span>
            </div>
        @endif
    </div>

    @if($selectedSale && ! $isExpiredPolicy)
        <form method="POST" action="{{ route('sale-returns.store') }}" 
              x-data="{
                  items: [
                      @foreach($selectedSale->items as $idx => $item)
                      {
                          enabled: false,
                          original_sale_item_id: '{{ $item->id }}',
                          product_id: '{{ $item->product_id }}',
                          product_name: '{{ addslashes($item->product->name ?? 'Obat') }}',
                          batch_id: '{{ $item->batch_id ?? '' }}',
                          batch_number: '{{ $item->batch->batch_number ?? '-' }}',
                          quantity: {{ $item->quantity }},
                          max_qty: {{ $item->quantity }},
                          unit_price: {{ $item->unit_price }},
                          item_condition: 'good',
                          return_to_stock: true,
                          notes: '',
                          onConditionChange() {
                              if (this.item_condition !== 'good') {
                                  this.return_to_stock = false;
                              } else {
                                  this.return_to_stock = true;
                              }
                          },
                          get subtotal() {
                              return this.enabled ? (parseFloat(this.quantity) || 0) * (parseFloat(this.unit_price) || 0) : 0;
                          }
                      },
                      @endforeach
                  ],
                  resolutionType: 'refund',
                  get grandTotal() {
                      return this.items.reduce((sum, item) => sum + item.subtotal, 0);
                  },
                  get activeItemsCount() {
                      return this.items.filter(i => i.enabled).length;
                  }
              }"
              class="space-y-6">
            @csrf
            <input type="hidden" name="original_sale_id" value="{{ $selectedSale->id }}">

            <!-- Header Info & Options -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-base font-semibold text-slate-800 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-emerald-600"></i> Detail Formulir Retur Pasien
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">No. Retur Penjualan</label>
                        <input type="text" value="{{ $returnNumber }}" readonly
                               class="w-full px-3 py-2 text-sm border border-slate-200 bg-slate-50 text-slate-600 rounded-lg font-mono font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Retur <span class="text-rose-500">*</span></label>
                        <input type="date" name="return_date" value="{{ old('return_date', now()->toDateString()) }}" required
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Solusi Pengembalian <span class="text-rose-500">*</span></label>
                        <select name="resolution_type" x-model="resolutionType" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 font-medium">
                            <option value="refund">Kembalikan Uang (Refund Uang)</option>
                            <option value="replacement">Tukar Obat Lain (Replacement)</option>
                        </select>
                    </div>

                    <div x-show="resolutionType === 'refund'">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Metode Refund Dana <span class="text-rose-500">*</span></label>
                        <select name="refund_method" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 font-medium">
                            <option value="cash">Tunai / Cash (Dari Kasir)</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="credit">Deposit Saldo Pasien</option>
                        </select>
                    </div>

                    <div :class="resolutionType === 'refund' ? 'md:col-span-2' : 'md:col-span-3'">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alasan Retur Pasien <span class="text-rose-500">*</span></label>
                        <input type="text" name="reason" value="{{ old('reason', 'Pasien salah beli obat / alergi / keluhan efek samping') }}" required
                               placeholder="Contoh: Pasien salah beli dosis obat, kemasan masih utuh dan tersegel"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan Tambahan (Opsional)</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Catatan apoteker / kasir..."
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Daftar Obat Yang Dibeli</h2>
                        <p class="text-xs text-slate-500">Centang baris obat yang dikembalikan oleh konsumen dan tentukan kondisinya.</p>
                    </div>
                    <div class="text-xs font-semibold text-slate-700">
                        Dipilih: <span x-text="activeItemsCount" class="text-emerald-600 font-bold"></span> item
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100 text-slate-600 text-xs uppercase font-semibold">
                            <tr>
                                <th class="p-3 text-center w-12">Pilih</th>
                                <th class="p-3 text-left">Nama Produk Obat</th>
                                <th class="p-3 text-left">Batch</th>
                                <th class="p-3 text-center w-28">Qty Retur</th>
                                <th class="p-3 text-right w-32">Harga Satuan</th>
                                <th class="p-3 text-center w-40">Kondisi Obat</th>
                                <th class="p-3 text-center w-36">Kembalikan ke Stok?</th>
                                <th class="p-3 text-right w-36">Subtotal Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in items" :key="index">
                                <tr :class="item.enabled ? 'bg-emerald-50/40' : 'opacity-60 bg-white'" class="transition-colors">
                                    <td class="p-3 text-center">
                                        <input type="checkbox" x-model="item.enabled" class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
                                    </td>
                                    <td class="p-3">
                                        <input type="hidden" :name="item.enabled ? `items[${index}][original_sale_item_id]` : ''" :value="item.original_sale_item_id">
                                        <input type="hidden" :name="item.enabled ? `items[${index}][product_id]` : ''" :value="item.product_id">
                                        <input type="hidden" :name="item.enabled ? `items[${index}][batch_id]` : ''" :value="item.batch_id">
                                        <span class="font-semibold text-slate-800" x-text="item.product_name"></span>
                                    </td>
                                    <td class="p-3 font-mono text-xs text-slate-600" x-text="item.batch_number"></td>
                                    <td class="p-3 text-center">
                                        <input type="number" step="any" min="0.01" :max="item.max_qty" 
                                               :name="item.enabled ? `items[${index}][quantity]` : ''" 
                                               x-model.number="item.quantity" :disabled="!item.enabled"
                                               class="w-20 text-center px-2 py-1 text-sm border border-slate-200 rounded font-semibold focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                        <span class="text-[10px] text-slate-400 block mt-0.5" x-text="'Maks: ' + item.max_qty"></span>
                                    </td>
                                    <td class="p-3 text-right font-mono text-slate-700">
                                        <input type="hidden" :name="item.enabled ? `items[${index}][unit_price]` : ''" :value="item.unit_price">
                                        <span x-text="'Rp ' + item.unit_price.toLocaleString('id-ID')"></span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <select :name="item.enabled ? `items[${index}][item_condition]` : ''" 
                                                x-model="item.item_condition" @change="item.onConditionChange()" :disabled="!item.enabled"
                                                class="text-xs border border-slate-200 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-medium w-full">
                                            <option value="good">Kondisi Baik (Segel Utuh)</option>
                                            <option value="damaged">Rusak / Cacat Fisik</option>
                                            <option value="expired">Kadaluarsa / ED</option>
                                        </select>
                                    </td>
                                    <td class="p-3 text-center">
                                        <input type="hidden" :name="item.enabled ? `items[${index}][return_to_stock]` : ''" :value="item.return_to_stock ? '1' : '0'">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs">
                                            <input type="checkbox" x-model="item.return_to_stock" :disabled="!item.enabled || item.item_condition !== 'good'"
                                                   class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                            <span :class="item.return_to_stock ? 'text-emerald-700 font-semibold' : 'text-slate-400'"
                                                  x-text="item.return_to_stock ? 'Restok' : 'Pemusnahan'"></span>
                                        </label>
                                    </td>
                                    <td class="p-3 text-right font-semibold font-mono text-slate-800">
                                        <span x-text="'Rp ' + item.subtotal.toLocaleString('id-ID')"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary -->
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-slate-500">
                        <i class="fa-solid fa-shield-halved text-emerald-600 mr-1"></i> Obat dalam kondisi <strong>Baik & Restok</strong> akan otomatis ditambahkan kembali ke stok apotek saat retur diselesaikan.
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="text-right">
                            <span class="text-xs text-slate-500 block">Total Nilai Pengembalian:</span>
                            <span class="text-lg font-bold text-rose-600 font-mono" x-text="'Rp ' + grandTotal.toLocaleString('id-ID')"></span>
                        </div>
                        <button type="submit" :disabled="activeItemsCount === 0"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-save"></i> Simpan Draft Retur
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @elseif(! $selectedSale)
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center text-2xl mx-auto mb-3">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <h3 class="text-base font-semibold text-slate-700">Masukkan Nomor Struk Penjualan Di Atas</h3>
            <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">Sistem akan memvalidasi tanggal transaksi (kebijakan maksimal 7 hari) dan memuat rincian obat yang dibeli.</p>
        </div>
    @endif
</x-app-layout>
