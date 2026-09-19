<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Analisis ABC (Klasifikasi Pareto)</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('stocks.index') }}" class="hover:text-slate-900">Inventori</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Analisis ABC</span>
            </nav>
        </div>
        <form method="POST" action="{{ route('stocks.abc.run') }}" class="inline">
            @csrf
            <input type="hidden" name="days" value="{{ $days }}">
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                <i class="fa-solid fa-arrows-rotate"></i> Hitung Ulang & Simpan Kategori
            </button>
        </form>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <!-- Category Explanation & Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <!-- Kelas A Card -->
        <div class="bg-white rounded-2xl border border-emerald-200 shadow-sm p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-4 -mt-4 -z-0"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        <i class="fa-solid fa-crown text-[10px]"></i> KELAS A (Prioritas Tinggi)
                    </span>
                    <span class="text-xs font-semibold text-emerald-600">~70% Nilai Omset</span>
                </div>
                <div class="text-2xl font-black text-slate-900 mb-1">
                    {{ $categorySummary['A']['count'] }} <span class="text-sm font-normal text-slate-500">Produk ({{ $totalItemsCount > 0 ? round(($categorySummary['A']['count'] / $totalItemsCount) * 100, 1) : 0 }}%)</span>
                </div>
                <div class="text-sm font-bold text-emerald-700 mb-2 font-mono">
                    Rp {{ number_format($categorySummary['A']['revenue'], 0, ',', '.') }}
                    <span class="text-xs font-normal text-slate-500">({{ $grandTotalRevenue > 0 ? round(($categorySummary['A']['revenue'] / $grandTotalRevenue) * 100, 1) : 0 }}% dari total)</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Kontrol sangat ketat, pemantauan stok harian, buffer stock minimum, jangan sampai out of stock.
                </p>
            </div>
        </div>

        <!-- Kelas B Card -->
        <div class="bg-white rounded-2xl border border-blue-200 shadow-sm p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 -z-0"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                        <i class="fa-solid fa-star-half-stroke text-[10px]"></i> KELAS B (Prioritas Sedang)
                    </span>
                    <span class="text-xs font-semibold text-blue-600">~20% Nilai Omset</span>
                </div>
                <div class="text-2xl font-black text-slate-900 mb-1">
                    {{ $categorySummary['B']['count'] }} <span class="text-sm font-normal text-slate-500">Produk ({{ $totalItemsCount > 0 ? round(($categorySummary['B']['count'] / $totalItemsCount) * 100, 1) : 0 }}%)</span>
                </div>
                <div class="text-sm font-bold text-blue-700 mb-2 font-mono">
                    Rp {{ number_format($categorySummary['B']['revenue'], 0, ',', '.') }}
                    <span class="text-xs font-normal text-slate-500">({{ $grandTotalRevenue > 0 ? round(($categorySummary['B']['revenue'] / $grandTotalRevenue) * 100, 1) : 0 }}% dari total)</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Kontrol berkala (mingguan), pemesanan rutin dengan reorder point yang telah terkalibrasi.
                </p>
            </div>
        </div>

        <!-- Kelas C Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-slate-100 rounded-bl-full -mr-4 -mt-4 -z-0"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                        <i class="fa-solid fa-boxes-stacked text-[10px]"></i> KELAS C (Prioritas Rendah)
                    </span>
                    <span class="text-xs font-semibold text-slate-500">~10% Nilai Omset</span>
                </div>
                <div class="text-2xl font-black text-slate-900 mb-1">
                    {{ $categorySummary['C']['count'] }} <span class="text-sm font-normal text-slate-500">Produk ({{ $totalItemsCount > 0 ? round(($categorySummary['C']['count'] / $totalItemsCount) * 100, 1) : 0 }}%)</span>
                </div>
                <div class="text-sm font-bold text-slate-700 mb-2 font-mono">
                    Rp {{ number_format($categorySummary['C']['revenue'], 0, ',', '.') }}
                    <span class="text-xs font-normal text-slate-500">({{ $grandTotalRevenue > 0 ? round(($categorySummary['C']['revenue'] / $grandTotalRevenue) * 100, 1) : 0 }}% dari total)</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Jumlah produk banyak dengan nilai omset kecil. Kontrol sederhana, hindari over-stocking.
                </p>
            </div>
        </div>
    </div>

    <!-- Pareto Chart Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Kurva Pareto (Top 25 Produk Berdasarkan Omset)</h3>
                <p class="text-xs text-slate-500">Grafik batang menunjukkan omset penjualan dan garis menunjukkan persentase kumulatif.</p>
            </div>
            <!-- Period Selector -->
            <form method="GET" action="{{ route('stocks.abc') }}" class="flex items-center gap-2">
                <input type="hidden" name="category" value="{{ $categoryFilter }}">
                <span class="text-xs font-medium text-slate-600">Periode Penjualan:</span>
                <select name="days" onchange="this.form.submit()" class="text-xs px-3 py-1.5 border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 font-medium">
                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 Hari Terakhir</option>
                    <option value="60" {{ $days == 60 ? 'selected' : '' }}>60 Hari Terakhir</option>
                    <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 Hari Terakhir (Standar)</option>
                    <option value="180" {{ $days == 180 ? 'selected' : '' }}>180 Hari Terakhir</option>
                    <option value="365" {{ $days == 365 ? 'selected' : '' }}>1 Tahun Terakhir</option>
                </select>
            </form>
        </div>

        <div class="h-72 w-full">
            <canvas id="paretoChart"></canvas>
        </div>
    </div>

    <!-- Products Classification Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Filter Pills -->
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('stocks.abc', ['days' => $days, 'category' => 'all']) }}"
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $categoryFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua Produk ({{ $totalItemsCount }})
                </a>
                <a href="{{ route('stocks.abc', ['days' => $days, 'category' => 'A']) }}"
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $categoryFilter === 'A' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    Kelas A ({{ $categorySummary['A']['count'] }})
                </a>
                <a href="{{ route('stocks.abc', ['days' => $days, 'category' => 'B']) }}"
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $categoryFilter === 'B' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                    Kelas B ({{ $categorySummary['B']['count'] }})
                </a>
                <a href="{{ route('stocks.abc', ['days' => $days, 'category' => 'C']) }}"
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $categoryFilter === 'C' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Kelas C ({{ $categorySummary['C']['count'] }})
                </a>
            </div>

            <!-- Search -->
            <form method="GET" action="{{ route('stocks.abc') }}" class="flex items-center gap-2">
                <input type="hidden" name="days" value="{{ $days }}">
                <input type="hidden" name="category" value="{{ $categoryFilter }}">
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau kode..."
                           class="pl-8 pr-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 w-52">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-2.5 top-2 text-xs"></i>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">Rank</th>
                        <th class="py-3 px-4">Kode & Nama Produk</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4 text-center">Stok Fisik</th>
                        <th class="py-3 px-4 text-center">Qty Terjual</th>
                        <th class="py-3 px-4 text-right">Total Nilai Penjualan</th>
                        <th class="py-3 px-4 text-center">% Kumulatif</th>
                        <th class="py-3 px-4 text-center">Klasifikasi ABC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($filteredList as $index => $item)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 text-center text-xs font-bold text-slate-400">
                                #{{ $loop->iteration }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-900">{{ $item->product_name }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $item->product_code }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 text-xs">
                                {{ $item->category_name }}
                            </td>
                            <td class="py-3 px-4 text-center text-xs font-semibold text-slate-700">
                                {{ number_format($item->current_stock, 0) }} {{ $item->unit_name }}
                            </td>
                            <td class="py-3 px-4 text-center text-xs font-semibold text-slate-700">
                                {{ number_format($item->sales_qty, 0) }} {{ $item->unit_name }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 text-xs">
                                Rp {{ number_format($item->revenue, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center text-xs font-mono font-semibold text-slate-600">
                                {{ $item->cumulative_pct }}%
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($item->abc_category === 'A')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-crown text-[9px]"></i> KELAS A
                                    </span>
                                @elseif($item->abc_category === 'B')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-blue-100 text-blue-800">
                                        KELAS B
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-slate-100 text-slate-700">
                                        KELAS C
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-500">
                                <p class="font-semibold text-slate-700">Tidak ada data produk ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart.js Pareto Logic -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('paretoChart').getContext('2d');
            const labels = @json($chartData['labels']);
            const revenues = @json($chartData['revenues']);
            const percentages = @json($chartData['percentages']);

            new Chart(ctx, {
                data: {
                    labels: labels,
                    datasets: [
                        {
                            type: 'line',
                            label: '% Kumulatif',
                            data: percentages,
                            borderColor: '#e11d48',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            pointRadius: 3,
                            yAxisID: 'y1',
                            tension: 0.2
                        },
                        {
                            type: 'bar',
                            label: 'Omset Penjualan (Rp)',
                            data: revenues,
                            backgroundColor: '#10b981',
                            borderRadius: 4,
                            yAxisID: 'y',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Pendapatan (Rp)' },
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + (value >= 1000000 ? (value/1000000).toFixed(1) + 'M' : value.toLocaleString('id-ID'));
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            min: 0,
                            max: 100,
                            title: { display: true, text: '% Kumulatif' },
                            grid: { drawOnChartArea: false },
                            ticks: {
                                callback: function(value) { return value + '%'; }
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0,
                                callback: function(value, index) {
                                    const lbl = this.getLabelForValue(value);
                                    return lbl.length > 12 ? lbl.substr(0, 10) + '...' : lbl;
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (context.dataset.type === 'line') {
                                        return '% Kumulatif: ' + context.raw + '%';
                                    }
                                    return 'Omset: Rp ' + Number(context.raw).toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
