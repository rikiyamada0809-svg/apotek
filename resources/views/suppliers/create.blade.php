<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Tambah Supplier Baru</h1>
        <nav class="flex text-xs text-slate-500 mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('suppliers.index') }}" class="hover:text-slate-900">Supplier</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 font-medium">Tambah</span>
        </nav>
    </div>

    <div class="max-w-4xl">
        <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-6">
            @csrf

            <!-- Informasi Umum -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Umum</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="code" class="block text-sm font-semibold text-slate-700 mb-1">Kode Supplier <span class="text-red-500">*</span></label>
                        <input type="text" name="code" id="code" value="{{ old('code') }}" placeholder="Contoh: SUP-001" required
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Supplier / PBF <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Contoh: PT Enseval Putera Megatrading" required
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="contact_person" class="block text-sm font-semibold text-slate-700 mb-1">Nama Kontak Person (PIC)</label>
                        <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person') }}" placeholder="Contoh: Budi Santoso"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('contact_person') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1">Nomor Telepon / HP</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="Contoh: 08123456789"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="Contoh: order@enseval.com"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-semibold text-slate-700 mb-1">Kota</label>
                        <input type="text" name="city" id="city" value="{{ old('city') }}" placeholder="Contoh: Jakarta Selatan"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-sm font-semibold text-slate-700 mb-1">Alamat Lengkap</label>
                    <textarea name="address" id="address" rows="2" placeholder="Jl. Raya Gudang..."
                              class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">{{ old('address') }}</textarea>
                    @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Pembayaran & Rekening Bank -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Pembayaran & Perbankan</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="payment_terms" class="block text-sm font-semibold text-slate-700 mb-1">Term of Payment (Tempo Hari)</label>
                        <div class="relative">
                            <input type="number" name="payment_terms" id="payment_terms" value="{{ old('payment_terms', 30) }}" min="0"
                                   class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                            <span class="absolute right-4 top-2 text-sm text-slate-400">Hari</span>
                        </div>
                        @error('payment_terms') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="tax_id" class="block text-sm font-semibold text-slate-700 mb-1">NPWP / Tax ID</label>
                        <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id') }}" placeholder="Contoh: 01.234.567.8-901.000"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('tax_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="bank_name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Bank</label>
                        <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}" placeholder="Contoh: BCA / Mandiri / BNI"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('bank_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="bank_account_number" class="block text-sm font-semibold text-slate-700 mb-1">Nomor Rekening</label>
                        <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="Contoh: 1234567890"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('bank_account_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="bank_account_name" class="block text-sm font-semibold text-slate-700 mb-1">Atas Nama Rekening</label>
                        <input type="text" name="bank_account_name" id="bank_account_name" value="{{ old('bank_account_name') }}" placeholder="Contoh: PT Enseval Putera Megatrading"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('bank_account_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" id="notes" rows="2" placeholder="Catatan syarat pemesanan, diskon rutin, dll..."
                              class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">{{ old('notes') }}</textarea>
                    @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <label for="is_active" class="text-sm font-medium text-slate-700">Supplier Aktif</label>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-check mr-1"></i> Simpan Supplier
                </button>
                <a href="{{ route('suppliers.index') }}" class="px-6 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium text-slate-700 rounded-lg transition-colors">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
