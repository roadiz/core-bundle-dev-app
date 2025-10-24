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

export default class RzInput extends HTMLInputElement {
    constructor() {
        super()
    }

    // Make the value attribute reflect the actual value of the input
    // Like this we can access the value through CSS with attr()
    // and know if the input is filled or not
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
