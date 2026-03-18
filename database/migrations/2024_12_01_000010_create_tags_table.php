<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->name('fk_tags_user_id');
            $table->string('name', 50);
            $table->char('color', 7)->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'name'], 'uq_tags_user_name');
            $table->index(['user_id', 'name'], 'idx_tags_user_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
