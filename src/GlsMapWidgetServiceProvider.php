<?php

namespace WebSystem\GlsMapWidget;

use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use WebSystem\GlsMapWidget\Commands\GlsMapWidgetCommand;
use WebSystem\GlsMapWidget\Commands\InstallCommand;
use WebSystem\GlsMapWidget\Components\GlsMapComponent;

class GlsMapWidgetServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('gls-map-widget')
            ->hasConfigFile()
            ->hasViews()
            ->hasAssets()
            ->hasViewComponent('gls', GlsMapComponent::class)
            ->hasCommands([
                GlsMapWidgetCommand::class,
                InstallCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        // Register the Blade component with a custom tag
        Blade::component('gls-map', GlsMapComponent::class);
    }

    public function packageBooted(): void
    {
        // Publish JavaScript assets
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->package->basePath('/../resources/js') => public_path('vendor/gls-map-widget/js'),
            ], "{$this->package->shortName()}-assets");
        }
    }
}
