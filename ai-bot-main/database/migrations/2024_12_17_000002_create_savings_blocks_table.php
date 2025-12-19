<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('savings_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null');
            $table->string('title'); // Заголовок блока
            $table->text('text')->nullable(); // Текст блока
            $table->string('logo')->nullable(); // Логотип
            $table->string('our_price')->nullable(); // Наша цена
            $table->string('normal_price')->nullable(); // Обычная цена
            $table->text('advantage')->nullable(); // Преимущество
            $table->string('locale', 10)->default('ru'); // Язык
            $table->integer('order')->default(0); // Порядок отображения
            $table->boolean('is_active')->default(true); // Активен ли блок
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('savings_blocks');
    }
};

