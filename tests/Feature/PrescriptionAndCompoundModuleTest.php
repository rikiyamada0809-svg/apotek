<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Compound;
use App\Models\CompoundTemplate;
use App\Models\Customer;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrescriptionAndCompoundModuleTest extends TestCase
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

    public function test_user_can_view_prescription_index(): void
    {
        Prescription::factory()->create();

        $response = $this->actingAs($this->adminUser)->get(route('prescriptions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('prescriptions.index');
        $response->assertSee('Resep Obat');
    }

    public function test_user_can_view_prescription_create_page(): void
    {
        Product::factory()->create();
        Unit::factory()->create();

        $response = $this->actingAs($this->adminUser)->get(route('prescriptions.create'));

        $response->assertStatus(200);
        $response->assertViewIs('prescriptions.create');
        $response->assertSee('Input Resep Dokter Baru');
    }

    public function test_user_can_store_prescription_with_items(): void
    {
        Storage::fake('public');
        $customer = Customer::factory()->create();
        $doctor = Doctor::factory()->create();
        $product = Product::factory()->create();
        $unit = Unit::factory()->create();

        $image = UploadedFile::fake()->image('resep_asli.jpg');

        $payload = [
            'customer_id' => $customer->id,
            'doctor_id' => $doctor->id,
            'prescription_date' => now()->toDateString(),
            'diagnosis' => 'Bronkitis Kronis',
            'image' => $image,
            'notes' => 'Minum sesudah makan',
            'items' => [
                [
                    'product_id' => $product->id,
                    'unit_id' => $unit->id,
                    'quantity' => 10,
                    'dosage' => '500 mg',
                    'frequency' => '3x1 hari',
                    'duration_days' => 3,
                    'instructions' => 'Sesudah makan habiskan',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('prescriptions.store'), $payload);

        $this->assertDatabaseHas('prescriptions', [
            'customer_id' => $customer->id,
            'doctor_id' => $doctor->id,
            'diagnosis' => 'Bronkitis Kronis',
            'status' => 'submitted',
        ]);

        $prescription = Prescription::where('customer_id', $customer->id)->first();
        $this->assertNotNull($prescription);
        $this->assertNotNull($prescription->image_url);

        $this->assertDatabaseHas('prescription_items', [
            'prescription_id' => $prescription->id,
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'quantity' => 10,
            'dosage' => '500 mg',
        ]);

        $response->assertRedirect(route('prescriptions.show', $prescription));
    }

    public function test_pharmacist_can_validate_and_approve_prescription(): void
    {
        $prescription = Prescription::factory()->create(['status' => 'submitted']);

        $response = $this->actingAs($this->adminUser)->post(route('prescriptions.validate', $prescription), [
            'action' => 'approve',
            'notes' => 'Dosis dan sediaan sesuai rekomendasi',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('prescriptions', [
            'id' => $prescription->id,
            'status' => 'validated',
            'validated_by' => $this->adminUser->id,
        ]);
    }

    public function test_pharmacist_can_reject_prescription(): void
    {
        $prescription = Prescription::factory()->create(['status' => 'submitted']);

        $response = $this->actingAs($this->adminUser)->post(route('prescriptions.validate', $prescription), [
            'action' => 'reject',
            'notes' => 'Dosis melebihi ambang batas maksimal',
        ]);

        $response->assertSessionHas('warning');
        $this->assertDatabaseHas('prescriptions', [
            'id' => $prescription->id,
            'status' => 'rejected',
        ]);
    }

    public function test_user_can_dispense_validated_prescription(): void
    {
        $prescription = Prescription::factory()->create(['status' => 'validated']);

        $response = $this->actingAs($this->adminUser)->post(route('prescriptions.dispense', $prescription));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('prescriptions', [
            'id' => $prescription->id,
            'status' => 'dispensed',
        ]);
    }

    public function test_user_can_copy_prescription_for_refill(): void
    {
        $prescription = Prescription::factory()->create([
            'diagnosis' => 'Hipertensi Primer',
        ]);
        $product = Product::factory()->create();
        $unit = Unit::factory()->create();

        PrescriptionItem::create([
            'prescription_id' => $prescription->id,
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'quantity' => 30,
            'dosage' => '10 mg',
            'frequency' => '1x1 pagi',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('prescriptions.copy', $prescription));

        $response->assertSessionHas('success');

        // Verify duplicate exists
        $copied = Prescription::where('id', '!=', $prescription->id)->first();
        $this->assertNotNull($copied);
        $this->assertEquals('submitted', $copied->status);
        $this->assertEquals('Hipertensi Primer', $copied->diagnosis);

        $this->assertDatabaseHas('prescription_items', [
            'prescription_id' => $copied->id,
            'product_id' => $product->id,
            'quantity' => 30,
        ]);
    }

    public function test_user_can_crud_compound_template(): void
    {
        $product = Product::factory()->create();
        $unit = Unit::factory()->create();

        // 1. Create Template
        $createPayload = [
            'name' => 'Puyer Paracetamol Anak',
            'description' => 'Untuk demam anak',
            'preparation_instructions' => 'Gerus halus bagi 10',
            'service_fee' => 7000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity_per_unit' => 0.5,
                    'unit_id' => $unit->id,
                    'notes' => 'Pulv',
                ],
            ],
        ];

        $resCreate = $this->actingAs($this->adminUser)->post(route('compound-templates.store'), $createPayload);
        $resCreate->assertRedirect(route('compound-templates.index'));

        $this->assertDatabaseHas('compound_templates', [
            'name' => 'Puyer Paracetamol Anak',
            'service_fee' => 7000,
        ]);

        $template = CompoundTemplate::where('name', 'Puyer Paracetamol Anak')->first();
        $this->assertNotNull($template);
        $this->assertDatabaseHas('compound_template_items', [
            'template_id' => $template->id,
            'product_id' => $product->id,
            'quantity_per_unit' => 0.5,
        ]);

        // 2. Edit / Update Template
        $updatePayload = [
            'name' => 'Puyer Paracetamol Anak Plus',
            'description' => 'Update formula',
            'preparation_instructions' => 'Gerus halus',
            'service_fee' => 8000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity_per_unit' => 0.75,
                    'unit_id' => $unit->id,
                    'notes' => 'Pulv rev',
                ],
            ],
        ];

        $resUpdate = $this->actingAs($this->adminUser)->put(route('compound-templates.update', $template), $updatePayload);
        $resUpdate->assertRedirect(route('compound-templates.index'));

        $this->assertDatabaseHas('compound_templates', [
            'id' => $template->id,
            'name' => 'Puyer Paracetamol Anak Plus',
            'service_fee' => 8000,
        ]);

        // 3. Delete Template
        $resDelete = $this->actingAs($this->adminUser)->delete(route('compound-templates.destroy', $template));
        $resDelete->assertRedirect(route('compound-templates.index'));

        $this->assertDatabaseMissing('compound_templates', [
            'id' => $template->id,
        ]);
    }

    public function test_user_can_create_compound_with_auto_calculated_materials_and_price(): void
    {
        $product1 = Product::factory()->create();
        $unit = Unit::factory()->create();

        // Active FEFO batch
        $batch = Batch::factory()->create([
            'product_id' => $product1->id,
            'status' => 'active',
            'current_qty' => 100,
            'expiry_date' => now()->addYear()->toDateString(),
        ]);

        $payload = [
            'name' => 'Racikan Batuk Flu 10 Bungkus',
            'total_packages' => 10,
            'service_fee' => 5000,
            'items' => [
                [
                    'product_id' => $product1->id,
                    'unit_id' => $unit->id,
                    'quantity_per_package' => 1.5,
                    'unit_price' => 1000,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('compounds.store'), $payload);

        // Expected calculation:
        // quantity_used = 1.5 * 10 = 15
        // total_material_cost = 15 * 1000 = 15000
        // total_price = 15000 + 5000 = 20000

        $this->assertDatabaseHas('compounds', [
            'name' => 'Racikan Batuk Flu 10 Bungkus',
            'total_packages' => 10,
            'service_fee' => 5000,
            'total_material_cost' => 15000,
            'total_price' => 20000,
        ]);

        $compound = Compound::where('name', 'Racikan Batuk Flu 10 Bungkus')->first();
        $this->assertNotNull($compound);

        $this->assertDatabaseHas('compound_items', [
            'compound_id' => $compound->id,
            'product_id' => $product1->id,
            'batch_id' => $batch->id,
            'quantity_used' => 15,
            'unit_price' => 1000,
            'subtotal' => 15000,
        ]);

        $response->assertRedirect(route('compounds.show', $compound));

        // Test label view (etiket)
        $labelResponse = $this->actingAs($this->adminUser)->get(route('compounds.label', $compound));
        $labelResponse->assertStatus(200);
        $labelResponse->assertSee('ETIKET OBAT RACIKAN', false);
        $labelResponse->assertSee($compound->compound_number);
    }
}
