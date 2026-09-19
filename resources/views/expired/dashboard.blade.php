<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Dashboard Monitoring Kadaluarsa (Expired)</h1>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('stocks.index') }}" class="hover:text-slate-900">Inventori</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">Monitoring Expired</span>
                </nav>
            </div>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('expired.run-check') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition-colors shadow-sm"
                            title="Jalankan pemindaian otomatis status expired hari ini">
                        <i class="fa-solid fa-arrows-rotate"></i> Scan & Sinkronisasi Expired
                    </button>
                </form>

                <a href="{{ route('expired.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition-colors shadow-sm">
                    <i class="fa-solid fa-table-list"></i> Lihat Semua Daftar Obat
                </a>
            </div>
        </div>

        @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
        @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

        <!-- 5 Alert Level Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Level 1: Sudah Expired -->
            <a href="{{ route('expired.index', ['range' => 'expired']) }}" 
               class="bg-white p-4 rounded-2xl border border-red-200 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-red-600 uppercase tracking-wider">Sudah Expired</span>
                    <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-skull-crossbones"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-black text-red-700">{{ number_format($expiredCount) }}</p>
                    <p class="text-xs text-slate-500 font-medium mt-1">Estimasi Kerugian:</p>
                    <p class="text-xs font-bold text-red-600">Rp {{ number_format($expiredEstimatedLoss, 0, ',', '.') }}</p>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-red-600 font-semibold">
                    <span>Lihat detail</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </div>
            </a>

            <!-- Level 2: Critical (<= 30 Hari) -->
            <a href="{{ route('expired.index', ['range' => '1_month']) }}" 
               class="bg-white p-4 rounded-2xl border border-rose-200 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Critical (≤ 30 Hari)</span>
                    <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-black text-rose-700">{{ number_format($criticalCount) }}</p>
                    <p class="text-xs text-slate-500 font-medium mt-1">Nilai Aset Terancam:</p>
                    <p class="text-xs font-bold text-rose-600">Rp {{ number_format($criticalEstimatedValue, 0, ',', '.') }}</p>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-rose-600 font-semibold">
                    <span>Prioritas penjualan/retur</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </div>
            </a>

            <!-- Level 3: Warning (31 - 90 Hari) -->
            <a href="{{ route('expired.index', ['range' => '3_months']) }}" 
               class="bg-white p-4 rounded-2xl border border-amber-200 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Warning (1-3 Bulan)</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-black text-amber-700">{{ number_format($warningCount) }}</p>
                    <p class="text-xs text-slate-500 font-medium mt-1">Rentang 31 - 90 hari</p>
                    <p class="text-xs text-slate-400">Siapkan strategi FEFO</p>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-amber-600 font-semibold">
                    <span>Lihat batch</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </div>
            </a>

            <!-- Level 4: Caution (91 - 180 Hari) -->
            <a href="{{ route('expired.index', ['range' => '6_months']) }}" 
               class="bg-white p-4 rounded-2xl border border-yellow-200 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-yellow-700 uppercase tracking-wider">Caution (3-6 Bulan)</span>
                    <div class="w-8 h-8 rounded-lg bg-yellow-100 text-yellow-700 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-black text-yellow-800">{{ number_format($cautionCount) }}</p>
                    <p class="text-xs text-slate-500 font-medium mt-1">Rentang 91 - 180 hari</p>
                    <p class="text-xs text-slate-400">Monitoring berkala</p>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-yellow-700 font-semibold">
                    <span>Lihat batch</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </div>
            </a>

            <!-- Level 5: Normal (> 180 Hari) -->
            <a href="{{ route('expired.index', ['range' => 'all']) }}" 
               class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Normal (> 6 Bulan)</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-black text-emerald-700">{{ number_format($normalCount) }}</p>
                    <p class="text-xs text-slate-500 font-medium mt-1">Kondisi Aman</p>
                    <p class="text-xs text-slate-400">Masa simpan panjang</p>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-emerald-600 font-semibold">
                    <span>Semua batch</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </div>
            </a>
        </div>

        <!-- Two Column Layout: Urgent Batches Table & Category Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Urgent Batches (8 cols) -->
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></div>
                        <h3 class="text-sm font-bold text-slate-900">Batch Paling Kritis / Segera Kadaluarsa (Top 10)</h3>
                    </div>
                    <a href="{{ route('expired.index', ['range' => '1_month']) }}" class="text-xs text-emerald-600 hover:text-emerald-800 font-semibold">
                        Lihat Selengkapnya &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                                <th class="px-4 py-3">Obat / Produk</th>
                                <th class="px-3 py-3">Batch</th>
                                <th class="px-3 py-3">Tgl Kadaluarsa</th>
                                <th class="px-3 py-3 text-center">Sisa Hari</th>
                                <th class="px-3 py-3 text-right">Sisa Stok</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($urgentBatches as $batch)
                                @php
                                    $diffDays = (int) now()->startOfDay()->diffInDays($batch->expiry_date->startOfDay(), false);
                                    $isExpired = $diffDays <= 0 || $batch->status === 'expired';
                                @endphp
                                <tr class="hover:bg-slate-50/75 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-slate-900">{{ $batch->product?->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $batch->product?->product_code }} • {{ $batch->product?->category?->name ?? 'Umum' }}</div>
                                    </td>
                                    <td class="px-3 py-3 font-mono font-semibold text-slate-700">
                                        {{ $batch->batch_number }}
                                    </td>
                                    <td class="px-3 py-3 text-slate-600">
                                        {{ $batch->expiry_date?->format('d/m/Y') }}
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        @if($isExpired)
                                            <span class="px-2 py-0.5 font-bold bg-red-100 text-red-700 rounded-md">
                                                Expired
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 font-bold {{ $diffDays <= 30 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }} rounded-md">
                                                {{ $diffDays }} hari lagi
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right font-bold text-slate-800">
                                        {{ number_format((float) $batch->current_qty) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('purchase-returns.create') }}" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-lg text-[11px] transition-colors inline-flex items-center gap-1">
                                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Retur
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                        <i class="fa-solid fa-circle-check text-emerald-500 text-2xl mb-1"></i>
                                        <p>Tidak ada batch kritis atau expired saat ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Category Breakdown (4 cols) -->
            <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
                        Kategori Terancam Kadaluarsa
                    </h3>
                    <div class="space-y-3">
                        @forelse($categories as $cat)
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-tag text-slate-400 text-xs"></i>
                                    <span class="text-xs font-semibold text-slate-800">{{ $cat->name }}</span>
                                </div>
                                <span class="px-2 py-0.5 text-xs font-bold bg-amber-100 text-amber-800 rounded-md">
                                    {{ $cat->expiring_batches_count }} batch
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">Semua kategori dalam kondisi aman.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-6 p-4 rounded-xl bg-blue-50 border border-blue-200">
                    <div class="flex items-start gap-2.5">
                        <i class="fa-solid fa-circle-info text-blue-600 text-sm mt-0.5"></i>
                        <div class="text-xs text-blue-900">
                            <strong>Prinsip FEFO (First-Expired, First-Out):</strong>
                            <p class="mt-1 text-blue-700">Sistem kasir (POS) otomatis merekomendasikan batch dengan masa kedaluwarsa terdekat untuk dikeluarkan terlebih dahulu.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
