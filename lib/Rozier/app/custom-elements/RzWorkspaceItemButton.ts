export default class RzWorkspaceItemButton extends HTMLButtonElement {
    COMPONENT_CLASS = 'rz-workspace-item'

    constructor() {
        super()
    }

    onClick(event: Event) {
        const el = event.currentTarget as HTMLButtonElement
        if (!el) return

        const expanded = el.getAttribute('aria-expanded') === 'true'
        el.setAttribute('aria-expanded', expanded ? 'false' : 'true')
        el.classList.toggle(`${this.COMPONENT_CLASS}--active`, !expanded)
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
