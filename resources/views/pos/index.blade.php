<x-app-layout>
    <div x-data="posApp()" x-init="init()" class="h-[calc(100vh-5rem)] flex flex-col -m-4 sm:-m-6 lg:-m-8 overflow-hidden bg-slate-100">

        <!-- Top POS Bar -->
        <div class="h-14 bg-white border-b border-slate-200 px-4 flex items-center justify-between shrink-0 z-10 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                    <i class="fa-solid fa-cash-register"></i>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-slate-900 leading-tight">ApotekCare POS Kasir</h1>
                    <div class="text-[11px] text-slate-500 flex items-center gap-2">
                        <span>Shift #{{ $activeShift->id }}</span>
                        <span>&bull;</span>
                        <span class="text-emerald-700 font-semibold">{{ $activeShift->warehouse->name ?? 'Gudang Utama' }}</span>
                    </div>
                </div>
            </div>

            <!-- Central shortcut hint -->
            <div class="hidden md:flex items-center gap-4 text-xs text-slate-500">
                <span class="flex items-center gap-1.5"><kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono text-[10px] text-slate-700">F2</kbd> Cari Obat</span>
                <span class="flex items-center gap-1.5"><kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono text-[10px] text-slate-700">F4</kbd> Hold</span>
                <span class="flex items-center gap-1.5"><kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono text-[10px] text-slate-700">F9</kbd> Bayar</span>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-2">
                <!-- Held Orders Button -->
                <button type="button" @click="showHeldModal = true" class="relative inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-semibold rounded-lg transition-colors border border-amber-200">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Pending / Hold</span>
                    <template x-if="heldOrders.length > 0">
                        <span class="w-5 h-5 rounded-full bg-amber-500 text-white text-[10px] flex items-center justify-center font-bold" x-text="heldOrders.length"></span>
                    </template>
                </button>

                <!-- Riwayat Hari Ini -->
                <a href="{{ route('pos.history') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                    <i class="fa-solid fa-receipt"></i>
                    <span class="hidden sm:inline">Riwayat Penjualan</span>
                </a>

                <!-- Tutup / Rekap Shift -->
                <a href="{{ route('shifts.show', $activeShift) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded-lg transition-colors border border-rose-200">
                    <i class="fa-solid fa-door-closed"></i>
                    <span class="hidden sm:inline">Tutup Shift</span>
                </a>
            </div>
        </div>

        <!-- Main Body: Two Columns -->
        <div class="flex-1 flex flex-col md:flex-row min-h-0 overflow-hidden">

            <!-- LEFT COLUMN: Product Search & Catalog -->
            <div class="w-full md:w-7/12 lg:w-3/5 p-4 flex flex-col min-h-0 border-r border-slate-200 bg-slate-50/50">
                
                <!-- Search & Barcode Scan Input -->
                <div class="mb-3 space-y-2">
                    <div class="relative">
                        <i class="fa-solid fa-barcode text-slate-400 absolute left-3.5 top-3 text-base"></i>
                        <input type="text" x-ref="searchInput" x-model="searchQuery" @keydown.enter="handleSearchEnter()"
                               placeholder="Scan Barcode atau ketik Nama Obat / Kode Produk (Tekan Enter)..."
                               class="w-full pl-11 pr-24 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm">
                        <span class="absolute right-3 top-2.5 text-[10px] font-bold text-slate-400 border border-slate-200 bg-slate-50 px-1.5 py-0.5 rounded">F2 / Enter</span>
                    </div>

                    <!-- Category filter chips -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                        <button type="button" @click="selectedCategory = 'all'"
                                :class="selectedCategory === 'all' ? 'bg-emerald-600 text-white font-semibold' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                                class="px-3 py-1 rounded-lg shrink-0 transition-colors">
                            Semua
                        </button>
                        <template x-for="cat in categories" :key="cat">
                            <button type="button" @click="selectedCategory = cat"
                                    :class="selectedCategory === cat ? 'bg-emerald-600 text-white font-semibold' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                                    class="px-3 py-1 rounded-lg shrink-0 transition-colors" x-text="cat">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Product Catalog Grid -->
                <div class="flex-1 overflow-y-auto pr-1">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div @click="addToCart(product)"
                                 class="bg-white rounded-xl border border-slate-200 p-3 hover:border-emerald-500 hover:shadow-md transition-all cursor-pointer flex flex-col justify-between group relative overflow-hidden">
                                
                                <!-- Top: Category & Prescription badge -->
                                <div class="flex items-start justify-between gap-1 mb-1.5">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase truncate" x-text="product.category_name"></span>
                                    <template x-if="product.requires_prescription">
                                        <span class="px-1.5 py-0.2 bg-rose-100 text-rose-800 text-[9px] font-extrabold rounded" title="Obat Keras">Resep</span>
                                    </template>
                                </div>

                                <!-- Middle: Name & Code -->
                                <div class="mb-2">
                                    <h3 class="font-bold text-slate-900 text-xs line-clamp-2 leading-tight group-hover:text-emerald-700 transition-colors" x-text="product.name"></h3>
                                    <span class="text-[10px] text-slate-400 font-mono" x-text="product.product_code || '-'"></span>
                                </div>

                                <!-- Bottom: Price & Stock -->
                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between mt-auto text-xs">
                                    <span class="font-extrabold text-emerald-600" x-text="formatRupiah(getDefaultPrice(product))"></span>
                                    <span :class="product.available_stock > 0 ? 'text-slate-500' : 'text-rose-600 font-bold'"
                                          class="text-[10px]">
                                        Stok: <span x-text="product.available_stock"></span>
                                    </span>
                                </div>

                                <!-- Add overlay hover feedback -->
                                <div class="absolute inset-0 bg-emerald-600/5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
                            </div>
                        </template>
                    </div>

                    <template x-if="filteredProducts.length === 0">
                        <div class="h-64 flex flex-col items-center justify-center text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-3xl mb-2"></i>
                            <p class="text-xs">Tidak ditemukan obat yang sesuai dengan pencarian.</p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- RIGHT COLUMN: Cart & Checkout -->
            <div class="w-full md:w-5/12 lg:w-2/5 flex flex-col min-h-0 bg-white shadow-xl">
                
                <!-- Customer Selection Bar -->
                <div class="p-3 border-b border-slate-200 bg-slate-50/70 flex items-center justify-between gap-2 shrink-0">
                    <div class="flex items-center gap-2 flex-1">
                        <i class="fa-solid fa-user text-slate-400 text-xs"></i>
                        <select x-model="selectedCustomerId" @change="onCustomerChange()"
                                class="w-full py-1 px-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="">Pelanggan Umum (Non-Member)</option>
                            <template x-for="c in customersData" :key="c.id">
                                <option :value="c.id" x-text="c.name + (c.customer_type === 'member' ? ' (Member - Diskon ' + (c.discount_percentage || 0) + '%)' : '')"></option>
                            </template>
                        </select>
                    </div>

                    <button type="button" @click="clearCart()" title="Hapus Semua Item" class="text-slate-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-slate-200 transition-colors">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </button>
                </div>

                <!-- Cart Items List (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-3 space-y-2">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-200 space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1">
                                    <div class="text-xs font-bold text-slate-900 leading-tight" x-text="item.name"></div>
                                    <div class="text-[10px] text-slate-400 font-mono" x-text="item.product_code || '-'"></div>
                                </div>
                                <button type="button" @click="removeFromCart(index)" class="text-slate-400 hover:text-rose-500 p-1">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                            </div>

                            <div class="flex items-center justify-between gap-2 text-xs">
                                <!-- Unit Selector -->
                                <select x-model="item.unit_id" @change="onCartItemUnitChange(item)"
                                        class="py-1 px-2 text-[11px] bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 w-24">
                                    <template x-for="u in item.units" :key="u.unit_id">
                                        <option :value="u.unit_id" x-text="u.name"></option>
                                    </template>
                                </select>

                                <!-- Qty increment/decrement controls -->
                                <div class="flex items-center border border-slate-200 rounded-lg bg-white overflow-hidden">
                                    <button type="button" @click="updateQty(item, -1)" class="px-2 py-1 hover:bg-slate-100 text-slate-600 font-bold">-</button>
                                    <input type="number" step="any" min="0.01" x-model.number="item.quantity"
                                           class="w-12 text-center text-xs font-bold border-0 focus:ring-0 p-0 text-slate-800">
                                    <button type="button" @click="updateQty(item, 1)" class="px-2 py-1 hover:bg-slate-100 text-slate-600 font-bold">+</button>
                                </div>

                                <!-- Subtotal -->
                                <div class="text-right">
                                    <div class="font-extrabold text-slate-900 text-xs" x-text="formatRupiah(calculateItemSubtotal(item))"></div>
                                    <div class="text-[10px] text-slate-400" x-text="'@ ' + formatRupiah(item.unit_price)"></div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="cart.length === 0">
                        <div class="h-48 flex flex-col items-center justify-center text-slate-400">
                            <i class="fa-solid fa-cart-shopping text-3xl mb-2 text-slate-300"></i>
                            <p class="text-xs font-medium">Keranjang belanja kosong</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Scan barcode atau klik obat di sebelah kiri</p>
                        </div>
                    </template>
                </div>

                <!-- Bottom Checkout Panel -->
                <div class="p-4 border-t border-slate-200 bg-slate-50/90 space-y-3 shrink-0">
                    <!-- Totals Breakdown -->
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between text-slate-500">
                            <span>Subtotal Item:</span>
                            <span class="font-semibold text-slate-800" x-text="formatRupiah(subtotal)"></span>
                        </div>
                        <template x-if="discountTotal > 0">
                            <div class="flex justify-between text-rose-600 font-semibold">
                                <span>Diskon Pelanggan/Member:</span>
                                <span x-text="'-' + formatRupiah(discountTotal)"></span>
                            </div>
                        </template>
                        <div class="flex justify-between text-base font-extrabold text-slate-900 pt-1 border-t border-slate-200">
                            <span>Total Bayar:</span>
                            <span class="text-emerald-600 text-lg" x-text="formatRupiah(grandTotal)"></span>
                        </div>
                    </div>

                    <!-- Action Buttons: Hold, Pay -->
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button type="button" @click="holdTransaction()" :disabled="cart.length === 0"
                                class="py-2.5 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-pause"></i> Hold (F4)
                        </button>

                        <button type="button" @click="openPaymentModal()" :disabled="cart.length === 0"
                                class="py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-extrabold rounded-xl transition-colors shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-money-bill-wave"></i> Bayar (F9)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAYMENT MODAL -->
        <div x-show="showPaymentModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div @click.away="showPaymentModal = false" class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl space-y-4">
                <div class="bg-emerald-600 px-6 py-4 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold">Pembayaran POS</h2>
                        <div class="text-xs text-emerald-100">Pilih metode & masukkan nominal uang</div>
                    </div>
                    <button type="button" @click="showPaymentModal = false" class="text-emerald-200 hover:text-white text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <!-- Grand Total Banner -->
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 text-center">
                        <div class="text-xs font-semibold text-slate-500">Total Tagihan:</div>
                        <div class="text-2xl font-extrabold text-emerald-600" x-text="formatRupiah(grandTotal)"></div>
                    </div>

                    <!-- Payment Method Select -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Metode Pembayaran</label>
                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="m in ['cash', 'debit', 'qris', 'transfer']" :key="m">
                                <button type="button" @click="paymentMethod = m"
                                        :class="paymentMethod === m ? 'bg-emerald-600 text-white font-bold border-emerald-600' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200'"
                                        class="py-2 text-xs rounded-xl border uppercase transition-colors" x-text="m">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Cash Input & Quick Cash Buttons (For Cash) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Uang Diterima (Rp)</label>
                        <input type="number" step="any" min="0" x-model.number="cashReceived"
                               class="w-full px-3 py-2 text-base font-bold text-slate-900 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        
                        <div class="grid grid-cols-4 gap-1.5 mt-2">
                            <button type="button" @click="cashReceived = grandTotal" class="py-1 bg-slate-100 hover:bg-slate-200 text-[11px] font-bold text-slate-700 rounded-lg">Uang Pas</button>
                            <button type="button" @click="cashReceived = 20000" class="py-1 bg-slate-100 hover:bg-slate-200 text-[11px] font-semibold text-slate-700 rounded-lg">20.000</button>
                            <button type="button" @click="cashReceived = 50000" class="py-1 bg-slate-100 hover:bg-slate-200 text-[11px] font-semibold text-slate-700 rounded-lg">50.000</button>
                            <button type="button" @click="cashReceived = 100000" class="py-1 bg-slate-100 hover:bg-slate-200 text-[11px] font-semibold text-slate-700 rounded-lg">100.000</button>
                        </div>
                    </div>

                    <!-- Kembalian Display -->
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 flex justify-between items-center text-sm font-bold">
                        <span class="text-emerald-900">Kembalian:</span>
                        <span :class="changeAmount >= 0 ? 'text-emerald-700 font-extrabold text-base' : 'text-rose-600'"
                              x-text="changeAmount >= 0 ? formatRupiah(changeAmount) : 'Kurang ' + formatRupiah(Math.abs(changeAmount))">
                        </span>
                    </div>

                    <div class="pt-2">
                        <button type="button" @click="processPayment()" :disabled="changeAmount < 0 || isProcessing"
                                class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-extrabold text-sm rounded-xl transition-colors shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2">
                            <span x-show="!isProcessing"><i class="fa-solid fa-print"></i> Selesai & Cetak Struk</span>
                            <span x-show="isProcessing"><i class="fa-solid fa-spinner fa-spin"></i> Memproses Transaksi...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- HELD ORDERS MODAL -->
        <div x-show="showHeldModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div @click.away="showHeldModal = false" class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl">
                <div class="bg-amber-500 px-6 py-4 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold">Transaksi Tertunda (Held Orders)</h2>
                        <div class="text-xs text-amber-100">Pilih transaksi untuk dimuat kembali ke keranjang</div>
                    </div>
                    <button type="button" @click="showHeldModal = false" class="text-amber-200 hover:text-white text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-6 max-h-96 overflow-y-auto space-y-3">
                    <template x-for="held in heldOrders" :key="held.id">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-slate-900 text-xs" x-text="held.invoice_number"></div>
                                <div class="text-[11px] text-slate-500" x-text="(held.customer ? held.customer.name : 'Pelanggan Umum') + ' • ' + held.items.length + ' item'"></div>
                                <div class="text-xs font-extrabold text-emerald-600 mt-0.5" x-text="formatRupiah(held.grand_total)"></div>
                            </div>
                            <button type="button" @click="recallHeldOrder(held)"
                                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                                Muat ke Kasir
                            </button>
                        </div>
                    </template>

                    <template x-if="heldOrders.length === 0">
                        <div class="text-center py-8 text-slate-400 text-xs">
                            <i class="fa-regular fa-folder-open text-2xl mb-1.5"></i>
                            <p>Tidak ada transaksi tertunda.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

    <!-- Pos Alpine Application Logic -->
    <script>
        function posApp() {
            return {
                products: {{ Js::from($products) }},
                customersData: {{ Js::from($customers) }},
                heldOrders: {{ Js::from($heldSales) }},
                searchQuery: '',
                selectedCategory: 'all',
                selectedCustomerId: '',
                cart: [],
                heldSaleId: null,
                showPaymentModal: false,
                showHeldModal: false,
                paymentMethod: 'cash',
                cashReceived: 0,
                isProcessing: false,

                init() {
                    // Global keyboard shortcut handler
                    window.addEventListener('keydown', (e) => {
                        if (e.key === 'F2') {
                            e.preventDefault();
                            this.$refs.searchInput.focus();
                        } else if (e.key === 'F4') {
                            e.preventDefault();
                            if (this.cart.length > 0) this.holdTransaction();
                        } else if (e.key === 'F8') {
                            e.preventDefault();
                            this.clearCart();
                        } else if (e.key === 'F9') {
                            e.preventDefault();
                            if (this.cart.length > 0) this.openPaymentModal();
                        } else if (e.key === 'Escape') {
                            this.showPaymentModal = false;
                            this.showHeldModal = false;
                        }
                    });
                },

                get categories() {
                    const set = new Set();
                    this.products.forEach(p => {
                        if (p.category_name) set.add(p.category_name);
                    });
                    return Array.from(set);
                },

                get filteredProducts() {
                    const q = this.searchQuery.toLowerCase().trim();
                    return this.products.filter(p => {
                        const matchesCategory = this.selectedCategory === 'all' || p.category_name === this.selectedCategory;
                        if (!matchesCategory) return false;

                        if (!q) return true;
                        const matchName = p.name.toLowerCase().includes(q);
                        const matchCode = (p.product_code || '').toLowerCase().includes(q);
                        const matchBarcode = (p.barcode || '').toLowerCase().includes(q);
                        return matchName || matchCode || matchBarcode;
                    });
                },

                getDefaultPrice(product) {
                    if (!product.units || product.units.length === 0) return 0;
                    const base = product.units.find(u => u.is_base_unit) || product.units[0];
                    return base.selling_price || 0;
                },

                handleSearchEnter() {
                    if (this.filteredProducts.length === 1) {
                        this.addToCart(this.filteredProducts[0]);
                        this.searchQuery = '';
                    }
                },

                addToCart(product) {
                    if (product.available_stock <= 0) {
                        alert('Peringatan: Stok obat ini saat ini kosong di gudang!');
                    }

                    const existing = this.cart.find(item => item.product_id === product.id);
                    if (existing) {
                        existing.quantity += 1;
                    } else {
                        const baseUnit = (product.units && product.units.length > 0)
                            ? (product.units.find(u => u.is_base_unit) || product.units[0])
                            : { unit_id: null, name: 'Satuan', selling_price: 0 };

                        this.cart.push({
                            product_id: product.id,
                            name: product.name,
                            product_code: product.product_code,
                            requires_prescription: product.requires_prescription,
                            units: product.units,
                            unit_id: baseUnit.unit_id,
                            unit_price: baseUnit.selling_price || 0,
                            quantity: 1,
                            discount_per_item: 0,
                            notes: ''
                        });
                    }
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },

                clearCart() {
                    if (this.cart.length === 0) return;
                    if (confirm('Kosongkan keranjang belanja?')) {
                        this.cart = [];
                        this.heldSaleId = null;
                    }
                },

                updateQty(item, delta) {
                    const newQty = item.quantity + delta;
                    if (newQty <= 0) {
                        const idx = this.cart.indexOf(item);
                        if (idx !== -1) this.removeFromCart(idx);
                    } else {
                        item.quantity = newQty;
                    }
                },

                onCartItemUnitChange(item) {
                    const matched = item.units.find(u => u.unit_id == item.unit_id);
                    if (matched) {
                        item.unit_price = matched.selling_price || 0;
                    }
                },

                calculateItemSubtotal(item) {
                    const q = parseFloat(item.quantity) || 0;
                    const p = parseFloat(item.unit_price) || 0;
                    const d = parseFloat(item.discount_per_item) || 0;
                    return Math.max(0, (q * p) - d);
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + this.calculateItemSubtotal(item), 0);
                },

                get selectedCustomer() {
                    return this.customersData.find(c => c.id == this.selectedCustomerId);
                },

                get discountTotal() {
                    const cust = this.selectedCustomer;
                    if (cust && cust.customer_type === 'member' && cust.discount_percentage > 0) {
                        return (this.subtotal * parseFloat(cust.discount_percentage)) / 100;
                    }
                    return 0;
                },

                get grandTotal() {
                    return Math.max(0, this.subtotal - this.discountTotal);
                },

                get changeAmount() {
                    return (parseFloat(this.cashReceived) || 0) - this.grandTotal;
                },

                onCustomerChange() {
                    // Triggers reactive discount recalculation
                },

                openPaymentModal() {
                    this.cashReceived = this.grandTotal;
                    this.showPaymentModal = true;
                },

                async processPayment() {
                    if (this.isProcessing) return;
                    this.isProcessing = true;

                    const payload = {
                        customer_id: this.selectedCustomerId || null,
                        status: 'completed',
                        discount_amount: this.discountTotal,
                        tax_amount: 0,
                        items: this.cart.map(item => ({
                            product_id: item.product_id,
                            unit_id: item.unit_id,
                            quantity: item.quantity,
                            unit_price: item.unit_price,
                            discount_per_item: item.discount_per_item || 0,
                            is_prescription: item.requires_prescription || false,
                        })),
                        payments: [
                            {
                                payment_method: this.paymentMethod,
                                amount: this.grandTotal,
                            }
                        ],
                        held_sale_id: this.heldSaleId,
                    };

                    try {
                        const response = await fetch("{{ route('pos.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        });

                        const result = await response.json();
                        if (response.ok && result.success) {
                            this.showPaymentModal = false;
                            this.cart = [];
                            this.heldSaleId = null;

                            // Open receipt in popup window
                            window.open(result.receipt_url, '_blank', 'width=600,height=700');
                        } else {
                            alert(result.message || 'Terjadi kesalahan saat memproses transaksi.');
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Koneksi ke server terputus.');
                    } finally {
                        this.isProcessing = false;
                    }
                },

                async holdTransaction() {
                    if (this.cart.length === 0) return;

                    const payload = {
                        customer_id: this.selectedCustomerId || null,
                        status: 'pending',
                        discount_amount: this.discountTotal,
                        tax_amount: 0,
                        items: this.cart.map(item => ({
                            product_id: item.product_id,
                            unit_id: item.unit_id,
                            quantity: item.quantity,
                            unit_price: item.unit_price,
                            discount_per_item: item.discount_per_item || 0,
                        })),
                        payments: []
                    };

                    try {
                        const response = await fetch("{{ route('pos.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        });

                        const result = await response.json();
                        if (response.ok && result.success) {
                            alert('Transaksi berhasil di-hold (pending).');
                            location.reload();
                        }
                    } catch (err) {
                        alert('Gagal meng-hold transaksi.');
                    }
                },

                recallHeldOrder(held) {
                    this.heldSaleId = held.id;
                    this.selectedCustomerId = held.customer_id || '';
                    this.cart = held.items.map(item => {
                        const prod = this.products.find(p => p.id === item.product_id) || {};
                        return {
                            product_id: item.product_id,
                            name: prod.name || item.product?.name || 'Obat',
                            product_code: prod.product_code,
                            requires_prescription: prod.requires_prescription || false,
                            units: prod.units || [],
                            unit_id: item.unit_id,
                            unit_price: (float) item.unit_price,
                            quantity: (float) item.quantity,
                            discount_per_item: (float) item.discount_per_item,
                        };
                    });
                    this.showHeldModal = false;
                },

                formatRupiah(amount) {
                    return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
                }
            };
        }
    </script>
</x-app-layout>
