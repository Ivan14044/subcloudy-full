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
        Schema::table('service_accounts', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_users')->nullable()->after('is_active')->comment('Максимальное количество пользователей на аккаунт. NULL = без ограничений');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_accounts', function (Blueprint $table) {
            $table->dropColumn('max_users');
        });
    }
};
