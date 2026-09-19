<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Stok Opname</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('stocks.index') }}" class="hover:text-slate-900">Inventori</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Stok Opname</span>
            </nav>
        </div>
        <a href="{{ route('stock-opnames.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Buat Stok Opname
        </a>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Opname</p>
                <p class="text-xl font-bold text-slate-800">{{ number_format($totalOpnames) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-slate-100 text-slate-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-file-pen"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Draft Opname</p>
                <p class="text-xl font-bold text-slate-700">{{ number_format($draftOpnames) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-spinner fa-spin-pulse"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Sedang Berlangsung</p>
                <p class="text-xl font-bold text-amber-600">{{ number_format($inProgressOpnames) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Selesai / Sesuai</p>
                <p class="text-xl font-bold text-emerald-600">{{ number_format($completedOpnames) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <form method="GET" action="{{ route('stock-opnames.index') }}" class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Opname atau Catatan..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-sm"></i>
                </div>
                <select name="warehouse_id" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" @selected(request('warehouse_id') == $wh->id)>{{ $wh->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                    <option value="submitted" @selected(request('status') === 'submitted')>Submitted</option>
                    <option value="reviewed" @selected(request('status') === 'reviewed')>Reviewed</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                @if(request()->hasAny(['search','warehouse_id','status','start_date','end_date']))
                    <a href="{{ route('stock-opnames.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                        <th class="px-4 py-3">No. Opname</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Gudang</th>
                        <th class="px-4 py-3">Cakupan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Total Item</th>
                        <th class="px-4 py-3 text-center">Selisih</th>
                        <th class="px-4 py-3 text-center">Freeze</th>
                        <th class="px-4 py-3">Inisiator</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($opnames as $opname)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                <a href="{{ route('stock-opnames.show', $opname) }}" class="text-emerald-700 hover:underline">
                                    {{ $opname->opname_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $opname->opname_date?->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $opname->warehouse?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 capitalize">
                                <span class="px-2 py-0.5 text-xs rounded-md bg-slate-100 font-medium">
                                    {{ $opname->scope }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
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
                                        'in_progress' => 'In Progress',
                                        'submitted' => 'Submitted',
                                        'reviewed' => 'Reviewed',
                                        'approved' => 'Approved',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$opname->status] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $statusLabels[$opname->status] ?? ucfirst($opname->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-medium text-slate-700">
                                {{ number_format($opname->total_items) }}
                            </td>
                            <td class="px-4 py-3 text-center font-medium">
                                @if($opname->discrepancy_items > 0)
                                    <span class="px-2 py-0.5 text-xs font-bold bg-rose-100 text-rose-700 rounded-full">
                                        {{ number_format($opname->discrepancy_items) }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">0</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($opname->freeze_stock)
                                    <span class="px-2 py-0.5 text-[11px] font-semibold bg-cyan-100 text-cyan-800 rounded-md" title="Stok gudang dibekukan saat opname aktif">
                                        <i class="fa-solid fa-snowflake mr-0.5"></i> Freeze
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $opname->initiator?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('stock-opnames.show', $opname) }}" class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors" title="Detail Opname">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </a>
                                    @if(in_array($opname->status, ['in_progress', 'submitted']))
                                        <a href="{{ route('stock-opnames.count', $opname) }}" class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors" title="Input Fisik">
                                            <i class="fa-solid fa-barcode text-sm"></i>
                                        </a>
                                    @endif
                                    @if($opname->status === 'completed')
                                        <a href="{{ route('stock-opnames.print-pdf', $opname) }}" target="_blank" class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors" title="Cetak PDF">
                                            <i class="fa-solid fa-file-pdf text-sm text-red-600"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-clipboard-check text-3xl text-slate-300 mb-2"></i>
                                    <p class="font-medium text-slate-600">Belum ada data stok opname.</p>
                                    <p class="text-xs text-slate-400 mt-1">Klik tombol "Buat Stok Opname" untuk memulai opname baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($opnames->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $opnames->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
