import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type RzBrandOptions,
    rzBrandRenderer,
} from '~/utils/storybook/renderer/rzBrand'

type Args = RzBrandOptions

const meta: Meta<Args> = {
    title: 'Foundations/Brand',
    tags: ['autodocs'],
    render: (args) => rzBrandRenderer(args),
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

export const Default: Story = {}
