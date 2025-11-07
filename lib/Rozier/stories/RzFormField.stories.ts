import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzFormFieldRenderer } from '~/utils/storybook/renderer/rzFormField'
import { INPUT_TYPES } from '~/custom-elements/RzInput'
import type { BadgeArgs } from './RzBadge.stories'
import type { Args as InputArgs } from './RzInput.stories'
import type { Args as ButonnGroupArg } from './rzButtonGroup.stories'

export type Args = {
    label: string
    required?: boolean
    description?: string // under label
    help?: string // under input
    error?: string
    type: (typeof INPUT_TYPES)[number]
    horizontal?: boolean
    // Elements
    badge: BadgeArgs
    iconClass?: string
    input?: InputArgs
    buttonGroup?: ButonnGroupArg
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
        horizontal: false,
        input: {
            type: 'text',
            placeholder: 'Enter text here',
            name: 'input-name',
            id: 'input-id',
        },
    },
    argTypes: {
        type: {
            options: INPUT_TYPES,
            control: { type: 'select' },
        },
        horizontal: {
            description:
                'If true, displays the field horizontally, input and label side by side. Message and help text remain below.',
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
        badge: {
            iconClass: 'rz-icon-ri--earth-line',
            title: 'Badge title',
        },
        input: {
            type: 'text',
            id: 'default-input-id',
            name: 'default-input-name',
        },
    },
}

export const WithoutBadge: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
    args: {
        badge: undefined,
        input: {
            type: 'text',
            id: 'WithoutBadge-input-id',
            name: 'WithoutBadge-input-name',
        },
    },
}

export const WithIcon: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
    args: {
        iconClass: 'rz-icon-ri--markdown-line',
        badge: undefined,
        input: {
            type: 'text',
            id: 'WithIcon-input-id',
            name: 'WithIcon-input-name',
        },
    },
}

export const Checkbox: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
    args: {
        label: 'Inline Checkbox',
        input: {
            type: 'checkbox',
            id: 'Checkbox-input-id',
            name: 'Checkbox-input-name',
        },
    },
}

export const CheckboxInline: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
    args: {
        label: 'Inline Checkbox',
        horizontal: true,
        input: {
            type: 'checkbox',
            name: 'CheckboxInline-name',
            id: 'CheckboxInline-id',
        },
    },
}

export const WithSupportingText: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
    args: {
        help: 'This is a supporting text for the input field.',
        input: {
            type: 'text',
            name: 'WithSupportingText-name',
            id: 'WithSupportingText-id',
        },
    },
}

export const WithError: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
    args: {
        error: 'This is an error message for the input field.',
        input: {
            type: 'text',
            name: 'WithError-name',
            id: 'WithError-id',
        },
    },
}

export const WithButtons: Story = {
    render: (args) => {
        return rzFormFieldRenderer(args)
    },
    args: {
        iconClass: 'rz-icon-ri--image-line',
        input: undefined,
        badge: {
            label: '0/255',
            color: 'error',
            size: 'xs',
        },
        buttonGroup: {
            spacing: 'md',
            buttons: [
                {
                    label: 'Upload',
                    iconClass: 'rz-icon-ri--upload-line',
                    size: 'sm',
                },
                {
                    label: 'Explore',
                    iconClass: 'rz-icon-ri--add-line',
                    size: 'sm',
                },
            ],
        },
    },
}
