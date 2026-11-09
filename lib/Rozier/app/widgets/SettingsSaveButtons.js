/**
 * Settings save buttons
 */
export default class SettingsSaveButtons {
    constructor() {
        this.buttons = document.querySelectorAll('.rz-button-settings-save')
        this.currentRequest = null
        this.buttonClick = this.buttonClick.bind(this)

        this.init()
    }

    /**
     * Init
     */
    init() {
        this.buttons.forEach((button) =>
            button.addEventListener('click', this.buttonClick),
        )
    }

    unbind() {
        this.buttons.forEach((button) =>
            button.removeEventListener('click', this.buttonClick),
        )
    }

    /**
     * Same markup as the rz-message Twig component, error color
     * @param {string} message
     * @returns {string}
     */
    errorMarkup(message) {
        const error = document.createElement('div')
        error.className = 'rz-settings-save-error rz-message rz-message--error'
        error.setAttribute('role', 'alert')
        const text = document.createElement('p')
        text.className = 'rz-message__text'
        text.textContent = message
        error.appendChild(text)

        return error.outerHTML
    }

    /**
     * Button click
     * @param {Event} e
     * @returns {boolean}
     */
    async buttonClick(e) {
        e.preventDefault()
        const parent =
            e.currentTarget?.closest('.setting-row') ||
            e.currentTarget?.closest('tr') // For attribute values edition
        let form = parent?.querySelector('.rz-form')

        if (!form) {
            console.error('No form found for settings save button')
            return false
        }

        if (form.querySelectorAll('input[type=file]').length) {
            form.requestSubmit()
            return false
        }

        form.querySelectorAll('.rz-settings-save-error').forEach((error) => {
            error.remove()
        })

        window.dispatchEvent(new CustomEvent('requestLoaderShow'))
        const formData = new window.FormData(form)
        const response = await fetch(window.location.href, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                // Required to prevent using this route as referer when login again
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        })
        if (!response.ok) {
            const data = await response.json()
            if (data.errors && data.errors.value) {
                for (let key in data.errors.value) {
                    form.insertAdjacentHTML(
                        'beforeend',
                        this.errorMarkup(data.errors.value[key]),
                    )
                }
            } else if (data.errors) {
                for (let key in data.errors) {
                    form.insertAdjacentHTML(
                        'beforeend',
                        this.errorMarkup(data.errors[key]),
                    )
                    break
                }
            }
        }

        await window.Rozier.getMessages()
        window.dispatchEvent(new CustomEvent('requestLoaderHide'))

        return false
    }

    /**
     * Window resize callback
     */
    resize() {}
}
