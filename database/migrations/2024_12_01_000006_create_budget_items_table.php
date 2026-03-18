<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')
                ->constrained('budgets')
                ->onDelete('cascade')
                ->name('fk_budget_items_budget_id');
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete()
                ->name('fk_budget_items_category_id');
            $table->decimal('planned_amount', 15, 2)->default(0);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            
            $table->unique(['budget_id', 'category_id'], 'uq_budget_items_budget_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};
