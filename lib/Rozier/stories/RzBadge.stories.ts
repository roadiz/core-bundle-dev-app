import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type RzBadgeOptions,
    rzBadgeRenderer,
    SIZES,
    COLORS,
} from '~/utils/component-renderer/rzBadge'

type Args = RzBadgeOptions

const meta: Meta<Args> = {
    title: 'Components/Data display/Badge',
    tags: ['autodocs'],
    render: (args) => rzBadgeRenderer(args),
    args: {
        label: 'My badge',
        iconClass: 'rz-icon-ri--add-line',
    },
    argTypes: {
        size: {
            options: ['', ...SIZES],
            control: { type: 'radio' },
            type: 'string',
            description:
                'If no size class is provided, size sm is applied by default.',
        },
        color: {
            options: ['', ...COLORS],
            control: { type: 'radio' },
            table: { type: { summary: 'string' } },
        },
    },
    globals: {
        backgrounds: { value: 'light' },
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}

export const IconOnly: Story = {
    args: {
        label: '',
    },
}

export const Information: Story = {
    args: {
        label: 'Information',
        color: 'information',
        iconClass: 'rz-icon-rz--status-published-fill',
    },
}

export const Published: Story = {
    args: {
        label: 'Published',
        color: 'success',
        iconClass: 'rz-icon-rz--status-published-fill',
    },
}

export const Draft: Story = {
    args: {
        label: 'Draft',
        color: 'warning',
        iconClass: 'rz-icon-rz--status-draft-fill',
    },
}

export const Unpublished: Story = {
    args: {
        label: 'Unpublished',
        color: 'danger',
        iconClass: 'rz-icon-rz--status-draft-fill',
    },
}
