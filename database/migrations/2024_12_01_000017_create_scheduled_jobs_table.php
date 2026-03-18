<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_transaction_id')
                ->constrained('recurring_transactions')
                ->onDelete('cascade')
                ->name('fk_scheduled_jobs_recurring_id');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->name('fk_scheduled_jobs_user_id');
            $table->foreignId('transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete()
                ->name('fk_scheduled_jobs_transaction_id');
            $table->dateTime('scheduled_at');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'skipped'])->default('pending');
            $table->text('error_message')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'scheduled_at'], 'idx_scheduled_jobs_status_scheduled');
            $table->index(['user_id', 'status'], 'idx_scheduled_jobs_user_status');
            $table->index('scheduled_at', 'idx_scheduled_jobs_scheduled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_jobs');
    }
};
