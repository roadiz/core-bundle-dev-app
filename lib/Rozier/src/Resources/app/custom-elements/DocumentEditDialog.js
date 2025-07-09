export default class DocumentEditDialog extends HTMLElement {
    static observedAttributes = ['image-path', 'input-base-name']

    constructor() {
        super()
    }

    connectedCallback() {
        // Dialog (root element)
        this.dialog = document.createElement('dialog')
        this.dialog.classList.add('document-edit-dialog')
        this.dialog.innerHTML = `
            <header>
            </header>
            <div class="document-edit-dialog__content"></div>
            <input type="hidden" data-proxy-imageCropAlignment />
            <input type="hidden" data-proxy-hotspot />
            <footer>
                <div class="document-edit-dialog__actions"> 
                    <button class="uk-button uk-button-small document-edit-dialog__cancel" type="button">Annuler</button>
                    <button class="uk-button uk-button-small document-edit-dialog__submit" type="button">Appliquer</button>
                </div>
            </footer>
        `

        // Widget
        this.widget = this.dialog.querySelector('document-alignment-widget')

        this.addEventListener('click', (event) => {
            if (event.target.classList.contains('document-edit-dialog__cancel')) {
                this.dialog.close()
            }
        })

        this.dialog.addEventListener('close', () => {
            this.remove()
        })

        this.appendChild(this.dialog)

        fetch('/rz-admin/documents/alignment-template').then(async (response) => {
            if (!response.ok) {
                throw new Error('Network response was not ok')
            }

            const content = this.dialog.querySelector('.document-edit-dialog__content')

            if (content) content.innerHTML = await response.text()

            this.widget = this.dialog.querySelector('document-alignment-widget')

            this.updateWidgetImagePath()
            this.updateWidgetInputBaseName()
        })
    }

    disconnectedCallback() { }

    attributeChangedCallback(name) {
        if (name === 'image-path') {
            this.updateWidgetImagePath()
        }
    }

    updateWidgetImagePath() {
        if (!this.widget) return

        const imagePath = this.getAttribute('image-path')

        this.widget.setAttribute('image-path', imagePath)
    }

    updateWidgetInputBaseName() {
        if (!this.widget) return

        const inputBaseName = this.getAttribute('input-base-name')

        this.widget.setAttribute('input-base-name', inputBaseName)
    }

    showModal() {
        this.dialog?.showModal()
    }
}
