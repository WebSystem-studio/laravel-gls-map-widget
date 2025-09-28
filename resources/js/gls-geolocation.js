/**
 * GLS Map Widget Simple Geolocation
 *
 * Simple geolocation functionality following KISS principle.
 * 15 lines of code instead of 700+ lines overkill.
 */

/**
 * Initialize simple geolocation for GLS widget with localStorage caching
 */
function initGeoLocation(elementId, config) {
    if (!config.enabled) return;

    // First check if we have cached geolocation data
    const cachedLocation = getCachedGeolocation();
    if (cachedLocation && isLocationCacheValid(cachedLocation)) {
        console.log('Using cached geolocation data');
        applyLocationToWidget(elementId, config, cachedLocation, true);
        return;
    }

    // Get fresh geolocation data
    if (!navigator.geolocation) {
        console.warn('Geolocation not supported');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (pos) => {
            try {
                const url = `https://nominatim.openstreetmap.org/reverse?lat=${pos.coords.latitude}&lon=${pos.coords.longitude}&format=json&addressdetails=1`;
                const data = await fetch(url).then(r => r.json());

                const locationData = {
                    country: data.address?.country_code?.toUpperCase(),
                    postalCode: data.address?.postcode,
                    city: data.address?.city || data.address?.town || data.address?.village,
                    latitude: pos.coords.latitude,
                    longitude: pos.coords.longitude,
                    timestamp: Date.now()
                };

                // Cache the location data
                cacheGeolocation(locationData);

                // Apply to widget
                applyLocationToWidget(elementId, config, locationData, false);

            } catch (error) {
                console.warn('Geolokácia zlyhala:', error);
            }
        },
        () => console.warn('Geolokácia zamietnutá')
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
    element.setAttribute('country', country.toLowerCase());
    element.setAttribute('language', config.countryMapping[country]?.toLowerCase());

    // Wait for GLS widget to load, then trigger search
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
 * Cache geolocation data to localStorage
 */
function cacheGeolocation(locationData) {
    try {
        const cacheKey = 'gls_geolocation_cache';
        localStorage.setItem(cacheKey, JSON.stringify(locationData));
        console.log('Geolocation data cached');
    } catch (error) {
        console.warn('Could not cache geolocation:', error);
    }
}

/**
 * Get cached geolocation data from localStorage
 */
function getCachedGeolocation() {
    try {
        const cacheKey = 'gls_geolocation_cache';
        const cached = localStorage.getItem(cacheKey);
        return cached ? JSON.parse(cached) : null;
    } catch (error) {
        console.warn('Could not read cached geolocation:', error);
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
        console.log('Geolocation cache cleared');
    } catch (error) {
        console.warn('Could not clear cache:', error);
    }
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
export { initGeoLocation, clearGeolocationCache };

// Make available globally
window.initializeGlsGeolocation = initGeoLocation;
window.clearGlsGeolocationCache = clearGeolocationCache;