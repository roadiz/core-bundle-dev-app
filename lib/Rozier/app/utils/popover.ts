import {
    computePosition,
    offset,
    flip,
    shift,
    autoUpdate,
} from '@floating-ui/dom'
import type { Placement } from '@floating-ui/dom'

export const POPOVER_PLACEMENTS: Placement[] = [
    'top',
    'right',
    'bottom',
    'left',
    'top-start',
    'top-end',
    'right-start',
    'right-end',
    'bottom-start',
    'bottom-end',
    'left-start',
    'left-end',
]

export const ATTRIBUTES_OPTIONS_MAP = {
    placement: 'data-popover-placement',
    offset: 'data-popover-offset',
    shift: 'data-popover-shift',
} as const

export const ATTRIBUTES_OPTIONS = Object.values(ATTRIBUTES_OPTIONS_MAP)

export type PopoverOptions = {
    targetElement?: HTMLElement | null
    floatingElement?: HTMLElement | null
    placement?: Placement
    offset?: string | number
    shift?: string | number
}

export class Popover {
    targetElement?: HTMLElement | null
    floatingElement?: HTMLElement | null
    placement: Placement = 'bottom-start'
    offset: number = 0
    shift: number = 0

    isFloating = false
    private cleanupAutoUpdate: (() => void) | null = null

    constructor(options: PopoverOptions) {
        this.targetElement = options.targetElement || null
        this.floatingElement = options.floatingElement || null
        this.placement = options.placement || 'bottom-start'
        this.offset = Number(options.offset) || 0
        this.shift = Number(options.shift) || 0
    }

    init() {
        if (!this.targetElement || !this.floatingElement) return
        this.isFloating = true

        this.cleanupAutoUpdate = autoUpdate(
            this.targetElement,
            this.floatingElement,
            () => this.updatePosition(),
        )
    }

    clear() {
        if (!this.isFloating) return

        this.isFloating = false
        this.cleanupAutoUpdate?.()
        this.cleanupAutoUpdate = null
    }

    toggle() {
        if (this.isFloating) {
            this.clear()
        } else {
            this.init()
        }
    }

    getAttributesOptions(element: HTMLElement | undefined) {
        const shift = element?.getAttribute(ATTRIBUTES_OPTIONS_MAP.shift)
        const offset = element?.getAttribute(ATTRIBUTES_OPTIONS_MAP.offset)
        const placement = element?.getAttribute(
            ATTRIBUTES_OPTIONS_MAP.placement,
        ) as Placement

        return {
            placement: placement || this.placement,
            offset: offset ? parseInt(offset) : this.offset,
            shift: shift ? parseInt(shift) : this.shift,
        }
    }

    updateAttributesOptions(element: HTMLElement) {
        const { offset, placement, shift } = this.getAttributesOptions(element)

        if (this.placement !== placement) {
            this.placement = placement
        }

        if (this.offset !== offset) {
            this.offset = offset
        }

        if (this.shift !== shift) {
            this.shift = shift
        }
    }

    private async updatePosition() {
        if (!this.targetElement || !this.floatingElement) return

        const { x, y } = await computePosition(
            this.targetElement,
            this.floatingElement,
            {
                placement: this.placement,
                middleware: [
                    flip(),
                    offset(this.offset),
                    shift({
                        crossAxis: true,
                        padding: this.shift,
                    }),
                ],
            },
        )

        Object.assign(this.floatingElement.style, {
            left: `${x}px`,
            top: `${y}px`,
        })
    }
}
