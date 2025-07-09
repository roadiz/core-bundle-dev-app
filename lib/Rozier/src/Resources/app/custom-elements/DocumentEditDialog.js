export default class DocumentEditDialog extends HTMLElement {
    constructor() {
        super()
    }

    async connectedCallback() {
        const template = await fetch('/rz-admin/documents/alignment-template').then((response) => {
            if (!response.ok) {
                throw new Error('Network response was not ok')
            }
            return response.text()
        })

        // Dialog (root element)
        this.dialog = document.createElement('dialog')
        this.dialog.classList.add('document-edit-dialog')
        this.dialog.innerHTML = `
            <header>
            </header>
            <div>
                ${template}
            </div>
            </footer>
        `

        this.appendChild(this.dialog)

        // Widget
        this.widget = this.dialog.querySelector('document-alignment-widget')
    }

    disconnectedCallback() { }

    showModal(document) {
        this.widget.setAttribute('image-path', document.editImageUrl)

        this.dialog?.showModal()
    }
}
