import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type RzFieldsetOptions,
    rzFieldsetRenderer,
} from '~/utils/storybook/renderer/rzFieldset'

type Args = RzFieldsetOptions

const meta: Meta<Args> = {
    title: 'Components/Form/Fieldset',
    tags: ['autodocs'],
    render: (args) => rzFieldsetRenderer(args),
    args: {
        legend: 'Fieldset Legend',
        horizontal: false,
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    args: {
        formFieldsData: [
            {
                label: 'Text Input',
                description: 'A simple text input',
                input: {
                    type: 'text',
                    name: 'text-input',
                    id: 'text-input',
                    placeholder: 'Enter text here',
                },
            },
            {
                label: 'Email Input',
                input: {
                    type: 'email',
                    name: 'email-input',
                    id: 'email-input',
                    placeholder: 'john@gmail.com',
                },
            },
        ],
    },
}

export const CheckboxGroup: Story = {
    args: {
        legend: 'Checkbox Group Legend',
        formFieldsData: Array.from({ length: 4 }, (_, i) => ({
            description: 'This is option description',
            label: `Option ${i + 1}`,
            horizontal: true,
            input: {
                type: 'checkbox',
                name: `InlineCheckboxGroup-option-${i + 1}`,
                id: `InlineCheckboxGroup-option-${i + 1}`,
            },
        })),
    },
}

export const HorizontalCheckboxGroup: Story = {
    args: {
        legend: 'Checkbox Group Legend',
        horizontal: true,
        formFieldsData: Array.from({ length: 4 }, (_, i) => ({
            description: 'This is option description',
            label: `Option ${i + 1}`,
            horizontal: true,
            input: {
                type: 'checkbox',
                name: `Horizontal-checkboxGroup-option-${i + 1}`,
                id: `Horizontal-checkboxGroup-option-${i + 1}`,
            },
        })),
    },
}

export const HorizontalSwitchGroup: Story = {
    args: {
        legend: 'Switch Group Legend',
        horizontal: true,
        formFieldsData: Array.from({ length: 10 }, (_, i) => ({
            label: `Option ${i + 1}`,
            description: 'This is option description',
            horizontal: true,
            input: {
                type: 'checkbox' as const,
                id: `SwitchGroup-option-${i + 1}`,
                name: `SwitchGroup-option-${i + 1}`,
                className: 'rz-switch',
            },
        })),
    },
}

export const Mixed: Story = {
    args: {
        legend: 'Switch Group Legend',
        formFieldsData: [
            {
                legend: 'Nested fieldset',
                horizontal: true,
                formFieldsData: Array.from({ length: 3 }, (_, i) => ({
                    label: `Option ${i + 1}`,
                    input: {
                        className: 'rz-switch',
                        type: 'checkbox' as const,
                        name: `Mixed-option-${i + 1}`,
                        id: `Mixed-option-${i + 1}`,
                    },
                })),
            },
            {
                label: 'Simple text 2',
                input: {
                    type: 'text',
                    name: 'simple-text-2-SwitchList',
                    id: 'simple-text-2-SwitchList',
                },
            },
            {
                description: 'This is option description',
                label: `Option solo`,
                horizontal: true,
                input: {
                    type: 'checkbox',
                    name: `Mixed-option-solo`,
                    className: 'rz-switch',
                    id: `Mixed-option-solo`,
                },
            },
            {
                label: 'Color 2',
                input: {
                    type: 'color',
                    name: 'Color-text-2-SwitchList',
                    id: 'Color-text-2-SwitchList',
                },
            },
        ],
    },
}
