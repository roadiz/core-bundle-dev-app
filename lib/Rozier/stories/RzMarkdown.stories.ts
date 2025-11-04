import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonRenderer } from '../app/utils/storybook/renderer/rzButton'

const ALL_CONTROLS_BUTTON_GROUPS = [
    [
        'rz-icon-ri--h-1',
        'rz-icon-ri--h-2',
        'rz-icon-ri--h-3',
        'rz-icon-ri--h-4',
        'rz-icon-ri--h-5',
        'rz-icon-ri--h-6',
    ],
    ['rz-icon-ri--bold', 'rz-icon-ri--italic'],
    [
        'rz-icon-ri--single-quotes-l',
        'rz-icon-ri--link',
        'rz-icon-ri--list-unordered',
    ],
    [
        'rz-icon-ri--corner-down-left-line',
        'rz-icon-ri--separator',
        'rz-icon-ri--space',
        'rz-icon-ri--subtract-line',
    ],
    ['rz-icon-ri--eye-line'],
    ['rz-icon-ri--translate', 'rz-icon-ri--translate-ai'],
]

const TEXTAREA_ATTRIBUTES = [
    'name',
    'value',
    'cols',
    'rows',
    'maxlength',
    'minlength',
    'required',
    'placeholder',
]

export type Args = {
    controlsButtonGroups?: string[][]
    name: string
    value?: string
    cols?: number
    rows?: number
    maxlength?: number
    minlength?: number
    required?: boolean
    placeholder?: string
}

const meta: Meta<Args> = {
    title: 'Components/Form/Markdown',
    tags: ['autodocs'],
    args: {
        placeholder: 'Enter your markdown here...',
        controlsButtonGroups: ALL_CONTROLS_BUTTON_GROUPS,
        minlength: 0,
        maxlength: 255,
    },
    argTypes: {},
}

export default meta
type Story = StoryObj<Args>

function buttonGroupRenderer(iconNames: string[]) {
    const group = document.createElement('div')
    group.classList.add('rz-button-group')

    iconNames.forEach((iconClass) => {
        const button = rzButtonRenderer({ iconClass, size: 'md' })
        group.appendChild(button)
    })

    return group
}

function controlsRenderer(iconNames: string[][]) {
    const controls = document.createElement('div')
    controls.classList.add('rz-markdown__controls')

    iconNames.forEach((iconList) => {
        const group = buttonGroupRenderer(iconList)
        controls.appendChild(group)
    })

    return controls
}

function itemRenderer(args: Args) {
    const wrapper = document.createElement('div')
    wrapper.classList.add('rz-markdown')

    const head = controlsRenderer(args.controlsButtonGroups || [[]])
    wrapper.appendChild(head)

    const textarea = document.createElement('textarea')
    textarea.classList.add('rz-markdown__textarea')

    for (const key in args) {
        if (TEXTAREA_ATTRIBUTES.includes(key) && args[key as keyof Args]) {
            textarea.setAttribute(key, String(args[key as keyof Args]))
            textarea[key] = args[key as keyof Args]
        }
    }
    textarea.name = args.name || 'fallback-name'
    wrapper.appendChild(textarea)

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        name: 'text-area-markdown-input',
    },
}

/**
 * `:invalid` pseudo-class is only apply when user interact with the form control.
 */
export const Error: Story = {
    render: (args) => {
        return itemRenderer(args)
    },
    args: {
        name: 'text-area-markdown-input',
        maxlength: 50,
        minlength: 40,
        required: true,
        value: 'Value need more than 40 characters...',
    },
}
