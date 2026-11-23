import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIconRetina from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIconRetina,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

const addTaskButton = document.getElementById('add-work-task');
const taskList = document.getElementById('work-task-list');
const taskTemplate = document.getElementById('work-task-template');

addTaskButton?.addEventListener('click', () => {
    if (taskList.children.length >= 25) {
        document.getElementById('task-limit-message')?.classList.remove('hidden');
        addTaskButton.disabled = true;

        return;
    }

    const nextIndex = taskList.children.length;
    const previousTasks = [...taskList.querySelectorAll('[data-task-title]')];
    const markup = taskTemplate.innerHTML.replaceAll('__INDEX__', String(nextIndex));
    const fragment = document.createRange().createContextualFragment(markup);
    const newRow = fragment.firstElementChild;
    const dependencySelect = newRow.querySelector('[data-task-dependencies]');

    newRow.querySelector('[data-task-label]').textContent = `Task ${nextIndex + 1}`;
    previousTasks.forEach((input, index) => {
        const title = input.value.trim() || `Task ${index + 1}`;
        dependencySelect.add(new Option(`Task ${index + 1}: ${title}`, String(index)));
    });
    taskList.append(newRow);
});

const initializeTileLayers = (map, element) => {
    const mapSection = element.closest('section') || document;
    const controls = mapSection.querySelector('[data-map-layer-controls]');
    const status = controls?.querySelector('[data-map-tile-status]');
    const streetButton = controls?.querySelector('[data-map-layer="street"]');
    const satelliteButton = controls?.querySelector('[data-map-layer="satellite"]');
    const streetUrl = element.dataset.streetTileUrl;
    const satelliteUrl = element.dataset.satelliteTileUrl;
    const streetLayer = L.tileLayer(streetUrl, {
        attribution: element.dataset.streetAttribution,
        maxZoom: 19,
    }).addTo(map);
    let satelliteLayer = null;
    let satelliteLoadTimer = null;
    let selectionVersion = 0;
    let satelliteHasLoaded = false;

    satelliteButton.disabled = !satelliteUrl;

    const setButtons = (selectedLayer) => {
        streetButton?.setAttribute('aria-pressed', String(selectedLayer === 'street'));
        satelliteButton?.setAttribute('aria-pressed', String(selectedLayer === 'satellite'));
        streetButton?.classList.toggle('border-forest-800', selectedLayer === 'street');
        streetButton?.classList.toggle('bg-forest-900', selectedLayer === 'street');
        streetButton?.classList.toggle('text-white', selectedLayer === 'street');
        satelliteButton?.classList.toggle('border-forest-800', selectedLayer === 'satellite');
        satelliteButton?.classList.toggle('bg-forest-900', selectedLayer === 'satellite');
        satelliteButton?.classList.toggle('text-white', selectedLayer === 'satellite');
    };

    const showStreetLayer = (message = 'Street map is active.') => {
        selectionVersion += 1;
        window.clearTimeout(satelliteLoadTimer);
        if (satelliteLayer && map.hasLayer(satelliteLayer)) {
            map.removeLayer(satelliteLayer);
        }
        if (!map.hasLayer(streetLayer)) {
            streetLayer.addTo(map);
        }
        setButtons('street');
        if (status) {
            status.textContent = message;
        }
    };

    const showSatelliteLayer = () => {
        if (!satelliteUrl) {
            showStreetLayer('Satellite imagery is not configured. Street map is active.');

            return;
        }

        selectionVersion += 1;
        const thisSelection = selectionVersion;
        window.clearTimeout(satelliteLoadTimer);

        if (!satelliteLayer) {
            satelliteLayer = L.tileLayer(satelliteUrl, {
                attribution: element.dataset.satelliteAttribution,
                maxZoom: 19,
            });
            satelliteLayer.on('tileload', () => {
                satelliteHasLoaded = true;
                window.clearTimeout(satelliteLoadTimer);

                if (map.hasLayer(satelliteLayer) && status) {
                    status.textContent = 'Satellite imagery is active.';
                }
            });
        }

        if (map.hasLayer(streetLayer)) {
            map.removeLayer(streetLayer);
        }

        satelliteLayer.addTo(map);
        setButtons('satellite');

        if (satelliteHasLoaded) {
            if (status) {
                status.textContent = 'Satellite imagery is active.';
            }

            return;
        }

        if (status) {
            status.textContent = 'Loading satellite imagery…';
        }

        satelliteLoadTimer = window.setTimeout(() => {
            if (!satelliteHasLoaded && thisSelection === selectionVersion) {
                showStreetLayer('Satellite imagery did not load. Street map is active.');
            }
        }, 8000);
    };

    streetButton?.addEventListener('click', () => showStreetLayer());
    satelliteButton?.addEventListener('click', showSatelliteLayer);

    if (!satelliteUrl && status) {
        status.textContent = 'Satellite imagery is not configured. Street map is active.';
    }

    return { showStreetLayer, showSatelliteLayer };
};

const initializePlaceSearch = (map, element, confirmLocation = null) => {
    const mapSection = element.closest('section') || document;
    const searchPanel = mapSection.querySelector('[data-map-search]');

    if (!searchPanel) {
        return;
    }

    const searchControls = searchPanel.querySelector('[data-search-controls]');
    const queryInput = searchControls.querySelector('[data-place-query]');
    const submitButton = searchControls.querySelector('[data-search-submit]');
    const status = searchPanel.querySelector('[data-search-status]');
    const resultsList = searchPanel.querySelector('[data-search-results]');
    const confirmation = searchPanel.querySelector('[data-search-confirmation]');
    const selectionLabel = searchPanel.querySelector('[data-search-selection]');
    const confirmButton = searchPanel.querySelector('[data-confirm-search]');
    const cancelButton = searchPanel.querySelector('[data-cancel-search]');
    let pendingResult = null;
    let previewMarker = null;
    let requestController = null;

    if (searchPanel.dataset.searchConfigured !== 'true') {
        queryInput.disabled = true;
        submitButton.disabled = true;
        status.textContent = 'Place search is not configured.';

        return;
    }

    const clearPreview = (message = null) => {
        pendingResult = null;
        previewMarker?.remove();
        previewMarker = null;
        if (confirmation) {
            confirmation.classList.add('hidden');
        }
        if (message) {
            status.textContent = message;
        }
    };

    const showResultOnMap = (result) => {
        const latitude = Number(result.latitude);
        const longitude = Number(result.longitude);

        if (!Number.isFinite(latitude) || !Number.isFinite(longitude)
            || latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) {
            status.textContent = 'This result has invalid coordinates. Choose another place.';

            return;
        }

        map.setView([latitude, longitude], Math.max(map.getZoom(), 16));

        if (typeof confirmLocation === 'function') {
            clearPreview();
            pendingResult = { ...result, latitude, longitude };
            previewMarker = L.circleMarker([latitude, longitude], {
                radius: 9,
                color: '#ffffff',
                weight: 3,
                fillColor: '#165c46',
                fillOpacity: 1,
            }).addTo(map);
            selectionLabel.textContent = result.label;
            confirmation.classList.remove('hidden');
            status.textContent = 'Place centered on the map. Confirm it to update the report location.';
        } else {
            status.textContent = `Map centered on ${result.label}.`;
        }
    };

    const runSearch = async (event) => {
        event.preventDefault();
        const query = queryInput.value.trim();

        if (query.length < 2) {
            status.textContent = 'Enter at least two characters to search.';
            queryInput.focus();

            return;
        }

        requestController?.abort();
        const currentRequestController = new AbortController();
        requestController = currentRequestController;
        clearPreview();
        resultsList.replaceChildren();
        resultsList.classList.add('hidden');
        submitButton.disabled = true;
        searchControls.setAttribute('aria-busy', 'true');
        status.textContent = 'Searching places…';

        try {
            const response = await fetch(searchPanel.dataset.searchUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: new URLSearchParams({ q: query }),
                credentials: 'same-origin',
                signal: currentRequestController.signal,
            });
            const payload = await response.json();

            if (requestController !== currentRequestController) {
                return;
            }

            if (!response.ok) {
                status.textContent = response.status === 429
                    ? 'Place search is rate limited. Wait a moment and try again.'
                    : payload.errors?.q?.[0] || payload.message || 'Place search is unavailable. Try again later.';

                return;
            }

            if (!Array.isArray(payload.results) || payload.results.length === 0) {
                status.textContent = 'No places found. Try a nearby place name or landmark.';

                return;
            }

            payload.results.forEach((result) => {
                const item = document.createElement('li');
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-left text-sm text-slate-800 hover:border-forest-300 hover:bg-forest-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-forest-700';
                button.textContent = result.label;
                button.addEventListener('click', () => showResultOnMap(result));
                item.append(button);
                resultsList.append(item);
            });
            resultsList.classList.remove('hidden');
            status.textContent = `${payload.results.length} place${payload.results.length === 1 ? '' : 's'} found. Select a result to view it on the map.`;
        } catch (error) {
            if (error.name !== 'AbortError') {
                status.textContent = 'Place search could not connect. Check your connection and try again.';
            }
        } finally {
            if (requestController === currentRequestController) {
                submitButton.disabled = false;
                searchControls.removeAttribute('aria-busy');
            }
        }
    };

    submitButton.addEventListener('click', runSearch);
    queryInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            runSearch(event);
        }
    });

    if (typeof confirmLocation === 'function') {
        confirmButton.addEventListener('click', () => {
            if (pendingResult) {
                confirmLocation(pendingResult);
                clearPreview();
                status.textContent = 'Report location updated.';
            }
        });

        cancelButton.addEventListener('click', () => {
            clearPreview();
            status.textContent = 'The report location was not changed.';
        });
    }

    return {
        clearPreview,
    };
};

document.querySelectorAll('[data-report-map]').forEach((element) => {
    const latitudeInput = document.getElementById(element.dataset.latitudeInput);
    const longitudeInput = document.getElementById(element.dataset.longitudeInput);
    const addressInput = document.getElementById(element.dataset.addressInput);
    const selectedLocation = document.getElementById(element.dataset.locationOutput);
    const locateButton = document.getElementById(element.dataset.locateButton);
    const coordinatePrecision = Math.min(7, Math.max(0, Number(element.dataset.coordinatePrecision || 7)));
    const locationStatus = element.closest('section')?.querySelector('[data-location-status]');
    const center = JSON.parse(element.dataset.mapCenter || '[0, 0]');
    const hasLocation = latitudeInput.value !== '' && longitudeInput.value !== '';
    const initialCenter = hasLocation ? [Number(latitudeInput.value), Number(longitudeInput.value)] : center;
    const defaultZoom = Number(element.dataset.mapZoom || 11);
    const map = L.map(element).setView(initialCenter, hasLocation ? 15 : defaultZoom);
    let marker = hasLocation ? L.marker(initialCenter).addTo(map) : null;

    initializeTileLayers(map, element);

    const updateLocation = (point, address = null) => {
        const latitude = point.lat.toFixed(coordinatePrecision);
        const longitude = point.lng.toFixed(coordinatePrecision);

        latitudeInput.value = latitude;
        longitudeInput.value = longitude;
        selectedLocation.textContent = `${latitude}, ${longitude}`;

        if (address !== null && addressInput) {
            addressInput.value = address;
        }

        if (marker === null) {
            marker = L.marker(point).addTo(map);
        } else {
            marker.setLatLng(point);
        }
    };

    if (hasLocation) {
        selectedLocation.textContent = `${Number(latitudeInput.value).toFixed(coordinatePrecision)}, ${Number(longitudeInput.value).toFixed(coordinatePrecision)}`;
    }

    const placeSearch = initializePlaceSearch(map, element, (result) => {
        updateLocation({ lat: result.latitude, lng: result.longitude }, result.label);
    });

    map.on('click', (event) => {
        placeSearch?.clearPreview('Manual map pin selected.');
        updateLocation(event.latlng);
    });

    locateButton?.addEventListener('click', () => {
        if (locationStatus) {
            locationStatus.textContent = 'Requesting your device location…';
        }
        map.locate({ setView: true, maxZoom: 16, enableHighAccuracy: true });
    });

    map.on('locationfound', (event) => {
        placeSearch?.clearPreview();
        updateLocation(event.latlng);
        if (locationStatus) {
            locationStatus.textContent = 'Device location selected.';
        }
    });
    map.on('locationerror', () => {
        if (locationStatus) {
            locationStatus.textContent = 'Your device location is unavailable. Select a point on the map instead.';
        }
    });

});

document.querySelectorAll('[data-public-report-map]').forEach((element) => {
    const center = JSON.parse(element.dataset.mapCenter || '[0, 0]');
    const markerData = JSON.parse(document.getElementById(element.dataset.markerData)?.textContent || '[]');
    const map = L.map(element).setView(center, 11);

    initializeTileLayers(map, element);

    const markers = markerData.map((item) => {
        const popup = document.createElement('div');
        const category = document.createElement('strong');
        const status = document.createElement('p');

        category.textContent = item.category;
        status.className = 'mt-1';
        status.textContent = item.status;
        popup.append(category, status);

        return L.marker([item.latitude, item.longitude]).bindPopup(popup);
    });

    const markerLayer = L.featureGroup(markers).addTo(map);

    if (markers.length > 0) {
        map.fitBounds(markerLayer.getBounds().pad(0.12), { maxZoom: 13 });
    }

    initializePlaceSearch(map, element);
});

const initializeLocationPreview = (element) => {
    const latitude = Number(element.dataset.latitude);
    const longitude = Number(element.dataset.longitude);
    const tileUrl = element.dataset.tileUrl;

    if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
        return;
    }

    const map = L.map(element, {
        zoomControl: false,
        dragging: false,
        scrollWheelZoom: false,
        doubleClickZoom: false,
        boxZoom: false,
        keyboard: false,
        touchZoom: false,
        attributionControl: Boolean(tileUrl),
    }).setView([latitude, longitude], element.dataset.publicLocation === 'true' ? 12 : 15);

    if (tileUrl) {
        L.tileLayer(tileUrl, {
            attribution: element.dataset.tileAttribution,
            maxZoom: 19,
        }).addTo(map);
    }

    L.circleMarker([latitude, longitude], {
        radius: 6,
        color: '#ffffff',
        weight: 2,
        fillColor: '#205044',
        fillOpacity: 1,
        interactive: false,
    }).addTo(map);
};

const locationPreviews = [...document.querySelectorAll('[data-location-preview]')];

if ('IntersectionObserver' in window) {
    const locationPreviewObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                initializeLocationPreview(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '120px' });

    locationPreviews.forEach((element) => locationPreviewObserver.observe(element));
} else {
    locationPreviews.forEach(initializeLocationPreview);
}
