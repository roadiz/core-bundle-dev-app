/*
 * You can add automatically form button to actions-menus
 * Just add them to the .rz-action-save class and use the data-action-save
 * attribute to point form ID to submit.
 */
export default class SaveButtons {
    constructor() {
        this.button = document.querySelector('.rz-action-save')
        this.actionMenu = document.querySelector('.actions-menu')
        this.formToSave = null

        // Bind method
        this.onClick = this.onClick.bind(this)
        this.bindKeyboard()

        if (this.button && this.actionMenu) {
            this.init()
        }
    }

    init() {
        const formSelector = this.button.getAttribute('data-action-save')
        this.formToSave = document.querySelector(formSelector)

        if (this.formToSave) {
            this.actionMenu.insertBefore(this.button, this.actionMenu.firstChild)
            this.button.addEventListener('click', this.onClick)

            window.Mousetrap.bind(['mod+s'], (event) => {
                this.formToSave.requestSubmit()

                return false
            })
        }
    }

    unbind() {
        if (this.formToSave && this.formToSave) {
            this.button.removeEventListener('click', this.onClick)
        }
    }

    onClick() {
        this.formToSave.requestSubmit()
    }

    bindKeyboard() {
        window.Mousetrap.stopCallback = (e, element) => {
            // if the element has the class "mousetrap" then no need to stop
            if ((' ' + element.className + ' ').indexOf(' mousetrap ') > -1) {
                return false
            }

            // stop for input, select, and textarea
            return element.tagName === 'SELECT'
        }
    }
}
