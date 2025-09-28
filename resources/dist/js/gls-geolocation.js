/**
 * GLS Map Widget Geolocation Module
 * 
 * This module provides advanced geolocation functionality for the GLS Map Widget,
 * including browser geolocation, reverse geocoding, and fallback mechanisms.
 */

class GlsGeolocation {
    constructor(config = {}) {
        this.config = {
            reverseGeocodingService: 'nominatim',
            nominatimEndpoint: 'https://nominatim.openstreetmap.org/reverse',
            timeout: 10000,
            cacheDuration: 3600,
            countryLanguageMapping: {},
            supportedCountries: [],
            countryEndpoints: {},
            ...config
        };
        
        this.cache = new Map();
        this.pendingRequests = new Map();
    }

    /**
     * Initialize geolocation for a specific GLS widget element
     */
    async initializeGeolocation(elementId) {
        const element = document.getElementById(elementId);
        if (!element) {
            console.warn(`GLS Geolocation: Element with ID '${elementId}' not found`);
            return false;
        }

        try {
            this.setLoadingState(element, true);
            
            const position = await this.getCurrentPosition();
            const location = await this.reverseGeocode(position.coords.latitude, position.coords.longitude);
            
            if (location && location.countryCode) {
                await this.updateWidgetLocation(element, location);
                console.log(`GLS Widget: Successfully updated to ${location.countryCode}`);
                return true;
            } else {
                console.warn('GLS Widget: Could not determine country from location');
                return false;
            }
        } catch (error) {
            console.warn('GLS Widget: Geolocation initialization failed:', error.message);
            return false;
        } finally {
            this.setLoadingState(element, false);
        }
    }

    /**
     * Get current position using browser geolocation API
     */
    getCurrentPosition() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Geolocation is not supported by this browser'));
                return;
            }

            // Check cache first
            const cachedPosition = this.getCachedPosition();
            if (cachedPosition) {
                resolve(cachedPosition);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.cachePosition(position);
                    resolve(position);
                },
                (error) => {
                    let message = 'Geolocation failed';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            message = 'Geolocation permission denied';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message = 'Position unavailable';
                            break;
                        case error.TIMEOUT:
                            message = 'Geolocation timeout';
                            break;
                    }
                    reject(new Error(message));
                },
                {
                    enableHighAccuracy: true,
                    timeout: this.config.timeout,
                    maximumAge: this.config.cacheDuration * 1000
                }
            );
        });
    }

    /**
     * Perform reverse geocoding to get country information
     */
    async reverseGeocode(latitude, longitude) {
        const cacheKey = `${latitude.toFixed(4)},${longitude.toFixed(4)}`;
        
        // Check cache first
        if (this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            if (Date.now() - cached.timestamp < this.config.cacheDuration * 1000) {
                return cached.data;
            }
            this.cache.delete(cacheKey);
        }

        // Check if there's already a pending request for this location
        if (this.pendingRequests.has(cacheKey)) {
            return await this.pendingRequests.get(cacheKey);
        }

        const requestPromise = this.performReverseGeocode(latitude, longitude);
        this.pendingRequests.set(cacheKey, requestPromise);

        try {
            const result = await requestPromise;
            
            // Cache the result
            this.cache.set(cacheKey, {
                data: result,
                timestamp: Date.now()
            });
            
            return result;
        } finally {
            this.pendingRequests.delete(cacheKey);
        }
    }

    /**
     * Perform the actual reverse geocoding request
     */
    async performReverseGeocode(latitude, longitude) {
        if (this.config.reverseGeocodingService === 'nominatim') {
            return await this.nominatimReverseGeocode(latitude, longitude);
        } else {
            throw new Error(`Unsupported reverse geocoding service: ${this.config.reverseGeocodingService}`);
        }
    }

    /**
     * Use Nominatim for reverse geocoding
     */
    async nominatimReverseGeocode(latitude, longitude) {
        const url = `${this.config.nominatimEndpoint}?lat=${latitude}&lon=${longitude}&format=json&accept-language=en&addressdetails=1`;
        
        try {
            const response = await fetch(url, {
                headers: {
                    'User-Agent': 'GLS-Map-Widget/1.0'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (!data.address) {
                throw new Error('No address information in response');
            }

            const countryCode = data.address.country_code?.toUpperCase();
            
            return {
                countryCode,
                country: data.address.country,
                city: data.address.city || data.address.town || data.address.village,
                postalCode: data.address.postcode,
                latitude,
                longitude,
                raw: data
            };
        } catch (error) {
            throw new Error(`Nominatim reverse geocoding failed: ${error.message}`);
        }
    }

    /**
     * Update widget with location information
     */
    async updateWidgetLocation(element, location) {
        const { countryCode } = location;
        
        if (!this.config.supportedCountries.includes(countryCode)) {
            throw new Error(`Country ${countryCode} is not supported by GLS`);
        }

        // Update element attributes
        element.setAttribute('country', countryCode.toLowerCase());
        
        // Set language based on country
        const language = this.config.countryLanguageMapping[countryCode];
        if (language) {
            element.setAttribute('language', language.toLowerCase());
        }

        // Load the appropriate country script
        const scriptUrl = this.config.countryEndpoints[countryCode];
        if (scriptUrl) {
            await this.loadCountryScript(scriptUrl);
        }

        // Dispatch location update event
        const event = new CustomEvent('gls-location-updated', {
            detail: {
                element,
                location,
                countryCode,
                language
            },
            bubbles: true
        });
        
        document.dispatchEvent(event);
    }

    /**
     * Load country-specific GLS script
     */
    async loadCountryScript(scriptUrl) {
        return new Promise((resolve, reject) => {
            // Check if script is already loaded
            const existingScript = document.querySelector(`script[src="${scriptUrl}"]`);
            if (existingScript) {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.type = 'module';
            script.src = scriptUrl;
            script.async = true;
            
            script.onload = () => resolve();
            script.onerror = () => reject(new Error(`Failed to load script: ${scriptUrl}`));
            
            document.head.appendChild(script);
        });
    }

    /**
     * Set loading state for element
     */
    setLoadingState(element, isLoading) {
        if (isLoading) {
            element.setAttribute('data-loading', 'true');
            element.style.opacity = '0.7';
            element.style.pointerEvents = 'none';
        } else {
            element.removeAttribute('data-loading');
            element.style.opacity = '';
            element.style.pointerEvents = '';
        }
    }

    /**
     * Cache position in sessionStorage
     */
    cachePosition(position) {
        try {
            const cacheData = {
                coords: {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                },
                timestamp: Date.now()
            };
            
            sessionStorage.setItem('gls_geolocation_cache', JSON.stringify(cacheData));
        } catch (error) {
            console.warn('Could not cache position:', error);
        }
    }

    /**
     * Get cached position from sessionStorage
     */
    getCachedPosition() {
        try {
            const cached = sessionStorage.getItem('gls_geolocation_cache');
            if (!cached) return null;

            const data = JSON.parse(cached);
            const age = Date.now() - data.timestamp;
            
            if (age < this.config.cacheDuration * 1000) {
                return {
                    coords: data.coords,
                    timestamp: data.timestamp
                };
            }
            
            // Cache expired
            sessionStorage.removeItem('gls_geolocation_cache');
            return null;
        } catch (error) {
            console.warn('Could not read cached position:', error);
            return null;
        }
    }

    /**
     * Clear all caches
     */
    clearCache() {
        this.cache.clear();
        try {
            sessionStorage.removeItem('gls_geolocation_cache');
        } catch (error) {
            console.warn('Could not clear position cache:', error);
        }
    }
}

// Global initialization function
let geolocationInstances = new Map();

export function initializeGeolocation(elementId) {
    const config = window.glsMapConfig?.[elementId] || {};
    
    if (!config.enabled) {
        console.warn(`GLS Geolocation: Not enabled for element ${elementId}`);
        return false;
    }

    // Create or reuse geolocation instance
    if (!geolocationInstances.has(elementId)) {
        geolocationInstances.set(elementId, new GlsGeolocation(config));
    }
    
    const instance = geolocationInstances.get(elementId);
    return instance.initializeGeolocation(elementId);
}

// Export for direct usage
export { GlsGeolocation };

// Make available globally for fallback scenarios
window.GlsGeolocation = GlsGeolocation;
window.initializeGlsGeolocation = initializeGeolocation;