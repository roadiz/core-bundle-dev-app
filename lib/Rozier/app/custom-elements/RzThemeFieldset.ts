export default class RzThemeFieldset extends HTMLFieldSetElement {
    inputs: HTMLInputElement[] = []

    constructor() {
        super()

        this.onInputChange = this.onInputChange.bind(this)
    }

    onInputChange(event: Event) {
        const input = event.target as HTMLInputElement
        if (!input.value) return
        document.documentElement.style.colorScheme = input.value
    }

    connectedCallback() {
        const inputs = Array.from(this.querySelectorAll('input'))
        inputs.forEach((input) =>
            input.addEventListener('change', this.onInputChange),
        )
    }

    disconnectedCallback() {
        const inputs = Array.from(this.querySelectorAll('input[type="radio"]'))
        inputs.forEach((input) =>
            input.removeEventListener('change', this.onInputChange),
        )
    }
}
