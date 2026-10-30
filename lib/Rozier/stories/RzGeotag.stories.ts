import type { Meta, StoryObj } from '@storybook/html-vite'
import 'leaflet/dist/leaflet.css' // The backoffice loads it through vendor.less
import {
    type RzGeotagOptions,
    rzGeotagRenderer,
} from '~/utils/storybook/renderer/rzGeotag'

type Args = RzGeotagOptions

const feature = (name: string, lng: number, lat: number) => ({
    type: 'Feature',
    geometry: { type: 'Point', coordinates: [lng, lat] },
    properties: { name },
})

const meta: Meta<Args> = {
    title: 'Components/Form/Geotag',
    tags: ['autodocs'],
    render: (args) => {
        window.RozierConfig = {
            ...(window.RozierConfig || {}),
            // roadiz_core.leafletMapTileUrl default
            leafletMapTileUrl: 'https://{s}.tile.osm.org/{z}/{x}/{y}.png',
        }
        return rzGeotagRenderer(args)
    },
    args: {
        id: 'geotag-default',
        label: 'Location',
        multiple: false,
        value: JSON.stringify(feature('Lyon', 4.835659, 45.764043)),
        help: 'Click the map or search a location to place the marker.',
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}

export const Empty: Story = {
    args: {
        id: 'geotag-empty',
        value: '',
    },
}

export const Multiple: Story = {
    args: {
        id: 'geotag-multiple',
        label: 'Locations',
        multiple: true,
        value: JSON.stringify({
            type: 'FeatureCollection',
            features: [
                feature('Lyon', 4.835659, 45.764043),
                feature('Paris', 2.352222, 48.856614),
            ],
        }),
    },
}
