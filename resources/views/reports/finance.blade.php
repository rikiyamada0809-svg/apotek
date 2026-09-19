<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Laporan Keuangan & Pembelian</h1>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('reports.index') }}" class="hover:text-slate-900">Laporan</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">Keuangan & Pembelian</span>
                </nav>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.finance.pdf', request()->all()) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 shadow-sm transition">
                    <i class="fa-solid fa-file-pdf text-red-600"></i> Cetak PDF
                </a>
                <a href="{{ route('reports.finance.csv', request()->all()) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl hover:bg-emerald-700 shadow-sm transition">
                    <i class="fa-solid fa-file-excel"></i> Unduh CSV / Excel
                </a>
            </div>
        </div>

        <!-- Type Selector Tabs -->
        <div class="border-b border-slate-200">
            <nav class="flex space-x-2 overflow-x-auto pb-2 text-xs font-bold">
                <a href="{{ route('reports.finance', array_merge(request()->except('page'), ['type' => 'purchases'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'purchases' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-receipt mr-1.5"></i> Faktur Pembelian
                </a>
                <a href="{{ route('reports.finance', array_merge(request()->except('page'), ['type' => 'debts'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'debts' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-file-invoice-dollar mr-1.5 text-amber-300"></i> Hutang Supplier
                </a>
                <a href="{{ route('reports.finance', array_merge(request()->except('page'), ['type' => 'aging_debts'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'aging_debts' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-clock-rotate-left mr-1.5"></i> Umur Hutang (Aging)
                </a>
                <a href="{{ route('reports.finance', array_merge(request()->except('page'), ['type' => 'receivables'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'receivables' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-hand-holding-dollar mr-1.5"></i> Piutang Pelanggan
                </a>
                <a href="{{ route('reports.finance', array_merge(request()->except('page'), ['type' => 'profit_loss'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'profit_loss' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-scale-balanced mr-1.5 text-emerald-300"></i> Laba Rugi Sederhana
                </a>
                <a href="{{ route('reports.finance', array_merge(request()->except('page'), ['type' => 'cash_flow'])) }}" 
                   class="px-4 py-2 rounded-xl transition-all shrink-0 {{ $type === 'cash_flow' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-money-bill-transfer mr-1.5"></i> Arus Kas Masuk & Keluar
                </a>
            </nav>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <form method="GET" action="{{ route('reports.finance') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <input type="hidden" name="type" value="{{ $type }}">

                @if(!in_array($type, ['receivables']))
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Tanggal Akhir</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-1 focus:ring-blue-500">
                    </div>
                @endif

                @if(in_array($type, ['purchases', 'debts', 'aging_debts']))
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Pilih Supplier / PBF</label>
                        <select name="supplier_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-1 focus:ring-blue-500">
                            <option value="">Semua Supplier</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" @selected($supplierId == $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition">
                        <i class="fa-solid fa-filter mr-1"></i> Filter Laporan
                    </button>
                    <a href="{{ route('reports.finance', ['type' => $type]) }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition" title="Reset">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Metric Cards -->
        @if($type === 'aging_debts')
            <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <div class="text-[10px] font-bold text-slate-400 uppercase">Belum Jatuh Tempo</div>
                    <div class="text-base font-bold text-emerald-600 mt-1">Rp {{ number_format($agingSummary['current'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <div class="text-[10px] font-bold text-slate-400 uppercase">Lewat 1 - 30 Hari</div>
                    <div class="text-base font-bold text-amber-600 mt-1">Rp {{ number_format($agingSummary['days_1_30'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <div class="text-[10px] font-bold text-slate-400 uppercase">Lewat 31 - 60 Hari</div>
                    <div class="text-base font-bold text-orange-600 mt-1">Rp {{ number_format($agingSummary['days_31_60'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <div class="text-[10px] font-bold text-slate-400 uppercase">Lewat 61 - 90 Hari</div>
                    <div class="text-base font-bold text-red-600 mt-1">Rp {{ number_format($agingSummary['days_61_90'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm bg-red-50/30">
                    <div class="text-[10px] font-bold text-red-600 uppercase">Lewat > 90 Hari</div>
                    <div class="text-base font-black text-red-700 mt-1">Rp {{ number_format($agingSummary['days_over_90'], 0, ',', '.') }}</div>
                </div>
            </div>
        @elseif($type === 'profit_loss')
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-3xl mx-auto space-y-4">
                <div class="text-center border-b border-slate-100 pb-4">
                    <h3 class="font-bold text-slate-900 text-lg">Laporan Laba Rugi Operasional Sederhana</h3>
                    <p class="text-xs text-slate-500">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between py-2 border-b border-slate-100">
                        <span class="font-semibold text-slate-800">1. Pendapatan Penjualan Bersih (Omzet)</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 text-red-600">
                        <span class="font-medium">2. Beban Pokok Penjualan (HPP Barang Terjual)</span>
                        <span class="font-semibold">(Rp {{ number_format($totalHpp, 0, ',', '.') }})</span>
                    </div>
                    <div class="flex items-center justify-between py-2.5 bg-slate-50 px-3 rounded-xl font-bold text-slate-900">
                        <span>LABA KOTOR OPERASIONAL (1 - 2)</span>
                        <span class="text-emerald-600 text-base">Rp {{ number_format($grossProfit, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 text-red-600">
                        <span class="font-medium">3. Kerugian Pemusnahan Obat Kadaluarsa / Rusak</span>
                        <span class="font-semibold">(Rp {{ number_format($destructionLoss, 0, ',', '.') }})</span>
                    </div>
                    <div class="flex items-center justify-between py-3 bg-emerald-50 px-4 rounded-xl font-black text-emerald-900 text-base border border-emerald-100">
                        <span>ESTIMASI LABA BERSIH SEMENTARA</span>
                        <span class="text-lg">Rp {{ number_format($netIncome, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @elseif($type === 'cash_flow')
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Total Arus Kas Masuk (Inflow)</div>
                    <div class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($totalInflow, 0, ',', '.') }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">Tunai: Rp {{ number_format($cashSales, 0, ',', '.') }} | Non-tunai: Rp {{ number_format($digitalSales, 0, ',', '.') }}</div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Total Arus Kas Keluar (Outflow)</div>
                    <div class="text-2xl font-black text-red-600 mt-1">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">Pembayaran faktur supplier</div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Arus Kas Bersih (Net Cash Flow)</div>
                    <div class="text-2xl font-black {{ $netCashFlow >= 0 ? 'text-emerald-700' : 'text-red-700' }} mt-1">Rp {{ number_format($netCashFlow, 0, ',', '.') }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">Selisih inflow & outflow</div>
                </div>
            </div>
        @else
            <!-- Default Purchases & Debts Table -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Total Tagihan / Pembelian</div>
                    <div class="text-2xl font-black text-slate-900 mt-1">Rp {{ number_format($totalPurchases ?? ($totalDebt ?? 0), 0, ',', '.') }}</div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Jumlah Faktur</div>
                    <div class="text-2xl font-black text-slate-700 mt-1">{{ count($items) }} Dokumen</div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-400 uppercase">Periode Laporan</div>
                    <div class="text-sm font-bold text-slate-800 mt-2">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} &ndash; {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>
                </div>
            </div>
        @endif

        <!-- Data Table (When applicable) -->
        @if(!in_array($type, ['profit_loss', 'cash_flow']))
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. Faktur</th>
                                <th class="py-3 px-4">Supplier</th>
                                <th class="py-3 px-4 text-center">Tgl Faktur</th>
                                <th class="py-3 px-4 text-center">Jatuh Tempo</th>
                                <th class="py-3 px-4 text-right">Total Tagihan</th>
                                <th class="py-3 px-4 text-right">Sudah Dibayar</th>
                                <th class="py-3 px-4 text-right">Sisa Hutang</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($items as $inv)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $inv->invoice_number }}</td>
                                    <td class="py-3 px-4 font-semibold text-slate-800">{{ $inv->supplier?->name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center text-slate-600">{{ $inv->invoice_date?->format('d/m/Y') }}</td>
                                    <td class="py-3 px-4 text-center text-slate-600">{{ $inv->due_date?->format('d/m/Y') ?: '-' }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-slate-900">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right text-emerald-600 font-semibold">Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-amber-700">Rp {{ number_format($inv->remaining_amount, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $inv->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $inv->payment_status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="py-8 text-center text-slate-400">Tidak ada data faktur pembelian / hutang pada filter ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
