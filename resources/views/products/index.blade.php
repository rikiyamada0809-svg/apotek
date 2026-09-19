<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Master Obat & Produk</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Obat & Produk</span>
            </nav>
        </div>
        <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Tambah Produk
        </a>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-4 border-b border-slate-100 bg-slate-50/50">
            <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="relative lg:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama obat, kode, barcode, atau nama generik..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-sm"></i>
                </div>

                <select name="category_id" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>

                <select name="drug_class" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Golongan</option>
                    <option value="Bebas" @selected(request('drug_class') == 'Bebas')>Obat Bebas</option>
                    <option value="Bebas Terbatas" @selected(request('drug_class') == 'Bebas Terbatas')>Obat Bebas Terbatas</option>
                    <option value="Keras" @selected(request('drug_class') == 'Keras')>Obat Keras (G)</option>
                    <option value="Narkotika" @selected(request('drug_class') == 'Narkotika')>Narkotika</option>
                    <option value="Psikotropika" @selected(request('drug_class') == 'Psikotropika')>Psikotropika</option>
                    <option value="Herbal" @selected(request('drug_class') == 'Herbal')>Herbal / Jamu</option>
                    <option value="Alkes" @selected(request('drug_class') == 'Alkes')>Alat Kesehatan</option>
                </select>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg transition-colors">
                        Filter
                    </button>
                    @if(request()->hasAny(['search','category_id','drug_class','manufacturer_id','status']))
                        <a href="{{ route('products.index') }}" class="px-3 py-2 text-sm text-slate-500 hover:text-slate-700">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">#</th>
                        <th class="px-4 py-3 text-left font-semibold">Kode & Barcode</th>
                        <th class="px-4 py-3 text-left font-semibold">Nama Obat / Produk</th>
                        <th class="px-4 py-3 text-left font-semibold">Kategori & Golongan</th>
                        <th class="px-4 py-3 text-left font-semibold">Pabrik / Produsen</th>
                        <th class="px-4 py-3 text-center font-semibold">Resep</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-slate-500">{{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</td>
                            <td class="px-4 py-3">
                                <div class="font-mono text-xs font-semibold text-slate-700">{{ $product->product_code }}</div>
                                @if($product->barcode)
                                    <div class="text-[11px] text-slate-400 font-mono"><i class="fa-solid fa-barcode mr-1"></i>{{ $product->barcode }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('products.show', $product) }}" class="font-semibold text-slate-900 hover:text-emerald-600 transition-colors block">
                                    {{ $product->name }}
                                </a>
                                @if($product->generic_name)
                                    <span class="text-xs text-slate-500 italic">{{ $product->generic_name }}</span>
                                @endif
                                @if($product->strength)
                                    <span class="inline-block text-[11px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 ml-1">{{ $product->strength }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs text-slate-700 font-medium">{{ $product->category->name ?? '-' }}</div>
                                @if($product->drug_class)
                                    @php
                                        $badgeColor = match($product->drug_class) {
                                            'Bebas' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'Bebas Terbatas' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'Keras' => 'bg-red-50 text-red-700 border-red-200',
                                            'Narkotika', 'Psikotropika' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            default => 'bg-slate-50 text-slate-700 border-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 mt-0.5 rounded text-[11px] font-semibold border {{ $badgeColor }}">
                                        {{ $product->drug_class }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 text-xs">
                                {{ $product->manufacturer->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($product->requires_prescription)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200" title="Wajib Resep Dokter">
                                        <i class="fa-solid fa-receipt text-[10px]"></i> R/
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <x-status-badge :active="$product->is_active" />
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('products.show', $product) }}" class="text-slate-500 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition-colors" title="Lihat Detail">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('products.edit', $product) }}" class="text-blue-500 hover:text-blue-700 p-1.5 rounded-lg hover:bg-blue-50 transition-colors" title="Edit">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </a>
                                    <x-delete-button :action="route('products.destroy', $product)" confirmText="Apakah Anda yakin ingin menghapus produk ini?" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-pills text-3xl mb-2"></i>
                                <p>Belum ada data obat atau produk.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
