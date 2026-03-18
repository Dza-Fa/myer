<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->name('fk_categories_user_id');
            $table->string('name', 100);
            $table->enum('type', ['income', 'expense']);
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete()
                ->name('fk_categories_parent_id');
            $table->string('icon', 50)->nullable();
            $table->char('color', 7)->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            
            $table->unique(['user_id', 'name', 'type'], 'uq_categories_user_name_type');
            $table->index(['user_id', 'type'], 'idx_categories_user_type');
            $table->index('position', 'idx_categories_position');
            $table->index('is_active', 'idx_categories_is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};