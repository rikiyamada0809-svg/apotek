<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Detail Supplier</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('suppliers.index') }}" class="hover:text-slate-900">Supplier</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">{{ $supplier->name }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('suppliers.edit', $supplier) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-pen-to-square"></i> Edit
            </a>
            <a href="{{ route('suppliers.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold border border-emerald-100">
                    <i class="fa-solid fa-truck-field"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $supplier->name }}</h2>
                    <p class="font-mono text-xs font-semibold text-slate-500">{{ $supplier->code }}</p>
                    <div class="mt-1">
                        <x-status-badge :active="$supplier->is_active" />
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-4 space-y-3 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block">Nama Kontak Person</span>
                    <span class="font-semibold text-slate-700">{{ $supplier->contact_person ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Nomor Telepon / HP</span>
                    <span class="font-semibold text-slate-700">{{ $supplier->phone ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Email</span>
                    <span class="font-semibold text-slate-700">{{ $supplier->email ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Kota</span>
                    <span class="font-semibold text-slate-700">{{ $supplier->city ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Alamat Lengkap</span>
                    <span class="font-medium text-slate-700">{{ $supplier->address ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Keuangan & Perbankan -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Informasi Pembayaran & Rekening Bank</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-xs text-slate-400 block mb-1">Term of Payment (Tempo)</span>
                        <span class="text-xl font-bold text-slate-800">{{ $supplier->payment_terms ?? 0 }} <span class="text-sm font-normal text-slate-500">Hari</span></span>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-xs text-slate-400 block mb-1">NPWP / Tax ID</span>
                        <span class="text-sm font-mono font-bold text-slate-800">{{ $supplier->tax_id ?? '-' }}</span>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 sm:col-span-2">
                        <span class="text-xs text-slate-400 block mb-1">Rekening Bank</span>
                        <div class="flex items-center gap-3 mt-1">
                            <i class="fa-solid fa-building-columns text-emerald-600 text-lg"></i>
                            <div>
                                <div class="font-bold text-slate-800">{{ $supplier->bank_name ?? 'Belum ada bank' }} - <span class="font-mono text-emerald-700">{{ $supplier->bank_account_number ?? '-' }}</span></div>
                                <div class="text-xs text-slate-500">a.n {{ $supplier->bank_account_name ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($supplier->notes)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 mb-3">Catatan Tambahan</h3>
                    <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ $supplier->notes }}</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
