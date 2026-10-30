import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type RzColorInputOptions,
    rzColorInputRenderer,
} from '~/utils/storybook/renderer/rzColorInput'

type Args = RzColorInputOptions

const meta: Meta<Args> = {
    title: 'Components/Form/ColorInput',
    tags: ['autodocs'],
    render: (args) => rzColorInputRenderer(args),
    args: {
        name: 'color-input-name',
        placeholder: 'Select a color',
        textPattern: '^#[0-9A-Fa-f]{6}$',
        textMaxLength: 7,
        value: '',
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    args: {
        id: 'color-input-1',
        placeholder: 'Select a color',
    },
}

export const WithDefaultValue: Story = {
    args: {
        id: 'color-input-2',
        value: '#00ff00',
    },
}
