export default class RzWorkspaceItemButton extends HTMLButtonElement {
    COMPONENT_CLASS_NAME = 'rz-workspace-item'

    constructor() {
        super()
    }

    onClick(event: Event) {
        const el = event.currentTarget
        if (!el || !(el instanceof HTMLButtonElement)) return

        const expanded = el.getAttribute('aria-expanded') === 'true'
        el.setAttribute('aria-expanded', expanded ? 'false' : 'true')
        el.classList.toggle(`${this.COMPONENT_CLASS_NAME}--active`, !expanded)
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
