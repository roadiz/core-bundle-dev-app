export default class DocumentAlignmentWidget extends HTMLElement {
    static observedAttributes = ['image-path']

    constructor() {
        super()
    }

    connectedCallback() {
        this.init()
        this.updateImagePath()
        this.updateElementsPositionsFromStoredData()
    }

    disconnectedCallback() {
        this.dispose()
    }

    attributeChangedCallback(name) {
        if (name === 'image-path') {
            this.updateImagePath()
        }
    }

    init() {
        this.imageWrapper = this.querySelector('.document-alignment-widget__image-wrapper')
        this.area = this.querySelector('.document-alignment-widget__area')
        this.areaHandles = Array.from(this.querySelectorAll('.document-alignment-widget__area-handle'))
        this.hotspot = this.querySelector('.document-alignment-widget__hotspot')
        this.resetButton = this.querySelector('.document-alignment-widget__reset')

        if (!this.imageWrapper || !this.area || this.areaHandles.length === 0 || !this.hotspot) {
            console.error('DocumentAlignmentWidget: Missing required elements')
            return
        }

        this.areaPointerDownHandler = this.onAreaPointerDown.bind(this)
        this.pointerMoveHandler = this.onPointerMove.bind(this)
        this.pointerUpHandler = this.onPointerUp.bind(this)

        this.resetButton?.addEventListener('click', () => {
            this.setHotspotData(null)
            this.updateElementsPositionsFromStoredData()
        })

        this.area.addEventListener('pointerdown', this.areaPointerDownHandler, { passive: false })
    }

    dispose() {
        this.area.removeEventListener('pointerdown', this.areaPointerDownHandler)

        this.imageWrapper = null
        this.area = null
        this.areaHandles = null
        this.hotspot = null
    }

    updateImagePath() {
        const image = this.querySelector('.document-alignment-widget__image')

        if (!image) return

        const imagePath = this.getAttribute('image-path')

        if (imagePath && image.src !== imagePath) {
            image.src = imagePath
        }
    }

    updateElementsPositionsFromStoredData() {
        const storedData = this.getStoredHotspotData()

        this.setAreaPosition(storedData)
        this.setHotspotPosition(storedData)

        this.toggleResetButtonVisibility(!!storedData)
    }

    toggleResetButtonVisibility(isVisible) {
        this.resetButton.classList.toggle('document-alignment-widget__reset--visible', isVisible)
    }

    getFormInput(id) {
        const name = this.getAttribute('input-base-name')

        return document.querySelector(`input[name="${name}[${id}]"]`)
    }

    moveArea({ x, y, width, height }) {
        // Limit area to imageWrapper bounds and normalize values if imageWrapper exists
        const wrapperRect = this.imageWrapper.getBoundingClientRect()
        const maxLeft = 0
        const maxTop = 0
        const maxWidth = wrapperRect.width
        const maxHeight = wrapperRect.height

        // Clamp values
        let clampedX = Math.max(maxLeft, Math.min(x, maxWidth - width))
        let clampedY = Math.max(maxTop, Math.min(y, maxHeight - height))
        let clampedWidth = width
        let clampedHeight = height

        // Clamp width and height so area stays inside wrapper
        if (clampedX + clampedWidth > maxWidth) {
            clampedWidth = maxWidth - clampedX
        }
        if (clampedY + clampedHeight > maxHeight) {
            clampedHeight = maxHeight - clampedY
        }

        // Normalize values to be between 0 and 1 relative to imageWrapper size
        const normLeft = clampedX / wrapperRect.width
        const normTop = clampedY / wrapperRect.height
        const normWidth = clampedWidth / wrapperRect.width
        const normHeight = clampedHeight / wrapperRect.height

        const data = {
            areaStartX: normLeft,
            areaStartY: normTop,
            areaEndX: normLeft + normWidth,
            areaEndY: normTop + normHeight,
        }

        this.setHotspotData(data)
        this.toggleResetButtonVisibility(true)
        this.setAreaPosition(data)
    }

    setAreaPosition(value) {
        let inset = ''

        if (value) {
            const { areaStartX, areaStartY, areaEndX, areaEndY } = value

            const top = (areaStartY || 0) * 100
            const right = (1 - (areaEndX || 1)) * 100
            const bottom = (1 - (areaEndY || 1)) * 100
            const left = (areaStartX || 0) * 100

            inset = `${top}% ${right}% ${bottom}% ${left}%`
        }

        this.area.style.inset = inset
    }

    getHotspotFromImageCropAlignment(imageCropAlignment) {
        switch (imageCropAlignment) {
            case 'top-left':
                return { x: 0, y: 0 }
            case 'top':
                return { x: 0.5, y: 0 }
            case 'top-right':
                return { x: 1, y: 0 }
            case 'left':
                return { x: 0, y: 0.5 }
            case 'center':
                return { x: 0.5, y: 0.5 }
            case 'right':
                return { x: 1, y: 0.5 }
            case 'bottom-left':
                return { x: 0, y: 1 }
            case 'bottom':
                return { x: 0.5, y: 1 }
            case 'bottom-right':
                return { x: 1, y: 1 }
        }
    }

    moveHotspot(clientX, clientY) {
        const rect = this.area.getBoundingClientRect()

        let x = (clientX - rect.left) / rect.width
        let y = (clientY - rect.top) / rect.height

        // Clamp values between 0 and 1
        x = Math.max(0, Math.min(1, x))
        y = Math.max(0, Math.min(1, y))

        // Round to four decimal places
        x = Math.round(x * 10000) / 10000
        y = Math.round(y * 10000) / 10000

        const data = { x, y }

        this.setHotspotData(data)
        this.toggleResetButtonVisibility(true)
        this.setHotspotPosition(data)
    }

    setHotspotPosition(value) {
        this.hotspot.style.top = typeof value?.y === 'number' ? `${value.y * 100}%` : ''
        this.hotspot.style.left = typeof value?.x === 'number' ? `${value.x * 100}%` : ''
    }

    // @param {Object} value
    // @param {number} value.x - X coordinate (0 to 1)
    // @param {number} value.y - Y coordinate (0 to 1)
    // @param {number} value.areaStartX - Area start X coordinate (0 to 1)
    // @param {number} value.areaStartY - Area start Y coordinate (0 to 1)
    getStoredHotspotData() {
        const hotspot = this.getFormInput('hotspot')?.value

        if (hotspot && hotspot !== 'null') {
            const value = JSON.parse(hotspot)

            if (value && typeof value.x === 'number' && typeof value.y === 'number') {
                return value
            }
        }

        const imageCropAlignment = this.getFormInput('imageCropAlignment')?.value

        if (imageCropAlignment) {
            const value = this.getHotspotFromImageCropAlignment(imageCropAlignment)

            if (value) return value
        }

        // return { x: 0.5, y: 0.5 } // Default to center if no data is available
    }

    // @param {Object} value
    // @param {number} value.x - X coordinate (0 to 1)
    // @param {number} value.y - Y coordinate (0 to 1)
    // @param {number} value.areaStartX - Area start X coordinate (0 to 1)
    // @param {number} value.areaStartY - Area start Y coordinate (0 to 1)
    setHotspotData(value) {
        const hotspotInput = this.getFormInput('hotspot')
        const imageCropAlignmentInput = this.getFormInput('imageCropAlignment')

        if (hotspotInput) {
            const oldValue = JSON.parse(hotspotInput.value || '{}') || {}
            const newData = value ? { x: 0.5, y: 0.5, ...oldValue, ...value } : null

            hotspotInput.value = JSON.stringify(newData)
        }

        if (imageCropAlignmentInput && imageCropAlignmentInput.value !== '') {
            imageCropAlignmentInput.value = '' // Clear the imageCropAlignment input if hotspot is set
        }
    }

    // AREA HANDLE DRAG
    startAreaHandleDrag(event) {
        const handle = event.target
        const areaRect = this.area.getBoundingClientRect()
        const parentRect = this.area.parentElement.getBoundingClientRect()

        this.isDraggingAreaHandle = true

        this.areaHandleDragProps = {
            startX: areaRect.left - parentRect.left,
            startY: areaRect.top - parentRect.top,
            startWidth: areaRect.width,
            startHeight: areaRect.height,
            handleIndex: this.areaHandles.indexOf(handle),
        }
    }

    dragAreaHandle(event) {
        const parentRect = this.area.parentElement.getBoundingClientRect()
        const mouseX = event.clientX - parentRect.left
        const mouseY = event.clientY - parentRect.top
        const { startX, startY, startWidth, startHeight, handleIndex } = this.areaHandleDragProps

        let x = startX
        let y = startY
        let width = startWidth
        let height = startHeight

        // Map handle index to resize direction
        // 0: top-left, 1: top, 2: top-right, 3: left, 4: right, 5: bottom-left, 6: bottom, 7: bottom-right
        switch (handleIndex) {
            case 0: // top-left
                width = startWidth + (startX - mouseX)
                height = startHeight + (startY - mouseY)
                x = mouseX
                y = mouseY
                break
            case 1: // top
                height = startHeight + (startY - mouseY)
                y = mouseY
                break
            case 2: // top-right
                width = mouseX - startX
                height = startHeight + (startY - mouseY)
                y = mouseY
                break
            case 3: // left
                width = startWidth + (startX - mouseX)
                x = mouseX
                break
            case 4: // right
                width = mouseX - startX
                break
            case 5: // bottom-left
                width = startWidth + (startX - mouseX)
                x = mouseX
                height = mouseY - startY
                break
            case 6: // bottom
                height = mouseY - startY
                break
            case 7: // bottom-right
                width = mouseX - startX
                height = mouseY - startY
                break
        }

        // Minimum size constraint
        width = Math.max(20, width)
        height = Math.max(20, height)

        // Limit area to imageWrapper bounds and normalize values if imageWrapper exists
        this.moveArea({ x, y, width, height })
    }

    stopAreaHandleDrag() {
        this.isDraggingAreaHandle = false
    }

    // HOTSPOT DRAG
    startHotspotDrag() {
        this.isDraggingHotspot = true

        this.hotspot?.classList.add('grabbing')
    }

    dragHotspot(event) {
        this.moveHotspot(event.clientX, event.clientY)
    }

    stopHotspotDrag() {
        this.isDraggingHotspot = false

        this.hotspot?.classList.remove('grabbing')
    }

    // AREA DRAG
    startAreaDrag(event) {
        const areaRect = this.area.getBoundingClientRect()
        const parentRect = this.area.parentElement.getBoundingClientRect()

        this.isDraggingArea = true

        this.areaDragProps = {
            startX: areaRect.left - parentRect.left,
            startY: areaRect.top - parentRect.top,
            startMouseX: event.clientX,
            startMouseY: event.clientY,
            areaWidth: areaRect.width,
            areaHeight: areaRect.height,
        }

        this.area.classList.add('dragging')
    }

    dragArea(event) {
        const { startX, startY, startMouseX, startMouseY, areaWidth, areaHeight } = this.areaDragProps
        const deltaX = event.clientX - startMouseX
        const deltaY = event.clientY - startMouseY
        const newX = startX + deltaX
        const newY = startY + deltaY

        this.moveArea({ x: newX, y: newY, width: areaWidth, height: areaHeight })
    }

    stopAreaDrag() {
        this.isDraggingArea = false

        this.area.classList.remove('dragging')
    }

    onPointerMove(event) {
        event.preventDefault() // Prevent scrolling during drag

        if (this.isDraggingAreaHandle) {
            this.dragAreaHandle(event)
        } else if (this.isDraggingHotspot) {
            this.dragHotspot(event)
        } else if (this.isDraggingArea) {
            this.dragArea(event)
        }
    }

    // AREA HANDLERS
    onAreaPointerDown(event) {
        const target = event.target

        this.pointerDownEvent = event

        event.preventDefault()
        event.stopPropagation()

        // Capture the pointer for this element
        this.area.setPointerCapture(event.pointerId)

        if (this.areaHandles.includes(target)) {
            this.startAreaHandleDrag(event)
        } else if (target === this.hotspot) {
            this.startHotspotDrag(event)
        } else if (target === this.area || this.area.contains(target)) {
            this.startAreaDrag(event)
        }

        document.addEventListener('pointermove', this.pointerMoveHandler, { passive: false })
        document.addEventListener('pointerup', this.pointerUpHandler)
    }

    onPointerUp(event) {
        if (this.pointerDownEvent.clientX === event.clientX && this.pointerDownEvent.clientY === event.clientY) {
            // If area was not dragged, move hotspot to clicked position
            this.moveHotspot(event.clientX, event.clientY)
        }

        this.pointerDownEvent = null

        // Release pointer capture
        this.area.releasePointerCapture(event.pointerId)

        if (this.isDraggingAreaHandle) this.stopAreaHandleDrag()
        if (this.isDraggingArea) this.stopAreaDrag()
        if (this.isDraggingHotspot) this.stopHotspotDrag()

        document.removeEventListener('pointermove', this.pointerMoveHandler)
        document.removeEventListener('pointerup', this.pointerUpHandler)
    }
}
