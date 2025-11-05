import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzFormFieldRenderer } from '~/utils/storybook/renderer/rzFormField'
import { INPUT_TYPES } from '~/custom-elements/RzInput'

export type Args = {
    label: string
    badgeIconClass?: string
    badgeTitle?: string
    name: string
    required?: boolean
    description?: string // under label
    help?: string // under input
    error?: string
    type: (typeof INPUT_TYPES)[number]
    inline?: boolean
    inputClassName?: string
}

const meta: Meta<Args> = {
    title: 'Components/Form/Field',
    tags: ['autodocs'],
    args: {
        label: 'Input Field Label',
        required: false,
        description: 'This is a description for the input field.',
        type: 'text',
        error: '',
        help: '',
        inline: false,
        badgeIconClass: '',
        badgeTitle: '',
    },
    argTypes: {
        type: {
            options: INPUT_TYPES,
            control: { type: 'select' },
        },
        name: {
            description: 'Required. The name and id of the input field.',
        },
        inline: {
            description:
                'If true, displays the field inline, input and label side by side. Message and help text remain below.',
        },
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
    args: {
        name: 'default-name',
    },
}

export const WithIcon: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
    args: {
        name: 'default-with-icon-name',
        badgeIconClass: 'rz-icon-ri--earth-line',
    },
}

export const Checkbox: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
    args: {
        name: 'Checkbox-name',
        type: 'checkbox',
        label: 'Inline Checkbox',
    },
}

export const CheckboxInline: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
    args: {
        name: 'CheckboxInline-name',
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
        name: 'with-supporting-text-name',
        help: 'This is a supporting text for the input field.',
    },
}

export const WithError: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
    args: {
        name: 'with-error-name',
        error: 'This is an error message for the input field.',
    },
}
