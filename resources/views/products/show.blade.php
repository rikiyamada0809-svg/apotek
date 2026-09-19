<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Detail Produk / Obat</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('products.index') }}" class="hover:text-slate-900">Obat & Produk</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">{{ $product->name }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-pen-to-square"></i> Edit Produk
            </a>
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold border border-emerald-100 shrink-0">
                    <i class="fa-solid fa-pills"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900 leading-tight">{{ $product->name }}</h2>
                    @if($product->generic_name)
                        <p class="text-xs text-slate-500 italic mt-0.5">{{ $product->generic_name }}</p>
                    @endif
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-700">{{ $product->product_code }}</span>
                        <x-status-badge :active="$product->is_active" />
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-4 space-y-3 text-sm">
                @if($product->barcode)
                    <div class="flex justify-between">
                        <span class="text-slate-400">Barcode / SKU</span>
                        <span class="font-mono font-semibold text-slate-700">{{ $product->barcode }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-slate-400">Kategori</span>
                    <span class="font-semibold text-slate-800">{{ $product->category->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Golongan Obat</span>
                    <span class="font-semibold text-slate-800">{{ $product->drug_class ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Bentuk Sediaan</span>
                    <span class="font-semibold text-slate-800">{{ $product->form ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Kekuatan Dosis</span>
                    <span class="font-semibold text-slate-800">{{ $product->strength ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Pabrik / Produsen</span>
                    <span class="font-semibold text-slate-800">{{ $product->manufacturer->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Kondisi Simpan</span>
                    <span class="font-semibold text-slate-800">{{ $product->storage_condition ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Wajib Resep Dokter</span>
                    <span class="font-semibold {{ $product->requires_prescription ? 'text-amber-600' : 'text-slate-700' }}">
                        {{ $product->requires_prescription ? 'Ya (R/)' : 'Tidak (Obat Bebas)' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Min. Order Qty</span>
                    <span class="font-semibold text-slate-800">{{ $product->min_order_qty ?? 1 }}</span>
                </div>
            </div>

            @if($product->description)
                <div class="border-t border-slate-100 pt-4">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-1">Indikasi / Deskripsi</span>
                    <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ $product->description }}</p>
                </div>
            @endif
        </div>

        <!-- Multi-Unit & Pricing Card -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-tags text-emerald-600"></i> Satuan & Harga Bertingkat
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-xs">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">Satuan</th>
                                <th class="px-3 py-2 text-center font-semibold">Tipe</th>
                                <th class="px-3 py-2 text-right font-semibold">Konversi</th>
                                <th class="px-3 py-2 text-right font-semibold">Harga Beli</th>
                                <th class="px-3 py-2 text-right font-semibold text-emerald-700">Harga Jual</th>
                                <th class="px-3 py-2 text-right font-semibold">Harga Grosir</th>
                                <th class="px-3 py-2 text-right font-semibold">Harga Resep</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($product->productUnits as $pUnit)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-3 py-3 font-semibold text-slate-800">
                                        {{ $pUnit->unit->name ?? 'N/A' }} 
                                        <span class="text-xs text-slate-400 font-normal">({{ $pUnit->unit->abbreviation ?? '' }})</span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        @if($pUnit->is_base_unit)
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Satuan Dasar
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-500">Turunan</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right font-mono text-slate-700">
                                        {{ number_format($pUnit->conversion_factor, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3 text-right font-mono text-slate-600">
                                        Rp {{ number_format($pUnit->purchase_price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3 text-right font-mono font-bold text-emerald-700">
                                        Rp {{ number_format($pUnit->selling_price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3 text-right font-mono text-slate-600">
                                        {{ $pUnit->wholesale_price > 0 ? 'Rp ' . number_format($pUnit->wholesale_price, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-3 py-3 text-right font-mono text-slate-600">
                                        {{ $pUnit->prescription_price > 0 ? 'Rp ' . number_format($pUnit->prescription_price, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-6 text-center text-slate-400">
                                        Belum ada data satuan dan harga untuk produk ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
