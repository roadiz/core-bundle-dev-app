import type { Meta, StoryObj } from '@storybook/html-vite'
import { BadgeArgs } from './RzBadge.stories'
import {
    rzPopoverItemRenderer,
    DEFAULT_ITEM,
} from '~/utils/storybook/renderer/rzPopoverItem'

export type Args = {
    iconClass?: string
    label?: string
    description?: string
    badge?: BadgeArgs
    rightIconClass?: string
    tag?: string
    attributes?: Record<string, string>
}

const meta: Meta<Args> = {
    title: 'Components/Popover/Item',
    tags: ['autodocs'],
    args: {
        ...DEFAULT_ITEM,
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzPopoverItemRenderer(args)
    },
}

export const Link: Story = {
    render: (args) => {
        return rzPopoverItemRenderer(args)
    },
    args: {
        tag: 'a',
        label: 'Découvrir le site',
        iconClass: 'rz-icon-ri--earth-line',
        attributes: {
            href: '#',
        },
        badge: undefined,
        description: undefined,
    },
}
