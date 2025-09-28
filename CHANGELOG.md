# Changelog

All notable changes to `gls-map-widget` will be documented in this file.

## v1.0.1 - 2025-09-28

**Full Changelog**: https://github.com/WebSystem-studio/laravel-gls-map-widget/compare/v1.0.0...v1.0.1

## v1.0.0 - 2025-09-28

**Full Changelog**: https://github.com/WebSystem-studio/laravel-gls-map-widget/commits/v1.0.0

## [1.0.0] - 2025-09-28

### Added

- 🎉 Initial release of Laravel GLS Map Widget package
- 🗺️ Full GLS ParcelShop and Locker finder widget integration
- 🌍 Support for all 21 GLS supported countries
- 📍 Advanced geolocation functionality with browser GPS and reverse geocoding
- 🎛️ Comprehensive filtering options (parcel-shop, parcel-locker, drop-off points)
- 🎨 Customizable widget dimensions and styling
- 📱 Both embedded widget and dialog modal modes
- 🔧 Extensive configuration options
- 🧪 Complete PEST test suite (39 tests with 173 assertions)
- 📚 Comprehensive documentation with examples
- ⚡ Artisan commands for easy setup and information display

### Features

- **Blade Component**: `<x-gls-map>` component with full attribute support
- **Multi-Country Support**: AT, BE, BG, CZ, DE, DK, ES, FI, FR, GR, HR, HU, IT, LU, NL, PL, PT, RO, RS, SI, SK
- **Geolocation**: Browser-based location detection with OpenStreetMap Nominatim integration
- **Event System**: JavaScript event handling for delivery point selection
- **Caching**: Smart caching for geolocation data
- **Error Handling**: Comprehensive exception handling and fallback mechanisms
- **TypeScript Support**: Modern ES6 modules with full browser compatibility

### Configuration

- Country-specific GLS JavaScript endpoints
- Language mappings for all supported countries
- Geolocation service configuration
- Default widget settings
- Filter type definitions

### Artisan Commands

- `gls-map-widget:install` - Quick installation and setup
- `gls-map-widget:info` - Display package information and usage examples

### Testing

- Unit tests for component logic and validation
- Feature tests for Blade rendering and integration
- Configuration validation tests
- Service provider registration tests
- Architecture tests for code quality
