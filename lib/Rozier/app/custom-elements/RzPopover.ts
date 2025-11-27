import { Popover, ATTRIBUTES_OPTIONS } from '~/utils/popover'

export class RzPopover extends HTMLElement {
    targetElement: HTMLElement | null = null
    floatingElement: HTMLElement | null = null
    floatingInstance: Popover | null = null
    toggle: (() => void) | null = null
    constructor() {
        super()
    }

    static get observedAttributes() {
        return [...ATTRIBUTES_OPTIONS]
    }

    attributeChangedCallback() {
        if (!this.floatingInstance || !this.targetElement) return
        this.floatingInstance.clear()
        this.floatingInstance.updateAttributesOptions(this.targetElement)
    }

    setElements() {
        this.targetElement = this.querySelector('[popovertarget]')

        const popoverId =
            this.targetElement?.getAttribute('popovertarget') || null
        this.floatingElement = document.querySelector(`#${popoverId}`)
    }

    connectedCallback() {
        this.setElements()

        if (!this.targetElement || !this.floatingElement) {
            console.error('RzPopover: Missing popover elements')
            return
        }

        this.floatingInstance = new Popover({
            targetElement: this.targetElement,
            floatingElement: this.floatingElement,
        })

        this.floatingInstance.updateAttributesOptions(this)

        if (!this.toggle) {
            this.toggle = this.floatingInstance.toggle.bind(
                this.floatingInstance,
            )
        }
        this.floatingElement.addEventListener('beforetoggle', this.toggle)
    }

    disconnectedCallback() {
        this.floatingElement?.removeEventListener('beforetoggle', this.toggle)
        this.toggle = null

        this.floatingInstance?.clear()
        this.floatingInstance = null
    }
}
