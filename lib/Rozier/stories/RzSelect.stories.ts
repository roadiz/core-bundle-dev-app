import type { Meta, StoryObj } from '@storybook/html-vite'

const COMPONENT_CLASS = 'rz-select'
const COMPONENT_CLASS_NAME = 'rz-select'
type Option = {
    value: string
    label: string
    disabled?: boolean
    selected?: boolean
    hidden?: boolean
}

type Args = {
    options: Option[]
    datalist?: string
    required?: boolean
}

const meta: Meta<Args> = {
    title: 'Components/Form/Select',
    args: {
        required: false,
        options: [
            {
                value: '',
                disabled: true,
                selected: true,
                hidden: false,
                label: '-- First option selected disabled --',
            },
            { value: '', label: 'Options without value' },
            { value: 'option-1', label: 'Option 1' },
            { value: 'option-2', label: 'Option 2' },
            { value: 'option-3', label: 'Option 3' },
        ],
    },
    parameters: {
        layout: 'centered',
    },
}

export default meta
type Story = StoryObj<Args>

function itemRenderer(args: Args) {
    const select = document.createElement('select', { is: COMPONENT_CLASS })
    select.setAttribute('is', COMPONENT_CLASS)

    select.required = args.required ?? false
    select.classList.add(COMPONENT_CLASS_NAME, 'rz-input')

    args.options.forEach((optionData) => {
        const option = document.createElement('option')

        const optionKeys = Object.keys(optionData) as (keyof Option)[]
        optionKeys.forEach((key) => {
            const value = optionData[key]
            if (value) option.setAttribute(key, value.toString())
        })

        select.appendChild(option)
    })

    return select
}

export const Default: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
}

function getNumberedOptions(length: number): Option[] {
    return Array.from({ length }, (_, i) => {
        const hour = i.toString().padStart(2, '0')
        return { value: hour, label: hour }
    })
}

export const Times: Story = {
    render: () => {
        const wrapper = document.createElement('div')
        wrapper.style.display = 'flex'
        wrapper.style.gap = '8px'

        const hourOptions = [
            { value: '', label: 'Hour' },
            ...getNumberedOptions(24),
        ]
        const hourSelect = itemRenderer({ options: hourOptions })
        wrapper.appendChild(hourSelect)

        const minuteOptions = [
            { value: '', label: 'Minute' },
            ...getNumberedOptions(60),
        ]
        const minuteSelect = itemRenderer({ options: minuteOptions })
        wrapper.appendChild(minuteSelect)

        return wrapper
    },
}
