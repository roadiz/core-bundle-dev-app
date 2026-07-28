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
        this.tooltip?.update()
    }

    connectedCallback() {
        if (this.tooltip) {
            return
        }

        if (getTooltipContent(this)) {
            this.tooltip = new Tooltip(this)
        }
    }

    disconnectedCallback() {
        this.tooltip?.dispose()
        this.tooltip = null
    }
}
