<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->name('fk_recurring_transactions_user_id');
            $table->foreignId('account_id')
                ->constrained('accounts')
                ->name('fk_recurring_transactions_account_id');
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete()
                ->name('fk_recurring_transactions_category_id');
            $table->enum('type', ['income', 'expense']);
            $table->decimal('amount', 15, 2);
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->tinyInteger('day_of_month')->nullable();
            $table->date('next_run_date');
            $table->unsignedInteger('total_runs')->nullable();
            $table->unsignedInteger('run_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_executed_at')->nullable();
            $table->timestamps();
            
            $table->unique(
                ['user_id', 'account_id', 'category_id', 'type', 'frequency', 'is_active'], 
                'uq_recurring_transactions_unique_active'
            );
            $table->index(['user_id', 'is_active', 'next_run_date'], 'idx_recurring_transactions_scheduler');
            $table->index(['is_active', 'next_run_date'], 'idx_recurring_transactions_next_run');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
