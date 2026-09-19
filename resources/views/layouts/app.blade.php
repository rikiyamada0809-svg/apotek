<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: true, mobileSidebarOpen: false }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SmartHR') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- FontAwesome Icons for sidebar -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            [x-cloak] { display: none !important; }
            /* Custom scrollbar for sidebar */
            .sidebar-scroll::-webkit-scrollbar {
                width: 5px;
            }
            .sidebar-scroll::-webkit-scrollbar-track {
                background: transparent;
            }
            .sidebar-scroll::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 4px;
            }
            .sidebar-scroll::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-[#f4f7fa] text-slate-800">
        <div class="min-h-screen flex">
            
            <!-- Sidebar (Desktop & Mobile Panel) -->
            <aside class="bg-white border-r border-slate-200 w-64 fixed inset-y-0 left-0 z-30 transition-transform duration-300 transform md:translate-x-0 flex flex-col"
                   :class="{'translate-x-0': mobileSidebarOpen, '-translate-x-full': !mobileSidebarOpen}">
                
                <!-- Sidebar Header / Logo -->
                <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100 shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <!-- SmartHR Styled SVG Logo -->
                        <div class="w-8 h-8 rounded-full bg-[#f83f37] flex items-center justify-center text-white font-extrabold text-sm shadow-sm">
                            <i class="fa-solid fa-atom text-white"></i>
                        </div>
                        <span class="text-xl font-bold tracking-tight text-slate-900">Smart<span class="text-[#f83f37]">HR</span></span>
                    </a>
                    
                    <!-- Close button for mobile sidebar -->
                    <button class="md:hidden text-slate-500 hover:text-slate-700" @click="mobileSidebarOpen = false">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Sidebar Nav Items -->
                <div class="flex-1 overflow-y-auto sidebar-scroll px-4 py-6 space-y-6">
                    
                    <!-- MAIN MENU / UTAMA Section -->
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-3">Utama</div>
                        <nav class="space-y-1">
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-red-50 text-[#f83f37]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-house text-base {{ request()->routeIs('dashboard') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Dashboard</span>
                            </a>
                        </nav>
                    </div>

                    <!-- MASTER DATA Section -->
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-3">Master Data</div>
                        <nav class="space-y-1">
                            <!-- Obat & Produk -->
                            <a href="{{ route('products.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('products.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-pills w-4 text-center {{ request()->routeIs('products.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Obat & Produk</span>
                            </a>

                            <!-- Kategori -->
                            <a href="{{ route('categories.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('categories.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-tags w-4 text-center {{ request()->routeIs('categories.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Kategori</span>
                            </a>

                            <!-- Satuan -->
                            <a href="{{ route('units.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('units.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-box w-4 text-center {{ request()->routeIs('units.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Satuan</span>
                            </a>

                            <!-- Pabrik / Produsen -->
                            <a href="{{ route('manufacturers.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('manufacturers.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-industry w-4 text-center {{ request()->routeIs('manufacturers.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Pabrik / Produsen</span>
                            </a>

                            <!-- Supplier -->
                            <a href="{{ route('suppliers.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('suppliers.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-truck-field w-4 text-center {{ request()->routeIs('suppliers.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Supplier</span>
                            </a>

                            <!-- Pelanggan -->
                            <a href="{{ route('customers.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('customers.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-users w-4 text-center {{ request()->routeIs('customers.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Pelanggan</span>
                            </a>

                            <!-- Dokter -->
                            <a href="{{ route('doctors.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('doctors.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-user-doctor w-4 text-center {{ request()->routeIs('doctors.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Dokter</span>
                            </a>

                            <!-- Gudang & Lokasi -->
                            <a href="{{ route('warehouses.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('warehouses.*') || request()->routeIs('storage-locations.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-warehouse w-4 text-center {{ request()->routeIs('warehouses.*') || request()->routeIs('storage-locations.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Gudang & Lokasi</span>
                            </a>
                        </nav>
                    </div>

                    <!-- KASIR & PENJUALAN Section -->
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-3">Kasir & Penjualan</div>
                        <nav class="space-y-1">
                            <!-- Kasir POS -->
                            <a href="{{ route('pos.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('pos.index') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-cash-register w-4 text-center {{ request()->routeIs('pos.index') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Point of Sale (POS)</span>
                            </a>

                            <!-- Riwayat Penjualan -->
                            <a href="{{ route('pos.history') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('pos.history') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-receipt w-4 text-center {{ request()->routeIs('pos.history') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Riwayat Penjualan</span>
                            </a>

                            <!-- Retur Penjualan -->
                            <a href="{{ route('sale-returns.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('sale-returns.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-arrow-rotate-left w-4 text-center {{ request()->routeIs('sale-returns.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Retur Penjualan</span>
                            </a>
                        </nav>
                    </div>

                    <!-- FARMASI & RESEP Section -->
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-3">Farmasi & Resep</div>
                        <nav class="space-y-1">
                            <!-- Resep Obat -->
                            <a href="{{ route('prescriptions.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('prescriptions.*') || request()->routeIs('compounds.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-file-prescription w-4 text-center {{ request()->routeIs('prescriptions.*') || request()->routeIs('compounds.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Resep Obat & Racikan</span>
                            </a>

                            <!-- Template Racikan -->
                            <a href="{{ route('compound-templates.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('compound-templates.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-mortar-pestle w-4 text-center {{ request()->routeIs('compound-templates.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Template Racikan</span>
                            </a>
                        </nav>
                    </div>

                    <!-- PEMBELIAN / PURCHASING Section -->
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-3">Pembelian</div>
                        <nav class="space-y-1">
                            <!-- Purchase Order -->
                            <a href="{{ route('purchase-orders.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('purchase-orders.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-file-invoice w-4 text-center {{ request()->routeIs('purchase-orders.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Purchase Order (PO)</span>
                            </a>

                            <!-- Penerimaan Barang -->
                            <a href="{{ route('goods-receipts.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('goods-receipts.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-dolly w-4 text-center {{ request()->routeIs('goods-receipts.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Penerimaan Barang</span>
                            </a>

                            <!-- Faktur Pembelian -->
                            <a href="{{ route('purchase-invoices.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('purchase-invoices.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-receipt w-4 text-center {{ request()->routeIs('purchase-invoices.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Faktur Pembelian</span>
                            </a>

                            <!-- Retur Pembelian -->
                            <a href="{{ route('purchase-returns.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('purchase-returns.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-truck-ramp-box w-4 text-center {{ request()->routeIs('purchase-returns.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Retur Pembelian</span>
                            </a>
                        </nav>
                    </div>

                    <!-- INVENTORI & STOK Section -->
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-3">Inventori & Stok</div>
                        <nav class="space-y-1">
                            <!-- Daftar Stok -->
                            <a href="{{ route('stocks.index') }}" 
                               class="flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('stocks.index') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-boxes-stacked w-4 text-center {{ request()->routeIs('stocks.index') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                    <span>Daftar Stok</span>
                                </div>
                                @if(isset($sidebarLowStockCount) && $sidebarLowStockCount > 0)
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold bg-red-100 text-red-600 rounded-full" title="{{ $sidebarLowStockCount }} obat stok menipis/habis">{{ $sidebarLowStockCount }}</span>
                                @endif
                            </a>

                            <!-- Batch & FEFO -->
                            <a href="{{ route('batches.index') }}" 
                               class="flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('batches.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-calendar-check w-4 text-center {{ request()->routeIs('batches.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                    <span>Batch & FEFO</span>
                                </div>
                                @if(isset($sidebarExpiringBatchesCount) && $sidebarExpiringBatchesCount > 0)
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-700 rounded-full" title="{{ $sidebarExpiringBatchesCount }} batch dekat kadaluarsa">{{ $sidebarExpiringBatchesCount }}</span>
                                @endif
                            </a>

                            <!-- Kartu Stok -->
                            <a href="{{ route('stocks.card') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('stocks.card') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-clipboard-list w-4 text-center {{ request()->routeIs('stocks.card') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Kartu Stok Digital</span>
                            </a>

                            <!-- Stok Opname -->
                            <a href="{{ route('stock-opnames.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('stock-opnames.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-clipboard-check w-4 text-center {{ request()->routeIs('stock-opnames.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Stok Opname</span>
                            </a>

                            <!-- Monitoring Expired -->
                            <a href="{{ route('expired.dashboard') }}" 
                               class="flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('expired.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-triangle-exclamation w-4 text-center {{ request()->routeIs('expired.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                    <span>Data & Alert Expired</span>
                                </div>
                                @if(isset($sidebarExpiredCount) && $sidebarExpiredCount > 0)
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold bg-red-600 text-white rounded-full" title="{{ $sidebarExpiredCount }} batch kedaluwarsa">{{ $sidebarExpiredCount }}</span>
                                @elseif(isset($sidebarExpiringBatchesCount) && $sidebarExpiringBatchesCount > 0)
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold bg-amber-500 text-white rounded-full" title="{{ $sidebarExpiringBatchesCount }} batch dekat expired">{{ $sidebarExpiringBatchesCount }}</span>
                                @endif
                            </a>

                            <!-- Pemusnahan Obat -->
                            <a href="{{ route('drug-destructions.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('drug-destructions.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-fire-burner w-4 text-center {{ request()->routeIs('drug-destructions.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Pemusnahan Obat</span>
                            </a>

                            <!-- Batch Recall (Penarikan Obat) -->
                            <a href="{{ route('batch-recalls.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('batch-recalls.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-ban w-4 text-center {{ request()->routeIs('batch-recalls.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Penarikan Obat (Recall)</span>
                            </a>

                            <!-- Transfer Stok Multi-Gudang -->
                            <a href="{{ route('stock-transfers.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('stock-transfers.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-truck-arrow-right w-4 text-center {{ request()->routeIs('stock-transfers.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Transfer Multi-Gudang</span>
                            </a>

                            <!-- Auto-Reorder & ROP -->
                            <a href="{{ route('stocks.reorder') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('stocks.reorder*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-arrows-rotate w-4 text-center {{ request()->routeIs('stocks.reorder*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Auto-Reorder (ROP)</span>
                            </a>

                            <!-- Analisis ABC Pareto -->
                            <a href="{{ route('stocks.abc') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('stocks.abc*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-ranking-star w-4 text-center {{ request()->routeIs('stocks.abc*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Analisis ABC (Pareto)</span>
                            </a>
                        </nav>
                    </div>

                    <!-- LAPORAN & ANALISIS Section -->
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-3">Laporan & Analisis</div>
                        <nav class="space-y-1">
                            <a href="{{ route('reports.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('reports.index') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-chart-pie w-4 text-center {{ request()->routeIs('reports.index') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Pusat Laporan</span>
                            </a>
                            <a href="{{ route('reports.sales') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('reports.sales*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-cash-register w-4 text-center {{ request()->routeIs('reports.sales*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Laporan Penjualan</span>
                            </a>
                            <a href="{{ route('reports.stock') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('reports.stock*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-boxes-stacked w-4 text-center {{ request()->routeIs('reports.stock*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Laporan Stok</span>
                            </a>
                            <a href="{{ route('reports.finance') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('reports.finance*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-file-invoice-dollar w-4 text-center {{ request()->routeIs('reports.finance*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Laporan Keuangan</span>
                            </a>
                        </nav>
                    </div>

                    <!-- PENGATURAN Section -->
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-3">Pengaturan</div>
                        <nav class="space-y-1">
                            <a href="{{ route('users.index') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('users.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-solid fa-user-gear w-4 text-center {{ request()->routeIs('users.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Kelola Pengguna</span>
                            </a>
                            <a href="{{ route('profile.edit') }}" 
                               class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ request()->routeIs('profile.*') ? 'bg-red-50 text-[#f83f37] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fa-regular fa-user w-4 text-center {{ request()->routeIs('profile.*') ? 'text-[#f83f37]' : 'text-slate-400' }}"></i>
                                <span>Profil Saya</span>
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Sidebar Footer -->
                <div class="p-4 border-t border-slate-100 shrink-0">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-red-50 hover:text-red-600 rounded-lg transition-all">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            <span>Sign Out</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main Panel -->
            <div class="flex-1 md:pl-64 flex flex-col min-w-0">
                
                <!-- Top Header -->
                <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 sticky top-0 z-20 shrink-0">
                    <!-- Toggle & Search -->
                    <div class="flex items-center gap-4">
                        <button class="md:hidden text-slate-500 hover:text-slate-700" @click="mobileSidebarOpen = !mobileSidebarOpen">
                            <i class="fa-solid fa-bars text-lg"></i>
                        </button>
                        
                        <!-- Search Bar -->
                        <div class="relative max-w-xs hidden sm:block">
                            <input type="text" 
                                   placeholder="Search in HRMS..." 
                                   class="pl-9 pr-12 py-1.5 bg-slate-50 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500 w-64">
                            <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-[11px]"></i>
                            <span class="absolute right-3 top-2 text-[9px] font-bold text-slate-400 border border-slate-200 bg-white px-1.5 py-0.5 rounded shadow-sm">CTRL + /</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-4">
                        <!-- Notifications Dropdown -->
                        <div class="relative" x-data="{ notifOpen: false }">
                            <button @click="notifOpen = !notifOpen" class="text-slate-500 hover:text-slate-700 relative p-1.5 rounded-full hover:bg-slate-50 focus:outline-none">
                                <i class="fa-regular fa-bell text-base"></i>
                                @if((isset($sidebarLowStockCount) && $sidebarLowStockCount > 0) || (isset($sidebarExpiringBatchesCount) && $sidebarExpiringBatchesCount > 0) || (isset($sidebarExpiredCount) && $sidebarExpiredCount > 0))
                                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                                @endif
                            </button>

                            <div class="absolute right-0 mt-2 w-72 bg-white border border-slate-200 rounded-xl shadow-lg py-2 z-30" 
                                 x-show="notifOpen" 
                                 @click.away="notifOpen = false" 
                                 x-transition x-cloak>
                                <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-800">Notifikasi Peringatan</span>
                                    <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-medium">Inventori</span>
                                </div>
                                <div class="divide-y divide-slate-100 max-h-60 overflow-y-auto">
                                    @if(isset($sidebarExpiredCount) && $sidebarExpiredCount > 0)
                                        <a href="{{ route('expired.index', ['range' => 'expired']) }}" class="flex items-start gap-3 px-4 py-3 hover:bg-red-50/50 transition-colors">
                                            <div class="w-7 h-7 rounded-full bg-red-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                                                <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-semibold text-red-600">{{ $sidebarExpiredCount }} Batch Telah Kedaluwarsa</p>
                                                <p class="text-[11px] text-slate-500">Perlu tindakan retur atau pemusnahan obat.</p>
                                            </div>
                                        </a>
                                    @endif

                                    @if(isset($sidebarLowStockCount) && $sidebarLowStockCount > 0)
                                        <a href="{{ route('stocks.index', ['stock_status' => 'low_stock']) }}" class="flex items-start gap-3 px-4 py-3 hover:bg-red-50/50 transition-colors">
                                            <div class="w-7 h-7 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0 mt-0.5">
                                                <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-semibold text-slate-800">{{ $sidebarLowStockCount }} Obat Stok Kritis/Habis</p>
                                                <p class="text-[11px] text-slate-500">Stok mencapai atau di bawah batas minimum.</p>
                                            </div>
                                        </a>
                                    @endif

                                    @if(isset($sidebarExpiringBatchesCount) && $sidebarExpiringBatchesCount > 0)
                                        <a href="{{ route('batches.index', ['status' => 'expiring_soon']) }}" class="flex items-start gap-3 px-4 py-3 hover:bg-amber-50/50 transition-colors">
                                            <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                                                <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-semibold text-slate-800">{{ $sidebarExpiringBatchesCount }} Batch Dekat Expired</p>
                                                <p class="text-[11px] text-slate-500">Kadaluarsa dalam ≤ 30 hari kedepan.</p>
                                            </div>
                                        </a>
                                    @endif

                                    @if((!isset($sidebarLowStockCount) || $sidebarLowStockCount == 0) && (!isset($sidebarExpiringBatchesCount) || $sidebarExpiringBatchesCount == 0) && (!isset($sidebarExpiredCount) || $sidebarExpiredCount == 0))
                                        <div class="px-4 py-6 text-center">
                                            <i class="fa-regular fa-circle-check text-emerald-500 text-2xl mb-1"></i>
                                            <p class="text-xs text-slate-500">Semua stok & batch dalam kondisi aman!</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Messages -->
                        <button class="text-slate-500 hover:text-slate-700 relative p-1.5 rounded-full hover:bg-slate-50">
                            <i class="fa-regular fa-envelope text-base"></i>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>

                        <!-- User Profile Dropdown -->
                        <div class="relative" x-data="{ dropdownOpen: false }">
                            <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-2 focus:outline-none">
                                <img src="/images/stephan_avatar.jpg" alt="User" class="w-8 h-8 rounded-full object-cover ring-2 ring-slate-100">
                                <span class="hidden md:inline text-xs font-semibold text-slate-700">{{ Auth::user()->name }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                            </button>
                            
                            <div class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg py-1 z-30" 
                                 x-show="dropdownOpen" 
                                 @click.away="dropdownOpen = false"
                                 x-transition x-cloak>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 transition-colors">
                                    <i class="fa-regular fa-user mr-2 text-slate-400"></i> Profil Saya
                                </a>
                                <hr class="border-slate-100 my-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs text-red-600 hover:bg-red-50 transition-colors font-semibold">
                                        <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 p-6 space-y-6 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
            
            <!-- Mobile Overlay -->
            <div class="fixed inset-0 bg-slate-900/40 z-20 md:hidden"
                 x-show="mobileSidebarOpen"
                 @click="mobileSidebarOpen = false"
                 x-transition x-cloak>
            </div>

        </div>
    </body>
</html>
