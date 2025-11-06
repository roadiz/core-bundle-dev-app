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
                type: 'text',
                name: 'text-input',
                label: 'Text Input',
                description: 'A simple text input',
            },
            {
                type: 'email',
                name: 'email-input',
                label: 'Email Input',
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
            type: 'checkbox',
            name: `InlineCheckboxGroup-option-${i + 1}`,
            description: 'This is option description',
            label: `Option ${i + 1}`,
            inline: true,
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
            type: 'checkbox',
            name: `SwitchGroup-option-${i + 1}`,
            description: 'This is option description',
            label: `Option ${i + 1}`,
            inputClassName: 'rz-switch',
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
                type: 'checkbox',
                name: `Mixed-option-${i + 1}`,
                description: 'This is option description',
                label: `Option ${i + 1}`,
                inputClassName: 'rz-switch',
            })),
            {
                label: 'Simple text 2',
                type: 'text',
                name: 'simple-text-2-SwitchList',
            },
            {
                type: 'checkbox',
                name: `Mixed-option-solo`,
                description: 'This is option description',
                label: `Option solo`,
                inputClassName: 'rz-switch',
            },
            {
                label: 'Simple text 2',
                type: 'color',
                name: 'simple-text-2-SwitchList',
            },
        ],
    },
}
