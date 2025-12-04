import type { Meta, StoryObj } from '@storybook/html-vite'
import { rzButtonGroupRenderer } from '../app/utils/storybook/renderer/rzButtonGroup'
import type { Args as ButtonGroupArgs } from './RzButtonGroup.stories'

const COMPONENT_CLASS_NAME = 'rz-markdown-editor'

const headingGroup = {
    size: 'sm',
    collapsed: true,
    buttons: [
        {
            iconClass: 'rz-icon-ri--h-1',
            additionalClasses: 'markdown-editor-button-h1',
            attributes: {
                'data-markdowneditor-button': 'h1',
            },
        },
        {
            iconClass: 'rz-icon-ri--h-2',
            additionalClasses: 'markdown-editor-button-h2',
            attributes: { 'data-markdowneditor-button': 'h2' },
        },
        {
            iconClass: 'rz-icon-ri--h-3',
            additionalClasses: 'markdown-editor-button-h3',
            attributes: { 'data-markdowneditor-button': 'h3' },
        },
        {
            iconClass: 'rz-icon-ri--h-4',
            additionalClasses: 'markdown-editor-button-h4',
            attributes: { 'data-markdowneditor-button': 'h4' },
        },
        {
            iconClass: 'rz-icon-ri--h-5',
            additionalClasses: 'markdown-editor-button-h5',
            attributes: { 'data-markdowneditor-button': 'h5' },
        },
        {
            iconClass: 'rz-icon-ri--h-6',
            additionalClasses: 'markdown-editor-button-h6',
            attributes: { 'data-markdowneditor-button': 'h6' },
        },
    ],
} as ButtonGroupArgs

const styleGroup = {
    size: 'sm',
    collapsed: true,
    buttons: [
        {
            iconClass: 'rz-icon-ri--bold',
            attributes: { 'data-markdowneditor-button': 'bold' },
        },
        {
            iconClass: 'rz-icon-ri--italic',
            attributes: { 'data-markdowneditor-button': 'italic' },
        },
    ],
} as ButtonGroupArgs

const nodeGroup = {
    size: 'sm',
    collapsed: true,
    buttons: [
        {
            iconClass: 'rz-icon-ri--single-quotes-l',
            attributes: { 'data-markdowneditor-button': 'blockquote' },
        },
        {
            iconClass: 'rz-icon-ri--link',
            attributes: { 'data-markdowneditor-button': 'link' },
        },
        {
            iconClass: 'rz-icon-ri--list-unordered',
            attributes: { 'data-markdowneditor-button': 'listUl' },
        },
    ],
} as ButtonGroupArgs

const spacingGroup = {
    size: 'sm',
    collapsed: true,
    buttons: [
        {
            iconClass: 'rz-icon-ri--separator',
            attributes: { 'data-markdowneditor-button': 'hr' },
        },
        {
            iconClass: 'rz-icon-ri--space',
            attributes: { 'data-markdowneditor-button': 'nbsp' },
        },
        {
            iconClass: 'rz-icon-ri--subtract-line',
            attributes: { 'data-markdowneditor-button': 'nb-hyphen' },
        },
    ],
} as ButtonGroupArgs

const featureGroup = {
    size: 'sm',
    collapsed: true,
    buttons: [
        {
            iconClass: 'rz-icon-ri--translate',
        },
        {
            iconClass: 'rz-icon-ri--translate-ai',
        },
    ],
} as ButtonGroupArgs

const previewGroup = {
    size: 'sm',
    collapsed: true,
    buttons: [
        {
            iconClass: 'rz-icon-ri--eye-line',
        },
    ],
} as ButtonGroupArgs

const BUTTON_GROUP_LIST = [
    headingGroup,
    styleGroup,
    nodeGroup,
    spacingGroup,
    featureGroup,
    previewGroup,
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
    controlsButtonGroups?: ButtonGroupArgs[]
    name: string
    value?: string
    cols?: number
    rows?: number
    maxlength?: number
    minlength?: number
    required?: boolean
    placeholder?: string
}

const mirrorPreviewId = 'codemirror-preview-containers'

const meta: Meta<Args> = {
    title: 'Components/Form/MarkdownEditor',
    tags: ['autodocs'],
    args: {
        placeholder: 'Enter your markdown here...',
        controlsButtonGroups: BUTTON_GROUP_LIST,
        minlength: 0,
        maxlength: 255,
    },
    decorators: [
        (story) => {
            const storyBody = document.querySelector('body')

            if (!document.querySelector(`#${mirrorPreviewId}`)) {
                const container = document.createElement('div')
                container.id = mirrorPreviewId
                storyBody?.appendChild(container)
            }

            return story()
        },
    ],
}

export default meta
type Story = StoryObj<Args>

function controlsRenderer(groups: Args['controlsButtonGroups']) {
    const controls = document.createElement('div')
    controls.classList.add(`${COMPONENT_CLASS_NAME}__controls`)

    groups?.forEach((groupArgs) => {
        const group = rzButtonGroupRenderer(groupArgs)
        controls.appendChild(group)
    })

    return controls
}

function rzMarkdownEditorRenderer(args: Args) {
    // Use `rz-markdown-editor` custom element to instantiate the component
    // Or `rz-markdown-editor` class to only apply the styles
    const field = document.createElement('div')
    field.classList.add('rz-form-field')

    const wrapper = document.createElement('rz-markdown-editor')
    wrapper.classList.add(COMPONENT_CLASS_NAME)
    field.appendChild(wrapper)

    const head = controlsRenderer(args.controlsButtonGroups || [])
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

    return field
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
