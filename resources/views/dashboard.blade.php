<x-app-layout>
    <!-- Chart.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="space-y-6" x-data="{ customRange: '{{ $period === 'custom' }}' }">
        <!-- Page Header & Period Filter -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">Dashboard Apotek</h1>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-[#f83f37] border border-red-100">
                        <i class="fa-solid fa-user-shield mr-1.5"></i> {{ $userRole }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Selamat datang kembali, <strong>{{ Auth::user()->name }}</strong>. Menampilkan data ringkasan <span class="font-semibold text-slate-700">{{ $periodLabel }}</span>.
                </p>
            </div>

            <!-- Filter Periode Form -->
            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2">
                <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs font-semibold">
                    <a href="{{ route('dashboard', ['period' => 'today']) }}" 
                       class="px-3 py-1.5 rounded-lg transition-all {{ $period === 'today' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Hari Ini
                    </a>
                    <a href="{{ route('dashboard', ['period' => 'this_week']) }}" 
                       class="px-3 py-1.5 rounded-lg transition-all {{ $period === 'this_week' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Minggu Ini
                    </a>
                    <a href="{{ route('dashboard', ['period' => 'this_month']) }}" 
                       class="px-3 py-1.5 rounded-lg transition-all {{ $period === 'this_month' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Bulan Ini
                    </a>
                    <button type="button" @click="customRange = !customRange" 
                            class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1 {{ $period === 'custom' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        <i class="fa-regular fa-calendar"></i> Kustom
                    </button>
                </div>

                <!-- Custom Range Inputs Dropdown -->
                <div x-show="customRange" class="flex items-center gap-2 mt-2 sm:mt-0" style="display: none;">
                    <input type="hidden" name="period" value="custom">
                    <input type="date" name="start_date" value="{{ request('start_date', $startDate->toDateString()) }}" 
                           class="px-2.5 py-1 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-red-500">
                    <span class="text-xs text-slate-400">s/d</span>
                    <input type="date" name="end_date" value="{{ request('end_date', $endDate->toDateString()) }}" 
                           class="px-2.5 py-1 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-red-500">
                    <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-semibold hover:bg-slate-900 transition">
                        Terapkan
                    </button>
                </div>

                <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-[#f83f37] hover:bg-[#e03129] text-white text-xs font-bold rounded-xl shadow-sm hover:shadow transition flex items-center gap-2">
                    <i class="fa-solid fa-cash-register"></i> Buka Kasir POS
                </a>
            </form>
        </div>

        @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
        @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

        <!-- Role Specific Attention Banner -->
        @if(in_array($userRole, ['Apoteker', 'Pemilik']) && $pendingPrescriptionsCount > 0)
            <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg">
                        <i class="fa-solid fa-file-prescription"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Perhatian: Ada {{ $pendingPrescriptionsCount }} Resep Dokter Menunggu Validasi Apoteker</h3>
                        <p class="text-xs text-slate-600">Lakukan skrining klinis dan administrasi sebelum obat disiapkan.</p>
                    </div>
                </div>
                <a href="{{ route('prescriptions.index', ['status' => 'submitted']) }}" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                    Tinjau Resep <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>
        @endif

        @if($userRole === 'Kasir')
            <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl {{ $activeShift ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }} flex items-center justify-center text-2xl shrink-0">
                        <i class="fa-solid {{ $activeShift ? 'fa-cash-register' : 'fa-door-closed' }}"></i>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase">Status Shift Kasir Anda</div>
                        <div class="text-base font-bold text-slate-900">
                            {{ $activeShift ? 'Shift Aktif Dimulai: ' . \Carbon\Carbon::parse($activeShift->start_time)->format('H:i') . ' (Modal Awal: Rp ' . number_format($activeShift->opening_cash, 0, ',', '.') . ')' : 'Belum Ada Shift Terbuka' }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($activeShift)
                        <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 shadow-sm">
                            <i class="fa-solid fa-play mr-1"></i> Lanjut Transaksi POS
                        </a>
                    @else
                        <a href="{{ route('shifts.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 shadow-sm">
                            <i class="fa-solid fa-key mr-1"></i> Buka Shift Kasir
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <!-- 4 Primary Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Metric 1: Total Omzet Penjualan -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Penjualan</span>
                    <div class="w-9 h-9 rounded-xl bg-red-50 text-[#f83f37] flex items-center justify-center text-base">
                        <i class="fa-solid fa-rupiah-sign"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <h3 class="text-2xl font-black text-slate-900">Rp {{ number_format($totalSalesAmount, 0, ',', '.') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        @if($salesGrowthPercentage >= 0)
                            <span class="text-[11px] font-bold text-emerald-600 flex items-center gap-0.5">
                                <i class="fa-solid fa-arrow-trend-up"></i> +{{ number_format($salesGrowthPercentage, 1) }}%
                            </span>
                        @else
                            <span class="text-[11px] font-bold text-red-600 flex items-center gap-0.5">
                                <i class="fa-solid fa-arrow-trend-down"></i> {{ number_format($salesGrowthPercentage, 1) }}%
                            </span>
                        @endif
                        <span class="text-[11px] text-slate-400">vs periode lalu</span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>{{ number_format($totalSalesCount) }} Transaksi</span>
                    <span>Rata-rata: Rp {{ number_format($avgTransaction, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Metric 2: Kas Hari Ini -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kas Masuk Hari Ini</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <h3 class="text-2xl font-black text-slate-900">Rp {{ number_format($todayCashSales + $todayNonCashSales, 0, ',', '.') }}</h3>
                    <div class="text-[11px] text-slate-400 mt-1">
                        Penerimaan tunai & non-tunai hari ini
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-emerald-700 font-semibold">Tunai: Rp {{ number_format($todayCashSales, 0, ',', '.') }}</span>
                    <span class="text-indigo-600 font-semibold">Digital: Rp {{ number_format($todayNonCashSales, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Metric 3: Hutang Supplier & Jatuh Tempo -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Hutang Pembelian</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <h3 class="text-2xl font-black text-slate-900">Rp {{ number_format($totalSupplierDebt, 0, ',', '.') }}</h3>
                    <div class="text-[11px] text-amber-700 font-semibold mt-1">
                        Rp {{ number_format($nearDueSupplierDebt, 0, ',', '.') }} jatuh tempo &le; 7 hari
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <a href="{{ route('purchase-invoices.index', ['payment_status' => 'unpaid']) }}" class="text-amber-700 hover:underline font-semibold">
                        Lihat Faktur Hutang &rarr;
                    </a>
                </div>
            </div>

            <!-- Metric 4: Piutang Pelanggan -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Piutang Pelanggan</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <h3 class="text-2xl font-black text-slate-900">Rp {{ number_format($totalCustomerReceivables, 0, ',', '.') }}</h3>
                    <div class="text-[11px] text-slate-400 mt-1">
                        {{ $debtCustomersCount }} pelanggan memiliki saldo piutang
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <a href="{{ route('customers.index') }}" class="text-blue-600 hover:underline font-semibold">
                        Kelola Pelanggan &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Stok Alert Warning Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('stocks.index') }}" class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 hover:border-red-300 transition">
                <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Stok Habis (0)</div>
                    <div class="text-lg font-black text-red-600">{{ $outOfStockCount }} Produk</div>
                </div>
            </a>

            <a href="{{ route('stocks.index') }}" class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 hover:border-amber-300 transition">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Stok Menipis (&le; Min)</div>
                    <div class="text-lg font-black text-amber-600">{{ $lowStockCount }} Produk</div>
                </div>
            </a>

            <a href="{{ route('expired.dashboard') }}" class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 hover:border-purple-300 transition">
                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-calendar-xmark"></i>
                </div>
                <div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Kadaluarsa / Dekat Expired</div>
                    <div class="text-lg font-black text-purple-700">{{ $expiredBatchesCount }} Exp / {{ $nearExpiredBatchesCount }} &le; 90 Hari</div>
                </div>
            </a>
        </div>

        <!-- Charts Section (30-Day Trend & Top 10 Products) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Line Chart: Tren Penjualan 30 Hari -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Tren Penjualan 30 Hari Terakhir</h2>
                        <p class="text-xs text-slate-400">Grafik omzet penjualan harian (Rp)</p>
                    </div>
                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-semibold">
                        <i class="fa-solid fa-chart-line text-[#f83f37] mr-1"></i> Omzet
                    </span>
                </div>
                <div class="h-72 w-full">
                    <canvas id="salesTrendChart"></canvas>
                </div>
            </div>

            <!-- Donut Chart: Komposisi Metode Pembayaran -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Metode Pembayaran</h2>
                            <p class="text-xs text-slate-400">Distribusi kas & non-tunai</p>
                        </div>
                        <i class="fa-solid fa-chart-pie text-slate-400"></i>
                    </div>
                    <div class="h-56 w-full flex items-center justify-center">
                        <canvas id="paymentMethodsChart"></canvas>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 text-center">
                    <a href="{{ route('reports.sales') }}" class="text-xs font-semibold text-blue-600 hover:underline">
                        Buka Laporan Penjualan Lengkap &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Top Products Bar Chart & Near-Expired Table -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Bar Chart: Top 10 Obat Terlaris -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Top 10 Obat Terlaris</h2>
                        <p class="text-xs text-slate-400">Berdasarkan kuantitas terjual pada periode ini</p>
                    </div>
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-semibold">
                        <i class="fa-solid fa-award mr-1"></i> Terbanyak
                    </span>
                </div>
                <div class="h-72 w-full">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>

            <!-- List: Batch Mendekati Expired (Countdown Alert) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-hourglass-half text-amber-500"></i>
                            <h2 class="text-base font-bold text-slate-900">Batch Mendekati Expired (&le; 90 Hari)</h2>
                        </div>
                        <a href="{{ route('expired.index') }}" class="text-xs text-blue-600 font-semibold hover:underline">Lihat Semua</a>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse($nearExpiredList as $batch)
                            @php
                                $daysLeft = (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($batch->expiry_date), false);
                            @endphp
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-bold text-slate-900">{{ $batch->product?->name ?? 'Produk' }}</div>
                                    <div class="text-xs text-slate-400">
                                        Batch: <span class="font-mono text-slate-600">{{ $batch->batch_number }}</span> &bull; Sisa: <strong class="text-slate-700">{{ $batch->current_qty }}</strong>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $daysLeft <= 30 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $daysLeft <= 0 ? 'Kadaluarsa' : $daysLeft . ' Hari Lagi' }}
                                    </span>
                                    <div class="text-[11px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($batch->expiry_date)->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400 text-xs">
                                <i class="fa-solid fa-shield-halved text-2xl text-emerald-400 mb-2"></i>
                                <p>Tidak ada batch obat yang mendekati kadaluarsa dalam 90 hari ke depan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400">Perlu tindakan retur/pemusnahan?</span>
                    <a href="{{ route('drug-destructions.create') }}" class="text-xs font-bold text-red-600 hover:underline">
                        Buat Pemusnahan Obat &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Transactions & Upcoming Due Invoices -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Sales Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-[#f83f37]"></i>
                        <h2 class="text-base font-bold text-slate-900">Transaksi Penjualan Terbaru</h2>
                    </div>
                    <a href="{{ route('pos.history') }}" class="text-xs text-blue-600 font-semibold hover:underline">Riwayat Lengkap</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-semibold">
                            <tr>
                                <th class="py-2.5 px-3">No. Nota</th>
                                <th class="py-2.5 px-2">Waktu</th>
                                <th class="py-2.5 px-3">Pelanggan</th>
                                <th class="py-2.5 px-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentSales as $sale)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2.5 px-3 font-mono font-semibold text-slate-800">{{ $sale->invoice_number }}</td>
                                    <td class="py-2.5 px-2 text-slate-500">{{ \Carbon\Carbon::parse($sale->sale_date)->format('H:i') }}</td>
                                    <td class="py-2.5 px-3 font-medium text-slate-800">{{ $sale->customer?->name ?? 'Umum' }}</td>
                                    <td class="py-2.5 px-3 text-right font-bold text-slate-900">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-400">Belum ada transaksi pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Upcoming Supplier Due Invoices -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-amber-500"></i>
                        <h2 class="text-base font-bold text-slate-900">Faktur Hutang Mendekati Jatuh Tempo</h2>
                    </div>
                    <a href="{{ route('purchase-invoices.index') }}" class="text-xs text-blue-600 font-semibold hover:underline">Semua Hutang</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-semibold">
                            <tr>
                                <th class="py-2.5 px-3">Supplier</th>
                                <th class="py-2.5 px-2">Tgl Jatuh Tempo</th>
                                <th class="py-2.5 px-3 text-right">Sisa Hutang</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($upcomingDebtInvoices as $inv)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2.5 px-3">
                                        <div class="font-semibold text-slate-900">{{ $inv->supplier?->name ?? 'Supplier' }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $inv->invoice_number }}</div>
                                    </td>
                                    <td class="py-2.5 px-2 text-slate-700">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ \Carbon\Carbon::parse($inv->due_date)->lte(now()) ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700' }}">
                                            {{ \Carbon\Carbon::parse($inv->due_date)->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-bold text-amber-700">
                                        Rp {{ number_format($inv->remaining_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-slate-400">Tidak ada hutang mendekati jatuh tempo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Initialization Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Line Chart: 30-Day Sales Trend
            const trendCtx = document.getElementById('salesTrendChart')?.getContext('2d');
            if (trendCtx) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: @json($trendDates),
                        datasets: [{
                            label: 'Omzet Penjualan (Rp)',
                            data: @json($trendSales),
                            borderColor: '#f83f37',
                            backgroundColor: 'rgba(248, 63, 55, 0.08)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 2,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Omzet: Rp ' + context.parsed.y.toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    callback: function(value) {
                                        if (value >= 1000000) return (value / 1000000) + ' Jt';
                                        if (value >= 1000) return (value / 1000) + ' Rb';
                                        return value;
                                    },
                                    font: { size: 10 }
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 }, maxRotation: 0 }
                            }
                        }
                    }
                });
            }

            // 2. Donut Chart: Payment Methods
            const paymentCtx = document.getElementById('paymentMethodsChart')?.getContext('2d');
            if (paymentCtx) {
                const paymentLabels = @json($paymentLabels);
                const paymentAmounts = @json($paymentAmounts);

                new Chart(paymentCtx, {
                    type: 'doughnut',
                    data: {
                        labels: paymentLabels.length > 0 ? paymentLabels : ['Belum Ada Data'],
                        datasets: [{
                            data: paymentAmounts.length > 0 ? paymentAmounts : [1],
                            backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ec4899', '#cbd5e1'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, font: { size: 11 } }
                            }
                        },
                        cutout: '70%'
                    }
                });
            }

            // 3. Bar Chart: Top 10 Selling Products
            const topCtx = document.getElementById('topProductsChart')?.getContext('2d');
            if (topCtx) {
                const labels = @json($topProductLabels);
                const quantities = @json($topProductQuantities);

                new Chart(topCtx, {
                    type: 'bar',
                    data: {
                        labels: labels.length > 0 ? labels : ['Belum Ada Penjualan'],
                        datasets: [{
                            label: 'Jumlah Terjual',
                            data: quantities.length > 0 ? quantities : [0],
                            backgroundColor: '#3b82f6',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: { font: { size: 10 }, precision: 0 }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { font: { size: 11 } }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
