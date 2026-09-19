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
        Schema::create('compounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->nullable()->constrained('prescriptions')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('compound_templates')->nullOnDelete();
            $table->string('compound_number', 50)->nullable()->unique();
            $table->string('name', 255)->nullable();
            $table->integer('total_packages')->default(1)->comment('Jumlah bungkus / kapsul racikan');
            $table->decimal('service_fee', 12, 2)->default(0)->comment('Jasa peracikan');
            $table->decimal('total_material_cost', 15, 2)->default(0)->comment('Total HPP bahan');
            $table->decimal('total_price', 15, 2)->default(0)->comment('Total harga jual racikan');
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('prescription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compounds');
    }
};
