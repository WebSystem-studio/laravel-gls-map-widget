/**
 * GLS Map Widget Simple Geolocation
 *
 * Simple geolocation functionality following KISS principle.
 * Enhanced with automatic postal code search, localStorage caching and debug logging.
 */

/**
 * Initialize simple geolocation for GLS widget with localStorage caching
 */
function initGeoLocation(elementId, config) {
    console.log('🚀 GLS Geolocation: Starting initialization', { elementId, config });

    if (!config.enabled) {
        console.log('❌ GLS Geolocation: Disabled in config');
        return;
    }

    // Debug localStorage availability
    try {
        localStorage.setItem('gls_test', 'test');
        localStorage.removeItem('gls_test');
        console.log('✅ localStorage available');
    } catch (e) {
        console.error('❌ localStorage not available:', e);
    }

    // First check if we have cached geolocation data
    const cachedLocation = getCachedGeolocation();
    console.log('💾 Cached location data:', cachedLocation);

    if (cachedLocation && isLocationCacheValid(cachedLocation)) {
        console.log('🎯 Using cached geolocation data:', cachedLocation);
        applyLocationToWidget(elementId, config, cachedLocation, true);
        return;
    } else if (cachedLocation) {
        console.log('⏰ Cached data expired, getting fresh location');
    } else {
        console.log('📍 No cached data found, getting fresh location');
    }

    // Get fresh geolocation data
    if (!navigator.geolocation) {
        console.error('❌ Geolocation not supported by browser');
        return;
    }

    console.log('📱 Requesting geolocation permission...');

    navigator.geolocation.getCurrentPosition(
        async (pos) => {
            try {
                console.log('✅ Geolocation obtained:', pos.coords);

                const url = `https://nominatim.openstreetmap.org/reverse?lat=${pos.coords.latitude}&lon=${pos.coords.longitude}&format=json&addressdetails=1`;
                console.log('🌐 Fetching reverse geocoding from:', url);

                const response = await fetch(url);
                const data = await response.json();
                console.log('🗺️ Reverse geocoding result:', data);

                const locationData = {
                    country: data.address?.country_code?.toUpperCase(),
                    postalCode: data.address?.postcode,
                    city: data.address?.city || data.address?.town || data.address?.village,
                    latitude: pos.coords.latitude,
                    longitude: pos.coords.longitude,
                    timestamp: Date.now()
                };

                console.log('📦 Prepared location data:', locationData);

                // Cache the location data
                cacheGeolocation(locationData);

                // Apply to widget
                applyLocationToWidget(elementId, config, locationData, false);

            } catch (error) {
                console.error('❌ Geolocation processing failed:', error);
            }
        },
        (error) => {
            console.error('❌ Geolocation denied/failed:', {
                code: error.code,
                message: error.message,
                PERMISSION_DENIED: error.PERMISSION_DENIED,
                POSITION_UNAVAILABLE: error.POSITION_UNAVAILABLE,
                TIMEOUT: error.TIMEOUT
            });
        },
        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 300000 // 5 minutes
        }
    );
}

/**
 * Apply location data to widget (from cache or fresh)
 */
async function applyLocationToWidget(elementId, config, locationData, isFromCache) {
    const { country, postalCode, city } = locationData;

    if (!config.supportedCountries.includes(country)) {
        console.warn(`Unsupported country: ${country}`);
        return;
    }

    const element = document.getElementById(elementId);

    // Update widget attributes
    element.setAttribute('country', country.toLowerCase());
    element.setAttribute('language', config.countryMapping[country]?.toLowerCase());

    // Load the correct country script first
    const countryEndpoints = {
        'AT': 'https://map.gls-austria.com/widget/gls-dpm.js',
        'BE': 'https://map.gls-belgium.com/widget/gls-dpm.js',
        'BG': 'https://map.gls-bulgaria.com/widget/gls-dpm.js',
        'CZ': 'https://map.gls-czech.com/widget/gls-dpm.js',
        'DE': 'https://map.gls-germany.com/widget/gls-dpm.js',
        'DK': 'https://map.gls-denmark.com/widget/gls-dpm.js',
        'ES': 'https://map.gls-spain.com/widget/gls-dpm.js',
        'FI': 'https://map.gls-finland.com/widget/gls-dpm.js',
        'FR': 'https://map.gls-france.com/widget/gls-dpm.js',
        'GR': 'https://map.gls-greece.com/widget/gls-dpm.js',
        'HR': 'https://map.gls-croatia.com/widget/gls-dpm.js',
        'HU': 'https://map.gls-hungary.com/widget/gls-dpm.js',
        'IT': 'https://map.gls-italy.com/widget/gls-dpm.js',
        'LU': 'https://map.gls-luxembourg.com/widget/gls-dpm.js',
        'NL': 'https://map.gls-netherlands.com/widget/gls-dpm.js',
        'PL': 'https://map.gls-poland.com/widget/gls-dpm.js',
        'PT': 'https://map.gls-portugal.com/widget/gls-dpm.js',
        'RO': 'https://map.gls-romania.com/widget/gls-dpm.js',
        'RS': 'https://map.gls-serbia.com/widget/gls-dpm.js',
        'SI': 'https://map.gls-slovenia.com/widget/gls-dpm.js',
        'SK': 'https://map.gls-slovakia.com/widget/gls-dpm.js'
    };

    const scriptUrl = countryEndpoints[country];
    if (!scriptUrl) {
        console.warn(`No script URL for country: ${country}`);
        return;
    }

    // Remove old script and load new one
    const oldScript = document.querySelector(`script[id="gls-script-${elementId}"]`);
    if (oldScript) {
        oldScript.remove();
    }

    // Load the new script for detected country
    await loadCountryScript(scriptUrl, elementId);

    // Wait for widget to load, then trigger search
    if (postalCode || city) {
        await waitForWidgetAndSearch(element, postalCode || city);
        const cacheText = isFromCache ? ' (cached)' : '';
        showSimpleNotification(`🎯 Automaticky vyhľadané pre ${postalCode || city}, ${country}${cacheText}`);
    } else {
        showSimpleNotification(`🌍 Detekovaná krajina: ${country}. Zadajte PSČ do mapy.`);
    }

    // Dispatch event with location details
    document.dispatchEvent(new CustomEvent('gls-location-updated', {
        detail: { countryCode: country, postalCode, city, element, isFromCache }
    }));
}

/**
 * Load country-specific GLS script
 */
async function loadCountryScript(scriptUrl, elementId) {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.type = 'module';
        script.src = scriptUrl;
        script.async = true;
        script.id = `gls-script-${elementId}`;

        script.onload = () => {
            console.log(`GLS script loaded for ${scriptUrl}`);
            resolve();
        };
        script.onerror = () => {
            console.error(`Failed to load GLS script: ${scriptUrl}`);
            reject(new Error(`Failed to load script: ${scriptUrl}`));
        };

        document.head.appendChild(script);
    });
}

/**
 * Cache geolocation data to localStorage
 */
function cacheGeolocation(locationData) {
    try {
        const cacheKey = 'gls_geolocation_cache';
        console.log('💾 Caching geolocation data:', locationData);
        localStorage.setItem(cacheKey, JSON.stringify(locationData));
        console.log('✅ Geolocation data cached successfully');

        // Verify cache was written
        const verification = localStorage.getItem(cacheKey);
        console.log('🔍 Cache verification:', verification);
    } catch (error) {
        console.error('❌ Could not cache geolocation:', error);
    }
}

/**
 * Get cached geolocation data from localStorage
 */
function getCachedGeolocation() {
    try {
        const cacheKey = 'gls_geolocation_cache';
        console.log('🔍 Checking for cached geolocation data...');

        const cached = localStorage.getItem(cacheKey);
        console.log('📄 Raw cached data:', cached);

        if (cached) {
            const parsed = JSON.parse(cached);
            console.log('📦 Parsed cached data:', parsed);
            return parsed;
        } else {
            console.log('📭 No cached data found');
            return null;
        }
    } catch (error) {
        console.error('❌ Could not read cached geolocation:', error);
        return null;
    }
}

/**
 * Check if cached location data is still valid (24 hours)
 */
function isLocationCacheValid(locationData) {
    const cacheValidityMs = 24 * 60 * 60 * 1000; // 24 hours
    const age = Date.now() - (locationData.timestamp || 0);
    return age < cacheValidityMs;
}

/**
 * Clear cached geolocation data (useful for debugging)
 */
function clearGeolocationCache() {
    try {
        localStorage.removeItem('gls_geolocation_cache');
        console.log('🗑️ Geolocation cache cleared');
    } catch (error) {
        console.warn('❌ Could not clear cache:', error);
    }
}

/**
 * Debug function to inspect geolocation status
 */
function debugGeolocation() {
    console.log('🔧 === GLS GEOLOCATION DEBUG ===');

    // Check localStorage
    try {
        localStorage.setItem('test', 'test');
        localStorage.removeItem('test');
        console.log('✅ localStorage: Available');
    } catch (e) {
        console.error('❌ localStorage: Not available', e);
    }

    // Check geolocation API
    if (navigator.geolocation) {
        console.log('✅ Geolocation API: Available');
    } else {
        console.error('❌ Geolocation API: Not available');
    }

    // Check cache content
    const cache = getCachedGeolocation();
    if (cache) {
        console.log('💾 Cache status: Found', cache);
        const isValid = isLocationCacheValid(cache);
        console.log('⏰ Cache validity:', isValid ? 'Valid' : 'Expired');
    } else {
        console.log('📭 Cache status: Empty');
    }

    // Check if HTTPS
    console.log('🔒 Protocol:', location.protocol);
    if (location.protocol === 'https:') {
        console.log('✅ HTTPS: Available for geolocation');
    } else {
        console.warn('⚠️ HTTP: Geolocation may be restricted');
    }

    // Check all localStorage keys
    console.log('🗂️ All localStorage keys:');
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        console.log(`  - ${key}: ${localStorage.getItem(key)?.substring(0, 100)}...`);
    }

    console.log('🔧 === END DEBUG ===');
}

/**
 * Wait for GLS widget to load and trigger search automatically
 */
async function waitForWidgetAndSearch(element, searchTerm) {
    // Wait for widget to be ready (max 10 seconds)
    for (let i = 0; i < 50; i++) {
        await new Promise(resolve => setTimeout(resolve, 200));

        if (triggerWidgetSearch(element, searchTerm)) {
            console.log(`GLS Widget: Automatically searched for "${searchTerm}"`);
            return true;
        }
    }
    console.warn('GLS Widget: Could not trigger automatic search');
    return false;
}

/**
 * Trigger search in GLS widget by simulating user input
 */
function triggerWidgetSearch(element, searchTerm) {
    // Try multiple selectors for search input
    const selectors = [
        'input[type="search"]',
        'input[type="text"]',
        'input[placeholder*="PSČ"]',
        'input[placeholder*="postcode"]',
        'input[placeholder*="ZIP"]',
        '.search-input',
        '#search'
    ];

    for (const selector of selectors) {
        // Try shadow DOM first
        const shadowInput = element.shadowRoot?.querySelector(selector);
        if (shadowInput && attemptSearch(shadowInput, searchTerm)) return true;

        // Try regular DOM
        const input = element.querySelector(selector);
        if (input && attemptSearch(input, searchTerm)) return true;

        // Try within nested elements
        const nestedInput = element.querySelector(`* ${selector}`);
        if (nestedInput && attemptSearch(nestedInput, searchTerm)) return true;
    }

    return false;
}

/**
 * Attempt to set search term and trigger search
 */
function attemptSearch(input, searchTerm) {
    try {
        input.value = searchTerm;
        input.focus();

        // Trigger input events
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));

        // Trigger Enter key
        input.dispatchEvent(new KeyboardEvent('keydown', {
            key: 'Enter',
            keyCode: 13,
            bubbles: true
        }));

        // Alternative: trigger submit on parent form if exists
        const form = input.closest('form');
        if (form) {
            form.dispatchEvent(new Event('submit', { bubbles: true }));
        }

        return true;
    } catch (error) {
        return false;
    }
}

/**
 * Show simple notification to user
 */
function showSimpleNotification(message) {
    // Create simple notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 10000;
        background: #2563eb; color: white; padding: 12px 16px;
        border-radius: 6px; font-size: 14px; max-width: 350px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

// Export for usage
export { initGeoLocation, clearGeolocationCache, debugGeolocation };

// Make available globally
window.initializeGlsGeolocation = initGeoLocation;
window.clearGlsGeolocationCache = clearGeolocationCache;
window.debugGlsGeolocation = debugGeolocation;