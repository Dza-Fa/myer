<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->name('fk_budgets_user_id');
            $table->smallInteger('year');
            $table->tinyInteger('month');
            $table->timestamps();
            
            $table->unique(['user_id', 'year', 'month'], 'uq_budgets_user_year_month');
            $table->index(['user_id', 'year', 'month'], 'idx_budgets_user_year_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
