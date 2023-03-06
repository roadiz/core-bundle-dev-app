import $ from 'jquery'
import { Map, Marker, LatLng, TileLayer, Icon, GeoJSON } from 'leaflet'
import GeoCodingService from '../services/GeoCodingService'

export const DEFAULT_LOCATION = { lat: 45.769785, lng: 4.833967, zoom: 14 }

export default class LeafletGeotagField {
    constructor() {
        this.$fields = $('.rz-geotag-field:not(.is-enable)')

        if (this.$fields.length) {
            this.init()
        }
    }

    init() {
        if (!this.$fields.hasClass('is-enable')) {
            this.$fields.addClass('is-enable')
            this.bindFields()
        }
    }

    unbind() {}

    bindFields() {
        this.$fields.each((index, element) => {
            this.bindSingleField(element)
        })
    }

    bindSingleField(element) {
        const $input = $(element)
        const $label = $input.parent().find('.uk-form-label')
        const labelText = $label[0].innerHTML
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
        $input.hide()
        $label.hide()
        $input.attr('data-geotag-canvas', fieldId)
        $input.after('<div class="rz-geotag-canvas" id="' + fieldId + '" style="width: 100%; height: 400px;"></div>')

        // Geocode input text
        let metaDOM = [
            '<nav class="geotag-widget-nav uk-navbar rz-geotag-meta">',
            '<ul class="uk-navbar-nav">',
            '<li class="uk-navbar-brand"><i class="uk-icon-rz-map-marker"></i>',
            '<li class="uk-navbar-brand label">' + labelText + '</li>',
            '</ul>',
            '<div class="uk-navbar-content uk-navbar-flip">',
            '<div class="geotag-widget-quick-creation uk-button-group">',
            '<input class="rz-geotag-address" id="' + fieldAddressId + '" type="text" value="" />',
            '<button id="' +
                resetButtonId +
                '" class="uk-button uk-button-content uk-button-table-delete rz-geotag-reset" title="' +
                window.Rozier.messages.geotag.resetMarker +
                '" data-uk-tooltip="{animation:true}"><i class="uk-icon-rz-trash-o"></i></button>',
            '</div>',
            '</div>',
            '</nav>',
        ].join('')

        $input.after(metaDOM)
        let $geocodeInput = $('#' + fieldAddressId)
        $geocodeInput.attr('placeholder', window.Rozier.messages.geotag.typeAnAddress)
        // Reset button
        let $geocodeReset = $('#' + resetButtonId)
        $geocodeReset.hide()

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

        if ($input.val() !== '') {
            try {
                jsonCode = JSON.parse($input.val())
                marker = this.createMarker(jsonCode, map)
                $geocodeReset.show()
            } catch (e) {
                $input.show()
                $(document.getElementById(fieldId)).hide()
                return false
            }
        } else {
            marker = this.createMarker(jsonCode, map)
        }

        marker.on('dragend', $.proxy(this.setMarkerEvent, this, marker, $input, $geocodeReset, map))
        map.on('click', $.proxy(this.setMarkerEvent, this, marker, $input, $geocodeReset, map))

        $geocodeInput.on('keypress', $.proxy(this.requestGeocode, this, marker, $input, $geocodeReset, map))
        $geocodeReset.on('click', $.proxy(this.resetMarker, this, marker, $input, $geocodeReset, map))
        window.Rozier.$window.on('resize', $.proxy(this.resetMap, this, map, marker, mapOptions))
        window.Rozier.$window.on('pageshowend', $.proxy(this.resetMap, this, map, marker, mapOptions))
        this.resetMap(map, marker, mapOptions, null)
    }

    resetMap(map, marker, mapOptions) {
        window.setTimeout(() => {
            map.invalidateSize(true)
            if (marker !== null) {
                map.panTo(marker.getLatLng())
            } else {
                map.panTo(mapOptions.center)
            }
        }, 400)
    }

    /**
     * @param {Object} marker
     * @param {jQuery} $input
     * @param {jQuery} $geocodeReset
     * @param {Map} map
     * @param {Event} event
     */
    resetMarker(marker, $input, $geocodeReset, map, event) {
        marker.removeFrom(map)
        $input.val('')
        $geocodeReset.hide()
        return false
    }

    /**
     * @param {Marker} marker
     * @param {jQuery} $input
     * @param {jQuery} $geocodeReset
     * @param {Map} map
     * @param {Event} event
     */
    setMarkerEvent(marker, $input, $geocodeReset, map, event) {
        if (typeof event.latlng !== 'undefined') {
            this.setMarker(marker, $input, $geocodeReset, map, event.latlng)
        } else if (marker !== null) {
            const latLng = marker.getLatLng()
            map.panTo(latLng)
            this.applyGeocode($input, $geocodeReset, latLng, map.getZoom(), undefined)
        }
    }

    /**
     * @param {Marker} marker
     * @param {jQuery} $input
     * @param {jQuery} $geocodeReset
     * @param {Map} map
     * @param {LatLng} latlng
     */
    setMarker(marker, $input, $geocodeReset, map, latlng) {
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
     * @param {jQuery} $input
     * @param {jQuery} $geocodeReset
     * @param {LatLng} latLng
     * @param {Number} zoom
     * @param {String|undefined} name
     * @return {void}
     */
    applyGeocode($input, $geocodeReset, latLng, zoom, name) {
        $input.val(JSON.stringify(this.latLngToFeature(latLng, zoom, name)))
        $geocodeReset.show()
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
     * @param {Marker} marker
     * @param {jQuery} $input
     * @param {jQuery} $geocodeReset
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
     * @param fieldId
     * @param mapOptions
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
            const latLng = new LatLng(
                data.geometry.coordinates[1],
                data.geometry.coordinates[0],
                data.properties.zoom || 7
            )
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
     * @return Promise<LatLng|null>
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
