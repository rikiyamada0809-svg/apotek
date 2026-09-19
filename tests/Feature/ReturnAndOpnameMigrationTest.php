<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\StorageLocation;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnAndOpnameMigrationTest extends TestCase
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

    public function test_purchase_return_and_items_can_be_created_with_relationships(): void
    {
        $supplier = Supplier::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $po = PurchaseOrder::create([
            'po_number' => 'PO-20260916-0001',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'order_date' => now()->toDateString(),
            'status' => 'approved',
            'subtotal' => 1000000,
            'discount_amount' => 0,
            'tax_amount' => 110000,
            'grand_total' => 1110000,
            'created_by' => $this->adminUser->id,
        ]);

        $product = Product::factory()->create();
        $batch = Batch::factory()->create(['product_id' => $product->id]);

        $return = PurchaseReturn::create([
            'return_number' => 'RTP-20260916-0001',
            'po_id' => $po->id,
            'supplier_id' => $supplier->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Barang rusak kemasan',
            'status' => 'approved',
            'resolution_type' => 'replacement',
            'total_amount' => 250000,
            'notes' => 'Tukar barang baru',
            'approved_by' => $this->adminUser->id,
            'created_by' => $this->adminUser->id,
        ]);

        $item = PurchaseReturnItem::create([
            'return_id' => $return->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'quantity' => 10,
            'unit_price' => 25000,
            'subtotal' => 250000,
            'reason' => 'Botol pecah',
        ]);

        $this->assertDatabaseHas('purchase_returns', [
            'return_number' => 'RTP-20260916-0001',
            'po_id' => $po->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('purchase_return_items', [
            'return_id' => $return->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'quantity' => 10,
        ]);

        $this->assertEquals($po->id, $return->purchaseOrder->id);
        $this->assertEquals($supplier->id, $return->supplier->id);
        $this->assertEquals(1, $return->items->count());
        $this->assertEquals($product->id, $item->product->id);
        $this->assertEquals($batch->id, $item->batch->id);
        $this->assertTrue($po->returns->contains($return));
    }

    public function test_sale_return_and_items_can_be_created_with_relationships(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $batch = Batch::factory()->create(['product_id' => $product->id]);
        $unit = Unit::factory()->create();

        $sale = Sale::create([
            'invoice_number' => 'INV-20260916-0001',
            'sale_date' => now(),
            'customer_id' => $customer->id,
            'cashier_id' => $this->adminUser->id,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 100000,
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);

        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'unit_id' => $unit->id,
            'quantity' => 2,
            'unit_price' => 50000,
            'subtotal' => 100000,
        ]);

        $saleReturn = SaleReturn::create([
            'return_number' => 'RTS-20260916-0001',
            'original_sale_id' => $sale->id,
            'customer_id' => $customer->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Pasien salah beli obat',
            'status' => 'completed',
            'resolution_type' => 'refund',
            'refund_amount' => 50000,
            'refund_method' => 'cash',
            'processed_by' => $this->adminUser->id,
            'approved_by' => $this->adminUser->id,
        ]);

        $saleReturnItem = SaleReturnItem::create([
            'return_id' => $saleReturn->id,
            'original_sale_item_id' => $saleItem->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
            'item_condition' => 'good',
            'return_to_stock' => true,
        ]);

        $this->assertDatabaseHas('sale_returns', [
            'return_number' => 'RTS-20260916-0001',
            'original_sale_id' => $sale->id,
            'refund_amount' => 50000,
        ]);

        $this->assertDatabaseHas('sale_return_items', [
            'return_id' => $saleReturn->id,
            'original_sale_item_id' => $saleItem->id,
            'return_to_stock' => true,
        ]);

        $this->assertEquals($sale->id, $saleReturn->originalSale->id);
        $this->assertEquals($customer->id, $saleReturn->customer->id);
        $this->assertEquals(1, $saleReturn->items->count());
        $this->assertEquals($saleItem->id, $saleReturnItem->originalSaleItem->id);
        $this->assertTrue($sale->returns->contains($saleReturn));
    }

    public function test_stock_opname_and_items_can_be_created_with_discrepancy_calculation(): void
    {
        $warehouse = Warehouse::factory()->create();
        $location = StorageLocation::factory()->create(['warehouse_id' => $warehouse->id]);
        $product = Product::factory()->create();
        $batch = Batch::factory()->create(['product_id' => $product->id]);

        $opname = StockOpname::create([
            'opname_number' => 'SO-20260916-0001',
            'warehouse_id' => $warehouse->id,
            'opname_date' => now()->toDateString(),
            'scope' => 'full',
            'status' => 'in_progress',
            'total_items' => 1,
            'matched_items' => 0,
            'discrepancy_items' => 1,
            'initiated_by' => $this->adminUser->id,
        ]);

        $opnameItem = new StockOpnameItem([
            'opname_id' => $opname->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'location_id' => $location->id,
            'system_qty' => 100,
            'physical_qty' => 95,
            'discrepancy_reason' => 'Selisih 5 butir saat hitung fisik',
            'adjustment_approved' => false,
            'counted_by' => $this->adminUser->id,
            'counted_at' => now(),
        ]);
        $opnameItem->calculateDiscrepancy();
        $opnameItem->save();

        $this->assertDatabaseHas('stock_opnames', [
            'opname_number' => 'SO-20260916-0001',
            'warehouse_id' => $warehouse->id,
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('stock_opname_items', [
            'opname_id' => $opname->id,
            'system_qty' => 100,
            'physical_qty' => 95,
            'discrepancy' => -5,
        ]);

        $this->assertEquals(-5.0, (float) $opnameItem->discrepancy);
        $this->assertEquals($warehouse->id, $opname->warehouse->id);
        $this->assertEquals(1, $opname->items->count());
        $this->assertEquals($location->id, $opnameItem->storageLocation->id);
        $this->assertTrue($warehouse->stockOpnames->contains($opname));
    }
}
