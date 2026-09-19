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
        Schema::create('drug_destructions', function (Blueprint $table) {
            $table->id();
            $table->string('destruction_number', 50)->unique();
            $table->date('destruction_date');
            $table->string('witness_name', 255)->nullable()->comment('Nama saksi (Dinkes/BPOM/Apoteker)');
            $table->string('witness_role', 100)->nullable();
            $table->string('method', 100)->nullable()->comment('Metode pemusnahan');
            $table->string('status', 30)->default('draft')->comment('draft, pending, approved, executed, completed, cancelled');
            $table->decimal('total_loss_amount', 15, 2)->default(0);
            $table->string('document_path', 255)->nullable()->comment('Upload scan berita acara pemusnahan');
            $table->text('notes')->nullable();
            $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('destruction_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_destructions');
    }
};
