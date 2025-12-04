import { Tooltip, ATTRIBUTES_OPTIONS } from '~/utils/Tooltip'

export class RzTooltip extends HTMLElement {
    tooltip: Tooltip | null = null

    constructor() {
        super()
    }

    static get observedAttributes() {
        return [...ATTRIBUTES_OPTIONS]
    }

    attributeChangedCallback() {
        this.tooltip?.updateOptions()
    }

    connectedCallback() {
        this.tooltip = new Tooltip(this)
    }

    disconnectedCallback() {
        this.tooltip?.destroy()
        this.tooltip = null
    }
}
