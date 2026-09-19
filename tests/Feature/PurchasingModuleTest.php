<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\StorageLocation;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InvoiceNumberService;
use App\Services\PurchaseService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchasingModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Warehouse $warehouse;

    protected Supplier $supplier;

    protected Product $product;

    protected Unit $unit;

    protected StorageLocation $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->user = User::factory()->create();
        $this->user->assignRole('Pemilik');

        $this->warehouse = Warehouse::create([
            'name' => 'Gudang Sentral',
            'address' => 'Jl. Obat No. 1',
            'type' => 'main',
            'is_active' => true,
        ]);

        $this->location = StorageLocation::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'RAK-B1',
            'name' => 'Rak Obat Kering B1',
            'type' => 'rak',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::factory()->create();
        $this->product = Product::factory()->create();
        $this->unit = Unit::factory()->create();
    }

    public function test_invoice_number_service_generates_formatted_numbers(): void
    {
        $service = app(InvoiceNumberService::class);

        $poNumber = $service->generatePoNumber();
        $this->assertStringStartsWith('PO-'.now()->format('Ymd'), $poNumber);

        $grNumber = $service->generateReceiptNumber();
        $this->assertStringStartsWith('GR-'.now()->format('Ymd'), $grNumber);

        $invNumber = $service->generateInvoiceNumber();
        $this->assertStringStartsWith('INV-'.now()->format('Ymd'), $invNumber);
    }

    public function test_can_create_po_via_web_route(): void
    {
        $response = $this->actingAs($this->user)->post(route('purchase-orders.store'), [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'discount_amount' => 10000,
            'tax_amount' => 0,
            'notes' => 'Pengiriman hari senin',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'unit_id' => $this->unit->id,
                    'quantity_ordered' => 20,
                    'unit_price' => 15000,
                    'discount' => 0,
                    'notes' => 'Item 1',
                ],
            ],
        ]);

        $po = PurchaseOrder::latest('id')->first();
        $this->assertNotNull($po);
        $response->assertRedirect(route('purchase-orders.show', $po));

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'subtotal' => 300000,
            'grand_total' => 290000,
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'po_id' => $po->id,
            'product_id' => $this->product->id,
            'quantity_ordered' => 20,
        ]);
    }

    public function test_can_approve_purchase_order(): void
    {
        $po = app(PurchaseService::class)->createPO(
            data: [
                'supplier_id' => $this->supplier->id,
                'warehouse_id' => $this->warehouse->id,
            ],
            items: [
                [
                    'product_id' => $this->product->id,
                    'unit_id' => $this->unit->id,
                    'quantity_ordered' => 5,
                    'unit_price' => 10000,
                ],
            ],
            userId: $this->user->id
        );

        $this->assertEquals('pending_approval', $po->status);

        $response = $this->actingAs($this->user)->post(route('purchase-orders.approve', $po));
        $response->assertRedirect(route('purchase-orders.show', $po));

        $po->refresh();
        $this->assertEquals('approved', $po->status);
        $this->assertEquals($this->user->id, $po->approved_by);
        $this->assertNotNull($po->approved_at);
    }

    public function test_can_receive_goods_and_update_batch_stock_and_mutation(): void
    {
        $po = app(PurchaseService::class)->createPO(
            data: [
                'supplier_id' => $this->supplier->id,
                'warehouse_id' => $this->warehouse->id,
            ],
            items: [
                [
                    'product_id' => $this->product->id,
                    'unit_id' => $this->unit->id,
                    'quantity_ordered' => 10,
                    'unit_price' => 20000,
                ],
            ],
            userId: $this->user->id
        );

        $poItem = $po->items->first();

        $response = $this->actingAs($this->user)->post(route('goods-receipts.store'), [
            'po_id' => $po->id,
            'receipt_date' => now()->toDateString(),
            'delivery_note_number' => 'SJ-DIST-0099',
            'notes' => 'Barang diterima lengkap & rapi',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'product_id' => $this->product->id,
                    'batch_number' => 'BATCH-EXP-001',
                    'expiry_date' => now()->addYears(2)->toDateString(),
                    'quantity_received' => 10,
                    'storage_location_id' => $this->location->id,
                    'condition' => 'good',
                ],
            ],
        ]);

        $response->assertSessionHas('success');

        // Verify Batch was created
        $batch = Batch::where('batch_number', 'BATCH-EXP-001')->first();
        $this->assertNotNull($batch);
        $this->assertEquals(10, $batch->current_qty);
        $this->assertEquals('active', $batch->status);

        // Verify Stock was created / incremented
        $stock = Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertNotNull($stock);
        $this->assertEquals(10, $stock->quantity);
        $this->assertEquals(10, $stock->available_qty);

        // Verify StockMutation was created
        $mutation = StockMutation::where('product_id', $this->product->id)
            ->where('mutation_type', 'in')
            ->first();
        $this->assertNotNull($mutation);
        $this->assertEquals(10, $mutation->quantity);

        // Verify PO status was updated to received
        $po->refresh();
        $this->assertEquals('received', $po->status);
    }

    public function test_can_create_invoice_and_process_payments(): void
    {
        $response = $this->actingAs($this->user)->post(route('purchase-invoices.store'), [
            'invoice_number' => 'INV-SUPP-999',
            'supplier_id' => $this->supplier->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'total_amount' => 500000,
            'notes' => 'Faktur tempo 30 hari',
        ]);

        $invoice = PurchaseInvoice::where('invoice_number', 'INV-SUPP-999')->first();
        $this->assertNotNull($invoice);
        $response->assertRedirect(route('purchase-invoices.show', $invoice));

        // 1. Partial payment
        $this->actingAs($this->user)->post(route('purchase-invoices.payments.store', $invoice), [
            'payment_date' => now()->toDateString(),
            'amount' => 200000,
            'payment_method' => 'transfer',
            'reference_number' => 'TRF-001',
        ]);

        $invoice->refresh();
        $this->assertEquals(200000, $invoice->paid_amount);
        $this->assertEquals(300000, $invoice->remaining_amount);
        $this->assertEquals('partial', $invoice->payment_status);

        // 2. Full payment
        $this->actingAs($this->user)->post(route('purchase-invoices.payments.store', $invoice), [
            'payment_date' => now()->toDateString(),
            'amount' => 300000,
            'payment_method' => 'transfer',
            'reference_number' => 'TRF-002',
        ]);

        $invoice->refresh();
        $this->assertEquals(500000, $invoice->paid_amount);
        $this->assertEquals(0, $invoice->remaining_amount);
        $this->assertEquals('paid', $invoice->payment_status);
    }

    public function test_can_download_purchase_order_pdf(): void
    {
        $po = app(PurchaseService::class)->createPO(
            data: [
                'supplier_id' => $this->supplier->id,
                'warehouse_id' => $this->warehouse->id,
            ],
            items: [
                [
                    'product_id' => $this->product->id,
                    'unit_id' => $this->unit->id,
                    'quantity_ordered' => 5,
                    'unit_price' => 10000,
                ],
            ],
            userId: $this->user->id
        );

        $response = $this->actingAs($this->user)->get(route('purchase-orders.print-pdf', $po));
        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}
