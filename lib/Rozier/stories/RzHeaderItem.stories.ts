import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzHeaderItemRenderer } from '~/utils/storybook/renderer/rzHeaderItem'

export type Args = {
    label: string
    iconClass?: string
    active?: boolean
    variants?: 'level-1' | 'level-2'
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
        variants: 'level-1',
        tag: '',
    },
    argTypes: {
        variants: {
            options: ['level-1', 'level-2'],
            control: { type: 'radio' },
        },
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzHeaderItemRenderer(args)
    },
}

export const Link: Story = {
    render: (args) => {
        return rzHeaderItemRenderer(args)
    },
    args: {
        tag: 'a',
        attributes: { href: window.location.href },
    },
}

export const ButtonCustomElement: Story = {
    render: (args) => {
        return rzHeaderItemRenderer(args)
    },
    args: {
        tag: 'button',
        iconClass: 'rz-icon-ri--computer-line',
        attributes: { is: 'rz-workspace-item-button' },
    },
    parameters: {
        controls: { exclude: ['tag'] },
    },
}
