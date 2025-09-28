{{-- GLS Map Widget Blade Component --}}
<div class="gls-map-widget-container" style="{{ $getContainerStyles() }}">
    @if($widgetType === 'gls-dpm')
        <{{ $widgetType }} {!! $getWidgetAttributesString() !!}></{{ $widgetType }}>
    @else
        <{{ $widgetType }} {!! $getWidgetAttributesString() !!}></{{ $widgetType }}>
    @endif
</div>

{{-- Load the country-specific GLS script --}}
<script type="module" src="{{ $scriptUrl }}" async></script>

@if($useGeolocation)
    {{-- Include geolocation functionality --}}
    <script type="module">
        // Geolocation configuration
        window.glsMapConfig = window.glsMapConfig || {};
        window.glsMapConfig['{{ $elementId }}'] = @json($getGeolocationConfig());

        // Import and initialize geolocation if enabled
        @if(file_exists(public_path('vendor/gls-map-widget/js/gls-geolocation.js')))
            import('{{ asset('vendor/gls-map-widget/js/gls-geolocation.js') }}').then(module => {
                if (module.initializeGeolocation) {
                    module.initializeGeolocation('{{ $elementId }}');
                }
            }).catch(error => {
                console.warn('Could not load GLS geolocation module:', error);
            });
        @else
            // Fallback geolocation implementation
            (function() {
                const config = window.glsMapConfig['{{ $elementId }}'];
                if (!config.enabled || !navigator.geolocation) return;

                const element = document.getElementById('{{ $elementId }}');
                if (!element) return;

                // Add loading indicator
                element.style.opacity = '0.7';
                element.style.pointerEvents = 'none';

                navigator.geolocation.getCurrentPosition(
                    async function(position) {
                        try {
                            const { latitude, longitude } = position.coords;
                            
                            // Simple reverse geocoding using Nominatim
                            const response = await fetch(
                                `${config.nominatimEndpoint}?lat=${latitude}&lon=${longitude}&format=json&accept-language=en`
                            );
                            
                            if (!response.ok) throw new Error('Geocoding failed');
                            
                            const data = await response.json();
                            const countryCode = data.address?.country_code?.toUpperCase();
                            
                            if (countryCode && config.supportedCountries.includes(countryCode)) {
                                const language = config.countryLanguageMapping[countryCode];
                                const scriptUrl = config.countryEndpoints[countryCode];
                                
                                // Update element attributes
                                element.setAttribute('country', countryCode.toLowerCase());
                                if (language) {
                                    element.setAttribute('language', language.toLowerCase());
                                }
                                
                                // Reload the appropriate country script
                                const newScript = document.createElement('script');
                                newScript.type = 'module';
                                newScript.src = scriptUrl;
                                document.head.appendChild(newScript);
                                
                                console.log(`GLS Widget: Updated to country ${countryCode} with language ${language}`);
                            }
                        } catch (error) {
                            console.warn('GLS Widget: Geolocation reverse geocoding failed:', error);
                        } finally {
                            // Remove loading indicator
                            element.style.opacity = '';
                            element.style.pointerEvents = '';
                        }
                    },
                    function(error) {
                        console.warn('GLS Widget: Geolocation failed:', error.message);
                        // Remove loading indicator
                        element.style.opacity = '';
                        element.style.pointerEvents = '';
                    },
                    {
                        timeout: config.timeout,
                        enableHighAccuracy: true,
                        maximumAge: config.cacheDuration * 1000
                    }
                );
            })();
        @endif
    </script>
@endif

{{-- Event handling script --}}
<script type="module">
    document.addEventListener('DOMContentLoaded', function() {
        const element = document.getElementById('{{ $elementId }}');
        if (!element) return;

        // Listen for delivery point selection
        element.addEventListener('change', function(event) {
            console.log('GLS Delivery Point Selected:', event.detail);
            
            // Dispatch a custom event for easier integration
            const customEvent = new CustomEvent('gls-delivery-point-selected', {
                detail: {
                    widgetId: '{{ $elementId }}',
                    deliveryPoint: event.detail,
                    timestamp: new Date().toISOString()
                },
                bubbles: true
            });
            
            document.dispatchEvent(customEvent);
        });

        @if($widgetType === 'gls-dpm-dialog')
            // Add modal functionality for dialog type
            window.glsOpenModal = window.glsOpenModal || function(elementId) {
                const modalElement = document.getElementById(elementId || '{{ $elementId }}');
                if (modalElement && modalElement.showModal) {
                    modalElement.showModal();
                }
            };
        @endif
    });
</script>

@push('styles')
<style>
    .gls-map-widget-container {
        position: relative;
        overflow: hidden;
    }
    
    .gls-map-widget-container gls-dpm,
    .gls-map-widget-container gls-dpm-dialog {
        width: 100%;
        height: 100%;
        display: block;
    }
    
    /* Loading state styles */
    .gls-map-widget-container[data-loading="true"] {
        opacity: 0.7;
        pointer-events: none;
    }
    
    .gls-map-widget-container[data-loading="true"]::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3498db;
        border-radius: 50%;
        animation: gls-spin 1s linear infinite;
    }
    
    @keyframes gls-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush