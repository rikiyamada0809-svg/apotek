<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnameModuleTest extends TestCase
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

    public function test_user_can_view_stock_opname_index(): void
    {
        $warehouse = Warehouse::factory()->create();
        StockOpname::factory()->create([
            'warehouse_id' => $warehouse->id,
            'initiated_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('stock-opnames.index'));

        $response->assertStatus(200);
        $response->assertViewIs('stock-opnames.index');
        $response->assertSee('Stok Opname');
    }

    public function test_user_can_view_stock_opname_create_page(): void
    {
        Warehouse::factory()->create();

        $response = $this->actingAs($this->adminUser)->get(route('stock-opnames.create'));

        $response->assertStatus(200);
        $response->assertViewIs('stock-opnames.create');
        $response->assertSee('Buat Stok Opname Baru');
    }

    public function test_user_can_create_stock_opname_with_full_scope_and_snapshot_items(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $batch1 = Batch::factory()->create(['product_id' => $product1->id]);

        // Create initial stock for product 1
        Stock::create([
            'product_id' => $product1->id,
            'batch_id' => $batch1->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 50,
            'available_qty' => 50,
            'reserved_qty' => 0,
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('stock-opnames.store'), [
            'warehouse_id' => $warehouse->id,
            'opname_date' => now()->toDateString(),
            'scope' => 'full',
            'freeze_stock' => 1,
            'notes' => 'Opname bulanan lengkap',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('stock_opnames', [
            'warehouse_id' => $warehouse->id,
            'scope' => 'full',
            'freeze_stock' => 1,
            'status' => 'draft',
        ]);

        $opname = StockOpname::where('warehouse_id', $warehouse->id)->first();
        $this->assertNotNull($opname);
        $this->assertTrue($opname->items()->count() >= 2);

        // Check snapshot for product 1 system_qty = 50
        $item1 = $opname->items()->where('product_id', $product1->id)->first();
        $this->assertNotNull($item1);
        $this->assertEquals(50, (float) $item1->system_qty);
    }

    public function test_user_can_start_stock_opname(): void
    {
        $warehouse = Warehouse::factory()->create();
        $opname = StockOpname::factory()->create([
            'warehouse_id' => $warehouse->id,
            'status' => 'draft',
            'initiated_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('stock-opnames.start', $opname));

        $response->assertRedirect(route('stock-opnames.count', $opname));

        $opname->refresh();
        $this->assertEquals('in_progress', $opname->status);
        $this->assertNotNull($opname->started_at);
    }

    public function test_user_can_count_and_save_physical_qty_with_discrepancies(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $opname = StockOpname::factory()->create([
            'warehouse_id' => $warehouse->id,
            'status' => 'in_progress',
            'initiated_by' => $this->adminUser->id,
        ]);

        $item = StockOpnameItem::create([
            'opname_id' => $opname->id,
            'product_id' => $product->id,
            'system_qty' => 50,
            'physical_qty' => 50,
            'discrepancy' => 0,
        ]);

        // Count physical as 45 (deficit of -5)
        $response = $this->actingAs($this->adminUser)->post(route('stock-opnames.count.update', $opname), [
            'items' => [
                [
                    'id' => $item->id,
                    'physical_qty' => 45,
                    'notes' => 'Fisik ditemukan 45 botol',
                ],
            ],
            'action' => 'save_draft',
        ]);

        $response->assertRedirect();

        $item->refresh();
        $this->assertEquals(45, (float) $item->physical_qty);
        $this->assertEquals(-5, (float) $item->discrepancy);
        $this->assertEquals($this->adminUser->id, $item->counted_by);

        $opname->refresh();
        $this->assertEquals(1, $opname->discrepancy_items);
    }

    public function test_user_can_submit_and_review_opname_with_reasons(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $opname = StockOpname::factory()->create([
            'warehouse_id' => $warehouse->id,
            'status' => 'in_progress',
            'initiated_by' => $this->adminUser->id,
        ]);

        $item = StockOpnameItem::create([
            'opname_id' => $opname->id,
            'product_id' => $product->id,
            'system_qty' => 50,
            'physical_qty' => 40,
            'discrepancy' => -10,
        ]);

        // Submit opname
        $submitResponse = $this->actingAs($this->adminUser)->post(route('stock-opnames.submit', $opname));
        $submitResponse->assertRedirect(route('stock-opnames.review', $opname));

        $opname->refresh();
        $this->assertEquals('submitted', $opname->status);

        // Review discrepancy
        $reviewResponse = $this->actingAs($this->adminUser)->post(route('stock-opnames.review.update', $opname), [
            'reasons' => [
                [
                    'id' => $item->id,
                    'reason' => 'Barang pecah saat pemindahan rak',
                ],
            ],
        ]);

        $reviewResponse->assertRedirect(route('stock-opnames.show', $opname));

        $opname->refresh();
        $this->assertEquals('reviewed', $opname->status);

        $item->refresh();
        $this->assertEquals('Barang pecah saat pemindahan rak', $item->discrepancy_reason);
    }

    public function test_manager_can_approve_stock_opname(): void
    {
        $warehouse = Warehouse::factory()->create();
        $opname = StockOpname::factory()->create([
            'warehouse_id' => $warehouse->id,
            'status' => 'reviewed',
            'initiated_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('stock-opnames.approve', $opname));

        $response->assertRedirect();

        $opname->refresh();
        $this->assertEquals('approved', $opname->status);
        $this->assertEquals($this->adminUser->id, $opname->approved_by);
        $this->assertNotNull($opname->approved_at);
    }

    public function test_user_can_complete_stock_opname_which_adjusts_stock_and_creates_mutations(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $batch = Batch::factory()->create([
            'product_id' => $product->id,
            'current_qty' => 20,
        ]);

        // Current warehouse stock: 20
        Stock::create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 20,
            'available_qty' => 20,
            'reserved_qty' => 0,
            'status' => 'available',
        ]);

        $opname = StockOpname::factory()->create([
            'warehouse_id' => $warehouse->id,
            'status' => 'approved',
            'initiated_by' => $this->adminUser->id,
            'approved_by' => $this->adminUser->id,
        ]);

        // Physical qty is 25 (surplus +5)
        StockOpnameItem::create([
            'opname_id' => $opname->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'system_qty' => 20,
            'physical_qty' => 25,
            'discrepancy' => 5,
            'discrepancy_reason' => 'Surplus penerimaan belum terhitung',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('stock-opnames.complete', $opname));

        $response->assertRedirect(route('stock-opnames.show', $opname));

        $opname->refresh();
        $this->assertEquals('completed', $opname->status);
        $this->assertNotNull($opname->completed_at);

        // Check stock updated to 25
        $stock = Stock::where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->first();
        $this->assertEquals(25, (float) $stock->quantity);

        // Check batch updated to 25
        $batch->refresh();
        $this->assertEquals(25, (float) $batch->current_qty);

        // Check StockMutation created
        $this->assertDatabaseHas('stock_mutations', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'mutation_type' => 'adjustment',
            'quantity' => 5,
            'reference_type' => 'stock_opname',
            'reference_id' => $opname->id,
        ]);
    }

    public function test_freeze_stock_opname_blocks_pos_sale_in_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $unit = Unit::factory()->create();
        $batch = Batch::factory()->create([
            'product_id' => $product->id,
            'current_qty' => 100,
            'expiry_date' => now()->addYear()->toDateString(),
        ]);

        Stock::create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 100,
            'available_qty' => 100,
            'reserved_qty' => 0,
            'status' => 'available',
        ]);

        // Active Stock Opname with freeze_stock = true
        StockOpname::factory()->create([
            'warehouse_id' => $warehouse->id,
            'status' => 'in_progress',
            'freeze_stock' => true,
        ]);

        // Open shift in this warehouse
        $shift = Shift::create([
            'user_id' => $this->adminUser->id,
            'warehouse_id' => $warehouse->id,
            'opening_cash' => 100000,
            'start_time' => now(),
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('pos.store'), [
            'status' => 'completed',
            'items' => [
                [
                    'product_id' => $product->id,
                    'unit_id' => $unit->id,
                    'quantity' => 1,
                    'unit_price' => 20000,
                    'batch_id' => $batch->id,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'cash',
                    'amount' => 20000,
                ],
            ],
        ]);

        // POS checkout should be blocked with 422
        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'Gudang saat ini sedang dibekukan untuk pelaksanaan Stok Opname aktif. Transaksi tidak dapat diproses.',
        ]);
    }

    public function test_user_can_print_pdf_of_stock_opname(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $opname = StockOpname::factory()->create([
            'warehouse_id' => $warehouse->id,
            'status' => 'completed',
            'initiated_by' => $this->adminUser->id,
        ]);

        StockOpnameItem::create([
            'opname_id' => $opname->id,
            'product_id' => $product->id,
            'system_qty' => 10,
            'physical_qty' => 10,
            'discrepancy' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('stock-opnames.print-pdf', $opname));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}
