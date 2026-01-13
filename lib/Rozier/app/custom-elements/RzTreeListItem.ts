export default class RzTreeListItem extends HTMLLIElement {
    EXPAND_BUTTON_CLASS_NAME = 'rz-tree__expand-button'
    expandButton: HTMLButtonElement | null = null

    constructor() {
        super()
        this.onExpandButtonClick = this.onExpandButtonClick.bind(this)
    }

    onExpandButtonClick() {
        const el = this.expandButton
        if (!el) return

        const expanded = el.getAttribute('aria-expanded') === 'true'
        el.setAttribute('aria-expanded', expanded ? 'false' : 'true')
        el.classList.toggle(
            `${this.EXPAND_BUTTON_CLASS_NAME}--active`,
            !expanded,
        )
    }

    connectedCallback() {
        this.expandButton =
            this.querySelector(`.${this.EXPAND_BUTTON_CLASS_NAME}`) ||
            this.querySelector('button[aria-expanded]')

        if (this.expandButton) {
            this.expandButton.addEventListener(
                'click',
                this.onExpandButtonClick,
            )
        }
    }

    disconnectedCallback() {
        this.expandButton?.removeEventListener(
            'click',
            this.onExpandButtonClick,
        )
    }
}
