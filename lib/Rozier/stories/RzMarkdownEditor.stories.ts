import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonGroupRenderer } from '~/utils/storybook/renderer/rzButtonGroup'

const COMPONENT_CLASS_NAME = 'rz-markdown-editor'
const BUTTON_GROUP_ICONS = [
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
    title: 'Components/Form/MarkdownEditor',
    tags: ['autodocs'],
    args: {
        placeholder: 'Enter your markdown here...',
        controlsButtonGroups: BUTTON_GROUP_ICONS,
        minlength: 0,
        maxlength: 255,
    },
    argTypes: {},
}

export default meta
type Story = StoryObj<Args>

function controlsRenderer(iconNames: string[][]) {
    const controls = document.createElement('div')
    controls.classList.add(
        `${COMPONENT_CLASS_NAME}__controls`,
        'rz-button-group--collapsed',
        'rz-button--md',
    )

    iconNames.forEach((iconList) => {
        const buttons = iconList.map((iconClass) => ({ iconClass }))
        const group = rzButtonGroupRenderer({ buttons, collapsed: true })
        controls.appendChild(group)
    })

    return controls
}

function rzMarkdownEditorRenderer(args: Args) {
    // Use `rz-markdown-editor` custom element to instantiate the component
    // Or `rz-markdown-editor` class to only apply the styles
    const wrapper = document.createElement('div')
    wrapper.classList.add(COMPONENT_CLASS_NAME)

    const head = controlsRenderer(args.controlsButtonGroups || [[]])
    wrapper.appendChild(head)

    const textarea = document.createElement('textarea')
    textarea.classList.add(`${COMPONENT_CLASS_NAME}__textarea`)

    for (const key in args) {
        if (TEXTAREA_ATTRIBUTES.includes(key) && args[key as keyof Args]) {
            textarea.setAttribute(key, String(args[key as keyof Args]))
            Object.assign(textarea, { [key]: args[key as keyof Args] })
        }
    }
    textarea.name = args.name || 'fallback-name'
    wrapper.appendChild(textarea)

    return wrapper
}

export const Default: Story = {
    render: (args) => {
        return rzMarkdownEditorRenderer(args)
    },
    args: {
        name: 'text-area-markdown-input',
    },
}

/**
 * `:invalid` pseudo-class is only apply when user interact with the form control.
 */
export const UserError: Story = {
    render: (args) => {
        return rzMarkdownEditorRenderer(args)
    },
    args: {
        name: 'text-area-markdown-input',
        maxlength: 50,
        minlength: 40,
        required: true,
        value: 'Value need more than 40 characters...',
    },
}

export const ErrorClass: Story = {
    render: (args) => {
        const wrapper = document.createElement('div')
        wrapper.classList.add('rz-form-field', 'rz-form-field--error')
        const markdown = rzMarkdownEditorRenderer(args)
        wrapper.appendChild(markdown)

        return wrapper
    },
    args: {
        name: 'text-area-markdown-input',
        maxlength: 50,
        minlength: 40,
        required: true,
        value: 'Value need more than 40 characters...',
    },
}
