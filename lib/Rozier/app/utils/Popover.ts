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
    placement: 'popover-placement',
    offset: 'popover-offset',
    shift: 'popover-shift',
} as const

export const ATTRIBUTES_OPTIONS = Object.values(ATTRIBUTES_OPTIONS_MAP)

export type PopoverOptions = {
    targetElement?: HTMLElement | null
    popoverElement?: HTMLElement | null
    placement?: Placement
    offset?: string | number
    shift?: string | number
    autoInit?: boolean
}

export class Popover {
    context: HTMLElement
    targetElement?: HTMLElement | null
    popoverElement?: HTMLElement | null
    placement: Placement = 'bottom-start'
    offset: number = 0
    shift: number = 0

    isFloating = false
    private cleanupAutoUpdate: (() => void) | null = null

    constructor(context: HTMLElement, options?: PopoverOptions) {
        this.context = context
        this.targetElement =
            options?.targetElement ||
            context.querySelector('[popovertarget]') ||
            context

        this.popoverElement =
            options?.popoverElement || context.querySelector('[popover]')

        this.placement = options?.placement || 'bottom-start'
        this.offset =
            (typeof options?.offset === 'string'
                ? parseInt(options.offset)
                : options?.offset) || 0
        this.shift =
            (typeof options?.shift === 'string'
                ? parseInt(options.shift)
                : options?.shift) || 0

        this.toggle = this.toggle.bind(this)

        if (options?.autoInit || options?.autoInit === undefined) {
            this.init()
        }
    }

    init() {
        this.updateOptions()
        this.popoverElement.addEventListener('beforetoggle', this.toggle)
    }

    destroy() {
        this.popoverElement?.removeEventListener('beforetoggle', this.toggle)
        this.close()
    }

    open() {
        if (!this.targetElement || !this.popoverElement) return
        this.isFloating = true

        this.cleanupAutoUpdate = autoUpdate(
            this.targetElement,
            this.popoverElement,
            () => this.updatePosition(),
        )
    }

    close() {
        if (!this.isFloating) return
        this.isFloating = false

        this.cleanupAutoUpdate?.()
        this.cleanupAutoUpdate = null
    }

    toggle() {
        if (this.isFloating) {
            this.close()
        } else {
            this.open()
        }
    }

    getOptions() {
        const shift = this.context?.getAttribute(ATTRIBUTES_OPTIONS_MAP.shift)
        const offset = this.context?.getAttribute(ATTRIBUTES_OPTIONS_MAP.offset)
        const placement = this.context?.getAttribute(
            ATTRIBUTES_OPTIONS_MAP.placement,
        ) as Placement

        return {
            placement: placement || this.placement,
            offset: offset ? parseInt(offset) : this.offset,
            shift: shift ? parseInt(shift) : this.shift,
        }
    }

    updateOptions() {
        const { offset, placement, shift } = this.getOptions()

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
        if (!this.targetElement || !this.popoverElement) return

        const { x, y } = await computePosition(
            this.targetElement,
            this.popoverElement,
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

        Object.assign(this.popoverElement.style, {
            left: `${x}px`,
            top: `${y}px`,
        })
    }
}
