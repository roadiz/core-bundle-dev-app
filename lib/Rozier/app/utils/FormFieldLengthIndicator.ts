import type RzMarkdownEditor from '~/custom-elements/RzMarkdownEditor'

export default class FormFieldLengthIndicator {
    inputElement: HTMLInputElement | HTMLTextAreaElement | null = null
    indicatorElement: HTMLElement | null = null
    lengthElement: HTMLElement | null = null
    errorClassName: string = ''
    maxLength: number = 0
    minLength: number = 0
    markdownEditor: RzMarkdownEditor | null = null

    constructor() {
        this.onInput = this.onInput.bind(this)
        this.onMarkdownEditorChange = this.onMarkdownEditorChange.bind(this)
    }

    init(
        element: HTMLElement,
        options: { maxLength?: number; minLength?: number } = {},
    ) {
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

        // Find input element
        const inputElement = element?.querySelector(
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
                : Number(this.inputElement?.dataset.maxLength)
        const minLength =
            typeof options.minLength !== 'undefined'
                ? options.minLength
                : Number(this.inputElement?.dataset.minLength)

        if (maxLength) {
            this.maxLength = maxLength
        }

        if (minLength) {
            this.minLength = minLength
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
}
