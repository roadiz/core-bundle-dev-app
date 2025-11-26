import {
    Popover,
    type PopoverOptions,
    ATTRIBUTES_OPTIONS,
} from '~/utils/popover'
import type { Placement } from '@floating-ui/dom'

export class RzTooltip extends HTMLElement {
    floatingInstance: Popover | null = null

    constructor() {
        super()

        this.showTooltip = this.showTooltip.bind(this)
        this.hideTooltip = this.hideTooltip.bind(this)
    }

    static get observedAttributes() {
        return [...ATTRIBUTES_OPTIONS]
    }

    attributeChangedCallback(name: string) {
        if (!this.floatingInstance) return

        if (name === 'data-popover-shift') {
            this.floatingInstance.shift = parseInt(
                this.getAttribute('data-popover-shift') || '0',
                10,
            )
        }
        if (name === 'data-popover-offset') {
            this.floatingInstance.offset = parseInt(
                this.getAttribute('data-popover-offset') || '0',
                10,
            )
        }
        if (name === 'data-popover-placement') {
            this.floatingInstance.placement =
                (this.getAttribute('data-popover-placement') as Placement) ||
                'top'
        }
        console.log('RzTooltip attributeChangedCallback', this.floatingInstance)
    }

    getTargetElement() {
        return this.floatingInstance?.targetElement
    }

    getFloatingElement() {
        return this.floatingInstance?.floatingElement
    }

    showTooltip() {
        this.floatingInstance?.init()
        this.getFloatingElement()?.showPopover()
    }

    hideTooltip() {
        this.floatingInstance?.clear()
        this.getFloatingElement()?.hidePopover()
    }

    getPopoverOptions() {
        const options: PopoverOptions = {
            placement: this.getAttribute('data-popover-placement') as Placement,
            offset:
                Number(this.getAttribute('data-popover-offset')) || undefined,
            shift: Number(this.getAttribute('data-popover-shift')) || undefined,
        }

        // If data-popover-text is set, create tooltip element dynamically
        const rawTextContent = this.getAttribute('data-popover-text')

        if (rawTextContent) {
            const generatedTooltip = document.createElement('div')
            generatedTooltip.setAttribute('popover', 'hint')
            generatedTooltip.textContent = rawTextContent
            this.appendChild(generatedTooltip)

            Object.assign(options, {
                targetElement: this,
                floatingElement: generatedTooltip,
            })
        } else {
            // Otherwise, expect the user to provide the tooltip element in the DOM with dedicated popover api attributes
            Object.assign(options, {
                targetElement: this.querySelector('[popovertarget]'),
                floatingElement: this.querySelector('[popover]'),
            })
        }

        if (
            options.targetElement instanceof HTMLElement === false ||
            options.floatingElement instanceof HTMLElement === false
        ) {
            console.error(
                'RzTooltip: Missing target or tooltip element for tooltip',
            )
            return
        }

        return options
    }

    connectedCallback() {
        const options = this.getPopoverOptions()
        if (!options) return

        this.floatingInstance = new Popover(options)

        const targetElement = this.getTargetElement()
        targetElement?.addEventListener('mouseenter', this.showTooltip)
        targetElement?.addEventListener('mouseleave', this.hideTooltip)
    }

    disconnectedCallback() {
        console.log('RzTooltip disconnected from the DOM', this)
        const targetElement = this.getTargetElement()

        targetElement?.removeEventListener('mouseenter', this.showTooltip)
        targetElement?.removeEventListener('mouseleave', this.hideTooltip)

        this.floatingInstance?.clear()
        this.floatingInstance = null
    }
}
