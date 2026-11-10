import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    DEFAULT_BULK_ACTIONS,
    type RzBulkActionsOptions,
    rzBulkActionsRenderer,
} from '~/utils/storybook/renderer/rzBulkActions'

type Args = RzBulkActionsOptions

const meta: Meta<Args> = {
    title: 'Components/Actions/BulkActions',
    tags: ['autodocs'],
    render: (args) => rzBulkActionsRenderer(args),
    args: {
        ...DEFAULT_BULK_ACTIONS,
        // Hidden until rz-bulk-section selects an item, see Components/Actions/BulkSection
        visible: true,
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}

export const WithoutPanels: Story = {
    args: {
        actions: [
            {
                button: {
                    emphasis: 'primary',
                    color: 'success',
                    iconClass: 'rz-icon-ri--check-line',
                    attributes: { type: 'submit', title: 'Enable' },
                },
            },
            {
                button: {
                    emphasis: 'primary',
                    color: 'success',
                    iconClass: 'rz-icon-ri--close-line',
                    attributes: { type: 'submit', title: 'Disable' },
                },
            },
        ],
    },
}
