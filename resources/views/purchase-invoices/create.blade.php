<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Catat Faktur Pembelian Supplier</h1>
        <nav class="flex text-xs text-slate-500 mt-1">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('purchase-invoices.index') }}" class="hover:text-slate-900">Faktur Pembelian</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 font-medium">Buat Baru</span>
        </nav>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
            <p class="font-bold mb-1">Terdapat kesalahan input:</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('purchase-invoices.store') }}" class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                <h2 class="text-base font-semibold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i> Rincian Tagihan Faktur
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">No. Faktur dari Supplier <span class="text-rose-500">*</span></label>
                        <input type="text" name="invoice_number" value="{{ old('invoice_number', $suggestedNumber) }}" required
                               placeholder="Contoh: INV/2026/09/012"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tautkan ke Purchase Order (Opsional)</label>
                        <select name="po_id" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="">-- Tanpa PO (Faktur Langsung) --</option>
                            @foreach($purchaseOrders as $po)
                                <option value="{{ $po->id }}" @selected(old('po_id', optional($selectedPo)->id) == $po->id)>
                                    {{ $po->po_number }} - {{ $po->supplier->name ?? '' }} ({{ format_rupiah($po->grand_total) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Supplier / Distributor <span class="text-rose-500">*</span></label>
                        <select name="supplier_id" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id', optional($selectedPo)->supplier_id) == $supplier->id)>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Total Nominal Faktur (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="any" min="0" name="total_amount"
                               value="{{ old('total_amount', optional($selectedPo)->grand_total ?? '') }}" required
                               placeholder="Contoh: 1500000"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 font-semibold text-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Faktur <span class="text-rose-500">*</span></label>
                        <input type="date" name="invoice_date" value="{{ old('invoice_date', now()->toDateString()) }}" required
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Jatuh Tempo <span class="text-rose-500">*</span></label>
                        <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(30)->toDateString()) }}" required
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan / Rekening Pembayaran Supplier</label>
                    <textarea name="notes" rows="2" placeholder="Catatan rekening bank supplier, no referensi, dll..."
                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('purchase-invoices.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-lg transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm inline-flex items-center gap-2">
                    <i class="fa-solid fa-save"></i> Simpan Faktur Pembelian
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
