import { Popover, ATTRIBUTES_OPTIONS } from '~/utils/Popover'

export class RzPopover extends HTMLElement {
    popoverInstance: Popover | null = null

    constructor() {
        super()
    }

    static get observedAttributes() {
        return [...ATTRIBUTES_OPTIONS]
    }

    attributeChangedCallback() {
        this.popoverInstance?.updateOptions()
    }

    connectedCallback() {
        this.popoverInstance = new Popover(this)
        this.popoverInstance.init()
    }

    disconnectedCallback() {
        this.popoverInstance?.destroy()
        this.popoverInstance = null
    }
}
