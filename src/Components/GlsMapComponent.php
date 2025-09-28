<?php

namespace WebSystem\GlsMapWidget\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\Component;
use WebSystem\GlsMapWidget\Exceptions\InvalidCountryException;
use WebSystem\GlsMapWidget\Exceptions\InvalidFilterTypeException;
use WebSystem\GlsMapWidget\Exceptions\InvalidLanguageException;

class GlsMapComponent extends Component
{
    public string $country;

    public ?string $language;

    public ?string $width;

    public ?string $height;

    public ?string $filterType;

    public bool $dropoffPointsOnly;

    public bool $useGeolocation;

    public string $elementId;

    public string $widgetType;

    public string $scriptUrl;

    /**
     * Create a new component instance.
     */
    public function __construct(
        ?string $country = null,
        ?string $language = null,
        ?string $width = null,
        ?string $height = null,
        ?string $filterType = null,
        bool $dropoffPointsOnly = false,
        bool $useGeolocation = false,
        ?string $id = null,
        string $widgetType = 'widget'
    ) {
        $config = config('gls-map-widget');

        // Set country (with validation)
        $this->country = $this->validateAndSetCountry($country ?? $config['defaults']['country'], $config);

        // Set language (auto-determine from country if not provided)
        $this->language = $this->validateAndSetLanguage($language, $this->country, $config);

        // Set dimensions
        $this->width = $width ?? $config['defaults']['width'];
        $this->height = $height ?? $config['defaults']['height'];

        // Set filter options
        $this->filterType = $this->validateAndSetFilterType($filterType, $config);
        $this->dropoffPointsOnly = $dropoffPointsOnly;

        // Set functionality options
        $this->useGeolocation = $useGeolocation;

        // Set element ID (generate unique if not provided)
        $this->elementId = $id ?? 'gls-map-'.uniqid();

        // Set widget type and script URL
        $this->widgetType = $this->validateAndSetWidgetType($widgetType, $config);
        $this->scriptUrl = $config['country_endpoints'][$this->country];
    }

    /**
     * Validate and set country.
     */
    private function validateAndSetCountry(?string $country, array $config): string
    {
        if (empty($country)) {
            throw new InvalidCountryException('Country is required.');
        }

        $country = strtoupper($country);

        if (! in_array($country, $config['supported_countries'])) {
            throw new InvalidCountryException(
                "Unsupported country: {$country}. Supported countries: ".implode(', ', $config['supported_countries'])
            );
        }

        return $country;
    }

    /**
     * Validate and set language.
     */
    private function validateAndSetLanguage(?string $language, string $country, array $config): ?string
    {
        if (empty($language)) {
            // Auto-determine language from country
            return $config['country_language_mapping'][$country] ?? null;
        }

        $language = strtoupper($language);

        if (! in_array($language, $config['supported_languages'])) {
            throw new InvalidLanguageException(
                "Unsupported language: {$language}. Supported languages: ".implode(', ', $config['supported_languages'])
            );
        }

        return $language;
    }

    /**
     * Validate and set filter type.
     */
    private function validateAndSetFilterType(?string $filterType, array $config): ?string
    {
        if (empty($filterType)) {
            return null;
        }

        if (! in_array($filterType, $config['filter_types'])) {
            throw new InvalidFilterTypeException(
                "Invalid filter type: {$filterType}. Supported types: ".implode(', ', $config['filter_types'])
            );
        }

        return $filterType;
    }

    /**
     * Validate and set widget type.
     */
    private function validateAndSetWidgetType(string $widgetType, array $config): string
    {
        if (! isset($config['widget_types'][$widgetType])) {
            throw new \InvalidArgumentException(
                "Invalid widget type: {$widgetType}. Supported types: ".implode(', ', array_keys($config['widget_types']))
            );
        }

        return $config['widget_types'][$widgetType];
    }

    /**
     * Get widget attributes as array.
     */
    public function getWidgetAttributes(): array
    {
        $attributes = [
            'id' => $this->elementId,
            'country' => strtolower($this->country),
        ];

        if ($this->language) {
            $attributes['language'] = strtolower($this->language);
        }

        if ($this->filterType) {
            $attributes['filter-type'] = $this->filterType;
        }

        if ($this->dropoffPointsOnly) {
            $attributes['dropoffpoints-only'] = 'true';
        }

        return $attributes;
    }

    /**
     * Get widget attributes as HTML string.
     */
    public function getWidgetAttributesString(): string
    {
        $attributes = $this->getWidgetAttributes();
        $attributeStrings = [];

        foreach ($attributes as $key => $value) {
            if ($value === 'true') {
                $attributeStrings[] = $key;
            } else {
                $attributeStrings[] = sprintf('%s="%s"', $key, htmlspecialchars($value, ENT_QUOTES));
            }
        }

        return implode(' ', $attributeStrings);
    }

    /**
     * Get container styles.
     */
    public function getContainerStyles(): string
    {
        $styles = [];

        if ($this->width) {
            $styles[] = "width: {$this->width}";
        }

        if ($this->height) {
            $styles[] = "height: {$this->height}";
        }

        return implode('; ', $styles);
    }

    /**
     * Get geolocation configuration for JavaScript.
     */
    public function getGeolocationConfig(): array
    {
        if (! $this->useGeolocation) {
            return [];
        }

        $config = config('gls-map-widget.geolocation');

        return [
            'enabled' => true,
            'reverseGeocodingService' => $config['reverse_geocoding_service'],
            'nominatimEndpoint' => $config['nominatim_endpoint'],
            'timeout' => $config['timeout_ms'],
            'cacheDuration' => $config['cache_duration'],
            'countryLanguageMapping' => config('gls-map-widget.country_language_mapping'),
            'supportedCountries' => config('gls-map-widget.supported_countries'),
            'countryEndpoints' => config('gls-map-widget.country_endpoints'),
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return ViewFacade::make('gls-map-widget::components.gls-map');
    }
}
