<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\Shift;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected Warehouse $warehouse;

    protected Product $product;

    protected Unit $unit;

    protected Batch $batch;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->cashier = User::factory()->create();
        $this->cashier->assignRole('Kasir');

        $this->warehouse = Warehouse::create([
            'name' => 'Apotek Utama',
            'address' => 'Jl. Farmasi No. 10',
            'type' => 'main',
            'is_active' => true,
        ]);

        $this->unit = Unit::factory()->create(['name' => 'Strip']);
        $this->product = Product::factory()->create([
            'name' => 'Paracetamol 500mg',
            'is_active' => true,
        ]);

        ProductUnit::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'purchase_price' => 5000,
            'selling_price' => 8000,
        ]);

        $supplier = Supplier::factory()->create();

        // Create initial batch with stock
        $this->batch = Batch::create([
            'product_id' => $this->product->id,
            'batch_number' => 'BCH-PCT-001',
            'expiry_date' => now()->addYear()->toDateString(),
            'supplier_id' => $supplier->id,
            'purchase_price' => 5000,
            'received_qty' => 100,
            'current_qty' => 100,
            'status' => 'active',
        ]);

        Stock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_id' => $this->batch->id,
            'quantity' => 100,
            'reserved_qty' => 0,
            'available_qty' => 100,
            'status' => 'available',
        ]);

        $this->customer = Customer::factory()->create([
            'name' => 'Budi Santoso',
            'customer_type' => 'member',
            'discount_percentage' => 10,
        ]);
    }

    public function test_pos_redirects_to_open_shift_if_no_active_shift(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('pos.index'));
        $response->assertRedirect(route('shifts.open'));
    }

    public function test_can_open_and_view_shift(): void
    {
        $response = $this->actingAs($this->cashier)->post(route('shifts.store'), [
            'warehouse_id' => $this->warehouse->id,
            'opening_cash' => 150000,
            'notes' => 'Shift Pagi Kasir 1',
        ]);

        $response->assertRedirect(route('pos.index'));

        $shift = Shift::where('user_id', $this->cashier->id)->where('status', 'open')->first();
        $this->assertNotNull($shift);
        $this->assertEquals(150000, $shift->opening_cash);
        $this->assertEquals(150000, $shift->closing_cash_system);

        // Now POS index should be accessible
        $posResponse = $this->actingAs($this->cashier)->get(route('pos.index'));
        $posResponse->assertOk();
        $posResponse->assertSee('Paracetamol 500mg');
    }

    public function test_can_process_sale_with_fefo_stock_deduction(): void
    {
        // 1. Open shift
        $shift = Shift::create([
            'user_id' => $this->cashier->id,
            'warehouse_id' => $this->warehouse->id,
            'start_time' => now(),
            'opening_cash' => 100000,
            'closing_cash_system' => 100000,
            'status' => 'open',
        ]);

        // 2. Submit sale
        $response = $this->actingAs($this->cashier)->postJson(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'status' => 'completed',
            'discount_amount' => 1600, // 10% of 16.000
            'tax_amount' => 0,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 2,
                    'unit_price' => 8000,
                    'discount_per_item' => 0,
                    'batch_id' => $this->batch->id,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'cash',
                    'amount' => 14400,
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // Verify sale was created
        $sale = Sale::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(16000, $sale->subtotal);
        $this->assertEquals(14400, $sale->grand_total);
        $this->assertEquals('paid', $sale->payment_status);

        // Verify batch was deducted (100 - 2 = 98)
        $this->batch->refresh();
        $this->assertEquals(98, $this->batch->current_qty);

        // Verify stock was deducted (100 - 2 = 98)
        $stock = Stock::where('product_id', $this->product->id)->where('warehouse_id', $this->warehouse->id)->first();
        $this->assertEquals(98, $stock->quantity);
        $this->assertEquals(98, $stock->available_qty);

        // Verify mutation was logged
        $mutation = StockMutation::where('reference_type', 'sale')->where('reference_id', $sale->id)->first();
        $this->assertNotNull($mutation);
        $this->assertEquals('out', $mutation->mutation_type);
        $this->assertEquals(2, $mutation->quantity);

        // Verify shift cash totals updated
        $shift->refresh();
        $this->assertEquals(14400, $shift->total_sales_cash);
        $this->assertEquals(114400, $shift->closing_cash_system);
    }

    public function test_can_view_receipt_and_history(): void
    {
        $shift = Shift::create([
            'user_id' => $this->cashier->id,
            'warehouse_id' => $this->warehouse->id,
            'start_time' => now(),
            'opening_cash' => 50000,
            'status' => 'open',
        ]);

        $sale = Sale::create([
            'invoice_number' => 'POS-20260913-9001',
            'sale_date' => now(),
            'cashier_id' => $this->cashier->id,
            'subtotal' => 8000,
            'grand_total' => 8000,
            'status' => 'completed',
            'shift_id' => $shift->id,
        ]);

        $receiptResponse = $this->actingAs($this->cashier)->get(route('pos.receipt', $sale));
        $receiptResponse->assertOk();
        $receiptResponse->assertSee('POS-20260913-9001');

        $historyResponse = $this->actingAs($this->cashier)->get(route('pos.history'));
        $historyResponse->assertOk();
        $historyResponse->assertSee('POS-20260913-9001');
    }

    public function test_can_void_sale_and_restore_inventory(): void
    {
        $shift = Shift::create([
            'user_id' => $this->cashier->id,
            'warehouse_id' => $this->warehouse->id,
            'start_time' => now(),
            'opening_cash' => 100000,
            'closing_cash_system' => 100000,
            'status' => 'open',
        ]);

        // Process a sale of 5 items
        $this->actingAs($this->cashier)->postJson(route('pos.store'), [
            'status' => 'completed',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 5,
                    'unit_price' => 8000,
                    'batch_id' => $this->batch->id,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'cash',
                    'amount' => 40000,
                ],
            ],
        ]);

        $sale = Sale::latest('id')->first();

        $this->batch->refresh();
        $this->assertEquals(95, $this->batch->current_qty);

        // Now void the sale
        $voidResponse = $this->actingAs($this->cashier)->post(route('pos.void', $sale), [
            'reason' => 'Pelanggan salah bawa resep dokter',
        ]);

        $voidResponse->assertSessionHas('success');

        $sale->refresh();
        $this->assertEquals('voided', $sale->status);

        // Inventory must be restored back to 100
        $this->batch->refresh();
        $this->assertEquals(100, $this->batch->current_qty);

        $stock = Stock::where('product_id', $this->product->id)->first();
        $this->assertEquals(100, $stock->quantity);
    }

    public function test_can_record_petty_cash_and_close_shift(): void
    {
        $shift = Shift::create([
            'user_id' => $this->cashier->id,
            'warehouse_id' => $this->warehouse->id,
            'start_time' => now(),
            'opening_cash' => 200000,
            'total_sales_cash' => 50000,
            'closing_cash_system' => 250000,
            'status' => 'open',
        ]);

        // Record petty cash expense
        $expenseResponse = $this->actingAs($this->cashier)->post(route('shifts.expense', $shift), [
            'amount' => 15000,
            'description' => 'Beli air galon apotek',
        ]);

        $expenseResponse->assertSessionHas('success');
        $shift->refresh();
        $this->assertEquals(15000, $shift->total_expenses);
        $this->assertEquals(235000, $shift->closing_cash_system);

        // Close shift with physical cash count of 230000 (selisih -5000)
        $closeResponse = $this->actingAs($this->cashier)->post(route('shifts.close', $shift), [
            'closing_cash_actual' => 230000,
            'notes' => 'Selisih 5000 uang receh',
        ]);

        $closeResponse->assertRedirect(route('shifts.show', $shift));

        $shift->refresh();
        $this->assertEquals('closed', $shift->status);
        $this->assertEquals(230000, $shift->closing_cash_actual);
        $this->assertEquals(-5000, $shift->cash_difference);
        $this->assertNotNull($shift->end_time);
    }
}
