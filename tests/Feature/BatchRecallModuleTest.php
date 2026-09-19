<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\BatchRecall;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchRecallModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Warehouse $warehouse;

    protected Unit $unit;

    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Pemilik');

        $this->warehouse = Warehouse::create([
            'code' => 'GUD-01',
            'name' => 'Gudang Utama Farmasi',
            'is_active' => true,
        ]);

        $this->unit = Unit::factory()->create(['name' => 'Botol']);
        $this->supplier = Supplier::factory()->create(['name' => 'PT Kalbe Farma']);
    }

    public function test_can_view_batch_recall_index_and_create_pages(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('batch-recalls.index'));
        $response->assertStatus(200);
        $response->assertViewIs('batch-recalls.index');
        $response->assertSee('Penarikan Obat (Batch Recall)');

        $responseCreate = $this->actingAs($this->adminUser)->get(route('batch-recalls.create'));
        $responseCreate->assertStatus(200);
        $responseCreate->assertViewIs('batch-recalls.create');
        $responseCreate->assertSee('Inisiasi Penarikan Obat');
    }

    public function test_can_initiate_recall_and_auto_quarantine_batch_and_stock(): void
    {
        $product = Product::factory()->create(['name' => 'Sirup Paracetamol 60ml']);
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $this->unit->id,
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'purchase_price' => 15000,
            'selling_price' => 22000,
        ]);

        $batch = Batch::factory()->create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-RECALL-TEST-01',
            'supplier_id' => $this->supplier->id,
            'status' => 'active',
        ]);

        $stock = Stock::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_id' => $batch->id,
            'quantity' => 50,
            'reserved_qty' => 0,
            'available_qty' => 50,
            'status' => 'available',
        ]);

        $storeData = [
            'batch_id' => $batch->id,
            'recall_source' => 'BPOM',
            'recall_reason' => 'Surat Edaran BPOM terkait cemaran etilen glikol',
            'recall_date' => now()->toDateString(),
            'action_taken' => 'Stok dibekukan dan dipindahkan ke ruang karantina',
            'notes' => 'Segera konfirmasi distributor Kalbe',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('batch-recalls.store'), $storeData);
        $response->assertSessionHas('success');

        $recall = BatchRecall::where('batch_id', $batch->id)->first();
        $this->assertNotNull($recall);
        $this->assertEquals('quarantined', $recall->status);
        $this->assertEquals(50, (float) $recall->affected_qty);
        $this->assertEquals(0, (float) $recall->sold_qty);

        // Verify Auto-Quarantine of batch and physical stocks
        $this->assertEquals('recalled', $batch->fresh()->status);
        $this->assertEquals('quarantined', $stock->fresh()->status);
        $this->assertEquals(0, (float) $stock->fresh()->available_qty);
    }

    public function test_identifies_affected_sales_transactions_and_customers(): void
    {
        $product = Product::factory()->create(['name' => 'Antibiotik Kapsul 500mg']);
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $this->unit->id,
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'purchase_price' => 20000,
            'selling_price' => 30000,
        ]);

        $batch = Batch::factory()->create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-SOLD-01',
            'supplier_id' => $this->supplier->id,
            'status' => 'active',
        ]);

        Stock::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_id' => $batch->id,
            'quantity' => 15,
            'reserved_qty' => 0,
            'available_qty' => 15,
            'status' => 'available',
        ]);

        $customer = Customer::factory()->create([
            'name' => 'Siti Nurhaliza',
            'phone' => '081234567890',
            'address' => 'Jl. Mawar No. 12',
        ]);

        $sale = Sale::create([
            'invoice_number' => 'POS-202609-RCL01',
            'warehouse_id' => $this->warehouse->id,
            'cashier_id' => $this->adminUser->id,
            'customer_id' => $customer->id,
            'subtotal' => 60000,
            'grand_total' => 60000,
            'status' => 'completed',
            'payment_status' => 'paid',
            'sale_date' => now(),
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'unit_id' => $this->unit->id,
            'quantity' => 2,
            'unit_price' => 30000,
            'subtotal' => 60000,
            'total_price' => 60000,
        ]);

        // Initiate recall
        $this->actingAs($this->adminUser)->post(route('batch-recalls.store'), [
            'batch_id' => $batch->id,
            'recall_source' => 'Produsen',
            'recall_reason' => 'Cacat cetak label dosis obat',
            'recall_date' => now()->toDateString(),
        ]);

        $recall = BatchRecall::where('batch_id', $batch->id)->first();
        $this->assertNotNull($recall);
        $this->assertEquals(2, (float) $recall->sold_qty);
        $this->assertEquals(17, (float) $recall->affected_qty); // 15 stock + 2 sold

        // View recall show page
        $response = $this->actingAs($this->adminUser)->get(route('batch-recalls.show', $recall));
        $response->assertStatus(200);
        $response->assertSee('Siti Nurhaliza');
        $response->assertSee('081234567890');
        $response->assertSee('POS-202609-RCL01');
    }

    public function test_can_update_recovery_progress_and_complete_recall(): void
    {
        $product = Product::factory()->create();
        $batch = Batch::factory()->create(['product_id' => $product->id]);

        $recall = BatchRecall::create([
            'batch_id' => $batch->id,
            'recall_number' => 'RCL-20260916-0001',
            'recall_source' => 'BPOM',
            'recall_reason' => 'Uji stabilitas tidak memenuhi syarat',
            'recall_date' => now()->toDateString(),
            'affected_qty' => 100,
            'sold_qty' => 50,
            'recovered_qty' => 0,
            'status' => 'quarantined',
            'created_by' => $this->adminUser->id,
        ]);

        // 1. Update progress
        $responseUpdate = $this->actingAs($this->adminUser)->post(route('batch-recalls.update-progress', $recall), [
            'recovered_qty' => 35,
            'status' => 'in_progress',
            'action_taken' => '35 unit obat berhasil ditarik dari peredaran pasien',
            'notes' => 'Sisa 15 unit dikonfirmasi sudah habis dikonsumsi',
        ]);
        $responseUpdate->assertSessionHas('success');

        $this->assertEquals(35, (float) $recall->fresh()->recovered_qty);
        $this->assertEquals('in_progress', $recall->fresh()->status);
        $this->assertEquals(70.0, $recall->fresh()->recovery_percentage);

        // 2. Complete recall
        $responseComplete = $this->actingAs($this->adminUser)->post(route('batch-recalls.complete', $recall), [
            'action_taken' => 'Seluruh obat karantina diserahkan ke distributor dengan berita acara pemusnahan',
        ]);
        $responseComplete->assertSessionHas('success');
        $this->assertEquals('completed', $recall->fresh()->status);
    }

    public function test_can_generate_batch_recall_pdf(): void
    {
        $product = Product::factory()->create(['name' => 'Vitamin C 500mg']);
        $batch = Batch::factory()->create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-VIT-001',
        ]);

        $recall = BatchRecall::create([
            'batch_id' => $batch->id,
            'recall_number' => 'RCL-20260916-0099',
            'recall_source' => 'Internal',
            'recall_reason' => 'Perubahan warna tablet pada kemasan strip',
            'recall_date' => now()->toDateString(),
            'affected_qty' => 20,
            'sold_qty' => 5,
            'recovered_qty' => 5,
            'status' => 'completed',
            'action_taken' => 'Obat telah ditarik dan dimusnahkan',
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('batch-recalls.pdf', $recall));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }
}
