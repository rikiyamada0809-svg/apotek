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
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('dosage', 100)->nullable()->comment('Contoh: 500mg, 1 tablet');
            $table->string('frequency', 100)->nullable()->comment('Contoh: 3x1 sehari');
            $table->integer('duration_days')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->foreignId('unit_id')->constrained('units');
            $table->string('instructions', 255)->nullable()->comment('Contoh: Sesudah makan, habiskan');
            $table->boolean('is_compounded')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['prescription_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
