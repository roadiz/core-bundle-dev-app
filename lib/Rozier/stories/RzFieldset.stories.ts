import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzFormFieldRenderer } from '~/utils/storybook/renderer/rzFormField'
import type { Args as RzFormFieldArgs } from './RzFormField.stories'

const COMPONENT_CLASS_NAME = 'rz-fieldset'
const ORIENTATION_OPTIONS = ['vertical', 'horizontal'] as const
type Args = {
    legend: string
    formFieldsData?: RzFormFieldArgs[]
    orientation?: (typeof ORIENTATION_OPTIONS)[number]
}

const meta: Meta<Args> = {
    title: 'Components/Form/Fieldset',
    tags: ['autodocs'],
    args: {
        legend: 'Fieldset Legend',
        orientation: 'vertical',
    },
    argTypes: {
        orientation: {
            options: ORIENTATION_OPTIONS,
            control: { type: 'select' },
        },
    },
}

export default meta
type Story = StoryObj<Args>

function fieldsetRenderer(args: Args) {
    const fieldset = document.createElement('fieldset')
    const orientationClass =
        args.orientation && `${COMPONENT_CLASS_NAME}--${args.orientation}`

    const fieldsetClasses = [COMPONENT_CLASS_NAME, orientationClass].filter(
        (c) => c,
    ) as string[]
    fieldset.classList.add(...fieldsetClasses)

    const legend = document.createElement('legend')
    legend.classList.add(`${COMPONENT_CLASS_NAME}__legend`)
    legend.textContent = args.legend
    fieldset.appendChild(legend)

    const fields = args.formFieldsData || []
    fields.forEach((fieldData) => {
        const input = rzFormFieldRenderer(fieldData)
        fieldset.appendChild(input)
    })

    return fieldset
}

export const Default: Story = {
    render: (args) => {
        return fieldsetRenderer(args)
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

export const CheckboxGroup: Story = {
    render: (args) => {
        return fieldsetRenderer(args)
    },
    args: {
        legend: 'Checkbox Group Legend',
        orientation: 'horizontal',
        formFieldsData: Array.from({ length: 10 }, (_, i) => ({
            type: 'checkbox',
            name: `option-${i + 1}`,
            description: 'This is option description',
            label: `Option ${i + 1}`,
            inline: true,
        })),
    },
}
