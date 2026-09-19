<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PurchaseInvoice;
use App\Models\Sale;
use App\Models\Shift;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Pemilik');
    }

    public function test_user_can_view_main_dashboard(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $unit = Unit::factory()->create();

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'purchase_price' => 10000,
            'selling_price' => 15000,
        ]);

        $batch = Batch::factory()->create([
            'product_id' => $product->id,
            'purchase_price' => 10000,
        ]);

        Stock::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'quantity' => 20,
        ]);

        Sale::create([
            'invoice_number' => 'POS-202609-0001',
            'grand_total' => 150000,
            'subtotal' => 150000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'sale_date' => now(),
            'cashier_id' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertSee('Dashboard Apotek');
        $response->assertSee('Total Penjualan');
        $response->assertSee('150.000');
    }

    public function test_dashboard_period_filtering(): void
    {
        // Sale yesterday
        Sale::create([
            'invoice_number' => 'POS-202609-0002',
            'grand_total' => 200000,
            'subtotal' => 200000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'sale_date' => now()->subDays(2),
            'cashier_id' => $this->adminUser->id,
        ]);

        // Sale today
        Sale::create([
            'invoice_number' => 'POS-202609-0003',
            'grand_total' => 50000,
            'subtotal' => 50000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'sale_date' => now(),
            'cashier_id' => $this->adminUser->id,
        ]);

        // Filter today
        $responseToday = $this->actingAs($this->adminUser)->get(route('dashboard', ['period' => 'today']));
        $responseToday->assertStatus(200);
        $responseToday->assertSee('50.000');

        // Filter this week
        $responseWeek = $this->actingAs($this->adminUser)->get(route('dashboard', ['period' => 'this_week']));
        $responseWeek->assertStatus(200);
    }

    public function test_dashboard_displays_supplier_debt_and_customer_receivables(): void
    {
        $supplier = Supplier::factory()->create();
        PurchaseInvoice::create([
            'invoice_number' => 'INV-SUP-001',
            'supplier_id' => $supplier->id,
            'invoice_date' => now()->toDateString(),
            'total_amount' => 500000,
            'paid_amount' => 200000,
            'payment_status' => 'partial',
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        Customer::factory()->create([
            'name' => 'Pak Budi',
            'outstanding_balance' => 75000,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('300.000'); // Remaining debt (500k - 200k)
        $response->assertSee('75.000');  // Customer receivables
    }

    public function test_dashboard_displays_pending_prescriptions_banner(): void
    {
        Prescription::factory()->create(['status' => 'submitted']);

        $response = $this->actingAs($this->adminUser)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Resep Dokter Menunggu Validasi');
    }

    public function test_cashier_sees_shift_info_on_dashboard(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Kasir');

        Shift::create([
            'user_id' => $cashier->id,
            'status' => 'open',
            'start_time' => now(),
            'opening_cash' => 100000,
        ]);

        $response = $this->actingAs($cashier)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Status Shift Kasir Anda');
        $response->assertSee('100.000');
    }
}
