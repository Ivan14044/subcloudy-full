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
        Schema::create('support_template_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_template_id')->constrained()->onDelete('cascade');
            $table->string('locale', 5);
            $table->string('code'); // 'title', 'text'
            $table->text('value');
            $table->timestamps();
            
            $table->index(['support_template_id', 'locale', 'code'], 'st_translations_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_template_translations');
    }
};

