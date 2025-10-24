import type { Meta, StoryObj } from '@storybook/html-vite'

const INPUT_TYPES = [
    'text',
    'email',
    'password',
    'number',
    'date',
    'file',
] as const

const COMPONENT_CLASS_NAME = 'rz-form-field'

const getId = () => 'input-' + Math.random().toString(36).substr(2, 9)

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
    title: 'Components/RzForm/Field',
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

function messageRenderer(content: string) {
    const supportingText = document.createElement('label')
    const supportingTextClasses = [
        `${COMPONENT_CLASS_NAME}__supporting-text`,
        'rz-form-message',
        'text-form-supporting-text',
    ].filter((c) => c)

    supportingText.classList.add(...supportingTextClasses)
    supportingText.textContent = content

    return supportingText
}

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

    const input = document.createElement('input')
    input.classList.add(`${COMPONENT_CLASS_NAME}__input`, 'rz-form-input')
    input.setAttribute('type', args.type)
    input.setAttribute('id', args.name)
    input.setAttribute('placeholder', 'Placeholder')
    if (args.required) input.setAttribute('required', 'true')
    wrapper.appendChild(input)

    if (args.supportingText) {
        const node = messageRenderer(args.supportingText)
        node.setAttribute('for', args.name)
        wrapper.appendChild(node)
    }

    if (args.error) {
        const node = messageRenderer(args.error)
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
