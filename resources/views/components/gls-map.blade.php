{{-- GLS Map Widget Blade Component --}}
<div class="gls-map-widget-container" style="{{ $getContainerStyles() }}">
    @if($widgetType === 'gls-dpm')
        <{{ $widgetType }} {!! $getWidgetAttributesString() !!}></{{ $widgetType }}>
    @else
        <{{ $widgetType }} {!! $getWidgetAttributesString() !!}></{{ $widgetType }}>
    @endif
</div>

{{-- Load the country-specific GLS script --}}
<script type="module" src="{{ $scriptUrl }}" async id="gls-script-{{ $elementId }}"></script>

@if($useGeolocation)
    {{-- Include geolocation functionality --}}
    <script type="module">
        // Geolocation configuration
        window.glsMapConfig = window.glsMapConfig || {};
        window.glsMapConfig['{{ $elementId }}'] = @json($getGeolocationConfig());

        // Import and initialize simple geolocation
        @if(file_exists(public_path('vendor/gls-map-widget/js/gls-geolocation.js')))
            import('{{ asset('vendor/gls-map-widget/js/gls-geolocation.js') }}').then(module => {
                if (module.initGeoLocation) {
                    const config = window.glsMapConfig['{{ $elementId }}'];
                    config.countryMapping = config.countryLanguageMapping; // Fix naming
                    module.initGeoLocation('{{ $elementId }}', config);
                }
            }).catch(error => {
                console.warn('Could not load GLS geolocation module:', error);
            });
        @else
            // Simple fallback geolocation with postal code search
            (function() {
                const config = window.glsMapConfig['{{ $elementId }}'];
                if (!config.enabled || !navigator.geolocation) return;

                navigator.geolocation.getCurrentPosition(
                    async function(pos) {
                        try {
                            const url = `https://nominatim.openstreetmap.org/reverse?lat=${pos.coords.latitude}&lon=${pos.coords.longitude}&format=json&addressdetails=1`;
                            const data = await fetch(url).then(r => r.json());
                            const country = data.address?.country_code?.toUpperCase();
                            const postalCode = data.address?.postcode;
                            const city = data.address?.city || data.address?.town || data.address?.village;

                            if (config.supportedCountries.includes(country)) {
                                const element = document.getElementById('{{ $elementId }}');
                                element.setAttribute('country', country.toLowerCase());
                                element.setAttribute('language', config.countryLanguageMapping[country]?.toLowerCase());

                                // Wait for widget to load and trigger search
                                if (postalCode || city) {
                                    // Wait for widget (max 10 seconds)
                                    for (let i = 0; i < 50; i++) {
                                        await new Promise(resolve => setTimeout(resolve, 200));

                                        // Try to find search input
                                        const searchInput = element.shadowRoot?.querySelector('input[type="search"]') ||
                                                           element.querySelector('input[type="search"]') ||
                                                           element.querySelector('input[type="text"]');

                                        if (searchInput) {
                                            searchInput.value = postalCode || city;
                                            searchInput.focus();
                                            searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                                            searchInput.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));

                                            // Show success notification
                                            const notification = document.createElement('div');
                                            notification.style.cssText = `
                                                position: fixed; top: 20px; right: 20px; z-index: 10000;
                                                background: #16a34a; color: white; padding: 12px 16px;
                                                border-radius: 6px; font-size: 14px; max-width: 350px;
                                                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                                            `;
                                            notification.textContent = `🎯 Automaticky vyhľadané pre ${postalCode || city}, ${country}`;
                                            document.body.appendChild(notification);
                                            setTimeout(() => notification.remove(), 5000);
                                            break;
                                        }
                                    }
                                } else {
                                    // Show country detection only
                                    const notification = document.createElement('div');
                                    notification.style.cssText = `
                                        position: fixed; top: 20px; right: 20px; z-index: 10000;
                                        background: #2563eb; color: white; padding: 12px 16px;
                                        border-radius: 6px; font-size: 14px; max-width: 350px;
                                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                                    `;
                                    notification.textContent = `🌍 Detekovaná krajina: ${country}. Zadajte PSČ do mapy.`;
                                    document.body.appendChild(notification);
                                    setTimeout(() => notification.remove(), 5000);
                                }
                            }
                        } catch (error) {
                            console.warn('Geolokácia zlyhala:', error);
                        }
                    },
                    () => console.warn('Geolokácia zamietnutá')
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