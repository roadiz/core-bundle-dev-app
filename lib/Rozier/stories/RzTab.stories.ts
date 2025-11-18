import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzTablistItemRenderer } from '../app/utils/storybook/renderer/rzTablist'

export type Args = {
    innerHTML: string
    selected?: boolean
    tag?: string
    attributes?: Record<string, string>
    panel?: {
        id: string
        hidden?: boolean
    }
}

const meta: Meta<Args> = {
    title: 'Components/Tab/Item',
    tags: ['autodocs'],
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

export const Default: Story = {
    render: (args) => {
        return rzTablistItemRenderer(args)
    },
}

export const WithIcon: Story = {
    render: (args) => {
        return rzTablistItemRenderer(args)
    },
    args: {
        innerHTML: `tab with icon<span class="rz-icon-ri--edit-line"></span>`,
    },
}

export const UnderlineSelected: Story = {
    render: (args) => {
        return rzTablistItemRenderer(args)
    },
    args: {
        selected: true,
        innerHTML: 'Underlined Selected Tab',
    },
}

export const Link: Story = {
    render: (args) => {
        return rzTablistItemRenderer(args)
    },
    args: {
        tag: 'a',
        innerHTML: 'Link Tab',
        attributes: { href: '#' },
    },
}

export const IconOnly: Story = {
    render: (args) => {
        return rzTablistItemRenderer(args)
    },
    args: {
        attributes: { 'aria-label': 'Icon tab label' },
        innerHTML: `<span class="rz-icon-ri--edit-line"></span>`,
    },
}
