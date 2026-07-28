import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzBrandRenderer } from '~/utils/storybook/renderer/rzBrand'

export type Args = {
    tag?: string
    innerText?: string
    iconClass?: string
    color?: string
    attributes?: Record<string, string>
}

const meta: Meta<Args> = {
    title: 'Components/Brand',
    tags: ['autodocs'],
    args: {
        tag: 'button',
        innerText: 'RZ',
        iconClass: 'rz-icon-rz--logo-rz',
        color: '',
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzBrandRenderer(args)
    },
}
