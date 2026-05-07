(function () {
    const DEFAULT_LAT = 23.8103;
    const DEFAULT_LNG = 90.4125;

    function toNumber(value) {
        if (value === null || value === undefined || value === '') return null;
        const number = Number.parseFloat(value);
        return Number.isFinite(number) ? number : null;
    }

    function hasValidCoords(lat, lng) {
        return lat !== null && lng !== null && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
    }

    function formatCoord(value) {
        return Number.parseFloat(value).toFixed(6);
    }

    function debounce(callback, wait) {
        let timeout;
        return function (...args) {
            window.clearTimeout(timeout);
            timeout = window.setTimeout(() => callback.apply(this, args), wait);
        };
    }

    function initLocationMap(panel) {
        if (panel.dataset.initialized === 'true') return;
        panel.dataset.initialized = 'true';

        if (!window.L) {
            const status = panel.querySelector('[data-location-map-status]');
            if (status) status.textContent = 'Map library could not be loaded.';
            return;
        }

        const latInput = document.querySelector(panel.dataset.latInput);
        const lngInput = document.querySelector(panel.dataset.lngInput);
        const addressInput = panel.dataset.addressInput ? document.querySelector(panel.dataset.addressInput) : null;
        const canvas = panel.querySelector('[data-location-map-canvas]');
        const searchInput = panel.querySelector('[data-location-map-search]');
        const searchResults = panel.querySelector('[data-location-map-results]');
        const locateButton = panel.querySelector('[data-location-map-locate]');
        const clearButton = panel.querySelector('[data-location-map-clear]');
        const status = panel.querySelector('[data-location-map-status]');

        if (!latInput || !lngInput || !canvas) return;

        const initialLat = toNumber(latInput.value);
        const initialLng = toNumber(lngInput.value);
        const hasInitialCoords = hasValidCoords(initialLat, initialLng);
        const startLat = hasInitialCoords ? initialLat : DEFAULT_LAT;
        const startLng = hasInitialCoords ? initialLng : DEFAULT_LNG;

        const map = L.map(canvas).setView([startLat, startLng], hasInitialCoords ? 15 : 11);
        let marker = null;
        let searchTimeout = null;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        function setStatus(message) {
            if (status) status.textContent = message;
        }

        function setInputs(lat, lng) {
            latInput.value = formatCoord(lat);
            lngInput.value = formatCoord(lng);
            latInput.dispatchEvent(new Event('change', { bubbles: true }));
            lngInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function setMarker(lat, lng, options = {}) {
            const point = [lat, lng];

            if (!marker) {
                marker = L.marker(point, { draggable: true }).addTo(map);
                marker.on('dragend', function () {
                    const position = marker.getLatLng();
                    setInputs(position.lat, position.lng);
                    setStatus(`Selected: ${formatCoord(position.lat)}, ${formatCoord(position.lng)}`);
                });
            } else {
                marker.setLatLng(point);
            }

            if (options.updateInputs) {
                setInputs(lat, lng);
            }

            if (options.pan !== false) {
                map.setView(point, options.zoom || Math.max(map.getZoom(), 15));
            }

            setStatus(`Selected: ${formatCoord(lat)}, ${formatCoord(lng)}`);
        }

        const updateFromInputs = debounce(function () {
            const lat = toNumber(latInput.value);
            const lng = toNumber(lngInput.value);

            if (!hasValidCoords(lat, lng)) {
                setStatus('Enter valid latitude and longitude to preview the location.');
                return;
            }

            setMarker(lat, lng);
        }, 250);

        latInput.addEventListener('input', updateFromInputs);
        lngInput.addEventListener('input', updateFromInputs);

        map.on('click', function (event) {
            setMarker(event.latlng.lat, event.latlng.lng, { updateInputs: true });
        });

        if (hasInitialCoords) {
            setMarker(initialLat, initialLng, { pan: false });
        } else {
            setStatus('Type coordinates, search, or click the map to set a location.');
        }

        if (locateButton && navigator.geolocation) {
            locateButton.addEventListener('click', function () {
                setStatus('Finding your current location...');
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        setMarker(position.coords.latitude, position.coords.longitude, { updateInputs: true, zoom: 16 });
                    },
                    function () {
                        setStatus('Unable to read current location. You can still search or click the map.');
                    },
                    { enableHighAccuracy: true, timeout: 8000 }
                );
            });
        }

        if (clearButton) {
            clearButton.addEventListener('click', function () {
                latInput.value = '';
                lngInput.value = '';
                if (marker) {
                    marker.remove();
                    marker = null;
                }
                map.setView([DEFAULT_LAT, DEFAULT_LNG], 11);
                setStatus('Coordinates cleared.');
            });
        }

        function renderSearchResults(results) {
            searchResults.innerHTML = '';

            if (!results.length) {
                searchResults.innerHTML = '<div class="list-group-item text-muted">No locations found.</div>';
                searchResults.style.display = 'block';
                return;
            }

            results.forEach(function (result) {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action';
                item.textContent = result.display_name;
                item.addEventListener('click', function () {
                    const lat = toNumber(result.lat);
                    const lng = toNumber(result.lon);
                    if (!hasValidCoords(lat, lng)) return;

                    setMarker(lat, lng, { updateInputs: true, zoom: 16 });
                    if (addressInput && !addressInput.value) {
                        addressInput.value = result.display_name;
                    }
                    searchInput.value = result.display_name;
                    searchResults.innerHTML = '';
                    searchResults.style.display = 'none';
                });
                searchResults.appendChild(item);
            });

            searchResults.style.display = 'block';
        }

        function searchLocation(query) {
            searchResults.innerHTML = '<div class="list-group-item text-muted">Searching...</div>';
            searchResults.style.display = 'block';

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=8`)
                .then(response => response.ok ? response.json() : Promise.reject(response))
                .then(renderSearchResults)
                .catch(function () {
                    searchResults.innerHTML = '<div class="list-group-item text-danger">Location search failed.</div>';
                    searchResults.style.display = 'block';
                });
        }

        if (searchInput && searchResults) {
            searchInput.addEventListener('input', function () {
                window.clearTimeout(searchTimeout);
                const query = searchInput.value.trim();

                if (query.length < 3) {
                    searchResults.innerHTML = '';
                    searchResults.style.display = 'none';
                    return;
                }

                searchTimeout = window.setTimeout(function () {
                    searchLocation(query);
                }, 500);
            });
        }

        panel.locationMap = map;
    }

    window.refreshBuildingLocationMaps = function () {
        document.querySelectorAll('.js-building-location-map').forEach(function (panel) {
            if (panel.dataset.initialized !== 'true') {
                initLocationMap(panel);
            }

            if (panel.locationMap) {
                window.setTimeout(function () {
                    panel.locationMap.invalidateSize();
                }, 100);
            }
        });
    };

    document.addEventListener('DOMContentLoaded', window.refreshBuildingLocationMaps);
})();
