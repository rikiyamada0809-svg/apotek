<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchasePayment;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Shift;
use App\Models\StorageLocation;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseAndSaleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Warehouse $warehouse;

    protected Supplier $supplier;

    protected Product $product;

    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->user = User::factory()->create();
        $this->user->assignRole('Pemilik');

        $this->warehouse = Warehouse::create([
            'name' => 'Gudang Utama',
            'address' => 'Jl. Farmasi No. 10',
            'type' => 'main',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::factory()->create();
        $this->product = Product::factory()->create();
        $this->unit = Unit::factory()->create();
    }

    public function test_can_create_purchase_order_and_items(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-20260913-0001',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => '2026-09-13',
            'expected_delivery_date' => '2026-09-20',
            'status' => 'pending_approval',
            'subtotal' => 500000,
            'discount_amount' => 50000,
            'tax_amount' => 49500,
            'grand_total' => 499500,
            'created_by' => $this->user->id,
        ]);

        $item = PurchaseOrderItem::create([
            'po_id' => $po->id,
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'quantity_ordered' => 10,
            'quantity_received' => 2,
            'unit_price' => 50000,
            'discount' => 5000,
            'subtotal' => 495000,
        ]);

        $this->assertDatabaseHas('purchase_orders', ['po_number' => 'PO-20260913-0001']);
        $this->assertDatabaseHas('purchase_order_items', ['po_id' => $po->id, 'product_id' => $this->product->id]);

        $this->assertCount(1, $po->items);
        $this->assertEquals(8, $item->outstanding_quantity);
        $this->assertEquals($this->supplier->id, $po->supplier->id);
        $this->assertEquals($this->warehouse->id, $po->warehouse->id);
        $this->assertTrue(PurchaseOrder::pendingApproval()->exists());
    }

    public function test_can_create_goods_receipt_and_items(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-20260913-0002',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => '2026-09-13',
            'status' => 'approved',
            'subtotal' => 200000,
            'grand_total' => 200000,
            'created_by' => $this->user->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'po_id' => $po->id,
            'product_id' => $this->product->id,
            'unit_id' => $this->unit->id,
            'quantity_ordered' => 10,
            'quantity_received' => 0,
            'unit_price' => 20000,
            'subtotal' => 200000,
        ]);

        $gr = GoodsReceipt::create([
            'receipt_number' => 'GR-20260913-0001',
            'po_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'receipt_date' => '2026-09-13',
            'delivery_note_number' => 'SJ-12345',
            'status' => 'verified',
            'received_by' => $this->user->id,
        ]);

        $location = StorageLocation::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'RAK-A1',
            'name' => 'Rak A1',
            'type' => 'rak',
            'is_active' => true,
        ]);

        $grItem = GoodsReceiptItem::create([
            'receipt_id' => $gr->id,
            'po_item_id' => $poItem->id,
            'product_id' => $this->product->id,
            'batch_number' => 'BCH-2026-001',
            'expiry_date' => '2028-09-13',
            'quantity_received' => 10,
            'storage_location_id' => $location->id,
            'condition' => 'good',
        ]);

        $this->assertDatabaseHas('goods_receipts', ['receipt_number' => 'GR-20260913-0001']);
        $this->assertDatabaseHas('goods_receipt_items', ['receipt_id' => $gr->id]);
        $this->assertEquals($po->id, $gr->purchaseOrder->id);
        $this->assertCount(1, $gr->items);
        $this->assertEquals($poItem->id, $grItem->purchaseOrderItem->id);
    }

    public function test_can_create_purchase_invoice_and_payments(): void
    {
        $invoice = PurchaseInvoice::create([
            'invoice_number' => 'INV-SUP-001',
            'supplier_id' => $this->supplier->id,
            'invoice_date' => '2026-09-13',
            'due_date' => '2026-10-13',
            'total_amount' => 1000000,
            'paid_amount' => 400000,
            'payment_status' => 'partial',
        ]);

        $payment = PurchasePayment::create([
            'invoice_id' => $invoice->id,
            'payment_date' => '2026-09-13',
            'amount' => 400000,
            'payment_method' => 'transfer',
            'reference_number' => 'TRF-00123',
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('purchase_invoices', ['invoice_number' => 'INV-SUP-001']);
        $this->assertDatabaseHas('purchase_payments', ['invoice_id' => $invoice->id]);
        $this->assertEquals(600000, $invoice->remaining_amount);
        $this->assertCount(1, $invoice->payments);
        $this->assertTrue(PurchaseInvoice::partial()->exists());
    }

    public function test_can_create_shift_and_cash_transactions(): void
    {
        $shift = Shift::create([
            'user_id' => $this->user->id,
            'warehouse_id' => $this->warehouse->id,
            'start_time' => now(),
            'opening_cash' => 200000,
            'status' => 'open',
        ]);

        $tx = CashTransaction::create([
            'shift_id' => $shift->id,
            'type' => 'expense',
            'amount' => 15000,
            'description' => 'Beli kantong plastik obat',
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('shifts', ['id' => $shift->id, 'status' => 'open']);
        $this->assertDatabaseHas('cash_transactions', ['shift_id' => $shift->id]);
        $this->assertCount(1, $shift->cashTransactions);
        $this->assertTrue(Shift::open()->exists());
    }

    public function test_can_create_sale_items_and_sale_payments(): void
    {
        $customer = Customer::factory()->create();

        $batch = Batch::create([
            'product_id' => $this->product->id,
            'batch_number' => 'BATCH-POS-01',
            'manufacturing_date' => '2025-01-01',
            'expiry_date' => '2027-01-01',
            'current_qty' => 50,
            'status' => 'active',
        ]);

        $shift = Shift::create([
            'user_id' => $this->user->id,
            'warehouse_id' => $this->warehouse->id,
            'start_time' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $sale = Sale::create([
            'invoice_number' => 'POS-20260913-0001',
            'sale_date' => now(),
            'customer_id' => $customer->id,
            'cashier_id' => $this->user->id,
            'subtotal' => 60000,
            'discount_amount' => 5000,
            'tax_amount' => 0,
            'grand_total' => 55000,
            'payment_status' => 'paid',
            'status' => 'completed',
            'shift_id' => $shift->id,
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'batch_id' => $batch->id,
            'unit_id' => $this->unit->id,
            'quantity' => 2,
            'unit_price' => 30000,
            'discount_per_item' => 5000,
            'subtotal' => 55000,
            'is_prescription' => false,
        ]);

        $payment = SalePayment::create([
            'sale_id' => $sale->id,
            'payment_method' => 'cash',
            'amount' => 55000,
            'payment_date' => now(),
        ]);

        $this->assertDatabaseHas('sales', ['invoice_number' => 'POS-20260913-0001']);
        $this->assertDatabaseHas('sale_items', ['sale_id' => $sale->id]);
        $this->assertDatabaseHas('sale_payments', ['sale_id' => $sale->id]);

        $this->assertCount(1, $sale->items);
        $this->assertCount(1, $sale->payments);
        $this->assertEquals($customer->id, $sale->customer->id);
        $this->assertEquals($this->user->id, $sale->cashier->id);
        $this->assertTrue(Sale::completed()->exists());
        $this->assertTrue(Sale::paid()->exists());
    }
}
