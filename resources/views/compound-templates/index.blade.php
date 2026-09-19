<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Template Racikan Obat</h1>
                <p class="text-sm text-slate-500 mt-1">Kelola formula standar obat racikan (puyer, kapsul, salep) untuk mempercepat proses racik di apotek.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('prescriptions.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-semibold rounded-xl shadow-sm transition-all">
                    <i class="fa-solid fa-file-prescription text-blue-600"></i> Resep Obat
                </a>
                <a href="{{ route('compound-templates.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition-all">
                    <i class="fa-solid fa-plus"></i> Tambah Template
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Grid Template Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($templates as $template)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold shrink-0">
                                <i class="fa-solid fa-mortar-pestle"></i>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                Jasa: Rp {{ number_format($template->service_fee, 0, ',', '.') }}
                            </span>
                        </div>

                        <h3 class="font-bold text-slate-900 text-lg mb-1">{{ $template->name }}</h3>
                        <p class="text-xs text-slate-500 mb-4 line-clamp-2">{{ $template->description ?: 'Formula racikan standar farmasi apotek.' }}</p>

                        <!-- Komposisi Bahan -->
                        <div class="space-y-1.5 border-t border-slate-100 pt-3">
                            <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Komposisi per bungkus/unit:</span>
                            @foreach($template->items as $item)
                                <div class="flex items-center justify-between text-xs py-1 border-b border-slate-50">
                                    <span class="text-slate-800 font-medium truncate pr-2">{{ $item->product?->name ?? 'Bahan' }}</span>
                                    <span class="text-slate-500 shrink-0 font-mono">{{ number_format($item->quantity_per_unit, 2) }} {{ $item->unit?->name ?? 'unit' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-400">
                            Digunakan {{ $template->compounds_count }}x
                        </span>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('compounds.create', ['template_id' => $template->id]) }}" class="px-3 py-1.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors flex items-center gap-1">
                                <i class="fa-solid fa-bolt"></i> Racik
                            </a>
                            <a href="{{ route('compound-templates.edit', $template) }}" class="p-1.5 text-slate-500 hover:text-blue-600 rounded-lg transition-colors" title="Edit Template">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <form method="POST" action="{{ route('compound-templates.destroy', $template) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template racikan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg transition-colors" title="Hapus Template">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-slate-200">
                    <i class="fa-solid fa-mortar-pestle text-4xl text-slate-300 mb-3"></i>
                    <h3 class="text-base font-bold text-slate-700">Belum Ada Template Racikan</h3>
                    <p class="text-xs text-slate-500 mt-1 mb-4">Buat template racikan standar (seperti Puyer Batuk, Salep Gatal, Kapsul Flu) untuk memudahkan staf apotek.</p>
                    <a href="{{ route('compound-templates.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition">
                        <i class="fa-solid fa-plus"></i> Buat Template Sekarang
                    </a>
                </div>
            @endforelse
        </div>

        @if($templates->hasPages())
            <div class="mt-6">
                {{ $templates->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
