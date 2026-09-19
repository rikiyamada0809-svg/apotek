<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Tambah Pelanggan Baru</h1>
        <nav class="flex text-xs text-slate-500 mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('customers.index') }}" class="hover:text-slate-900">Pelanggan</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 font-medium">Tambah</span>
        </nav>
    </div>

    <div class="max-w-4xl">
        <form method="POST" action="{{ route('customers.store') }}" 
              x-data="{ customerType: '{{ old('customer_type', 'regular') }}' }" class="space-y-6">
            @csrf

            <!-- Informasi Pribadi -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Pribadi & Kontak</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="code" class="block text-sm font-semibold text-slate-700 mb-1">Kode Pelanggan <span class="text-red-500">*</span></label>
                        <input type="text" name="code" id="code" value="{{ old('code', 'CST-' . strtoupper(Str::random(5))) }}" required
                               class="w-full px-4 py-2 text-sm font-mono border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Contoh: Ahmad Dahlan" required
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="Contoh: 08123456789"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="Contoh: ahmad@gmail.com"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="date_of_birth" class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Lahir</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('date_of_birth') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="gender" class="block text-sm font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
                        <select name="gender" id="gender"
                                class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">— Pilih Jenis Kelamin —</option>
                            <option value="Laki-laki" @selected(old('gender') == 'Laki-laki')>Laki-laki</option>
                            <option value="Perempuan" @selected(old('gender') == 'Perempuan')>Perempuan</option>
                        </select>
                        @error('gender') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-sm font-semibold text-slate-700 mb-1">Alamat Tempat Tinggal</label>
                    <textarea name="address" id="address" rows="2" placeholder="Alamat lengkap..."
                              class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">{{ old('address') }}</textarea>
                    @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Tipe Keanggotaan & Keuangan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Keanggotaan & Keuangan</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="customer_type" class="block text-sm font-semibold text-slate-700 mb-1">Tipe Pelanggan <span class="text-red-500">*</span></label>
                        <select name="customer_type" id="customer_type" x-model="customerType" required
                                class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="regular">Regular (Umum)</option>
                            <option value="member">Member (Anggota Apotek)</option>
                            <option value="reseller">Reseller / Mitra B2B</option>
                        </select>
                        @error('customer_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="discount_percentage" class="block text-sm font-semibold text-slate-700 mb-1">Diskon Khusus (%)</label>
                        <input type="number" step="0.1" min="0" max="100" name="discount_percentage" id="discount_percentage" value="{{ old('discount_percentage', 0) }}"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('discount_percentage') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Member Specific Fields -->
                    <div x-show="customerType === 'member'" x-transition>
                        <label for="membership_number" class="block text-sm font-semibold text-slate-700 mb-1">Nomor Kartu Member</label>
                        <input type="text" name="membership_number" id="membership_number" value="{{ old('membership_number') }}" placeholder="Contoh: MBR-2024-001"
                               class="w-full px-4 py-2 text-sm font-mono border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('membership_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="customerType === 'member'" x-transition>
                        <label for="membership_valid_until" class="block text-sm font-semibold text-slate-700 mb-1">Masa Berlaku Member</label>
                        <input type="date" name="membership_valid_until" id="membership_valid_until" value="{{ old('membership_valid_until') }}"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('membership_valid_until') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="credit_limit" class="block text-sm font-semibold text-slate-700 mb-1">Limit Piutang / Kredit (Rp)</label>
                        <input type="number" step="1000" min="0" name="credit_limit" id="credit_limit" value="{{ old('credit_limit', 0) }}"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('credit_limit') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="allergy_notes" class="block text-sm font-semibold text-slate-700 mb-1">Riwayat Alergi Obat / Makanan</label>
                    <textarea name="allergy_notes" id="allergy_notes" rows="2" placeholder="Penting: catat jika ada alergi obat seperti Amoxicillin, Paracetamol, dll..."
                              class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">{{ old('allergy_notes') }}</textarea>
                    @error('allergy_notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <label for="is_active" class="text-sm font-medium text-slate-700">Pelanggan Aktif</label>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-check mr-1"></i> Simpan Pelanggan
                </button>
                <a href="{{ route('customers.index') }}" class="px-6 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium text-slate-700 rounded-lg transition-colors">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
