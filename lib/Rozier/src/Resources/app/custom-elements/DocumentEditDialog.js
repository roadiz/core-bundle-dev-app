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
            if (this.dialog.returnValue === 'submit') {
                this.applyInputValues(INPUT_BASE_NAME, this.getAttribute('input-base-name'), this.dialog, document)
            }

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
                this.applyInputValues(this.getAttribute('input-base-name'), INPUT_BASE_NAME, document, this.dialog)

                const imagePath = this.getAttribute('image-path')
                const activeTabId =
                    INPUT_KEYS.map((key) => {
                        const value = this.dialog.querySelector(`input[name="${INPUT_BASE_NAME}[${key}]"]`)?.value

                        return !!value && value !== 'null' ? key : ''
                    }).filter(Boolean)[0] || 'none'

                widget.setAttribute('active-tab-id', activeTabId)
                widget.setAttribute(
                    'active-tab-value',
                    this.dialog.querySelector(`input[name="${INPUT_BASE_NAME}[${activeTabId}]"]`)?.value
                )
                widget.setAttribute('image-path', imagePath)
                widget.setAttribute('input-base-name', INPUT_BASE_NAME)
            }

            // Append the widget to the dialog
            const content = this.dialog.querySelector('.document-edit-dialog__content')

            content.append(...tempContainer.childNodes)
        })
    }

    applyInputValues(sourceInputBaseName, destInputBaseName, sourceElement, destElement) {
        INPUT_KEYS.forEach((key) => {
            const sourceInput = sourceElement.querySelector(`input[name="${sourceInputBaseName}[${key}]"]`)

            if (sourceInput) {
                const destInput = destElement.querySelector(`input[name="${destInputBaseName}[${key}]"]`)

                if (destInput) destInput.value = sourceInput.value
            }
        })
    }

    showModal() {
        this.dialog?.showModal()
    }
}
