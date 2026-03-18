<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->onDelete('cascade')
                ->name('fk_transaction_logs_transaction_id');
            $table->foreignId('user_id')
                ->constrained('users')
                ->name('fk_transaction_logs_user_id');
            $table->enum('action', ['created', 'updated', 'deleted']);
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->char('checksum', 64)->nullable();
            $table->timestamps();
            
            $table->index(['transaction_id', 'created_at'], 'idx_transaction_logs_transaction_created');
            $table->index(['user_id', 'created_at'], 'idx_transaction_logs_user_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_logs');
    }
};
