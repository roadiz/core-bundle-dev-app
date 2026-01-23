type AnchorPosition = 'left' | 'right' | 'top' | 'bottom'

const DRAG_DELAY = 400
const EDGE_SWITCH_THRESHOLD = 24

export default class RzActionsMenu extends HTMLElement {
    private pointerId: number | null = null
    private startX = 0
    private startY = 0
    private anchor: AnchorPosition | null = null
    private longPressTimer: number | null = null
    private isDragging = false
    private grabOffsetX = 0
    private grabOffsetY = 0
    private dragRAF: number | null = null
    private pendingX = 0
    private pendingY = 0
    private viewportWidth = window.innerWidth
    private viewportHeight = window.innerHeight
    private rectWidth = 0
    private rectHeight = 0
    private lastAnchor: AnchorPosition | null = null

    get anchorOffset() {
        return parseInt(this.getAttribute('anchor-offset') || '0', 10)
    }

    constructor() {
        super()

        this.onPointerDown = this.onPointerDown.bind(this)
        this.onResize = this.onResize.bind(this)
    }

    connectedCallback() {
        this.style.position = this.style.position || 'fixed'
        this.style.zIndex = this.style.zIndex || '101'
        this.style.touchAction = this.style.touchAction || 'none'

        const anchorAttr = this.getAttribute('anchor-position')
        if (anchorAttr) this.anchor = anchorAttr as AnchorPosition

        this.anchorToNearestEdge()

        this.addEventListener('pointerdown', this.onPointerDown)
        window.addEventListener('resize', this.onResize)
    }

    disconnectedCallback() {
        this.removeEventListener('pointerdown', this.onPointerDown)
        window.removeEventListener('resize', this.onResize)
    }

    private onResize = () => {
        const vw = window.innerWidth
        const vh = window.innerHeight
        this.viewportWidth = vw
        this.viewportHeight = vh
        const rect = this.getBoundingClientRect()
        this.rectWidth = rect.width
        this.rectHeight = rect.height
        if (!this.anchor) {
            this.anchorToNearestEdge()
            return
        }
        const axisPos =
            this.anchor === 'left' || this.anchor === 'right'
                ? rect.top
                : rect.left
        this.applyEdgePosition(
            this.anchor,
            axisPos,
            this.rectWidth,
            this.rectHeight,
            vw,
            vh,
        )
    }

    private onPointerDown = (e: PointerEvent) => {
        const rect = this.getBoundingClientRect()
        this.anchor = this.computeNearestEdge(rect)

        this.pointerId = e.pointerId
        this.startX = e.clientX
        this.startY = e.clientY
        this.grabOffsetX = e.clientX - rect.left
        this.grabOffsetY = e.clientY - rect.top

        // Defer actual dragging until a long press is detected
        this.longPressTimer = window.setTimeout(() => {
            this.startDragging()
        }, DRAG_DELAY)

        this.addEventListener('pointerup', this.onPointerUp)
        this.addEventListener('pointercancel', this.onPointerUp)
    }

    private startDragging() {
        if (this.pointerId === null || this.isDragging === true) return

        this.isDragging = true
        this.setPointerCapture(this.pointerId)

        const rect = this.getBoundingClientRect()
        const vw = window.innerWidth
        const vh = window.innerHeight
        this.viewportWidth = vw
        this.viewportHeight = vh
        this.rectWidth = rect.width
        this.rectHeight = rect.height
        this.style.willChange = 'top, left'
        const axisPos =
            this.anchor === 'left' || this.anchor === 'right'
                ? rect.top
                : rect.left
        this.applyEdgePosition(
            this.anchor!,
            axisPos,
            this.rectWidth,
            this.rectHeight,
            vw,
            vh,
        )

        this.addEventListener('pointermove', this.onPointerMove, {
            passive: true,
        })
    }

    private onPointerMove = (e: PointerEvent) => {
        if (this.pointerId === null || e.pointerId !== this.pointerId) return
        if (!this.isDragging) return

        this.pendingX = e.clientX
        this.pendingY = e.clientY
        if (this.dragRAF === null) {
            this.dragRAF = window.requestAnimationFrame(this.applyDragFrame)
        }
    }

    private applyDragFrame = () => {
        this.dragRAF = null
        const vw = this.viewportWidth
        const vh = this.viewportHeight
        // Dynamically pick the nearest edge to the cursor with hysteresis
        const newAnchor = this.pickNearestEdgeWithHysteresis(
            this.pendingX,
            this.pendingY,
        )
        this.anchor = newAnchor
        const desiredPos =
            newAnchor === 'left' || newAnchor === 'right'
                ? this.pendingY - this.grabOffsetY
                : this.pendingX - this.grabOffsetX
        this.applyEdgePosition(
            newAnchor,
            desiredPos,
            this.rectWidth,
            this.rectHeight,
            vw,
            vh,
        )
    }

    private setDirectionClass() {
        if (this.anchor === 'left' || this.anchor === 'right') {
            this.classList.add('rz-actions-menu--vertical')
        } else {
            this.classList.remove('rz-actions-menu--vertical')
        }
    }

    private setInnerPopoversPlacement() {
        const popovers = this.querySelectorAll('rz-popover')
        if (!popovers.length) return

        popovers.forEach((popover) => {
            if (this.anchor === 'left') {
                popover.setAttribute('popover-placement', 'right-center')
            } else if (this.anchor === 'top') {
                popover.setAttribute('popover-placement', 'bottom-center')
            } else if (this.anchor === 'bottom') {
                popover.setAttribute('popover-placement', 'top-center')
            } else {
                popover.setAttribute('popover-placement', 'left-center')
            }
        })
    }

    private onPointerUp = (e: PointerEvent) => {
        if (this.pointerId === null || e.pointerId !== this.pointerId) return

        // If long press hasn't triggered yet, cancel drag start
        if (!this.isDragging) {
            if (this.longPressTimer !== null) {
                window.clearTimeout(this.longPressTimer)
                this.longPressTimer = null
            }
        } else {
            this.releasePointerCapture(this.pointerId)
            this.removeEventListener('pointermove', this.onPointerMove)
        }

        if (this.dragRAF !== null) {
            window.cancelAnimationFrame(this.dragRAF)
            this.dragRAF = null
        }
        this.style.willChange = ''

        this.setAttribute('anchor-position', this.anchor)

        this.isDragging = false
        this.pointerId = null
        this.removeEventListener('pointerup', this.onPointerUp)
        this.removeEventListener('pointercancel', this.onPointerUp)
    }

    private anchorToNearestEdge() {
        const rect = this.getBoundingClientRect()
        this.anchor = this.computeNearestEdge(rect)
        const vw = window.innerWidth
        const vh = window.innerHeight
        this.viewportWidth = vw
        this.viewportHeight = vh
        this.rectWidth = rect.width
        this.rectHeight = rect.height
        const axisPos =
            this.anchor === 'left' || this.anchor === 'right'
                ? rect.top
                : rect.left
        this.applyEdgePosition(
            this.anchor,
            axisPos,
            this.rectWidth,
            this.rectHeight,
            vw,
            vh,
        )
    }

    private computeNearestEdge(
        rect: DOMRect,
    ): 'left' | 'right' | 'top' | 'bottom' {
        const vw = window.innerWidth
        const vh = window.innerHeight
        const distLeft = rect.left
        const distRight = vw - rect.right
        const distTop = rect.top
        const distBottom = vh - rect.bottom

        const min = Math.min(distLeft, distRight, distTop, distBottom)
        if (min === distLeft) return 'left'
        if (min === distRight) return 'right'
        if (min === distTop) return 'top'
        return 'bottom'
    }

    private pickNearestEdgeWithHysteresis(
        x: number,
        y: number,
    ): 'left' | 'right' | 'top' | 'bottom' {
        const vw = window.innerWidth
        const vh = window.innerHeight

        const dist = {
            left: x,
            right: vw - x,
            top: y,
            bottom: vh - y,
        } as const

        let candidate: 'left' | 'right' | 'top' | 'bottom' = 'left'
        let min = dist.left
        if (dist.right < min) {
            min = dist.right
            candidate = 'right'
        }
        if (dist.top < min) {
            min = dist.top
            candidate = 'top'
        }
        if (dist.bottom < min) {
            min = dist.bottom
            candidate = 'bottom'
        }

        if (this.anchor) {
            const currentDist = dist[this.anchor]
            // Switch only if the candidate is sufficiently closer than current
            if (currentDist - min > EDGE_SWITCH_THRESHOLD) {
                return candidate
            }
            return this.anchor
        }

        return candidate
    }

    private applyEdgePosition(
        anchor: 'left' | 'right' | 'top' | 'bottom',
        axisPos: number,
        rectWidth: number,
        rectHeight: number,
        vw: number,
        vh: number,
    ): void {
        if (anchor !== this.lastAnchor && this.isDragging) {
            this.setDirectionClass()
            this.setInnerPopoversPlacement()
            this.lastAnchor = anchor
        }

        if (anchor === 'left') {
            const newTop = Math.max(0, Math.min(axisPos, vh - rectHeight))
            this.style.top = `${newTop}px`
            this.style.left = `${this.anchorOffset}px`
            this.style.right = 'auto'
            this.style.bottom = 'auto'
        } else if (anchor === 'right') {
            const newTop = Math.max(0, Math.min(axisPos, vh - rectHeight))
            this.style.top = `${newTop}px`
            this.style.right = `${this.anchorOffset}px`
            this.style.left = 'auto'
            this.style.bottom = 'auto'
        } else if (anchor === 'top') {
            const newLeft = Math.max(0, Math.min(axisPos, vw - rectWidth))
            this.style.left = `${newLeft}px`
            this.style.top = `${this.anchorOffset}px`
            this.style.right = 'auto'
            this.style.bottom = 'auto'
        } else {
            const newLeft = Math.max(0, Math.min(axisPos, vw - rectWidth))
            this.style.left = `${newLeft}px`
            this.style.bottom = `${this.anchorOffset}px`
            this.style.right = 'auto'
            this.style.top = 'auto'
        }
    }
}
