<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdraw', 'transfer_out', 'transfer_in']);
            $table->decimal('amount', 10, 2);
            $table->text('comment')->nullable();
            $table->foreignId('related_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
            // Индексы для улучшения производительности
            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
