export default class InputLengthWatcher {
    constructor() {
        this.maxLengthed = document.querySelectorAll('input[data-max-length]')
        this.minLengthed = document.querySelectorAll('input[data-min-length]')

        this.onMaxKeyUp = this.onMaxKeyUp.bind(this)
        this.onMinKeyUp = this.onMinKeyUp.bind(this)

        this.init()
    }

    init() {
        if (this.maxLengthed.length) {
            this.maxLengthed.forEach((input) => {
                input.addEventListener('keyup', this.onMaxKeyUp)
            })
        }

        if (this.minLengthed.length) {
            this.minLengthed.forEach((input) => {
                input.addEventListener('keyup', this.onMinKeyUp)
            })
        }
    }

    unbind() {
        if (this.maxLengthed.length) {
            this.maxLengthed.forEach((input) => {
                input.removeEventListener('keyup', this.onMaxKeyUp)
            })
        }

        if (this.minLengthed.length) {
            this.minLengthed.forEach((input) => {
                input.removeEventListener('keyup', this.onMinKeyUp)
            })
        }
    }

    /**
     * @param {Event} event
     */
    onMaxKeyUp(event) {
        let input = event.currentTarget
        let maxLength = Math.round(event.currentTarget.getAttribute('data-max-length'))
        let currentLength = event.currentTarget.value.length

        if (currentLength > maxLength) {
            input.classList.add('uk-form-danger')
        } else {
            input.classList.remove('uk-form-danger')
        }
    }

    /**
     * @param {Event} event
     */
    onMinKeyUp(event) {
        let input = event.currentTarget
        let maxLength = Math.round(event.currentTarget.getAttribute('data-min-length'))
        let currentLength = event.currentTarget.value.length

        if (currentLength <= maxLength) {
            input.classList.add('uk-form-danger')
        } else {
            input.classList.remove('uk-form-danger')
        }
    }
}
