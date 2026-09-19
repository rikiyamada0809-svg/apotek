<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\BatchRecall;
use App\Models\Compound;
use App\Models\CompoundItem;
use App\Models\CompoundTemplate;
use App\Models\CompoundTemplateItem;
use App\Models\Customer;
use App\Models\Doctor;
use App\Models\DrugDestruction;
use App\Models\DrugDestructionItem;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionCompoundAndRecallMigrationTest extends TestCase
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

    public function test_prescription_and_items_can_be_created_with_relationships(): void
    {
        $doctor = Doctor::factory()->create(['name' => 'dr. Hendra Kurniawan, Sp.A']);
        $customer = Customer::factory()->create(['name' => 'Budi Santoso']);
        $product = Product::factory()->create(['name' => 'Amoxicillin Sirup']);
        $unit = Unit::factory()->create(['name' => 'Botol']);

        $prescription = Prescription::create([
            'prescription_number' => 'RX-20260916-0001',
            'doctor_id' => $doctor->id,
            'customer_id' => $customer->id,
            'prescription_date' => now()->toDateString(),
            'diagnosis' => 'ISPA',
            'status' => 'validated',
            'validated_by' => $this->adminUser->id,
            'validated_at' => now(),
            'notes' => 'Pasien anak 4 tahun',
        ]);

        $item = PrescriptionItem::create([
            'prescription_id' => $prescription->id,
            'product_id' => $product->id,
            'dosage' => '125mg/5ml',
            'frequency' => '3x1 sendok takar',
            'duration_days' => 5,
            'quantity' => 1,
            'unit_id' => $unit->id,
            'instructions' => 'Sesudah makan, habiskan',
            'is_compounded' => false,
        ]);

        $this->assertDatabaseHas('prescriptions', [
            'prescription_number' => 'RX-20260916-0001',
            'doctor_id' => $doctor->id,
            'customer_id' => $customer->id,
            'status' => 'validated',
        ]);

        $this->assertDatabaseHas('prescription_items', [
            'prescription_id' => $prescription->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->assertEquals($doctor->id, $prescription->doctor->id);
        $this->assertEquals($customer->id, $prescription->customer->id);
        $this->assertEquals($this->adminUser->id, $prescription->validator->id);
        $this->assertEquals(1, $prescription->items->count());
        $this->assertTrue($doctor->prescriptions->contains($prescription));
        $this->assertTrue($customer->prescriptions->contains($prescription));
    }

    public function test_compound_template_and_items_can_be_created(): void
    {
        $product = Product::factory()->create(['name' => 'Paracetamol 500mg']);
        $unit = Unit::factory()->create(['name' => 'Tablet']);

        $template = CompoundTemplate::create([
            'name' => 'Puyer Demam Anak',
            'description' => 'Untuk anak 1-3 tahun',
            'preparation_instructions' => 'Gerus halus dan bagi menjadi 10 bungkus',
            'service_fee' => 5000,
            'created_by' => $this->adminUser->id,
        ]);

        $templateItem = CompoundTemplateItem::create([
            'template_id' => $template->id,
            'product_id' => $product->id,
            'quantity_per_unit' => 0.5,
            'unit_id' => $unit->id,
            'notes' => 'Setengah tablet per bungkus',
        ]);

        $this->assertDatabaseHas('compound_templates', [
            'name' => 'Puyer Demam Anak',
            'service_fee' => 5000,
        ]);

        $this->assertDatabaseHas('compound_template_items', [
            'template_id' => $template->id,
            'product_id' => $product->id,
            'quantity_per_unit' => 0.5,
        ]);

        $this->assertEquals($this->adminUser->id, $template->creator->id);
        $this->assertEquals(1, $template->items->count());
    }

    public function test_compound_transaction_calculates_totals_and_relationships(): void
    {
        $doctor = Doctor::factory()->create();
        $customer = Customer::factory()->create();
        $prescription = Prescription::factory()->create([
            'doctor_id' => $doctor->id,
            'customer_id' => $customer->id,
        ]);

        $product1 = Product::factory()->create(['name' => 'CTM 4mg']);
        $product2 = Product::factory()->create(['name' => 'Dexamethasone 0.5mg']);
        $batch1 = Batch::factory()->create(['product_id' => $product1->id]);
        $batch2 = Batch::factory()->create(['product_id' => $product2->id]);
        $unit = Unit::factory()->create(['name' => 'Tablet']);

        $compound = Compound::create([
            'prescription_id' => $prescription->id,
            'compound_number' => 'CMP-20260916-0001',
            'name' => 'Puyer Alergi & Anti-inflamasi',
            'total_packages' => 10,
            'service_fee' => 7000,
            'total_material_cost' => 0,
            'total_price' => 0,
            'prepared_by' => $this->adminUser->id,
        ]);

        CompoundItem::create([
            'compound_id' => $compound->id,
            'product_id' => $product1->id,
            'batch_id' => $batch1->id,
            'quantity_used' => 5,
            'unit_id' => $unit->id,
            'unit_price' => 500,
            'subtotal' => 2500,
        ]);

        CompoundItem::create([
            'compound_id' => $compound->id,
            'product_id' => $product2->id,
            'batch_id' => $batch2->id,
            'quantity_used' => 5,
            'unit_id' => $unit->id,
            'unit_price' => 1000,
            'subtotal' => 5000,
        ]);

        $compound->load('items');
        $compound->calculateTotals();
        $compound->save();

        $this->assertDatabaseHas('compounds', [
            'compound_number' => 'CMP-20260916-0001',
            'total_material_cost' => 7500,
            'total_price' => 14500, // 7500 materials + 7000 fee
        ]);

        $this->assertEquals(2, $compound->items->count());
        $this->assertEquals($prescription->id, $compound->prescription->id);
    }

    public function test_drug_destruction_and_items_can_be_recorded(): void
    {
        $product = Product::factory()->create(['name' => 'Sirup Amoxicillin Expired']);
        $batch = Batch::factory()->create(['product_id' => $product->id]);

        $destruction = DrugDestruction::create([
            'destruction_number' => 'BA-MUSNAH-20260916-0001',
            'destruction_date' => now()->toDateString(),
            'witness_name' => 'Drs. H. Mulyadi, Apt',
            'witness_role' => 'Petugas Balai POM',
            'method' => 'Insinerasi Limbah Medis Berizin',
            'status' => 'executed',
            'total_loss_amount' => 500000,
            'executed_by' => $this->adminUser->id,
            'approved_by' => $this->adminUser->id,
        ]);

        $item = DrugDestructionItem::create([
            'destruction_id' => $destruction->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'quantity' => 20,
            'unit_price' => 25000,
            'loss_amount' => 500000,
            'reason' => 'Kadaluarsa dan cairan menggumpal',
        ]);

        $this->assertDatabaseHas('drug_destructions', [
            'destruction_number' => 'BA-MUSNAH-20260916-0001',
            'status' => 'executed',
        ]);

        $this->assertDatabaseHas('drug_destruction_items', [
            'destruction_id' => $destruction->id,
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'loss_amount' => 500000,
        ]);

        $this->assertEquals(1, $destruction->items->count());
        $this->assertTrue($batch->destructionItems->contains($item));
    }

    public function test_batch_recall_can_be_tracked_with_relationships(): void
    {
        $product = Product::factory()->create(['name' => 'Obat Batuk Tercemar']);
        $batch = Batch::factory()->create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-RECALL-99',
        ]);

        $recall = BatchRecall::create([
            'batch_id' => $batch->id,
            'recall_number' => 'RCL-20260916-0001',
            'recall_source' => 'Badan Pengawas Obat dan Makanan (BPOM)',
            'recall_reason' => 'Peringatan cemaran senyawa melebihi batas aman',
            'recall_date' => now()->toDateString(),
            'affected_qty' => 500,
            'sold_qty' => 120,
            'recovered_qty' => 380,
            'status' => 'quarantined',
            'action_taken' => 'Karantina sisa stok di lemari khusus dan pengumuman penarikan ke pelanggan',
            'created_by' => $this->adminUser->id,
        ]);

        $this->assertDatabaseHas('batch_recalls', [
            'batch_id' => $batch->id,
            'recall_number' => 'RCL-20260916-0001',
            'status' => 'quarantined',
        ]);

        $this->assertEquals($batch->id, $recall->batch->id);
        $this->assertTrue($batch->recalls->contains($recall));
    }
}
