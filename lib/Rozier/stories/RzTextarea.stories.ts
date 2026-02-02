import type { Meta, StoryObj } from '@storybook/html-vite'
import { Args as RzInputArgs } from './RzInput.stories'

export type Args = RzInputArgs & {
    rows?: number
    cols?: number
    maxlength?: number
    minlength?: number
    wrap?: 'soft' | 'hard' | 'off'
    autocomplete?: AutoFill
    autofocus?: boolean
    disabled?: boolean
    readonly?: boolean
    required?: boolean
    placeholder?: string
    name?: string
    form?: string
    dirname?: string
    spellcheck?: boolean | 'true' | 'false'
}

const meta: Meta<Args> = {
    title: 'Components/Form/Textarea',
    tags: ['autodocs'],
    args: {
        rows: 4,
        cols: 50,
        maxlength: undefined,
        minlength: undefined,
        wrap: 'soft',
        autocomplete: undefined,
        autofocus: false,
        disabled: false,
        readonly: false,
        required: false,
        placeholder: '',
        name: 'default-textarea-name',
        form: undefined,
        dirname: undefined,
        spellcheck: undefined,
    },
}

export default meta
type Story = StoryObj<Args>

function rzTextareaRenderer(args: Args) {
    const textarea = document.createElement('textarea')
    textarea.classList.add('rz-textarea')

    // Apply standard attributes
    if (args.rows !== undefined) textarea.rows = args.rows
    if (args.cols !== undefined) textarea.cols = args.cols
    if (args.maxlength) textarea.maxLength = args.maxlength
    if (args.minlength) textarea.minLength = args.minlength
    if (args.wrap) textarea.wrap = args.wrap
    if (args.autocomplete) textarea.autocomplete = args.autocomplete
    if (args.autofocus) textarea.autofocus = args.autofocus
    if (args.disabled) textarea.disabled = args.disabled
    if (args.readonly) textarea.readOnly = args.readonly
    if (args.required) textarea.required = args.required
    if (args.placeholder) textarea.placeholder = args.placeholder
    if (args.name) textarea.name = args.name
    if (args.form) textarea.setAttribute('form', args.form)
    if (args.dirname) textarea.setAttribute('dirname', args.dirname)
    if (args.spellcheck !== undefined) {
        textarea.spellcheck =
            typeof args.spellcheck === 'boolean'
                ? args.spellcheck
                : args.spellcheck === 'true'
    }

    Object.entries(args.attributes || {}).forEach(([key, value]) => {
        if (value) textarea.setAttribute(key, String(value))
    })

    return textarea
}

export const Default: Story = {
    render: (args) => {
        return rzTextareaRenderer(args)
    },
}
