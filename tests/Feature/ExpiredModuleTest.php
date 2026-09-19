<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Stock;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpiredModuleTest extends TestCase
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

    public function test_user_can_view_expired_dashboard(): void
    {
        $product = Product::factory()->create();

        // Expired batch
        Batch::factory()->create([
            'product_id' => $product->id,
            'status' => 'expired',
            'expiry_date' => now()->subDay()->toDateString(),
            'current_qty' => 10,
        ]);

        // Critical batch (≤ 30 days)
        Batch::factory()->create([
            'product_id' => $product->id,
            'status' => 'active',
            'expiry_date' => now()->addDays(15)->toDateString(),
            'current_qty' => 20,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('expired.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('expired.dashboard');
        $response->assertSee('Dashboard Monitoring Kadaluarsa');
        $response->assertSee('Sudah Expired');
        $response->assertSee('Critical (≤ 30 Hari)');
    }

    public function test_user_can_filter_expired_index_by_range(): void
    {
        $product = Product::factory()->create();

        $expiredBatch = Batch::factory()->create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-EXP-001',
            'status' => 'expired',
            'expiry_date' => now()->subDays(5)->toDateString(),
            'current_qty' => 10,
        ]);

        $nearBatch = Batch::factory()->create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-NEAR-002',
            'status' => 'active',
            'expiry_date' => now()->addDays(10)->toDateString(),
            'current_qty' => 15,
        ]);

        $safeBatch = Batch::factory()->create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-SAFE-003',
            'status' => 'active',
            'expiry_date' => now()->addDays(300)->toDateString(),
            'current_qty' => 25,
        ]);

        // Filter expired
        $respExpired = $this->actingAs($this->adminUser)->get(route('expired.index', ['range' => 'expired']));
        $respExpired->assertStatus(200);
        $respExpired->assertSee('BATCH-EXP-001');
        $respExpired->assertDontSee('BATCH-SAFE-003');

        // Filter 1 month (critical)
        $resp1Month = $this->actingAs($this->adminUser)->get(route('expired.index', ['range' => '1_month']));
        $resp1Month->assertStatus(200);
        $resp1Month->assertSee('BATCH-NEAR-002');
        $resp1Month->assertDontSee('BATCH-SAFE-003');
    }

    public function test_artisan_command_expired_check_updates_expired_batches(): void
    {
        $product = Product::factory()->create();

        $batch = Batch::factory()->create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-YESTERDAY',
            'status' => 'active',
            'expiry_date' => now()->subDays(2)->toDateString(),
            'current_qty' => 30,
        ]);

        $this->assertEquals('active', $batch->status);

        $this->artisan('expired:check')
            ->expectsOutputToContain('Berhasil memperbarui')
            ->assertExitCode(0);

        $batch->refresh();
        $this->assertEquals('expired', $batch->status);
    }

    public function test_pos_blocks_checkout_of_expired_batch(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $unit = Unit::factory()->create();

        $expiredBatch = Batch::factory()->create([
            'product_id' => $product->id,
            'batch_number' => 'BATCH-EXPIRED-TEST',
            'status' => 'expired',
            'expiry_date' => now()->subDays(10)->toDateString(),
            'current_qty' => 50,
        ]);

        Stock::create([
            'product_id' => $product->id,
            'batch_id' => $expiredBatch->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 50,
            'available_qty' => 50,
            'reserved_qty' => 0,
            'status' => 'available',
        ]);

        $shift = Shift::create([
            'user_id' => $this->adminUser->id,
            'warehouse_id' => $warehouse->id,
            'opening_cash' => 100000,
            'start_time' => now(),
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('pos.store'), [
            'status' => 'completed',
            'items' => [
                [
                    'product_id' => $product->id,
                    'unit_id' => $unit->id,
                    'quantity' => 1,
                    'unit_price' => 15000,
                    'batch_id' => $expiredBatch->id,
                ],
            ],
            'payments' => [
                [
                    'payment_method' => 'cash',
                    'amount' => 15000,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => "Batch {$expiredBatch->batch_number} telah kedaluwarsa (expired) dan tidak dapat diperjualbelikan.",
        ]);
    }

    public function test_user_can_manually_trigger_run_check_from_web(): void
    {
        $product = Product::factory()->create();

        Batch::factory()->create([
            'product_id' => $product->id,
            'status' => 'active',
            'expiry_date' => now()->subDay()->toDateString(),
            'current_qty' => 5,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('expired.run-check'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
