<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('DROP TRIGGER IF EXISTS trg_ledger_balance_check');
            DB::statement('DROP TRIGGER IF EXISTS trg_ledger_balance_update');

            DB::statement('
                CREATE TRIGGER trg_ledger_balance_check
                AFTER INSERT ON ledger_entries
                FOR EACH ROW
                BEGIN
                    DECLARE total_debit DECIMAL(15,2);
                    DECLARE total_credit DECIMAL(15,2);
                    
                    SELECT COALESCE(SUM(debit_amount), 0), COALESCE(SUM(credit_amount), 0)
                    INTO total_debit, total_credit
                    FROM ledger_entries
                    WHERE transaction_group_id = NEW.transaction_group_id;
                    
                    IF total_debit != total_credit THEN
                        SIGNAL SQLSTATE \'45000\' 
                        SET MESSAGE_TEXT = \'Ledger must be balanced: total debit must equal total credit\';
                    END IF;
                END
            ');

            DB::statement('
                CREATE TRIGGER trg_ledger_balance_update
                AFTER UPDATE ON ledger_entries
                FOR EACH ROW
                BEGIN
                    DECLARE total_debit DECIMAL(15,2);
                    DECLARE total_credit DECIMAL(15,2);
                    
                    SELECT COALESCE(SUM(debit_amount), 0), COALESCE(SUM(credit_amount), 0)
                    INTO total_debit, total_credit
                    FROM ledger_entries
                    WHERE transaction_group_id = NEW.transaction_group_id;
                    
                    IF total_debit != total_credit THEN
                        SIGNAL SQLSTATE \'45000\' 
                        SET MESSAGE_TEXT = \'Ledger must be balanced: total debit must equal total credit\';
                    END IF;
                END
            ');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('DROP TRIGGER IF EXISTS trg_ledger_balance_check');
            DB::statement('DROP TRIGGER IF EXISTS trg_ledger_balance_update');
        }
    }
};
