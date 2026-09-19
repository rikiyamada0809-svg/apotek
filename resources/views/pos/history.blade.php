<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Riwayat Penjualan Kasir</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('pos.index') }}" class="hover:text-slate-900">POS Kasir</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Riwayat Transaksi</span>
            </nav>
        </div>

        <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-cash-register"></i> Ke Layar Kasir
        </a>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ voidModalOpen: false, voidSaleId: null, voidInvoice: '' }">
        <div class="p-4 border-b border-slate-100">
            <form method="GET" action="{{ route('pos.history') }}" class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Invoice atau nama pelanggan..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-sm"></i>
                </div>

                <select name="status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="completed" @selected(request('status') === 'completed')>Selesai (Lunas)</option>
                    <option value="pending" @selected(request('status') === 'pending')>Tertunda (Hold)</option>
                    <option value="voided" @selected(request('status') === 'voided')>Dibatalkan (Void)</option>
                </select>

                <input type="date" name="date" value="{{ request('date', now()->toDateString()) }}"
                       class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">

                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg transition-colors">Filter</button>
                @if(request()->hasAny(['search','status','date']))
                    <a href="{{ route('pos.history') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">No. Invoice</th>
                        <th class="px-4 py-3 text-left font-semibold">Waktu</th>
                        <th class="px-4 py-3 text-left font-semibold">Kasir</th>
                        <th class="px-4 py-3 text-left font-semibold">Pelanggan</th>
                        <th class="px-4 py-3 text-left font-semibold">Item Obat</th>
                        <th class="px-4 py-3 text-right font-semibold">Grand Total</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-slate-50/50 {{ $sale->status === 'voided' ? 'bg-rose-50/30' : '' }}">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                <a href="{{ route('pos.receipt', $sale) }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 hover:underline">
                                    {{ $sale->invoice_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $sale->sale_date->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-slate-700 font-medium">{{ $sale->cashier->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $sale->customer->name ?? 'Umum' }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                <div class="max-w-xs truncate" title="{{ $sale->items->pluck('product.name')->join(', ') }}">
                                    {{ $sale->items->pluck('product.name')->join(', ') }}
                                </div>
                                <div class="text-[10px] text-slate-400">{{ $sale->items->count() }} macam item</div>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900">{{ format_rupiah($sale->grand_total) }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusBadge = [
                                        'completed' => 'bg-emerald-100 text-emerald-800',
                                        'pending' => 'bg-amber-100 text-amber-800',
                                        'voided' => 'bg-rose-100 text-rose-800 line-through',
                                    ];
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $statusBadge[$sale->status] ?? 'bg-slate-100' }}">
                                    {{ ucfirst($sale->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Cetak Struk -->
                                    <a href="{{ route('pos.receipt', $sale) }}" target="_blank" title="Cetak Struk"
                                       class="p-1.5 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-slate-100">
                                        <i class="fa-solid fa-print text-xs"></i>
                                    </a>

                                    <!-- PDF -->
                                    <a href="{{ route('pos.receipt.pdf', $sale) }}" target="_blank" title="Download PDF"
                                       class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100">
                                        <i class="fa-solid fa-file-pdf text-xs"></i>
                                    </a>

                                    <!-- Void button (if completed) -->
                                    @if($sale->status === 'completed')
                                        <button type="button" @click="voidSaleId = {{ $sale->id }}; voidInvoice = '{{ $sale->invoice_number }}'; voidModalOpen = true;"
                                                title="Batalkan (Void)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100">
                                            <i class="fa-solid fa-ban text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-receipt text-3xl mb-2"></i>
                                <p>Tidak ada riwayat transaksi penjualan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $sales->links() }}
            </div>
        @endif

        <!-- VOID CONFIRMATION MODAL -->
        <div x-show="voidModalOpen" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div @click.away="voidModalOpen = false" class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl">
                <div class="bg-rose-600 px-6 py-4 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold">Batalkan Transaksi (Void)</h2>
                        <div class="text-xs text-rose-100" x-text="'No. Invoice: ' + voidInvoice"></div>
                    </div>
                    <button type="button" @click="voidModalOpen = false" class="text-rose-200 hover:text-white text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form :action="'/pos/' + voidSaleId + '/void'" method="POST" class="p-6 space-y-4">
                    @csrf

                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs">
                        <i class="fa-solid fa-triangle-exclamation mr-1 text-rose-600"></i>
                        Perhatian: Membatalkan transaksi akan memulihkan kembali kuantitas batch dan stok gudang obat, serta menyesuaikan rekap kasir.
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pembatalan <span class="text-rose-500">*</span></label>
                        <textarea name="reason" required rows="2" placeholder="Contoh: Salah input obat, pelanggan batal beli..."
                                  class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-rose-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="voidModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg shadow-sm">
                            Ya, Batalkan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
