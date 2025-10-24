export default class RzSelect extends HTMLSelectElement {
    constructor() {
        super()
    }

    syncValueAttribute() {
        if (this.value === '') {
            this.removeAttribute('value')
        } else {
            this.setAttribute('value', this.value)
        }
    }

    connectedCallback() {
        this.syncValueAttribute()

        this.addEventListener('input', this.syncValueAttribute)
    }

    disconnectedCallback() {
        this.removeEventListener('input', this.syncValueAttribute)
    }
}
