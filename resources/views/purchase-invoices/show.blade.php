<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900">Faktur: {{ $purchaseInvoice->invoice_number }}</h1>
                @php
                    $paymentClasses = [
                        'unpaid' => 'bg-rose-100 text-rose-800 border-rose-200',
                        'partial' => 'bg-amber-100 text-amber-800 border-amber-200',
                        'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    ];
                    $paymentLabels = [
                        'unpaid' => 'Belum Lunas',
                        'partial' => 'Sebagian (Cicil)',
                        'paid' => 'Lunas',
                    ];
                @endphp
                <span class="px-3 py-1 text-xs font-semibold rounded-full border {{ $paymentClasses[$purchaseInvoice->payment_status] ?? 'bg-slate-100 text-slate-600' }}">
                    {{ $paymentLabels[$purchaseInvoice->payment_status] ?? ucfirst($purchaseInvoice->payment_status) }}
                </span>
            </div>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('purchase-invoices.index') }}" class="hover:text-slate-900">Faktur Pembelian</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Detail</span>
            </nav>
        </div>

        <div class="flex items-center gap-2">
            @if($purchaseInvoice->purchaseOrder)
                <a href="{{ route('purchase-orders.show', $purchaseInvoice->purchaseOrder) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                    <i class="fa-solid fa-file-invoice"></i> Lihat PO Terkait
                </a>
            @endif
        </div>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Kartu Tagihan & Sisa -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                <i class="fa-solid fa-wallet text-emerald-600"></i> Status Finansial Faktur
            </h2>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Total Tagihan:</span>
                    <span class="font-bold text-slate-900">{{ format_rupiah($purchaseInvoice->total_amount) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Sudah Dibayar:</span>
                    <span class="font-semibold text-emerald-600">{{ format_rupiah($purchaseInvoice->paid_amount) }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100 flex justify-between text-base">
                    <span class="font-bold text-slate-800">Sisa Tagihan:</span>
                    <span class="font-extrabold {{ $purchaseInvoice->remaining_amount > 0 ? 'text-rose-600' : 'text-slate-500' }}">
                        {{ format_rupiah($purchaseInvoice->remaining_amount) }}
                    </span>
                </div>
            </div>

            <!-- Progress Bar Pembayaran -->
            @php
                $percentage = $purchaseInvoice->total_amount > 0 ? min(100, round(($purchaseInvoice->paid_amount / $purchaseInvoice->total_amount) * 100)) : 0;
            @endphp
            <div>
                <div class="flex justify-between text-[11px] text-slate-500 mb-1">
                    <span>Progress Pelunasan</span>
                    <span>{{ $percentage }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-emerald-500 h-2 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                </div>
            </div>
        </div>

        <!-- Info Supplier & Jatuh Tempo -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-3 text-xs">
            <h2 class="font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-2">
                <i class="fa-solid fa-truck-field text-emerald-600"></i> Informasi Supplier
            </h2>
            <div class="text-sm font-bold text-slate-900">{{ $purchaseInvoice->supplier->name ?? '-' }}</div>
            <div class="text-slate-600">Kontak: {{ $purchaseInvoice->supplier->contact_person ?? '-' }} ({{ $purchaseInvoice->supplier->phone ?? '-' }})</div>
            <div class="text-slate-600">Rekening Bank Supplier:</div>
            <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 font-mono text-[11px]">
                {{ $purchaseInvoice->supplier->bank_name ?? 'Bank -' }} - {{ $purchaseInvoice->supplier->bank_account_number ?? '-' }}
                <div class="text-[10px] text-slate-500 font-sans mt-0.5">a.n {{ $purchaseInvoice->supplier->bank_account_name ?? '-' }}</div>
            </div>
        </div>

        <!-- Jadwal & Jatuh Tempo -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-3 text-xs">
            <h2 class="font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-2">
                <i class="fa-regular fa-calendar-check text-emerald-600"></i> Jadwal Jatuh Tempo
            </h2>
            <div class="flex justify-between">
                <span class="text-slate-500">Tanggal Faktur:</span>
                <span class="font-semibold text-slate-800">{{ $purchaseInvoice->invoice_date->format('d M Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Jatuh Tempo:</span>
                <span class="font-semibold {{ $purchaseInvoice->due_date->isPast() && $purchaseInvoice->payment_status !== 'paid' ? 'text-rose-600 font-bold' : 'text-slate-800' }}">
                    {{ $purchaseInvoice->due_date->format('d M Y') }}
                </span>
            </div>
            @if($purchaseInvoice->due_date->isPast() && $purchaseInvoice->payment_status !== 'paid')
                <div class="p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 font-medium text-[11px] flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                    <span>Tagihan ini telah melewati tanggal jatuh tempo!</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Form Tambah Pembayaran & Riwayat Pembayaran -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Form Bayar (Jika belum lunas) -->
        @if($purchaseInvoice->remaining_amount > 0)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-sm font-semibold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-money-bill-wave text-emerald-600"></i> Input Pembayaran Tagihan
                </h2>

                <form method="POST" action="{{ route('purchase-invoices.payments.store', $purchaseInvoice) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pembayaran <span class="text-rose-500">*</span></label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" required
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal Pembayaran (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="any" min="0.01" max="{{ $purchaseInvoice->remaining_amount }}"
                               name="amount" value="{{ old('amount', $purchaseInvoice->remaining_amount) }}" required
                               class="w-full px-3 py-2 text-xs font-bold text-slate-800 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <span class="text-[10px] text-slate-400">Maksimal: {{ format_rupiah($purchaseInvoice->remaining_amount) }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Pembayaran <span class="text-rose-500">*</span></label>
                        <select name="payment_method" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="transfer">Transfer Bank</option>
                            <option value="cash">Tunai (Kasir/Kas Kantor)</option>
                            <option value="giro">Giro / Cek</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">No. Bukti Referensi / Transfer</label>
                        <input type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="Contoh: TRF-BCA-98124"
                               class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Pembayaran</label>
                        <textarea name="notes" rows="2" placeholder="Catatan opsional..."
                                  class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check"></i> Simpan Pembayaran
                    </button>
                </form>
            </div>
        @endif

        <!-- Riwayat Pembayaran -->
        <div class="{{ $purchaseInvoice->remaining_amount > 0 ? 'lg:col-span-2' : 'lg:col-span-3' }} bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i> Riwayat Pembayaran Tagihan
                </h2>
                <span class="text-xs text-slate-500">{{ $purchaseInvoice->payments->count() }} transaksi</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Tgl Pembayaran</th>
                            <th class="px-4 py-3 text-left font-semibold">Metode</th>
                            <th class="px-4 py-3 text-left font-semibold">Referensi</th>
                            <th class="px-4 py-3 text-right font-semibold">Jumlah Dibayar</th>
                            <th class="px-4 py-3 text-left font-semibold">Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($purchaseInvoice->payments as $payment)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 text-slate-800 font-medium">{{ $payment->payment_date->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                        {{ $payment->payment_method }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 font-mono">{{ $payment->reference_number ?? '-' }}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-600">{{ format_rupiah($payment->amount) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $payment->creator->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-receipt text-2xl mb-2"></i>
                                    <p>Belum ada catatan pembayaran untuk faktur ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
