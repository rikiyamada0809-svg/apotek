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
        Schema::create('compound_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('compound_templates')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity_per_unit', 12, 2)->comment('Dosis / jumlah bahan per bungkus');
            $table->foreignId('unit_id')->constrained('units');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['template_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compound_template_items');
    }
};
