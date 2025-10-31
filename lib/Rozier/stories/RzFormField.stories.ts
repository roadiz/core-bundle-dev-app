import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzFormFieldRenderer } from '~/utils/storybook/renderer/rzFormField'
import { INPUT_TYPES } from '~/custom-elements/RzInput'

const getId = () => 'input-' + Math.random().toString(36).slice(2, 11)

export type Args = {
    label: string
    name: string
    required?: boolean
    description?: string // under label
    supportingText?: string // under input
    error?: string
    type: (typeof INPUT_TYPES)[number]
    inline?: boolean
}

const meta: Meta<Args> = {
    title: 'Components/Form/Field',
    args: {
        label: 'Input Field Label',
        name: getId(),
        required: false,
        description: 'This is a description for the input field.',
        type: 'text',
        error: '',
        supportingText: '',
    },
    argTypes: {
        type: {
            options: INPUT_TYPES,
            control: { type: 'select' },
        },
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
}

export const Inline: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
    args: {
        type: 'checkbox',
        label: 'Inline Checkbox',
        inline: true,
    },
}

export const WithSupportingText: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
    args: {
        supportingText: 'This is a supporting text for the input field.',
    },
}

export const WithError: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
    args: {
        error: 'This is an error message for the input field.',
    },
}
