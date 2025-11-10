import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzColorInputRenderer } from '~/utils/storybook/renderer/rzColorInput'

export type Args = {
    name: string
    placeholder?: string
    value?: string
    required?: boolean
    id?: string
    textPattern?: string
    textMaxLength?: number
}

const meta: Meta<Args> = {
    title: 'Components/Form/ColorInput',
    tags: ['autodocs'],
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
    render: (args) => {
        return rzColorInputRenderer(args)
    },
    args: {
        id: 'color-input-1',
        placeholder: 'Select a color',
    },
}

export const WithDefaultValue: Story = {
    render: (args) => {
        return rzColorInputRenderer(args)
    },
    args: {
        id: 'color-input-2',
        value: '#00ff00',
    },
}
