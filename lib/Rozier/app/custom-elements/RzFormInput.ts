export default class RzFormInput extends HTMLInputElement {
    COMPONENT_CLASS_NAME = 'rz-form-input'

    constructor() {
        super()
    }

    onChange(event: Event) {
        // set the value attribute in DOM to reflect the current value
        // CSS could use it via attr()
        this.setAttribute('value', this.value)
    }

    connectedCallback() {
        this.addEventListener('input', this.onChange.bind(this))
    }

    disconnectedCallback() {
        this.removeEventListener('input', this.onChange.bind(this))
    }
}
