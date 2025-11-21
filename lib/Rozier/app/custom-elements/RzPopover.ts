import {
    computePosition,
    offset,
    flip,
    shift,
    autoUpdate,
} from '@floating-ui/dom'
import type { Placement } from '@floating-ui/dom'

export class RzPopover extends HTMLElement {
    isFloating = false
    targetElement: HTMLElement | null = null
    floatingElement: HTMLElement | null = null
    cleanupAutoUpdate: (() => void) | null = null

    constructor() {
        super()

        this.onBeforeToggle = this.onBeforeToggle.bind(this)
    }

    static get observedAttributes() {
        return ['popover-placement', 'popover-offset', 'popover-shift']
    }

    attributeChangedCallback() {
        if (!this.isFloating) return
        this.stopFloating()
        this.startFloating()
    }

    connectedCallback() {
        this.targetElement = this.querySelector('[popovertarget]')
        if (!this.targetElement) {
            console.error('RzPopover: Missing popovertarget element')
            return
        }
        const popoverId =
            this.targetElement?.getAttribute('popovertarget') || null

        this.floatingElement = this.querySelector(`#${popoverId}`)
        if (!this.floatingElement) {
            console.error(
                'RzPopover: Missing popover panel element with id',
                popoverId,
            )
            return
        }

        this.floatingElement.addEventListener(
            'beforetoggle',
            this.onBeforeToggle,
        )
    }

    onBeforeToggle() {
        if (this.cleanupAutoUpdate) {
            this.stopFloating()
        } else {
            this.startFloating()
        }
    }

    disconnectedCallback() {
        this.floatingElement.removeEventListener(
            'beforetoggle',
            this.onBeforeToggle,
        )
        this.stopFloating()
    }

    startFloating() {
        if (!this.targetElement || !this.floatingElement || this.isFloating) {
            return
        }

        this.isFloating = true
        this.cleanupAutoUpdate = autoUpdate(
            this.targetElement,
            this.floatingElement,
            () => this.updatePosition(),
        )
    }

    stopFloating() {
        if (!this.isFloating) return

        this.isFloating = false
        this.cleanupAutoUpdate?.()
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
            this.targetElement,
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
