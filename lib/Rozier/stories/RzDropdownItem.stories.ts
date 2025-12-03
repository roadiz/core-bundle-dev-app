import type { Meta, StoryObj } from '@storybook/html-vite'
import { BadgeArgs } from './RzBadge.stories'
import {
    rzDropdownItemRenderer,
    DEFAULT_ITEM,
} from '~/utils/storybook/renderer/rzDropDownItem'

export type Args = {
    iconClass?: string
    label?: string
    description?: string
    badge?: BadgeArgs
    rightIconClass?: string
    tag?: string
    attributes?: Record<string, string>
    selected?: boolean
}

const meta: Meta<Args> = {
    title: 'Components/Overlay/Dropdown/Item',
    tags: ['autodocs'],
    args: {
        ...DEFAULT_ITEM,
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzDropdownItemRenderer(args)
    },
}

export const Selected: Story = {
    render: (args) => {
        return rzDropdownItemRenderer(args)
    },
    args: {
        selected: true,
    },
}

export const Link: Story = {
    render: (args) => {
        return rzDropdownItemRenderer(args)
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
