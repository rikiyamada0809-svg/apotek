<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Seed roles and permissions for the pharmacy system.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions per module
        $modules = [
            'dashboard' => ['view'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'categories' => ['view', 'create', 'edit', 'delete'],
            'units' => ['view', 'create', 'edit', 'delete'],
            'manufacturers' => ['view', 'create', 'edit', 'delete'],
            'suppliers' => ['view', 'create', 'edit', 'delete'],
            'products' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
            'customers' => ['view', 'create', 'edit', 'delete'],
            'doctors' => ['view', 'create', 'edit', 'delete'],
            'warehouses' => ['view', 'create', 'edit', 'delete'],
            'pos' => ['view', 'create', 'void', 'hold', 'close-shift'],
            'prescriptions' => ['view', 'create', 'validate'],
            'purchase-orders' => ['view', 'create', 'edit', 'delete', 'approve'],
            'goods-receipts' => ['view', 'create', 'verify'],
            'purchase-returns' => ['view', 'create', 'approve'],
            'sale-returns' => ['view', 'create', 'approve'],
            'stock-opname' => ['view', 'create', 'approve', 'adjust'],
            'stock' => ['view', 'transfer', 'destroy'],
            'reports' => ['view-financial', 'view-stock', 'view-sales', 'export'],
        ];

        // Create all permissions
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::findOrCreate("{$module}.{$action}");
            }
        }

        // Create roles and assign permissions
        $pemilik = Role::findOrCreate('Pemilik');
        $pemilik->syncPermissions(Permission::all());

        $manajer = Role::findOrCreate('Manajer');
        $manajer->syncPermissions(Permission::whereNotIn('name', [
            'users.create', 'users.edit', 'users.delete',
            'prescriptions.validate',
        ])->pluck('name'));

        $apoteker = Role::findOrCreate('Apoteker');
        $apoteker->syncPermissions([
            'dashboard.view',
            'products.view',
            'customers.view',
            'doctors.view',
            'pos.view', 'pos.create',
            'prescriptions.view', 'prescriptions.create', 'prescriptions.validate',
            'sale-returns.view', 'sale-returns.create',
            'stock.view',
            'reports.view-stock',
        ]);

        $kasir = Role::findOrCreate('Kasir');
        $kasir->syncPermissions([
            'products.view',
            'customers.view',
            'pos.view', 'pos.create', 'pos.hold', 'pos.close-shift',
            'sale-returns.view', 'sale-returns.create',
        ]);

        $adminGudang = Role::findOrCreate('Admin Gudang');
        $adminGudang->syncPermissions([
            'products.view',
            'suppliers.view',
            'warehouses.view',
            'goods-receipts.view', 'goods-receipts.create', 'goods-receipts.verify',
            'purchase-returns.view', 'purchase-returns.create',
            'stock-opname.view', 'stock-opname.create',
            'stock.view', 'stock.transfer', 'stock.destroy',
            'reports.view-stock',
        ]);
    }
}
