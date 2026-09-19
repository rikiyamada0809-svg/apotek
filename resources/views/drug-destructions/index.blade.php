<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pemusnahan Obat</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('stocks.index') }}" class="hover:text-slate-900">Inventori</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Pemusnahan Obat</span>
            </nav>
        </div>
        <a href="{{ route('drug-destructions.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Buat Pemusnahan Baru
        </a>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-fire"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Pemusnahan</p>
                <p class="text-xl font-bold text-slate-800">{{ number_format($totalDestructions) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Menunggu Persetujuan</p>
                <p class="text-xl font-bold text-amber-600">{{ number_format($pendingDestructions) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Telah Dimusnahkan</p>
                <p class="text-xl font-bold text-emerald-600">{{ number_format($completedDestructions) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Kerugian</p>
                <p class="text-lg font-bold text-rose-700">Rp {{ number_format($totalLossAmount, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <form method="GET" action="{{ route('drug-destructions.index') }}" class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Pemusnahan atau Saksi..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-sm"></i>
                </div>
                <select name="status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                    <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                </select>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                @if(request()->hasAny(['search','status','start_date','end_date']))
                    <a href="{{ route('drug-destructions.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                        <th class="px-4 py-3">No. Berita Acara</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Saksi Pemusnahan</th>
                        <th class="px-4 py-3">Metode</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Total Item</th>
                        <th class="px-4 py-3 text-right">Total Kerugian</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($destructions as $dest)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                <a href="{{ route('drug-destructions.show', $dest) }}" class="text-emerald-700 hover:underline">
                                    {{ $dest->destruction_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $dest->destruction_date?->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">{{ $dest->witness_name }}</div>
                                <div class="text-xs text-slate-400">{{ $dest->witness_role }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-700 capitalize">
                                <span class="px-2 py-0.5 text-xs bg-slate-100 rounded-md font-medium">
                                    {{ $dest->method }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusClasses = [
                                        'draft' => 'bg-slate-100 text-slate-700',
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'approved' => 'bg-blue-100 text-blue-700',
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'cancelled' => 'bg-rose-100 text-rose-700',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$dest->status] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst($dest->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-medium text-slate-700">
                                {{ $dest->items->count() }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-rose-700">
                                Rp {{ number_format((float) $dest->total_loss_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('drug-destructions.show', $dest) }}" class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors" title="Detail">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('drug-destructions.pdf', $dest) }}" target="_blank" class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors" title="Cetak Berita Acara (PDF)">
                                        <i class="fa-solid fa-file-pdf text-red-500"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                <i class="fa-solid fa-fire text-3xl text-slate-300 mb-2"></i>
                                <p class="font-medium text-slate-600">Belum ada data pemusnahan obat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($destructions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $destructions->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
