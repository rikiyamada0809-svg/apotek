<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pelanggan / Pasien</h1>
            <nav class="flex text-xs text-slate-500 mt-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-medium">Pelanggan</span>
            </nav>
        </div>
        <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Tambah Pelanggan
        </a>
    </div>

    @if(session('success')) <x-alert type="success">{{ session('success') }}</x-alert> @endif
    @if(session('error')) <x-alert type="error">{{ session('error') }}</x-alert> @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <form method="GET" action="{{ route('customers.index') }}" class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode, atau telepon pelanggan..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-sm"></i>
                </div>
                <select name="customer_type" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Tipe</option>
                    <option value="regular" @selected(request('customer_type') == 'regular')>Regular</option>
                    <option value="member" @selected(request('customer_type') == 'member')>Member</option>
                    <option value="reseller" @selected(request('customer_type') == 'reseller')>Reseller</option>
                </select>
                <select name="status" class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="1" @selected(request('status') === '1')>Aktif</option>
                    <option value="0" @selected(request('status') === '0')>Nonaktif</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg transition-colors">Filter</button>
                @if(request()->hasAny(['search','customer_type','status']))
                    <a href="{{ route('customers.index') }}" class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">#</th>
                        <th class="px-4 py-3 text-left font-semibold">Kode</th>
                        <th class="px-4 py-3 text-left font-semibold">Nama Pelanggan</th>
                        <th class="px-4 py-3 text-left font-semibold">Tipe</th>
                        <th class="px-4 py-3 text-left font-semibold">Telepon / Email</th>
                        <th class="px-4 py-3 text-right font-semibold">Diskon (%)</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 text-slate-500">{{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-700">{{ $customer->code }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">
                                <a href="{{ route('customers.show', $customer) }}" class="hover:text-emerald-600 transition-colors">
                                    {{ $customer->name }}
                                </a>
                                @if($customer->membership_number)
                                    <div class="text-[11px] text-emerald-600 font-mono">No: {{ $customer->membership_number }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $typeBadge = match($customer->customer_type) {
                                        'member' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'reseller' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold border {{ $typeBadge }}">
                                    {{ ucfirst($customer->customer_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <div>{{ $customer->phone ?? '-' }}</div>
                                @if($customer->email)
                                    <div class="text-xs text-slate-400">{{ $customer->email }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-700">
                                {{ $customer->discount_percentage > 0 ? number_format($customer->discount_percentage, 1) . '%' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <x-status-badge :active="$customer->is_active" />
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('customers.show', $customer) }}" class="text-slate-500 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition-colors" title="Lihat Detail">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('customers.edit', $customer) }}" class="text-blue-500 hover:text-blue-700 p-1.5 rounded-lg hover:bg-blue-50 transition-colors" title="Edit">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </a>
                                    <x-delete-button :action="route('customers.destroy', $customer)" confirmText="Apakah Anda yakin ingin menghapus pelanggan ini?" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-users text-3xl mb-2"></i>
                                <p>Belum ada data pelanggan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
