import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzMessageRenderer } from '../app/utils/storybook/renderer/rzMessage'
import { rzInputRenderer } from '../app/utils/storybook/renderer/rzInput'

const INPUT_TYPES = [
    'text',
    'email',
    'password',
    'number',
    'date',
    'file',
] as const

const COMPONENT_CLASS_NAME = 'rz-form-field'

const getId = () => 'input-' + Math.random().toString(36).slice(2, 11)

type fieldArgs = {
    label: string
    name: string
    required: boolean
    description: string // under label
    supportingText: string // under input
    error: string
    type: (typeof INPUT_TYPES)[number]
}

const meta: Meta<fieldArgs> = {
    title: 'Components/Form/Field',
    args: {
        label: 'Input Field Label',
        name: getId(),
        required: false,
        description: 'This is a description for the input field.',
        type: 'text',
        error: '',
        supportingText: '',
    },
    argTypes: {
        type: {
            options: INPUT_TYPES,
            control: { type: 'select' },
        },
    },
}

export default meta
type Story = StoryObj<fieldArgs>

function itemRenderer(args: fieldArgs) {
    const wrapper = document.createElement('div')
    const wrapperClasses = [
        COMPONENT_CLASS_NAME,
        `${COMPONENT_CLASS_NAME}--type-${args.type}`,
        args.required && `${COMPONENT_CLASS_NAME}--required`,
    ].filter((c) => c) as string[]
    wrapper.classList.add(...wrapperClasses)

    const label = document.createElement('label')
    label.classList.add(`${COMPONENT_CLASS_NAME}__label`)
    label.textContent = args.label
    label.setAttribute('for', args.name)
    wrapper.appendChild(label)

    if (args.description) {
        const description = document.createElement('label')
        description.classList.add(`${COMPONENT_CLASS_NAME}__description`)
        description.setAttribute('for', args.name)
        description.textContent = args.description
        wrapper.appendChild(description)
    }

    const input = rzInputRenderer({
        ...args,
        id: args.name,
        placeholder: 'Placeholder',
    })
    input.classList.add(`${COMPONENT_CLASS_NAME}__input`, 'rz-form-input')
    if (args.error) input.classList.add('rz-input--error')
    if (args.required) input.setAttribute('required', 'true')
    wrapper.appendChild(input)

    if (args.supportingText) {
        const node = rzMessageRenderer({ text: args.supportingText, type: '' })
        node.setAttribute('for', args.name)
        wrapper.appendChild(node)
    }

    if (args.error) {
        const node = rzMessageRenderer({ text: args.error, type: 'error' })
        node.setAttribute('for', args.name)
        node.classList.add('rz-form-message--type-error')
        wrapper.appendChild(node)
    }

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
}

export const WithSupportingText: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        supportingText: 'This is a supporting text for the input field.',
    },
}

export const WithError: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        error: 'This is an error message for the input field.',
    },
}
