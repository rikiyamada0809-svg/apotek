<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Resep Obat</h1>
                <p class="text-sm text-slate-500 mt-1">Kelola resep dokter, verifikasi apoteker, dan integrasi racikan obat.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('compound-templates.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-semibold rounded-xl shadow-sm transition-all">
                    <i class="fa-solid fa-mortar-pestle text-indigo-600"></i> Template Racikan
                </a>
                <a href="{{ route('prescriptions.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition-all">
                    <i class="fa-solid fa-plus"></i> Buat Resep Baru
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('warning'))
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-base"></i>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-file-prescription"></i>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Resep</div>
                    <div class="text-2xl font-bold text-slate-900">{{ number_format($totalPrescriptions) }}</div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Menunggu Validasi</div>
                    <div class="text-2xl font-bold text-amber-600">{{ number_format($submittedPrescriptions) }}</div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-file-circle-check"></i>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Telah Divalidasi</div>
                    <div class="text-2xl font-bold text-indigo-600">{{ number_format($validatedPrescriptions) }}</div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-hand-holding-medical"></i>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Selesai Diserahkan</div>
                    <div class="text-2xl font-bold text-emerald-600">{{ number_format($dispensedPrescriptions) }}</div>
                </div>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
            <form method="GET" action="{{ route('prescriptions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <div class="lg:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. resep, diagnosis, pasien..." 
                           class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>

                <div>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="submitted" @selected(request('status') === 'submitted')>Menunggu Validasi</option>
                        <option value="validated" @selected(request('status') === 'validated')>Divalidasi</option>
                        <option value="dispensed" @selected(request('status') === 'dispensed')>Selesai (Dispensed)</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
                    </select>
                </div>

                <div>
                    <select name="doctor_id" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">Semua Dokter</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}" @selected(request('doctor_id') == $doc->id)>{{ $doc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>

                <div class="flex items-center gap-2">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <button type="submit" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition shrink-0">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    @if(request()->hasAny(['search', 'status', 'doctor_id', 'start_date', 'end_date']))
                        <a href="{{ route('prescriptions.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition shrink-0" title="Reset Filter">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-6">No. Resep</th>
                            <th class="py-3.5 px-4">Tanggal</th>
                            <th class="py-3.5 px-4">Pasien</th>
                            <th class="py-3.5 px-4">Dokter & Diagnosis</th>
                            <th class="py-3.5 px-4 text-center">Item Obat</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($prescriptions as $prescription)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-6 font-mono font-semibold text-slate-800">
                                    <a href="{{ route('prescriptions.show', $prescription) }}" class="text-blue-600 hover:underline">
                                        {{ $prescription->prescription_number }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ \Carbon\Carbon::parse($prescription->prescription_date)->format('d/m/Y') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-900">{{ $prescription->customer?->name ?? 'Pasien Umum' }}</div>
                                    <div class="text-xs text-slate-400">{{ $prescription->customer?->phone ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-800">{{ $prescription->doctor?->name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500 truncate max-w-xs">{{ $prescription->diagnosis ?: 'Tanpa diagnosis spesifik' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $prescription->items->count() }} item
                                    </span>
                                    @if($prescription->compounds->isNotEmpty())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 ml-1">
                                            {{ $prescription->compounds->count() }} racikan
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @php
                                        $badge = match($prescription->status) {
                                            'submitted' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Menunggu Validasi'],
                                            'validated' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'label' => 'Divalidasi'],
                                            'dispensed' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Selesai Diserahkan'],
                                            'rejected' => ['bg' => 'bg-red-50 text-red-700 border-red-200', 'label' => 'Ditolak'],
                                            'cancelled' => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => 'Dibatalkan'],
                                            default => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => ucfirst($prescription->status)]
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badge['bg'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-right space-x-2">
                                    <a href="{{ route('prescriptions.show', $prescription) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </a>
                                    @if($prescription->status === 'validated')
                                        <a href="{{ route('compounds.create', ['prescription_id' => $prescription->id]) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                            <i class="fa-solid fa-mortar-pestle"></i> Racik
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-file-prescription text-4xl mb-3 text-slate-300"></i>
                                    <p>Belum ada data resep obat.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($prescriptions->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $prescriptions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
