import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type RzHeaderNavItemOptions,
    rzHeaderNavItemRenderer,
} from '~/utils/storybook/renderer/rzHeaderNavItem'

type Args = RzHeaderNavItemOptions

const meta: Meta<Args> = {
    title: 'Components/Navigation/HeaderNavItem',
    tags: ['autodocs'],
    render: (args) => rzHeaderNavItemRenderer(args),
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

export const Default: Story = {}

export const Link: Story = {
    args: {
        tag: 'a',
        attributes: { href: window.location.href },
    },
}

export const ButtonCustomElement: Story = {
    args: {
        tag: 'button',
        iconClass: 'rz-icon-ri--computer-line',
        attributes: { is: 'rz-header-nav-item-button' },
    },
    parameters: {
        controls: { exclude: ['tag'] },
    },
}
