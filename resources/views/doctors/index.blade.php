<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Dokter Mitra / Penulis Resep</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Dokter</span>
            </nav>
        </div>
        <a href="{{ route('doctors.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Tambah Dokter
        </a>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <form method="GET" action="{{ route('doctors.index') }}" class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama dokter, no SIP, atau spesialisasi..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-sm"></i>
                </div>
                <select name="status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="1" @selected(request('status') === '1')>Aktif</option>
                    <option value="0" @selected(request('status') === '0')>Nonaktif</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg transition-colors">Filter</button>
                @if(request()->hasAny(['search','status']))
                    <a href="{{ route('doctors.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">#</th>
                        <th class="px-4 py-3 text-left font-semibold">Nama Dokter</th>
                        <th class="px-4 py-3 text-left font-semibold">Nomor SIP</th>
                        <th class="px-4 py-3 text-left font-semibold">Spesialisasi</th>
                        <th class="px-4 py-3 text-left font-semibold">Klinik / RS</th>
                        <th class="px-4 py-3 text-left font-semibold">Telepon</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($doctors as $doctor)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-slate-500">{{ $loop->iteration + ($doctors->currentPage() - 1) * $doctors->perPage() }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-user-doctor text-emerald-600"></i>
                                    <span>{{ $doctor->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $doctor->sip_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">
                                @if($doctor->specialization)
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $doctor->specialization }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">Umum</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $doctor->clinic_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $doctor->phone ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <x-status-badge :active="$doctor->is_active" />
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('doctors.edit', $doctor) }}" class="text-blue-500 hover:text-blue-700 p-1.5 rounded-lg hover:bg-blue-50 transition-colors" title="Edit">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </a>
                                    <x-delete-button :action="route('doctors.destroy', $doctor)" confirmText="Apakah Anda yakin ingin menghapus dokter ini?" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-user-doctor text-3xl mb-2"></i>
                                <p>Belum ada data dokter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($doctors->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $doctors->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
