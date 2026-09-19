<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('storage_locations')->nullOnDelete();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('reserved_qty', 12, 2)->default(0);
            $table->decimal('available_qty', 12, 2)->default(0);
            $table->string('status', 20)->default('available')->comment('available, low, out_of_stock, expired');
            $table->timestamps();

            $table->unique(['product_id', 'batch_id', 'warehouse_id', 'location_id'], 'unique_product_batch_warehouse_location');
            $table->index(['product_id', 'warehouse_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
