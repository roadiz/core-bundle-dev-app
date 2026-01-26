import type { LatLng, Map, Marker, LeafletMouseEvent } from 'leaflet'
import type { Point, Feature, FeatureCollection } from 'geojson'
import {
    nominatimFetch,
    type NominatimSearchResult,
} from '~/utils/nominatimFetch'
import iconUrl from '~/assets/img/marker.png'
import shadowUrl from '~/assets/img/marker_shadow.png'

type LegacyLocation = {
    lat: number
    lng: number
    alt?: number
    zoom?: number
}

type MarkerProps = {
    name?: string
    itemDetailId?: string
    alt?: number
    zoom?: number
}

type FeaturePoint = Feature<Point, MarkerProps>
type TextareaData = FeatureCollection<Point, MarkerProps> | FeaturePoint
type MarkerExtended = Marker<MarkerProps>

type GeocodeInput =
    | LegacyLocation
    | FeaturePoint
    | LatLng
    | NominatimSearchResult

export default class RzGeotag extends HTMLElement {
    defaultLocation: LegacyLocation = {
        lat: 45.769785,
        lng: 4.833967,
        zoom: 14,
    }
    private leaflet: typeof import('leaflet') | null = null

    private map: Map | null = null
    private markers: MarkerExtended[] = []
    private resizeObserver: ResizeObserver | null = null
    private mapContainer: HTMLDivElement | null = null
    private idSeed: string = Date.now().toString(36)
    private idCounter: number = 0

    get fieldWrapper() {
        const selector = this.getAttribute('wrapper-id')
        return this.closest(`#${selector}`) as HTMLElement | null
    }

    get searchInput() {
        const inputId = this.getAttribute('search-input-id')
        if (!inputId) return null

        return this.fieldWrapper?.querySelector(
            `input#${inputId}`,
        ) as HTMLInputElement | null
    }

    get textarea() {
        return this.querySelector(`textarea`) as HTMLTextAreaElement | null
    }

    get isMultiple() {
        const val = (this.getAttribute('multiple') || '').toLowerCase()
        const isMultiple = val === 'true' || val === '1' || val === 'yes'

        return isMultiple
    }

    constructor() {
        super()

        this.dataset.initialized = 'false'

        this.onTargetMarker = this.onTargetMarker.bind(this)
        this.onSearchChange = this.onSearchChange.bind(this)
        this.onDelete = this.onDelete.bind(this)
        this.onMapClick = this.onMapClick.bind(this)
        this.onCommand = this.onCommand.bind(this)
    }

    connectedCallback() {
        if (this.dataset.initialized === 'true') return

        import('leaflet')
            .then((Leaflet) => {
                this.leaflet = Leaflet
                this.init()
                this.dataset.initialized = 'true'
            })
            .catch((err) => {
                console.error('Failed to load Leaflet dynamically', err)
            })
    }

    disconnectedCallback() {
        this.destroy()
    }

    get defaultOptions() {
        const defaultConfig =
            (window.RozierConfig?.defaultMapLocation as LegacyLocation) ||
            this.defaultLocation

        const center = new this.leaflet.LatLng(
            defaultConfig.lat,
            defaultConfig.lng,
            defaultConfig?.zoom ?? defaultConfig?.alt,
        )
        const zoom =
            defaultConfig.zoom ||
            defaultConfig?.alt ||
            this.defaultLocation.zoom

        return { center, zoom }
    }

    init() {
        if (!this.textarea) {
            console.error(`<rz-geotag> cannot find textarea`)
            return
        }

        // Create map container
        this.mapContainer = document.createElement('div')
        this.mapContainer.className = 'rz-geotag__map'
        this.appendChild(this.mapContainer)

        // Initialize Leaflet map
        const { center, zoom } = this.defaultOptions
        this.map = this.createMap(this.mapContainer, { center, zoom })

        // Initialize default markers
        this.initMarkersFromTextarea()

        // Event listeners
        this.addEventListener('command', this.onCommand)
        this.searchInput?.addEventListener('change', this.onSearchChange)
        this.map?.on('click', this.onMapClick)
        this.resizeObserver = new ResizeObserver(() => {
            if (!this.map) return

            this.map.invalidateSize({ animate: false, pan: false })

            // Set map view to markers
            if (this.markers.length > 1) {
                const bounds = new this.leaflet.LatLngBounds(
                    this.markers.map((m) => m.getLatLng()),
                )
                this.map.fitBounds(bounds, {
                    animate: false,
                })
            } else if (this.markers.length === 1) {
                const latLng = this.markers[0].getLatLng()
                this.map.panTo(latLng, {
                    animate: false,
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
        const map = new this.leaflet.Map(container).setView(
            mapOptions.center,
            mapOptions.zoom,
        )
        const tileUrl =
            window.RozierConfig?.leafletMapTileUrl || 'OpenStreetMap'
        const osmLayer = new this.leaflet.TileLayer(tileUrl, {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18,
        })
        map.addLayer(osmLayer)
        return map
    }

    private initMarkersFromTextarea() {
        const raw = this.textarea?.value?.trim()
        if (!raw) return

        const parsed = JSON.parse(raw) as TextareaData
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
            const marker = this.createMarker(feature, {
                itemDetailId: item?.id,
            })
            this.markers.push(marker)

            this.bindMarker(marker)
        })
    }

    private formatLatLngLabel(latLng: LatLng) {
        return `${latLng.lat.toFixed(5)}, ${latLng.lng.toFixed(5)}`
    }

    addAndSyncMarker(marker: Marker) {
        if (this.isMultiple) {
            this.markers.push(marker)
        } else {
            // Single-pin mode: keep only one marker
            const prev = this.markers.pop()
            if (prev) prev.removeFrom(this.map)
            this.markers = [marker]
        }

        this.bindMarker(marker)
        this.updateItemDetailList()
        this.syncTextareaData()

        const latLng = marker.getLatLng()
        if (latLng) this.map.flyTo(latLng, latLng.alt)
    }

    private createMarker(data: GeocodeInput, props?: MarkerProps) {
        if (!this.map) return

        let latLng: LatLng | null = null
        let name = props?.name || ''

        if (data instanceof this.leaflet.LatLng) {
            latLng = data
        } else if ('display_name' in data) {
            latLng = new this.leaflet.LatLng(
                parseFloat(data.lat),
                parseFloat(data.lon),
            )
            name = data.display_name
        } else if ('lat' in data && 'lng' in data) {
            latLng = new this.leaflet.LatLng(
                data.lat,
                data.lng,
                data?.zoom ?? data?.alt,
            )
        } else if ('type' in data && data.type === 'Feature') {
            const zoomValue = data.properties?.zoom
            const zoom =
                typeof zoomValue === 'string'
                    ? parseInt(zoomValue, 10)
                    : (zoomValue ?? 7)
            latLng = new this.leaflet.LatLng(
                data.geometry.coordinates[1],
                data.geometry.coordinates[0],
                zoom,
            )
            name = data.properties?.name || ''
        }

        const marker = new this.leaflet.Marker<MarkerProps>(latLng, {
            icon: new this.leaflet.Icon({
                iconUrl,
                shadowUrl,
                iconSize: [22, 30],
                shadowSize: [25, 22],
                iconAnchor: [11, 30],
                shadowAnchor: [2, 22],
            }),
            draggable: true,
        }).addTo(this.map)

        const geojson = marker.toGeoJSON()
        marker.feature = {
            ...geojson,
            properties: {
                ...geojson.properties,
                ...(props || {}),
                name: name || this.formatLatLngLabel(latLng),
                itemDetailId:
                    props?.itemDetailId || this.generateItemDetailId(),
            },
        }
        return marker
    }

    private onMapClick(event: LeafletMouseEvent) {
        if (!this.map) return

        const marker = this.createMarker(event.latlng)
        this.addAndSyncMarker(marker)
    }

    onCommand(event: CommandEvent) {
        switch (event.command) {
            case '--search-location': {
                this.onSearchChange()
                break
            }
            case '--delete-marker':
                this.onDelete(event)
                break
            case '--delete-all-marker':
                this.onDeleteAll(event)
                break
            case '--target-marker':
                this.onTargetMarker(event)
                break
        }
    }

    onTargetMarker(event: CommandEvent) {
        const itemDetailId = event.source.getAttribute('data-item-detail-id')
        const itemIndex = this.markers.findIndex((m) => {
            const geo = m.toGeoJSON()
            return geo?.properties?.itemDetailId === itemDetailId
        })

        const marker = this.markers[itemIndex]
        const latLng = marker.getLatLng()
        if (!marker || !latLng) return
        this.map.flyTo(latLng, this.map.getZoom())
    }

    updateButtonSearchState(isLoading: boolean) {
        const btn = this.fieldWrapper?.querySelector(
            'button[command="--search-location"]',
        ) as HTMLButtonElement | null
        if (!btn) return

        if (isLoading) {
            btn.classList.add('rz-button--loading')
        } else {
            btn.classList.remove('rz-button--loading')
        }
    }

    async onSearchChange() {
        const address = this.searchInput?.value
        if (!address?.trim().length) return

        this.updateButtonSearchState(true)
        const response = await nominatimFetch(address)
        const { lat, lon } = response || {}

        this.updateButtonSearchState(false)
        if (!response || !lat || !lon) {
            this.searchInput?.classList.add('rz-input--error')
            this.searchInput?.setCustomValidity('Place not found')
            return
        } else {
            this.searchInput?.classList.remove('rz-input--error')
            this.searchInput?.setCustomValidity('')
        }

        const marker = this.createMarker(response)
        this.addAndSyncMarker(marker)
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
            const itemDetailId = event.source.getAttribute(
                'data-item-detail-id',
            )
            itemIndex = this.markers.findIndex((m) => {
                return m.feature?.properties?.itemDetailId === itemDetailId
            })
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

        const featureCollection = this.markers.map((marker) =>
            marker.toGeoJSON(),
        )

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

    private generateItemDetailId() {
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

        const geo = marker.toGeoJSON()
        const elId = geo.properties?.itemDetailId
        clone.id = elId

        const nameEl = clone.querySelector(
            '[data-item-detail-name]',
        ) as HTMLElement | null
        if (nameEl) {
            const content = geo.properties?.name || ''
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
            '.rz-geotag__item__button[data-item-detail-id]',
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
        this.searchInput?.removeEventListener('change', this.onSearchChange)
        this.removeEventListener('command', this.onCommand)
    }
}
