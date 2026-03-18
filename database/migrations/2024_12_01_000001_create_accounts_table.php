<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->name('fk_accounts_user_id');
            $table->string('name', 100);
            $table->enum('type', ['cash', 'bank', 'ewallet', 'investment', 'credit'])->default('cash');
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->char('currency', 3)->default('IDR');
            $table->timestamp('last_reconciled_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['user_id', 'type'], 'idx_accounts_user_type');
            $table->index('currency', 'idx_accounts_currency');
            $table->index('is_active', 'idx_accounts_is_active');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};