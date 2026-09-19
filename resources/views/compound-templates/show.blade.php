<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $compoundTemplate->name }}</h1>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('compound-templates.index') }}" class="hover:text-slate-900">Template Racikan</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">{{ $compoundTemplate->name }}</span>
                </nav>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('compound-templates.index') }}" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
                </a>
                <a href="{{ route('compound-templates.edit', $compoundTemplate) }}" class="px-3.5 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg shadow-sm transition-colors">
                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                </a>
                <a href="{{ route('compounds.create', ['template_id' => $compoundTemplate->id]) }}" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
                    <i class="fa-solid fa-bolt mr-1"></i> Racik Sekarang
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Jasa Racik Standar</span>
                    <span class="font-bold text-emerald-600 text-lg">Rp {{ number_format($compoundTemplate->service_fee, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Dibuat Oleh</span>
                    <span class="font-medium text-slate-800">{{ $compoundTemplate->creator?->name ?? 'Apoteker' }}</span>
                </div>
            </div>

            @if($compoundTemplate->description)
                <div class="pt-3 border-t border-slate-100">
                    <span class="block text-xs font-semibold text-slate-400 uppercase mb-1">Deskripsi</span>
                    <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-lg">{{ $compoundTemplate->description }}</p>
                </div>
            @endif

            @if($compoundTemplate->preparation_instructions)
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase mb-1">Cara Peracikan (SOP)</span>
                    <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-lg">{{ $compoundTemplate->preparation_instructions }}</p>
                </div>
            @endif
        </div>

        <!-- Tabel Komposisi -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 font-bold text-slate-800">
                Komposisi Bahan per Bungkus/Unit
            </div>
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-6">Bahan Obat</th>
                        <th class="py-3 px-4 text-right">Kuantitas per Bungkus</th>
                        <th class="py-3 px-6">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($compoundTemplate->items as $item)
                        <tr>
                            <td class="py-3 px-6 font-medium text-slate-900">{{ $item->product?->name ?? 'Bahan' }}</td>
                            <td class="py-3 px-4 text-right font-bold text-slate-800">{{ number_format($item->quantity_per_unit, 3) }} {{ $item->unit?->name ?? 'unit' }}</td>
                            <td class="py-3 px-6 text-xs text-slate-500">{{ $item->notes ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
