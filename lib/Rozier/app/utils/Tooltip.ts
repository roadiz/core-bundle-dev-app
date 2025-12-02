import {
    Popover,
    type PopoverOptions,
    ATTRIBUTES_OPTIONS_MAP as POPOVER_ATTRIBUTES_OPTIONS_MAP,
} from '~/utils/Popover'
import { uniqueId } from 'lodash'

export const ATTRIBUTES_OPTIONS_MAP = {
    text: 'tooltip-text',
    ...POPOVER_ATTRIBUTES_OPTIONS_MAP,
} as const
export const ATTRIBUTES_OPTIONS = Object.values(ATTRIBUTES_OPTIONS_MAP)

type TooltipOptions = PopoverOptions

export class Tooltip {
    popoverInstance: Popover | null = null

    constructor(context: HTMLElement, options?: TooltipOptions) {
        // Set default options
        if (!context.hasAttribute(ATTRIBUTES_OPTIONS_MAP.placement)) {
            context.setAttribute(ATTRIBUTES_OPTIONS_MAP.placement, 'top')
        }

        if (!context.hasAttribute(ATTRIBUTES_OPTIONS_MAP.offset)) {
            context.setAttribute(ATTRIBUTES_OPTIONS_MAP.offset, '4')
        }

        // Define target element and set attributes
        const targetElement: HTMLElement | null =
            context.querySelector('[popovertarget]') || context

        const popoverId: string | null =
            targetElement?.getAttribute('popovertarget') ||
            uniqueId('rz-tooltip-')

        if (!targetElement?.hasAttribute('popovertarget')) {
            targetElement.setAttribute('popovertarget', popoverId)
        }

        // Create popover element if not present
        const popoverElement = context.querySelector('[popover]')
        const textContent = context.getAttribute(ATTRIBUTES_OPTIONS_MAP.text)
        if (textContent && !popoverElement) {
            const generatedTooltip = this.createTooltipElement(textContent)
            generatedTooltip.id = popoverId
            context.appendChild(generatedTooltip)
        }

        this.popoverInstance = new Popover(context, options)

        this.open = this.open.bind(this)
        this.close = this.close.bind(this)
        this.initListeners()
    }

    createTooltipElement(text: string) {
        const generatedTooltip = document.createElement('div')
        generatedTooltip.classList.add('rz-tooltip__content')
        generatedTooltip.setAttribute('popover', 'hint')
        generatedTooltip.setAttribute('role', 'tooltip')
        generatedTooltip.textContent = text

        return generatedTooltip
    }

    updateOptions() {
        this.popoverInstance?.updateOptions()
    }

    close() {
        // Popover Class will sync popover state and visibility
        this.popoverInstance?.popoverElement?.hidePopover()
    }

    open() {
        // Popover Class will sync popover state and visibility
        this.popoverInstance?.popoverElement?.showPopover()
    }

    initListeners() {
        const target = this.popoverInstance?.targetElement
        target?.addEventListener('mouseenter', this.open)
        target?.addEventListener('focus', this.open)
        target?.addEventListener('mouseleave', this.close)
        target?.addEventListener('blur', this.close)
    }

    destroy() {
        const target = this.popoverInstance?.targetElement
        target?.removeEventListener('mouseenter', this.open)
        target?.removeEventListener('focus', this.open)
        target?.removeEventListener('mouseleave', this.close)
        target?.removeEventListener('blur', this.close)

        this.popoverInstance?.destroy()
        this.popoverInstance = null
    }
}
