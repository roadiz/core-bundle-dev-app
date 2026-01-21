import {
    GeoJSON,
    Icon,
    LatLng,
    Map,
    Marker,
    TileLayer,
    LeafletMouseEvent,
} from 'leaflet'
import GeoCodingService from '~/services/GeoCodingService'
import iconUrl from '~/assets/img/marker.png'
import iconRetinaUrl from '~/assets/img/marker@2x.png'
import shadowUrl from '~/assets/img/marker_shadow.png'
import shadowRetinaUrl from '~/assets/img/marker_shadow@2x.png'

type LegacyLocation = {
    lat: number
    lng: number
    zoom?: number
    alt?: number
    name?: string
}

type GeoResponse = {
    lat: string | number
    lon: string | number
    display_name: string
}

type FeaturePoint = {
    type: 'Feature'
    properties?: { name?: string; zoom?: number | string }
    geometry: { type: 'Point'; coordinates: [number, number] }
    name?: string
    zoom?: number
    alt?: number
}

type GeocodeInput = LegacyLocation | FeaturePoint | LatLng
type MapLocationInput = LegacyLocation | FeaturePoint

export const DEFAULT_LOCATION: LegacyLocation = {
    lat: 45.769785,
    lng: 4.833967,
    zoom: 14,
}

export default class RzGeolocation extends HTMLElement {
    private map: Map | null = null
    private markers: Marker[] = []
    private resizeObserver: ResizeObserver | null = null
    private mapContainer: HTMLDivElement | null = null

    get fieldWrapper() {
        const selector = this.getAttribute('wrapper-id')
        return this.closest(`#${selector}`) as HTMLElement | null
    }

    get textareaId() {
        return this.getAttribute('textarea-id') || ''
    }

    get searchInput() {
        return this.fieldWrapper?.querySelector(
            `input#${this.getAttribute('search-input-id')}`,
        ) as HTMLInputElement | null
    }

    get textarea() {
        return this.fieldWrapper?.querySelector(
            `#${this.textareaId}`,
        ) as HTMLTextAreaElement | null
    }

    get isMultiple() {
        const val = (this.getAttribute('multiple') || '').toLowerCase()
        const isMultiple = val === 'true' || val === '1' || val === 'yes'

        return isMultiple
    }

    get multiple() {
        return this.isMultiple
    }

    constructor() {
        super()

        this.updateFromSearch = this.updateFromSearch.bind(this)
        this.onMapClick = this.onMapClick.bind(this)
        this.onCommand = this.onCommand.bind(this)
    }

    connectedCallback() {
        if (this.dataset.initialized === 'true') return
        this.dataset.initialized = 'false'

        this.init()

        this.dataset.initialized = 'true'
    }

    disconnectedCallback() {
        this.destroy()
    }

    init() {
        if (!this.textarea) {
            console.error(
                `<rz-geolocation> cannot find textarea #${this.textareaId}`,
            )
            return
        }

        this.mapContainer = document.createElement('div')
        this.mapContainer.className = 'rz-geolocation__map'
        this.appendChild(this.mapContainer)

        // Initialize Leaflet map
        const defaultConfig =
            (window.RozierConfig?.defaultMapLocation as LegacyLocation) ||
            DEFAULT_LOCATION
        const center = this.createLatLng(defaultConfig)

        this.map = this.createMap(this.mapContainer, {
            center,
            zoom:
                defaultConfig.zoom ||
                defaultConfig?.alt ||
                DEFAULT_LOCATION.zoom,
        })

        // Init default markers from textarea value with normalization
        this.normalizeInitialValue()

        // Event listeners
        this.addEventListener('command', this.onCommand)
        this.searchInput?.addEventListener('change', this.updateFromSearch)
        this.map?.on('click', this.onMapClick)
        this.resizeObserver = new ResizeObserver(() => {
            this.map?.invalidateSize({ animate: false, pan: false })
            const lastMarker = this.markers[this.markers.length - 1]
            if (lastMarker) {
                this.map?.panTo(lastMarker.getLatLng(), {
                    animate: false,
                    pan: false,
                })
            } else {
                this.map?.panTo(center, { animate: false, pan: false })
            }
        })
        this.resizeObserver.observe(this)
    }

    private createMap(
        container: HTMLElement | null,
        mapOptions: { center: LatLng; zoom: number },
    ) {
        if (!container) {
            throw new Error('Missing map container')
        }
        const map = new Map(container).setView(
            mapOptions.center,
            mapOptions.zoom,
        )
        const title = window.RozierConfig?.leafletMapTileUrl || 'OpenStreetMap'
        const osmLayer = new TileLayer(title, {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18,
        })
        map.addLayer(osmLayer)
        return map
    }

    private normalizeInitialValue() {
        const raw = this.textarea?.value?.trim()
        if (!raw) return

        try {
            const parsed = JSON.parse(raw)
            const featureCollection: FeaturePoint[] = []
            if (
                parsed.type === 'FeatureCollection' &&
                Array.isArray(parsed.features)
            ) {
                featureCollection.push(...parsed.features)
            } else if (parsed.type === 'Feature') {
                featureCollection.push(parsed)
            }

            featureCollection.forEach((feature) => {
                const marker = this.createMarker(feature)
                this.bindMarker(marker)
                this.markers.push(marker)
            })
        } catch (e) {
            console.warn('Invalid geolocation JSON, ignoring', e)
        }
    }

    private formatLatLngLabel(latLng: LatLng) {
        return `${latLng.lat.toFixed(5)}, ${latLng.lng.toFixed(5)}`
    }

    private createMarker(geocode: GeocodeInput, name?: string) {
        if (!this.map) return
        const latLng =
            geocode instanceof LatLng ? geocode : this.createLatLng(geocode)

        const icon = new Icon({
            iconUrl,
            iconRetinaUrl,
            shadowUrl,
            shadowRetinaUrl,
            iconSize: [22, 30],
            shadowSize: [25, 22],
            iconAnchor: [11, 30],
            shadowAnchor: [2, 22],
        })

        const _name =
            name ||
            geocode.options?.name ||
            geocode.properties?.name ||
            geocode?.name ||
            this.formatLatLngLabel(latLng)

        const marker = new Marker(latLng, {
            name: _name,
            icon,
            draggable: true,
        }).addTo(this.map)

        this.map.flyTo(latLng, latLng.alt)
        return marker
    }

    private onMapClick(event: LeafletMouseEvent) {
        console.log('event', event)
        const marker = this.createMarker(event.latlng)

        this.bindMarker(marker)

        if (this.isMultiple) {
            this.markers.push(marker)
        } else {
            // Single-pin mode: keep only one marker
            // Remove previous marker from map
            const prev = this.markers.pop()
            if (prev && this.map) prev.removeFrom(this.map)
            this.markers = [marker]
        }

        this.syncTextareaData()
    }

    onCommand(event: CommandEvent) {
        switch (event.command) {
            case '--search-location': {
                this.updateFromSearch()
                break
            }
            case '--delete-pin':
                this.onDelete(event)
                break
            case '--delete-all-pin':
                this.onDeleteAll(event)
                break
        }
    }

    async updateFromSearch() {
        const address = this.searchInput?.value
        if (!address?.trim().length) return

        const response = await GeoCodingService.geoCode(address)

        const { lat, lon } = response || {}
        if (!response || !lat || !lon) {
            console.warn('Geocoding response is null or undefined')
            this.searchInput?.classList.add('rz-input--error')

            return
        }
        this.searchInput?.classList.remove('rz-input--error')

        const latLng = new LatLng(
            lat === 'string' ? parseFloat(lat) : lat,
            lon === 'string' ? parseFloat(lon) : lon,
        )

        const marker = this.createMarker(latLng, response.display_name)
        this.bindMarker(marker)

        if (this.isMultiple) {
            this.markers.push(marker)
        } else {
            const prev = this.markers.pop()
            if (prev && this.map) prev.removeFrom(this.map)
            this.markers = [marker]
        }

        this.syncTextareaData()
    }

    private onDeleteAll(event: Event) {
        event.preventDefault()

        if (this.map) {
            this.markers.forEach((m) => m.removeFrom(this.map as Map))
        }

        if (this.searchInput?.value) {
            this.searchInput.value = ''
        }

        if (this.textarea) {
            this.textarea.value = ''
        }

        this.markers = []

        this.updatePinList()
    }

    private onDelete(event: CommandEvent) {
        console.log('onDelete', event)
        event.preventDefault()

        if (this.isMultiple) {
            const lastMarker = this.markers.pop()
            if (lastMarker && this.map) {
                lastMarker.removeFrom(this.map)
            }
            this.syncTextareaData()
        } else {
            const prev = this.markers.pop()
            if (prev && this.map) {
                prev.removeFrom(this.map)
            }

            if (this.searchInput?.value) {
                this.searchInput.value = ''
            }
            if (this.textarea) {
                this.textarea.value = ''
            }
        }
    }

    private bindMarker(marker: Marker & { name?: string }) {
        marker.on('dragend', () => {
            if (!this.map) return
            const latLng = marker.getLatLng()
            this.map.panTo(latLng)
        })
    }

    private syncTextareaData() {
        if (!this.textarea || !this.map) return

        const featureCollection = [] as FeaturePoint[]

        this.markers.forEach((marker) => {
            const latLng = marker.getLatLng()

            const feature: FeaturePoint = {
                type: 'Feature',
                properties: {
                    name: marker?.options?.name,
                    zoom: latLng.alt || this.map.getZoom(),
                },
                geometry: {
                    type: 'Point',
                    coordinates: GeoJSON.latLngToCoords(latLng),
                },
            }

            featureCollection.push(feature)
        })

        if (this.multiple) {
            this.textarea.value = JSON.stringify({
                type: 'FeatureCollection',
                features: featureCollection,
            })
        } else {
            this.textarea.value = JSON.stringify(featureCollection[0])
        }

        this.updatePinList()
    }

    private createLatLng(data: MapLocationInput): LatLng {
        if ('lat' in data && 'lng' in data) {
            return new LatLng(data.lat, data.lng, data.zoom ?? data.alt)
        }
        if (data.type === 'Feature') {
            const zoomValue = data.properties?.zoom
            const zoom =
                typeof zoomValue === 'string'
                    ? parseInt(zoomValue, 10)
                    : (zoomValue ?? 7)
            return new LatLng(
                data.geometry.coordinates[1],
                data.geometry.coordinates[0],
                zoom,
            )
        }
        throw new Error('Cannot create LatLng object from data')
    }

    // Update template elements
    get pinList() {
        return this.querySelector('[data-pin-list]') as HTMLElement | null
    }

    get pinItemTemplate() {
        const templateEl = this.fieldWrapper.querySelector(
            'template[data-pin-item]',
        ) as HTMLTemplateElement | null
        return templateEl?.content?.firstElementChild as HTMLElement | null
    }

    private updatePinList() {
        const templateRoot = this.pinItemTemplate
        if (!this.pinList || !this.isMultiple || !templateRoot) return

        this.pinList.innerHTML = ''
        this.markers.forEach((marker) => {
            const newPin = this.insertPin(marker)
            this.pinList.appendChild(newPin)
        })
    }

    private insertPin(marker: Marker) {
        const clone = document.importNode(this.pinItemTemplate, true)

        const nameEl = clone.querySelector(
            '[data-pin-name]',
        ) as HTMLElement | null
        if (nameEl) {
            nameEl.textContent = marker.options.name || ''
        }

        const contentEl = clone.querySelector(
            '[data-pin-content]',
        ) as HTMLElement | null
        if (contentEl) {
            contentEl.textContent = this.formatLatLngLabel(marker.getLatLng())
        }

        const deleteBtn = clone.querySelector(
            '.rz-geolocation__pin__button',
        ) as HTMLElement | null
        if (deleteBtn) {
            deleteBtn.setAttribute('commandfor', this.id)
            deleteBtn.setAttribute('data-pin-name', marker.options.name || '')
        }

        return clone
    }

    destroy() {
        this.resizeObserver?.disconnect()
        this.map?.off()
        this.markers.forEach((m) => m.off())
        this.searchInput?.removeEventListener('change', this.updateFromSearch)
        this.removeEventListener('command', this.onCommand)
    }
}
