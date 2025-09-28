# Laravel GLS Map Widget

[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg?style=flat-square)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11%2B%20%7C%C2%A012%2B-red.svg?style=flat-square)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](LICENSE.md)
[![Tests](https://img.shields.io/badge/tests-46%20passed-brightgreen.svg?style=flat-square)](#testing)

A Laravel Blade component for integrating GLS ParcelShop and GLS Locker finder widget with OpenStreetMap. This package provides an easy-to-use component that supports all GLS widget features including geolocation, country/language detection, and various filtering options.

## Features

- 🗺️ **Full GLS Widget Integration** - Supports both embedded widget and dialog modes
- 🌍 **Multi-Country Support** - Works with all GLS supported countries (21 countries)
- 📍 **Automatic Geolocation** - Optional browser-based location detection with fallback
- 🎛️ **Flexible Filtering** - Filter by parcel shops, lockers, or drop-off points only
- 🎨 **Customizable** - Configurable dimensions, styling, and behavior
- 🧪 **Well Tested** - Comprehensive PEST test suite
- ⚡ **Easy to Use** - Simple Blade component integration

## Supported Countries

Austria, Belgium, Bulgaria, Czech Republic, Germany, Denmark, Spain, Finland, France, Greece, Croatia, Hungary, Italy, Luxembourg, Netherlands, Poland, Portugal, Romania, Serbia, Slovenia, Slovakia.

## Installation

You can install the package via composer:

```bash
composer require websystem-sro/gls-map-widget
```

Publish the config file with:

```bash
php artisan vendor:publish --tag="gls-map-widget-config"
```

Publish the JavaScript assets:

```bash
php artisan vendor:publish --tag="gls-map-widget-assets"
```

Optionally, you can publish the views:

```bash
php artisan vendor:publish --tag="gls-map-widget-views"
```

## Quick Start

### Basic Usage

```php
// Basic widget for Slovakia
<x-gls-map country="SK" />

// With custom dimensions
<x-gls-map country="CZ" width="800px" height="500px" />

// With geolocation (auto-detects user's country)
<x-gls-map :use-geolocation="true" />
```

### Advanced Usage

```php
// Parcel lockers only with English language
<x-gls-map 
    country="HU" 
    language="EN" 
    filter-type="parcel-locker" 
    width="100%" 
    height="600px" 
/>

// Drop-off points only
<x-gls-map 
    country="PL" 
    :dropoffpoints-only="true" 
    id="custom-gls-widget" 
/>

// Dialog modal version
<x-gls-map 
    country="DE" 
    widget-type="dialog" 
    id="gls-modal" 
/>

<!-- Button to open modal -->
<button onclick="glsOpenModal('gls-modal')">
    Select Delivery Point
</button>
```

## Component Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `country` | string | `SK` | Two-letter country code (AT, BE, BG, CZ, DE, DK, ES, FI, FR, GR, HR, HU, IT, LU, NL, PL, PT, RO, RS, SI, SK) |
| `language` | string | auto | Two-letter language code (CS, HR, HU, RO, SR, SL, SK, PL, EN, DE, FR, ES, IT, BG) |
| `width` | string | `100%` | Widget width (CSS value) |
| `height` | string | `600px` | Widget height (CSS value) |
| `filter-type` | string | null | Filter by type: `parcel-shop` or `parcel-locker` |
| `dropoffpoints-only` | boolean | `false` | Show only drop-off points |
| `use-geolocation` | boolean | `false` | Enable automatic location detection |
| `id` | string | auto | Custom element ID |
| `widget-type` | string | `widget` | Widget type: `widget` or `dialog` |

## Event Handling

The component dispatches events when a delivery point is selected:

```javascript
// Listen for delivery point selection
document.addEventListener('gls-delivery-point-selected', function(event) {
    console.log('Selected delivery point:', event.detail.deliveryPoint);
    
    // Access delivery point data
    const point = event.detail.deliveryPoint;
    console.log('ID:', point.id);
    console.log('Name:', point.name);
    console.log('Address:', point.contact.address);
    console.log('City:', point.contact.city);
    console.log('Postal Code:', point.contact.postalCode);
});

// Listen for geolocation updates
document.addEventListener('gls-location-updated', function(event) {
    console.log('Location updated to:', event.detail.countryCode);
});
```

## Geolocation

The package includes advanced geolocation functionality:

```php
<x-gls-map :use-geolocation="true" height="500px" />
```

### How it works:

1. **Browser Geolocation** - Requests user's GPS coordinates
2. **Reverse Geocoding** - Determines country using OpenStreetMap Nominatim
3. **Auto-Configuration** - Updates widget to show appropriate country's delivery points
4. **Fallback** - Gracefully falls back to default country if geolocation fails
5. **Caching** - Caches location data to avoid repeated API calls

### Privacy & Permissions

- Geolocation only works with user consent
- No coordinate data is sent to your server
- Uses OpenStreetMap's free Nominatim service
- Respects browser privacy settings

## Configuration

The config file provides extensive customization options:

```php
return [
    // Country-specific script endpoints
    'country_endpoints' => [
        'SK' => 'https://map.gls-slovakia.com/widget/gls-dpm.js',
        'CZ' => 'https://map.gls-czech.com/widget/gls-dpm.js',
        // ... all supported countries
    ],

    // Default language for each country
    'country_language_mapping' => [
        'SK' => 'SK',
        'CZ' => 'CS',
        // ... mappings for all countries
    ],

    // Default widget settings
    'defaults' => [
        'width' => '100%',
        'height' => '600px',
        'country' => 'SK',
        'use_geolocation' => false,
    ],

    // Geolocation configuration
    'geolocation' => [
        'reverse_geocoding_service' => 'nominatim',
        'nominatim_endpoint' => 'https://nominatim.openstreetmap.org/reverse',
        'timeout_ms' => 10000,
        'cache_duration' => 3600,
    ],
];
```

## Examples

### E-commerce Checkout Integration

```php
<div class="delivery-selection">
    <h3>Select Delivery Point</h3>
    
    <x-gls-map 
        :use-geolocation="true"
        width="100%" 
        height="400px"
        id="checkout-delivery-map" 
    />
    
    <input type="hidden" name="selected_delivery_point" id="delivery-point-input">
</div>

<script>
document.addEventListener('gls-delivery-point-selected', function(event) {
    const deliveryPoint = event.detail.deliveryPoint;
    
    // Update hidden form field
    document.getElementById('delivery-point-input').value = JSON.stringify(deliveryPoint);
    
    // Update UI
    document.getElementById('selected-point-info').innerHTML = `
        <strong>${deliveryPoint.name}</strong><br>
        ${deliveryPoint.contact.address}<br>
        ${deliveryPoint.contact.postalCode} ${deliveryPoint.contact.city}
    `;
});
</script>
```

### Modal Integration

```php
<button class="btn btn-primary" onclick="glsOpenModal('delivery-modal')">
    📦 Choose Pickup Point
</button>

<x-gls-map 
    country="CZ" 
    widget-type="dialog" 
    id="delivery-modal"
    filter-type="parcel-locker"
/>
```

### Multi-Language Support

```php
@switch(app()->getLocale())
    @case('cs')
        <x-gls-map country="CZ" language="CS" />
        @break
    @case('sk')
        <x-gls-map country="SK" language="SK" />
        @break
    @default
        <x-gls-map country="CZ" language="EN" />
@endswitch
```

## Browser Compatibility

- **Modern Browsers**: Chrome 70+, Firefox 70+, Safari 12+, Edge 79+
- **Geolocation**: Requires HTTPS in production
- **JavaScript**: ES6 modules supported

## Performance

- **Lazy Loading**: GLS scripts load only when needed
- **Caching**: Geolocation results cached for 1 hour
- **Lightweight**: ~15KB additional JavaScript
- **CDN**: Uses GLS official CDN endpoints

## Troubleshooting

### Common Issues

**Geolocation not working:**
- Ensure your site uses HTTPS in production
- Check browser permissions
- Verify Nominatim endpoint is accessible

**Widget not loading:**
- Check console for JavaScript errors
- Verify country code is supported
- Ensure GLS scripts are accessible

**Styling issues:**
- Publish and customize views if needed
- Check for CSS conflicts
- Use browser developer tools to debug

### Debug Mode

```php
<x-gls-map 
    country="SK" 
    :use-geolocation="true"
    id="debug-widget"
/>

<script>
// Enable debugging
console.log('GLS Config:', window.glsMapConfig);

// Listen to all events
document.addEventListener('gls-location-updated', console.log);
document.addEventListener('gls-delivery-point-selected', console.log);
</script>
```

## API Reference

### GlsMapComponent Methods

```php
// Get widget HTML attributes
$component->getWidgetAttributes(): array

// Get widget attributes as HTML string  
$component->getWidgetAttributesString(): string

// Get container CSS styles
$component->getContainerStyles(): string

// Get geolocation configuration
$component->getGeolocationConfig(): array
```

### JavaScript API

```javascript
// Initialize geolocation manually
window.initializeGlsGeolocation(elementId);

// Access geolocation instance
const geolocation = new window.GlsGeolocation(config);

// Open modal programmatically
window.glsOpenModal(elementId);
```

## Testing

### Running All Tests

```bash
composer test
```

### Unit Tests Only

```bash
# Run unit tests without browser tests
npm run test-unit
# or
./vendor/bin/pest --exclude-group=browser
```

### Browser Tests (Local Only)

Browser tests require Playwright and are configured to run **only locally** (skipped in CI):

```bash
# Install Playwright browsers (first time only)
npm run install-playwright

# Run browser tests
npm run test-browser
# or
./vendor/bin/pest --group=browser
```

**Note**: Browser tests are configured for local development only:
- Use `->skipOnCi()` modifier to skip in CI environments
- GitHub Actions workflow explicitly excludes browser tests (`--exclude-group=browser`)
- Playwright installation not required in CI/production environments

### Test Types

The package includes comprehensive test coverage:

- **Unit Tests** (39 tests): Component logic, validation, and configuration
- **Feature Tests**: Blade rendering and service provider integration  
- **Browser Tests** (7 tests): Real browser testing with Playwright
  - Widget rendering in different browsers
  - Responsive design testing
  - Dialog functionality
  - Filter types validation
  - Multi-country widget support
  - Smoke testing
  - Visual regression testing

### CI/CD Testing

The package is configured for seamless CI/CD workflows:

- **GitHub Actions**: Automatically runs unit tests only (`--exclude-group=browser`)
- **Multi-Platform**: Tests on Ubuntu and Windows
- **Multi-Version**: PHP 8.3/8.4 + Laravel 11/12 combinations
- **PHPStan**: Static analysis with level 5 compliance
- **No Playwright Required**: Browser tests excluded from CI environments

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

**Made with ❤️ by [WebSystem Studio](https://github.com/WebSystem-studio)**
