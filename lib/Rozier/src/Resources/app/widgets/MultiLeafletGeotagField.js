import LeafletGeotagField, {DEFAULT_LOCATION} from './LeafletGeotagField'
import {LatLngBounds} from 'leaflet'

export default class MultiLeafletGeotagField extends LeafletGeotagField {
    constructor() {
        super()
        this.$fields = document.querySelectorAll('.rz-multi-geotag-field:not(.is-enable)')

        if (this.$fields.length) {
            this.init()
        }
    }

    /**
     * @param {Array<Marker>} markers
     * @param {HTMLInputElement} $input
     * @param {HTMLElement} $geocodeReset
     * @param {Map} map
     * @param {HTMLElement} $selector
     * @returns {boolean}
     */
    resetMarker(markers, $input, $geocodeReset, map, $selector) {
        $input.value = ''

        for (const marker of markers) {
            if (marker !== null) {
                marker.removeFrom(map)
            }
        }
        markers = []
        $geocodeReset.style.display = 'none'
        this.syncSelector($selector, markers, map, $input)

        return false
    }

    /**
     * @param {HTMLInputElement} element
     * @returns {boolean}
     */
    bindSingleField(element) {
        /** @type {HTMLLabelElement} */
        const $label = element.parentElement.querySelector('.uk-form-label')
        const labelText = $label.innerHTML || 'Geotag'
        $label.style.display = 'none'

        let jsonCode = null
        if (window.Rozier.defaultMapLocation) {
            jsonCode = window.Rozier.defaultMapLocation
        } else {
            jsonCode = DEFAULT_LOCATION
        }
        const fieldId = 'geotag-canvas-' + this.uniqid()
        const fieldAddressId = fieldId + '-address'
        const resetButtonId = fieldId + '-reset'
        const mapOptions = {
            center: this.createLatLng(jsonCode),
            zoom: jsonCode.zoom || jsonCode.alt,
            scrollwheel: false,
            styles: window.Rozier.mapsStyle,
        }

        // Prepare DOM
        element.style.display = 'none'
        element.setAttribute('data-geotag-canvas', fieldId)

        // Geocode input text
        let metaDOM = [
            '<nav class="geotag-widget-nav rz-geotag-meta">',
            '<div class="geotag-widget-nav__head">',
            '<div class="geotag-widget-nav__title"><i class="uk-icon-rz-map-multi-marker"></i></div>',
            '<div class="geotag-widget-nav__title label">' + labelText + '</div>',
            '</div>',
            '<div class="geotag-widget-nav__content">',
            '<div class="geotag-widget-quick-creation uk-button-group">',
            '<input autocomplete="off" class="rz-geotag-address" id="' + fieldAddressId + '" type="text" value="" />',
            '<button type="button" id="' +
                resetButtonId +
                '" class="uk-button uk-button-content uk-button-danger rz-geotag-reset" title="' +
                window.Rozier.messages.geotag.resetMarker +
                '" data-uk-tooltip="{animation:true}"><i class="uk-icon-rz-trash-o"></i></button>',
            '</div>',
            '</div>',
            '</nav>',
            '<div class="multi-geotag-group">',
            '<ul class="multi-geotag-list-markers">',
            '</ul>',
            '<div class="rz-geotag-canvas" id="' + fieldId + '"></div>',
            '</div>',
        ].join('')
        const metaNode = document.createElement('div')
        metaNode.innerHTML = metaDOM
        element.after(metaNode)

        let $geocodeInput = document.getElementById(fieldAddressId)
        $geocodeInput.setAttribute('placeholder', window.Rozier.messages.geotag.typeAnAddress)
        // Reset button
        let $geocodeReset = document.getElementById(resetButtonId)
        $geocodeReset.style.display = 'none'

        /*
         * Prepare map and marker
         */
        const map = this.createMap(fieldId, mapOptions)
        const markers = []
        /** @type {HTMLElement} */
        const $selector = element.parentElement.querySelector('.multi-geotag-list-markers')

        if (element.value !== '') {
            try {
                const featureCollection = JSON.parse(element.value)
                if (!featureCollection.features) {
                    throw new Error('Data is not a valid GeoJSON featureCollection')
                }

                for (const feature of featureCollection.features) {
                    const marker = this.createMarker(feature, map)
                    marker.on(
                        'dragend',
                        this.setMarkerEvent.bind(this, marker, markers, element, $geocodeReset, map, $selector)
                    )
                    markers.push(marker)
                }
            } catch (e) {
                element.style.display = 'block'
                document.getElementById(fieldId).style.display = 'none'
                return false
            }
        }

        map.on('click', this.setMarkerEvent.bind(this, null, markers, element, $geocodeReset, map, $selector))
        $geocodeInput.addEventListener(
            'keypress',
            this.requestGeocode.bind(this, markers, element, $geocodeReset, map, $selector)
        )

        this.resetMap(map, markers, mapOptions, null)
        this.syncSelector($selector, markers, map, element)

        // Use a resize observer to invalidate the map when the container changes size or is hidden
        const resizeObserver = new ResizeObserver((entries) => {
            map.invalidateSize({
                animate: false,
                pan: false,
            })
            if (typeof markers !== 'undefined' && markers.length > 0) {
                map.fitBounds(this.getMediumLatLng(markers), {
                    animate: false,
                    pan: false,
                })
            } else {
                map.panTo(mapOptions.center, {
                    animate: false,
                    pan: false,
                })
            }
        })
        resizeObserver.observe(document.getElementById(fieldId))
    }

    /**
     * @param {HTMLElement} $selector
     * @param {Array<Marker>} markers
     * @param {Map} map
     * @param {HTMLInputElement} $input
     */
    syncSelector($selector, markers, map, $input) {
        $selector.innerHTML = ''
        const innerHtml = []
        let i = 0
        const actualMarkers = markers.filter((marker) => marker !== null && marker.getLatLng() !== null)

        for (const marker of actualMarkers) {
            ;[
                '<li>',
                '<span class="multi-geotag-marker-name">',
                marker.name ? marker.name : '#' + i,
                '</span>',
                '<span class="uk-button-group">',
                '<button type="button" class="uk-button uk-button-mini rz-multi-geotag-center" data-geocode-id="' +
                    i +
                    '"><i class="uk-icon-rz-marker"></i></button>',
                '<button type="button" class="uk-button uk-button-mini rz-multi-geotag-remove" data-geocode-id="' +
                    i +
                    '"><i class="uk-icon-rz-trash-o"></i></button>',
                '</span>',
                '</li>',
            ].forEach((item) => innerHtml.push(item))
            i++
        }
        $selector.innerHTML = innerHtml.join('')

        let j = 0
        for (const marker of actualMarkers) {
            let $centerBtn = $selector.querySelector('.rz-multi-geotag-center[data-geocode-id="' + j + '"]')
            let $removeBtn = $selector.querySelector('.rz-multi-geotag-remove[data-geocode-id="' + j + '"]')
            if ($centerBtn && $removeBtn) {
                $centerBtn.addEventListener('click', this.centerMap.bind(this, map, marker))
                $removeBtn.addEventListener(
                    'click',
                    this.removeMarker.bind(this, map, markers, marker, $selector, $input)
                )
            }
            j++
        }
    }

    /**
     * @param {Map} map
     * @param {Array<Marker>} markers
     * @param {Marker} marker
     * @param {HTMLElement} $selector
     * @param {HTMLInputElement} $input
     * @param {Event|undefined} event
     * @return {boolean}
     */
    removeMarker(map, markers, marker, $selector, $input, event) {
        if (event) {
            event.preventDefault()
        }

        const index = markers.indexOf(marker)
        marker.removeFrom(map)
        markers.splice(index, 1)
        this.syncSelector($selector, markers, map, $input)
        this.writeMarkers(markers, $input)

        return false
    }

    resetMap(map, markers, mapOptions, event) {
        if (event) {
            event.preventDefault()
        }

        window.requestAnimationFrame(() => {
            map.invalidateSize({
                animate: true,
                pan: true,
            })
            if (typeof markers !== 'undefined' && markers.length > 0) {
                map.fitBounds(this.getMediumLatLng(markers))
            } else {
                map.panTo(mapOptions.center)
            }
        })
    }

    /**
     * @param {Map} map
     * @param {Marker} marker
     * @param {Event} event
     */
    centerMap(map, marker, event) {
        if (event) {
            event.preventDefault()
        }
        if (map && marker) {
            const latLng = marker.getLatLng()
            map.flyTo(latLng, latLng.alt)
        }
    }

    /**
     * @param {Array<Marker>} markers
     * @returns {LatLngBounds}
     */
    getMediumLatLng(markers) {
        let bounds = new LatLngBounds()
        for (const marker of markers) {
            bounds.extend(marker.getLatLng())
        }
        return bounds
    }

    /**
     * @param {Marker|null} marker
     * @param {Array<Marker>} markers
     * @param {HTMLInputElement} $input
     * @param {HTMLElement} $geocodeReset
     * @param {Map} map
     * @param {Event|MouseEvent} event
     * @param {HTMLElement} $selector
     */
    setMarkerEvent(marker, markers, $input, $geocodeReset, map, $selector, event) {
        if (event.latlng) {
            this.setMarker(marker, markers, $input, $geocodeReset, map, event.latlng)
        } else if (marker !== null) {
            const latLng = marker.getLatLng()
            latLng.alt = map.getZoom()
            marker.setLatLng(latLng)
            map.flyTo(latLng, latLng.alt)
            this.writeMarkers(markers, $input)
        }

        this.syncSelector($selector, markers, map, $input)
    }

    /**
     * @param {Marker} marker
     * @param {Array<Marker>} markers
     * @param {HTMLInputElement} $input
     * @param {HTMLElement} $geocodeReset
     * @param {Map|null} map
     * @param {LatLng} latLng
     * @param {String} name
     * @returns {Marker}
     */
    setMarker(marker, markers, $input, $geocodeReset, map, latLng, name) {
        if (map) {
            if (!latLng.alt) {
                latLng.alt = map.getZoom()
            }

            if (marker === null) {
                marker = this.createMarker(latLng, map)
            } else {
                marker.setLatLng(latLng)
                marker.addTo(map)
            }

            marker.name = name
            map.flyTo(latLng, latLng.alt)
            markers.push(marker)
            this.writeMarkers(markers, $input)
        }

        return marker
    }

    /**
     * Convert markers to GeoJSON.
     *
     * @param {Array<Marker>} markers
     * @param {HTMLInputElement} $input
     */
    writeMarkers(markers, $input) {
        const featuresCollection = {
            type: 'FeatureCollection',
            features: [],
        }
        for (const marker of markers) {
            if (marker) {
                const latLng = marker.getLatLng()
                featuresCollection.features.push(this.latLngToFeature(latLng, latLng.alt, marker.name))
            }
        }
        $input.value = JSON.stringify(featuresCollection)
    }

    /**
     * @param {Array<Marker>} markers
     * @param {HTMLInputElement} $input
     * @param {HTMLElement} $geocodeReset
     * @param {Map} map
     * @param {HTMLElement} $selector
     * @param {Event} event
     * @return {Promise<void>}
     */
    async requestGeocode(markers, $input, $geocodeReset, map, $selector, event) {
        let address = event.currentTarget.value
        if (event.which && event.which === 13) {
            event.preventDefault()
            const latLng = await this.getLatLngForAddress(address)
            if (latLng === null) {
                console.error('Geocode was not successful.')
                return
            }

            latLng.alt = map.getZoom()
            this.setMarker(null, markers, $input, $geocodeReset, map, latLng, latLng.name || '')
            this.syncSelector($selector, markers, map, $input)
        }
    }
}
