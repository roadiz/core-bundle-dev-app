// src/components/my-dropdown.js
import {
    computePosition,
    offset,
    flip,
    shift,
    autoUpdate,
} from '@floating-ui/dom'
import type { Placement } from '@floating-ui/dom'

export class RzPopover extends HTMLElement {
    target: HTMLElement | null = null
    floatingElement: HTMLElement | null = null
    cleanupAutoUpdate: (() => void) | null = null

    constructor() {
        super()
    }

    static get observedAttributes() {
        return ['popover-placement', 'popover-offset', 'popover-shift']
    }

    attributeChangedCallback() {
        this.stopFloating()
        this.startFloating()
    }

    connectedCallback() {
        this.target = this.querySelector('[popovertarget]')
        if (!this.target) {
            console.error('RzPopover: Missing popovertarget element')
            return
        }
        const popoverId = this.target?.getAttribute('popovertarget') || null

        this.floatingElement = this.querySelector(`#${popoverId}`)
        if (!this.floatingElement) {
            console.error(
                'RzPopover: Missing popover panel element with id',
                popoverId,
            )
            return
        }

        this.startFloating()
    }

    disconnectedCallback() {
        this.stopFloating()
    }

    startFloating() {
        if (!this.target || !this.floatingElement) return

        this.cleanupAutoUpdate = autoUpdate(
            this.target,
            this.floatingElement,
            () => this.updatePosition(),
        )
    }

    stopFloating() {
        if (!this.cleanupAutoUpdate) return

        this.cleanupAutoUpdate()
        this.cleanupAutoUpdate = null
    }

    async updatePosition() {
        const placement = (this.getAttribute('popover-placement') ||
            'bottom-start') as Placement

        const offsetValue = parseInt(
            this.getAttribute('popover-offset') || '0',
            10,
        )
        const shiftValue = parseInt(
            this.getAttribute('popover-shift') || '0',
            10,
        )
        const { x, y } = await computePosition(
            this.target,
            this.floatingElement,
            {
                placement,
                middleware: [
                    flip(),
                    offset(offsetValue),
                    shift({
                        crossAxis: true,
                        padding: shiftValue,
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
