/**
 * Settings save buttons
 */
export default class SettingsSaveButtons {
    constructor() {
        this.buttons = document.querySelectorAll('.uk-button-settings-save')
        this.currentRequest = null
        this.buttonClick = this.buttonClick.bind(this)

        this.init()
    }

    /**
     * Init
     */
    init() {
        this.buttons.forEach((button) => button.addEventListener('click', this.buttonClick))
    }

    unbind() {
        this.buttons.forEach((button) => button.removeEventListener('click', this.buttonClick))
    }

    /**
     * Button click
     * @param {Event} e
     * @returns {boolean}
     */
    async buttonClick(e) {
        e.preventDefault()
        let form = e.currentTarget.parentElement.parentElement.querySelector('.uk-form')

        if (form.querySelectorAll('input[type=file]').length) {
            form.requestSubmit()
            return false
        }

        if (form.classList.contains('uk-has-errors')) {
            form.querySelector('.uk-alert').remove()
            form.classList.remove('uk-has-errors')
        }

        window.Rozier.lazyload.canvasLoader.show()
        const formData = new window.FormData(form)
        const response = await fetch(window.location.href, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
            body: formData,
        })
        if (!response.ok) {
            const data = await response.json()
            if (data.errors && data.errors.value) {
                for (let key in data.errors.value) {
                    form.classList.add('uk-has-errors')
                    form.insertAdjacentHTML(
                        'beforeend',
                        '<span class="uk-alert uk-alert-danger">' + data.errors.value[key] + '</span>'
                    )
                }
            } else if (data.errors) {
                for (let key in data.errors) {
                    form.classList.add('uk-has-errors')
                    form.insertAdjacentHTML(
                        'beforeend',
                        '<span class="uk-alert uk-alert-danger">' + data.errors[key] + '</span>'
                    )
                    break
                }
            }
        }

        await window.Rozier.getMessages()
        window.Rozier.lazyload.canvasLoader.hide()

        return false
    }

    /**
     * Window resize callback
     */
    resize() {}
}
