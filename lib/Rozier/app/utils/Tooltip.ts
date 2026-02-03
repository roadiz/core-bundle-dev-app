import { ATTRIBUTES_OPTIONS_MAP as POPOVER_ATTRIBUTES_OPTIONS_MAP } from '~/utils/Popover'
import {
    computePosition,
    offset,
    flip,
    shift,
    autoUpdate,
} from '@floating-ui/dom'
import type { Placement } from '@floating-ui/dom'
export const ATTRIBUTES_OPTIONS_MAP = {
    text: 'tooltip-text',
    ...POPOVER_ATTRIBUTES_OPTIONS_MAP,
} as const

export const ATTRIBUTES_OPTIONS = Object.values(ATTRIBUTES_OPTIONS_MAP)

export function getTooltipContent(element: HTMLElement) {
    return (
        element.getAttribute(ATTRIBUTES_OPTIONS_MAP.text) ||
        element.getAttribute('title')
    )
}

export class Tooltip {
    tooltip: HTMLElement | null = null
    tooltipClass = 'rz-tooltip__content'
    context: HTMLElement | null = null
    initialized = false
    private cleanupAutoUpdate: (() => void) | null = null

    static get observedAttributes() {
        return [...ATTRIBUTES_OPTIONS]
    }

    update() {
        if (!this.textContent) return

        if (this.tooltip) {
            this.tooltip.textContent = this.textContent
        } else if (!this.initialized) {
            this.init()
        }
    }

    get textContent() {
        return getTooltipContent(this.context)
    }

    get options() {
        const placement =
            this.context.getAttribute(ATTRIBUTES_OPTIONS_MAP.placement) || 'top'
        const shift =
            this.context.getAttribute(ATTRIBUTES_OPTIONS_MAP.shift) || '4'
        const offset =
            this.context.getAttribute(ATTRIBUTES_OPTIONS_MAP.offset) || '4'

        return {
            placement: placement as Placement,
            shift: parseInt(shift.toString(), 10),
            offset: parseInt(offset.toString(), 10),
        }
    }

    constructor(context: HTMLElement) {
        this.context = context
        this.open = this.open.bind(this)
        this.close = this.close.bind(this)

        if (!this.textContent) return
        this.init()
    }

    init() {
        const content = this.textContent
        if (!content || this.initialized) return

        if (this.context.hasAttribute('title')) {
            this.context.removeAttribute('title')

            // If title is removed, we need to set aria-label for accessibility
            // Use aria-label for non interactive element (like badge)
            if (!this.context.hasAttribute('aria-label')) {
                this.context.setAttribute('aria-label', content)
            }
        }

        this.tooltip = document.createElement('div')
        this.tooltip.classList.add(this.tooltipClass)
        this.tooltip.setAttribute('role', 'tooltip')
        this.tooltip.textContent = content
        this.context.appendChild(this.tooltip)

        this.initListeners()
    }

    open() {
        this.tooltip?.classList.add(`${this.tooltipClass}--visible`)
        this.cleanupAutoUpdate = autoUpdate(this.context, this.tooltip, () =>
            this.updatePosition(),
        )
    }

    private async updatePosition() {
        if (!this.context || !this.tooltip) return

        const { x, y } = await computePosition(this.context, this.tooltip, {
            // strategy: 'fixed',
            placement: this.options.placement,
            middleware: [
                flip(),
                offset(this.options.offset),
                shift({
                    crossAxis: true,
                    padding: this.options.shift,
                }),
            ],
        })

        Object.assign(this.tooltip.style, {
            left: `${x}px`,
            top: `${y}px`,
        })
    }

    close() {
        this.tooltip?.classList.remove(`${this.tooltipClass}--visible`)
        this.cleanupAutoUpdate?.()
        this.cleanupAutoUpdate = null
    }

    initListeners() {
        this.context?.addEventListener('mouseenter', this.open)
        this.context?.addEventListener('focus', this.open)
        this.context?.addEventListener('mouseleave', this.close)
        this.context?.addEventListener('blur', this.close)
        this.initialized = true
    }

    disposeListeners() {
        this.context?.removeEventListener('mouseenter', this.open)
        this.context?.removeEventListener('focus', this.open)
        this.context?.removeEventListener('mouseleave', this.close)
        this.context?.removeEventListener('blur', this.close)
        this.initialized = false
    }
}
