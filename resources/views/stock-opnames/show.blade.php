<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">{{ $stockOpname->opname_number }}</h1>
                    @php
                        $statusClasses = [
                            'draft' => 'bg-slate-100 text-slate-700',
                            'in_progress' => 'bg-blue-100 text-blue-700',
                            'submitted' => 'bg-amber-100 text-amber-700',
                            'reviewed' => 'bg-purple-100 text-purple-700',
                            'approved' => 'bg-indigo-100 text-indigo-700',
                            'completed' => 'bg-emerald-100 text-emerald-700',
                            'cancelled' => 'bg-rose-100 text-rose-700',
                        ];
                        $statusLabels = [
                            'draft' => 'Draft',
                            'in_progress' => 'Sedang Berjalan',
                            'submitted' => 'Menunggu Review',
                            'reviewed' => 'Sudah Direview',
                            'approved' => 'Disetujui Manajer',
                            'completed' => 'Selesai (Stok Disesuaikan)',
                            'cancelled' => 'Dibatalkan',
                        ];
                    @endphp
                    <span class="px-3 py-1 text-xs font-bold rounded-full {{ $statusClasses[$stockOpname->status] ?? 'bg-slate-100 text-slate-700' }}">
                        {{ $statusLabels[$stockOpname->status] ?? ucfirst($stockOpname->status) }}
                    </span>
                    @if($stockOpname->freeze_stock)
                        <span class="px-2.5 py-1 text-xs font-semibold bg-cyan-100 text-cyan-800 rounded-md">
                            <i class="fa-solid fa-snowflake mr-1"></i> Freeze Active
                        </span>
                    @endif
                </div>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('stock-opnames.index') }}" class="hover:text-slate-900">Stok Opname</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">{{ $stockOpname->opname_number }}</span>
                </nav>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('stock-opnames.index') }}" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-xl transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
                </a>

                @if($stockOpname->status === 'draft')
                    <form method="POST" action="{{ route('stock-opnames.start', $stockOpname) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
                            <i class="fa-solid fa-play"></i> Mulai Opname Fisik
                        </button>
                    </form>
                @endif

                @if(in_array($stockOpname->status, ['in_progress', 'submitted']))
                    <a href="{{ route('stock-opnames.count', $stockOpname) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-barcode"></i> Input Fisik & Barcode
                    </a>
                @endif

                @if($stockOpname->status === 'in_progress')
                    <form method="POST" action="{{ route('stock-opnames.submit', $stockOpname) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
                            <i class="fa-solid fa-paper-plane"></i> Submit Opname
                        </button>
                    </form>
                @endif

                @if(in_array($stockOpname->status, ['submitted', 'reviewed']) && $discrepancyItems > 0)
                    <a href="{{ route('stock-opnames.review', $stockOpname) }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-magnifying-glass-chart"></i> Review Selisih ({{ $discrepancyItems }})
                    </a>
                @endif

                @if(in_array($stockOpname->status, ['submitted', 'reviewed']))
                    <form method="POST" action="{{ route('stock-opnames.approve', $stockOpname) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm flex items-center gap-1.5" onclick="return confirm('Apakah Anda yakin menyetujui penyesuaian stok opname ini?')">
                            <i class="fa-solid fa-check-double"></i> Setujui Penyesuaian
                        </button>
                    </form>
                @endif

                @if($stockOpname->status === 'approved')
                    <form method="POST" action="{{ route('stock-opnames.complete', $stockOpname) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm flex items-center gap-1.5" onclick="return confirm('PENTING: Seluruh stok fisik akan langsung disinkronkan ke sistem dan mutasi stok dicatat. Lanjutkan?')">
                            <i class="fa-solid fa-boxes-packing"></i> Terapkan Penyesuaian & Selesaikan
                        </button>
                    </form>
                @endif

                <a href="{{ route('stock-opnames.print-pdf', $stockOpname) }}" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
                    <i class="fa-solid fa-file-pdf text-red-400"></i> Cetak PDF
                </a>
            </div>
        </div>

        @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
        @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

        <!-- Summary Statistics Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-medium text-slate-500">Total Item</span>
                <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($totalItems) }}</p>
                <span class="text-[11px] text-slate-400">Obat & batch terdaftar</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-medium text-emerald-600">Sesuai (Cocok)</span>
                <p class="text-xl font-bold text-emerald-700 mt-1">{{ number_format($matchedItems) }}</p>
                <span class="text-[11px] text-emerald-500 font-medium">{{ $totalItems > 0 ? round(($matchedItems / $totalItems) * 100) : 0 }}% akurasi</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-medium text-purple-600">Ada Selisih</span>
                <p class="text-xl font-bold text-purple-700 mt-1">{{ number_format($discrepancyItems) }}</p>
                <span class="text-[11px] text-slate-400">Item perlu penyesuaian</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-medium text-rose-600">Defisit (Kurang)</span>
                <p class="text-xl font-bold text-rose-700 mt-1">{{ number_format($deficitCount) }}</p>
                <span class="text-[11px] text-rose-500 font-medium">Stok fisik < sistem</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-medium text-blue-600">Surplus (Lebih)</span>
                <p class="text-xl font-bold text-blue-700 mt-1">{{ number_format($surplusCount) }}</p>
                <span class="text-[11px] text-blue-500 font-medium">Stok fisik > sistem</span>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Informasi Pelaksanaan Opname</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 text-sm">
                <div>
                    <span class="text-xs text-slate-400 font-medium">Gudang Target</span>
                    <p class="font-semibold text-slate-800 mt-0.5">{{ $stockOpname->warehouse?->name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-medium">Cakupan (Scope)</span>
                    <p class="font-semibold text-slate-800 mt-0.5 capitalize">{{ $stockOpname->scope }}</p>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-medium">Tanggal Opname</span>
                    <p class="font-semibold text-slate-800 mt-0.5">{{ $stockOpname->opname_date?->format('d F Y') }}</p>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-medium">Dibuat Oleh</span>
                    <p class="font-semibold text-slate-800 mt-0.5">{{ $stockOpname->initiator?->name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-medium">Waktu Dimulai</span>
                    <p class="font-semibold text-slate-800 mt-0.5">{{ $stockOpname->started_at?->format('d/m/Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-medium">Disetujui Oleh</span>
                    <p class="font-semibold text-slate-800 mt-0.5">{{ $stockOpname->approver?->name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-medium">Waktu Selesai</span>
                    <p class="font-semibold text-slate-800 mt-0.5">{{ $stockOpname->completed_at?->format('d/m/Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-medium">Status Freeze Stok</span>
                    <p class="font-semibold mt-0.5 {{ $stockOpname->freeze_stock ? 'text-cyan-600' : 'text-slate-600' }}">
                        {{ $stockOpname->freeze_stock ? 'Aktif (Dibekukan)' : 'Tidak Aktif' }}
                    </p>
                </div>
            </div>
            @if($stockOpname->notes)
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <span class="text-xs text-slate-400 font-medium">Catatan:</span>
                    <p class="text-sm text-slate-700 mt-1">{{ $stockOpname->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ search: '', filter: 'all' }">
            <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-sm font-bold text-slate-900">Daftar Item Stok Opname ({{ $stockOpname->items->count() }})</h3>
                
                <div class="flex items-center gap-2">
                    <select x-model="filter" class="px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none">
                        <option value="all">Semua Item</option>
                        <option value="discrepancy">Hanya Ada Selisih</option>
                        <option value="deficit">Hanya Defisit (< 0)</option>
                        <option value="surplus">Hanya Surplus (> 0)</option>
                        <option value="matched">Hanya Sesuai (= 0)</option>
                    </select>

                    <div class="relative">
                        <input type="text" x-model="search" placeholder="Cari nama/kode/batch..."
                               class="pl-8 pr-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none w-48 sm:w-60">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-2.5 top-2 text-[11px]"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                            <th class="px-4 py-3 text-center w-12">#</th>
                            <th class="px-4 py-3">Obat / Produk</th>
                            <th class="px-4 py-3">No. Batch</th>
                            <th class="px-4 py-3">Lokasi Rak</th>
                            <th class="px-4 py-3 text-right">Qty Sistem</th>
                            <th class="px-4 py-3 text-right">Qty Fisik</th>
                            <th class="px-4 py-3 text-center">Selisih</th>
                            <th class="px-4 py-3">Alasan Selisih</th>
                            <th class="px-4 py-3 text-center">Penyesuaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($stockOpname->items as $index => $item)
                            @php
                                $diff = (float) $item->discrepancy;
                                $rowType = $diff == 0 ? 'matched' : ($diff < 0 ? 'deficit' : 'surplus');
                            @endphp
                            <tr class="hover:bg-slate-50/75 transition-colors"
                                x-show="
                                    (filter === 'all' || 
                                     (filter === 'discrepancy' && '{{ $rowType }}' !== 'matched') || 
                                     (filter === 'deficit' && '{{ $rowType }}' === 'deficit') || 
                                     (filter === 'surplus' && '{{ $rowType }}' === 'surplus') || 
                                     (filter === 'matched' && '{{ $rowType }}' === 'matched'))
                                    && 
                                    (search === '' || 
                                     '{{ strtolower($item->product?->name . ' ' . $item->product?->product_code . ' ' . ($item->batch?->batch_number ?? '')) }}'.includes(search.toLowerCase()))
                                ">
                                <td class="px-4 py-3 text-center text-xs text-slate-400">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $item->product?->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-400">{{ $item->product?->product_code ?? '-' }} • {{ $item->product?->category?->name ?? 'Umum' }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-700 font-mono text-xs">
                                    {{ $item->batch?->batch_number ?? '-' }}
                                    @if($item->batch?->expiry_date)
                                        <div class="text-[10px] text-slate-400">Exp: {{ $item->batch->expiry_date->format('d/m/Y') }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600 text-xs">
                                    {{ $item->storageLocation?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-slate-700">
                                    {{ number_format((float) $item->system_qty, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-slate-900">
                                    {{ number_format((float) $item->physical_qty, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($diff > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded-md">
                                            +{{ number_format($diff, 2) }}
                                        </span>
                                    @elseif($diff < 0)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold bg-rose-100 text-rose-700 rounded-md">
                                            {{ number_format($diff, 2) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-emerald-600">
                                            <i class="fa-solid fa-check mr-1"></i> Cocok
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600">
                                    {{ $item->discrepancy_reason ?: ($diff != 0 ? '-' : 'Sesuai') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($item->adjustment_approved)
                                        <span class="px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-700 rounded-md">
                                            Diterapkan
                                        </span>
                                    @elseif($stockOpname->status === 'approved')
                                        <span class="px-2 py-0.5 text-[11px] font-semibold bg-indigo-100 text-indigo-700 rounded-md">
                                            Disetujui
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">Belum</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
