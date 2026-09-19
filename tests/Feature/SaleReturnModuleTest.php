<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleReturnModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Warehouse $warehouse;

    protected Customer $customer;

    protected Product $product;

    protected Batch $batch;

    protected Sale $sale;

    protected SaleItem $saleItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->user = User::factory()->create();
        $this->user->assignRole('Pemilik');

        $this->warehouse = Warehouse::create([
            'name' => 'Apotek Kasir Depan',
            'address' => 'Jl. Apotek No. 1',
            'type' => 'main',
            'is_active' => true,
        ]);

        $this->customer = Customer::factory()->create([
            'name' => 'Budi Santoso',
        ]);

        $this->product = Product::factory()->create([
            'name' => 'Amoxicillin 500mg',
        ]);

        $this->batch = Batch::create([
            'product_id' => $this->product->id,
            'batch_number' => 'BCH-AMX-01',
            'expiry_date' => now()->addMonths(18)->toDateString(),
            'current_qty' => 40,
        ]);

        Stock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_id' => $this->batch->id,
            'quantity' => 40,
        ]);

        $this->sale = Sale::create([
            'invoice_number' => 'POS-202609-0001',
            'sale_date' => now()->subDays(2),
            'customer_id' => $this->customer->id,
            'cashier_id' => $this->user->id,
            'subtotal' => 35000,
            'grand_total' => 35000,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $unit = Unit::factory()->create();

        $this->saleItem = SaleItem::create([
            'sale_id' => $this->sale->id,
            'product_id' => $this->product->id,
            'batch_id' => $this->batch->id,
            'unit_id' => $unit->id,
            'quantity' => 10,
            'unit_price' => 3500,
            'subtotal' => 35000,
        ]);
    }

    public function test_user_can_view_sale_returns_index_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('sale-returns.index'));
        $response->assertStatus(200);
        $response->assertSee('Retur Penjualan Pasien / Konsumen');
    }

    public function test_user_can_view_sale_return_create_page_with_valid_sale(): void
    {
        $response = $this->actingAs($this->user)->get(route('sale-returns.create', ['sale_id' => $this->sale->id]));
        $response->assertStatus(200);
        $response->assertSee($this->sale->invoice_number);
        $response->assertSee($this->product->name);
    }

    public function test_cannot_create_sale_return_if_sale_is_older_than_seven_days(): void
    {
        // Update sale date to 10 days ago
        $this->sale->update(['sale_date' => now()->subDays(10)]);

        $payload = [
            'original_sale_id' => $this->sale->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Pasien salah beli',
            'resolution_type' => 'refund',
            'refund_method' => 'cash',
            'items' => [
                [
                    'original_sale_item_id' => $this->saleItem->id,
                    'product_id' => $this->product->id,
                    'batch_id' => $this->batch->id,
                    'quantity' => 2,
                    'unit_price' => 3500,
                    'item_condition' => 'good',
                    'return_to_stock' => true,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('sale-returns.store'), $payload);
        $response->assertSessionHas('error');
        $this->assertEquals(0, SaleReturn::count());
    }

    public function test_user_can_create_sale_return_draft(): void
    {
        $payload = [
            'original_sale_id' => $this->sale->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Pasien salah beli dosis obat',
            'resolution_type' => 'refund',
            'refund_method' => 'cash',
            'notes' => 'Kemasan obat masih tersegel rapi',
            'items' => [
                [
                    'original_sale_item_id' => $this->saleItem->id,
                    'product_id' => $this->product->id,
                    'batch_id' => $this->batch->id,
                    'quantity' => 4,
                    'unit_price' => 3500,
                    'item_condition' => 'good',
                    'return_to_stock' => true,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('sale-returns.store'), $payload);

        $return = SaleReturn::latest()->first();
        $this->assertNotNull($return);
        $this->assertEquals('draft', $return->status);
        $this->assertEquals(14000, $return->refund_amount);
        $this->assertCount(1, $return->items);

        $response->assertRedirect(route('sale-returns.show', $return));
    }

    public function test_completing_sale_return_restocks_good_condition_items_and_records_mutation(): void
    {
        $return = SaleReturn::create([
            'return_number' => 'SR-202609-001',
            'original_sale_id' => $this->sale->id,
            'customer_id' => $this->customer->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Pasien salah beli',
            'status' => 'draft',
            'resolution_type' => 'refund',
            'refund_method' => 'cash',
            'refund_amount' => 14000,
            'processed_by' => $this->user->id,
        ]);

        SaleReturnItem::create([
            'return_id' => $return->id,
            'product_id' => $this->product->id,
            'batch_id' => $this->batch->id,
            'quantity' => 4,
            'unit_price' => 3500,
            'subtotal' => 14000,
            'item_condition' => 'good',
            'return_to_stock' => true,
        ]);

        $initialStock = Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->value('quantity');
        $this->assertEquals(40, $initialStock);

        $response = $this->actingAs($this->user)->post(route('sale-returns.complete', $return));
        $response->assertSessionHas('success');

        $return->refresh();
        $this->assertEquals('completed', $return->status);

        // Check stock increased by 4
        $newStock = Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->value('quantity');
        $this->assertEquals(44, $newStock);

        // Check batch qty increased by 4
        $this->batch->refresh();
        $this->assertEquals(44, $this->batch->current_qty);

        // Check stock mutation in recorded
        $mutation = StockMutation::where('reference_type', 'sale_return')
            ->where('reference_id', $return->id)
            ->first();
        $this->assertNotNull($mutation);
        $this->assertEquals('in', $mutation->mutation_type);
        $this->assertEquals(4, $mutation->quantity);
    }

    public function test_completing_sale_return_with_damaged_condition_does_not_restock(): void
    {
        $return = SaleReturn::create([
            'return_number' => 'SR-202609-002',
            'original_sale_id' => $this->sale->id,
            'customer_id' => $this->customer->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Segel rusak dari pabrik',
            'status' => 'draft',
            'resolution_type' => 'replacement',
            'refund_amount' => 7000,
            'processed_by' => $this->user->id,
        ]);

        SaleReturnItem::create([
            'return_id' => $return->id,
            'product_id' => $this->product->id,
            'batch_id' => $this->batch->id,
            'quantity' => 2,
            'unit_price' => 3500,
            'subtotal' => 7000,
            'item_condition' => 'damaged',
            'return_to_stock' => false,
        ]);

        $initialStock = Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->value('quantity');

        $response = $this->actingAs($this->user)->post(route('sale-returns.complete', $return));
        $response->assertSessionHas('success');

        $return->refresh();
        $this->assertEquals('completed', $return->status);

        // Stock must remain unchanged
        $newStock = Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->value('quantity');
        $this->assertEquals($initialStock, $newStock);

        // No mutation created
        $mutation = StockMutation::where('reference_type', 'sale_return')
            ->where('reference_id', $return->id)
            ->first();
        $this->assertNull($mutation);
    }

    public function test_user_can_generate_sale_return_pdf(): void
    {
        $return = SaleReturn::create([
            'return_number' => 'SR-202609-PDF',
            'original_sale_id' => $this->sale->id,
            'customer_id' => $this->customer->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Test PDF',
            'status' => 'completed',
            'resolution_type' => 'refund',
            'refund_method' => 'cash',
            'refund_amount' => 3500,
            'processed_by' => $this->user->id,
        ]);

        SaleReturnItem::create([
            'return_id' => $return->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => 3500,
            'subtotal' => 3500,
            'item_condition' => 'good',
            'return_to_stock' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('sale-returns.print-pdf', $return));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}
