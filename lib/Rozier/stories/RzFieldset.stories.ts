import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzFormFieldRenderer } from '~/utils/storybook/renderer/rzFormField'
import { rzMessageRenderer } from '~/utils/storybook/renderer/rzMessage'

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
    title: 'Components/Form/Fieldset',
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

const commonsInputList = [
    {
        type: 'text',
        name: 'text-input',
        label: 'Text Input',
        placeholder: 'Enter text',
        description: 'A simple text input',
    },
    {
        type: 'email',
        name: 'email-input',
        label: 'Email Input',
        placeholder: 'Enter email',
    },
]

function fieldsetRenderer(args: Args) {
    const fieldset = document.createElement('fieldset')
    const fieldsetClasses = [
        COMPONENT_CLASS_NAME,
        args.required && `${COMPONENT_CLASS_NAME}--required`,
    ].filter((c) => c) as string[]
    fieldset.classList.add(...fieldsetClasses)

    const legend = document.createElement('legend')
    legend.classList.add(`${COMPONENT_CLASS_NAME}__legend`)
    legend.textContent = args.legend
    fieldset.appendChild(legend)

    if (args.description) {
        const description = document.createElement('p')
        description.classList.add(`${COMPONENT_CLASS_NAME}__description`)
        description.setAttribute('for', args.name)
        description.textContent = args.description
        fieldset.appendChild(description)
    }

    // Inputs
    const fieldsetBody = document.createElement('div')
    fieldsetBody.classList.add(`${COMPONENT_CLASS_NAME}__body`)
    fieldset.appendChild(fieldsetBody)
    commonsInputList.forEach((inputDef) => {
        const input = rzFormFieldRenderer(inputDef)
        fieldsetBody.appendChild(input)
    })

    if (args.supportingText) {
        const node = rzMessageRenderer({ text: args.supportingText })
        node.setAttribute('for', args.name)
        fieldset.appendChild(node)
    }

    if (args.error) {
        const node = rzMessageRenderer({ text: args.error, type: 'error' })
        node.setAttribute('for', args.name)
        node.classList.add('rz-form-message--type-error')
        fieldset.appendChild(node)
    }

    return fieldset
}

export const Default: Story = {
    render: (args) => {
        return fieldsetRenderer(args)
    },
}

export const WithSupportingText: Story = {
    render: (args) => {
        const fieldset = fieldsetRenderer(args)

        return fieldset
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
