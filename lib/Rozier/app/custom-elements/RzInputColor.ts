export default class RzInputColor extends HTMLElement {
    private colorInput: HTMLInputElement | null = null
    private textInput: HTMLInputElement | null = null

    constructor() {
        super()

        this.onColorInputChange = this.onColorInputChange.bind(this)
        this.onTextInputChange = this.onTextInputChange.bind(this)
    }

    onColorInputChange() {
        const newValue = this.colorInput?.value
        if (!newValue || !this.textInput) return

        this.textInput.value = newValue
        this.textInput.textContent = newValue
    }

    /**
     * Validates hex color formats:
     * - #RRGGBB (standard)
     * - #RRGGBBAA (with alpha)
     */
    private isValidHexColor(value: string): boolean {
        return /^#([0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$/.test(value)
    }

    onTextInputChange() {
        if (!this.textInput || !this.colorInput) return

        const value = this.textInput.value.trim()
        if (
            !this.textInput.hasAttribute('pattern') &&
            !this.isValidHexColor(value)
        ) {
            this.textInput.setCustomValidity('Invalid hex color format')
        } else {
            this.colorInput.value = value
        }
    }

    connectedCallback(): void {
        this.colorInput = this.querySelector('input[type="color"]')
        this.textInput = this.querySelector('input[type="text"]')

        if (!this.colorInput) {
            console.warn('RzInputColor: Missing color input')
            return
        }

        if (!this.textInput) {
            console.warn('RzInputColor: Missing text input')
            return
        }

        this.colorInput.addEventListener('change', this.onColorInputChange)
        this.textInput.addEventListener('change', this.onTextInputChange)
    }

    disconnectedCallback() {
        this.colorInput?.removeEventListener('change', this.onColorInputChange)
        this.textInput?.removeEventListener('change', this.onTextInputChange)
    }
}
