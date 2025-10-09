export default class RzSaveButton extends HTMLButtonElement {
    constructor() {
        super()

        /** {@type {HTMLElement|null} */
        this.actionMenu = null
        /** {@type {HTMLFormElement|null} */
        this.formToSave = null

        this.onClick = this.onClick.bind(this)
        this.onKeyDown = this.onKeyDown.bind(this)
        this.binded = false
    }

    connectedCallback() {
        this.actionMenu = document.querySelector('.actions-menu')

        if (!this.actionMenu) {
            console.warn(
                'RzSaveButton: No .actions-menu found in the document.',
            )
            return
        }

        if (this.actionMenu !== this.parentElement) {
            // First move the button to the action menu, this will trigger again connectedCallback
            // Because the custom-element is disconnected from the DOM and reconnected
            this.actionMenu.insertBefore(this, this.actionMenu.firstChild)
            return
        }

        const formSelector = this.getAttribute('data-action-save')
        if (!formSelector) {
            return
        }
        this.formToSave = document.querySelector(formSelector)

        if (!this.formToSave) {
            console.warn(
                `RzSaveButton: No ${formSelector} found in the document.`,
            )
            return
        }

        this.addEventListener('click', this.onClick)
        window.addEventListener('keydown', this.onKeyDown)
        this.binded = true
    }

    disconnectedCallback() {
        if (!this.binded) {
            return
        }
        this.removeEventListener('click', this.onClick)
        window.removeEventListener('keydown', this.onKeyDown)
    }

    onKeyDown(event) {
        if (
            this.formToSave &&
            (event.metaKey || event.ctrlKey) &&
            event.key === 's'
        ) {
            event.preventDefault()
            this.formToSave.requestSubmit()
            return false
        }
    }

    onClick() {
        this.formToSave.requestSubmit()
    }
}
