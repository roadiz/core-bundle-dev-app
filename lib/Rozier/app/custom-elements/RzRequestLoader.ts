export const ACTIVE_CLASS_NAME = 'rz-request-loader--active'

/**
 * Global loader shown while an AJAX navigation or a long action runs.
 * Any module drives it through the `requestLoaderShow` / `requestLoaderHide` window events.
 */
export default class RzRequestLoader extends HTMLElement {
    constructor() {
        super()

        this.show = this.show.bind(this)
        this.hide = this.hide.bind(this)
    }

    get labelElement() {
        return this.querySelector<HTMLElement>('.rz-request-loader__label')
    }

    connectedCallback() {
        window.addEventListener('requestLoaderShow', this.show)
        window.addEventListener('requestLoaderHide', this.hide)
    }

    disconnectedCallback() {
        window.removeEventListener('requestLoaderShow', this.show)
        window.removeEventListener('requestLoaderHide', this.hide)
    }

    show() {
        this.classList.add(ACTIVE_CLASS_NAME)

        // The status region announces text inserted while it is in the DOM
        const label = this.labelElement
        if (label && !label.textContent) {
            label.textContent = label.dataset.label || ''
        }
    }

    hide() {
        this.classList.remove(ACTIVE_CLASS_NAME)

        const label = this.labelElement
        if (label) {
            label.textContent = ''
        }
    }
}
