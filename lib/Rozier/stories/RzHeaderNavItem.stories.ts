import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzHeaderNavItemRenderer } from '~/utils/storybook/renderer/rzHeaderNavItem'

export type Args = {
    label: string
    iconClass?: string
    active?: boolean
    subItem?: boolean
    tag?: string
    attributes?: { [key: string]: string }
}

const meta: Meta<Args> = {
    title: 'Components/Header/Item',
    tags: ['autodocs'],
    args: {
        label: 'Workspace item label',
        active: false,
        iconClass: 'rz-icon-ri--computer-line',
        subItem: false,
        tag: '',
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzHeaderNavItemRenderer(args)
    },
}

export const Link: Story = {
    render: (args) => {
        return rzHeaderNavItemRenderer(args)
    },
    args: {
        tag: 'a',
        attributes: { href: window.location.href },
    },
}

export const ButtonCustomElement: Story = {
    render: (args) => {
        return rzHeaderNavItemRenderer(args)
    },
    args: {
        tag: 'button',
        iconClass: 'rz-icon-ri--computer-line',
        attributes: { is: 'rz-header-nav-button' },
    },
    parameters: {
        controls: { exclude: ['tag'] },
    },
}
