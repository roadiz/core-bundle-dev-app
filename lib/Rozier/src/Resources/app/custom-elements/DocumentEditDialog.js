export default class DocumentEditDialog extends HTMLElement {
    // Constants
    static INPUT_BASE_NAME = 'document_edit_dialog[proxy]'
    static INPUT_KEYS = ['imageCropAlignment', 'hotspot']

    static CSS_SELECTORS = {
        DIALOG_CLOSE: '.document-edit-dialog__close',
        DIALOG_ACTIONS: '.document-edit-dialog__actions > button',
        DIALOG_CONTENT: '.document-edit-dialog__content',
        ALIGNMENT_WIDGET: 'document-alignment-widget',
    }

    // DOM element references
    dialog = null

    // Event handlers (bound once for performance)
    boundHandlers = {}

    // Lifecycle methods
    constructor() {
        super()

        // Bind event handlers for performance
        this.boundHandlers = {
            buttonClick: this.onButtonClick.bind(this),
            dialogClose: this.onDialogClose.bind(this),
        }
    }

    connectedCallback() {
        this.createDialog()
        this.setupEventListeners()
        this.appendToDOM()
        this.loadTemplate()
    }

    disconnectedCallback() {
        this.cleanup()
    }

    /**
     * Create the dialog element with its HTML structure
     */
    createDialog() {
        this.dialog = document.createElement('dialog')
        this.dialog.setAttribute('closedby', 'any')
        this.dialog.classList.add('uk-form', 'document-edit-dialog')
        this.dialog.innerHTML = this.getDialogHTML()
    }

    /**
     * Generate the dialog HTML content
     * @returns {string} The dialog HTML
     */
    getDialogHTML() {
        const { INPUT_BASE_NAME, INPUT_KEYS } = DocumentEditDialog

        return `
            <header class="document-edit-dialog__header">
                <h2 class="document-edit-dialog__title">
                    <i class="uk-icon-image"></i>
                    <span>${this.getAttribute('title')}</span>
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
                </div>
            </footer>
        `
    }

    /**
     * Setup all event listeners
     */
    setupEventListeners() {
        const { CSS_SELECTORS } = DocumentEditDialog

        // Add event listeners for dialog actions
        this.dialog
            .querySelectorAll(`${CSS_SELECTORS.DIALOG_CLOSE}, ${CSS_SELECTORS.DIALOG_ACTIONS}`)
            ?.forEach((button) => {
                button.addEventListener('click', this.boundHandlers.buttonClick)
            })

        this.dialog.addEventListener('close', this.boundHandlers.dialogClose)
    }

    /**
     * Append the dialog to the DOM
     */
    appendToDOM() {
        this.appendChild(this.dialog)
    }

    /**
     * Clean up resources and event listeners
     */
    cleanup() {
        const { CSS_SELECTORS } = DocumentEditDialog

        // Remove event listeners
        this.dialog
            ?.querySelectorAll(`${CSS_SELECTORS.DIALOG_CLOSE}, ${CSS_SELECTORS.DIALOG_ACTIONS}`)
            ?.forEach((button) => {
                button.removeEventListener('click', this.boundHandlers.buttonClick)
            })

        this.dialog?.removeEventListener('close', this.boundHandlers.dialogClose)

        // Nullify DOM references to prevent memory leaks
        this.dialog = null
    }

    /**
     * Load and setup the template content
     * @returns {Promise|undefined} Promise if template is loaded, undefined otherwise
     */
    loadTemplate() {
        const templatePath = this.getAttribute('template-path')

        if (!templatePath) return

        return this.fetchTemplate(templatePath)
            .then((html) => this.parseTemplate(html))
            .then((widget) => this.setupWidget(widget))
            .then((content) => this.replaceDialogContent(content))
            .catch((error) => {
                console.error('DocumentEditDialog: Failed to load template:', error)
            })
    }

    /**
     * Fetch the template from the server
     * @param {string} templatePath - Path to the template
     * @returns {Promise<string>} Promise resolving to HTML content
     */
    async fetchTemplate(templatePath) {
        const response = await fetch(templatePath)

        if (!response.ok) {
            throw new Error('Network response was not ok')
        }

        return response.text()
    }

    /**
     * Parse the HTML template and extract the widget
     * @param {string} html - HTML content
     * @returns {HTMLElement|null} The widget element or null
     */
    parseTemplate(html) {
        const tempContainer = document.createElement('div')
        tempContainer.innerHTML = html

        return tempContainer.querySelector(DocumentEditDialog.CSS_SELECTORS.ALIGNMENT_WIDGET)
    }

    /**
     * Setup the alignment widget with required attributes
     * @param {HTMLElement|null} widget - The widget element
     * @returns {HTMLElement} The container with the widget
     */
    setupWidget(widget) {
        const { INPUT_BASE_NAME } = DocumentEditDialog

        if (widget) {
            this.applyInputValues(this.getAttribute('input-base-name'), INPUT_BASE_NAME, document, this.dialog)

            widget.setAttribute('image-path', this.getAttribute('image-path'))
            widget.setAttribute('input-base-name', INPUT_BASE_NAME)
            widget.setAttribute('hotspot-overridable', true)

            const originalHotspot = this.getAttribute('original-hotspot')
            if (originalHotspot) {
                widget.setAttribute('original-hotspot', originalHotspot)
            }
        }

        return widget?.parentNode || document.createElement('div')
    }

    /**
     * Replace the dialog content with the new content
     * @param {HTMLElement} container - Container with the new content
     */
    replaceDialogContent(container) {
        const content = this.dialog.querySelector(DocumentEditDialog.CSS_SELECTORS.DIALOG_CONTENT)
        content.replaceChildren(...container.childNodes)
    }

    /**
     * Apply input values between source and destination elements
     * @param {string} sourceInputBaseName - Source input base name
     * @param {string} destInputBaseName - Destination input base name
     * @param {HTMLElement} sourceElement - Source element to copy values from
     * @param {HTMLElement} destElement - Destination element to copy values to
     */
    applyInputValues(sourceInputBaseName, destInputBaseName, sourceElement, destElement) {
        const { INPUT_KEYS } = DocumentEditDialog

        INPUT_KEYS.forEach((key) => {
            const sourceInput = sourceElement.querySelector(`input[name="${sourceInputBaseName}[${key}]"]`)

            if (sourceInput) {
                const destInput = destElement.querySelector(`input[name="${destInputBaseName}[${key}]"]`)

                if (destInput) {
                    destInput.value = sourceInput.value
                }
            }
        })
    }

    /**
     * Generate the edit link HTML
     * @returns {string} The edit link HTML or empty string
     */
    getEditLink() {
        const editUrl = this.getAttribute('edit-url')

        if (!editUrl) return ''

        return `
            <a class="uk-button uk-button-small document-edit-dialog__edit-link" href="${editUrl}">
                ${window.RozierConfig?.messages?.documentEditDialogEdit}
            </a>
        `
    }

    /**
     * Show the modal dialog
     */
    showModal() {
        this.dialog?.showModal()
    }

    // Event handlers
    /**
     * Handle button click events
     * @param {Event} event - Click event
     */
    onButtonClick(event) {
        if (event.currentTarget.value === 'cancel' || event.target.value === 'submit') {
            this.dialog.close(event.target.value)
        }
    }

    /**
     * Handle dialog close events
     */
    onDialogClose() {
        const { INPUT_BASE_NAME } = DocumentEditDialog

        if (this.dialog.returnValue === 'submit') {
            this.applyInputValues(INPUT_BASE_NAME, this.getAttribute('input-base-name'), this.dialog, document)
        }

        this.remove()
    }
}
