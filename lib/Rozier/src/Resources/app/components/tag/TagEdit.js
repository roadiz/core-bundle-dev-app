import {toType} from '../../utils/plugins'

/**
 * Node edit source
 */
export default class TagEdit {
    constructor() {
        // Selectors
        this.content = document.querySelector('.content-tag-edit')
        this.form = document.getElementById('edit-tag-form')
        this.formRow = null
        this.dropdown = null

        // Binded methods
        this.onFormSubmit = this.onFormSubmit.bind(this)

        // Methods
        if (this.content) {
            this.formRow = this.content.querySelectorAll('.uk-form-row')
            this.initEvents()
        }
    }

    initEvents() {
        this.form.addEventListener('submit', this.onFormSubmit)
    }

    unbind() {
        if (this.content) {
            this.form.removeEventListener('submit', this.onFormSubmit)
        }
    }

    async onFormSubmit(event) {
        event.preventDefault()
        window.Rozier.lazyload.canvasLoader.show()

        /*
         * Trigger event on window to notify open
         * widgets to close.
         */
        let pageChangeEvent = new CustomEvent('pagechange')
        window.dispatchEvent(pageChangeEvent)

        const formData = new FormData(this.form)
        const response = await fetch(window.location.href, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
            body: formData,
        })
        if (!response.ok) {
            const data = await response.json()
            if (data.errors) {
                this.displayErrors(data.errors)
                window.UIkit.notify({
                    message: data.message,
                    status: 'danger',
                    timeout: 2000,
                    pos: 'top-center',
                })
            }
        } else {
            this.cleanErrors()
        }
        await window.Rozier.refreshMainTagTree()
        await window.Rozier.getMessages()
        window.Rozier.lazyload.canvasLoader.hide()

        return false
    }

    cleanErrors() {
        const previousErrors = document.querySelectorAll('.form-errored')
        previousErrors.forEach((index) => {
            index.classList.remove('form-errored')
            index.querySelector('.error-message').remove()
        })
    }

    /*
     * @param {Array} errors
     * @param {Boolean} keepExisting Keep existing errors.
     */
    displayErrors(errors, keepExisting) {
        // First clean fields
        if (!keepExisting || keepExisting === false) {
            this.cleanErrors()
        }

        for (let key in errors) {
            let classKey = null
            let errorMessage = null
            if (toType(errors[key]) === 'object') {
                this.displayErrors(errors[key], true)
            } else {
                classKey = key.replace('_', '-')
                if (errors[key] instanceof Array) {
                    errorMessage = errors[key][0]
                } else {
                    errorMessage = errors[key]
                }
                let field = document.querySelector('.form-col-' + classKey)
                if (field) {
                    field.classList.add('form-errored')
                    field.insertAdjacentHTML('beforeend',
                        '<p class="error-message uk-alert uk-alert-danger"><i class="uk-icon uk-icon-warning"></i> ' +
                            errorMessage +
                            '</p>'
                    )
                }
            }
        }
    }

    /**
     * Window resize callback
     */
    resize() {}
}
