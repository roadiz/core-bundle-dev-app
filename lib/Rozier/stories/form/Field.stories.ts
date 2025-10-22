import type { Meta, StoryObj } from '@storybook/html-vite'
import { INPUT_TYPES } from '../../app/custom-elements/RzFormInput'

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
    globals: {
        backgrounds: { value: 'light' },
    },
    args: {
        label: 'Input Field Label',
        name: getId(),
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

    const description = document.createElement('p')
    description.classList.add(`${COMPONENT_CLASS_NAME}__description`)
    description.textContent = args.description
    wrapper.appendChild(description)

    const input = document.createElement('input')
    input.classList.add(`${COMPONENT_CLASS_NAME}__input`, 'rz-form-input')
    input.setAttribute('type', args.type)
    input.setAttribute('id', args.name)
    if (args.required) input.setAttribute('required', 'true')
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
