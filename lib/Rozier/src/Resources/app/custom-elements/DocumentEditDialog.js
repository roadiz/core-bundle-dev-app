const INPUT_BASE_NAME = 'document_edit_dialog[proxy]'
const INPUT_KEYS = ['imageCropAlignment', 'hotspot']

export default class DocumentEditDialog extends HTMLElement {
    constructor() {
        super()
    }

    connectedCallback() {
        // Dialog (root element)
        this.dialog = document.createElement('dialog')
        this.dialog.setAttribute('closedby', 'any')
        this.dialog.classList.add('document-edit-dialog')
        this.dialog.innerHTML = `
            <header>
            </header>
            <div class="document-edit-dialog__content"></div>
            ${INPUT_KEYS.map((key) => `<input type="hidden" name="${INPUT_BASE_NAME}[${key}]">`).join('\n')}
            <footer>
                <div class="document-edit-dialog__actions"> 
                    <button class="uk-button uk-button-small document-edit-dialog__cancel" type="button" value="cancel">Annuler</button>
                    <button class="uk-button uk-button-small document-edit-dialog__submit" type="button" value="submit">Appliquer</button>
                </div>
            </footer>
        `

        // Add event listeners for dialog actions
        this.addEventListener('click', (event) => {
            if (event.target.value === 'cancel' || event.target.value === 'submit') {
                this.dialog.close(event.target.value)
            }
        })

        this.dialog.addEventListener('close', () => {
            if (this.dialog.returnValue === 'submit') this.applyInputValues()
            this.remove()
        })

        // Append the dialog to the custom element
        this.appendChild(this.dialog)

        fetch('/rz-admin/documents/alignment-template').then(async (response) => {
            if (!response.ok) {
                throw new Error('Network response was not ok')
            }

            // Parse the HTML response
            const html = await response.text()
            const tempContainer = document.createElement('div')
            tempContainer.innerHTML = html

            const widget = tempContainer.querySelector('document-alignment-widget')

            // Setup the widget
            if (widget) {
                const imagePath = this.getAttribute('image-path')

                widget.setAttribute('image-path', imagePath)
                widget.setAttribute('input-base-name', INPUT_BASE_NAME)
            }

            // Append the widget to the dialog
            const content = this.dialog.querySelector('.document-edit-dialog__content')
            content.append(...tempContainer.childNodes)
        })
    }

    disconnectedCallback() { }

    applyInputValues() {
        const destInputBaseName = this.getAttribute('input-base-name')

        if (!destInputBaseName) return

        INPUT_KEYS.forEach((key) => {
            const input = this.querySelector(`input[name="${INPUT_BASE_NAME}[${key}]"]`)

            if (input) {
                const destInput = document.querySelector(`input[name="${destInputBaseName}[${key}]"]`)

                if (destInput) destInput.value = input.value
            }
        })
    }

    showModal() {
        this.dialog?.showModal()
    }
}
