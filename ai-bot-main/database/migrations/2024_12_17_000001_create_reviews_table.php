<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Имя автора отзыва
            $table->text('text'); // Текст отзыва
            $table->integer('rating')->default(5); // Рейтинг (1-5)
            $table->string('photo')->nullable(); // Фото автора
            $table->string('logo')->nullable(); // Логотип компании
            $table->string('locale', 10)->default('ru'); // Язык
            $table->integer('order')->default(0); // Порядок отображения
            $table->boolean('is_active')->default(true); // Активен ли отзыв
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};

