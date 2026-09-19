<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PurchaseInvoice;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsModuleTest extends TestCase
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

    public function test_user_can_view_reports_hub(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
        $response->assertSee('Pusat Laporan Apotek');
    }

    public function test_user_can_view_sales_reports_with_subtypes(): void
    {
        $customer = Customer::factory()->create(['name' => 'Budi Sutrisno']);
        $product = Product::factory()->create(['name' => 'Amoxicillin 500mg']);
        $unit = Unit::factory()->create();

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'purchase_price' => 5000,
            'selling_price' => 10000,
        ]);

        $batch = Batch::factory()->create(['product_id' => $product->id, 'purchase_price' => 5000]);

        $sale = Sale::create([
            'invoice_number' => 'POS-202609-0010',
            'customer_id' => $customer->id,
            'cashier_id' => $this->adminUser->id,
            'subtotal' => 30000,
            'grand_total' => 30000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'sale_date' => now(),
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'unit_id' => $unit->id,
            'quantity' => 3,
            'unit_price' => 10000,
            'subtotal' => 30000,
        ]);

        // 1. Daily sales report
        $resDaily = $this->actingAs($this->adminUser)->get(route('reports.sales', ['type' => 'daily']));
        $resDaily->assertStatus(200);
        $resDaily->assertSee($sale->invoice_number);

        // 2. Product sales report
        $resProduct = $this->actingAs($this->adminUser)->get(route('reports.sales', ['type' => 'product']));
        $resProduct->assertStatus(200);
        $resProduct->assertSee('Amoxicillin 500mg');

        // 3. Gross profit report
        $resProfit = $this->actingAs($this->adminUser)->get(route('reports.sales', ['type' => 'gross_profit']));
        $resProfit->assertStatus(200);
        $resProfit->assertSee('Laba Kotor');

        // 4. Export PDF
        $resPdf = $this->actingAs($this->adminUser)->get(route('reports.sales.pdf', ['type' => 'daily']));
        $resPdf->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $resPdf->headers->get('Content-Type'));

        // 5. Export CSV
        $resCsv = $this->actingAs($this->adminUser)->get(route('reports.sales.csv', ['type' => 'daily']));
        $resCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resCsv->headers->get('Content-Type'));
    }

    public function test_user_can_view_stock_reports_and_export(): void
    {
        $warehouse = Warehouse::factory()->create(['name' => 'Gudang Utama']);
        $product = Product::factory()->create(['name' => 'Paracetamol Tablet']);
        $unit = Unit::factory()->create();

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'purchase_price' => 4000,
            'selling_price' => 6000,
        ]);

        $batch = Batch::factory()->create([
            'product_id' => $product->id,
            'current_qty' => 50,
            'purchase_price' => 4000,
            'expiry_date' => now()->addMonths(2)->toDateString(),
        ]);

        Stock::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'quantity' => 50,
        ]);

        // 1. Current stock & valuation report
        $resCurrent = $this->actingAs($this->adminUser)->get(route('reports.stock', ['type' => 'current']));
        $resCurrent->assertStatus(200);
        $resCurrent->assertSee('Paracetamol Tablet');
        $resCurrent->assertSee('200.000'); // 50 * 4000

        // 2. Expired stock report
        $resExpired = $this->actingAs($this->adminUser)->get(route('reports.stock', [
            'type' => 'expired',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
        ]));
        $resExpired->assertStatus(200);
        $resExpired->assertSee($batch->batch_number);

        // 3. Export PDF
        $resPdf = $this->actingAs($this->adminUser)->get(route('reports.stock.pdf', ['type' => 'current']));
        $resPdf->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $resPdf->headers->get('Content-Type'));

        // 4. Export CSV
        $resCsv = $this->actingAs($this->adminUser)->get(route('reports.stock.csv', ['type' => 'current']));
        $resCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resCsv->headers->get('Content-Type'));
    }

    public function test_user_can_view_finance_reports_and_profit_loss(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Kimia Farma Trading']);
        $invoice = PurchaseInvoice::create([
            'invoice_number' => 'INV-PUR-001',
            'supplier_id' => $supplier->id,
            'invoice_date' => now()->toDateString(),
            'total_amount' => 1000000,
            'paid_amount' => 400000,
            'payment_status' => 'partial',
            'due_date' => now()->addDays(10)->toDateString(),
        ]);

        Customer::factory()->create([
            'outstanding_balance' => 125000,
        ]);

        // 1. Purchases report
        $resPurchases = $this->actingAs($this->adminUser)->get(route('reports.finance', ['type' => 'purchases']));
        $resPurchases->assertStatus(200);
        $resPurchases->assertSee($invoice->invoice_number);

        // 2. Debts report
        $resDebts = $this->actingAs($this->adminUser)->get(route('reports.finance', ['type' => 'debts']));
        $resDebts->assertStatus(200);
        $resDebts->assertSee('600.000'); // Remaining 1M - 400k

        // 3. Aging debts report
        $resAging = $this->actingAs($this->adminUser)->get(route('reports.finance', ['type' => 'aging_debts']));
        $resAging->assertStatus(200);
        $resAging->assertSee('Belum Jatuh Tempo');

        // 4. Simple Profit & Loss
        $resPL = $this->actingAs($this->adminUser)->get(route('reports.finance', ['type' => 'profit_loss']));
        $resPL->assertStatus(200);
        $resPL->assertSee('Laba Rugi');

        // 5. Cash flow
        $resCF = $this->actingAs($this->adminUser)->get(route('reports.finance', ['type' => 'cash_flow']));
        $resCF->assertStatus(200);
        $resCF->assertSee('Arus Kas Masuk');

        // 6. Export PDF
        $resPdf = $this->actingAs($this->adminUser)->get(route('reports.finance.pdf', ['type' => 'debts']));
        $resPdf->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $resPdf->headers->get('Content-Type'));

        // 7. Export CSV
        $resCsv = $this->actingAs($this->adminUser)->get(route('reports.finance.csv', ['type' => 'debts']));
        $resCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resCsv->headers->get('Content-Type'));
    }
}
