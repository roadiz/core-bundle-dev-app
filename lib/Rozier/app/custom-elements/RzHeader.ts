import RzHeaderNavItemButton from './RzHeaderNavItemButton'

export class RzHHeader extends HTMLElement {
    constructor() {
        super()
        this.onMouseLeave = this.onMouseLeave.bind(this)
        this.closeAllCollapsibles = this.closeAllCollapsibles.bind(this)
    }

    get collapsibleButtons(): RzHeaderNavItemButton[] {
        return Array.from(
            this.querySelectorAll('button[is="rz-header-nav-item-button"]'),
        )
    }

    onMouseLeave() {
        this.closeAllCollapsibles()
    }

    onKeyUp(event: KeyboardEvent) {
        if (event.key === 'Escape' || event.key === 'Esc') {
            this.closeAllCollapsibles()
        }
    }

    closeAllCollapsibles() {
        this.collapsibleButtons.forEach((button) => {
            button.collapse()
        })
    }

    connectedCallback() {
        this.addEventListener('mouseleave', this.onMouseLeave)
        this.addEventListener('keyup', this.onKeyUp)
        window.addEventListener('pageshowend', this.closeAllCollapsibles)
    }

    disconnectedCallback() {
        this.removeEventListener('mouseleave', this.onMouseLeave)
        this.removeEventListener('keyup', this.onKeyUp)
        window.removeEventListener('pageshowend', this.closeAllCollapsibles)
    }
}
