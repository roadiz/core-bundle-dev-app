import { Tooltip, ATTRIBUTES_OPTIONS, getTooltipContent } from '~/utils/Tooltip'

export class RzBadge extends HTMLElement {
    tooltip: Tooltip | null = null

    constructor() {
        super()
    }

    static get observedAttributes() {
        return [...ATTRIBUTES_OPTIONS]
    }

    attributeChangedCallback() {
        this.tooltip?.update()
    }

    connectedCallback() {
        if (getTooltipContent(this)) {
            this.tooltip = new Tooltip(this)
        }
    }

    disconnectedCallback() {
        this.tooltip?.disposeListeners()
        this.tooltip = null
    }
}
