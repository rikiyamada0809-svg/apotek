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
        Schema::create('batch_recalls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->string('recall_number', 50)->unique();
            $table->string('recall_source', 100)->nullable()->comment('BPOM, Produsen, Kemenkes, Internal');
            $table->string('recall_reason', 255);
            $table->date('recall_date');
            $table->decimal('affected_qty', 12, 2)->default(0);
            $table->decimal('sold_qty', 12, 2)->default(0);
            $table->decimal('recovered_qty', 12, 2)->default(0);
            $table->string('status', 30)->default('initiated')->comment('initiated, in_progress, quarantined, returned_to_supplier, destroyed, completed');
            $table->text('action_taken')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['batch_id', 'status']);
            $table->index('recall_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_recalls');
    }
};
