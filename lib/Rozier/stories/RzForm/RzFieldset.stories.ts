import type { Meta, StoryObj } from '@storybook/html-vite'

const COMPONENT_CLASS_NAME = 'rz-fieldset'

type Args = {
    legend: string
    name: string
    required: boolean
    description: string // under label
    supportingText: string // under input
    error: string
}

const meta: Meta<Args> = {
    title: 'Components/RzForm/Fieldset',
    args: {
        legend: 'Fieldset Legend',
        name: 'fieldset-1',
        required: false,
        description: 'This is a description for the input field.',
        error: '',
        supportingText: '',
    },
}

export default meta
type Story = StoryObj<Args>

// Use imported inputRenderer from RzFormField stories
function inputRenderer(type: string, name: string) {
    const input = document.createElement('input')
    input.type = type
    input.name = name
    input.id = name
    input.classList.add('rz-form-input', `rz-form-input--type-${type}`)

    return input
}

function legendRenderer(legendText: string) {
    const legend = document.createElement('legend')
    legend.classList.add(`${COMPONENT_CLASS_NAME}__legend`)
    legend.textContent = legendText

    return legend
}

function fieldsetRenderer(args: Args) {
    const fieldset = document.createElement('fieldset')
    const fieldsetClasses = [
        COMPONENT_CLASS_NAME,
        args.required && `${COMPONENT_CLASS_NAME}--required`,
    ].filter((c) => c) as string[]
    fieldset.classList.add(...fieldsetClasses)

    return fieldset
}

const commonsInputList = [
    { type: 'text', label: 'Text Input', placeholder: 'Enter text' },
    { type: 'email', label: 'Email Input', placeholder: 'Enter email' },
]

export const Default: Story = {
    render: (args) => {
        const fieldset = fieldsetRenderer(args)
        const legend = legendRenderer(args.legend)
        fieldset.appendChild(legend)

        commonsInputList.forEach((inputDef) => {
            const input = inputRenderer(
                inputDef.type,
                `${args.name}-${inputDef.type}`,
            )
            fieldset.appendChild(input)
        })

        return fieldset
    },
}

export const WithSupportingText: Story = {
    render: (args) => {
        return fieldsetRenderer(args)
    },
    args: {
        supportingText: 'This is a supporting text for the input field.',
    },
}

export const WithError: Story = {
    render: (args) => {
        return fieldsetRenderer(args)
    },
    args: {
        error: 'This is an error message for the input field.',
    },
}
