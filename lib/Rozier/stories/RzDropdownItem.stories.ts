import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    DEFAULT_ITEM,
    type RzDropdownItemOptions,
    rzDropdownItemRenderer,
} from '~/utils/storybook/renderer/rzDropDown'

type Args = RzDropdownItemOptions

const meta: Meta<Args> = {
    title: 'Components/Overlay/Dropdown/Item',
    tags: ['autodocs'],
    render: (args) => rzDropdownItemRenderer(args),
    args: {
        ...DEFAULT_ITEM,
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}

export const Selected: Story = {
    args: {
        selected: true,
    },
}

export const Link: Story = {
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
