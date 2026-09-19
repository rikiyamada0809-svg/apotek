<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Buat Transfer Stok Multi-Gudang</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('stock-transfers.index') }}" class="hover:text-slate-900">Transfer Stok</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Buat Baru</span>
            </nav>
        </div>
        <a href="{{ route('stock-transfers.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif
    @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
            <div class="font-bold mb-1">Terdapat beberapa kesalahan:</div>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('stock-transfers.store') }}" id="transferForm">
        @csrf

        <!-- Card 1: Informasi Transfer -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
            <h2 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-truck-ramp-box text-emerald-600"></i> Informasi Gudang & Tanggal
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Gudang Asal (Pengirim) <span class="text-rose-500">*</span></label>
                    <select name="from_warehouse_id" id="fromWarehouse" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Pilih Gudang Asal...</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('from_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} ({{ $wh->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Gudang Tujuan (Penerima) <span class="text-rose-500">*</span></label>
                    <select name="to_warehouse_id" id="toWarehouse" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Pilih Gudang Tujuan...</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('to_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} ({{ $wh->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Transfer <span class="text-rose-500">*</span></label>
                    <input type="date" name="transfer_date" value="{{ old('transfer_date', now()->toDateString()) }}" required
                           class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Alasan Transfer</label>
                    <textarea name="notes" rows="2" placeholder="Tuliskan catatan atau instruksi pengiriman transfer stok..."
                              class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Card 2: Items to Transfer -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-emerald-600"></i> Daftar Obat yang Ditransfer
                </h2>
                <button type="button" id="addRowBtn" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-lg transition-colors">
                    <i class="fa-solid fa-plus"></i> Tambah Baris Obat
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="itemsTable">
                    <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3 min-w-[240px]">Produk Obat</th>
                            <th class="py-2.5 px-3 min-w-[200px]">Batch & Tanggal Kadaluarsa</th>
                            <th class="py-2.5 px-3 w-28 text-center">Stok Tersedia</th>
                            <th class="py-2.5 px-3 w-32 text-center">Qty Transfer</th>
                            <th class="py-2.5 px-3 min-w-[160px]">Catatan Item</th>
                            <th class="py-2.5 px-3 w-12 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="itemsTableBody">
                        <!-- Dynamic Rows will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('stock-transfers.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl shadow-md transition-colors flex items-center gap-2">
                <i class="fa-solid fa-check"></i> Simpan Draft Transfer
            </button>
        </div>
    </form>

    <!-- Product Data & JS Logic -->
    <script>
        const productsData = {!! json_encode($productsJson) !!};

        let rowIndex = 0;
        const fromWarehouseSelect = document.getElementById('fromWarehouse');
        const itemsTableBody = document.getElementById('itemsTableBody');
        const addRowBtn = document.getElementById('addRowBtn');

        function createRow() {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/50 transition-colors item-row';
            tr.id = `row-${rowIndex}`;

            let productOptions = '<option value="">Pilih Obat...</option>';
            productsData.forEach(p => {
                productOptions += `<option value="${p.id}">${p.name} (${p.code})</option>`;
            });

            tr.innerHTML = `
                <td class="py-2.5 px-3">
                    <select name="items[` + rowIndex + `][product_id]" class="product-select w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded focus:ring-1 focus:ring-emerald-500" required>
                        ` + productOptions + `
                    </select>
                </td>
                <td class="py-2.5 px-3">
                    <select name="items[` + rowIndex + `][batch_id]" class="batch-select w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded focus:ring-1 focus:ring-emerald-500" required disabled>
                        <option value="">Pilih batch...</option>
                    </select>
                </td>
                <td class="py-2.5 px-3 text-center">
                    <span class="available-stock-text font-bold text-xs text-slate-700">-</span>
                </td>
                <td class="py-2.5 px-3 text-center">
                    <input type="number" step="0.01" min="0.01" name="items[` + rowIndex + `][quantity]" value="1" required
                           class="qty-input w-24 px-2 py-1 text-center font-bold text-slate-900 border border-slate-300 rounded focus:ring-1 focus:ring-emerald-500 text-xs">
                </td>
                <td class="py-2.5 px-3">
                    <input type="text" name="items[` + rowIndex + `][notes]" placeholder="Opsional"
                           class="w-full px-2 py-1 text-xs border border-slate-200 rounded focus:ring-1 focus:ring-emerald-500">
                </td>
                <td class="py-2.5 px-3 text-center">
                    <button type="button" class="delete-row-btn text-rose-500 hover:text-rose-700 p-1">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </button>
                </td>
            `;

            itemsTableBody.appendChild(tr);

            const productSelect = tr.querySelector('.product-select');
            const batchSelect = tr.querySelector('.batch-select');
            const availableStockText = tr.querySelector('.available-stock-text');
            const qtyInput = tr.querySelector('.qty-input');
            const deleteBtn = tr.querySelector('.delete-row-btn');

            deleteBtn.addEventListener('click', function () {
                tr.remove();
            });

            productSelect.addEventListener('change', function () {
                loadBatchesForProduct(this.value, batchSelect, availableStockText);
            });

            batchSelect.addEventListener('change', function () {
                const selectedOpt = batchSelect.options[batchSelect.selectedIndex];
                const available = selectedOpt ? parseFloat(selectedOpt.dataset.available || 0) : 0;
                availableStockText.textContent = available;
                qtyInput.max = available;
            });

            rowIndex++;
        }

        async function loadBatchesForProduct(productId, batchSelect, availableStockText) {
            const warehouseId = fromWarehouseSelect.value;
            if (!warehouseId || !productId) {
                batchSelect.innerHTML = '<option value="">Pilih batch...</option>';
                batchSelect.disabled = true;
                availableStockText.textContent = '-';
                return;
            }

            batchSelect.disabled = true;
            batchSelect.innerHTML = '<option value="">Memuat batch...</option>';

            try {
                const res = await fetch(`{{ route('stock-transfers.warehouse-stock') }}?warehouse_id=${warehouseId}&product_id=${productId}`);
                const data = await res.json();

                batchSelect.innerHTML = '<option value="">Pilih Batch...</option>';
                if (data.stocks && data.stocks.length > 0) {
                    data.stocks.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.batch_id;
                        opt.dataset.available = s.available_qty;
                        opt.textContent = `${s.batch_number} (Exp: ${s.expiry_date}) - Stok: ${s.available_qty}`;
                        batchSelect.appendChild(opt);
                    });
                    batchSelect.disabled = false;
                } else {
                    batchSelect.innerHTML = '<option value="">Tidak ada stok di gudang ini</option>';
                    batchSelect.disabled = true;
                }
            } catch (err) {
                console.error(err);
                batchSelect.innerHTML = '<option value="">Gagal memuat batch</option>';
            }
        }

        fromWarehouseSelect.addEventListener('change', function () {
            // Re-fetch batches for all existing rows
            document.querySelectorAll('.item-row').forEach(row => {
                const pSelect = row.querySelector('.product-select');
                const bSelect = row.querySelector('.batch-select');
                const aText = row.querySelector('.available-stock-text');
                if (pSelect.value) {
                    loadBatchesForProduct(pSelect.value, bSelect, aText);
                }
            });
        });

        addRowBtn.addEventListener('click', createRow);

        document.addEventListener('DOMContentLoaded', function () {
            createRow(); // initial first row
        });
    </script>
</x-app-layout>
