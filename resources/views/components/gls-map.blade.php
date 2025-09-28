{{-- GLS Map Widget Blade Component --}}
<div class="gls-map-widget-container">
    <{{ $widgetType }} {!! $widgetAttributesString !!}></{{ $widgetType }}>
</div>

{{-- Load the country-specific GLS script --}}
<script type="module" src="{{ $scriptUrl }}" async id="gls-script-{{ $elementId }}"></script>

@if($useGeolocation)
<script>
    // Get user location data (country code + postal code)
    document.addEventListener('DOMContentLoaded', function() {
        const elementId = '{{ $elementId }}';
        const element = document.getElementById(elementId);

        if (!element) return;

        // Check if already saved
        const saved = localStorage.getItem('userLocationData');
        if (saved) {
            const data = JSON.parse(saved);
            console.log('🎯 Using cached location - Country Code:', data.country_code);
            console.log('🎯 Using cached location - Postal Code (PSČ):', data.postcode);

            // Apply cached data to widget
            applyLocationToWidget(element, data.country_code, data.postcode);
            return;
        }

        // Get new location
        if ('geolocation' in navigator) {
            console.log('📍 Requesting geolocation permission...');
            navigator.geolocation.getCurrentPosition(async function(position) {
                try {
                    console.log('✅ Geolocation obtained:', position.coords);
                    const response = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.coords.latitude}&lon=${position.coords.longitude}&addressdetails=1`
                    );
                    const data = await response.json();
                    console.log('🗺️ Reverse geocoding result:', data);

                    const country_code = data.address?.country_code?.toUpperCase() || 'unknown';
                    const postcode = data.address?.postcode || 'unknown';

                    // Save to localStorage
                    localStorage.setItem('userLocationData', JSON.stringify({
                        country_code: country_code,
                        postcode: postcode,
                        saved_at: new Date().toISOString()
                    }));

                    console.log('💾 Saved to localStorage - Country Code:', country_code);
                    console.log('💾 Saved to localStorage - Postal Code (PSČ):', postcode);

                    // Apply to widget
                    applyLocationToWidget(element, country_code, postcode);
                } catch (error) {
                    console.error('❌ Error getting location data:', error);
                }
            }, function(error) {
                console.error('❌ Geolocation denied/failed:', error);
            });
        } else {
            console.error('❌ Geolocation not supported by browser');
        }

        function applyLocationToWidget(element, countryCode, postalCode) {
            // Update widget attributes
            element.setAttribute('country', countryCode.toLowerCase());

            // Set language based on country
            const languageMapping = {
                'SK': 'sk', 'CZ': 'cs', 'PL': 'pl', 'HU': 'hu', 'RO': 'ro',
                'HR': 'hr', 'SI': 'sl', 'RS': 'sr', 'BG': 'bg',
                'DE': 'de', 'AT': 'de', 'FR': 'fr', 'IT': 'it', 'ES': 'es',
                'NL': 'en', 'BE': 'en', 'LU': 'en', 'DK': 'en', 'FI': 'en', 'GR': 'en', 'PT': 'en'
            };
            element.setAttribute('language', languageMapping[countryCode] || 'en');

            // Wait for widget to load and trigger postal code search
            if (postalCode && postalCode !== 'unknown') {
                setTimeout(() => {
                    triggerPostalCodeSearch(element, postalCode, countryCode);
                }, 2000); // Wait 2 seconds for widget to fully load
            }
        }

        function triggerPostalCodeSearch(element, postalCode, countryCode) {
            // Try to find search input in widget
            const selectors = [
                'input[type="search"]',
                'input[type="text"]',
                'input[placeholder*="PSČ"]',
                'input[placeholder*="postcode"]',
                'input[placeholder*="ZIP"]',
                '.search-input'
            ];

            let attempts = 0;
            const maxAttempts = 25; // Try for 5 seconds (25 * 200ms)

            function attemptSearch() {
                attempts++;

                for (const selector of selectors) {
                    // Try shadow DOM first
                    const shadowInput = element.shadowRoot?.querySelector(selector);
                    if (shadowInput) {
                        setSearchValue(shadowInput, postalCode, countryCode);
                        return;
                    }

                    // Try regular DOM
                    const input = element.querySelector(selector);
                    if (input) {
                        setSearchValue(input, postalCode, countryCode);
                        return;
                    }
                }

                // If not found and haven't reached max attempts, try again
                if (attempts < maxAttempts) {
                    setTimeout(attemptSearch, 200);
                } else {
                    console.warn('⚠️ Could not find search input in GLS widget');
                }
            }

            attemptSearch();
        }

        function setSearchValue(input, postalCode, countryCode) {
            try {
                input.value = postalCode;
                input.focus();

                // Trigger events
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                input.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));

                console.log('✅ Triggered search for:', postalCode);

                // Show notification
                showNotification(`🎯 Automaticky vyhľadané pre ${postalCode}, ${countryCode}`);
            } catch (error) {
                console.error('❌ Error setting search value:', error);
            }
        }

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed; top: 20px; right: 20px; z-index: 10000;
                background: #16a34a; color: white; padding: 12px 16px;
                border-radius: 6px; font-size: 14px; max-width: 350px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 5000);
        }
    });
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