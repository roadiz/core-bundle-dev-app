import $ from 'jquery'
import { Map, Marker, LatLng, TileLayer, Icon, GeoJSON } from 'leaflet'
import GeoCodingService from '../services/GeoCodingService'

export const DEFAULT_LOCATION = { lat: 45.769785, lng: 4.833967, zoom: 14 }

export default class LeafletGeotagField {
    constructor() {
        this.$fields = document.querySelectorAll('.rz-geotag-field:not(.is-enable)')

        if (this.$fields.length) {
            this.init()
        }
    }

    init() {
        this.bindFields()
    }

    unbind() {}

    bindFields() {
        const fieldsLength = this.$fields.length
        for (let i = 0; i < fieldsLength; i++) {
            const element = this.$fields[i]
            if (!element.classList.contains('is-enable')) {
                this.bindSingleField(element)
                element.classList.add('is-enable')
            }
        }
    }

    /**
     * @param {HTMLInputElement} element
     * @returns {boolean}
     */
    bindSingleField(element) {
        const $label = element.parentElement.querySelector('.uk-form-label')
        const labelText = $label.innerHTML
        let jsonCode = null

        if (window.Rozier.defaultMapLocation) {
            jsonCode = window.Rozier.defaultMapLocation
        } else {
            jsonCode = DEFAULT_LOCATION
        }

        const fieldId = 'geotag-canvas-' + this.uniqid()
        const fieldAddressId = fieldId + '-address'
        const resetButtonId = fieldId + '-reset'

        /*
         * prepare DOM
         */
        element.style.display = 'none'
        $label.style.display = 'none'
        element.setAttribute('data-geotag-canvas', fieldId)

        // Geocode input text
        let metaDOM = [
            '<nav class="geotag-widget-nav rz-geotag-meta">',
            '<div class="geotag-widget-nav__head">',
            '<div class="geotag-widget-nav__title"><i class="uk-icon-rz-map-marker"></i></div>',
            '<div class="geotag-widget-nav__title label">' + labelText + '</div>',
            '</div>',
            '<div class="geotag-widget-nav__content">',
            '<div class="geotag-widget-quick-creation uk-button-group">',
            '<input autocomplete="off" class="rz-geotag-address" id="' + fieldAddressId + '" type="text" value="" />',
            '<button type="button" id="' +
                resetButtonId +
                '" class="uk-button uk-button-content uk-button-table-delete rz-geotag-reset" title="' +
                window.Rozier.messages.geotag.resetMarker +
                '" data-uk-tooltip="{animation:true}"><i class="uk-icon-rz-trash-o"></i></button>',
            '</div>',
            '</div>',
            '</nav>',
            '<div class="rz-geotag-canvas" id="' + fieldId + '" style="width: 100%; height: 400px;"></div>',
        ].join('')
        const metaNode = document.createElement('div')
        metaNode.innerHTML = metaDOM
        element.after(metaNode)

        const mapContainer = document.getElementById(fieldId)
        if (!mapContainer) {
            throw new Error('Map container does not exist')
        }
        let $geocodeInput = document.getElementById(fieldAddressId)
        if ($geocodeInput) {
            $geocodeInput.setAttribute('placeholder', window.Rozier.messages.geotag.typeAnAddress)
        }
        // Reset button
        let $geocodeReset = document.getElementById(resetButtonId)
        if ($geocodeReset) {
            $geocodeReset.style.display = 'none'
        }

        /*
         * Prepare map and marker
         */
        let mapOptions = {
            center: this.createLatLng(jsonCode),
            zoom: jsonCode.zoom || jsonCode.alt,
            styles: window.Rozier.mapsStyle,
        }
        let map = this.createMap(fieldId, mapOptions)
        let marker = null

        if (element.value !== '') {
            try {
                jsonCode = JSON.parse(element.value)
                marker = this.createMarker(jsonCode, map)
                $geocodeReset.style.display = 'inline-block'
                marker.on('dragend', $.proxy(this.setMarkerEvent, this, marker, element, $geocodeReset, map))
                $geocodeReset.addEventListener(
                    'click',
                    $.proxy(this.resetMarker, this, marker, element, $geocodeReset, map)
                )
            } catch (e) {
                element.style.display = null
                document.getElementById(fieldId).style.display = 'none'
                return false
            }
        }

        map.on('click', $.proxy(this.setMarkerEvent, this, marker, element, $geocodeReset, map))
        $geocodeInput.addEventListener(
            'keypress',
            $.proxy(this.requestGeocode, this, marker, element, $geocodeReset, map)
        )
        const resetMapProxy = $.proxy(this.resetMap, this, map, marker, mapOptions)
        window.Rozier.$window.on('resize', resetMapProxy)
        window.Rozier.$window.on('pageshowend', resetMapProxy)
        window.addEventListener('NodeEditSource:tabSwitched', resetMapProxy)
        this.resetMap(map, marker, mapOptions, null)
    }

    resetMap(map, marker, mapOptions) {
        window.requestAnimationFrame(() => {
            map.invalidateSize(true)
            if (marker !== null) {
                map.panTo(marker.getLatLng())
            } else {
                map.panTo(mapOptions.center)
            }
        })
    }

    /**
     * @param {Marker} marker
     * @param {HTMLInputElement} $input
     * @param {HTMLElement} $geocodeReset
     * @param {Map} map
     * @param {Event} event
     */
    resetMarker(marker, $input, $geocodeReset, map, event) {
        event.preventDefault()
        marker.removeFrom(map)
        $input.value = ''
        $geocodeReset.style.display = 'none'
        return false
    }

    /**
     * @param {Marker|null} marker
     * @param {HTMLInputElement} $input
     * @param {HTMLElement} $geocodeReset
     * @param {Map} map
     * @param {Event} event
     */
    setMarkerEvent(marker, $input, $geocodeReset, map, event) {
        if (!marker) {
            marker = this.createAndBindMarker(event.latlng, map, $input, $geocodeReset)
        }

        if (typeof event.latlng !== 'undefined') {
            this.setMarker(marker, $input, $geocodeReset, map, event.latlng)
        } else {
            const latLng = marker.getLatLng()
            map.panTo(latLng)
            this.applyGeocode($input, $geocodeReset, latLng, map.getZoom(), undefined)
        }
    }

    /**
     * @param {LatLng} latlng
     * @param {Map} map
     * @param {HTMLInputElement} $input
     * @param {HTMLElement} $geocodeReset
     * @returns {Marker}
     */
    createAndBindMarker(latlng, map, $input, $geocodeReset) {
        const marker = this.createMarker(latlng, map)
        marker.on('dragend', $.proxy(this.setMarkerEvent, this, marker, $input, $geocodeReset, map))
        $geocodeReset.addEventListener('click', $.proxy(this.resetMarker, this, marker, $input, $geocodeReset, map))
        // reset existing click event
        map.off('click')
        map.on('click', $.proxy(this.setMarkerEvent, this, marker, $input, $geocodeReset, map))
        return marker
    }

    /**
     * @param {Marker|null} marker
     * @param {HTMLInputElement} $input
     * @param {HTMLElement} $geocodeReset
     * @param {Map} map
     * @param {LatLng} latlng
     */
    setMarker(marker, $input, $geocodeReset, map, latlng) {
        if (!marker) {
            marker = this.createAndBindMarker(latlng, map, $input, $geocodeReset)
        }
        marker.setLatLng(latlng)
        marker.addTo(map)
        map.panTo(latlng)
        this.applyGeocode($input, $geocodeReset, marker.getLatLng(), map.getZoom(), marker.name)
    }

    /**
     * @param {LatLng} latLng
     * @param {Number} zoom
     * @param {String|undefined} name
     * @returns {{geometry: {coordinates: ([*,*,*]|[*,*]), type: string}, type: string, properties: {name: string, zoom}}}
     */
    latLngToFeature(latLng, zoom, name) {
        if (latLng.alt) {
            // Remove altitude to be compatible with MySQL Geometry POINT
            zoom = latLng.alt
            latLng.alt = undefined
        }
        return {
            type: 'Feature',
            properties: {
                name: name || '',
                zoom: zoom,
            },
            geometry: {
                type: 'Point',
                coordinates: GeoJSON.latLngToCoords(latLng),
            },
        }
    }

    /**
     * @param {HTMLInputElement} $input
     * @param {HTMLElement} $geocodeReset
     * @param {LatLng} latLng
     * @param {Number} zoom
     * @param {String|undefined} name
     * @return {void}
     */
    applyGeocode($input, $geocodeReset, latLng, zoom, name) {
        $input.value = JSON.stringify(this.latLngToFeature(latLng, zoom, name))
        $geocodeReset.style.display = 'inline-block'
    }

    /**
     * @param {Object|LatLng} geocode
     * @param {Map} map
     *
     * @return Marker
     */
    createMarker(geocode, map) {
        let latLng = null
        if (geocode instanceof LatLng) {
            latLng = geocode
        } else {
            latLng = this.createLatLng(geocode)
        }
        let marker = new Marker(latLng, {
            icon: this.createIcon(),
            draggable: true,
        }).addTo(map)

        map.flyTo(latLng, latLng.alt)
        marker.alt = latLng.alt

        if (geocode.type && geocode.type === 'Feature' && geocode.properties && geocode.properties.name) {
            marker.name = geocode.properties.name
        }
        if (geocode.name) {
            marker.name = geocode.name
        }

        return marker
    }

    /**
     * @param {Marker|null} marker
     * @param {HTMLInputElement} $input
     * @param {HTMLElement} $geocodeReset
     * @param {Map} map
     * @param {Event} event
     * @return {Promise<void>}
     */
    async requestGeocode(marker, $input, $geocodeReset, map, event) {
        let address = event.currentTarget.value
        if (event.which && event.which === 13) {
            event.preventDefault()
            const latLng = await this.getLatLngForAddress(address)
            if (latLng === null) {
                console.error('Geocode was not successful.')
                return
            }
            this.setMarker(marker, $input, $geocodeReset, map, latLng)
        }
    }

    uniqid() {
        let n = new Date()
        return n.getTime()
    }

    /**
     *
     * @param {string} fieldId
     * @param {object} mapOptions
     * @returns {*}
     */
    createMap(fieldId, mapOptions) {
        const map = new Map(document.getElementById(fieldId)).setView(mapOptions.center, mapOptions.zoom)
        const osmLayer = new TileLayer(window.Rozier.leafletMapTileUrl, {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18,
        })
        map.addLayer(osmLayer)
        return map
    }

    createLatLng(data) {
        // Data is a Legacy LatLng Object
        if (typeof data.lat === 'number' && typeof data.lng === 'number') {
            return new LatLng(data.lat, data.lng, data.zoom || data.alt)
        } else if (data.type && data.type === 'Feature') {
            // Data is a GeoJSON feature
            const zoom = data.properties && data.properties.zoom ? Number.parseInt(data.properties.zoom) : 7
            const latLng = new LatLng(data.geometry.coordinates[1], data.geometry.coordinates[0], zoom)
            if (data.properties && data.properties.name) {
                latLng.name = data.properties.name
            }
            return latLng
        }
        throw new Error('Cannot create LatLng object from data')
    }

    createIcon() {
        return new Icon({
            iconUrl: window.Rozier.resourcesUrl + 'assets/img/marker.png',
            iconRetinaUrl: window.Rozier.resourcesUrl + 'assets/img/marker@2x.png',
            shadowUrl: window.Rozier.resourcesUrl + 'assets/img/marker_shadow.png',
            shadowRetinaUrl: window.Rozier.resourcesUrl + 'assets/img/marker_shadow@2x.png',
            iconSize: [22, 30], // size of the icon
            shadowSize: [25, 22], // size of the shadow
            iconAnchor: [11, 30], // point of the icon which will correspond to marker's location
            shadowAnchor: [2, 22], // the same for the shadow
        })
    }

    /**
     *
     * @param {String} address
     * @return {Promise<LatLng|null>}
     */
    getLatLngForAddress(address) {
        return GeoCodingService.geoCode(address)
            .then((response) => {
                if (response !== null) {
                    const latLng = new LatLng(response.lat, response.lon)
                    latLng.name = response.display_name
                    return latLng
                } else {
                    console.error('Geocode was not successful.')
                    return null
                }
            })
            .catch((e) => {
                console.error(e)
                return null
            })
    }
}
