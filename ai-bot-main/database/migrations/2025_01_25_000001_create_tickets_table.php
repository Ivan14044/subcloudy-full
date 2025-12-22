<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('external_channel')->nullable()->comment('web, telegram или null');
            $table->string('telegram_chat_id')->nullable()->comment('ID чата в Telegram');
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            $table->string('subject')->nullable()->comment('Тема обращения');
            $table->string('guest_email')->nullable()->comment('Email для неавторизованных пользователей');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

