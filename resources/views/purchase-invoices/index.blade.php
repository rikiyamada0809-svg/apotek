<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Faktur Pembelian Supplier</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Faktur Pembelian</span>
            </nav>
        </div>
        <a href="{{ route('purchase-invoices.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Catat Faktur Baru
        </a>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <form method="GET" action="{{ route('purchase-invoices.index') }}" class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Faktur, Supplier, atau No. PO..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-sm"></i>
                </div>
                <select name="supplier_id" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
                <select name="payment_status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Status Bayar</option>
                    <option value="unpaid" @selected(request('payment_status') === 'unpaid')>Belum Lunas</option>
                    <option value="partial" @selected(request('payment_status') === 'partial')>Sebagian (Cicil)</option>
                    <option value="paid" @selected(request('payment_status') === 'paid')>Lunas</option>
                </select>
                <label class="inline-flex items-center gap-2 px-3 py-2 text-sm border border-slate-200 rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 text-slate-700">
                    <input type="checkbox" name="overdue" value="1" @checked(request('overdue') === '1') class="rounded text-rose-600 focus:ring-rose-500">
                    <span class="text-xs font-semibold text-rose-700">Hanya Jatuh Tempo</span>
                </label>
                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg transition-colors">Filter</button>
                @if(request()->hasAny(['search','supplier_id','payment_status','overdue']))
                    <a href="{{ route('purchase-invoices.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">No. Faktur</th>
                        <th class="px-4 py-3 text-left font-semibold">Supplier</th>
                        <th class="px-4 py-3 text-left font-semibold">Referensi PO</th>
                        <th class="px-4 py-3 text-left font-semibold">Tgl Faktur</th>
                        <th class="px-4 py-3 text-left font-semibold">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-right font-semibold">Total Tagihan</th>
                        <th class="px-4 py-3 text-right font-semibold">Terbayar</th>
                        <th class="px-4 py-3 text-right font-semibold">Sisa Tagihan</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($invoices as $invoice)
                        @php
                            $isOverdue = $invoice->due_date->isPast() && $invoice->payment_status !== 'paid';
                        @endphp
                        <tr class="hover:bg-slate-50/50 {{ $isOverdue ? 'bg-rose-50/20' : '' }}">
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                <a href="{{ route('purchase-invoices.show', $invoice) }}" class="text-emerald-600 hover:text-emerald-700 hover:underline">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $invoice->supplier->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                @if($invoice->purchaseOrder)
                                    <a href="{{ route('purchase-orders.show', $invoice->purchaseOrder) }}" class="hover:text-blue-600 hover:underline">
                                        {{ $invoice->purchaseOrder->po_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $invoice->invoice_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium {{ $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                    {{ $invoice->due_date->format('d M Y') }}
                                </span>
                                @if($isOverdue)
                                    <span class="inline-block px-1.5 py-0.2 text-[9px] font-extrabold rounded bg-rose-100 text-rose-800">Lewat Tempo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ format_rupiah($invoice->total_amount) }}</td>
                            <td class="px-4 py-3 text-right text-emerald-600 font-medium">{{ format_rupiah($invoice->paid_amount) }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ $invoice->remaining_amount > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ format_rupiah($invoice->remaining_amount) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $paymentClasses = [
                                        'unpaid' => 'bg-rose-100 text-rose-800',
                                        'partial' => 'bg-amber-100 text-amber-800',
                                        'paid' => 'bg-emerald-100 text-emerald-800',
                                    ];
                                    $paymentLabels = [
                                        'unpaid' => 'Belum Lunas',
                                        'partial' => 'Sebagian',
                                        'paid' => 'Lunas',
                                    ];
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $paymentClasses[$invoice->payment_status] ?? 'bg-slate-100' }}">
                                    {{ $paymentLabels[$invoice->payment_status] ?? ucfirst($invoice->payment_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('purchase-invoices.show', $invoice) }}" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded bg-slate-100 hover:bg-slate-200 text-slate-700">
                                    <i class="fa-solid fa-eye text-xs"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-receipt text-3xl mb-2"></i>
                                <p>Belum ada faktur pembelian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
