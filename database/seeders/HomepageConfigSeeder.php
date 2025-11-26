<?php

namespace Database\Seeders;

use App\Services\HomepageConfigService;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;

class HomepageConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settingsService = app(SettingsService::class);

        // Check if homepage config already exists
        if ($settingsService->has('homepage_config')) {
            $this->command->info('Homepage configuration already exists. Skipping...');
            return;
        }

        $configService = app(HomepageConfigService::class);
        $defaultConfig = $configService->getDefaultConfig();

        $settingsService->set('homepage_config', $defaultConfig, 'json', 'homepage');

        $this->command->info('Homepage configuration seeded successfully.');
    }
}
