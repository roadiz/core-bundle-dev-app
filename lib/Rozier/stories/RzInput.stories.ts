import type { Meta, StoryObj } from '@storybook/html-vite'
import { INPUT_TYPES } from '../app/custom-elements/RzInput'
import { rzInputRenderer } from '../app/utils/storybook/renderer/rzInput'

export type Args = {
    type: (typeof INPUT_TYPES)[number]
    name: string
    id: string
    placeholder?: string
    value?: string
    required?: boolean
    className?: string
}

const meta: Meta<Args> = {
    title: 'Components/Form/Input',
    tags: ['autodocs'],
    args: {
        name: 'input-name',
        placeholder: 'A default placeholder',
        value: '',
        type: 'text',
    },
    argTypes: {
        type: {
            control: { type: 'select' },
            options: INPUT_TYPES,
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
        return rzInputRenderer(args)
    },
}

export const Text: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'text',
        placeholder: 'placeholder text',
        value: '',
    },
}

export const TextWithDefault: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'text',
        placeholder: 'placeholder text',
        value: 'A default value',
    },
}

export const Number: Story = {
    render: (args) => {
        return rzInputRenderer(args, {
            min: '1',
            max: '990000', // Initial input width depends on attribute character length
        })
    },
    args: {
        type: 'number',
        placeholder: '42',
    },
}

export const Radio: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'radio',
    },
}

export const Checkbox: Story = {
    render: (args) => {
        return rzInputRenderer(args, { checked: args.value })
    },
    args: {
        type: 'checkbox',
        value: 'true',
    },
}

/**
 * Use [RzColorInput custom element](http://localhost:6006/?path=/docs/components-form-colorinput--docs) for better color input support.
 */
export const Color: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    argTypes: {
        value: {
            control: { type: 'color' },
        },
    },
    args: {
        type: 'color',
        placeholder: '#000000',
        value: '',
    },
}

export const ColorWithDefault: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    argTypes: {
        value: {
            control: { type: 'color' },
        },
    },
    args: {
        type: 'color',
        placeholder: 'Select a color',
        value: '#ff0000',
    },
}

export const Email: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'email',
        placeholder: 'john-doe@gmail.com',
    },
}

export const Tel: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'tel',
    },
}

export const Range: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'range',
    },
}

export const File: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'file',
    },
}

export const Hidden: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'hidden',
    },
}

export const Image: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'image',
    },
}

export const Password: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'password',
    },
}

export const Search: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'search',
        placeholder: 'Search...',
    },
}

export const Url: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'url',
    },
}

export const Reset: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'reset',
    },
}

export const Submit: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'submit',
    },
}

export const Time: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'time',
    },
}

export const Date: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'date',
    },
}

export const DatetimeLocal: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'datetime-local',
    },
}

export const Month: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'month',
    },
}

export const Week: Story = {
    render: (args) => {
        return rzInputRenderer(args)
    },
    args: {
        type: 'week',
    },
}
