<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Gudang & Lokasi Penyimpanan</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Gudang</span>
            </nav>
        </div>
        <a href="{{ route('warehouses.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Tambah Gudang
        </a>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <form method="GET" action="{{ route('warehouses.index') }}" class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama gudang..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-sm"></i>
                </div>
                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg transition-colors">Filter</button>
                @if(request('search'))
                    <a href="{{ route('warehouses.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">#</th>
                        <th class="px-4 py-3 text-left font-semibold">Nama Gudang</th>
                        <th class="px-4 py-3 text-left font-semibold">Tipe Gudang</th>
                        <th class="px-4 py-3 text-left font-semibold">Alamat</th>
                        <th class="px-4 py-3 text-center font-semibold">Jumlah Lokasi / Rak</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($warehouses as $warehouse)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-slate-500">{{ $loop->iteration + ($warehouses->currentPage() - 1) * $warehouses->perPage() }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                <a href="{{ route('warehouses.show', $warehouse) }}" class="hover:text-emerald-600 transition-colors">
                                    {{ $warehouse->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                @if($warehouse->type === 'main')
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Gudang Utama
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        Gudang Cabang
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 max-w-xs truncate">{{ $warehouse->address ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('warehouses.show', $warehouse) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                                    <i class="fa-solid fa-layer-group text-[10px]"></i>
                                    {{ $warehouse->storage_locations_count }} Lokasi
                                </a>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <x-status-badge :active="$warehouse->is_active" />
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('warehouses.show', $warehouse) }}" class="text-slate-500 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition-colors" title="Lihat Lokasi & Rak">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('warehouses.edit', $warehouse) }}" class="text-blue-500 hover:text-blue-700 p-1.5 rounded-lg hover:bg-blue-50 transition-colors" title="Edit">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </a>
                                    <x-delete-button :action="route('warehouses.destroy', $warehouse)" confirmText="Apakah Anda yakin ingin menghapus gudang ini?" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-warehouse text-3xl mb-2"></i>
                                <p>Belum ada data gudang.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($warehouses->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $warehouses->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
