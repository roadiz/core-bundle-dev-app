import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzTabRenderer, VARIANTS } from '../app/utils/storybook/renderer/rzTab'

export type Args = {
    innerHTML: string
    variant?: (typeof VARIANTS)[number]
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
    argTypes: {
        variant: {
            control: 'select',
            options: ['', ...VARIANTS],
        },
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzTabRenderer(args)
    },
}

export const FilledSelected: Story = {
    render: (args) => {
        return rzTabRenderer(args)
    },
    args: {
        variant: 'filled',
        selected: true,
        innerHTML: 'Filled Selected Tab',
    },
}

export const Underline: Story = {
    render: (args) => {
        return rzTabRenderer(args)
    },
    args: {
        variant: 'underlined',
        innerHTML: 'Underlined Tab',
    },
}

export const UnderlineSelected: Story = {
    render: (args) => {
        return rzTabRenderer(args)
    },
    args: {
        variant: 'underlined',
        selected: true,
        innerHTML: 'Underlined Selected Tab',
    },
}

export const Link: Story = {
    render: (args) => {
        return rzTabRenderer(args)
    },
    args: {
        tag: 'a',
        innerHTML: 'Link Tab',
        attributes: { href: '#' },
    },
}

export const Icon: Story = {
    render: (args) => {
        return rzTabRenderer(args)
    },
    args: {
        attributes: { 'aria-label': 'Icon tab label' },
        innerHTML: `<span class="rz-icon-ri--edit-line"></span>`,
    },
}

export const IconUnderline: Story = {
    render: (args) => {
        return rzTabRenderer(args)
    },
    args: {
        ...Icon.args,
        variant: 'underlined',
    },
}
