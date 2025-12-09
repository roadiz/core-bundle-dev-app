import {
    Tooltip,
    ATTRIBUTES_OPTIONS,
    ATTRIBUTES_OPTIONS_MAP,
} from '~/utils/Tooltip'

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
        if (this.hasAttribute(ATTRIBUTES_OPTIONS_MAP.text)) {
            this.tooltip = new Tooltip(this)
        }
    }

    disconnectedCallback() {
        this.tooltip?.destroy()
        this.tooltip = null
    }
}
