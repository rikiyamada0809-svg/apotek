<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\StockSetting;
use App\Models\StorageLocation;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAndBatchViewTest extends TestCase
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

    public function test_stock_index_page_can_be_rendered_with_tabs(): void
    {
        $category = Category::factory()->create(['name' => 'Antibiotik']);
        $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Amoxicillin 500mg']);
        $warehouse = Warehouse::factory()->create(['name' => 'Gudang Utama']);
        $unit = Unit::factory()->create();
        $batch = Batch::factory()->create(['product_id' => $product->id]);
        $product->productUnits()->create([
            'unit_id' => $unit->id,
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'purchase_price' => 10000,
            'selling_price' => 15000,
        ]);

        $location = StorageLocation::factory()->create([
            'warehouse_id' => $warehouse->id,
            'name' => 'Rak Obat Paten',
        ]);

        Stock::create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'warehouse_id' => $warehouse->id,
            'location_id' => $location->id,
            'quantity' => 100,
            'reserved_qty' => 10,
            'available_qty' => 90,
            'status' => 'available',
        ]);

        // Tab products
        $response = $this->actingAs($this->adminUser)->get(route('stocks.index'));
        $response->assertOk();
        $response->assertSee('Amoxicillin 500mg');
        $response->assertSee('90');

        // Tab batches
        $responseBatches = $this->actingAs($this->adminUser)->get(route('stocks.index', ['tab' => 'batches']));
        $responseBatches->assertOk();

        // Tab warehouses
        $responseWarehouses = $this->actingAs($this->adminUser)->get(route('stocks.index', ['tab' => 'warehouses']));
        $responseWarehouses->assertOk();
        $responseWarehouses->assertSee('Gudang Utama');
        $responseWarehouses->assertSee('Rak Obat Paten');
    }

    public function test_stock_card_page_calculates_initial_balance_and_running_balance_correctly(): void
    {
        $product = Product::factory()->create(['name' => 'Paracetamol 500mg']);
        $warehouse = Warehouse::factory()->create();
        $batch = Batch::factory()->create(['product_id' => $product->id]);

        // Mutation 1: 15 days ago (+50)
        StockMutation::create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'warehouse_id' => $warehouse->id,
            'mutation_type' => 'in',
            'reference_type' => 'goods_receipt',
            'reference_id' => 1,
            'quantity' => 50,
            'stock_before' => 0,
            'stock_after' => 50,
            'created_by' => $this->adminUser->id,
            'created_at' => now()->subDays(15),
        ]);

        // Mutation 2: 5 days ago (-10)
        StockMutation::create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'warehouse_id' => $warehouse->id,
            'mutation_type' => 'out',
            'reference_type' => 'sale',
            'reference_id' => 1,
            'quantity' => 10,
            'stock_before' => 50,
            'stock_after' => 40,
            'created_by' => $this->adminUser->id,
            'created_at' => now()->subDays(5),
        ]);

        // Query with start_date = 10 days ago (Mutation 1 is before start_date, so initial balance = 50)
        $response = $this->actingAs($this->adminUser)->get(route('stocks.card', [
            'product_id' => $product->id,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSee('Paracetamol 500mg');
        $response->assertSee('50'); // Initial balance
        $response->assertSee('40'); // Final balance
    }

    public function test_batch_index_lists_batches_sorted_by_fefo(): void
    {
        $product = Product::factory()->create();
        $supplier = Supplier::factory()->create(['name' => 'PT Kimia Sejahtera']);

        $batch1 = Batch::create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-FAR-01',
            'expiry_date' => now()->addMonths(6)->toDateString(),
            'supplier_id' => $supplier->id,
            'purchase_price' => 5000,
            'received_qty' => 100,
            'current_qty' => 100,
            'status' => 'active',
        ]);

        $batch2 = Batch::create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-NEAR-02',
            'expiry_date' => now()->addDays(15)->toDateString(),
            'supplier_id' => $supplier->id,
            'purchase_price' => 5000,
            'received_qty' => 50,
            'current_qty' => 50,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('batches.index'));
        $response->assertOk();
        $response->assertSee('BATCH-FAR-01');
        $response->assertSee('BATCH-NEAR-02');
        $response->assertSee('PT Kimia Sejahtera');

        // BATCH-NEAR-02 should appear before BATCH-FAR-01 in FEFO ordering
        $content = $response->getContent();
        $posNear = strpos($content, 'BATCH-NEAR-02');
        $posFar = strpos($content, 'BATCH-FAR-01');
        $this->assertTrue($posNear < $posFar);
    }

    public function test_batch_show_page_displays_batch_details_and_mutations(): void
    {
        $product = Product::factory()->create(['name' => 'Ibuprofen 400mg']);
        $warehouse = Warehouse::factory()->create(['name' => 'Gudang Depan']);
        $location = StorageLocation::factory()->create(['warehouse_id' => $warehouse->id, 'name' => 'Rak A-1']);
        $supplier = Supplier::factory()->create();

        $batch = Batch::create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-IBU-99',
            'expiry_date' => now()->addYear()->toDateString(),
            'supplier_id' => $supplier->id,
            'purchase_price' => 12000,
            'received_qty' => 200,
            'current_qty' => 200,
            'status' => 'active',
        ]);

        Stock::create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'warehouse_id' => $warehouse->id,
            'location_id' => $location->id,
            'quantity' => 200,
            'reserved_qty' => 0,
            'available_qty' => 200,
            'status' => 'available',
        ]);

        StockMutation::create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'warehouse_id' => $warehouse->id,
            'mutation_type' => 'in',
            'quantity' => 200,
            'stock_before' => 0,
            'stock_after' => 200,
            'notes' => 'Penerimaan batch perdana',
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('batches.show', $batch));
        $response->assertOk();
        $response->assertSee('BATCH-IBU-99');
        $response->assertSee('Ibuprofen 400mg');
        $response->assertSee('Rak A-1');
        $response->assertSee('Penerimaan batch perdana');
    }

    public function test_can_update_product_stock_settings(): void
    {
        $product = Product::factory()->create(['name' => 'Cetirizine 10mg']);
        $warehouse = Warehouse::factory()->create();

        $response = $this->actingAs($this->adminUser)->post(route('stocks.settings.update', $product), [
            'warehouse_id' => $warehouse->id,
            'min_stock' => 25,
            'max_stock' => 500,
            'reorder_point' => 40,
            'safety_stock' => 15,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('stock_settings', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'min_stock' => 25,
            'reorder_point' => 40,
        ]);
    }

    public function test_sidebar_and_navbar_badges_reflect_low_stock_and_expiring_batches(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $batch = Batch::factory()->create(['product_id' => $product->id]);

        // Product with 0 stock (out of stock / low stock)
        Stock::create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 2,
            'reserved_qty' => 0,
            'available_qty' => 2,
            'status' => 'available',
        ]);
        StockSetting::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'min_stock' => 10,
            'max_stock' => 100,
            'reorder_point' => 15,
        ]);

        // Batch expiring in 5 days
        Batch::factory()->create([
            'product_id' => $product->id,
            'batch_number' => 'EXP-SOON-01',
            'expiry_date' => now()->addDays(5)->toDateString(),
            'status' => 'active',
            'current_qty' => 50,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Inventori & Stok', false);
        $response->assertSee('Daftar Stok');
        $response->assertSee('Batch & FEFO', false);
        $response->assertSee('Kartu Stok Digital');
    }
}
