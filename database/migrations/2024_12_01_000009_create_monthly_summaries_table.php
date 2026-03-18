<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->name('fk_monthly_summaries_user_id');
            $table->smallInteger('year');
            $table->tinyInteger('month');
            $table->decimal('total_income', 15, 2)->default(0);
            $table->decimal('total_expense', 15, 2)->default(0);
            $table->decimal('net_savings', 15, 2)->default(0);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'year', 'month'], 'uq_monthly_summaries_user_year_month');
            $table->index(['user_id', 'year', 'month'], 'idx_monthly_summaries_user_year_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_summaries');
    }
};
