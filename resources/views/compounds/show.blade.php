<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">{{ $compound->compound_number }}</h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 border border-indigo-200">
                        <i class="fa-solid fa-mortar-pestle"></i>
                        {{ $compound->total_packages }} Kemasan
                    </span>
                </div>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('prescriptions.index') }}" class="hover:text-slate-900">Resep & Racikan</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">{{ $compound->compound_number }}</span>
                </nav>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
                @if($compound->prescription)
                    <a href="{{ route('prescriptions.show', $compound->prescription) }}" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg transition-colors">
                        <i class="fa-solid fa-file-prescription mr-1"></i> Ke Resep
                    </a>
                @else
                    <a href="{{ route('prescriptions.index') }}" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg transition-colors">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
                    </a>
                @endif

                <a href="{{ route('compounds.label', $compound) }}" target="_blank" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-print"></i> Cetak Etiket Obat
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Card Informasi Racikan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="fa-solid fa-circle-info text-indigo-500"></i> Detail Obat Racikan
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 text-sm">
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Nama Racikan</span>
                    <span class="font-bold text-slate-900 text-base">{{ $compound->name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Jumlah Kemasan</span>
                    <span class="font-bold text-slate-800">{{ $compound->total_packages }} bungkus / wadah</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Template Digunakan</span>
                    <span class="font-medium text-slate-700">{{ $compound->template?->name ?? 'Racikan Khusus (Tanpa Template)' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Pasien</span>
                    <span class="font-semibold text-slate-900">{{ $compound->prescription?->customer?->name ?? 'Pasien Umum' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Resep Asal</span>
                    <span class="font-mono text-blue-600 font-semibold">{{ $compound->prescription?->prescription_number ?? 'Tanpa Resep Dokter' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Peracik (Apotek)</span>
                    <span class="font-medium text-slate-800">{{ $compound->preparer?->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Tabel Rincian Bahan yang Digunakan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-boxes-packing text-indigo-500"></i>
                    <h3 class="font-bold text-slate-800">Komposisi Bahan & Batch Terpilih (FEFO)</h3>
                </div>
                <span class="text-xs font-semibold text-slate-500">{{ $compound->items->count() }} Bahan Obat</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-6">Bahan Obat</th>
                            <th class="py-3.5 px-4 text-center">Batch Terpakai (FEFO)</th>
                            <th class="py-3.5 px-4 text-center">Tgl Expired Batch</th>
                            <th class="py-3.5 px-4 text-right">Jumlah Terpakai</th>
                            <th class="py-3.5 px-4 text-right">Harga Satuan</th>
                            <th class="py-3.5 px-6 text-right">Subtotal Bahan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($compound->items as $item)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3.5 px-6 font-semibold text-slate-900">
                                    {{ $item->product?->name ?? 'Obat' }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono text-xs">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold">
                                        {{ $item->batch?->batch_number ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center text-xs text-slate-600">
                                    {{ $item->batch?->expiry_date ? \Carbon\Carbon::parse($item->batch->expiry_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-slate-800">
                                    {{ number_format($item->quantity_used, 2) }} {{ $item->unit?->name ?? 'unit' }}
                                </td>
                                <td class="py-3.5 px-4 text-right text-xs text-slate-600">
                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-6 text-right font-bold text-slate-900">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700">
                        <tr>
                            <td colspan="5" class="py-3 px-6 text-right">Total Biaya Bahan Baku:</td>
                            <td class="py-3 px-6 text-right font-bold text-slate-900">
                                Rp {{ number_format($compound->total_material_cost, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" class="py-2.5 px-6 text-right">Jasa Peracikan Apotek:</td>
                            <td class="py-2.5 px-6 text-right font-bold text-indigo-600">
                                Rp {{ number_format($compound->service_fee, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr class="bg-indigo-50/50 text-indigo-950 font-bold border-t border-indigo-100">
                            <td colspan="5" class="py-3.5 px-6 text-right text-sm">TOTAL HARGA JUAL RACIKAN:</td>
                            <td class="py-3.5 px-6 text-right text-base text-indigo-700">
                                Rp {{ number_format($compound->total_price, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
