import type RzMarkdownEditor from '~/custom-elements/RzMarkdownEditor'

export default class FormFieldLengthIndicator {
    element: HTMLElement | null = null
    inputElement: HTMLInputElement | HTMLTextAreaElement | null = null
    indicatorElement: HTMLElement | null = null
    lengthElement: HTMLElement | null = null
    errorClassName: string = ''
    maxLength: number = 0
    minLength: number = 0
    markdownEditor: RzMarkdownEditor | null = null

    constructor() {
        // Bind methods
        this.onInput = this.onInput.bind(this)
        this.onMarkdownEditorChange = this.onMarkdownEditorChange.bind(this)
        this.onLengthChange = this.onLengthChange.bind(this)
    }

    init(
        element: HTMLElement,
        options: { maxLength?: number; minLength?: number } = {},
    ) {
        this.element = element

        // Find elements
        this.indicatorElement = element?.querySelector?.(
            '[data-length-indicator]',
        )

        if (!this.indicatorElement) return

        this.lengthElement = this.indicatorElement?.querySelector(
            '[data-length-indicator-current]',
        )

        // Get error class name from data attribute
        const errorClassName = this.indicatorElement?.dataset.lengthIndicator

        if (errorClassName) {
            this.errorClassName = errorClassName
        }

        element.addEventListener('length-change', this.onLengthChange)

        // Find input element
        const inputElement = element.querySelector<HTMLElement>(
            '[data-max-length], [data-min-length]',
        )

        if (
            inputElement instanceof HTMLInputElement ||
            inputElement instanceof HTMLTextAreaElement
        ) {
            this.inputElement = inputElement
            this.inputElement?.addEventListener('input', this.onInput)
        }

        // Markdown editor
        const markdownEditor =
            element.querySelector<RzMarkdownEditor>('rz-markdown-editor')

        if (markdownEditor) {
            this.markdownEditor = markdownEditor

            this.markdownEditor.addEventListener(
                'change',
                this.onMarkdownEditorChange,
            )
        }

        // Set maxLength and minLength
        const maxLength =
            typeof options.maxLength !== 'undefined'
                ? options.maxLength
                : Number(inputElement?.dataset.maxLength)
        const minLength =
            typeof options.minLength !== 'undefined'
                ? options.minLength
                : Number(inputElement?.dataset.minLength)

        if (maxLength) {
            this.maxLength = maxLength
        }

        if (minLength) {
            this.minLength = minLength
        }

        // Initial update
        if (this.inputElement) {
            this.updateLength(this.inputElement.value.length)
        } else if (this.markdownEditor) {
            this.updateLength(this.markdownEditor.strippedValue.length)
        }
    }

    dispose() {
        this.inputElement?.removeEventListener('input', this.onInput)
        this.inputElement = null

        this.markdownEditor?.removeEventListener(
            'change',
            this.onMarkdownEditorChange,
        )
        this.markdownEditor = null

        this.element?.removeEventListener('length-change', this.onLengthChange)
        this.element = null
    }

    updateLength(length: number) {
        const hasError =
            (this.maxLength !== 0 && length > this.maxLength) ||
            (this.minLength !== 0 && length < this.minLength)

        if (this.errorClassName) {
            this.indicatorElement?.classList.toggle(
                this.errorClassName,
                hasError,
            )
        }

        if (this.lengthElement) {
            this.lengthElement.textContent = String(length)
        }
    }

    onInput() {
        this.updateLength(this.inputElement!.value.length)
    }

    onMarkdownEditorChange() {
        this.updateLength(this.markdownEditor!.strippedValue.length)
    }

    onLengthChange(event: CustomEvent<{ length: number }>) {
        if (!event.detail) return

        const { length } = event.detail

        if (typeof length !== 'number') return

        this.updateLength(length)
    }
}
