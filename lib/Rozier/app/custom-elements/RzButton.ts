import { Tooltip, ATTRIBUTES_OPTIONS, getTooltipContent } from '~/utils/Tooltip'

export class RzButton extends HTMLButtonElement {
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
        if (getTooltipContent(this)) {
            this.tooltip = new Tooltip(this)
        }
    }

    disconnectedCallback() {
        this.tooltip?.destroy()
        this.tooltip = null
    }
}
