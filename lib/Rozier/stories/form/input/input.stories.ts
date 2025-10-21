import type { Meta, StoryObj } from '@storybook/html-vite'

const COMPONENT_CLASS = 'rz-form-input'
const TYPES = [
    'text',
    'number',
    'checkbox',
    'radio',
    'color',
    'email',
    'tel',
    'range',

    'url',
    'file',
    'password',
    'image',
    'hidden',

    'time',
    'date',
    'datetime-local',
    'month',
    'week',

    'button',
    'reset',
    'search',
    'submit',
] as const

type fieldArgs = {
    placeholder: string
    type: (typeof TYPES)[number]
    value: string | boolean | object | number // depending on input type (e.g., checkbox might be boolean)
    name: string
    required: boolean
}

const meta: Meta<fieldArgs> = {
    title: 'Components/RzForm/Input',
    args: {
        name: 'input-name',
    },
    argTypes: {
        type: {
            control: { type: 'select' },
            options: TYPES,
        },
    },
    globals: {
        backgrounds: { value: 'light' },
    },
}

export default meta
type Story = StoryObj<fieldArgs>

function itemRenderer(args: fieldArgs, attrs: Record<string, unknown> = {}) {
    const customComponentName = 'rz-form-input'

    const input = document.createElement('input', { is: customComponentName })
    input.setAttribute('is', customComponentName)

    input.classList.add(COMPONENT_CLASS)

    Object.entries(attrs).forEach(([key, value]) => {
        if (value) input.setAttribute(key, String(value))
    })
    if (args.name) input.id = args.name
    if (args.name) input.name = args.name
    if (args.type) input.type = args.type
    if (args.placeholder) input.placeholder = args.placeholder
    if (args.required) input.required = args.required

    if (args.value) input.setAttribute('value', String(args.value))

    return input
}

export const Default: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
}

export const Text: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'text',
        placeholder: 'placeholder text',
        value: '',
    },
}

export const Number: Story = {
    render: (args) => {
        return itemRenderer(args, {
            min: '1',
            max: '990000',
        })
    },
    args: {
        type: 'number',
        placeholder: '42',
    },
}

export const Checkbox: Story = {
    render: (args) => {
        return itemRenderer(args, { checked: args.value })
    },
    args: {
        type: 'checkbox',
        value: true,
    },
}

export const CheckboxToggle: Story = {
    render: (args) => {
        const input = itemRenderer(args, { checked: args.value })
        input.classList.add('rz-form-input--toggle')

        return input
    },
    args: {
        type: 'checkbox',
        value: true,
    },
}

export const Radio: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'radio',
    },
}

export const Color: Story = {
    render: (args) => {
        return itemRenderer(args, {
            'data-value': args.value, // to display the initial color label
        })
    },
    argTypes: {
        value: {
            control: { type: 'color' },
        },
    },
    args: {
        type: 'color',
        placeholder: 'Select a color',
        value: '',
    },
}

export const Email: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'email',
    },
}

export const Tel: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'tel',
    },
}

export const Range: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'range',
    },
}

export const File: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'file',
    },
}

export const Hidden: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'hidden',
    },
}

export const Image: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'image',
    },
}

export const Password: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'password',
    },
}

export const Search: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'search',
    },
}

export const Url: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'url',
    },
}

export const Reset: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'reset',
    },
}

export const Submit: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'submit',
    },
}

export const Time: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'time',
    },
}

export const Date: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'date',
    },
}

export const DatetimeLocal: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'datetime-local',
    },
}

export const Month: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'month',
    },
}

export const Week: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        type: 'week',
    },
}
