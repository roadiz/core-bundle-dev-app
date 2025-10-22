export const INPUT_TYPES = [
    'text',
    'number',
    'checkbox',
    'radio',
    'color',
    'email',
    'tel',
    'range',
    'url',
    'file',
    'password',
    'image',
    'hidden',
    'time',
    'date',
    'datetime-local',
    'month',
    'week',
    'button',
    'reset',
    'search',
    'submit',
] as const

// Find a way to reset value on color input
// if no value is set, the default is black
export default class RzFormInput extends HTMLInputElement {
    COMPONENT_CLASS_NAME = 'rz-form-input'

    constructor() {
        super()
    }

    syncValueAttribute() {
        // set the value attribute in DOM to reflect the current value, CSS could use it via attr()
        this.setAttribute('value', this.value)
        this.updateColorValue()
    }

    updateColorValue() {
        if (this.getAttribute('type') !== 'color') return false

        // Input color element always provide #000000 if no value is set
        // We consider a black value as unset data input
        if (this.value === '#000000') {
            this.removeAttribute('value')
        }
    }

    connectedCallback() {
        this.updateColorValue()

        this.addEventListener('input', this.syncValueAttribute)
    }

    disconnectedCallback() {
        this.removeEventListener('input', this.syncValueAttribute)
    }
}
