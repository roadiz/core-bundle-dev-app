import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzBadgeRenderer } from '~/utils/storybook/renderer/rzBadge'

const SIZES = ['xs', 'sm', 'md'] as const
const COLORS = ['information', 'success', 'warning', 'error'] as const

export type BadgeArgs = {
    iconClass?: string
    label?: string
    title?: string
    size?: (typeof SIZES)[number]
    color?: (typeof COLORS)[number]
}

const meta: Meta<BadgeArgs> = {
    title: 'Components/Badge',
    tags: ['autodocs'],
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
type Story = StoryObj<BadgeArgs>

export const Default: Story = {
    render: (args) => {
        return rzBadgeRenderer(args)
    },
}

export const IconOnly: Story = {
    render: (args) => {
        return rzBadgeRenderer(args)
    },
    args: {
        label: '',
    },
}

export const Information: Story = {
    render: (args) => {
        return rzBadgeRenderer({
            ...args,
            label: 'Information',
            color: 'information',
            iconClass: 'rz-icon-rz--status-published-colored',
        })
    },
}

export const Published: Story = {
    render: (args) => {
        return rzBadgeRenderer({
            ...args,
            label: 'Published',
            color: 'success',
            iconClass: 'rz-icon-rz--status-published-colored',
        })
    },
}

export const Draft: Story = {
    render: (args) => {
        return rzBadgeRenderer({
            ...args,
            label: 'Draft',
            color: 'warning',
            iconClass: 'rz-icon-rz--status-draft-colored',
        })
    },
}

export const Unpublished: Story = {
    render: (args) => {
        return rzBadgeRenderer({
            ...args,
            label: 'Unpublished',
            color: 'error',
            iconClass: 'rz-icon-rz--status-draft-colored',
        })
    },
}
