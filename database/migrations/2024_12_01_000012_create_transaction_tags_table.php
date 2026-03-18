<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->onDelete('cascade')
                ->name('fk_transaction_tags_transaction_id');
            $table->foreignId('tag_id')
                ->constrained('tags')
                ->onDelete('cascade')
                ->name('fk_transaction_tags_tag_id');
            $table->timestamps();
            
            $table->unique(['transaction_id', 'tag_id'], 'uq_transaction_tags_transaction_tag');
            $table->index('tag_id', 'idx_transaction_tags_tag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_tags');
    }
};
