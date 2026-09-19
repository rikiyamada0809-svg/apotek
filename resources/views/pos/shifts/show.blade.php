<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900">Rekap Shift Kasir: #{{ $shift->id }}</h1>
                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $shift->status === 'open' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                    {{ $shift->status === 'open' ? 'Shift Aktif' : 'Shift Selesai (Closed)' }}
                </span>
            </div>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('pos.index') }}" class="hover:text-slate-900">POS Kasir</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Rekap Shift</span>
            </nav>
        </div>

        @if($shift->status === 'open')
            <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-cash-register"></i> Kembali ke Kasir POS
            </a>
        @endif
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <!-- Kartu Metrik Keuangan Shift -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Modal Kas Awal</div>
            <div class="text-lg font-bold text-slate-900 mt-1">{{ format_rupiah($shift->opening_cash) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Saat shift dibuka</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Penjualan Tunai</div>
            <div class="text-lg font-bold text-emerald-600 mt-1">+{{ format_rupiah($shift->total_sales_cash) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Uang kas masuk</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Penjualan Non-Tunai</div>
            <div class="text-lg font-bold text-blue-600 mt-1">{{ format_rupiah($shift->total_sales_non_cash) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">QRIS / Debit / Transfer</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Pengeluaran Kas</div>
            <div class="text-lg font-bold text-rose-600 mt-1">-{{ format_rupiah($shift->total_expenses) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Petty cash shift</div>
        </div>

        <div class="bg-slate-900 rounded-2xl p-4 shadow-sm text-white">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kas Sistem (Laci)</div>
            <div class="text-lg font-extrabold text-emerald-400 mt-1">{{ format_rupiah($shift->closing_cash_system) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Kas Awal + Tunai - Pengeluaran</div>
        </div>
    </div>

    @if($shift->status === 'closed')
        <div class="mb-6 p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="text-xs text-slate-500">Hasil Perhitungan Fisik Tutup Shift:</div>
                <div class="text-xl font-bold text-slate-900 mt-0.5">
                    Kas Fisik Aktual: <span class="text-emerald-600">{{ format_rupiah($shift->closing_cash_actual) }}</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold text-slate-500">Selisih Kas:</span>
                <span class="px-3.5 py-1.5 rounded-xl font-extrabold text-sm {{ $shift->cash_difference >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    {{ ($shift->cash_difference > 0 ? '+' : '').format_rupiah($shift->cash_difference) }}
                </span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Panel Tutup Shift (Jika Masih Buka) -->
        @if($shift->status === 'open')
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-lock text-rose-600"></i> Tutup Shift Kasir
                </h2>

                <form method="POST" action="{{ route('shifts.close', $shift) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kas Fisik di Laci Aktual (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="any" min="0" name="closing_cash_actual" required
                               placeholder="Hitung uang fisik di laci kasir..."
                               class="w-full px-3 py-2 text-sm font-bold text-slate-900 border border-slate-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <p class="text-[11px] text-slate-400 mt-1">Harus dihitung secara manual oleh kasir.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Penutupan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan selisih kas / kondisi akhir..."
                                  class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-emerald-500"></textarea>
                    </div>

                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menutup shift ini? Sesi kasir akan berakhir.')"
                            class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-door-closed"></i> Tutup Shift Sekarang
                    </button>
                </form>
            </div>

            <!-- Panel Catat Petty Cash -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-amber-600"></i> Catat Pengeluaran Kas (Petty Cash)
                </h2>

                <form method="POST" action="{{ route('shifts.expense', $shift) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal Pengeluaran (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="any" min="1" name="amount" required placeholder="Contoh: 15000"
                               class="w-full px-3 py-2 text-sm font-bold text-slate-900 border border-slate-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan Pengeluaran <span class="text-rose-500">*</span></label>
                        <input type="text" name="description" required placeholder="Misal: Beli plastik klip obat, sapu, dll..."
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus"></i> Catat Pengeluaran Kas
                    </button>
                </form>
            </div>
        @endif

        <!-- Info Petugas & Waktu -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-3 text-xs">
            <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-user-clock text-emerald-600"></i> Info Petugas & Waktu
            </h2>
            <div class="flex justify-between">
                <span class="text-slate-500">Nama Kasir:</span>
                <span class="font-bold text-slate-800">{{ $shift->user->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Gudang / Outlet:</span>
                <span class="font-semibold text-slate-800">{{ $shift->warehouse->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Waktu Mulai:</span>
                <span class="font-medium text-slate-700">{{ $shift->start_time->format('d M Y H:i:s') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Waktu Selesai:</span>
                <span class="font-medium text-slate-700">{{ $shift->end_time ? $shift->end_time->format('d M Y H:i:s') : 'Masih berjalan' }}</span>
            </div>
            @if($shift->notes)
                <div class="pt-2 border-t border-slate-100 text-slate-500">
                    <strong>Catatan:</strong> {{ $shift->notes }}
                </div>
            @endif
        </div>
    </div>

    <!-- Riwayat Petty Cash Shift Ini -->
    @if($shift->cashTransactions->count() > 0)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="p-4 border-b border-slate-100 font-semibold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-money-bill-transfer text-amber-600"></i> Riwayat Pengeluaran Kas (Petty Cash) Selama Shift
            </div>
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-2.5 text-left">Waktu</th>
                        <th class="px-4 py-2.5 text-left">Keterangan</th>
                        <th class="px-4 py-2.5 text-right">Jumlah</th>
                        <th class="px-4 py-2.5 text-left">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($shift->cashTransactions as $tx)
                        <tr>
                            <td class="px-4 py-2.5 text-slate-500">{{ $tx->created_at->format('H:i') }}</td>
                            <td class="px-4 py-2.5 font-medium text-slate-800">{{ $tx->description }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-rose-600">-{{ format_rupiah($tx->amount) }}</td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $tx->creator->name ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-app-layout>
