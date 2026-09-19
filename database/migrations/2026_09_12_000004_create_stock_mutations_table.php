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
        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('mutation_type', 30)->comment('in, out, adjustment, transfer, expired, disposal');
            $table->string('reference_type', 50)->nullable()->comment('purchase_order, goods_receipt, sale, stock_opname, etc');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->decimal('stock_before', 12, 2)->default(0);
            $table->decimal('stock_after', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('mutation_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');
    }
};
