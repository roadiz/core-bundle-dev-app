import { toType } from '../utils/plugins'

export default class NodeSourceEditPage extends HTMLElement {
    constructor() {
        super()

        this.onInputKeyDown = this.onInputKeyDown.bind(this)
        this.onInputKeyUp = this.onInputKeyUp.bind(this)
        this.inputFocus = this.inputFocus.bind(this)
        this.inputFocusOut = this.inputFocusOut.bind(this)
        this.onFormSubmit = this.onFormSubmit.bind(this)
        this.wrapInTabs = this.wrapInTabs.bind(this)

        this.dropdown = null
        this.form = this.querySelector('#edit-node-source-form')
        this.formRow = this.querySelectorAll('.uk-form-row')
        this.input = this.querySelectorAll('input, select')
        this.devNames = this.querySelectorAll('[data-dev-name]')
    }

    connectedCallback() {
        if (!this.form) {
            return
        }

        // Inputs - add form help
        this.wrapInTabs()

        this.devNames.forEach((devName) => {
            if (devName.getAttribute('data-dev-name') !== '') {
                const label = devName.closest('.uk-form-row').querySelector('label')
                const barLabel = devName.querySelector('.uk-navbar-brand.label')

                if (label) {
                    label.insertAdjacentHTML(
                        'beforeend',
                        '<span class="field-dev-name">' + devName.getAttribute('data-dev-name') + '</span>'
                    )
                } else if (barLabel) {
                    barLabel.insertAdjacentHTML(
                        'beforeend',
                        '<span class="field-dev-name">' + devName.getAttribute('data-dev-name') + '</span>'
                    )
                }
            }
        })

        // Check if children node widget needs his dropdowns to be flipped up
        this.formRow.forEach((row, i) => {
            if (row.className.indexOf('children-nodes-widget') >= 0) {
                this.childrenNodeWidgetFlip(i)
            }
        })
        window.addEventListener('keydown', this.onInputKeyDown)
        window.addEventListener('keyup', this.onInputKeyUp)
        this.input.forEach((input) => {
            input.addEventListener('focus', this.inputFocus)
            input.addEventListener('focusout', this.inputFocusOut)
        })
        this.form.addEventListener('submit', this.onFormSubmit)
    }

    disconnectedCallback() {
        if (!this.form) {
            return
        }
        window.removeEventListener('keydown', this.onInputKeyDown)
        window.removeEventListener('keyup', this.onInputKeyUp)
        this.input.forEach((input) => {
            input.removeEventListener('focus', this.inputFocus)
            input.removeEventListener('focusout', this.inputFocusOut)
        })
        this.form.removeEventListener('submit', this.onFormSubmit)
    }

    wrapInTabs() {
        let fieldGroups = {
            default: {
                name: 'default',
                id: 'default',
                fields: [],
            },
        }
        let fields = this.querySelectorAll('.uk-form-row[data-field-group-canonical]')
        const fieldsLength = fields.length
        let fieldsGroupsLength = 1

        if (fieldsLength <= 0) {
            this.classList.add('content-tabs-ready')
            return
        }

        for (let i = 0; i < fieldsLength; i++) {
            let groupName = fields[i].getAttribute('data-field-group')
            let groupNameCanonical = fields[i].getAttribute('data-field-group-canonical')
            if (groupNameCanonical) {
                if (typeof fieldGroups[groupNameCanonical] === 'undefined') {
                    fieldGroups[groupNameCanonical] = {
                        name: groupName,
                        id: groupNameCanonical,
                        fields: [],
                    }
                    fieldsGroupsLength++
                }
                fieldGroups[groupNameCanonical].fields.push(fields[i])
            } else {
                fieldGroups['default'].fields.push(fields[i])
            }
        }

        if (fieldsGroupsLength > 1) {
            this.form.insertAdjacentHTML(
                'beforeend',
                '<div id="node-source-form-switcher-nav-cont">' +
                '<ul id="node-source-form-switcher-nav" class="uk-subnav uk-subnav-pill" data-uk-switcher="{connect:\'#node-source-form-switcher\', swiping:false}">' +
                '</ul>' +
                '</div>' +
                '<ul id="node-source-form-switcher" class="uk-switcher">' +
                '</ul>'
            )
            const formSwitcher = this.form.querySelector('.uk-switcher')
            const formSwitcherNav = this.form.querySelector('#node-source-form-switcher-nav')

            for (let index in fieldGroups) {
                let fieldGroup = fieldGroups[index]
                let groupName2Safe = fieldGroup.id.replace(/[\s_]/g, '-').replace(/[^\w-]+/g, '')
                let groupId = 'group-' + groupName2Safe

                formSwitcher.insertAdjacentHTML('beforeend', '<li class="field-group" id="' + groupId + '"></li>')

                if (fieldGroup.id === 'default') {
                    formSwitcherNav.insertAdjacentHTML(
                        'beforeend',
                        '<li class="switcher-nav-item"><a href="#"><i class="uk-icon-star"></i></a></li>'
                    )
                } else {
                    formSwitcherNav.insertAdjacentHTML(
                        'beforeend',
                        '<li class="switcher-nav-item"><a href="#">' + fieldGroup.name + '</a></li>'
                    )
                }
                let group = formSwitcher.querySelector('#' + groupId)

                for (let index = 0; index < fieldGroup.fields.length; index++) {
                    group.appendChild(fieldGroup.fields[index])
                }
            }
        }

        this.classList.add('content-tabs-ready')
    }

    async onFormSubmit(event) {
        event.preventDefault()
        window.dispatchEvent(new CustomEvent('requestLoaderShow'))

        /*
         * Trigger event on window to notify open
         * widgets to close.
         */
        window.dispatchEvent(new CustomEvent('pagechange'))

        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
                body: new URLSearchParams(new FormData(this.form)),
            })
            if (!response.ok) {
                throw response
            }
            const data = await response.json()
            this.cleanErrors()
            // Update preview or public url
            if (data.public_url) {
                const publicUrlLinks = document.querySelectorAll('a.public-url-link')
                publicUrlLinks.forEach((link) => link.setAttribute('href', data.public_url))
            }
            if (data.preview_url) {
                const previewUrlLinks = document.querySelectorAll('a.preview-url-link')
                previewUrlLinks.forEach((link) => link.setAttribute('href', data.preview_url))
            }
        } catch (error) {
            // Error is a response object
            const data = await error.json()
            if (data.errors) {
                this.displayErrors(data.errors)
                window.dispatchEvent(new CustomEvent('pushToast', {
                    detail: {
                        message: data.message,
                        status: 'danger',
                    },
                }))
            }
        }

        window.dispatchEvent(new CustomEvent('requestLoaderHide'))
        window.dispatchEvent(new CustomEvent('requestAllNodeTreeChange'))

        return false
    }

    cleanErrors() {
        const previousErrors = document.querySelectorAll('.form-errored')
        previousErrors.forEach((element) => {
            element.classList.remove('form-errored')
            element.querySelector('.error-message').remove()
        })
    }

    /**
     * @param {array} errors
     * @param {boolean} keepExisting Keep existing errors.
     * @param {string} prefix Keep existing errors.
     */
    displayErrors(errors, keepExisting = false, prefix = '') {
        // First clean fields
        if (!keepExisting || keepExisting === false) {
            this.cleanErrors()
        }

        const keys = Object.keys(errors)
        keys.forEach((index) => {
            const error = errors[index]

            if (Array.isArray(error)) {
                error.forEach((subError, subIndex) => {
                    this.displayErrors(subError, true, prefix + index + '_' + subIndex + '_')
                })
                return
            }

            if (toType(error) === 'string') {
                this.displaySingleError(prefix, index, errors)
                return
            }

            // If error is an object, we need to display it recursively
            if (toType(error) !== 'object') {
                return
            }

            // loop through object keys
            const keys = Object.keys(error)
            keys.forEach((key) => {
                if (toType(error[key]) === 'object') {
                    this.displayErrors(error[key], true, prefix + index + '_' + key + '_')
                } else {
                    this.displaySingleError(prefix, key, error)
                }
            })
        })
    }

    /**
     * @param {string} prefix
     * @param {string} index
     * @param {array} errors
     */
    displaySingleError(prefix, index, errors = []) {
        const idKey = 'source_' + (prefix + index)
        let errorMessage = ''
        if (Array.isArray(errors[index])) {
            errorMessage = errors[index][0]
        } else {
            errorMessage = errors[index]
        }
        const input = document.getElementById(idKey)
        if (input) {
            const field = input.closest('.uk-form-row')
            if (field) {
                field.classList.add('form-errored')
                field.insertAdjacentHTML(
                    'beforeend',
                    `<p class="error-message uk-alert uk-alert-danger"><i class="uk-icon uk-icon-warning"></i> ${errorMessage}</p>`
                )
            }
        }
    }

    /**
     * On keyboard key down
     * @param {Event} event
     */
    onInputKeyDown(event) {
        // ALT key
        if (event.keyCode === 18) {
            document.body.classList.toggle('dev-name-visible')
        }
    }

    /**
     * On keyboard key up
     * @param {Event} event
     */
    onInputKeyUp(event) {
        // ALT key
        if (event.keyCode === 18) {
            document.body.classList.toggle('dev-name-visible')
        }
    }

    /**
     * Flip children node widget
     * @param  {Number} index
     */
    childrenNodeWidgetFlip(index) {
        if (index >= this.formRow.length - 2) {
            this.dropdown = this.formRow[index].querySelector('.uk-dropdown-small')
            this.dropdown.classList.add('uk-dropdown-up')
        }
    }

    /**
     * Input focus
     * @param {Event} e
     */
    inputFocus(e) {
        e.currentTarget.parentElement.classList.add('form-col-focus')
    }

    /**
     * Input focus out
     * @param {Event} e
     */
    inputFocusOut(e) {
        e.currentTarget.parentElement.classList.remove('form-col-focus')
    }
}
