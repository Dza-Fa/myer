<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')
                ->constrained('accounts')
                ->onDelete('cascade')
                ->name('fk_account_categories_account_id');
            $table->foreignId('category_id')
                ->constrained('categories')
                ->onDelete('cascade')
                ->name('fk_account_categories_category_id');
            $table->timestamps();
            
            $table->unique(['account_id', 'category_id'], 'uq_account_categories_account_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_categories');
    }
};
