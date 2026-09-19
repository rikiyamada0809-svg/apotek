<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Doctor;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\StorageLocation;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->seed(RoleAndPermissionSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Pemilik');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Apotek');
    }

    public function test_can_render_all_master_data_index_pages(): void
    {
        $this->actingAs($this->adminUser)->get(route('categories.index'))->assertStatus(200)->assertSee('Kategori Obat');
        $this->actingAs($this->adminUser)->get(route('units.index'))->assertStatus(200)->assertSee('Satuan Obat');
        $this->actingAs($this->adminUser)->get(route('manufacturers.index'))->assertStatus(200)->assertSee('Pabrik / Produsen');
        $this->actingAs($this->adminUser)->get(route('suppliers.index'))->assertStatus(200)->assertSee('Supplier / PBF');
        $this->actingAs($this->adminUser)->get(route('products.index'))->assertStatus(200)->assertSee('Master Obat');
        $this->actingAs($this->adminUser)->get(route('customers.index'))->assertStatus(200)->assertSee('Pelanggan / Pasien');
        $this->actingAs($this->adminUser)->get(route('doctors.index'))->assertStatus(200)->assertSee('Dokter Mitra');
        $this->actingAs($this->adminUser)->get(route('warehouses.index'))->assertStatus(200)->assertSee('Gudang');
        $this->actingAs($this->adminUser)->get(route('users.index'))->assertStatus(200)->assertSee('Manajemen Pengguna');
    }

    public function test_can_render_all_master_data_create_pages(): void
    {
        $warehouse = Warehouse::factory()->create();

        $this->actingAs($this->adminUser)->get(route('categories.create'))->assertStatus(200);
        $this->actingAs($this->adminUser)->get(route('units.create'))->assertStatus(200);
        $this->actingAs($this->adminUser)->get(route('manufacturers.create'))->assertStatus(200);
        $this->actingAs($this->adminUser)->get(route('suppliers.create'))->assertStatus(200);
        $this->actingAs($this->adminUser)->get(route('products.create'))->assertStatus(200);
        $this->actingAs($this->adminUser)->get(route('customers.create'))->assertStatus(200);
        $this->actingAs($this->adminUser)->get(route('doctors.create'))->assertStatus(200);
        $this->actingAs($this->adminUser)->get(route('warehouses.create'))->assertStatus(200);
        $this->actingAs($this->adminUser)->get(route('storage-locations.create', $warehouse))->assertStatus(200);
        $this->actingAs($this->adminUser)->get(route('users.create'))->assertStatus(200);
    }

    public function test_can_render_all_master_data_edit_and_show_pages(): void
    {
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();
        $manufacturer = Manufacturer::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();
        ProductUnit::factory()->create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
        ]);
        $customer = Customer::factory()->create();
        $doctor = Doctor::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $location = StorageLocation::factory()->create(['warehouse_id' => $warehouse->id]);
        $user = User::factory()->create();

        $this->actingAs($this->adminUser)->get(route('categories.edit', $category))->assertStatus(200);
        $this->actingAs($this->adminUser)->get(route('units.edit', $unit))->assertStatus(200);
        $this->actingAs($this->adminUser)->get(route('manufacturers.edit', $manufacturer))->assertStatus(200);

        $this->actingAs($this->adminUser)->get(route('suppliers.show', $supplier))->assertStatus(200)->assertSee($supplier->name);
        $this->actingAs($this->adminUser)->get(route('suppliers.edit', $supplier))->assertStatus(200);

        $this->actingAs($this->adminUser)->get(route('products.show', $product))->assertStatus(200)->assertSee($product->name);
        $this->actingAs($this->adminUser)->get(route('products.edit', $product))->assertStatus(200);

        $this->actingAs($this->adminUser)->get(route('customers.show', $customer))->assertStatus(200)->assertSee($customer->name);
        $this->actingAs($this->adminUser)->get(route('customers.edit', $customer))->assertStatus(200);

        $this->actingAs($this->adminUser)->get(route('doctors.edit', $doctor))->assertStatus(200);

        $this->actingAs($this->adminUser)->get(route('warehouses.show', $warehouse))->assertStatus(200)->assertSee($warehouse->name);
        $this->actingAs($this->adminUser)->get(route('warehouses.edit', $warehouse))->assertStatus(200);
        $this->actingAs($this->adminUser)->get(route('storage-locations.edit', [$warehouse, $location]))->assertStatus(200);

        $this->actingAs($this->adminUser)->get(route('users.edit', $user))->assertStatus(200);
    }

    public function test_can_create_category(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('categories.store'), [
            'name' => 'Antibiotik',
            'description' => 'Golongan obat antibiotik',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Antibiotik',
        ]);
    }

    public function test_can_create_unit(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('units.store'), [
            'name' => 'Strip',
            'abbreviation' => 'Str',
        ]);

        $response->assertRedirect(route('units.index'));
        $this->assertDatabaseHas('units', [
            'name' => 'Strip',
            'abbreviation' => 'Str',
        ]);
    }

    public function test_can_create_manufacturer(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('manufacturers.store'), [
            'name' => 'Kimia Farma',
            'country' => 'Indonesia',
            'contact' => '021-123456',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('manufacturers.index'));
        $this->assertDatabaseHas('manufacturers', [
            'name' => 'Kimia Farma',
        ]);
    }

    public function test_can_create_supplier(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('suppliers.store'), [
            'code' => 'SUP-001',
            'name' => 'PBF Enseval Putera Megatrading',
            'contact_person' => 'Budi Santoso',
            'phone' => '08123456789',
            'email' => 'contact@enseval.com',
            'payment_terms' => 30,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'code' => 'SUP-001',
            'name' => 'PBF Enseval Putera Megatrading',
        ]);
    }

    public function test_can_create_product_with_pricing_units(): void
    {
        $category = Category::factory()->create();
        $manufacturer = Manufacturer::factory()->create();
        $unit = Unit::factory()->create(['name' => 'Strip', 'abbreviation' => 'Str']);

        $response = $this->actingAs($this->adminUser)->post(route('products.store'), [
            'product_code' => 'PRD-0001',
            'name' => 'Amoxicillin 500mg',
            'generic_name' => 'Amoxicillin Trihydrate',
            'category_id' => $category->id,
            'manufacturer_id' => $manufacturer->id,
            'drug_class' => 'Keras',
            'form' => 'Kapsul',
            'strength' => '500mg',
            'requires_prescription' => true,
            'is_active' => true,
            'units' => [
                [
                    'unit_id' => $unit->id,
                    'conversion_factor' => 1,
                    'is_base_unit' => true,
                    'purchase_price' => 5000,
                    'selling_price' => 7500,
                    'wholesale_price' => 6500,
                    'prescription_price' => 8000,
                ],
            ],
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'product_code' => 'PRD-0001',
            'name' => 'Amoxicillin 500mg',
        ]);
        $this->assertDatabaseHas('product_units', [
            'purchase_price' => 5000,
            'selling_price' => 7500,
        ]);
    }

    public function test_can_create_customer(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('customers.store'), [
            'code' => 'CST-001',
            'name' => 'John Doe',
            'phone' => '08129876543',
            'customer_type' => 'member',
            'discount_percentage' => 5,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', [
            'code' => 'CST-001',
            'name' => 'John Doe',
            'customer_type' => 'member',
        ]);
    }

    public function test_can_create_doctor(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('doctors.store'), [
            'name' => 'dr. Sarah Sp.A',
            'sip_number' => 'SIP-445/123/2024',
            'specialization' => 'Anak',
            'phone' => '081122334455',
            'clinic_name' => 'RS Ibu & Anak Kasih',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('doctors.index'));
        $this->assertDatabaseHas('doctors', [
            'name' => 'dr. Sarah Sp.A',
            'sip_number' => 'SIP-445/123/2024',
        ]);
    }

    public function test_can_create_warehouse_and_storage_locations(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('warehouses.store'), [
            'name' => 'Gudang Utama Farmasi',
            'type' => 'main',
            'address' => 'Jl. Kesehatan No. 10',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('warehouses.index'));
        $warehouse = Warehouse::where('name', 'Gudang Utama Farmasi')->first();
        $this->assertNotNull($warehouse);

        $locResponse = $this->actingAs($this->adminUser)->post(route('storage-locations.store', $warehouse), [
            'code' => 'RAK-A1',
            'name' => 'Rak Obat Keras 1',
            'type' => 'rak',
            'is_active' => true,
        ]);

        $locResponse->assertRedirect(route('warehouses.show', $warehouse));
        $this->assertDatabaseHas('storage_locations', [
            'warehouse_id' => $warehouse->id,
            'code' => 'RAK-A1',
        ]);
    }

    public function test_can_create_user_with_role(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('users.store'), [
            'name' => 'Apoteker Dewi',
            'email' => 'dewi@apotek.com',
            'phone' => '081233445566',
            'role' => 'Apoteker',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('users.index'));
        $user = User::where('email', 'dewi@apotek.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Apoteker'));
    }
}
