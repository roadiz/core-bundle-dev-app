import {
    GeoJSON,
    Icon,
    LatLng,
    Map,
    Marker,
    TileLayer,
    LatLngBounds,
    LeafletMouseEvent,
} from 'leaflet'
import GeoCodingService from '~/services/GeoCodingService'
import iconUrl from '~/assets/img/marker.png'
import shadowUrl from '~/assets/img/marker_shadow.png'

type LegacyLocation = {
    lat: number
    lng: number
    zoom?: number
    alt?: number
    name?: string
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

export default class RzGeolocation extends HTMLElement {
    defaultLocation: LegacyLocation = {
        lat: 45.769785,
        lng: 4.833967,
        zoom: 14,
    }
    private map: Map | null = null
    private markers: Marker[] = []
    private resizeObserver: ResizeObserver | null = null
    private mapContainer: HTMLDivElement | null = null
    private idSeed: string = Date.now().toString(36)
    private idCounter: number = 0

    get fieldWrapper() {
        const selector = this.getAttribute('wrapper-id')
        return this.closest(`#${selector}`) as HTMLElement | null
    }

    get textareaId() {
        return this.getAttribute('textarea-id') || ''
    }

    get searchInput() {
        const inputId = this.getAttribute('search-input-id')
        if (!inputId) return null

        return this.fieldWrapper?.querySelector(
            `input#${inputId}`,
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

    constructor() {
        super()

        this.targetMarker = this.targetMarker.bind(this)
        this.updateFromSearch = this.updateFromSearch.bind(this)
        this.onDelete = this.onDelete.bind(this)
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

        // Create map container
        this.mapContainer = document.createElement('div')
        this.mapContainer.className = 'rz-geolocation__map'
        this.appendChild(this.mapContainer)

        // Initialize Leaflet map
        const defaultConfig =
            (window.RozierConfig?.defaultMapLocation as LegacyLocation) ||
            this.defaultLocation
        const center = this.createLatLng(defaultConfig)
        const zoom =
            defaultConfig.zoom ||
            defaultConfig?.alt ||
            this.defaultLocation.zoom

        this.map = this.createMap(this.mapContainer, { center, zoom })

        // Initialize default markers
        this.initMarkersFromTextarea()

        // Event listeners
        this.addEventListener('command', this.onCommand)
        this.searchInput?.addEventListener('change', this.updateFromSearch)
        this.map?.on('click', this.onMapClick)
        this.resizeObserver = new ResizeObserver(() => {
            if (!this.map) return

            this.map.invalidateSize({ animate: false, pan: false })

            // Set map view to markers
            if (this.markers.length > 1) {
                const bounds = new LatLngBounds()
                for (const marker of this.markers) {
                    bounds.extend(marker.getLatLng())
                }
                this.map.fitBounds(bounds, {
                    animate: false,
                    pan: false,
                })
            } else if (this.markers.length === 1) {
                const latLng = this.markers[0].getLatLng()
                this.map.panTo(latLng, {
                    animate: false,
                    pan: false,
                })
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
        const tileUrl =
            window.RozierConfig?.leafletMapTileUrl || 'OpenStreetMap'
        const osmLayer = new TileLayer(tileUrl, {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18,
        })
        map.addLayer(osmLayer)
        return map
    }

    private initMarkersFromTextarea() {
        const raw = this.textarea?.value?.trim()
        if (!raw) return

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

        featureCollection.forEach((feature, index) => {
            // These markers has been already rendered from SSR
            // map these element with actual dom element data
            const item = this.itemDetailList?.children.item(index)
            const marker = this.createMarker(feature, undefined, item?.id)
            this.markers.push(marker)

            this.bindMarker(marker)
        })
    }

    private formatLatLngLabel(latLng: LatLng) {
        return `${latLng.lat.toFixed(5)}, ${latLng.lng.toFixed(5)}`
    }

    private createMarker(
        geocode: GeocodeInput,
        name?: string,
        itemId?: string,
    ) {
        if (!this.map) return
        const latLng =
            geocode instanceof LatLng ? geocode : this.createLatLng(geocode)

        const icon = new Icon({
            iconUrl,
            shadowUrl,
            iconSize: [22],
            shadowSize: [25],
            iconAnchor: [11],
            shadowAnchor: [2],
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
            itemId: itemId || this.generateItemId(),
        }).addTo(this.map)

        return marker
    }

    private onMapClick(event: LeafletMouseEvent) {
        if (!this.map) return
        const marker = this.createMarker(event.latlng)

        this.bindMarker(marker)

        if (this.isMultiple) {
            this.markers.push(marker)
            this.updateItemDetailList()
        } else {
            // Single-pin mode: keep only one marker
            // Remove previous marker from map
            const prev = this.markers.pop()
            if (prev) prev.removeFrom(this.map)
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
            case '--delete-marker':
                this.onDelete(event)
                break
            case '--delete-all-marker':
                this.onDeleteAll(event)
                break
            case '--target-marker':
                this.targetMarker(event)
                break
        }
    }

    targetMarker(event: CommandEvent) {
        const itemId = event.source.getAttribute('data-item-detail-id')
        const itemIndex = this.markers.findIndex(
            (m) => m.options.itemId === itemId,
        )
        const marker = this.markers[itemIndex]
        this.map.flyTo(marker.getLatLng(), marker.getLatLng().alt)
    }

    async updateFromSearch() {
        const address = this.searchInput?.value
        if (!address?.trim().length) return

        const response = await GeoCodingService.geoCode(address)
        const { lat, lon } = response || {}

        if (!response || !lat || !lon) {
            this.searchInput?.classList.add('rz-input--error')
            this.searchInput?.setCustomValidity('Place not found')
            return
        } else {
            this.searchInput?.classList.remove('rz-input--error')
            this.searchInput?.setCustomValidity('')
        }

        const latLng = new LatLng(
            typeof lat === 'string' ? parseFloat(lat) : lat,
            typeof lon === 'string' ? parseFloat(lon) : lon,
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

        this.map.flyTo(latLng, latLng.alt)

        this.syncTextareaData()
        this.updateItemDetailList()
    }

    private onDeleteAll(event: Event) {
        event.preventDefault()

        if (this.map) {
            this.markers.forEach((m) => m.removeFrom(this.map))
        }

        if (this.searchInput?.value) {
            this.searchInput.value = ''
        }

        if (this.textarea) {
            this.textarea.value = ''
        }

        this.markers = []

        this.updateItemDetailList()
    }

    private onDelete(event: CommandEvent) {
        event.preventDefault()

        let itemIndex = -1

        if (this.isMultiple) {
            const itemId = event.source.getAttribute('data-item-detail-id')
            itemIndex = this.markers.findIndex(
                (m) => m.options.itemId === itemId,
            )
        } else {
            itemIndex = 0

            if (this.searchInput) {
                this.searchInput.value = ''
            }
        }

        if (itemIndex === -1) {
            console.warn('Pin not found for deletion')
            return
        }

        this.markers[itemIndex].removeFrom(this.map)
        this.markers.splice(itemIndex, 1)

        this.syncTextareaData()
        this.updateItemDetailList()
    }

    private bindMarker(marker: Marker) {
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

        if (!featureCollection.length) {
            this.textarea.value = ''
        } else if (this.isMultiple) {
            this.textarea.value = JSON.stringify({
                type: 'FeatureCollection',
                features: featureCollection,
            })
        } else {
            this.textarea.value = JSON.stringify(featureCollection[0])
        }
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

    get itemDetailList() {
        return this.querySelector(
            '[data-item-detail-list]',
        ) as HTMLElement | null
    }

    get itemDetailSkeleton() {
        const templateEl = this.fieldWrapper.querySelector(
            'template[data-item-detail]',
        ) as HTMLTemplateElement | null
        return templateEl?.content?.firstElementChild as HTMLElement | null
    }

    private generateItemId() {
        const prefix = this.getAttribute('item-detail-id-prefix') || ''
        this.idCounter += 1
        return `${prefix}${this.idSeed}-${this.idCounter}`
    }

    private updateItemDetailList() {
        const templateRoot = this.itemDetailSkeleton
        if (!this.itemDetailList || !this.isMultiple || !templateRoot) return

        this.itemDetailList.innerHTML = ''
        this.markers.forEach((marker) => {
            const newPin = this.getItemDetail(marker)
            this.itemDetailList.appendChild(newPin)
        })
    }

    private getItemDetail(marker: Marker) {
        const clone = document.importNode(this.itemDetailSkeleton, true)
        const elId = (marker.options.itemId as string) || ''
        clone.id = elId

        const nameEl = clone.querySelector(
            '[data-item-detail-name]',
        ) as HTMLElement | null
        if (nameEl) {
            const content = marker.options.name || ''
            nameEl.setAttribute('title', content)
            nameEl.textContent = content
        }

        const contentEl = clone.querySelector(
            '[data-item-detail-content]',
        ) as HTMLElement | null
        if (contentEl) {
            contentEl.textContent = this.formatLatLngLabel(marker.getLatLng())
        }

        const commandButtons = clone.querySelectorAll(
            '.rz-geolocation__item__button[data-item-detail-id]',
        ) as NodeListOf<HTMLElement> | null
        if (commandButtons.length) {
            commandButtons.forEach((button) => {
                button.setAttribute('commandfor', this.id)
                button.setAttribute('data-item-detail-id', elId)
            })
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
