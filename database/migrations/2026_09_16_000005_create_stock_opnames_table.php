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
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('opname_number', 50)->unique();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->date('opname_date');
            $table->string('scope', 30)->default('full')->comment('full, category, location');
            $table->text('scope_filter')->nullable()->comment('JSON category/location ids');
            $table->string('status', 30)->default('draft')->comment('draft, in_progress, submitted, reviewed, approved, completed, cancelled');
            $table->integer('total_items')->default(0);
            $table->integer('matched_items')->default(0);
            $table->integer('discrepancy_items')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['warehouse_id', 'status']);
            $table->index('opname_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
