<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->name('fk_user_settings_user_id');
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->string('type', 50)->default('string'); // string, boolean, integer, json
            $table->timestamps();
            
            $table->unique(['user_id', 'key'], 'uq_user_settings_user_key');
            $table->index(['user_id', 'key'], 'idx_user_settings_user_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
