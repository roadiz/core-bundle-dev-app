import type { Meta, StoryObj } from '@storybook/html-vite'
import type { Args as RzFormFieldArgs } from './RzFormField.stories'
import { rzFieldsetRenderer } from '~/utils/storybook/renderer/rzFieldset'

export type Args = {
    legend: string
    formFieldsData?: RzFormFieldArgs[]
}

const meta: Meta<Args> = {
    title: 'Components/Form/Fieldset',
    tags: ['autodocs'],
    args: {
        legend: 'Fieldset Legend',
    },
}

export default meta
type Story = StoryObj<Args>

export const Default: Story = {
    render: (args) => {
        return rzFieldsetRenderer(args)
    },
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

export const InlineCheckboxGroup: Story = {
    render: (args) => {
        return rzFieldsetRenderer(args)
    },
    args: {
        legend: 'Checkbox Group Legend',
        formFieldsData: Array.from({ length: 10 }, (_, i) => ({
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

export const SwitchGroup: Story = {
    render: (args) => {
        return rzFieldsetRenderer(args)
    },
    args: {
        legend: 'Switch Group Legend',
        formFieldsData: Array.from({ length: 10 }, (_, i) => ({
            label: `Option ${i + 1}`,
            description: 'This is option description',
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
    render: (args) => {
        return rzFieldsetRenderer(args)
    },
    args: {
        legend: 'Switch Group Legend',
        formFieldsData: [
            ...Array.from({ length: 3 }, (_, i) => ({
                label: `Option ${i + 1}`,
                horizontal: true,
                input: {
                    className: 'rz-switch',
                    type: 'checkbox' as const,
                    name: `Mixed-option-${i + 1}`,
                    id: `Mixed-option-${i + 1}`,
                },
            })),
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
                input: {
                    type: 'checkbox',
                    name: `Mixed-option-solo`,
                    className: 'rz-switch',
                    id: `Mixed-option-solo`,
                },
            },
            {
                label: 'Simple text 2',
                input: {
                    type: 'color',
                    name: 'simple-text-2-SwitchList',
                    id: 'simple-text-2-SwitchList',
                },
            },
        ],
    },
}
