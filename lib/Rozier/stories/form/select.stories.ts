import type { Meta, StoryObj } from '@storybook/html-vite'

const COMPONENT_CLASS_NAME = 'rz-form-select'
type Option = { value: string; label: string }

type Args = {
    options: Option[]
    datalist: string
}

const meta: Meta<Args> = {
    title: 'Components/RzForm/Select',
    args: {
        options: [
            { value: '', label: '-- My first option --' },
            { value: 'option-1', label: 'Option 1' },
            { value: 'option-2', label: 'Option 2' },
            { value: 'option-3', label: 'Option 3' },
        ],
    },
    argTypes: {},
}

export default meta
type Story = StoryObj<Args>

function itemRenderer(args: Args) {
    const wrapper = document.createElement('select')
    const wrapperClasses = [COMPONENT_CLASS_NAME].filter((c) => c) as string[]
    wrapper.classList.add(...wrapperClasses, 'rz-form-input')

    args.options.forEach((optionData) => {
        const option = document.createElement('option')
        option.value = optionData.value
        option.textContent = optionData.label
        wrapper.appendChild(option)
    })

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
}
