import RoadizElement from '~/utils/custom-element/RoadizElement'
import { rzButtonRenderer } from '~/utils/component-renderer/rzButton'

export default class DocumentEditDialog extends RoadizElement {
    // Constants
    static INPUT_BASE_NAME = 'document_edit_dialog[proxy]'
    static INPUT_KEYS = ['imageCropAlignment', 'hotspot']

    static CSS_SELECTORS = {
        DIALOG_CLOSE: '.rz-dialog__close',
        DIALOG_ACTIONS: '.rz-dialog__footer > button',
        DIALOG_CONTENT: '.rz-dialog__content',
        ALIGNMENT_WIDGET: 'document-alignment-widget',
    }

    // DOM element references
    dialog = null

    connectedCallback() {
        this.createDialog()
        this.setupEventListeners()
        this.appendToDOM()
        this.loadTemplate()

        if (this.hasAttribute('open')) {
            this.dialog.showModal()
        }
    }

    /**
     * Create the dialog element with its HTML structure
     */
    createDialog() {
        this.dialog = document.createElement('dialog', { is: 'rz-dialog' })
        this.dialog.classList.add('rz-dialog')
        this.dialog.setAttribute('closedby', 'any')
        this.dialog.innerHTML = this.getDialogHTML()
    }

    /**
     * Generate the dialog HTML content
     * @returns {string} The dialog HTML
     */

    getBodyStyle() {
        let style = ''
        const imageWidth = this.getAttribute('image-width')
        if (imageWidth) {
            style += `min-width: min(${imageWidth}px, 90vw);`
        }

        const imageHeight = this.getAttribute('image-height')
        if (imageHeight) {
            style += `min-height: min(${imageHeight}px, 90vh);`
        }

        return style
    }

    getDialogHTML() {
        const { INPUT_BASE_NAME, INPUT_KEYS } = DocumentEditDialog

        return `
            <header class="rz-dialog__header">
                <span class="rz-dialog__icon rz-icon-ri--edit-line"></span>
                <h1 class="rz-dialog__title">${this.getAttribute('title')}</h1>
                ${
                    rzButtonRenderer({
                        iconClass: 'rz-icon-ri--close-line',
                        emphasis: 'tertiary',
                        attributes: {
                            class: 'rz-dialog__close',
                            type: 'button',
                            value: 'cancel',
                            closetarget: '',
                        },
                    }).outerHTML
                }
            </header>
            <div class="rz-dialog__body" style="${this.getBodyStyle()}">
                <div class="rz-dialog__content">
                    <div class="rz-spinner rz-spinner--lg"></div>
                </div>
            </div>
            ${INPUT_KEYS.map((key) => `<input type="hidden" name="${INPUT_BASE_NAME}[${key}]">`).join('\n')}
            <footer class="rz-dialog__footer">
                ${this.getEditLink()}
                ${
                    rzButtonRenderer({
                        iconClass: 'rz-icon-ri--close-line',
                        label: window.RozierConfig?.messages
                            ?.documentEditDialogCancel,
                        attributes: {
                            type: 'button',
                            value: 'cancel',
                        },
                    }).outerHTML
                }
                ${
                    rzButtonRenderer({
                        label: window.RozierConfig?.messages
                            ?.documentEditDialogSubmit,
                        emphasis: 'primary',
                        iconClass: 'rz-icon-ri--check-line',
                        attributes: {
                            type: 'button',
                            value: 'submit',
                        },
                    }).outerHTML
                }
            </footer>
        `
    }

    /**
     * Generate the edit link HTML
     * @returns {string} The edit link HTML or empty string
     */
    getEditLink() {
        const editUrl = this.getAttribute('edit-url')

        if (!editUrl) return ''

        return `
            <a is="rz-link" href="${editUrl}" class="rz-dialog__push-right">
                ${window.RozierConfig?.messages?.documentEditDialogEdit}
            </a>
        `
    }

    /**
     * Setup all event listeners
     */
    setupEventListeners() {
        const { CSS_SELECTORS } = DocumentEditDialog
        const buttons = this.dialog?.querySelectorAll(
            `${CSS_SELECTORS.DIALOG_CLOSE}, ${CSS_SELECTORS.DIALOG_ACTIONS}`,
        )

        if (buttons) this.listen(buttons, 'click', this.onButtonClick)
        this.listen(this.dialog, 'close', this.onDialogClose)
    }

    /**
     * Append the dialog to the DOM
     */
    appendToDOM() {
        this.appendChild(this.dialog)
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
                console.error(
                    'DocumentEditDialog: Failed to load template:',
                    error,
                )
            })
    }

    /**
     * Fetch the template from the server
     * @param {string} templatePath - Path to the template
     * @returns {Promise<string>} Promise resolving to HTML content
     */
    async fetchTemplate(templatePath) {
        const response = await fetch(templatePath, {
            headers: {
                // Required to prevent using this route as referer when login again
                'X-Requested-With': 'XMLHttpRequest',
            },
        })

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

        return tempContainer.querySelector(
            DocumentEditDialog.CSS_SELECTORS.ALIGNMENT_WIDGET,
        )
    }

    /**
     * Setup the alignment widget with required attributes
     * @param {HTMLElement|null} widget - The widget element
     * @returns {HTMLElement} The container with the widget
     */
    setupWidget(widget) {
        const { INPUT_BASE_NAME } = DocumentEditDialog

        if (widget) {
            this.applyInputValues(
                this.getAttribute('input-base-name'),
                INPUT_BASE_NAME,
                document,
                this.dialog,
            )

            widget.setAttribute('image-path', this.getAttribute('image-path'))
            widget.setAttribute('image-width', this.getAttribute('image-width'))
            widget.setAttribute(
                'image-height',
                this.getAttribute('image-height'),
            )
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
        const content = this.dialog.querySelector(
            DocumentEditDialog.CSS_SELECTORS.DIALOG_CONTENT,
        )
        content.replaceChildren(...container.childNodes)
    }

    /**
     * Apply input values between source and destination elements
     * @param {string} sourceInputBaseName - Source input base name
     * @param {string} destInputBaseName - Destination input base name
     * @param {HTMLElement} sourceElement - Source element to copy values from
     * @param {HTMLElement} destElement - Destination element to copy values to
     */
    applyInputValues(
        sourceInputBaseName,
        destInputBaseName,
        sourceElement,
        destElement,
    ) {
        const { INPUT_KEYS } = DocumentEditDialog

        INPUT_KEYS.forEach((key) => {
            const sourceInput = sourceElement.querySelector(
                `input[name="${sourceInputBaseName}[${key}]"]`,
            )

            if (sourceInput) {
                const destInput = destElement.querySelector(
                    `input[name="${destInputBaseName}[${key}]"]`,
                )

                if (destInput) {
                    destInput.value = sourceInput.value
                }
            }
        })
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
        const buttonvalue = event.currentTarget.value || event.target.value
        if (buttonvalue === 'cancel' || buttonvalue === 'submit') {
            this.dialog.close(buttonvalue)
        }
    }

    /**
     * Handle dialog close events
     */
    onDialogClose() {
        const { INPUT_BASE_NAME } = DocumentEditDialog

        if (this.dialog.returnValue === 'submit') {
            this.applyInputValues(
                INPUT_BASE_NAME,
                this.getAttribute('input-base-name'),
                this.dialog,
                document,
            )
        }

        this.remove()
    }
}
