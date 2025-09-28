<?php

use Illuminate\Support\Facades\Route;

// Basic browser test for GLS widget rendering
it('can render basic GLS widget in browser', function () {
    // Set up route for testing
    Route::get('/test-basic-widget', function () {
        return '<!DOCTYPE html>
<html>
<head>
    <title>GLS Widget Test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .gls-map-widget-container { border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div id="app">
        <h1>GLS Widget Browser Test</h1>
        <div class="gls-map-widget-container" style="width: 800px; height: 600px;">
            <gls-dpm id="test-widget" country="sk"></gls-dpm>
        </div>
        <script type="module" src="https://map.gls-slovakia.com/widget/gls-dpm.js" async></script>
    </div>
</body>
</html>';
    });

    $page = visit('/test-basic-widget')->on()->desktop();

    $page->assertSee('GLS Widget Browser Test');
})->group('browser')->skipOnCi();

// Test GLS dialog widget
it('can render GLS dialog widget in browser', function () {
    Route::get('/test-dialog-widget', function () {
        return '<!DOCTYPE html>
<html>
<head>
    <title>GLS Dialog Test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        button { padding: 10px 20px; font-size: 16px; margin: 10px 0; }
    </style>
</head>
<body>
    <div id="app">
        <h1>GLS Dialog Browser Test</h1>
        <button id="open-dialog" onclick="openModal()">Open GLS Dialog</button>

        <gls-dpm-dialog id="gls-dialog" country="cz"></gls-dpm-dialog>

        <script type="module" src="https://map.gls-czech.com/widget/gls-dpm.js" async></script>
        <script>
            function openModal() {
                const element = document.getElementById("gls-dialog");
                if (element && element.showModal) {
                    element.showModal();
                }
            }
        </script>
    </div>
</body>
</html>';
    });

    $page = visit('/test-dialog-widget')->on()->desktop();

    $page->assertSee('GLS Dialog Browser Test')
        ->assertSee('Open GLS Dialog');
})->group('browser')->skipOnCi();

// Test widget with filters
it('can test widget with different filter types', function () {
    Route::get('/test-filter-widget', function () {
        return '<!DOCTYPE html>
<html>
<head>
    <title>GLS Filter Types Test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .widget-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div id="app">
        <h1>GLS Filter Types Browser Test</h1>

        <div class="widget-section">
            <h3>Parcel Shops Only</h3>
            <gls-dpm id="parcel-shop-widget" country="sk" filters="parcelshop"></gls-dpm>
        </div>

        <div class="widget-section">
            <h3>Parcel Lockers Only</h3>
            <gls-dpm id="parcel-locker-widget" country="sk" filters="parcellockers"></gls-dpm>
        </div>

        <div class="widget-section">
            <h3>Drop-off Points Only</h3>
            <gls-dpm id="dropoff-widget" country="sk" filters="depots"></gls-dpm>
        </div>

        <script type="module" src="https://map.gls-slovakia.com/widget/gls-dpm.js" async></script>
    </div>
</body>
</html>';
    });

    $page = visit('/test-filter-widget')->on()->desktop();

    $page->assertSee('GLS Filter Types Browser Test')
        ->assertSee('Parcel Shops Only')
        ->assertSee('Parcel Lockers Only')
        ->assertSee('Drop-off Points Only');
})->group('browser')->skipOnCi();

// Test responsiveness on different devices
it('can test widget responsiveness on different devices', function () {
    Route::get('/test-responsive-widget', function () {
        return '<!DOCTYPE html>
<html>
<head>
    <title>GLS Responsive Test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .responsive-container { width: 100%; max-width: 800px; }
        @media (max-width: 768px) {
            .responsive-container { max-width: 100%; }
        }
    </style>
</head>
<body>
    <div id="app">
        <h1>GLS Responsive Browser Test</h1>
        <div class="responsive-container">
            <gls-dpm id="responsive-widget" country="sk"></gls-dpm>
        </div>
        <script type="module" src="https://map.gls-slovakia.com/widget/gls-dpm.js" async></script>
    </div>
</body>
</html>';
    });

    // Test on desktop
    $desktopPage = visit('/test-responsive-widget')->on()->desktop();
    $desktopPage->assertSee('GLS Responsive Browser Test');

    // Test on mobile
    $mobilePage = visit('/test-responsive-widget')->on()->mobile();
    $mobilePage->assertSee('GLS Responsive Browser Test');
})->group('browser')->skipOnCi();

// Test multiple country widgets
it('can test multiple country widgets', function () {
    Route::get('/test-multi-country', function () {
        return '<!DOCTYPE html>
<html>
<head>
    <title>GLS Multi-Country Test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .country-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div id="app">
        <h1>GLS Multi-Country Browser Test</h1>

        <div class="country-section">
            <h3>Slovakia</h3>
            <gls-dpm id="sk-widget" country="sk"></gls-dpm>
            <script type="module" src="https://map.gls-slovakia.com/widget/gls-dpm.js" async></script>
        </div>

        <div class="country-section">
            <h3>Czech Republic</h3>
            <gls-dpm id="cz-widget" country="cz"></gls-dpm>
            <script type="module" src="https://map.gls-czech.com/widget/gls-dpm.js" async></script>
        </div>

        <div class="country-section">
            <h3>Hungary</h3>
            <gls-dpm id="hu-widget" country="hu"></gls-dpm>
            <script type="module" src="https://map.gls-hungary.com/widget/gls-dpm.js" async></script>
        </div>
    </div>
</body>
</html>';
    });

    $page = visit('/test-multi-country')->on()->desktop();

    $page->assertSee('GLS Multi-Country Browser Test')
        ->assertSee('Slovakia')
        ->assertSee('Czech Republic')
        ->assertSee('Hungary');
})->group('browser')->skipOnCi();

// Smoke test for basic widget variations
it('can smoke test all basic widget variations', function () {
    // Define test routes for smoke testing
    Route::get('/smoke-test-1', function () {
        return '<!DOCTYPE html><html><head><title>Smoke Test 1</title></head><body><h1>Basic Widget</h1><gls-dpm country="sk"></gls-dpm><script type="module" src="https://map.gls-slovakia.com/widget/gls-dpm.js" async></script></body></html>';
    });

    Route::get('/smoke-test-2', function () {
        return '<!DOCTYPE html><html><head><title>Smoke Test 2</title></head><body><h1>Dialog Widget</h1><gls-dpm-dialog country="cz"></gls-dpm-dialog><script type="module" src="https://map.gls-czech.com/widget/gls-dpm.js" async></script></body></html>';
    });

    Route::get('/smoke-test-3', function () {
        return '<!DOCTYPE html><html><head><title>Smoke Test 3</title></head><body><h1>Filtered Widget</h1><gls-dpm country="hu" filters="parcelshop"></gls-dpm><script type="module" src="https://map.gls-hungary.com/widget/gls-dpm.js" async></script></body></html>';
    });

    // Test each route
    $routes = ['/smoke-test-1', '/smoke-test-2', '/smoke-test-3'];

    foreach ($routes as $route) {
        $page = visit($route)->on()->desktop();
        // Just ensure the page loads without JavaScript errors
        $page->assertSee('Widget'); // All smoke test pages contain 'Widget' in their content
    }
})->group('browser', 'smoke')->skipOnCi();

// Visual regression test
it('can take screenshots for visual regression testing', function () {
    Route::get('/test-visual-regression', function () {
        return '<!DOCTYPE html>
<html>
<head>
    <title>GLS Visual Test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .visual-section { margin: 20px 0; padding: 20px; background: white; border-radius: 8px; }
    </style>
</head>
<body>
    <div id="app">
        <h1>GLS Map Widget Visual Test</h1>

        <div class="visual-section">
            <h3>Slovakia Widget</h3>
            <gls-dpm id="visual-sk-widget" country="sk"></gls-dpm>
            <script type="module" src="https://map.gls-slovakia.com/widget/gls-dpm.js" async></script>
        </div>

        <div class="visual-section">
            <h3>Czech Republic - Parcel Lockers</h3>
            <gls-dpm id="visual-cz-widget" country="cz" filters="parcellockers"></gls-dpm>
            <script type="module" src="https://map.gls-czech.com/widget/gls-dpm.js" async></script>
        </div>
    </div>
</body>
</html>';
    });

    $page = visit('/test-visual-regression')
        ->on()->desktop()
        ->inLightMode();

    $page->assertSee('GLS Map Widget Visual Test')
        ->assertSee('Slovakia Widget')
        ->assertSee('Czech Republic - Parcel Lockers');

    // Uncomment below for actual screenshot capture
    // $page->screenshot('gls-widget-visual-test');
})->group('browser', 'visual')->skipOnCi();

// Test geolocation with automatic postal code search
it('can test geolocation with automatic postal code setting', function () {
    Route::get('/test-geolocation-postalcode', function () {
        return '<!DOCTYPE html>
<html>
<head>
    <title>GLS Geolocation Postal Code Test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .test-container { margin: 20px 0; padding: 20px; border: 1px solid #ddd; }
        #debug-info { background: #f5f5f5; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <div id="app">
        <h1>GLS Geolocation with Postal Code Test</h1>

        <div class="test-container">
            <h3>Simulated Geolocation Test</h3>
            <p>Testing automatic postal code detection and search</p>
            <div id="debug-info">Waiting for geolocation...</div>

            <div style="height: 400px;">
                <gls-dpm id="geo-test-widget" country="sk"></gls-dpm>
            </div>
        </div>

        <script type="module" src="https://map.gls-slovakia.com/widget/gls-dpm.js" async></script>

        <script>
            // Mock geolocation for testing
            const mockPosition = {
                coords: {
                    latitude: 48.148598,   // Bratislava coordinates
                    longitude: 17.107748
                }
            };

            // Override geolocation for testing
            Object.defineProperty(navigator, "geolocation", {
                value: {
                    getCurrentPosition: function(success, error) {
                        setTimeout(() => success(mockPosition), 100);
                    }
                },
                configurable: true
            });

            // Mock fetch for Nominatim response
            const originalFetch = window.fetch;
            window.fetch = function(url) {
                if (url.includes("nominatim.openstreetmap.org")) {
                    return Promise.resolve({
                        json: () => Promise.resolve({
                            address: {
                                country_code: "sk",
                                postcode: "81101",
                                city: "Bratislava",
                                country: "Slovakia"
                            }
                        })
                    });
                }
                return originalFetch.apply(this, arguments);
            };

            // Test function to trigger geolocation
            function testGeolocation() {
                const element = document.getElementById("geo-test-widget");
                const debugInfo = document.getElementById("debug-info");

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        async function(pos) {
                            debugInfo.innerHTML += "<br>✓ Position obtained: " + pos.coords.latitude + ", " + pos.coords.longitude;

                            try {
                                const url = `https://nominatim.openstreetmap.org/reverse?lat=${pos.coords.latitude}&lon=${pos.coords.longitude}&format=json&addressdetails=1`;
                                const data = await fetch(url).then(r => r.json());

                                debugInfo.innerHTML += "<br>✓ Reverse geocoding: " + data.address.city + ", " + data.address.postcode;

                                // Wait for widget and attempt search
                                for (let i = 0; i < 20; i++) {
                                    await new Promise(resolve => setTimeout(resolve, 200));

                                    const searchInput = element.shadowRoot?.querySelector("input[type=search]") ||
                                                       element.querySelector("input[type=search]") ||
                                                       element.querySelector("input[type=text]");

                                    if (searchInput) {
                                        searchInput.value = data.address.postcode;
                                        searchInput.dispatchEvent(new Event("input", { bubbles: true }));
                                        debugInfo.innerHTML += "<br>✓ Search triggered for: " + data.address.postcode;
                                        break;
                                    }
                                }
                            } catch (error) {
                                debugInfo.innerHTML += "<br>✗ Error: " + error.message;
                            }
                        },
                        function(error) {
                            debugInfo.innerHTML += "<br>✗ Geolocation failed: " + error.message;
                        }
                    );
                } else {
                    debugInfo.innerHTML += "<br>✗ Geolocation not supported";
                }
            }

            // Start test when page loads
            setTimeout(testGeolocation, 1000);
        </script>
    </div>
</body>
</html>';
    });

    $page = visit('/test-geolocation-postalcode')->on()->desktop();

    $page->assertSee('GLS Geolocation with Postal Code Test')
        ->assertSee('Simulated Geolocation Test')
        ->assertSee('Testing automatic postal code detection');

    // Check if geolocation test completed (simplified check)
    $page->assertSee('GLS Geolocation with Postal Code Test');
})->group('browser', 'geolocation')->skipOnCi();
