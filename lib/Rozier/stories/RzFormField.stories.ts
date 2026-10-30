import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type RzFormFieldOptions,
    rzFormFieldRenderer,
} from '~/utils/storybook/renderer/rzFormField'
import { INPUT_TYPES } from '~/custom-elements/RzInput'

type Args = RzFormFieldOptions

const meta: Meta<Args> = {
    title: 'Components/Form/Field',
    tags: ['autodocs'],
    render: (args) => rzFormFieldRenderer(args),
    args: {
        label: 'Input Field Label',
        required: false,
        description: 'This is a description for the input field.',
        type: undefined,
        error: '',
        help: '',
        horizontal: false,
        alignStart: false,
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
    args: {
        label: 'Simple checkbox',
        input: {
            type: 'checkbox',
            id: 'Checkbox-input-id',
            name: 'Checkbox-input-name',
        },
    },
}

export const SwitchCheckbox: Story = {
    args: {
        iconClass: 'rz-icon-ri--image-line',
        description: undefined,
        badge: {
            label: 'Switch Badge',
            color: 'information',
            size: 'xs',
        },
        input: {
            type: 'checkbox',
            className: 'rz-switch',
            name: 'Switch-name',
            id: 'Switch-id',
        },
    },
}

export const CheckboxInline: Story = {
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
    args: {
        iconClass: 'rz-icon-ri--image-line',
        input: undefined,
        badge: {
            label: '0/255',
            color: 'danger',
            size: 'xs',
        },
        buttonGroup: {
            size: 'md',
            gap: 'md',
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

export const MaxLength: Story = {
    args: {
        maxLength: 20,
        input: {
            type: 'text',
            name: 'maxlength-name',
            id: 'maxlength-id',
        },
    },
}

export const MinLength: Story = {
    args: {
        minLength: 5,
        input: {
            type: 'text',
            name: 'minlength-name',
            id: 'minlength-id',
        },
    },
}
