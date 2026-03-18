<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->onDelete('cascade')
                ->name('fk_attachments_transaction_id');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->name('fk_attachments_user_id');
            $table->string('filename', 255);
            $table->string('original_filename', 255);
            $table->string('mime_type', 100);
            $table->bigInteger('file_size');
            $table->string('path', 500);
            $table->string('disk', 50)->default('local');
            $table->char('checksum', 64)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['transaction_id', 'created_at'], 'idx_attachments_transaction_created');
            $table->index(['user_id', 'created_at'], 'idx_attachments_user_created');
            $table->index('checksum', 'idx_attachments_checksum');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
