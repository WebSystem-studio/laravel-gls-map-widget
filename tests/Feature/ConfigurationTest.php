<?php

it('loads configuration correctly', function () {
    $config = config('gls-map-widget');

    expect($config)->toBeArray()
        ->and($config)->toHaveKey('country_endpoints')
        ->and($config)->toHaveKey('country_language_mapping')
        ->and($config)->toHaveKey('supported_languages')
        ->and($config)->toHaveKey('supported_countries')
        ->and($config)->toHaveKey('defaults')
        ->and($config)->toHaveKey('geolocation')
        ->and($config)->toHaveKey('widget_types')
        ->and($config)->toHaveKey('filter_types');
});

it('has correct country endpoints', function () {
    $endpoints = config('gls-map-widget.country_endpoints');

    expect($endpoints)->toHaveKey('SK', 'https://map.gls-slovakia.com/widget/gls-dpm.js')
        ->and($endpoints)->toHaveKey('CZ', 'https://map.gls-czech.com/widget/gls-dpm.js')
        ->and($endpoints)->toHaveKey('HU', 'https://map.gls-hungary.com/widget/gls-dpm.js')
        ->and($endpoints)->toHaveKey('RO', 'https://map.gls-romania.com/widget/gls-dpm.js')
        ->and($endpoints)->toHaveKey('HR', 'https://map.gls-croatia.com/widget/gls-dpm.js')
        ->and($endpoints)->toHaveKey('SI', 'https://map.gls-slovenia.com/widget/gls-dpm.js')
        ->and($endpoints)->toHaveKey('RS', 'https://map.gls-serbia.com/widget/gls-dpm.js');
});

it('has correct country language mappings', function () {
    $mappings = config('gls-map-widget.country_language_mapping');

    expect($mappings)->toHaveKey('SK', 'SK')
        ->and($mappings)->toHaveKey('CZ', 'CS')
        ->and($mappings)->toHaveKey('HU', 'HU')
        ->and($mappings)->toHaveKey('RO', 'RO')
        ->and($mappings)->toHaveKey('HR', 'HR')
        ->and($mappings)->toHaveKey('SI', 'SL')
        ->and($mappings)->toHaveKey('RS', 'SR');
});

it('includes all supported countries', function () {
    $countries = config('gls-map-widget.supported_countries');

    expect($countries)->toContain('SK')
        ->and($countries)->toContain('CZ')
        ->and($countries)->toContain('HU')
        ->and($countries)->toContain('RO')
        ->and($countries)->toContain('HR')
        ->and($countries)->toContain('SI')
        ->and($countries)->toContain('RS')
        ->and($countries)->toContain('PL')
        ->and($countries)->toContain('DE')
        ->and($countries)->toContain('AT');
});

it('includes all supported languages', function () {
    $languages = config('gls-map-widget.supported_languages');

    expect($languages)->toContain('SK')
        ->and($languages)->toContain('CS')
        ->and($languages)->toContain('HU')
        ->and($languages)->toContain('RO')
        ->and($languages)->toContain('HR')
        ->and($languages)->toContain('SL')
        ->and($languages)->toContain('SR')
        ->and($languages)->toContain('PL')
        ->and($languages)->toContain('EN')
        ->and($languages)->toContain('DE')
        ->and($languages)->toContain('FR')
        ->and($languages)->toContain('ES')
        ->and($languages)->toContain('IT');
});

it('has correct default values', function () {
    $defaults = config('gls-map-widget.defaults');

    expect($defaults)->toHaveKey('width', '100%')
        ->and($defaults)->toHaveKey('height', '600px')
        ->and($defaults)->toHaveKey('country', 'SK')
        ->and($defaults)->toHaveKey('language', null)
        ->and($defaults)->toHaveKey('use_geolocation', false);
});

it('has geolocation configuration', function () {
    $geolocation = config('gls-map-widget.geolocation');

    expect($geolocation)->toHaveKey('reverse_geocoding_service', 'nominatim')
        ->and($geolocation)->toHaveKey('nominatim_endpoint', 'https://nominatim.openstreetmap.org/reverse')
        ->and($geolocation)->toHaveKey('timeout_ms', 10000)
        ->and($geolocation)->toHaveKey('cache_duration', 3600);
});

it('has correct widget types', function () {
    $widgetTypes = config('gls-map-widget.widget_types');

    expect($widgetTypes)->toHaveKey('widget', 'gls-dpm')
        ->and($widgetTypes)->toHaveKey('dialog', 'gls-dpm-dialog');
});

it('has correct filter types', function () {
    $filterTypes = config('gls-map-widget.filter_types');

    expect($filterTypes)->toContain('parcel-shop')
        ->and($filterTypes)->toContain('parcel-locker');
});
