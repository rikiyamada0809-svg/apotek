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
        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('sale_returns')->cascadeOnDelete();
            $table->foreignId('original_sale_item_id')->nullable()->constrained('sale_items')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 15, 2);
            $table->string('item_condition', 30)->default('good')->comment('good, damaged, expired');
            $table->boolean('return_to_stock')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['return_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
    }
};
