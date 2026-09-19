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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code', 30)->unique();
            $table->string('barcode', 50)->nullable()->unique();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('brand')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('drug_class', 30)->nullable()->comment('Bebas, Bebas Terbatas, Keras, Narkotika, Psikotropika');
            $table->string('form', 50)->nullable()->comment('Tablet, Kapsul, Sirup, Salep, dll');
            $table->string('strength', 50)->nullable()->comment('500mg, 250mg, 10mg/ml, dll');
            $table->foreignId('manufacturer_id')->nullable()->constrained('manufacturers')->nullOnDelete();
            $table->boolean('requires_prescription')->default(false);
            $table->string('storage_condition', 50)->nullable()->default('Suhu Kamar');
            $table->text('description')->nullable();
            $table->integer('min_order_qty')->default(1);
            $table->boolean('is_active')->default(true);
            $table->string('image_url')->nullable();
            $table->timestamps();

            $table->index(['name', 'is_active']);
            $table->index('generic_name');
            $table->index('drug_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
