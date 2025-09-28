<?php

use Illuminate\Support\Facades\View;

beforeEach(function () {
    // Set up the testing configuration
    config([
        'gls-map-widget.country_endpoints' => [
            'SK' => 'https://map.gls-slovakia.com/widget/gls-dpm.js',
            'CZ' => 'https://map.gls-czech.com/widget/gls-dpm.js',
            'HU' => 'https://map.gls-hungary.com/widget/gls-dpm.js',
        ],
        'gls-map-widget.country_language_mapping' => [
            'SK' => 'SK',
            'CZ' => 'CS',
            'HU' => 'HU',
        ],
        'gls-map-widget.supported_countries' => ['SK', 'CZ', 'HU'],
        'gls-map-widget.supported_languages' => ['SK', 'CS', 'HU', 'EN'],
        'gls-map-widget.filter_types' => ['parcel-shop', 'parcel-locker'],
        'gls-map-widget.widget_types' => [
            'widget' => 'gls-dpm',
            'dialog' => 'gls-dpm-dialog',
        ],
        'gls-map-widget.defaults' => [
            'width' => '100%',
            'height' => '600px',
            'country' => 'SK',
            'use_geolocation' => false,
        ],
        'gls-map-widget.geolocation' => [
            'reverse_geocoding_service' => 'nominatim',
            'nominatim_endpoint' => 'https://nominatim.openstreetmap.org/reverse',
            'timeout_ms' => 10000,
            'cache_duration' => 3600,
        ],
    ]);
});

it('can render gls-map blade component', function () {
    $html = View::make('gls-map-widget::components.gls-map', [
        'country' => 'SK',
        'language' => 'SK',
        'width' => '100%',
        'height' => '600px',
        'filterType' => null,
        'dropoffPointsOnly' => false,
        'useGeolocation' => false,
        'elementId' => 'test-gls-map',
        'widgetType' => 'gls-dpm',
        'scriptUrl' => 'https://map.gls-slovakia.com/widget/gls-dpm.js',
        'getWidgetAttributesString' => fn() => 'id="test-gls-map" country="sk" language="sk"',
        'getContainerStyles' => fn() => 'width: 100%; height: 600px',
        'getGeolocationConfig' => fn() => [],
    ])->render();
    
    expect($html)->toContain('gls-dpm')
        ->and($html)->toContain('id="test-gls-map"')
        ->and($html)->toContain('country="sk"')
        ->and($html)->toContain('language="sk"')
        ->and($html)->toContain('https://map.gls-slovakia.com/widget/gls-dpm.js');
});

it('renders correct widget type for dialog', function () {
    $html = View::make('gls-map-widget::components.gls-map', [
        'country' => 'SK',
        'language' => 'SK',
        'width' => '100%',
        'height' => '600px',
        'filterType' => null,
        'dropoffPointsOnly' => false,
        'useGeolocation' => false,
        'elementId' => 'test-gls-dialog',
        'widgetType' => 'gls-dpm-dialog',
        'scriptUrl' => 'https://map.gls-slovakia.com/widget/gls-dpm.js',
        'getWidgetAttributesString' => fn() => 'id="test-gls-dialog" country="sk"',
        'getContainerStyles' => fn() => 'width: 100%; height: 600px',
        'getGeolocationConfig' => fn() => [],
    ])->render();
    
    expect($html)->toContain('gls-dpm-dialog')
        ->and($html)->toContain('id="test-gls-dialog"');
});

it('includes geolocation script when enabled', function () {
    $html = View::make('gls-map-widget::components.gls-map', [
        'country' => 'SK',
        'language' => 'SK',
        'width' => '100%',
        'height' => '600px',
        'filterType' => null,
        'dropoffPointsOnly' => false,
        'useGeolocation' => true,
        'elementId' => 'test-gls-geo',
        'widgetType' => 'gls-dpm',
        'scriptUrl' => 'https://map.gls-slovakia.com/widget/gls-dpm.js',
        'getWidgetAttributesString' => fn() => 'id="test-gls-geo" country="sk"',
        'getContainerStyles' => fn() => 'width: 100%; height: 600px',
        'getGeolocationConfig' => fn() => [
            'enabled' => true,
            'reverseGeocodingService' => 'nominatim',
            'nominatimEndpoint' => 'https://nominatim.openstreetmap.org/reverse',
        ],
    ])->render();
    
    expect($html)->toContain('window.glsMapConfig')
        ->and($html)->toContain('test-gls-geo')
        ->and($html)->toContain('navigator.geolocation');
});

it('does not include geolocation script when disabled', function () {
    $html = View::make('gls-map-widget::components.gls-map', [
        'country' => 'SK',
        'language' => 'SK',
        'width' => '100%',
        'height' => '600px',
        'filterType' => null,
        'dropoffPointsOnly' => false,
        'useGeolocation' => false,
        'elementId' => 'test-gls-no-geo',
        'widgetType' => 'gls-dpm',
        'scriptUrl' => 'https://map.gls-slovakia.com/widget/gls-dpm.js',
        'getWidgetAttributesString' => fn() => 'id="test-gls-no-geo" country="sk"',
        'getContainerStyles' => fn() => 'width: 100%; height: 600px',
        'getGeolocationConfig' => fn() => [],
    ])->render();
    
    expect($html)->not->toContain('window.glsMapConfig')
        ->and($html)->not->toContain('navigator.geolocation');
});

it('includes event handling script for all components', function () {
    $html = View::make('gls-map-widget::components.gls-map', [
        'country' => 'SK',
        'language' => 'SK',
        'width' => '100%',
        'height' => '600px',
        'filterType' => null,
        'dropoffPointsOnly' => false,
        'useGeolocation' => false,
        'elementId' => 'test-events',
        'widgetType' => 'gls-dpm',
        'scriptUrl' => 'https://map.gls-slovakia.com/widget/gls-dpm.js',
        'getWidgetAttributesString' => fn() => 'id="test-events" country="sk"',
        'getContainerStyles' => fn() => 'width: 100%; height: 600px',
        'getGeolocationConfig' => fn() => [],
    ])->render();
    
    expect($html)->toContain('addEventListener(\'change\'')
        ->and($html)->toContain('gls-delivery-point-selected')
        ->and($html)->toContain('test-events');
});

it('includes modal functionality for dialog type', function () {
    $html = View::make('gls-map-widget::components.gls-map', [
        'country' => 'SK',
        'language' => 'SK',
        'width' => '100%',
        'height' => '600px',
        'filterType' => null,
        'dropoffPointsOnly' => false,
        'useGeolocation' => false,
        'elementId' => 'test-modal',
        'widgetType' => 'gls-dpm-dialog',
        'scriptUrl' => 'https://map.gls-slovakia.com/widget/gls-dpm.js',
        'getWidgetAttributesString' => fn() => 'id="test-modal" country="sk"',
        'getContainerStyles' => fn() => 'width: 100%; height: 600px',
        'getGeolocationConfig' => fn() => [],
    ])->render();
    
    expect($html)->toContain('window.glsOpenModal')
        ->and($html)->toContain('showModal');
});

it('does not include modal functionality for widget type', function () {
    $html = View::make('gls-map-widget::components.gls-map', [
        'country' => 'SK',
        'language' => 'SK',
        'width' => '100%',
        'height' => '600px',
        'filterType' => null,
        'dropoffPointsOnly' => false,
        'useGeolocation' => false,
        'elementId' => 'test-no-modal',
        'widgetType' => 'gls-dpm',
        'scriptUrl' => 'https://map.gls-slovakia.com/widget/gls-dpm.js',
        'getWidgetAttributesString' => fn() => 'id="test-no-modal" country="sk"',
        'getContainerStyles' => fn() => 'width: 100%; height: 600px',
        'getGeolocationConfig' => fn() => [],
    ])->render();
    
    expect($html)->not->toContain('window.glsOpenModal')
        ->and($html)->not->toContain('showModal');
});

it('includes container div and basic structure', function () {
    $html = View::make('gls-map-widget::components.gls-map', [
        'country' => 'SK',
        'language' => 'SK',
        'width' => '100%',
        'height' => '600px',
        'filterType' => null,
        'dropoffPointsOnly' => false,
        'useGeolocation' => false,
        'elementId' => 'test-styles',
        'widgetType' => 'gls-dpm',
        'scriptUrl' => 'https://map.gls-slovakia.com/widget/gls-dpm.js',
        'getWidgetAttributesString' => fn() => 'id="test-styles" country="sk"',
        'getContainerStyles' => fn() => 'width: 100%; height: 600px',
        'getGeolocationConfig' => fn() => [],
    ])->render();
    
    expect($html)->toContain('class="gls-map-widget-container"')
        ->and($html)->toContain('style="width: 100%; height: 600px"')
        ->and($html)->toContain('<gls-dpm');
});