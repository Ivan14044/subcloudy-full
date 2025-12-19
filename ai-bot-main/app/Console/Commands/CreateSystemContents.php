<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Content;

class CreateSystemContents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contents:create-system {--force : Update existing contents}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create system content templates (homepage_reviews, saving_on_subscriptions)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating system content templates...');

        $contentsConfig = config('contents', []);
        $force = $this->option('force');

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($contentsConfig as $code => $config) {
            $existing = Content::where('code', $code)->first();

            if ($existing) {
                if ($force) {
                    $existing->update([
                        'name' => ucfirst(str_replace('_', ' ', $code)),
                        'is_system' => true,
                    ]);
                    $this->info("Updated: {$code}");
                    $updated++;
                } else {
                    $this->warn("Content '{$code}' already exists. Skipping... (use --force to update)");
                    $skipped++;
                }
                continue;
            }

            Content::create([
                'name' => ucfirst(str_replace('_', ' ', $code)),
                'code' => $code,
                'is_system' => true,
            ]);

            $this->info("Created: {$code}");
            $created++;
        }

        $this->info("\nDone! Created: {$created}, Updated: {$updated}, Skipped: {$skipped}");

        return Command::SUCCESS;
    }
}

