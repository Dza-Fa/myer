<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3);
            $table->string('target_currency', 3);
            $table->decimal('rate', 20, 8);
            $table->date('effective_date');
            $table->timestamps();
            
            $table->unique(['base_currency', 'target_currency', 'effective_date'], 'uq_currency_rates_currency_date');
            $table->index(['base_currency', 'target_currency'], 'idx_currency_rates_currencies');
            $table->index('effective_date', 'idx_currency_rates_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_rates');
    }
};
