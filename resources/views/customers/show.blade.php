<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Detail Pelanggan</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('customers.index') }}" class="hover:text-slate-900">Pelanggan</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">{{ $customer->name }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-pen-to-square"></i> Edit Pelanggan
            </a>
            <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold border border-emerald-100">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $customer->name }}</h2>
                    <p class="font-mono text-xs font-semibold text-slate-500">{{ $customer->code }}</p>
                    <div class="mt-1">
                        <x-status-badge :active="$customer->is_active" />
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-4 space-y-3 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block">Tipe Pelanggan</span>
                    <span class="font-semibold text-slate-700 capitalize">{{ $customer->customer_type }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Nomor Telepon / WhatsApp</span>
                    <span class="font-semibold text-slate-700">{{ $customer->phone ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Email</span>
                    <span class="font-semibold text-slate-700">{{ $customer->email ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Tanggal Lahir & Jenis Kelamin</span>
                    <span class="font-semibold text-slate-700">
                        {{ $customer->date_of_birth ? $customer->date_of_birth->format('d/m/Y') : '-' }} 
                        @if($customer->gender) ({{ $customer->gender }}) @endif
                    </span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Alamat</span>
                    <span class="font-medium text-slate-700">{{ $customer->address ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Keuangan, Member & Alergi -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Member & Diskon -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Informasi Keanggotaan & Keuangan</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-xs text-slate-400 block mb-1">Diskon Khusus</span>
                        <span class="text-2xl font-bold text-emerald-600">{{ number_format($customer->discount_percentage, 1) }}%</span>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-xs text-slate-400 block mb-1">Limit Piutang (Kredit)</span>
                        <span class="text-xl font-bold text-slate-800">Rp {{ number_format($customer->credit_limit, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-xs text-slate-400 block mb-1">Sisa Saldo Piutang (Outstanding)</span>
                        <span class="text-xl font-bold text-amber-600">Rp {{ number_format($customer->outstanding_balance, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-xs text-slate-400 block mb-1">Nomor & Validitas Member</span>
                        <span class="text-sm font-mono font-bold text-slate-800">{{ $customer->membership_number ?? 'Bukan Member' }}</span>
                        @if($customer->membership_valid_until)
                            <div class="text-xs text-slate-500 mt-0.5">Berlaku s/d {{ $customer->membership_valid_until->format('d M Y') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Alergi -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-red-500"></i> Riwayat Alergi Obat / Makanan
                </h3>
                @if($customer->allergy_notes)
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800 leading-relaxed font-medium">
                        {{ $customer->allergy_notes }}
                    </div>
                @else
                    <p class="text-sm text-slate-400 italic">Tidak ada catatan alergi untuk pasien/pelanggan ini.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
