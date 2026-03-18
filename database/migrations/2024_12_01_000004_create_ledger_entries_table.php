<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->onDelete('cascade')
                ->name('fk_ledger_entries_transaction_id');
            $table->foreignId('account_id')
                ->constrained('accounts')
                ->name('fk_ledger_entries_account_id');
            $table->decimal('debit_amount', 15, 2)->default(0);
            $table->decimal('credit_amount', 15, 2)->default(0);
            $table->uuid('transaction_group_id');
            $table->smallInteger('sequence_no');
            $table->timestamps();
            
            $table->index(['account_id', 'created_at'], 'idx_ledger_entries_account_created');
            $table->index('transaction_group_id', 'idx_ledger_entries_group_id');
        });

        // MySQL CHECK constraints for double-entry accounting
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE ledger_entries ADD CONSTRAINT chk_debit_or_credit_positive 
                CHECK ((debit_amount > 0 AND credit_amount = 0) OR (debit_amount = 0 AND credit_amount > 0))');
            
            DB::statement('ALTER TABLE ledger_entries ADD CONSTRAINT chk_debit_non_negative 
                CHECK (debit_amount >= 0)');
            
            DB::statement('ALTER TABLE ledger_entries ADD CONSTRAINT chk_credit_non_negative 
                CHECK (credit_amount >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
