<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Daftar Obat & Batch Kadaluarsa</h1>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('expired.dashboard') }}" class="hover:text-slate-900">Monitoring Expired</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">Daftar Batch</span>
                </nav>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('expired.dashboard') }}" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-xl transition-colors">
                    <i class="fa-solid fa-chart-pie mr-1"></i> Dashboard Alert
                </a>
            </div>
        </div>

        @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
        @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

        <!-- Range Filter Tab Pills -->
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-2">
            <a href="{{ route('expired.index', ['range' => 'all', 'search' => request('search')]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors flex items-center gap-2 {{ $range === 'all' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                <span>Semua Batch Aktif</span>
                <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $range === 'all' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $tabCounts['all'] ?? 0 }}</span>
            </a>

            <a href="{{ route('expired.index', ['range' => 'expired', 'search' => request('search')]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors flex items-center gap-2 {{ $range === 'expired' ? 'bg-red-600 text-white' : 'bg-white text-red-600 hover:bg-red-50 border border-red-200' }}">
                <span><i class="fa-solid fa-skull-crossbones mr-1"></i> Sudah Expired</span>
                <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $range === 'expired' ? 'bg-red-800 text-white' : 'bg-red-100 text-red-700' }}">{{ $tabCounts['expired'] ?? 0 }}</span>
            </a>

            <a href="{{ route('expired.index', ['range' => '1_month', 'search' => request('search')]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors flex items-center gap-2 {{ $range === '1_month' ? 'bg-rose-600 text-white' : 'bg-white text-rose-600 hover:bg-rose-50 border border-rose-200' }}">
                <span><i class="fa-solid fa-triangle-exclamation mr-1"></i> Critical (≤ 1 Bulan)</span>
                <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $range === '1_month' ? 'bg-rose-800 text-white' : 'bg-rose-100 text-rose-700' }}">{{ $tabCounts['1_month'] ?? 0 }}</span>
            </a>

            <a href="{{ route('expired.index', ['range' => '3_months', 'search' => request('search')]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors flex items-center gap-2 {{ $range === '3_months' ? 'bg-amber-600 text-white' : 'bg-white text-amber-700 hover:bg-amber-50 border border-amber-200' }}">
                <span><i class="fa-solid fa-clock-rotate-left mr-1"></i> Warning (≤ 3 Bulan)</span>
                <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $range === '3_months' ? 'bg-amber-800 text-white' : 'bg-amber-100 text-amber-800' }}">{{ $tabCounts['3_months'] ?? 0 }}</span>
            </a>

            <a href="{{ route('expired.index', ['range' => '6_months', 'search' => request('search')]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-colors flex items-center gap-2 {{ $range === '6_months' ? 'bg-yellow-600 text-white' : 'bg-white text-yellow-800 hover:bg-yellow-50 border border-yellow-200' }}">
                <span><i class="fa-solid fa-calendar-days mr-1"></i> Caution (≤ 6 Bulan)</span>
                <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $range === '6_months' ? 'bg-yellow-800 text-white' : 'bg-yellow-100 text-yellow-800' }}">{{ $tabCounts['6_months'] ?? 0 }}</span>
            </a>
        </div>

        <!-- Search & Filter Bar -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <form method="GET" action="{{ route('expired.index') }}" class="flex flex-wrap gap-3">
                    <input type="hidden" name="range" value="{{ $range }}">

                    <div class="relative flex-1 min-w-[240px]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari obat, kode produk, batch, atau supplier..."
                               class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-sm"></i>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg transition-colors">
                        <i class="fa-solid fa-filter mr-1"></i> Filter
                    </button>

                    @if(request('search'))
                        <a href="{{ route('expired.index', ['range' => $range]) }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                            <th class="px-4 py-3">Obat / Produk</th>
                            <th class="px-3 py-3">No. Batch</th>
                            <th class="px-3 py-3">Supplier</th>
                            <th class="px-3 py-3">Tgl Kadaluarsa</th>
                            <th class="px-3 py-3 text-center">Status / Sisa Hari</th>
                            <th class="px-3 py-3 text-right">Stok Fisik</th>
                            <th class="px-3 py-3 text-right">Estimasi Nilai</th>
                            <th class="px-4 py-3 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($batches as $batch)
                            @php
                                $diffDays = (int) now()->startOfDay()->diffInDays($batch->expiry_date->startOfDay(), false);
                                $isExpired = $diffDays <= 0 || $batch->status === 'expired';
                                $val = (float) $batch->current_qty * (float) $batch->purchase_price;
                            @endphp
                            <tr class="hover:bg-slate-50/75 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900">{{ $batch->product?->name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $batch->product?->product_code }} • {{ $batch->product?->category?->name ?? 'Umum' }}</div>
                                </td>
                                <td class="px-3 py-3 font-mono font-semibold text-slate-700 text-xs">
                                    {{ $batch->batch_number }}
                                </td>
                                <td class="px-3 py-3 text-xs text-slate-600">
                                    {{ $batch->supplier?->name ?? '-' }}
                                </td>
                                <td class="px-3 py-3 text-slate-700 text-xs">
                                    {{ $batch->expiry_date?->format('d/m/Y') }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($isExpired)
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-red-100 text-red-700 rounded-full">
                                            <i class="fa-solid fa-skull-crossbones mr-1 text-[10px]"></i> Expired
                                        </span>
                                    @elseif($diffDays <= 30)
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-rose-100 text-rose-700 rounded-full">
                                            <i class="fa-solid fa-triangle-exclamation mr-1 text-[10px]"></i> {{ $diffDays }} hari lagi
                                        </span>
                                    @elseif($diffDays <= 90)
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-amber-100 text-amber-800 rounded-full">
                                            <i class="fa-solid fa-clock mr-1 text-[10px]"></i> {{ $diffDays }} hari lagi
                                        </span>
                                    @elseif($diffDays <= 180)
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-yellow-100 text-yellow-800 rounded-full">
                                            {{ $diffDays }} hari lagi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 rounded-full">
                                            Aman ({{ $diffDays }} hr)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right font-bold text-slate-900">
                                    {{ number_format((float) $batch->current_qty) }}
                                </td>
                                <td class="px-3 py-3 text-right font-medium text-slate-700 text-xs">
                                    Rp {{ number_format($val, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('purchase-returns.create') }}" 
                                           class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg transition-colors inline-flex items-center gap-1"
                                           title="Buat Retur Pembelian ke Supplier">
                                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Retur
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-circle-check text-emerald-500 text-3xl mb-2"></i>
                                    <p class="font-medium text-slate-600">Tidak ada batch obat pada kriteria ini.</p>
                                    <p class="text-xs text-slate-400 mt-1">Coba ganti filter rentang waktu atau kata kunci pencarian.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($batches->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $batches->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
