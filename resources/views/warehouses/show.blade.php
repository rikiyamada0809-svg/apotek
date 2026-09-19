<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Detail Gudang & Lokasi Rak</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('warehouses.index') }}" class="hover:text-slate-900">Gudang</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">{{ $warehouse->name }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('storage-locations.create', $warehouse) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-plus"></i> Tambah Lokasi / Rak
            </a>
            <a href="{{ route('warehouses.edit', $warehouse) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-pen-to-square"></i> Edit Gudang
            </a>
            <a href="{{ route('warehouses.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <!-- Warehouse Overview Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold border border-emerald-100 shrink-0">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $warehouse->name }}</h2>
                    <div class="flex items-center gap-3 mt-1">
                        @if($warehouse->type === 'main')
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Gudang Utama
                            </span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                Gudang Cabang / Depo
                            </span>
                        @endif
                        <x-status-badge :active="$warehouse->is_active" />
                    </div>
                </div>
            </div>

            <div class="text-sm text-slate-600 sm:text-right">
                <span class="text-xs text-slate-400 block">Alamat Gudang</span>
                <span class="font-medium text-slate-700">{{ $warehouse->address ?? '-' }}</span>
            </div>
        </div>
    </div>

    <!-- Storage Locations Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-emerald-600"></i> Daftar Lokasi Penyimpanan (Rak, Kulkas, Lemari)
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Titik penyimpanan spesifik obat di dalam gudang ini.</p>
            </div>
            <a href="{{ route('storage-locations.create', $warehouse) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-semibold rounded-lg transition-colors">
                <i class="fa-solid fa-plus text-[10px]"></i> Tambah Lokasi
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold w-16">#</th>
                        <th class="px-4 py-3 text-left font-semibold">Kode Lokasi</th>
                        <th class="px-4 py-3 text-left font-semibold">Nama Lokasi / Rak</th>
                        <th class="px-4 py-3 text-left font-semibold">Tipe Penyimpanan</th>
                        <th class="px-4 py-3 text-left font-semibold">Rentang Suhu</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($warehouse->storageLocations as $location)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-mono font-bold text-slate-800">{{ $location->code }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $location->name }}</td>
                            <td class="px-4 py-3">
                                @if($location->type === 'kulkas')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="fa-solid fa-snowflake text-[10px]"></i> Kulkas / Cold Chain
                                    </span>
                                @elseif($location->type === 'lemari_narkotika')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                        <i class="fa-solid fa-lock text-[10px]"></i> Lemari Narkotika
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        <i class="fa-solid fa-cubes text-[10px]"></i> Rak Biasa
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $location->temperature_range ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <x-status-badge :active="$location->is_active" />
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('storage-locations.edit', [$warehouse, $location]) }}" class="text-blue-500 hover:text-blue-700 p-1.5 rounded-lg hover:bg-blue-50 transition-colors" title="Edit Lokasi">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </a>
                                    <x-delete-button :action="route('storage-locations.destroy', [$warehouse, $location])" confirmText="Apakah Anda yakin ingin menghapus lokasi penyimpanan ini?" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-boxes-stacked text-3xl mb-2"></i>
                                <p>Belum ada lokasi penyimpanan / rak di gudang ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
