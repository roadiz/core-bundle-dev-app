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
        this.dialog.classList.add('uk-form', 'document-edit-dialog')
        this.dialog.innerHTML = `
            <header class="document-edit-dialog__header">
                <h2 class="document-edit-dialog__title">
                    <i class="uk-icon-image"></i>
                    ${this.getAttribute('title')}
                </h2>
                <button class="document-edit-dialog__close" type="button" value="cancel">
                    <i class="uk-icon-close"></i>
                </button>
            </header>
            <div class="document-edit-dialog__content">
                <div class="document-edit-dialog__content__placeholder">
                    <div class="spinner"></div>
                </div>
            </div>
            ${INPUT_KEYS.map((key) => `<input type="hidden" name="${INPUT_BASE_NAME}[${key}]">`).join('\n')}
            <footer>
                <div class="document-edit-dialog__actions"> 
                    ${this.getEditLink()}
                    <button class="uk-button uk-button-small document-edit-dialog__cancel" type="button" value="cancel">
                        ${window.RozierConfig?.messages?.documentEditDialogCancel}
                    </button>
                    <button class="uk-button uk-button-small document-edit-dialog__submit" type="button" value="submit">
                        ${window.RozierConfig?.messages?.documentEditDialogSubmit}
                    </button>
                </div >
            </footer >
    `

        // Add event listeners for dialog actions
        this.dialog
            .querySelectorAll('.document-edit-dialog__close, .document-edit-dialog__actions > button')
            ?.forEach((button) => {
                button.addEventListener('click', (event) => {
                    if (event.currentTarget.value === 'cancel' || event.target.value === 'submit') {
                        this.dialog.close(event.target.value)
                    }
                })
            })

        this.dialog.addEventListener('close', () => {
            if (this.dialog.returnValue === 'submit') {
                this.applyInputValues(INPUT_BASE_NAME, this.getAttribute('input-base-name'), this.dialog, document)
            }

            this.remove()
        })

        // Append the dialog to the custom element
        this.appendChild(this.dialog)

        this.loadTemplate()
    }

    loadTemplate() {
        const templatePath = this.getAttribute('template-path')

        if (!templatePath) return

        return fetch(templatePath).then(async (response) => {
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
                        const value = this.dialog.querySelector(`input[name = "${INPUT_BASE_NAME}[${key}]"]`)?.value

                        return !!value && value !== 'null' ? key : ''
                    }).filter(Boolean)[0] || 'none'

                widget.setAttribute('active-tab-id', activeTabId)
                widget.setAttribute(
                    'active-tab-value',
                    this.dialog.querySelector(`input[name = "${INPUT_BASE_NAME}[${activeTabId}]"]`)?.value
                )
                widget.setAttribute('image-path', imagePath)
                widget.setAttribute('input-base-name', INPUT_BASE_NAME)
            }

            // Append the widget to the dialog
            const content = this.dialog.querySelector('.document-edit-dialog__content')

            content.replaceChildren(...tempContainer.childNodes)
        })
    }

    applyInputValues(sourceInputBaseName, destInputBaseName, sourceElement, destElement) {
        INPUT_KEYS.forEach((key) => {
            const sourceInput = sourceElement.querySelector(`input[name = "${sourceInputBaseName}[${key}]"]`)

            if (sourceInput) {
                const destInput = destElement.querySelector(`input[name = "${destInputBaseName}[${key}]"]`)

                if (destInput) destInput.value = sourceInput.value
            }
        })
    }

    getEditLink() {
        const editUrl = this.getAttribute('edit-url')

        if (!editUrl) return ''

        return `
            <a class="uk-button uk-button-small document-edit-dialog__edit-link" href="${editUrl}">
                ${window.RozierConfig?.messages?.documentEditDialogEdit}
            </a>
        `
    }

    showModal() {
        this.dialog?.showModal()
    }
}
