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
        try {
            Schema::table('tickets', function (Blueprint $table) {
                $table->index('status');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('tickets', function (Blueprint $table) {
                $table->index('external_channel');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('tickets', function (Blueprint $table) {
                $table->index('updated_at');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('tickets', function (Blueprint $table) {
                $table->index(['user_id', 'status']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('tickets', function (Blueprint $table) {
                $table->index(['guest_email', 'session_token']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('tickets', function (Blueprint $table) {
                $table->index('telegram_chat_id');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('ticket_messages', function (Blueprint $table) {
                $table->index(['ticket_id', 'created_at']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('ticket_messages', function (Blueprint $table) {
                $table->index(['ticket_id', 'sender_type']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('ticket_messages', function (Blueprint $table) {
                $table->index('source');
            });
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['external_channel']);
            $table->dropIndex(['updated_at']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['guest_email', 'session_token']);
            $table->dropIndex(['telegram_chat_id']);
        });

        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->dropIndex(['ticket_id', 'created_at']);
            $table->dropIndex(['ticket_id', 'sender_type']);
            $table->dropIndex(['source']);
        });
    }
};

