<?php

namespace App\Console\Commands;

use App\Models\Review;
use Illuminate\Console\Command;

class CheckReviews extends Command
{
    protected $signature = 'reviews:check';
    protected $description = 'Check reviews in database';

    public function handle()
    {
        $this->info('=== Checking Reviews ===');
        $this->newLine();
        
        $total = Review::count();
        $this->info("Total reviews: {$total}");
        
        if ($total > 0) {
            $this->newLine();
            $this->info('All reviews:');
            $reviews = Review::all();
            foreach ($reviews as $review) {
                $this->line("  ID: {$review->id} | Name: {$review->name} | Locale: {$review->locale} | Active: " . ($review->is_active ? 'Yes' : 'No'));
            }
            
            $this->newLine();
            $this->info('Active reviews by locale:');
            $locales = ['ru', 'en', 'uk', 'es', 'zh'];
            foreach ($locales as $locale) {
                $count = Review::where('locale', $locale)
                    ->where('is_active', true)
                    ->count();
                $this->line("  {$locale}: {$count}");
            }
        } else {
            $this->warn('No reviews found in database!');
        }
        
        return 0;
    }
}

