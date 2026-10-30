import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type RzTablistItemOptions,
    rzTablistItemRenderer,
} from '~/utils/storybook/renderer/rzTablist'

type Args = RzTablistItemOptions

const meta: Meta<Args> = {
    title: 'Components/Navigation/Tablist/Item',
    tags: ['autodocs'],
    render: (args) => rzTablistItemRenderer(args),
    args: {
        innerHTML: 'Tab label',
        tag: 'button',
        selected: false,
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {}

export const WithIcon: Story = {
    args: {
        innerHTML: `tab with icon<span class="rz-icon-ri--edit-line"></span>`,
    },
}

export const UnderlineSelected: Story = {
    args: {
        selected: true,
        innerHTML: 'Underlined Selected Tab',
    },
}

export const Link: Story = {
    args: {
        tag: 'a',
        innerHTML: 'Link Tab',
        attributes: { href: '#' },
    },
}

export const IconOnly: Story = {
    args: {
        attributes: { 'aria-label': 'Icon tab label' },
        innerHTML: `<span class="rz-icon-ri--edit-line"></span>`,
    },
}
