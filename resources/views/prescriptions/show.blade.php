<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Header & Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">{{ $prescription->prescription_number }}</h1>
                    @php
                        $badge = match($prescription->status) {
                            'submitted' => ['bg' => 'bg-amber-100 text-amber-800 border-amber-200', 'label' => 'Menunggu Validasi Apoteker', 'icon' => 'fa-clock'],
                            'validated' => ['bg' => 'bg-blue-100 text-blue-800 border-blue-200', 'label' => 'Divalidasi Apoteker', 'icon' => 'fa-check-double'],
                            'dispensed' => ['bg' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'label' => 'Selesai Diserahkan', 'icon' => 'fa-circle-check'],
                            'rejected' => ['bg' => 'bg-red-100 text-red-800 border-red-200', 'label' => 'Resep Ditolak', 'icon' => 'fa-ban'],
                            'cancelled' => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => 'Dibatalkan', 'icon' => 'fa-circle-xmark'],
                            default => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => ucfirst($prescription->status), 'icon' => 'fa-circle-info']
                        };
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $badge['bg'] }}">
                        <i class="fa-solid {{ $badge['icon'] }}"></i>
                        {{ $badge['label'] }}
                    </span>
                </div>
                <nav class="flex text-xs text-slate-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('prescriptions.index') }}" class="hover:text-slate-900">Resep Obat</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">{{ $prescription->prescription_number }}</span>
                </nav>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('prescriptions.index') }}" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
                </a>

                <!-- Salin Resep (Copy Resep) -->
                <form method="POST" action="{{ route('prescriptions.copy', $prescription) }}" onsubmit="return confirm('Buat salinan resep (copy resep) baru untuk pasien ini?');">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 text-sm font-semibold text-slate-700 hover:text-slate-900 bg-white border border-slate-300 rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-copy text-indigo-600"></i> Buat Copy Resep
                    </button>
                </form>

                @if($prescription->status === 'validated')
                    <a href="{{ route('compounds.create', ['prescription_id' => $prescription->id]) }}" class="px-3.5 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-mortar-pestle"></i> Buat Racikan Obat
                    </a>

                    <form method="POST" action="{{ route('prescriptions.dispense', $prescription) }}" onsubmit="return confirm('Tandai resep telah diserahkan (dispensed) ke pasien?');">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-hand-holding-medical"></i> Selesai Diserahkan
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('warning'))
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-base"></i>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        <!-- Panel Validasi Apoteker (Jika berstatus submitted) -->
        @if($prescription->status === 'submitted')
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-user-check text-blue-600"></i>
                            Pemeriksaan & Validasi Apoteker (Skrining Resep)
                        </h3>
                        <p class="text-xs text-slate-600 mt-1">
                            Lakukan verifikasi kesesuaian administratif, farmasetik, dan klinis (dosis, interaksi, alergi) sebelum disiapkan.
                        </p>
                    </div>
                    <div class="flex items-center gap-2" x-data="{ showRejectModal: false }">
                        <!-- Tombol Setujui -->
                        <form method="POST" action="{{ route('prescriptions.validate', $prescription) }}">
                            @csrf
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-colors flex items-center gap-1.5">
                                <i class="fa-solid fa-check"></i> Validasi & Setujui Resep
                            </button>
                        </form>

                        <!-- Tombol Tolak Modal Trigger -->
                        <button type="button" @click="showRejectModal = true" class="px-4 py-2 text-sm font-semibold text-red-600 hover:text-red-700 bg-white border border-red-200 rounded-xl shadow-sm transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-xmark"></i> Tolak Resep
                        </button>

                        <!-- Modal Tolak -->
                        <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" style="display: none;">
                            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4" @click.away="showRejectModal = false">
                                <h4 class="text-base font-bold text-slate-900">Alasan Penolakan Resep</h4>
                                <form method="POST" action="{{ route('prescriptions.validate', $prescription) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <div class="mb-4">
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Alasan Penolakan <span class="text-red-500">*</span></label>
                                        <textarea name="notes" rows="3" required placeholder="Contoh: Dosis melebihi batas lazim, obat kontraindikasi..."
                                                  class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-1 focus:ring-red-500"></textarea>
                                    </div>
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-lg">Batal</button>
                                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg">Tolak Resep</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Card Informasi Utama Resep & Pasien -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i class="fa-solid fa-hospital-user text-blue-500"></i> Informasi Resep & Pasien
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase">Tanggal Resep</span>
                        <span class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($prescription->prescription_date)->translatedFormat('d F Y') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase">Pasien</span>
                        <span class="font-semibold text-slate-900">{{ $prescription->customer?->name ?? 'Pasien Umum' }}</span>
                        <span class="block text-xs text-slate-500">{{ $prescription->customer?->phone ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase">Dokter Penulis</span>
                        <span class="font-semibold text-slate-900">{{ $prescription->doctor?->name ?? 'Dokter Luar / Tidak tercatat' }}</span>
                        <span class="block text-xs text-slate-500">{{ $prescription->doctor?->sip_number ? 'SIP: '.$prescription->doctor->sip_number : '' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase">Diagnosis Medis</span>
                        <span class="font-medium text-slate-800">{{ $prescription->diagnosis ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase">Apoteker Pemeriksa</span>
                        <span class="font-medium text-slate-800">{{ $prescription->validator?->name ?? 'Belum diverifikasi' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase">Waktu Validasi</span>
                        <span class="font-medium text-slate-800">{{ $prescription->validated_at ? \Carbon\Carbon::parse($prescription->validated_at)->translatedFormat('d M Y H:i') : '-' }}</span>
                    </div>
                </div>

                @if($prescription->notes)
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <span class="block text-xs font-semibold text-slate-400 uppercase mb-1">Catatan Resep / Skrining</span>
                        <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-lg">{{ $prescription->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Foto Resep Fisik -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                        <i class="fa-solid fa-camera text-indigo-500"></i> Lembar Resep Fisik
                    </h2>
                    @if($prescription->image_url)
                        <div class="relative group rounded-xl overflow-hidden border border-slate-200 aspect-[4/3] bg-slate-100 flex items-center justify-center">
                            <img src="{{ Storage::url($prescription->image_url) }}" alt="Foto Resep" class="w-full h-full object-cover">
                            <a href="{{ Storage::url($prescription->image_url) }}" target="_blank" class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-semibold transition-opacity gap-1.5">
                                <i class="fa-solid fa-magnifying-glass-plus"></i> Lihat Ukuran Penuh
                            </a>
                        </div>
                    @else
                        <div class="p-6 bg-slate-50 border border-slate-200 rounded-xl text-center">
                            <i class="fa-regular fa-image text-3xl text-slate-300 mb-2"></i>
                            <div class="text-xs font-semibold text-slate-500">Tidak Ada Foto Resep</div>
                            <div class="text-xs text-slate-400 mt-0.5">Resep diinput langsung tanpa scan.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabel Item Obat Resep -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-tablets text-blue-500"></i>
                    <h3 class="font-bold text-slate-800">Daftar Obat Resep</h3>
                </div>
                <span class="text-xs font-semibold text-slate-500">Total {{ $prescription->items->count() }} item</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-6">Nama Obat</th>
                            <th class="py-3.5 px-4 text-center">Jumlah & Satuan</th>
                            <th class="py-3.5 px-4">Dosis</th>
                            <th class="py-3.5 px-4">Frekuensi Pakai</th>
                            <th class="py-3.5 px-6">Aturan & Petunjuk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($prescription->items as $item)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3.5 px-6">
                                    <div class="font-semibold text-slate-900">{{ $item->product?->name ?? 'Produk Dihapus' }}</div>
                                    <div class="text-xs text-slate-400">{{ $item->product?->category?->name ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                    {{ number_format($item->quantity, 0, ',', '.') }} {{ $item->unit?->name ?? 'Unit' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    {{ $item->dosage ?: '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    {{ $item->frequency ?: '-' }}
                                </td>
                                <td class="py-3.5 px-6 text-slate-700">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-xs font-medium">
                                        {{ $item->instructions ?: 'Sesuai petunjuk dokter' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section Terkait: Racikan Obat Terdaftar -->
        @if($prescription->compounds->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-mortar-pestle text-indigo-500"></i>
                        <h3 class="font-bold text-slate-800">Obat Racikan dari Resep Ini</h3>
                    </div>
                    <span class="text-xs font-semibold text-slate-500">{{ $prescription->compounds->count() }} racikan</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($prescription->compounds as $compound)
                        <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/50">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-indigo-600">{{ $compound->compound_number }}</span>
                                    <h4 class="font-bold text-slate-900">{{ $compound->name }}</h4>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                                        {{ $compound->total_packages }} {{ $compound->package_type }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 mt-1">
                                    Bahan: {{ $compound->items->pluck('product.name')->join(', ') }}
                                </div>
                                <div class="text-xs text-slate-600 mt-0.5">
                                    Aturan: {{ $compound->signa ?: '-' }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('compounds.label', $compound) }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition flex items-center gap-1">
                                    <i class="fa-solid fa-print"></i> Cetak Etiket
                                </a>
                                <a href="{{ route('compounds.show', $compound) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1">
                                    <i class="fa-solid fa-eye"></i> Detail Racikan
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
