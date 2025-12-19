<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Сначала мигрируем данные
        $reviews = DB::table('reviews')->get();
        
        foreach ($reviews as $review) {
            // Создаем переводы для каждого отзыва
            if (isset($review->name) && $review->name) {
                DB::table('review_translations')->insert([
                    'review_id' => $review->id,
                    'locale' => $review->locale ?? 'ru',
                    'code' => 'name',
                    'value' => $review->name,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at,
                ]);
            }
            
            if (isset($review->text) && $review->text) {
                DB::table('review_translations')->insert([
                    'review_id' => $review->id,
                    'locale' => $review->locale ?? 'ru',
                    'code' => 'text',
                    'value' => $review->text,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at,
                ]);
            }
            
            if (isset($review->photo) && $review->photo) {
                DB::table('review_translations')->insert([
                    'review_id' => $review->id,
                    'locale' => $review->locale ?? 'ru',
                    'code' => 'photo',
                    'value' => $review->photo,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at,
                ]);
            }
            
            if (isset($review->logo) && $review->logo) {
                DB::table('review_translations')->insert([
                    'review_id' => $review->id,
                    'locale' => $review->locale ?? 'ru',
                    'code' => 'logo',
                    'value' => $review->logo,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at,
                ]);
            }
        }
        
        // Теперь удаляем поля
        Schema::table('reviews', function (Blueprint $table) {
            // Удаляем поля, которые теперь в translations
            $table->dropColumn(['name', 'text', 'locale', 'photo', 'logo']);
        });
    }

    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->text('text')->after('name');
            $table->string('photo')->nullable()->after('rating');
            $table->string('logo')->nullable()->after('photo');
            $table->string('locale', 10)->default('ru')->after('logo');
        });
        
        // Восстанавливаем данные из translations
        $reviews = DB::table('reviews')->get();
        foreach ($reviews as $review) {
            $translations = DB::table('review_translations')
                ->where('review_id', $review->id)
                ->get()
                ->groupBy('locale');
            
            // Берем первую доступную локализацию
            foreach ($translations as $locale => $trans) {
                $name = $trans->where('code', 'name')->first()?->value;
                $text = $trans->where('code', 'text')->first()?->value;
                $photo = $trans->where('code', 'photo')->first()?->value;
                $logo = $trans->where('code', 'logo')->first()?->value;
                
                if ($name || $text) {
                    DB::table('reviews')
                        ->where('id', $review->id)
                        ->update([
                            'name' => $name ?? '',
                            'text' => $text ?? '',
                            'locale' => $locale,
                            'photo' => $photo,
                            'logo' => $logo,
                        ]);
                    break;
                }
            }
        }
    }
};

