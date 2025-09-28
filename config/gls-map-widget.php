<?php

// config for WebSystem/GlsMapWidget
return [

    /*
    |--------------------------------------------------------------------------
    | GLS Widget Country Endpoints
    |--------------------------------------------------------------------------
    |
    | These are the JavaScript URLs for each supported GLS country.
    | The widget will load the appropriate script based on the country attribute.
    |
    */
    'country_endpoints' => [
        'AT' => 'https://map.gls-austria.com/widget/gls-dpm.js',
        'BE' => 'https://map.gls-belgium.com/widget/gls-dpm.js',
        'BG' => 'https://map.gls-bulgaria.com/widget/gls-dpm.js',
        'CZ' => 'https://map.gls-czech.com/widget/gls-dpm.js',
        'DE' => 'https://map.gls-germany.com/widget/gls-dpm.js',
        'DK' => 'https://map.gls-denmark.com/widget/gls-dpm.js',
        'ES' => 'https://map.gls-spain.com/widget/gls-dpm.js',
        'FI' => 'https://map.gls-finland.com/widget/gls-dpm.js',
        'FR' => 'https://map.gls-france.com/widget/gls-dpm.js',
        'GR' => 'https://map.gls-greece.com/widget/gls-dpm.js',
        'HR' => 'https://map.gls-croatia.com/widget/gls-dpm.js',
        'HU' => 'https://map.gls-hungary.com/widget/gls-dpm.js',
        'IT' => 'https://map.gls-italy.com/widget/gls-dpm.js',
        'LU' => 'https://map.gls-luxembourg.com/widget/gls-dpm.js',
        'NL' => 'https://map.gls-netherlands.com/widget/gls-dpm.js',
        'PL' => 'https://map.gls-poland.com/widget/gls-dpm.js',
        'PT' => 'https://map.gls-portugal.com/widget/gls-dpm.js',
        'RO' => 'https://map.gls-romania.com/widget/gls-dpm.js',
        'RS' => 'https://map.gls-serbia.com/widget/gls-dpm.js',
        'SI' => 'https://map.gls-slovenia.com/widget/gls-dpm.js',
        'SK' => 'https://map.gls-slovakia.com/widget/gls-dpm.js',
    ],

    /*
    |--------------------------------------------------------------------------
    | Country to Language Mapping
    |--------------------------------------------------------------------------
    |
    | Default language mapping for each country.
    | This is used when no explicit language is provided.
    |
    */
    'country_language_mapping' => [
        'AT' => 'DE',
        'BE' => 'FR',
        'BG' => 'BG',
        'CZ' => 'CS',
        'DE' => 'DE',
        'DK' => 'EN',
        'ES' => 'ES',
        'FI' => 'EN',
        'FR' => 'FR',
        'GR' => 'EN',
        'HR' => 'HR',
        'HU' => 'HU',
        'IT' => 'IT',
        'LU' => 'FR',
        'NL' => 'EN',
        'PL' => 'PL',
        'PT' => 'EN',
        'RO' => 'RO',
        'RS' => 'SR',
        'SI' => 'SL',
        'SK' => 'SK',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    |
    | List of all supported language codes for the GLS widget.
    |
    */
    'supported_languages' => [
        'CS', 'HR', 'HU', 'RO', 'SR', 'SL', 'SK', 'PL', 'EN', 'DE', 'FR', 'ES', 'IT', 'BG'
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Countries
    |--------------------------------------------------------------------------
    |
    | List of all supported country codes for the GLS widget.
    |
    */
    'supported_countries' => [
        'AT', 'BE', 'BG', 'CZ', 'DE', 'DK', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IT', 'LU', 'NL', 'PL', 'PT', 'RO', 'RS', 'SI', 'SK'
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Default configuration values for the widget.
    |
    */
    'defaults' => [
        'width' => '100%',
        'height' => '600px',
        'country' => 'SK',
        'language' => null, // Will be auto-determined from country if not set
        'use_geolocation' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Geolocation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for geolocation functionality.
    |
    */
    'geolocation' => [
        'reverse_geocoding_service' => 'nominatim', // Options: 'nominatim', 'custom'
        'nominatim_endpoint' => 'https://nominatim.openstreetmap.org/reverse',
        'timeout_ms' => 10000, // Geolocation timeout in milliseconds
        'cache_duration' => 3600, // Cache duration in seconds (1 hour)
    ],

    /*
    |--------------------------------------------------------------------------
    | Widget Element Types
    |--------------------------------------------------------------------------
    |
    | Supported widget element types.
    |
    */
    'widget_types' => [
        'widget' => 'gls-dpm',           // Embedded widget
        'dialog' => 'gls-dpm-dialog',   // Dialog modal
    ],

    /*
    |--------------------------------------------------------------------------
    | Filter Types
    |--------------------------------------------------------------------------
    |
    | Supported filter types for delivery points.
    |
    */
    'filter_types' => [
        'parcel-shop',
        'parcel-locker',
    ],

];
