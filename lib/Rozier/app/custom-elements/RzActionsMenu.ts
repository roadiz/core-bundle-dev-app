type ActionsMenuAxis = 'y' | 'xy'
type ActionsMenuEdge = 'right' | 'left'

type ActionsMenuPosition = {
    top: number
    left?: number
    right?: number
    axis: ActionsMenuAxis
    edge: ActionsMenuEdge
}

export default class RzActionsMenu extends HTMLElement {
    private static readonly storageKey = 'rz-actions-menu-position'
    private handle: HTMLElement | null
    private isDragging: boolean
    private startPointerY: number
    private startPointerX: number
    private startTop: number
    private startLeft: number
    private axis: ActionsMenuAxis
    private edge: ActionsMenuEdge
    private pointerId: number | null

    constructor() {
        super()
        this.handle = null
        this.isDragging = false
        this.startPointerY = 0
        this.startPointerX = 0
        this.startTop = 0
        this.startLeft = 0
        this.axis = 'y'
        this.edge = 'right'
        this.pointerId = null

        this.onPointerDown = this.onPointerDown.bind(this)
        this.onPointerMove = this.onPointerMove.bind(this)
        this.onPointerUp = this.onPointerUp.bind(this)
        this.onResize = this.onResize.bind(this)
    }

    connectedCallback() {
        this.handle = this.querySelector('.rz-actions-menu__handle')
        if (!this.handle) {
            return
        }

        this.axis = this.getAxisAttribute()
        this.applyInitialPosition()
        this.handle.addEventListener('pointerdown', this.onPointerDown)
        window.addEventListener('resize', this.onResize)
    }

    disconnectedCallback() {
        this.handle?.removeEventListener('pointerdown', this.onPointerDown)
        window.removeEventListener('resize', this.onResize)
        this.removeDragListeners()
    }

    private onPointerDown(event: PointerEvent) {
        if (event.button !== 0) {
            return
        }

        this.isDragging = true
        this.pointerId = event.pointerId
        this.startPointerY = event.clientY
        this.startPointerX = event.clientX
        this.startTop = this.getCurrentTop()
        this.startLeft = this.getCurrentLeft()

        this.handle?.setPointerCapture(event.pointerId)
        document.body.style.userSelect = 'none'
        this.addDragListeners()
    }

    private onPointerMove(event: PointerEvent) {
        if (!this.isDragging || this.pointerId !== event.pointerId) {
            return
        }

        const nextTop = this.getClampedTop(
            this.startTop + (event.clientY - this.startPointerY),
        )
        const position: ActionsMenuPosition = {
            top: nextTop,
            axis: this.axis,
            edge: this.edge,
        }

        if (this.axis === 'xy') {
            const nextLeft = this.getClampedLeft(
                this.startLeft + (event.clientX - this.startPointerX),
            )
            position.left = nextLeft
            position.edge = 'left'
        }

        this.applyPosition(position)
    }

    private onPointerUp(event: PointerEvent) {
        if (this.pointerId !== event.pointerId) {
            return
        }

        this.isDragging = false
        this.pointerId = null
        this.handle?.releasePointerCapture(event.pointerId)
        document.body.style.userSelect = ''
        this.removeDragListeners()
        this.persistPosition()
    }

    private onResize() {
        const clampedTop = this.getClampedTop(this.getCurrentTop())
        this.applyPosition({
            top: clampedTop,
            axis: this.axis,
            edge: this.edge,
        })
    }

    private addDragListeners() {
        window.addEventListener('pointermove', this.onPointerMove)
        window.addEventListener('pointerup', this.onPointerUp)
        window.addEventListener('pointercancel', this.onPointerUp)
    }

    private removeDragListeners() {
        window.removeEventListener('pointermove', this.onPointerMove)
        window.removeEventListener('pointerup', this.onPointerUp)
        window.removeEventListener('pointercancel', this.onPointerUp)
    }

    private applyInitialPosition() {
        const saved = this.getPersistedPosition()
        if (saved) {
            const top = this.getClampedTop(saved.top)
            const position: ActionsMenuPosition = {
                top,
                axis: this.axis,
                edge: saved.edge,
            }

            if (this.axis === 'xy' && typeof saved.left === 'number') {
                position.left = this.getClampedLeft(saved.left)
            }

            this.applyPosition(position)
            return
        }

        const top = this.getClampedTop(this.getCurrentTop())
        this.applyPosition({
            top,
            axis: this.axis,
            edge: this.edge,
        })
    }

    private applyPosition(position: ActionsMenuPosition) {
        this.style.transform = 'none'
        this.style.top = `${position.top}px`
        if (position.edge === 'left') {
            this.style.left =
                typeof position.left === 'number' ? `${position.left}px` : '0px'
            this.style.right = ''
        } else {
            const rightFallback = this.getDefaultRight()
            this.style.right =
                typeof position.right === 'number'
                    ? `${position.right}px`
                    : `${rightFallback}px`
            this.style.left = ''
        }

        this.dataset.edge = position.edge
    }

    private getCurrentTop() {
        const topValue = window.getComputedStyle(this).top
        if (topValue && topValue !== 'auto' && topValue.endsWith('px')) {
            const parsed = Number.parseFloat(topValue)
            if (!Number.isNaN(parsed)) {
                return parsed
            }
        }

        const rect = this.getBoundingClientRect()
        return rect.top
    }

    private getCurrentLeft() {
        const leftValue = window.getComputedStyle(this).left
        if (leftValue && leftValue !== 'auto' && leftValue.endsWith('px')) {
            const parsed = Number.parseFloat(leftValue)
            if (!Number.isNaN(parsed)) {
                return parsed
            }
        }

        const rect = this.getBoundingClientRect()
        return rect.left
    }

    private getDefaultRight() {
        const rightValue = window.getComputedStyle(this).right
        if (rightValue && rightValue !== 'auto' && rightValue.endsWith('px')) {
            const parsed = Number.parseFloat(rightValue)
            if (!Number.isNaN(parsed)) {
                return parsed
            }
        }

        return 0
    }

    private getAxisAttribute(): ActionsMenuAxis {
        const axis = this.getAttribute('data-axis')
        return axis === 'xy' ? 'xy' : 'y'
    }

    private getClampedTop(value: number) {
        const rect = this.getBoundingClientRect()
        const maxTop = Math.max(0, window.innerHeight - rect.height)
        return Math.min(Math.max(0, value), maxTop)
    }

    private getClampedLeft(value: number) {
        const rect = this.getBoundingClientRect()
        const maxLeft = Math.max(0, window.innerWidth - rect.width)
        return Math.min(Math.max(0, value), maxLeft)
    }

    private persistPosition() {
        const position: ActionsMenuPosition = {
            top: this.getCurrentTop(),
            axis: this.axis,
            edge: this.dataset.edge === 'left' ? 'left' : 'right',
        }

        if (this.axis === 'xy') {
            position.left = this.getCurrentLeft()
        }

        try {
            window.localStorage.setItem(
                RzActionsMenu.storageKey,
                JSON.stringify(position),
            )
        } catch (error) {
            console.warn('RzActionsMenu: Unable to persist position.', error)
        }
    }

    private getPersistedPosition(): ActionsMenuPosition | null {
        try {
            const raw = window.localStorage.getItem(RzActionsMenu.storageKey)
            if (!raw) {
                return null
            }

            const parsed = JSON.parse(raw) as Partial<ActionsMenuPosition>
            if (typeof parsed.top !== 'number') {
                return null
            }

            return {
                top: parsed.top,
                left: parsed.left,
                right: parsed.right,
                axis: parsed.axis === 'xy' ? 'xy' : 'y',
                edge: parsed.edge === 'left' ? 'left' : 'right',
            }
        } catch (error) {
            console.warn('RzActionsMenu: Unable to read position.', error)
            return null
        }
    }
}
