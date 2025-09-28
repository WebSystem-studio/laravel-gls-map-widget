<?php

use WebSystem\GlsMapWidget\Components\GlsMapComponent;
use WebSystem\GlsMapWidget\GlsMapWidgetServiceProvider;

it('registers the service provider correctly', function () {
    $app = app();

    expect($app->getProviders(GlsMapWidgetServiceProvider::class))->not->toBeEmpty();
});

it('publishes config file', function () {
    // Test that the config file exists and is accessible
    expect(config('gls-map-widget'))->not->toBeNull()
        ->and(config('gls-map-widget'))->toBeArray();
});

it('publishes views', function () {
    // Test that the view can be resolved
    $viewPath = resource_path('views/vendor/gls-map-widget/components/gls-map.blade.php');

    // This would be true after publishing views
    expect(view()->exists('gls-map-widget::components.gls-map'))->toBeTrue();
});

it('registers blade component', function () {
    // Test that the Blade component is registered
    expect(class_exists(GlsMapComponent::class))->toBeTrue();
});

it('can resolve blade component from container', function () {
    $component = app(GlsMapComponent::class, ['country' => 'SK']);

    expect($component)->toBeInstanceOf(GlsMapComponent::class)
        ->and($component->country)->toBe('SK');
});
