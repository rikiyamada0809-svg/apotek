<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Stock;
use App\Models\StockMutation;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseReturnModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Warehouse $warehouse;

    protected Supplier $supplier;

    protected Product $product;

    protected Batch $batch;

    protected PurchaseOrder $purchaseOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->user = User::factory()->create();
        $this->user->assignRole('Pemilik');

        $this->warehouse = Warehouse::create([
            'name' => 'Gudang Farmasi Utama',
            'address' => 'Jl. Apotek No. 1',
            'type' => 'main',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::factory()->create([
            'name' => 'PT Kimia Farma Trading',
        ]);

        $this->product = Product::factory()->create([
            'name' => 'Paracetamol 500mg',
        ]);

        $this->batch = Batch::create([
            'product_id' => $this->product->id,
            'batch_number' => 'BCH-2026-RET',
            'expiry_date' => now()->addMonths(12)->toDateString(),
            'current_qty' => 100,
        ]);

        Stock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_id' => $this->batch->id,
            'quantity' => 100,
        ]);

        $this->purchaseOrder = PurchaseOrder::create([
            'po_number' => 'PO-202609-001',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->toDateString(),
            'status' => 'approved',
            'subtotal' => 50000,
            'grand_total' => 50000,
            'created_by' => $this->user->id,
        ]);

        $unit = Unit::factory()->create();

        PurchaseOrderItem::create([
            'po_id' => $this->purchaseOrder->id,
            'product_id' => $this->product->id,
            'unit_id' => $unit->id,
            'quantity_ordered' => 10,
            'quantity_received' => 10,
            'unit_price' => 5000,
            'subtotal' => 50000,
        ]);
    }

    public function test_user_can_view_purchase_returns_index_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchase-returns.index'));
        $response->assertStatus(200);
        $response->assertSee('Retur Pembelian (Supplier)');
    }

    public function test_user_can_view_purchase_return_create_page_with_po(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchase-returns.create', ['po_id' => $this->purchaseOrder->id]));
        $response->assertStatus(200);
        $response->assertSee($this->purchaseOrder->po_number);
        $response->assertSee($this->product->name);
    }

    public function test_user_can_create_purchase_return_draft(): void
    {
        $payload = [
            'po_id' => $this->purchaseOrder->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Kemasan obat rusak saat diterima',
            'resolution_type' => 'credit_note',
            'notes' => 'Harap potong tagihan faktur berikutnya',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'batch_id' => $this->batch->id,
                    'quantity' => 5,
                    'unit_price' => 5000,
                    'reason' => 'Kemasan sobek',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('purchase-returns.store'), $payload);

        $return = PurchaseReturn::latest()->first();
        $this->assertNotNull($return);
        $this->assertEquals('draft', $return->status);
        $this->assertEquals(25000, $return->total_amount);
        $this->assertCount(1, $return->items);

        $response->assertRedirect(route('purchase-returns.show', $return));
    }

    public function test_user_can_approve_purchase_return(): void
    {
        $return = PurchaseReturn::create([
            'return_number' => 'PR-202609-001',
            'po_id' => $this->purchaseOrder->id,
            'supplier_id' => $this->supplier->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Rusak',
            'status' => 'draft',
            'resolution_type' => 'credit_note',
            'total_amount' => 25000,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('purchase-returns.approve', $return));
        $response->assertSessionHas('success');

        $return->refresh();
        $this->assertEquals('approved', $return->status);
        $this->assertEquals($this->user->id, $return->approved_by);
    }

    public function test_sending_purchase_return_deducts_stock_and_records_mutation(): void
    {
        $return = PurchaseReturn::create([
            'return_number' => 'PR-202609-002',
            'po_id' => $this->purchaseOrder->id,
            'supplier_id' => $this->supplier->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Mutu tidak sesuai',
            'status' => 'approved',
            'resolution_type' => 'credit_note',
            'total_amount' => 25000,
            'created_by' => $this->user->id,
            'approved_by' => $this->user->id,
        ]);

        PurchaseReturnItem::create([
            'return_id' => $return->id,
            'product_id' => $this->product->id,
            'batch_id' => $this->batch->id,
            'quantity' => 10,
            'unit_price' => 5000,
            'subtotal' => 50000,
        ]);

        $initialStock = Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->value('quantity');

        $this->assertEquals(100, $initialStock);

        $response = $this->actingAs($this->user)->post(route('purchase-returns.send', $return));
        $response->assertSessionHas('success');

        $return->refresh();
        $this->assertEquals('sent', $return->status);

        // Check stock deducted
        $newStock = Stock::where('product_id', $this->product->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->value('quantity');
        $this->assertEquals(90, $newStock);

        // Check batch deducted
        $this->batch->refresh();
        $this->assertEquals(90, $this->batch->current_qty);

        // Check stock mutation out recorded
        $mutation = StockMutation::where('reference_type', 'purchase_return')
            ->where('reference_id', $return->id)
            ->first();
        $this->assertNotNull($mutation);
        $this->assertEquals('out', $mutation->mutation_type);
        $this->assertEquals(10, $mutation->quantity);
    }

    public function test_resolving_purchase_return_with_credit_note_reduces_supplier_invoice(): void
    {
        $invoice = PurchaseInvoice::create([
            'invoice_number' => 'INV-SUP-999',
            'supplier_id' => $this->supplier->id,
            'po_id' => $this->purchaseOrder->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'total_amount' => 100000,
            'paid_amount' => 0,
            'payment_status' => 'unpaid',
            'created_by' => $this->user->id,
        ]);

        $return = PurchaseReturn::create([
            'return_number' => 'PR-202609-003',
            'po_id' => $this->purchaseOrder->id,
            'supplier_id' => $this->supplier->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Barang Cacat',
            'status' => 'sent',
            'resolution_type' => 'credit_note',
            'total_amount' => 20000,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('purchase-returns.resolve', $return));
        $response->assertSessionHas('success');

        $return->refresh();
        $this->assertEquals('completed', $return->status);

        // Check invoice amount reduced
        $invoice->refresh();
        $this->assertEquals(80000, $invoice->total_amount);
    }

    public function test_user_can_generate_purchase_return_pdf(): void
    {
        $return = PurchaseReturn::create([
            'return_number' => 'PR-202609-PDF',
            'po_id' => $this->purchaseOrder->id,
            'supplier_id' => $this->supplier->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Test PDF',
            'status' => 'approved',
            'resolution_type' => 'replacement',
            'total_amount' => 10000,
            'created_by' => $this->user->id,
        ]);

        PurchaseReturnItem::create([
            'return_id' => $return->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 5000,
            'subtotal' => 10000,
        ]);

        $response = $this->actingAs($this->user)->get(route('purchase-returns.print-pdf', $return));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}
