import $ from 'jquery'

/**
 * Settings save buttons
 */
export default class SettingsSaveButtons {
    constructor() {
        // Selectors
        this.$button = $('.uk-button-settings-save')
        this.currentRequest = null

        // Bind methods
        this.buttonClick = this.buttonClick.bind(this)

        // Methods
        if (this.$button.length) {
            this.init()
        }
    }

    /**
     * Init
     */
    init() {
        // Events
        this.$button.on('click', this.buttonClick)
    }

    unbind() {
        this.$button.off('click', this.buttonClick)
    }

    /**
     * Button click
     * @param {Event} e
     * @returns {boolean}
     */
    async buttonClick(e) {
        e.preventDefault()
        let $form = $(e.currentTarget).parent().parent().find('.uk-form').eq(0)

        if ($form.find('input[type=file]').length) {
            $form.submit()
            return false
        }

        if ($form.hasClass('uk-has-errors')) {
            $form.find('.uk-alert').remove()
            $form.removeClass('uk-has-errors')
        }

        window.Rozier.lazyload.canvasLoader.show()
        const formData = new window.FormData($form[0])
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
                    $form.addClass('uk-has-errors')
                    $form.append('<span class="uk-alert uk-alert-danger">' + data.errors.value[key] + '</span>')
                }
            } else if (data.errors) {
                for (let key in data.errors) {
                    $form.addClass('uk-has-errors')
                    $form.append('<span class="uk-alert uk-alert-danger">' + data.errors[key] + '</span>')
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
