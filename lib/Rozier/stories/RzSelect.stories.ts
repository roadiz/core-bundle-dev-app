import type { Meta, StoryObj } from '@storybook/html-vite'
import {
    type RzSelectOption,
    type RzSelectOptions,
    rzSelectRenderer,
} from '~/utils/storybook/renderer/rzSelect'

type Option = RzSelectOption
type Args = RzSelectOptions

const meta: Meta<Args> = {
    title: 'Components/Form/Select',
    tags: ['autodocs'],
    render: (args) => rzSelectRenderer(args),
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

export const Default: Story = {}

function getNumberedOptions(length: number): Option[] {
    return Array.from({ length }, (_, i) => {
        const hour = i.toString().padStart(2, '0')
        return { value: hour, label: hour }
    })
}

export const Times: Story = {
    render: (args) => {
        const wrapper = document.createElement('div')
        wrapper.style.display = 'flex'
        wrapper.style.gap = '8px'

        const hourOptions = [
            { value: '', label: 'Hour' },
            ...getNumberedOptions(24),
        ]
        const hourSelect = rzSelectRenderer({ ...args, options: hourOptions })
        wrapper.appendChild(hourSelect)

        const minuteOptions = [
            { value: '', label: 'Minute' },
            ...getNumberedOptions(60),
        ]
        const minuteSelect = rzSelectRenderer({
            ...args,
            options: minuteOptions,
        })
        wrapper.appendChild(minuteSelect)

        return wrapper
    },
    parameters: {
        controls: { exclude: ['options'] },
    },
}
