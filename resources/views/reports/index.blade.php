<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Pusat Laporan Apotek</h1>
                <p class="text-sm text-slate-500 mt-1">Akses seluruh laporan penjualan, analisis laba kotor, inventori stok, valuasi aset, dan laporan keuangan.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="px-3.5 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition shadow-sm">
                    <i class="fa-solid fa-house mr-1"></i> Dashboard
                </a>
            </div>
        </div>

        <!-- Quick Metrik Rekap Bulan Berjalan -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-red-50 text-[#f83f37] flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-rupiah-sign"></i>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase">Omzet Bulan Ini</div>
                    <div class="text-xl font-bold text-slate-900">Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</div>
                    <div class="text-[11px] text-slate-400">{{ number_format($monthlyTransactions) }} Transaksi</div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase">Total Hutang Supplier</div>
                    <div class="text-xl font-bold text-amber-700">Rp {{ number_format($totalUnpaidDebt, 0, ',', '.') }}</div>
                    <div class="text-[11px] text-slate-400">Dari {{ $totalSuppliers }} Supplier</div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase">Total Piutang Pelanggan</div>
                    <div class="text-xl font-bold text-blue-700">Rp {{ number_format($totalReceivables, 0, ',', '.') }}</div>
                    <div class="text-[11px] text-slate-400">Dari {{ $totalCustomers }} Pelanggan</div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase">Katalog Produk</div>
                    <div class="text-xl font-bold text-slate-900">{{ number_format($totalProducts) }} SKU</div>
                    <div class="text-[11px] text-slate-400">Obat & Alat Kesehatan</div>
                </div>
            </div>
        </div>

        <!-- 3 Kategori Utama Laporan -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 1. Laporan Penjualan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4 border-b border-slate-100 pb-3">
                        <div class="w-10 h-10 rounded-xl bg-red-50 text-[#f83f37] flex items-center justify-center text-lg font-bold">
                            <i class="fa-solid fa-cash-register"></i>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate-900 text-base">Laporan Penjualan</h2>
                            <p class="text-xs text-slate-400">Transaksi, omzet & margin laba</p>
                        </div>
                    </div>

                    <ul class="space-y-2 text-xs">
                        <li>
                            <a href="{{ route('reports.sales', ['type' => 'daily']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-calendar-day text-slate-400"></i> Laporan Penjualan Harian</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.sales', ['type' => 'product']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-pills text-slate-400"></i> Penjualan per Produk / Kategori</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.sales', ['type' => 'gross_profit']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-chart-line text-emerald-500"></i> Laporan Laba Kotor (Gross Profit)</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.sales', ['type' => 'customer']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-users text-slate-400"></i> Penjualan per Pelanggan</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.sales', ['type' => 'prescription']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-file-prescription text-blue-500"></i> Penjualan Resep Dokter</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.sales', ['type' => 'payment_method']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-credit-card text-purple-500"></i> Rekap Metode Pembayaran</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.sales', ['type' => 'returns']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-arrow-rotate-left text-red-500"></i> Laporan Retur Penjualan</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="pt-4 border-t border-slate-100 mt-4">
                    <a href="{{ route('reports.sales') }}" class="block w-full text-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-sm">
                        Buka Laporan Penjualan &rarr;
                    </a>
                </div>
            </div>

            <!-- 2. Laporan Stok & Gudang -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4 border-b border-slate-100 pb-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
                            <i class="fa-solid fa-warehouse"></i>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate-900 text-base">Laporan Stok & Gudang</h2>
                            <p class="text-xs text-slate-400">Valuasi aset, mutasi & kadaluarsa</p>
                        </div>
                    </div>

                    <ul class="space-y-2 text-xs">
                        <li>
                            <a href="{{ route('reports.stock', ['type' => 'current']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-coins text-emerald-600"></i> Stok Terkini & Valuasi Persediaan</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.stock', ['type' => 'mutation']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-clipboard-list text-slate-400"></i> Kartu Mutasi & Pergerakan Stok</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.stock', ['type' => 'min_stock']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation text-amber-500"></i> Stok Minimum & Reorder Point</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.stock', ['type' => 'expired']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-calendar-xmark text-purple-500"></i> Obat Expired & Near-Expired</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.stock', ['type' => 'dead_stock']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-hourglass-end text-slate-400"></i> Dead Stock / Slow Moving (>60 hari)</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.stock', ['type' => 'opname']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-clipboard-check text-blue-500"></i> Rekapitulasi Stok Opname</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.stock', ['type' => 'destruction']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-fire-burner text-red-500"></i> Laporan Pemusnahan & Kerugian</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="pt-4 border-t border-slate-100 mt-4">
                    <a href="{{ route('reports.stock') }}" class="block w-full text-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                        Buka Laporan Stok &rarr;
                    </a>
                </div>
            </div>

            <!-- 3. Laporan Pembelian & Keuangan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4 border-b border-slate-100 pb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate-900 text-base">Laporan Pembelian & Keuangan</h2>
                            <p class="text-xs text-slate-400">Hutang, piutang, laba rugi & kas</p>
                        </div>
                    </div>

                    <ul class="space-y-2 text-xs">
                        <li>
                            <a href="{{ route('reports.finance', ['type' => 'purchases']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-receipt text-slate-400"></i> Faktur Pembelian ke Supplier</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.finance', ['type' => 'debts']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-hand-holding-dollar text-amber-600"></i> Daftar Hutang Supplier</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.finance', ['type' => 'aging_debts']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-clock-rotate-left text-purple-600"></i> Analisis Umur Hutang (Aging)</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.finance', ['type' => 'receivables']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-users-line text-blue-600"></i> Piutang Pelanggan (Outstanding)</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.finance', ['type' => 'profit_loss']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-scale-balanced text-emerald-600"></i> Laporan Laba Rugi Sederhana</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.finance', ['type' => 'cash_flow']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-slate-700 font-medium">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-money-bill-transfer text-indigo-600"></i> Arus Kas (Inflow & Outflow)</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="pt-4 border-t border-slate-100 mt-4">
                    <a href="{{ route('reports.finance') }}" class="block w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                        Buka Laporan Keuangan &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
