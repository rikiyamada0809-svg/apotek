<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Penarikan Obat (Batch Recall)</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('stocks.index') }}" class="hover:text-slate-900">Inventori</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Batch Recall</span>
            </nav>
        </div>
        <a href="{{ route('batch-recalls.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-triangle-exclamation"></i> Inisiasi Recall Baru
        </a>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Kasus Recall</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total']) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Sedang Diproses / Karantina</p>
                <p class="text-2xl font-bold text-amber-600">{{ number_format($stats['active']) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Kasus Selesai</p>
                <p class="text-2xl font-bold text-emerald-600">{{ number_format($stats['completed']) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-box-archive"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Obat Ditarik</p>
                <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_recovered']) }} <span class="text-xs font-normal text-slate-400">Unit</span></p>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-100">
            <form method="GET" action="{{ route('batch-recalls.index') }}" class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari No. Recall, Batch, atau Obat..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-rose-500">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-sm"></i>
                </div>

                <select name="status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-rose-500">
                    <option value="">Semua Status</option>
                    <option value="quarantined" {{ $status === 'quarantined' ? 'selected' : '' }}>Dikarantina (Quarantined)</option>
                    <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>Proses Penarikan (In Progress)</option>
                    <option value="returned_to_supplier" {{ $status === 'returned_to_supplier' ? 'selected' : '' }}>Retur ke Supplier</option>
                    <option value="destroyed" {{ $status === 'destroyed' ? 'selected' : '' }}>Dimusnahkan</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai (Completed)</option>
                </select>

                <select name="source" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-rose-500">
                    <option value="">Semua Sumber Instruksi</option>
                    <option value="BPOM" {{ $source === 'BPOM' ? 'selected' : '' }}>BPOM RI</option>
                    <option value="Produsen" {{ $source === 'Produsen' ? 'selected' : '' }}>Produsen / Pabrik</option>
                    <option value="Kemenkes" {{ $source === 'Kemenkes' ? 'selected' : '' }}>Kemenkes RI</option>
                    <option value="Internal" {{ $source === 'Internal' ? 'selected' : '' }}>Temuan Internal Apotek</option>
                </select>

                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-rose-500 text-xs">
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-rose-500 text-xs">

                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg transition-colors">
                    Filter
                </button>
                @if($search || $status || $source || $startDate || $endDate)
                    <a href="{{ route('batch-recalls.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-lg transition-colors">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. Recall</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Produk Obat & Batch</th>
                        <th class="py-3 px-4">Sumber & Alasan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center min-w-[160px]">Progres Penarikan</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recalls as $recall)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                <a href="{{ route('batch-recalls.show', $recall) }}" class="text-rose-600 hover:underline">
                                    {{ $recall->recall_number }}
                                </a>
                            </td>
                            <td class="py-3 px-4 text-slate-600 text-xs">
                                {{ $recall->recall_date?->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-900">{{ $recall->batch?->product?->name }}</div>
                                <div class="text-xs text-slate-500 font-mono">
                                    Batch: <span class="font-bold text-slate-700">{{ $recall->batch?->batch_number }}</span>
                                    @if($recall->batch?->expiry_date)
                                        (Exp: {{ $recall->batch->expiry_date->format('d/m/Y') }})
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 text-xs">
                                <span class="inline-flex items-center gap-1 font-semibold text-slate-800">
                                    <i class="fa-solid fa-bullhorn text-rose-500 text-[10px]"></i>
                                    {{ $recall->recall_source }}
                                </span>
                                <div class="text-slate-500 truncate max-w-xs mt-0.5" title="{{ $recall->recall_reason }}">
                                    {{ $recall->recall_reason }}
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($recall->status === 'quarantined')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-lock text-[10px]"></i> Dikarantina
                                    </span>
                                @elseif($recall->status === 'in_progress')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        <i class="fa-solid fa-clock-rotate-left text-[10px]"></i> Proses Penarikan
                                    </span>
                                @elseif($recall->status === 'returned_to_supplier')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                        <i class="fa-solid fa-truck-ramp-box text-[10px]"></i> Retur Supplier
                                    </span>
                                @elseif($recall->status === 'destroyed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        <i class="fa-solid fa-fire text-[10px]"></i> Dimusnahkan
                                    </span>
                                @elseif($recall->status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i> Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ ucfirst($recall->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="w-full">
                                    <div class="flex items-center justify-between text-xs font-semibold mb-1">
                                        <span class="text-slate-700 font-mono">{{ number_format($recall->recovered_qty, 0) }} / {{ number_format($recall->sold_qty, 0) }} Unit</span>
                                        <span class="text-rose-600 font-mono">{{ $recall->recovery_percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                        <div class="bg-rose-600 h-2 rounded-full transition-all duration-300" style="width: {{ $recall->recovery_percentage }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('batch-recalls.show', $recall) }}" class="p-1.5 text-slate-400 hover:text-rose-600 rounded transition-colors" title="Kelola Penarikan">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('batch-recalls.pdf', $recall) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-rose-600 rounded transition-colors" title="Berita Acara (PDF)">
                                        <i class="fa-solid fa-file-pdf text-sm"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500">
                                <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <p class="font-semibold text-slate-700 text-base">Tidak Ada Kasus Penarikan Obat</p>
                                <p class="text-xs text-slate-400 mt-1">Seluruh batch obat berada dalam status aman dan layak edar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recalls->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $recalls->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
