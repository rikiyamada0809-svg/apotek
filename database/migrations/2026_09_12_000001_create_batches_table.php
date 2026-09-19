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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('batch_number', 50);
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('received_qty', 12, 2)->default(0);
            $table->decimal('current_qty', 12, 2)->default(0);
            $table->string('status', 20)->default('active')->comment('active, expired, recalled, exhausted');
            $table->string('barcode', 50)->nullable();
            $table->text('notes')->nullable();
            $table->date('received_date')->nullable();
            $table->unsignedBigInteger('receipt_id')->nullable()->comment('Relasi ke goods_receipts');
            $table->timestamps();

            $table->index(['product_id', 'expiry_date']);
            $table->index(['product_id', 'status']);
            $table->index('batch_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
