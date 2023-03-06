import $ from 'jquery'
import LeafletGeotagField, { DEFAULT_LOCATION } from './LeafletGeotagField'
import { LatLngBounds } from 'leaflet'

export default class MultiLeafletGeotagField extends LeafletGeotagField {
    constructor() {
        super()
        this.$fields = $('.rz-multi-geotag-field:not(.is-enable)')

        if (this.$fields.length) {
            this.init()
        }
    }

    /**
     * @param {Array<Marker>} markers
     * @param {jQuery} $input
     * @param {jQuery} $geocodeReset
     * @param {Map} map
     * @param {jQuery} $selector
     * @returns {boolean}
     */
    resetMarker(markers, $input, $geocodeReset, map, $selector) {
        $input.val('')

        for (const marker of markers) {
            if (marker !== null) {
                marker.removeFrom(map)
            }
        }
        markers = []
        $geocodeReset.hide()
        this.syncSelector($selector, markers, map, $input)

        return false
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
        const mapOptions = {
            center: this.createLatLng(jsonCode),
            zoom: jsonCode.zoom || jsonCode.alt,
            scrollwheel: false,
            styles: window.Rozier.mapsStyle,
        }

        // Prepare DOM
        $input.hide()
        $label.hide()
        $input.attr('data-geotag-canvas', fieldId)

        // Geocode input text
        let metaDOM = [
            '<nav class="geotag-widget-nav uk-navbar rz-geotag-meta">',
            '<ul class="uk-navbar-nav">',
            '<li class="uk-navbar-brand"><i class="uk-icon-rz-map-multi-marker"></i>',
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
            '<div class="multi-geotag-group">',
            '<ul class="multi-geotag-list-markers">',
            '</ul>',
            '<div class="rz-geotag-canvas" id="' + fieldId + '"></div>',
            '</div>',
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
        const map = this.createMap(fieldId, mapOptions)
        const markers = []
        const $selector = $input.parent().find('.multi-geotag-list-markers').eq(0)

        if ($input.val() !== '') {
            try {
                const featureCollection = JSON.parse($input.val())
                if (!featureCollection.features) {
                    throw new Error('Data is not a valid GeoJSON featureCollection')
                }

                for (const feature of featureCollection.features) {
                    const marker = this.createMarker(feature, map)
                    marker.on(
                        'dragend',
                        $.proxy(this.setMarkerEvent, this, marker, markers, $input, $geocodeReset, map, $selector)
                    )
                    markers.push(marker)
                }
                $geocodeReset.show()
            } catch (e) {
                $input.show()
                $(document.getElementById(fieldId)).hide()
                return false
            }
        }

        map.on('click', $.proxy(this.setMarkerEvent, this, null, markers, $input, $geocodeReset, map, $selector))
        $geocodeInput.on('keypress', $.proxy(this.requestGeocode, this, markers, $input, $geocodeReset, map, $selector))
        $geocodeReset.on('click', $.proxy(this.resetMarker, this, markers, $input, $geocodeReset, map, $selector))
        window.Rozier.$window.on('resize', $.proxy(this.resetMap, this, map, markers, mapOptions))
        window.Rozier.$window.on('pageshowend', $.proxy(this.resetMap, this, map, markers, mapOptions))
        this.resetMap(map, markers, mapOptions, null)
        this.syncSelector($selector, markers, map, $input)
    }

    /**
     * @param {jQuery} $selector
     * @param {Array<Marker>} markers
     * @param {Map} map
     * @param {jQuery} $input
     */
    syncSelector($selector, markers, map, $input) {
        $selector.empty()
        let i = 0

        for (const marker of markers) {
            if (marker === null) {
                continue
            }
            const geocode = marker.getLatLng()
            if (geocode) {
                $selector.append(
                    [
                        '<li>',
                        '<span class="multi-geotag-marker-name">',
                        marker.name ? marker.name : '#' + i,
                        '</span>',
                        '<span class="uk-button-group">',
                        '<button class="uk-button uk-button-mini rz-multi-geotag-center" data-geocode-id="' +
                            i +
                            '"><i class="uk-icon-rz-marker"></i></button>',
                        '<button class="uk-button uk-button-mini rz-multi-geotag-remove" data-geocode-id="' +
                            i +
                            '"><i class="uk-icon-rz-trash-o"></i></button>',
                        '</span>',
                        '</li>',
                    ].join('')
                )

                let $centerBtn = $selector.find('.rz-multi-geotag-center[data-geocode-id="' + i + '"]').eq(0)
                let $removeBtn = $selector.find('.rz-multi-geotag-remove[data-geocode-id="' + i + '"]').eq(0)
                $centerBtn.on('click', $.proxy(this.centerMap, this, map, marker))
                $removeBtn.on('click', $.proxy(this.removeMarker, this, map, markers, marker, $selector, $input))
            }
            i++
        }
    }

    /**
     * @param {Map} map
     * @param {Array<Marker>} markers
     * @param {Marker} marker
     * @param {jQuery} $selector
     * @param {jQuery} $input
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

        map.invalidateSize(true)
        if (typeof markers !== 'undefined' && markers.length > 0) {
            map.fitBounds(this.getMediumLatLng(markers))
        } else {
            map.panTo(mapOptions.center)
        }
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
     * @param {jQuery} $input
     * @param {jQuery} $geocodeReset
     * @param {Map} map
     * @param {Event|MouseEvent} event
     * @param {jQuery} $selector
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
     * @param {jQuery} $input
     * @param {jQuery} $geocodeReset
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
            $geocodeReset.show()
        }

        return marker
    }

    /**
     * Convert markers to GeoJSON.
     *
     * @param {Array<Marker>} markers
     * @param {jQuery} $input
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
        $input.val(JSON.stringify(featuresCollection))
    }

    /**
     * @param {Array<Marker>} markers
     * @param {jQuery} $input
     * @param {jQuery} $geocodeReset
     * @param {Map} map
     * @param {jQuery} $selector
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
