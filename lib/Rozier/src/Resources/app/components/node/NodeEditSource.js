import { toType } from '../../utils/plugins'

/**
 * Node edit source
 */
export default class NodeEditSource {
    constructor() {
        // Selectors
        this.content = document.querySelector('.content-node-edit-source')
        this.form = document.getElementById('edit-node-source-form')
        this.formRow = null
        this.dropdown = null
        this.input = null

        this.onInputKeyDown = this.onInputKeyDown.bind(this)
        this.onInputKeyUp = this.onInputKeyUp.bind(this)
        this.inputFocus = this.inputFocus.bind(this)
        this.inputFocusOut = this.inputFocusOut.bind(this)
        this.onFormSubmit = this.onFormSubmit.bind(this)
        this.wrapInTabs = this.wrapInTabs.bind(this)

        // Methods
        if (this.content && this.form) {
            this.formRow = this.content.querySelectorAll('.uk-form-row')
            window.requestAnimationFrame(this.wrapInTabs)
            this.init()
            this.initEvents()
        }
    }

    wrapInTabs() {
        let fieldGroups = {
            default: {
                name: 'default',
                id: 'default',
                fields: [],
            },
        }
        let fields = this.content.querySelectorAll('.uk-form-row[data-field-group-canonical]')
        let fieldsLength = fields.length
        let fieldsGroupsLength = 1

        if (fieldsLength > 0) {
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
        }

        this.content.classList.add('content-tabs-ready')
    }

    /**
     * Init
     */
    init() {
        // Inputs - add form help
        this.input = this.content.querySelectorAll('input, select')
        this.devNames = this.content.querySelectorAll('[data-dev-name]')

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
    }

    initEvents() {
        if (!this.content) {
            return
        }
        window.addEventListener('keydown', this.onInputKeyDown)
        window.addEventListener('keyup', this.onInputKeyUp)
        this.input.forEach((input) => {
            input.addEventListener('focus', this.inputFocus)
            input.addEventListener('focusout', this.inputFocusOut)
        })
        this.form.addEventListener('submit', this.onFormSubmit)
    }

    unbind() {
        if (!this.content) {
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

    async onFormSubmit(event) {
        event.preventDefault()
        window.Rozier.lazyload.canvasLoader.show()

        /*
         * Trigger event on window to notify open
         * widgets to close.
         */
        const pageChangeEvent = new CustomEvent('pagechange')
        window.dispatchEvent(pageChangeEvent)

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
            if (error.response) {
                const data = await error.response.json()
                if (data.errors) {
                    this.displayErrors(data.errors)
                    window.UIkit.notify({
                        message: data.message,
                        status: 'danger',
                        timeout: 2000,
                        pos: 'top-center',
                    })
                }
            }
        }

        window.Rozier.lazyload.canvasLoader.hide()
        await window.Rozier.getMessages()
        window.Rozier.refreshAllNodeTrees()

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
     * @param {Array} errors
     * @param {Boolean} keepExisting Keep existing errors.
     */
    displayErrors(errors, keepExisting = false) {
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
                    field.insertAdjacentHTML(
                        'beforeend',
                        '<p class="error-message uk-alert uk-alert-danger"><i class="uk-icon uk-icon-warning"></i> ' +
                            errorMessage +
                            '</p>'
                    )
                }
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

    /**
     * Window resize callback
     */
    resize() {}
}
