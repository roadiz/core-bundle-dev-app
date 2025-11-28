import { Popover, ATTRIBUTES_OPTIONS } from '~/utils/Popover'

export class RzPopover extends HTMLElement {
    popoverInstance: Popover | null = null
    toggle: (() => void) | null = null

    constructor() {
        super()
    }

    static get observedAttributes() {
        return [...ATTRIBUTES_OPTIONS]
    }

    attributeChangedCallback() {
        if (!this.popoverInstance?.targetElement) return

        this.popoverInstance.clear()
        this.popoverInstance.updateAttributesOptions(this)
    }

    connectedCallback() {
        const targetElement = this.querySelector('[popovertarget]')

        const popoverId = targetElement?.getAttribute('popovertarget') || null
        const popoverElement =
            popoverId && document.querySelector(`#${popoverId}`)

        if (
            targetElement instanceof HTMLElement === false ||
            popoverElement instanceof HTMLElement === false
        ) {
            console.error('RzPopover: Missing popover elements')
            return
        }

        this.popoverInstance = new Popover({ targetElement, popoverElement })
        this.popoverInstance.updateAttributesOptions(this)

        if (!this.toggle) {
            this.toggle = this.popoverInstance.toggle.bind(this.popoverInstance)
        }
        popoverElement.addEventListener('beforetoggle', this.toggle)
    }

    disconnectedCallback() {
        this.popoverInstance.popoverElement?.removeEventListener(
            'beforetoggle',
            this.toggle,
        )
        this.toggle = null

        this.popoverInstance?.clear()
        this.popoverInstance = null
    }
}
