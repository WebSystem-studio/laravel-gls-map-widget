/**
 * GLS Map Widget Simple Geolocation
 *
 * Simple geolocation functionality following KISS principle.
 * 15 lines of code instead of 700+ lines overkill.
 */

/**
 * Initialize simple geolocation for GLS widget
 */
function initGeoLocation(elementId, config) {
    if (!navigator.geolocation || !config.enabled) return;

    navigator.geolocation.getCurrentPosition(
        async (pos) => {
            try {
                const url = `https://nominatim.openstreetmap.org/reverse?lat=${pos.coords.latitude}&lon=${pos.coords.longitude}&format=json`;
                const data = await fetch(url).then(r => r.json());
                const country = data.address?.country_code?.toUpperCase();

                if (config.supportedCountries.includes(country)) {
                    const element = document.getElementById(elementId);
                    element.setAttribute('country', country.toLowerCase());
                    element.setAttribute('language', config.countryMapping[country]?.toLowerCase());

                    // Simple notification instead of complex overlay
                    showSimpleNotification(`🌍 Detekovaná krajina: ${country}. Zadajte PSČ do mapy pre vyhľadávanie ParcelShops.`);

                    // Dispatch simple event
                    document.dispatchEvent(new CustomEvent('gls-location-updated', {
                        detail: { countryCode: country, element }
                    }));
                }
            } catch (error) {
                console.warn('Geolokácia zlyhala:', error);
            }
        },
        () => console.warn('Geolokácia zamietnutá')
    );
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
export { initGeoLocation };

// Make available globally
window.initializeGlsGeolocation = initGeoLocation;