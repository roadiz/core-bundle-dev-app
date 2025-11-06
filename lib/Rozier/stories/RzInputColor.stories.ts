import type { Meta, StoryObj } from '@storybook/html-vite'

export type BadgeArgs = {
    name: string
    placeholder?: string
    value?: string
    required?: boolean
    id?: string
    textPattern?: string
    textMaxLength?: number
}

const meta: Meta<BadgeArgs> = {
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
type Story = StoryObj<BadgeArgs>

function rzInputColorRenderer(args: BadgeArgs): HTMLElement {
    const node = document.createElement('rz-input-color')
    node.classList.add('rz-input')

    const color = document.createElement('input')
    color.type = 'color'
    color.value = args.value || ''
    node.appendChild(color)

    const text = document.createElement('input')
    text.type = 'text'
    text.value = args.value || ''
    text.id = args.id || 'color-input-id'
    if (args.textPattern) text.pattern = args.textPattern
    if (args.textMaxLength) text.maxLength = args.textMaxLength
    if (args.placeholder) text.placeholder = args.placeholder
    node.appendChild(text)

    return node
}

export const Default: Story = {
    render: (args) => {
        return rzInputColorRenderer(args)
    },
    args: {
        id: 'color-input-1',
        placeholder: 'Select a color',
    },
}

export const WithDefaultValue: Story = {
    render: (args) => {
        return rzInputColorRenderer(args)
    },
    args: {
        id: 'color-input-2',
        value: '#00ff00',
    },
}
