<?php

namespace WebSystem\GlsMapWidget\Commands;

use Illuminate\Console\Command;

class GlsMapWidgetCommand extends Command
{
    public $signature = 'gls-map-widget:info';

    public $description = 'Display GLS Map Widget package information and configuration';

    public function handle(): int
    {
        $this->info('🗺️  GLS Map Widget - Laravel Package');
        $this->line('');

        $this->info('Configuration:');
        $config = config('gls-map-widget');

        $this->table(
            ['Setting', 'Value'],
            [
                ['Supported Countries', count($config['supported_countries']).' countries'],
                ['Supported Languages', count($config['supported_languages']).' languages'],
                ['Default Country', $config['defaults']['country']],
                ['Default Width', $config['defaults']['width']],
                ['Default Height', $config['defaults']['height']],
                ['Geolocation Service', $config['geolocation']['reverse_geocoding_service']],
                ['Geolocation Timeout', $config['geolocation']['timeout_ms'].'ms'],
            ]
        );

        $this->line('');
        $this->info('Usage Examples:');
        $this->line('<info>Basic usage:</info>');
        $this->line('<comment><x-gls-map country="SK" /></comment>');
        $this->line('');
        $this->line('<info>With geolocation:</info>');
        $this->line('<comment><x-gls-map :use-geolocation="true" height="500px" /></comment>');
        $this->line('');
        $this->line('<info>Parcel lockers only:</info>');
        $this->line('<comment><x-gls-map country="CZ" filter-type="parcel-locker" /></comment>');

        $this->line('');
        $this->info('📚 Documentation: https://github.com/WebSystem-studio/laravel-gls-map-widget');

        return self::SUCCESS;
    }
}
