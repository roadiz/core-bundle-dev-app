export default class RzHeaderNavItemButton extends HTMLButtonElement {
    COMPONENT_CLASS_NAME = 'rz-header-nav-item'

    constructor() {
        super()

        this.expand = this.expand.bind(this)
        this.collapse = this.collapse.bind(this)
        this.onClick = this.onClick.bind(this)
    }

    expand() {
        this.setAttribute('aria-expanded', 'true')
        this.classList.add(`${this.COMPONENT_CLASS_NAME}--active`)
    }

    collapse() {
        this.setAttribute('aria-expanded', 'false')
        this.classList.remove(`${this.COMPONENT_CLASS_NAME}--active`)
    }

    onClick(event: Event) {
        const el = event.currentTarget
        if (!el || !(el instanceof HTMLButtonElement)) return

        if (el.getAttribute('aria-expanded') === 'true') {
            this.collapse()
        } else {
            this.expand()
        }
    }

    connectedCallback() {
        if (!this.hasAttribute('aria-expanded')) {
            this.setAttribute('aria-expanded', 'false')
        }
        this.addEventListener('click', this.onClick)
    }

    disconnectedCallback() {
        this.removeEventListener('click', this.onClick)
    }
}
