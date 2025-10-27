export default class RzSwitch extends HTMLButtonElement {
    constructor() {
        super()
    }

    onClick() {
        const isChecked = this.getAttribute('aria-checked') === 'true'
        this.setAttribute('aria-checked', (!isChecked).toString())
        this.classList.toggle('rz-switch--checked', !isChecked)
    }

    connectedCallback() {
        if (!this.hasAttribute('type')) {
            this.setAttribute('type', 'button')
        }
        if (!this.hasAttribute('role')) {
            this.setAttribute('role', 'switch')
        }
        if (!this.hasAttribute('aria-checked')) {
            this.setAttribute('aria-checked', 'false')
        }

        this.addEventListener('click', this.onClick)
    }

    disconnectedCallback() {
        this.removeEventListener('click', this.onClick)
    }
}
