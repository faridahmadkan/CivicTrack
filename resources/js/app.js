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

document.querySelectorAll('[data-report-map]').forEach((element) => {
    const latitudeInput = document.getElementById(element.dataset.latitudeInput);
    const longitudeInput = document.getElementById(element.dataset.longitudeInput);
    const selectedLocation = document.getElementById(element.dataset.locationOutput);
    const locateButton = document.getElementById(element.dataset.locateButton);
    const center = JSON.parse(element.dataset.mapCenter || '[0, 0]');
    const hasLocation = latitudeInput.value !== '' && longitudeInput.value !== '';
    const initialCenter = hasLocation ? [Number(latitudeInput.value), Number(longitudeInput.value)] : center;
    const map = L.map(element).setView(initialCenter, hasLocation ? 15 : 2);
    const tiles = element.dataset.tileUrl;
    let marker = hasLocation ? L.marker(initialCenter).addTo(map) : null;

    L.tileLayer(tiles, {
        attribution: element.dataset.attribution,
        maxZoom: 19,
    }).addTo(map);

    const updateLocation = (point) => {
        const latitude = point.lat.toFixed(7);
        const longitude = point.lng.toFixed(7);

        latitudeInput.value = latitude;
        longitudeInput.value = longitude;
        selectedLocation.textContent = `${latitude}, ${longitude}`;

        if (marker === null) {
            marker = L.marker(point).addTo(map);
        } else {
            marker.setLatLng(point);
        }
    };

    if (hasLocation) {
        selectedLocation.textContent = `${Number(latitudeInput.value).toFixed(7)}, ${Number(longitudeInput.value).toFixed(7)}`;
    }

    map.on('click', (event) => updateLocation(event.latlng));

    locateButton?.addEventListener('click', () => {
        map.locate({ setView: true, maxZoom: 16, enableHighAccuracy: true });
    });

    map.on('locationfound', (event) => updateLocation(event.latlng));
});

document.querySelectorAll('[data-public-report-map]').forEach((element) => {
    const center = JSON.parse(element.dataset.mapCenter || '[0, 0]');
    const markerData = JSON.parse(document.getElementById(element.dataset.markerData)?.textContent || '[]');
    const map = L.map(element).setView(center, 2);

    L.tileLayer(element.dataset.tileUrl, {
        attribution: element.dataset.attribution,
        maxZoom: 19,
    }).addTo(map);

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
});
