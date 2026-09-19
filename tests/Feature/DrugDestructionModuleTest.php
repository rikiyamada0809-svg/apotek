<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\DrugDestruction;
use App\Models\DrugDestructionItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DrugDestructionModuleTest extends TestCase
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

    public function test_user_can_view_drug_destruction_index(): void
    {
        DrugDestruction::factory()->create([
            'executed_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('drug-destructions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('drug-destructions.index');
        $response->assertSee('Pemusnahan Obat');
    }

    public function test_user_can_view_drug_destruction_create_page(): void
    {
        Warehouse::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->adminUser)->get(route('drug-destructions.create'));

        $response->assertStatus(200);
        $response->assertViewIs('drug-destructions.create');
        $response->assertSee('Buat Berita Acara Pemusnahan Obat');
    }

    public function test_user_can_store_drug_destruction_draft(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $batch = Batch::factory()->create([
            'product_id' => $product->id,
            'purchase_price' => 20000,
            'current_qty' => 50,
        ]);

        $payload = [
            'destruction_date' => now()->toDateString(),
            'witness_name' => 'Apt. Budi Santoso',
            'witness_role' => 'Saksi Apoteker Pendamping',
            'method' => 'Incinerator / Pembakaran',
            'notes' => 'Pemusnahan obat kadaluarsa triwulan 1',
            'items' => [
                [
                    'batch_id' => $batch->id,
                    'quantity' => 10,
                    'reason' => 'Kadaluarsa / Expired',
                    'notes' => 'Warna tablet pudar',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('drug-destructions.store'), $payload);

        $this->assertDatabaseHas('drug_destructions', [
            'witness_name' => 'Apt. Budi Santoso',
            'status' => 'draft',
            'total_loss_amount' => 200000, // 10 * 20000
        ]);

        $destruction = DrugDestruction::where('witness_name', 'Apt. Budi Santoso')->first();
        $this->assertNotNull($destruction);

        $this->assertDatabaseHas('drug_destruction_items', [
            'destruction_id' => $destruction->id,
            'batch_id' => $batch->id,
            'quantity' => 10,
            'loss_amount' => 200000,
        ]);

        $response->assertRedirect(route('drug-destructions.show', $destruction));
    }

    public function test_user_can_view_drug_destruction_detail(): void
    {
        $destruction = DrugDestruction::factory()->create([
            'executed_by' => $this->adminUser->id,
        ]);
        DrugDestructionItem::factory()->create([
            'destruction_id' => $destruction->id,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('drug-destructions.show', $destruction));

        $response->assertStatus(200);
        $response->assertViewIs('drug-destructions.show');
        $response->assertSee($destruction->destruction_number);
    }

    public function test_user_can_approve_draft_destruction(): void
    {
        $destruction = DrugDestruction::factory()->create([
            'status' => 'draft',
            'executed_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('drug-destructions.approve', $destruction));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('drug_destructions', [
            'id' => $destruction->id,
            'status' => 'approved',
            'approved_by' => $this->adminUser->id,
        ]);
    }

    public function test_user_can_complete_approved_destruction_and_deduct_stock_and_record_disposal_mutation(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $batch = Batch::factory()->create([
            'product_id' => $product->id,
            'current_qty' => 30,
            'purchase_price' => 10000,
            'status' => 'active',
        ]);

        // Stock in warehouse
        Stock::create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 30,
        ]);

        $destruction = DrugDestruction::factory()->create([
            'status' => 'approved',
            'executed_by' => $this->adminUser->id,
        ]);

        DrugDestructionItem::create([
            'destruction_id' => $destruction->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'quantity' => 10,
            'unit_price' => 10000,
            'loss_amount' => 100000,
            'reason' => 'Kadaluarsa',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('drug-destructions.complete', $destruction));

        $response->assertSessionHas('success');

        // Check destruction status
        $this->assertDatabaseHas('drug_destructions', [
            'id' => $destruction->id,
            'status' => 'completed',
        ]);

        // Check batch quantity decreased from 30 to 20
        $this->assertDatabaseHas('batches', [
            'id' => $batch->id,
            'current_qty' => 20,
        ]);

        // Check warehouse stock decreased from 30 to 20
        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 20,
        ]);

        // Check disposal mutation was logged
        $this->assertDatabaseHas('stock_mutations', [
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'warehouse_id' => $warehouse->id,
            'mutation_type' => 'disposal',
            'quantity' => 10,
            'reference_type' => 'drug_destruction',
            'reference_id' => $destruction->id,
        ]);
    }

    public function test_user_can_upload_signed_destruction_document(): void
    {
        Storage::fake('public');

        $destruction = DrugDestruction::factory()->create([
            'status' => 'completed',
            'executed_by' => $this->adminUser->id,
        ]);

        $file = UploadedFile::fake()->create('berita_acara_ttd.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->adminUser)->post(
            route('drug-destructions.upload-document', $destruction),
            ['document' => $file]
        );

        $response->assertSessionHas('success');

        $destruction->refresh();
        $this->assertNotNull($destruction->document_path);
        Storage::disk('public')->assertExists($destruction->document_path);
    }

    public function test_user_can_print_or_stream_destruction_pdf(): void
    {
        $destruction = DrugDestruction::factory()->create([
            'executed_by' => $this->adminUser->id,
        ]);
        DrugDestructionItem::factory()->create([
            'destruction_id' => $destruction->id,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('drug-destructions.pdf', $destruction));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }
}
