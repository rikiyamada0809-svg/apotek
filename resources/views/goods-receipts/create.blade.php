<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Penerimaan Barang dari PO: {{ $purchaseOrder->po_number }}</h1>
        <nav class="flex text-xs text-slate-500 mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('goods-receipts.index') }}" class="hover:text-slate-900">Penerimaan Barang</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 font-medium">Input Penerimaan</span>
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

    <form method="POST" action="{{ route('goods-receipts.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="po_id" value="{{ $purchaseOrder->id }}">

        <!-- 1. Informasi Header Surat Jalan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
            <h2 class="text-base font-semibold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-truck-ramp-box text-emerald-600"></i> Informasi Dokumen Penerimaan
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Supplier / Distributor</label>
                    <div class="text-sm font-bold text-slate-800">{{ $purchaseOrder->supplier->name ?? '-' }}</div>
                    <div class="text-xs text-slate-400">{{ $purchaseOrder->supplier->phone ?? '' }}</div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Gudang Penerimaan</label>
                    <div class="text-sm font-bold text-slate-800">{{ $purchaseOrder->warehouse->name ?? '-' }}</div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Penerimaan <span class="text-rose-500">*</span></label>
                    <input type="date" name="receipt_date" value="{{ old('receipt_date', now()->toDateString()) }}" required
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">No. Surat Jalan Supplier</label>
                    <input type="text" name="delivery_note_number" value="{{ old('delivery_note_number') }}" placeholder="Contoh: SJ-2026/09/001"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan Penerimaan</label>
                <textarea name="notes" rows="2" placeholder="Keterangan kondisi fisik pengiriman atau catatan gudang..."
                          class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- 2. Rincian Obat & Input Batch/Expired -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-emerald-600"></i> Verifikasi Fisik Item Obat, No. Batch & Expired
                </h2>
                <span class="text-xs text-slate-500">Isi qty 0 jika obat belum datang pada pengiriman ini</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs">
                        <tr>
                            <th class="px-3 py-2.5 text-left font-semibold">Nama Produk & Satuan</th>
                            <th class="px-3 py-2.5 text-center font-semibold w-24">Order / Sisa</th>
                            <th class="px-3 py-2.5 text-left font-semibold w-36">No. Batch <span class="text-rose-500">*</span></th>
                            <th class="px-3 py-2.5 text-left font-semibold w-36">Tgl Expired <span class="text-rose-500">*</span></th>
                            <th class="px-3 py-2.5 text-right font-semibold w-28">Qty Diterima <span class="text-rose-500">*</span></th>
                            <th class="px-3 py-2.5 text-left font-semibold w-36">Lokasi Rak</th>
                            <th class="px-3 py-2.5 text-center font-semibold w-28">Kondisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($purchaseOrder->items as $index => $item)
                            @php
                                $outstanding = $item->outstanding_quantity;
                            @endphp
                            <tr class="align-top {{ $outstanding <= 0 ? 'bg-slate-50/50 opacity-60' : '' }}">
                                <input type="hidden" name="items[{{ $index }}][po_item_id]" value="{{ $item->id }}">
                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">

                                <td class="px-3 py-2.5">
                                    <div class="font-semibold text-slate-800">{{ $item->product->name }}</div>
                                    <div class="text-xs text-slate-500">Satuan: <strong>{{ $item->unit->name }}</strong></div>
                                    @if($outstanding <= 0)
                                        <span class="inline-block px-2 py-0.5 mt-1 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800">Sudah Lengkap</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2.5 text-center text-xs">
                                    <div class="text-slate-600">Pesanan: {{ (float) $item->quantity_ordered }}</div>
                                    <div class="font-bold {{ $outstanding > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                        Sisa: {{ $outstanding }}
                                    </div>
                                </td>

                                <!-- No Batch -->
                                <td class="px-3 py-2.5">
                                    <input type="text" name="items[{{ $index }}][batch_number]"
                                           value="{{ old("items.{$index}.batch_number", 'BCH-'.now()->format('ymd').'-'.($index + 1)) }}"
                                           placeholder="Batch No..." required
                                           class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 font-mono">
                                </td>

                                <!-- Tgl Expired -->
                                <td class="px-3 py-2.5">
                                    <input type="date" name="items[{{ $index }}][expiry_date]"
                                           value="{{ old("items.{$index}.expiry_date", now()->addYears(2)->format('Y-m-d')) }}"
                                           required min="{{ now()->addDay()->format('Y-m-d') }}"
                                           class="w-full px-2 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                </td>

                                <!-- Qty Diterima -->
                                <td class="px-3 py-2.5 text-right">
                                    <input type="number" step="any" min="0" max="{{ $outstanding }}"
                                           name="items[{{ $index }}][quantity_received]"
                                           value="{{ old("items.{$index}.quantity_received", $outstanding) }}"
                                           required
                                           class="w-full text-right px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 font-semibold text-slate-800">
                                </td>

                                <!-- Lokasi Rak -->
                                <td class="px-3 py-2.5">
                                    <select name="items[{{ $index }}][storage_location_id]"
                                            class="w-full px-2 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                        <option value="">-- Lokasi --</option>
                                        @foreach($storageLocations as $loc)
                                            <option value="{{ $loc->id }}" @selected(old("items.{$index}.storage_location_id") == $loc->id)>
                                                {{ $loc->code }} ({{ $loc->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <!-- Kondisi -->
                                <td class="px-3 py-2.5 text-center">
                                    <select name="items[{{ $index }}][condition]"
                                            class="w-full px-2 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                        <option value="good" @selected(old("items.{$index}.condition") === 'good')>Baik / Bagus</option>
                                        <option value="damaged" @selected(old("items.{$index}.condition") === 'damaged')>Rusak / Cacat</option>
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-lg transition-colors">
                Batal
            </a>
            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin memproses penerimaan barang ini? Stok gudang akan otomatis bertambah.')"
                    class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm inline-flex items-center gap-2">
                <i class="fa-solid fa-check-double"></i> Konfirmasi Penerimaan & Update Stok
            </button>
        </div>
    </form>
</x-app-layout>
