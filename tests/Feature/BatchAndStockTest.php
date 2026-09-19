<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\StockSetting;
use App\Models\StorageLocation;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchAndStockTest extends TestCase
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

    public function test_can_create_batch_and_verify_relations(): void
    {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $batch = Batch::create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-ABC-001',
            'manufacturing_date' => '2025-01-01',
            'expiry_date' => '2027-01-01',
            'supplier_id' => $supplier->id,
            'purchase_price' => 25000,
            'received_qty' => 500,
            'current_qty' => 500,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('batches', [
            'batch_number' => 'BATCH-ABC-001',
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
        ]);

        $this->assertEquals($product->id, $batch->product->id);
        $this->assertEquals($supplier->id, $batch->supplier->id);
        $this->assertTrue($product->batches->contains($batch));
    }

    public function test_batch_scopes_and_fefo(): void
    {
        $product = Product::factory()->create();

        // 1. Batch expired
        $expiredBatch = Batch::factory()->create([
            'product_id' => $product->id,
            'batch_number' => 'EXP-01',
            'expiry_date' => now()->subDays(5)->toDateString(),
            'status' => 'expired',
            'current_qty' => 50,
        ]);

        // 2. Batch expiring soon (10 days)
        $nearExpiryBatch = Batch::factory()->create([
            'product_id' => $product->id,
            'batch_number' => 'NEAR-01',
            'expiry_date' => now()->addDays(10)->toDateString(),
            'status' => 'active',
            'current_qty' => 100,
        ]);

        // 3. Fresh batch (1 year)
        $freshBatch = Batch::factory()->create([
            'product_id' => $product->id,
            'batch_number' => 'FRESH-01',
            'expiry_date' => now()->addYear()->toDateString(),
            'status' => 'active',
            'current_qty' => 200,
        ]);

        $this->assertTrue($expiredBatch->isExpired());
        $this->assertFalse($freshBatch->isExpired());

        $this->assertTrue($nearExpiryBatch->isExpiringSoon(30));
        $this->assertFalse($freshBatch->isExpiringSoon(30));

        // Test Scope Expired
        $expiredList = Batch::expired()->pluck('id');
        $this->assertTrue($expiredList->contains($expiredBatch->id));
        $this->assertFalse($expiredList->contains($freshBatch->id));

        // Test Scope Expiring Soon
        $expiringSoonList = Batch::expiringSoon(30)->pluck('id');
        $this->assertTrue($expiringSoonList->contains($nearExpiryBatch->id));
        $this->assertFalse($expiringSoonList->contains($freshBatch->id));

        // Test FEFO Ordering
        $fefoOrdered = Batch::where('product_id', $product->id)->fefo()->pluck('batch_number')->toArray();
        $this->assertEquals(['EXP-01', 'NEAR-01', 'FRESH-01'], $fefoOrdered);
    }

    public function test_can_manage_stock_and_calculate_available(): void
    {
        $product = Product::factory()->create();
        $batch = Batch::factory()->create(['product_id' => $product->id]);
        $warehouse = Warehouse::factory()->create();
        $location = StorageLocation::factory()->create(['warehouse_id' => $warehouse->id]);

        $stock = Stock::create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'warehouse_id' => $warehouse->id,
            'location_id' => $location->id,
            'quantity' => 100,
            'reserved_qty' => 20,
            'available_qty' => 80,
            'status' => 'available',
        ]);

        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'location_id' => $location->id,
        ]);

        $this->assertEquals($location->id, $stock->storageLocation->id);
        $this->assertEquals($warehouse->id, $stock->warehouse->id);
        $this->assertEquals(80, $product->totalAvailableStock());

        // Test recalculate available
        $stock->reserved_qty = 50;
        $stock->recalculateAvailable();
        $stock->save();

        $this->assertEquals(50, $stock->fresh()->available_qty);
        $this->assertEquals('available', $stock->fresh()->status);

        // Test out of stock status
        $stock->reserved_qty = 100;
        $stock->recalculateAvailable();
        $stock->save();

        $this->assertEquals(0, $stock->fresh()->available_qty);
        $this->assertEquals('out_of_stock', $stock->fresh()->status);
    }

    public function test_can_create_stock_settings(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $setting = StockSetting::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'min_stock' => 20,
            'max_stock' => 1000,
            'reorder_point' => 100,
            'safety_stock' => 30,
            'lead_time_days' => 5,
            'abc_category' => 'A',
        ]);

        $this->assertDatabaseHas('stock_settings', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'abc_category' => 'A',
        ]);

        $this->assertEquals($product->id, $setting->product->id);
        $this->assertEquals($warehouse->id, $setting->warehouse->id);
    }

    public function test_can_record_stock_mutations(): void
    {
        $product = Product::factory()->create();
        $batch = Batch::factory()->create(['product_id' => $product->id]);
        $warehouse = Warehouse::factory()->create();

        $mutation = StockMutation::create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'warehouse_id' => $warehouse->id,
            'mutation_type' => 'in',
            'reference_type' => 'goods_receipt',
            'reference_id' => 101,
            'quantity' => 150,
            'stock_before' => 0,
            'stock_after' => 150,
            'notes' => 'Barang masuk penerimaan PO',
            'created_by' => $this->adminUser->id,
        ]);

        $this->assertDatabaseHas('stock_mutations', [
            'product_id' => $product->id,
            'mutation_type' => 'in',
            'quantity' => 150,
        ]);

        $this->assertEquals($this->adminUser->id, $mutation->creator->id);
        $this->assertEquals(1, StockMutation::ofType('in')->count());
        $this->assertEquals(0, StockMutation::ofType('out')->count());
    }
}
