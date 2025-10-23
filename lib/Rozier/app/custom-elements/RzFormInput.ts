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

export default class RzFormInput extends HTMLInputElement {
    constructor() {
        super()
    }

    // set the value attribute in DOM to reflect the current value, CSS could use it via attr()
    syncValueAttribute() {
        const isUnsetColorValue =
            this.getAttribute('type') === 'color' && this.value === '#000000'

        if (this.value === '' || isUnsetColorValue) {
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
