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

export const ATTRIBUTES_OPTIONS = [
    'data-popover-position',
    'data-popover-offset',
    'data-popover-shift',
]

// export const ATTRIBUTES_OPTIONS_MAP = {
//     position: 'data-popover-position',
//     offset: 'data-popover-offset',
//     shift: 'data-popover-shift',
// }

export type PopoverOptions = {
    targetElement?: HTMLElement | null
    floatingElement?: HTMLElement | null
    placement?: Placement
    offset?: number
    shift?: number
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
        this.offset = options.offset || 0
        this.shift = options.shift || 0
    }

    init() {
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
        if (this.cleanupAutoUpdate) {
            this.clear()
        } else {
            this.init()
        }
    }

    reset() {
        if (!this.isFloating) return

        this.clear()
        this.init()
    }

    private async updatePosition() {
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
