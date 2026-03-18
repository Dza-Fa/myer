<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->name('fk_transactions_user_id');
            $table->string('idempotency_key')->unique()->nullable();
            $table->string('client_request_id', 255)->nullable();
            $table->foreignId('account_id')
                ->constrained('accounts')
                ->onDelete('cascade')
                ->name('fk_transactions_account_id');
            $table->foreignId('transfer_account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete()
                ->name('fk_transactions_transfer_account_id');
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete()
                ->name('fk_transactions_category_id');
            $table->enum('type', ['income', 'expense', 'transfer']);
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'posted', 'locked', 'voided'])->default('draft');
            $table->timestamp('posting_date')->nullable();
            $table->unsignedInteger('version')->default(0);
            $table->foreignId('reversal_of_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete()
                ->name('fk_transactions_reversal_of_id');
            $table->boolean('is_reversal')->default(false);
            $table->uuid('transaction_group_id');
            $table->boolean('cleared')->default(false);
            $table->softDeletes();
            $table->timestamps();
            
            // Composite indexes untuk range queries
            $table->index(['user_id', 'transaction_date'], 'idx_transactions_user_date');
            $table->index(['user_id', 'status', 'transaction_date'], 'idx_transactions_user_status_date');
            $table->index(['user_id', 'type', 'transaction_date'], 'idx_transactions_user_type_date');
            $table->index('client_request_id', 'idx_transactions_client_request_id');
            $table->index('cleared', 'idx_transactions_cleared');
            $table->index('transaction_group_id', 'idx_transactions_group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
