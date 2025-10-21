import type { Meta, StoryObj } from '@storybook/html-vite'

const COMPONENT_CLASS_NAME = 'rz-input-field'
const INPUT_TYPES = [
    'text',
    'password',
    'email',
    'number',
    'date',
    'textarea',
    'select',
    'checkbox',
    'radio',
] as const

type fieldArgs = {
    placeholder: string
    label: string
    required: boolean
    description: string // under label
    supportingText: string // under input
    error: string
    type: (typeof INPUT_TYPES)[number]
}

const meta: Meta<fieldArgs> = {
    title: 'Components/RzForm/inputField',
    globals: {
        backgrounds: { value: 'light' },
    },
    args: {
        placeholder: 'Enter your text here',
        label: 'Input Field Label',
        required: false,
        description: 'This is a description for the input field.',
        supportingText: 'This is a hint for the input field.',
        error: '',
        type: 'text',
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

const getId = () => 'input-' + Math.random().toString(36).substr(2, 9)

function itemRenderer(args: fieldArgs) {
    const wrapper = document.createElement('div')
    const wrapperClasses = [
        COMPONENT_CLASS_NAME,
        `${COMPONENT_CLASS_NAME}--type-${args.type}`,
        args.required && `${COMPONENT_CLASS_NAME}--required`,
    ].filter((c) => c) as string[]

    wrapper.classList.add(...wrapperClasses)

    const id = getId()

    const label = document.createElement('label')
    label.classList.add(`${COMPONENT_CLASS_NAME}__label`)
    label.textContent = args.label
    label.setAttribute('for', id)
    wrapper.appendChild(label)

    const description = document.createElement('p')
    description.classList.add(`${COMPONENT_CLASS_NAME}__description`)
    description.textContent = args.description
    wrapper.appendChild(description)

    const input = document.createElement('input')
    input.classList.add(`${COMPONENT_CLASS_NAME}__input`, 'rz-input')
    input.setAttribute('type', args.type)
    input.setAttribute('placeholder', args.placeholder)
    input.setAttribute('id', id)
    if (args.required) {
        input.setAttribute('required', 'true')
    }
    wrapper.appendChild(input)

    const supportingText = document.createElement('p')
    supportingText.classList.add(`${COMPONENT_CLASS_NAME}__supporting-text`)
    supportingText.textContent = args.supportingText
    wrapper.appendChild(supportingText)

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
}
