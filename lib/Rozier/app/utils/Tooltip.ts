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

// TODO: Performance Improvement
// The constructor calls init() which creates the tooltip and attaches listeners immediately.
// This means tooltips are always initialized eagerly even if they may never be shown.
// Consider lazy initialization (only creating the tooltip on first hover/focus)
// to improve performance when many tooltips exist on a page.
export class Tooltip {
    tooltip: HTMLElement | null = null
    tooltipClass = 'rz-tooltip__content'
    context: HTMLElement | null = null

    private cleanupAutoUpdate: (() => void) | null = null

    static get observedAttributes() {
        return [...ATTRIBUTES_OPTIONS]
    }

    update() {
        if (!this.textContent) return

        if (this.tooltip) {
            this.tooltip.textContent = this.textContent
        } else {
            this.disposeListeners()
            this.init()
        }
    }

    get textContent() {
        return getTooltipContent(this.context)
    }

    get options() {
        const placement =
            this.context.getAttribute(ATTRIBUTES_OPTIONS_MAP.placement) || 'top'
        const shiftValue =
            this.context.getAttribute(ATTRIBUTES_OPTIONS_MAP.shift) || '4'
        const offsetValue =
            this.context.getAttribute(ATTRIBUTES_OPTIONS_MAP.offset) || '4'

        return {
            placement: placement as Placement,
            shift: parseInt(shiftValue.toString(), 10),
            offset: parseInt(offsetValue.toString(), 10),
        }
    }

    constructor(context: HTMLElement) {
        this.context = context
        this.open = this.open.bind(this)
        this.close = this.close.bind(this)

        this.init()
    }

    init() {
        const content = this.textContent
        if (!content) return

        if (this.context.hasAttribute('title')) {
            this.context.removeAttribute('title')

            // If title is removed, we need to set aria-label for accessibility
            // Use aria-label for non interactive element (like badge)
            if (!this.context.hasAttribute('aria-label')) {
                this.context.setAttribute('aria-label', content)
            }
        }

        if (this.tooltip) {
            this.tooltip.remove()
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
        this.cleanupAutoUpdate?.()
        this.cleanupAutoUpdate = autoUpdate(this.context, this.tooltip, () =>
            this.updatePosition(),
        )
    }

    close() {
        this.tooltip?.classList.remove(`${this.tooltipClass}--visible`)
        this.cleanupAutoUpdate?.()
        this.cleanupAutoUpdate = null
    }

    private async updatePosition() {
        if (!this.context || !this.tooltip) return

        const { x, y } = await computePosition(this.context, this.tooltip, {
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

    initListeners() {
        if (!this.context) return
        this.context.addEventListener('mouseenter', this.open)
        this.context.addEventListener('focus', this.open)
        this.context.addEventListener('mouseleave', this.close)
        this.context.addEventListener('blur', this.close)
    }

    disposeListeners() {
        if (!this.context) return
        this.context.removeEventListener('mouseenter', this.open)
        this.context.removeEventListener('focus', this.open)
        this.context.removeEventListener('mouseleave', this.close)
        this.context.removeEventListener('blur', this.close)
    }

    dispose() {
        this.close()
        this.disposeListeners()
        this.tooltip?.remove()
        this.tooltip = null
    }
}
