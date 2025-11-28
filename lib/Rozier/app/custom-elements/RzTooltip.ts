import {
    Popover,
    ATTRIBUTES_OPTIONS,
    ATTRIBUTES_OPTIONS_MAP,
} from '~/utils/Popover'
import type { Placement } from '@floating-ui/dom'

export class RzTooltip extends HTMLElement {
    popoverInstance: Popover | null = null

    constructor() {
        super()

        this.showTooltip = this.showTooltip.bind(this)
        this.hideTooltip = this.hideTooltip.bind(this)
    }

    static get observedAttributes() {
        return [...ATTRIBUTES_OPTIONS]
    }

    attributeChangedCallback() {
        if (!this.popoverInstance) return

        this.popoverInstance.clear()
        this.popoverInstance.updateAttributesOptions(this)
    }

    showTooltip() {
        this.popoverInstance?.init()
        // Manually show the popover for native popover behavior
        this.getTooltipElement()?.showPopover()
    }

    hideTooltip() {
        this.popoverInstance?.clear()
        this.getTooltipElement()?.hidePopover()
    }

    /*
     * Creates a tooltip element if not present in the DOM,
     * using the data-popover-text attribute as content.
     */
    createTooltipElement(rawTextContent: string) {
        const generatedTooltip = document.createElement('div')
        generatedTooltip.classList.add('rz-tooltip__content')
        generatedTooltip.setAttribute('popover', 'hint')
        generatedTooltip.setAttribute('role', 'tooltip')
        generatedTooltip.textContent = rawTextContent
        return generatedTooltip
    }

    getTargetElement() {
        return (
            this.popoverInstance?.targetElement ||
            this.querySelector('button[popovertarget]') ||
            this
        )
    }

    getTooltipElement() {
        return (
            this.popoverInstance?.popoverElement ||
            this.querySelector('[popover]')
        )
    }

    connectedCallback() {
        const targetElement = this.getTargetElement()
        if (targetElement instanceof HTMLElement === false) {
            console.error('RzTooltip: Missing target element')
            return
        }

        let tooltipElement = this.getTooltipElement()
        const rawTextContent = this.getAttribute('data-popover-text')

        if (!tooltipElement && rawTextContent) {
            tooltipElement = this.createTooltipElement(rawTextContent)
            targetElement.appendChild(tooltipElement)
        }

        this.popoverInstance = new Popover({
            targetElement,
            popoverElement: tooltipElement,
            shift: this.getAttribute(ATTRIBUTES_OPTIONS_MAP.shift),
            offset: this.getAttribute(ATTRIBUTES_OPTIONS_MAP.offset) || '4px',
            placement:
                (this.getAttribute(
                    ATTRIBUTES_OPTIONS_MAP.placement,
                ) as Placement) || 'top',
        })

        targetElement.addEventListener('mouseenter', this.showTooltip)
        targetElement.addEventListener('focus', this.showTooltip)
        targetElement.addEventListener('mouseleave', this.hideTooltip)
        targetElement.addEventListener('blur', this.hideTooltip)
    }

    disconnectedCallback() {
        const targetElement = this.getTargetElement()
        targetElement?.removeEventListener('mouseenter', this.showTooltip)
        targetElement?.removeEventListener('focus', this.showTooltip)
        targetElement?.removeEventListener('mouseleave', this.hideTooltip)
        targetElement?.removeEventListener('blur', this.hideTooltip)

        this.popoverInstance?.clear()
        this.popoverInstance = null
    }
}
