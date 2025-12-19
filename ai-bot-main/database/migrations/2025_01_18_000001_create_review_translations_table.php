<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('review_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->onDelete('cascade');
            $table->string('locale', 10);
            $table->string('code', 50); // name, text, photo, logo
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['review_id', 'locale', 'code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('review_translations');
    }
};

