import { Popover, ATTRIBUTES_OPTIONS } from '~/utils/Popover'
import type { OverflowList } from '~/utils/OverflowList'

export class RzPopover extends HTMLElement {
    popoverInstance: Popover | null = null
    overflowList: OverflowList | null = null

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

        // Opt-in, loaded on demand: most popovers never need it.
        if (this.hasAttribute('overflow-responsive')) {
            import('~/utils/OverflowList').then(({ OverflowList }) => {
                if (this.isConnected && !this.overflowList) {
                    this.overflowList = new OverflowList(this)
                }
            })
        }
    }

    disconnectedCallback() {
        this.popoverInstance?.destroy()
        this.popoverInstance = null
        this.overflowList?.destroy()
        this.overflowList = null
    }
}
