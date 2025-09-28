<?php

use WebSystem\GlsMapWidget\Components\GlsMapComponent;

beforeEach(function () {
    // Set up the testing configuration
    config([
        'gls-map-widget.country_endpoints' => [
            'SK' => 'https://map.gls-slovakia.com/widget/gls-dpm.js',
            'CZ' => 'https://map.gls-czech.com/widget/gls-dpm.js',
            'DE' => 'https://map.gls-germany.com/widget/gls-dpm.js',
        ],
        'gls-map-widget.country_language_mapping' => [
            'SK' => 'SK',
            'CZ' => 'CS',
            'DE' => 'DE',
        ],
        'gls-map-widget.supported_countries' => ['SK', 'CZ', 'DE'],
        'gls-map-widget.supported_languages' => ['SK', 'CS', 'DE', 'EN'],
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
    $component = new GlsMapComponent(
        country: 'SK',
        id: 'test-gls-render'
    );

    $html = $component->render()->render();

    expect($html)->toContain('gls-map-widget-container')
        ->and($html)->toContain('gls-dpm')
        ->and($html)->toContain('test-gls-render');
});

it('renders correct widget type for dialog', function () {
    $component = new GlsMapComponent(
        country: 'SK',
        widgetType: 'dialog',
        id: 'test-gls-dialog'
    );

    $html = $component->render()->render();

    expect($html)->toContain('gls-dpm-dialog')
        ->and($html)->toContain('id="test-gls-dialog"');
});

it('includes geolocation script when enabled', function () {
    $component = new GlsMapComponent(
        country: 'SK',
        useGeolocation: true,
        id: 'test-gls-geo'
    );

    $html = $component->render()->render();

    expect($html)->toContain('localStorage.getItem(\'userLocationData\')')
        ->and($html)->toContain('test-gls-geo')
        ->and($html)->toContain('navigator.geolocation');
});

it('does not include geolocation script when disabled', function () {
    $component = new GlsMapComponent(
        country: 'SK',
        useGeolocation: false,
        id: 'test-gls-no-geo'
    );

    $html = $component->render()->render();

    expect($html)->not->toContain('localStorage.getItem(\'userLocationData\')')
        ->and($html)->not->toContain('navigator.geolocation');
});

it('includes event handling script for all components', function () {
    $component = new GlsMapComponent(
        country: 'SK',
        useGeolocation: false,
        id: 'test-gls-events'
    );

    $html = $component->render()->render();

    expect($html)->toContain('document.addEventListener')
        ->and($html)->toContain('gls-delivery-point-selected')
        ->and($html)->toContain('test-gls-events');
});

it('includes modal functionality for dialog type', function () {
    $component = new GlsMapComponent(
        country: 'SK',
        widgetType: 'dialog',
        id: 'test-gls-modal'
    );

    $html = $component->render()->render();

    expect($html)->toContain('window.glsOpenModal')
        ->and($html)->toContain('showModal')
        ->and($html)->toContain('test-gls-modal');
});

it('does not include modal functionality for widget type', function () {
    $component = new GlsMapComponent(
        country: 'SK',
        widgetType: 'widget',
        id: 'test-gls-widget'
    );

    $html = $component->render()->render();

    expect($html)->not->toContain('window.glsOpenModal')
        ->and($html)->not->toContain('showModal');
});

it('includes container div and basic structure', function () {
    $component = new GlsMapComponent(
        country: 'SK',
        id: 'test-structure'
    );

    $html = $component->render()->render();

    expect($html)->toContain('<div class="gls-map-widget-container">')
        ->and($html)->toContain('gls-dpm')
        ->and($html)->toContain('id="test-structure"')
        ->and($html)->toContain('<script type="module"');
});
