<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Edit Pengguna: {{ $user->name }}</h1>
        <nav class="flex text-xs text-slate-500 mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('users.index') }}" class="hover:text-slate-900">Pengguna</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 font-medium">Edit</span>
        </nav>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="role" class="block text-sm font-semibold text-slate-700 mb-1">Peran / Role <span class="text-red-500">*</span></label>
                    <select name="role" id="role" required
                            class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">— Pilih Role —</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" @selected(old('role', $user->roles->first()?->name) == $r->name)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <p class="text-xs text-slate-500 mb-3">Kosongkan kolom password di bawah jika tidak ingin mengubah password.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Password Baru</label>
                            <input type="password" name="password" id="password"
                                   class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="w-full px-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $user->is_active))
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <label for="is_active" class="text-sm text-slate-700">Akun Aktif</label>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                        <i class="fa-solid fa-check mr-1"></i> Perbarui User
                    </button>
                    <a href="{{ route('users.index') }}" class="px-6 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium text-slate-700 rounded-lg transition-colors">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
