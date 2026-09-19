<x-app-layout>
    <div class="space-y-6" x-data="stockOpnameCounter()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">Perhitungan Fisik Stok Opname</h1>
                    <span class="px-2.5 py-0.5 text-xs font-mono bg-blue-100 text-blue-800 rounded-md font-bold">
                        {{ $stockOpname->opname_number }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Gudang: <span class="font-medium text-slate-800">{{ $stockOpname->warehouse?->name }}</span> • 
                    Cakupan: <span class="capitalize font-medium text-slate-800">{{ $stockOpname->scope }}</span> • 
                    Total: <span class="font-bold text-slate-800">{{ $stockOpname->items->count() }} Item</span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('stock-opnames.show', $stockOpname) }}" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-xl transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Detail
                </a>
            </div>
        </div>

        @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
        @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

        <!-- Barcode Scanner & Search Toolbar -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                <!-- Barcode Fast Scan Box -->
                <div class="sm:col-span-6 relative">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-barcode mr-1 text-emerald-600"></i> Scan Barcode / Input Cepat
                    </label>
                    <div class="relative">
                        <input type="text" x-ref="barcodeInput" x-model="barcodeScan" @keydown.enter.prevent="handleBarcodeScan()"
                               placeholder="Arahkan barcode scanner kemari atau tekan Enter..."
                               class="w-full pl-9 pr-24 py-2.5 bg-emerald-50/50 border border-emerald-300 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-inner">
                        <i class="fa-solid fa-expand text-emerald-500 absolute left-3 top-3 text-sm"></i>
                        <button type="button" @click="handleBarcodeScan()"
                                class="absolute right-1.5 top-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors">
                            Scan (+1)
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Scan barcode obat untuk otomatis menambah +1 pada jumlah fisik.</p>
                </div>

                <!-- Instant Search Filter -->
                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Cari Baris Item</label>
                    <div class="relative">
                        <input type="text" x-model="searchQuery" placeholder="Filter nama obat, kode, batch..."
                               class="w-full pl-8 pr-3 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-2.5 top-2.5 text-xs"></i>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="sm:col-span-2 text-right">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Status Selisih</span>
                    <div class="mt-1 flex items-center justify-end gap-2 text-xs font-bold">
                        <span class="text-rose-600" title="Item Kurang"><i class="fa-solid fa-arrow-down mr-0.5"></i> <span x-text="stats.deficit"></span></span>
                        <span class="text-slate-300">•</span>
                        <span class="text-emerald-600" title="Item Sesuai"><i class="fa-solid fa-check mr-0.5"></i> <span x-text="stats.matched"></span></span>
                        <span class="text-slate-300">•</span>
                        <span class="text-blue-600" title="Item Lebih"><i class="fa-solid fa-arrow-up mr-0.5"></i> <span x-text="stats.surplus"></span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Counting Form -->
        <form method="POST" action="{{ route('stock-opnames.count.update', $stockOpname) }}" id="opnameCountForm">
            @csrf
            <input type="hidden" name="action" id="formActionInput" value="save_draft">

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                                <th class="px-3 py-3 text-center w-10">#</th>
                                <th class="px-4 py-3">Obat / Produk</th>
                                <th class="px-3 py-3">Batch & Expired</th>
                                <th class="px-3 py-3">Lokasi Rak</th>
                                <th class="px-3 py-3 text-right">Qty Sistem</th>
                                <th class="px-3 py-3 text-center w-36">Qty Fisik</th>
                                <th class="px-3 py-3 text-center w-28">Selisih</th>
                                <th class="px-4 py-3">Catatan Item</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($stockOpname->items as $index => $item)
                                @php
                                    $barcode = $item->batch?->barcode ?: ($item->product?->barcode ?: $item->product?->product_code);
                                @endphp
                                <tr id="item-row-{{ $item->id }}" 
                                    class="hover:bg-slate-50/75 transition-colors"
                                    :class="{ 'bg-emerald-50/60 ring-2 ring-emerald-400': activeItemId === {{ $item->id }} }"
                                    x-show="matchesSearch('{{ strtolower($item->product?->name . ' ' . $item->product?->product_code . ' ' . $barcode . ' ' . ($item->batch?->batch_number ?? '')) }}')"
                                    data-barcode="{{ strtolower($barcode) }}"
                                    data-item-id="{{ $item->id }}">
                                    
                                    <td class="px-3 py-3 text-center text-xs text-slate-400">
                                        {{ $index + 1 }}
                                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-900">{{ $item->product?->name }}</div>
                                        <div class="text-xs text-slate-400 font-mono">
                                            {{ $item->product?->product_code }}
                                            @if($barcode)
                                                • <i class="fa-solid fa-barcode text-[10px] mr-0.5"></i> {{ $barcode }}
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-3 py-3 text-xs text-slate-700 font-mono">
                                        {{ $item->batch?->batch_number ?? '-' }}
                                        @if($item->batch?->expiry_date)
                                            <div class="text-[10px] text-slate-400">Exp: {{ $item->batch->expiry_date->format('d/m/Y') }}</div>
                                        @endif
                                    </td>

                                    <td class="px-3 py-3 text-xs text-slate-600">
                                        {{ $item->storageLocation?->name ?? '-' }}
                                    </td>

                                    <td class="px-3 py-3 text-right font-medium text-slate-700">
                                        {{ number_format((float) $item->system_qty, 2) }}
                                    </td>

                                    <td class="px-3 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" @click="decrementQty({{ $item->id }})"
                                                    class="w-7 h-7 flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-bold transition-colors">
                                                -
                                            </button>
                                            <input type="number" step="any" min="0" 
                                                   id="input-qty-{{ $item->id }}"
                                                   name="items[{{ $index }}][physical_qty]" 
                                                   x-model.number="items[{{ $item->id }}].physical_qty"
                                                   @input="recalcDiscrepancy({{ $item->id }})"
                                                   class="w-20 text-center py-1.5 text-sm font-bold border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                                            <button type="button" @click="incrementQty({{ $item->id }})"
                                                    class="w-7 h-7 flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-bold transition-colors">
                                                +
                                            </button>
                                        </div>
                                    </td>

                                    <td class="px-3 py-3 text-center">
                                        <template x-if="items[{{ $item->id }}].discrepancy > 0">
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded-md">
                                                +<span x-text="formatNumber(items[{{ $item->id }}].discrepancy)"></span>
                                            </span>
                                        </template>
                                        <template x-if="items[{{ $item->id }}].discrepancy < 0">
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold bg-rose-100 text-rose-700 rounded-md">
                                                <span x-text="formatNumber(items[{{ $item->id }}].discrepancy)"></span>
                                            </span>
                                        </template>
                                        <template x-if="items[{{ $item->id }}].discrepancy == 0">
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-emerald-600">
                                                <i class="fa-solid fa-check mr-1"></i> 0.00
                                            </span>
                                        </template>
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="text" name="items[{{ $index }}][notes]" value="{{ $item->notes }}"
                                               placeholder="Catatan..."
                                               class="w-full px-2.5 py-1 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Sticky Footer Toolbar -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sticky bottom-0 z-10">
                    <div class="text-xs text-slate-600">
                        Total: <span class="font-bold text-slate-900">{{ $stockOpname->items->count() }} Item</span> • 
                        Item Selisih: <span class="font-bold text-purple-700" x-text="stats.discrepancies"></span> item
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" @click="setAction('save_draft')"
                                class="px-5 py-2.5 bg-white hover:bg-slate-100 text-slate-700 text-sm font-semibold border border-slate-200 rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Draft
                        </button>
                        <button type="submit" @click="setAction('submit')"
                                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
                            <i class="fa-solid fa-paper-plane"></i> Selesai & Ajukan (Submit)
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function stockOpnameCounter() {
            return {
                barcodeScan: '',
                searchQuery: '',
                activeItemId: null,
                items: {
                    @foreach($stockOpname->items as $item)
                        {{ $item->id }}: {
                            system_qty: {{ (float) $item->system_qty }},
                            physical_qty: {{ (float) $item->physical_qty }},
                            discrepancy: {{ (float) $item->discrepancy }},
                            barcode: '{{ strtolower($item->batch?->barcode ?: ($item->product?->barcode ?: $item->product?->product_code)) }}',
                        },
                    @endforeach
                },
                stats: {
                    matched: 0,
                    deficit: 0,
                    surplus: 0,
                    discrepancies: 0,
                },
                init() {
                    this.updateStats();
                    this.$nextTick(() => {
                        if (this.$refs.barcodeInput) {
                            this.$refs.barcodeInput.focus();
                        }
                    });
                },
                matchesSearch(text) {
                    if (!this.searchQuery) return true;
                    return text.includes(this.searchQuery.toLowerCase());
                },
                recalcDiscrepancy(id) {
                    const item = this.items[id];
                    if (item) {
                        item.discrepancy = Number((item.physical_qty - item.system_qty).toFixed(2));
                    }
                    this.updateStats();
                },
                incrementQty(id) {
                    if (this.items[id]) {
                        this.items[id].physical_qty = Number((this.items[id].physical_qty + 1).toFixed(2));
                        this.recalcDiscrepancy(id);
                    }
                },
                decrementQty(id) {
                    if (this.items[id] && this.items[id].physical_qty > 0) {
                        this.items[id].physical_qty = Number((this.items[id].physical_qty - 1).toFixed(2));
                        this.recalcDiscrepancy(id);
                    }
                },
                handleBarcodeScan() {
                    const code = (this.barcodeScan || '').trim().toLowerCase();
                    if (!code) return;

                    let foundId = null;
                    for (const [id, data] of Object.entries(this.items)) {
                        if (data.barcode === code) {
                            foundId = Number(id);
                            break;
                        }
                    }

                    if (foundId) {
                        this.incrementQty(foundId);
                        this.activeItemId = foundId;
                        const row = document.getElementById('item-row-' + foundId);
                        if (row) {
                            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        this.barcodeScan = '';
                        setTimeout(() => { this.activeItemId = null; }, 1500);
                    } else {
                        alert('Produk atau Barcode "' + code + '" tidak ditemukan dalam daftar opname ini.');
                        this.barcodeScan = '';
                    }

                    if (this.$refs.barcodeInput) {
                        this.$refs.barcodeInput.focus();
                    }
                },
                updateStats() {
                    let m = 0, d = 0, s = 0, disc = 0;
                    for (const item of Object.values(this.items)) {
                        if (item.discrepancy === 0) {
                            m++;
                        } else if (item.discrepancy < 0) {
                            d++;
                            disc++;
                        } else {
                            s++;
                            disc++;
                        }
                    }
                    this.stats.matched = m;
                    this.stats.deficit = d;
                    this.stats.surplus = s;
                    this.stats.discrepancies = disc;
                },
                formatNumber(val) {
                    return Number(val).toFixed(2);
                },
                setAction(action) {
                    document.getElementById('formActionInput').value = action;
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
