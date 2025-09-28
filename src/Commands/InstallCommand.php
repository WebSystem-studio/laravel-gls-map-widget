<?php

namespace WebSystem\GlsMapWidget\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    public $signature = 'gls-map-widget:install';

    public $description = 'Install and setup the GLS Map Widget package';

    public function handle(): int
    {
        $this->info('🗺️  Installing GLS Map Widget...');
        $this->line('');

        // Publish config
        $this->info('📝 Publishing configuration file...');
        $this->call('vendor:publish', [
            '--tag' => 'gls-map-widget-config',
            '--force' => $this->option('force', false),
        ]);

        // Publish assets
        $this->info('📦 Publishing JavaScript assets...');
        $this->call('vendor:publish', [
            '--tag' => 'gls-map-widget-assets',
            '--force' => $this->option('force', false),
        ]);

        $this->line('');
        $this->info('✅ GLS Map Widget installed successfully!');
        $this->line('');
        
        $this->info('Quick start:');
        $this->line('<comment><x-gls-map country="SK" /></comment>');
        $this->line('');
        
        $this->info('With geolocation:');
        $this->line('<comment><x-gls-map :use-geolocation="true" /></comment>');
        $this->line('');
        
        $this->info('📚 Full documentation: https://github.com/WebSystem-studio/laravel-gls-map-widget');
        
        return self::SUCCESS;
    }
}