export default class RzTreeItem extends HTMLLIElement {
    EXPAND_BUTTON_CLASS_NAME = 'rz-tree__item__expand-button'
    nodeElement: HTMLElement | null = null
    expandButtonElement: HTMLButtonElement | null = null

    constructor() {
        super()

        this.onExpandButtonClick = this.onExpandButtonClick.bind(this)
    }

    connectedCallback() {
        this.expandButtonElement = this.querySelector(
            `.${this.EXPAND_BUTTON_CLASS_NAME}`,
        )
        this.nodeElement = this.querySelector(`[role="treeitem"]`)

        if (this.expandButtonElement) {
            this.expandButtonElement.addEventListener(
                'click',
                this.onExpandButtonClick,
            )
        }
    }

    disconnectedCallback() {
        this.expandButtonElement?.removeEventListener(
            'click',
            this.onExpandButtonClick,
        )
    }

    onExpandButtonClick() {
        if (!this.nodeElement) return

        const expanded =
            this.nodeElement.getAttribute('aria-expanded') === 'true'

        this.nodeElement.setAttribute(
            'aria-expanded',
            expanded ? 'false' : 'true',
        )
    }
}
