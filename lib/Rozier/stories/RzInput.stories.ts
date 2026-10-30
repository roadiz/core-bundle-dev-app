import type { Meta, StoryObj } from '@storybook/html-vite'
import { INPUT_TYPES } from '~/custom-elements/RzInput'
import {
    type RzInputOptions,
    rzInputRenderer,
} from '~/utils/storybook/renderer/rzInput'

type Args = RzInputOptions

const meta: Meta<Args> = {
    title: 'Components/Form/Input',
    tags: ['autodocs'],
    render: (args) => rzInputRenderer(args),
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

export const Default: Story = {}

export const Text: Story = {
    args: {
        type: 'text',
        placeholder: 'placeholder text',
        value: '',
    },
}

export const TextWithDefault: Story = {
    args: {
        type: 'text',
        placeholder: 'placeholder text',
        value: 'A default value',
    },
}

export const Number: Story = {
    args: {
        type: 'number',
        placeholder: '42',
        attributes: {
            min: '1',
            max: '990000', // Initial input width depends on attribute character length
        },
    },
}

export const Radio: Story = {
    args: {
        type: 'radio',
    },
}

export const Checkbox: Story = {
    args: {
        type: 'checkbox',
        value: 'true',
        attributes: { checked: 'checked' },
    },
}

/**
 * Use [RzColorInput custom element](?path=/docs/components-form-colorinput--docs) for better color input support.
 */
export const Color: Story = {
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
    args: {
        type: 'email',
        placeholder: 'john-doe@gmail.com',
    },
}

export const Tel: Story = {
    args: {
        type: 'tel',
    },
}

export const Range: Story = {
    args: {
        type: 'range',
    },
}

export const File: Story = {
    args: {
        type: 'file',
    },
}

export const Hidden: Story = {
    args: {
        type: 'hidden',
    },
}

export const Image: Story = {
    args: {
        type: 'image',
    },
}

export const Password: Story = {
    args: {
        type: 'password',
    },
}

export const Search: Story = {
    args: {
        type: 'search',
        placeholder: 'Search...',
    },
}

export const Url: Story = {
    args: {
        type: 'url',
    },
}

export const Reset: Story = {
    args: {
        type: 'reset',
    },
}

export const Submit: Story = {
    args: {
        type: 'submit',
    },
}

export const Time: Story = {
    args: {
        type: 'time',
    },
}

export const Date: Story = {
    args: {
        type: 'date',
    },
}

export const DatetimeLocal: Story = {
    args: {
        type: 'datetime-local',
    },
}

export const Month: Story = {
    args: {
        type: 'month',
    },
}

export const Week: Story = {
    args: {
        type: 'week',
    },
}
