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
        Schema::create('drug_destruction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destruction_id')->constrained('drug_destructions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('loss_amount', 15, 2)->default(0);
            $table->string('reason', 255)->nullable()->comment('expired, damaged, contaminated');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['destruction_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_destruction_items');
    }
};
