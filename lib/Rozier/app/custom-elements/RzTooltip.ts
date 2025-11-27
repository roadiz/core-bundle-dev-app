import {
    Popover,
    ATTRIBUTES_OPTIONS,
    ATTRIBUTES_OPTIONS_MAP,
} from '~/utils/popover'
import type { Placement } from '@floating-ui/dom'

export class RzTooltip extends HTMLElement {
    targetElement: HTMLElement | null = null
    tooltipElement: HTMLElement | null = null
    floatingInstance: Popover | null = null

    constructor() {
        super()

        this.showTooltip = this.showTooltip.bind(this)
        this.hideTooltip = this.hideTooltip.bind(this)
    }

    static get observedAttributes() {
        return [...ATTRIBUTES_OPTIONS]
    }

    attributeChangedCallback() {
        if (!this.floatingInstance || !this.targetElement) return
        this.floatingInstance.clear()
        this.floatingInstance.updateAttributesOptions(this.targetElement)
    }

    showTooltip() {
        this.floatingInstance?.init()
        // Manually show the popover for native popover behavior
        this.tooltipElement?.showPopover()
    }

    hideTooltip() {
        this.floatingInstance?.clear()
        this.tooltipElement?.hidePopover()
    }

    createPopoverContent(rawTextContent: string) {
        const generatedTooltip = document.createElement('div')
        generatedTooltip.classList.add('rz-tooltip__content')
        generatedTooltip.setAttribute('popover', 'hint')
        generatedTooltip.setAttribute('role', 'tooltip')
        generatedTooltip.textContent = rawTextContent
        return generatedTooltip
    }

    initPopoverElements() {
        const targetElement =
            this.querySelector('button[popovertarget]') || this

        if (targetElement instanceof HTMLElement) {
            this.targetElement = targetElement
        }

        if (
            targetElement instanceof HTMLButtonElement === false &&
            !targetElement.hasAttribute('tabindex')
        ) {
            this.targetElement.setAttribute('tabindex', '0')
        }

        // If data-popover-text is set, create tooltip element dynamically
        const tooltipElement = this.querySelector('[popover]')
        const rawTextContent = this.getAttribute('data-popover-text')

        if (tooltipElement instanceof HTMLElement) {
            this.tooltipElement = tooltipElement
        } else if (rawTextContent) {
            const generatedTooltip = this.createPopoverContent(rawTextContent)
            targetElement.appendChild(generatedTooltip)
            this.tooltipElement = generatedTooltip
        }
    }

    connectedCallback() {
        this.initPopoverElements()

        if (!this.targetElement || !this.tooltipElement) return

        this.floatingInstance = new Popover({
            targetElement: this.targetElement,
            floatingElement: this.tooltipElement,
            shift: this.targetElement?.getAttribute(
                ATTRIBUTES_OPTIONS_MAP.shift,
            ),
            offset: this.targetElement?.getAttribute(
                ATTRIBUTES_OPTIONS_MAP.offset,
            ),
            placement:
                (this.targetElement?.getAttribute(
                    ATTRIBUTES_OPTIONS_MAP.placement,
                ) as Placement) || 'top',
        })

        this.targetElement.addEventListener('mouseenter', this.showTooltip)
        this.targetElement.addEventListener('focus', this.showTooltip)
        this.targetElement.addEventListener('mouseleave', this.hideTooltip)
        this.targetElement.addEventListener('blur', this.hideTooltip)
    }

    disconnectedCallback() {
        this.targetElement?.removeEventListener('mouseenter', this.showTooltip)
        this.targetElement?.removeEventListener('focus', this.showTooltip)
        this.targetElement?.removeEventListener('mouseleave', this.hideTooltip)
        this.targetElement?.removeEventListener('blur', this.hideTooltip)

        this.floatingInstance?.clear()
        this.floatingInstance = null
    }
}
