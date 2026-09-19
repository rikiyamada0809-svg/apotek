<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Edit Dokter</h1>
        <nav class="flex text-xs text-slate-500 mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('doctors.index') }}" class="hover:text-slate-900">Dokter</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 font-medium">Edit: {{ $doctor->name }}</span>
        </nav>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <form method="POST" action="{{ route('doctors.update', $doctor) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Dokter & Gelar <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $doctor->name) }}" required
                           class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="sip_number" class="block text-sm font-semibold text-slate-700 mb-1">Nomor SIP (Surat Izin Praktik)</label>
                        <input type="text" name="sip_number" id="sip_number" value="{{ old('sip_number', $doctor->sip_number) }}"
                               class="w-full px-4 py-2 text-sm font-mono border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('sip_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="specialization" class="block text-sm font-semibold text-slate-700 mb-1">Spesialisasi</label>
                        <input type="text" name="specialization" id="specialization" value="{{ old('specialization', $doctor->specialization) }}"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('specialization') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1">Nomor Telepon / HP</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $doctor->phone) }}"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="clinic_name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Klinik / Rumah Sakit</label>
                        <input type="text" name="clinic_name" id="clinic_name" value="{{ old('clinic_name', $doctor->clinic_name) }}"
                               class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('clinic_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="clinic_address" class="block text-sm font-semibold text-slate-700 mb-1">Alamat Tempat Praktik / Klinik</label>
                    <textarea name="clinic_address" id="clinic_address" rows="2"
                              class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">{{ old('clinic_address', $doctor->clinic_address) }}</textarea>
                    @error('clinic_address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $doctor->is_active))
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <label for="is_active" class="text-sm font-medium text-slate-700">Dokter Aktif</label>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                        <i class="fa-solid fa-check mr-1"></i> Perbarui Dokter
                    </button>
                    <a href="{{ route('doctors.index') }}" class="px-6 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium text-slate-700 rounded-lg transition-colors">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
