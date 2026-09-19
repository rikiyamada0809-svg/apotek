<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\StockSetting;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedStockManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Warehouse $mainWarehouse;

    protected Warehouse $branchWarehouse;

    protected Unit $baseUnit;

    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Pemilik');

        $this->mainWarehouse = Warehouse::create([
            'code' => 'GUD-UTAMA',
            'name' => 'Gudang Utama',
            'is_active' => true,
        ]);

        $this->branchWarehouse = Warehouse::create([
            'code' => 'GUD-CABANG',
            'name' => 'Gudang Cabang 1',
            'is_active' => true,
        ]);

        $this->baseUnit = Unit::factory()->create(['name' => 'Tablet']);
        $this->supplier = Supplier::factory()->create(['name' => 'PT Kimia Farma']);
    }

    public function test_stock_check_reorder_command_and_draft_po_generation(): void
    {
        $product = Product::factory()->create(['name' => 'Paracetamol 500mg']);
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $this->baseUnit->id,
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'purchase_price' => 500,
            'selling_price' => 1000,
        ]);

        $batch = Batch::factory()->create([
            'product_id' => $product->id,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => 500,
        ]);

        StockSetting::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->mainWarehouse->id,
            'min_stock' => 10,
            'max_stock' => 50,
            'reorder_point' => 20,
            'safety_stock' => 5,
            'lead_time_days' => 3,
        ]);

        // Stock is 5 (below ROP of 20)
        Stock::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->mainWarehouse->id,
            'batch_id' => $batch->id,
            'quantity' => 5,
            'reserved_qty' => 0,
            'available_qty' => 5,
            'status' => 'available',
        ]);

        $this->artisan('stock:check-reorder', ['--generate-po' => true])
            ->assertSuccessful();

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->mainWarehouse->id,
            'status' => 'draft',
        ]);

        $po = PurchaseOrder::where('supplier_id', $this->supplier->id)->first();
        $this->assertNotNull($po);
        $this->assertDatabaseHas('purchase_order_items', [
            'po_id' => $po->id,
            'product_id' => $product->id,
            'quantity_ordered' => 45, // 50 max_stock - 5 current_stock
        ]);
    }

    public function test_abc_analysis_command_and_pareto_classification(): void
    {
        $productA = Product::factory()->create(['name' => 'Expensive Product A']);
        $productB = Product::factory()->create(['name' => 'Medium Product B']);
        $productC = Product::factory()->create(['name' => 'Cheap Product C']);

        $sale = Sale::create([
            'invoice_number' => 'POS-202609-ABC01',
            'warehouse_id' => $this->mainWarehouse->id,
            'cashier_id' => $this->adminUser->id,
            'subtotal' => 10000000,
            'grand_total' => 10000000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'sale_date' => now()->toDateString(),
        ]);

        // Revenue: A = 7,000,000 (70%), B = 2,000,000 (20%), C = 1,000,000 (10%)
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $productA->id,
            'unit_id' => $this->baseUnit->id,
            'quantity' => 70,
            'unit_price' => 100000,
            'subtotal' => 7000000,
            'total_price' => 7000000,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $productB->id,
            'unit_id' => $this->baseUnit->id,
            'quantity' => 40,
            'unit_price' => 50000,
            'subtotal' => 2000000,
            'total_price' => 2000000,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $productC->id,
            'unit_id' => $this->baseUnit->id,
            'quantity' => 100,
            'unit_price' => 10000,
            'subtotal' => 1000000,
            'total_price' => 1000000,
        ]);

        $this->artisan('stock:abc-analysis', ['--days' => 30])
            ->assertSuccessful();

        $this->assertDatabaseHas('stock_settings', [
            'product_id' => $productA->id,
            'abc_category' => 'A',
        ]);

        $this->assertDatabaseHas('stock_settings', [
            'product_id' => $productB->id,
            'abc_category' => 'B',
        ]);

        $this->assertDatabaseHas('stock_settings', [
            'product_id' => $productC->id,
            'abc_category' => 'C',
        ]);
    }

    public function test_web_reorder_and_abc_dashboards(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('stocks.reorder'));
        $response->assertStatus(200);
        $response->assertViewIs('stocks.reorder');
        $response->assertSee('Rekomendasi Pemesanan Ulang');

        $responseAbc = $this->actingAs($this->adminUser)->get(route('stocks.abc'));
        $responseAbc->assertStatus(200);
        $responseAbc->assertViewIs('stocks.abc');
        $responseAbc->assertSee('Analisis ABC');

        $responseRun = $this->actingAs($this->adminUser)->post(route('stocks.abc.run'), ['days' => 90]);
        $responseRun->assertSessionHas('success');
    }

    public function test_stock_transfer_complete_lifecycle(): void
    {
        $product = Product::factory()->create(['name' => 'Amoxicillin 500mg']);
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $this->baseUnit->id,
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'purchase_price' => 2000,
            'selling_price' => 4000,
        ]);

        $batch = Batch::factory()->create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-TEST-001',
            'supplier_id' => $this->supplier->id,
        ]);

        // Main warehouse stock: 100
        Stock::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->mainWarehouse->id,
            'batch_id' => $batch->id,
            'quantity' => 100,
            'reserved_qty' => 0,
            'available_qty' => 100,
            'status' => 'available',
        ]);

        // 1. View create form
        $responseCreate = $this->actingAs($this->adminUser)->get(route('stock-transfers.create'));
        $responseCreate->assertStatus(200);

        // 2. Store transfer (30 units)
        $storeData = [
            'from_warehouse_id' => $this->mainWarehouse->id,
            'to_warehouse_id' => $this->branchWarehouse->id,
            'transfer_date' => now()->toDateString(),
            'notes' => 'Transfer bulanan ke cabang 1',
            'items' => [
                [
                    'product_id' => $product->id,
                    'batch_id' => $batch->id,
                    'quantity' => 30,
                    'notes' => 'Pack kardus',
                ],
            ],
        ];

        $responseStore = $this->actingAs($this->adminUser)->post(route('stock-transfers.store'), $storeData);
        $responseStore->assertSessionHas('success');

        $transfer = StockTransfer::where('from_warehouse_id', $this->mainWarehouse->id)->first();
        $this->assertNotNull($transfer);
        $this->assertEquals('draft', $transfer->status);
        $this->assertCount(1, $transfer->items);

        // 3. View show page
        $responseShow = $this->actingAs($this->adminUser)->get(route('stock-transfers.show', $transfer));
        $responseShow->assertStatus(200);
        $responseShow->assertSee($transfer->transfer_number);

        // 4. Approve transfer
        $responseApprove = $this->actingAs($this->adminUser)->post(route('stock-transfers.approve', $transfer));
        $responseApprove->assertSessionHas('success');
        $this->assertEquals('approved', $transfer->fresh()->status);

        // 5. Receive transfer (at branch warehouse)
        $item = $transfer->items->first();
        $responseReceive = $this->actingAs($this->adminUser)->post(route('stock-transfers.receive', $transfer), [
            'received_qty' => [
                $item->id => 30,
            ],
        ]);
        $responseReceive->assertSessionHas('success');

        $transferFresh = $transfer->fresh();
        $this->assertEquals('received', $transferFresh->status);
        $this->assertNotNull($transferFresh->received_at);

        // 6. Verify stocks updated
        $sourceStock = Stock::where('warehouse_id', $this->mainWarehouse->id)
            ->where('product_id', $product->id)
            ->where('batch_id', $batch->id)
            ->first();
        $this->assertEquals(70, (float) $sourceStock->quantity); // 100 - 30

        $destStock = Stock::where('warehouse_id', $this->branchWarehouse->id)
            ->where('product_id', $product->id)
            ->where('batch_id', $batch->id)
            ->first();
        $this->assertEquals(30, (float) $destStock->quantity);

        // 7. Verify mutations recorded
        $this->assertDatabaseHas('stock_mutations', [
            'product_id' => $product->id,
            'warehouse_id' => $this->mainWarehouse->id,
            'mutation_type' => 'transfer_out',
            'quantity' => 30,
        ]);

        $this->assertDatabaseHas('stock_mutations', [
            'product_id' => $product->id,
            'warehouse_id' => $this->branchWarehouse->id,
            'mutation_type' => 'transfer_in',
            'quantity' => 30,
        ]);

        // 8. Verify Surat Jalan PDF can be generated
        $responsePdf = $this->actingAs($this->adminUser)->get(route('stock-transfers.pdf', $transfer));
        $responsePdf->assertStatus(200);
        $this->assertEquals('application/pdf', $responsePdf->headers->get('content-type'));
    }
}
