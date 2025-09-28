<?php

use WebSystem\GlsMapWidget\Components\GlsMapComponent;
use WebSystem\GlsMapWidget\Exceptions\InvalidCountryException;
use WebSystem\GlsMapWidget\Exceptions\InvalidLanguageException;
use WebSystem\GlsMapWidget\Exceptions\InvalidFilterTypeException;

it('can create a basic GLS map component', function () {
    $component = new GlsMapComponent(country: 'SK');
    
    expect($component->country)->toBe('SK')
        ->and($component->language)->toBe('SK')
        ->and($component->width)->toBe('100%')
        ->and($component->height)->toBe('600px')
        ->and($component->useGeolocation)->toBeFalse()
        ->and($component->dropoffPointsOnly)->toBeFalse()
        ->and($component->filterType)->toBeNull()
        ->and($component->widgetType)->toBe('gls-dpm');
});

it('can create component with all attributes', function () {
    $component = new GlsMapComponent(
        country: 'CZ',
        language: 'EN',
        width: '800px',
        height: '500px',
        filterType: 'parcel-locker',
        dropoffPointsOnly: true,
        useGeolocation: true,
        id: 'custom-id',
        widgetType: 'dialog'
    );
    
    expect($component->country)->toBe('CZ')
        ->and($component->language)->toBe('EN')
        ->and($component->width)->toBe('800px')
        ->and($component->height)->toBe('500px')
        ->and($component->filterType)->toBe('parcel-locker')
        ->and($component->dropoffPointsOnly)->toBeTrue()
        ->and($component->useGeolocation)->toBeTrue()
        ->and($component->elementId)->toBe('custom-id')
        ->and($component->widgetType)->toBe('gls-dpm-dialog');
});

it('auto-determines language from country', function () {
    $component = new GlsMapComponent(country: 'HU');
    expect($component->language)->toBe('HU');
    
    $component = new GlsMapComponent(country: 'CZ');
    expect($component->language)->toBe('CS');
    
    $component = new GlsMapComponent(country: 'RO');
    expect($component->language)->toBe('RO');
});

it('throws exception for invalid country', function () {
    expect(fn() => new GlsMapComponent(country: 'XX'))
        ->toThrow(InvalidCountryException::class);
});

it('throws exception for empty country', function () {
    expect(fn() => new GlsMapComponent(country: ''))
        ->toThrow(InvalidCountryException::class);
});

it('throws exception for invalid language', function () {
    expect(fn() => new GlsMapComponent(country: 'SK', language: 'XX'))
        ->toThrow(InvalidLanguageException::class);
});

it('throws exception for invalid filter type', function () {
    expect(fn() => new GlsMapComponent(country: 'SK', filterType: 'invalid-type'))
        ->toThrow(InvalidFilterTypeException::class);
});

it('accepts valid filter types', function () {
    $component1 = new GlsMapComponent(country: 'SK', filterType: 'parcel-shop');
    expect($component1->filterType)->toBe('parcel-shop');
    
    $component2 = new GlsMapComponent(country: 'SK', filterType: 'parcel-locker');
    expect($component2->filterType)->toBe('parcel-locker');
});

it('generates widget attributes correctly', function () {
    $component = new GlsMapComponent(
        country: 'CZ',
        language: 'EN',
        filterType: 'parcel-locker',
        dropoffPointsOnly: true,
        id: 'test-id'
    );
    
    $attributes = $component->getWidgetAttributes();
    
    expect($attributes)->toHaveKey('id', 'test-id')
        ->and($attributes)->toHaveKey('country', 'cz')
        ->and($attributes)->toHaveKey('language', 'en')
        ->and($attributes)->toHaveKey('filter-type', 'parcel-locker')
        ->and($attributes)->toHaveKey('dropoffpoints-only', 'true');
});

it('generates widget attributes string correctly', function () {
    $component = new GlsMapComponent(
        country: 'SK',
        language: 'SK',
        dropoffPointsOnly: true,
        id: 'test-widget'
    );
    
    $attributeString = $component->getWidgetAttributesString();
    
    expect($attributeString)->toContain('id="test-widget"')
        ->and($attributeString)->toContain('country="sk"')
        ->and($attributeString)->toContain('language="sk"')
        ->and($attributeString)->toContain('dropoffpoints-only');
});

it('generates container styles correctly', function () {
    $component = new GlsMapComponent(
        country: 'SK',
        width: '100%',
        height: '400px'
    );
    
    $styles = $component->getContainerStyles();
    
    expect($styles)->toContain('width: 100%')
        ->and($styles)->toContain('height: 400px');
});

it('generates geolocation config when enabled', function () {
    $component = new GlsMapComponent(
        country: 'SK',
        useGeolocation: true
    );
    
    $config = $component->getGeolocationConfig();
    
    expect($config)->toHaveKey('enabled', true)
        ->and($config)->toHaveKey('reverseGeocodingService')
        ->and($config)->toHaveKey('nominatimEndpoint')
        ->and($config)->toHaveKey('countryLanguageMapping')
        ->and($config)->toHaveKey('supportedCountries')
        ->and($config)->toHaveKey('countryEndpoints');
});

it('returns empty geolocation config when disabled', function () {
    $component = new GlsMapComponent(
        country: 'SK',
        useGeolocation: false
    );
    
    $config = $component->getGeolocationConfig();
    
    expect($config)->toBeEmpty();
});

it('generates unique element IDs when not provided', function () {
    $component1 = new GlsMapComponent(country: 'SK');
    $component2 = new GlsMapComponent(country: 'SK');
    
    expect($component1->elementId)->not->toBe($component2->elementId)
        ->and($component1->elementId)->toStartWith('gls-map-')
        ->and($component2->elementId)->toStartWith('gls-map-');
});

it('sets correct script URL based on country', function () {
    $component = new GlsMapComponent(country: 'SK');
    expect($component->scriptUrl)->toBe('https://map.gls-slovakia.com/widget/gls-dpm.js');
    
    $component = new GlsMapComponent(country: 'CZ');
    expect($component->scriptUrl)->toBe('https://map.gls-czech.com/widget/gls-dpm.js');
    
    $component = new GlsMapComponent(country: 'HU');
    expect($component->scriptUrl)->toBe('https://map.gls-hungary.com/widget/gls-dpm.js');
});

it('renders the component view', function () {
    $component = new GlsMapComponent(country: 'SK');
    $view = $component->render();
    
    expect($view)->toBeInstanceOf(\Illuminate\Contracts\View\View::class);
});
