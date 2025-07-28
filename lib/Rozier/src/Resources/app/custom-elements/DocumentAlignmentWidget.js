export default class DocumentAlignmentWidget extends HTMLElement {
    static observedAttributes = ['image-path', 'input-base-name']

    // @param {Object} value
    // @param {number} value.x - X coordinate (0 to 1)
    // @param {number} value.y - Y coordinate (0 to 1)
    // @param {number} value.areaStartX - Area start X coordinate (0 to 1)
    // @param {number} value.areaStartY - Area start Y coordinate (0 to 1)
    get hotspotData() {
        const hotspot = this.getFormInput('hotspot')?.value

        if (hotspot && hotspot !== 'null') {
            const value = JSON.parse(hotspot)

            if (value && typeof value.x === 'number' && typeof value.y === 'number') {
                return value
            }
        }

        const imageCropAlignment = this.getFormInput('imageCropAlignment')?.value

        if (imageCropAlignment) {
            const value = this.getHotspotFromImageCropAlignment(imageCropAlignment.value)

            if (value) return value
        }

        return { x: 0.5, y: 0.5 } // Default to center if no data is available
    }

    // @param {Object} value
    // @param {number} value.x - X coordinate (0 to 1)
    // @param {number} value.y - Y coordinate (0 to 1)
    // @param {number} value.areaStartX - Area start X coordinate (0 to 1)
    // @param {number} value.areaStartY - Area start Y coordinate (0 to 1)
    set hotspotData(value) {
        const hotspotInput = this.getFormInput('hotspot')
        const imageCropAlignmentInput = this.getFormInput('imageCropAlignment')

        if (hotspotInput) {
            const oldValue = JSON.parse(hotspotInput.value || '{}') || {}

            hotspotInput.value = JSON.stringify({
                ...oldValue,
                ...value,
            })
        }

        if (imageCropAlignmentInput && imageCropAlignmentInput.value !== 'null') {
            imageCropAlignmentInput.value = 'null' // Clear the imageCropAlignment input if hotspot is set
        }
    }

    constructor() {
        super()
    }

    connectedCallback() {
        this.init()
        this.updateImage()
    }

    disconnectedCallback() {
        this.dispose()
    }

    attributeChangedCallback(name) {
        if (name === 'image-path') {
            this.updateImage()
        }
    }

    init() {
        this.imageWrapper = this.querySelector('.document-alignment-widget__image-wrapper')
        this.area = this.querySelector('.document-alignment-widget__area')
        this.areaHandles = Array.from(this.querySelectorAll('.document-alignment-widget__area-handle'))

        this.mouseDownEvent = 0

        // Add resize logic using mousedown on each handle
        let startX,
            startY,
            startWidth,
            startHeight,
            handleIndex = 0

        // Helper to get area bounds and mouse position relative to parent
        const getRelativePosition = (e) => {
            const parentRect = this.area.parentElement.getBoundingClientRect()
            return {
                x: e.clientX - parentRect.left,
                y: e.clientY - parentRect.top,
            }
        }

        const onMouseMove = (e) => {
            const { x: mouseX, y: mouseY } = getRelativePosition(e)
            let newLeft = startX
            let newTop = startY
            let newWidth = startWidth
            let newHeight = startHeight

            // Map handle index to resize direction
            // 0: top-left, 1: top, 2: top-right, 3: left, 4: right, 5: bottom-left, 6: bottom, 7: bottom-right
            switch (handleIndex) {
                case 0: // top-left
                    newWidth = startWidth + (startX - mouseX)
                    newHeight = startHeight + (startY - mouseY)
                    newLeft = mouseX
                    newTop = mouseY
                    break
                case 1: // top
                    newHeight = startHeight + (startY - mouseY)
                    newTop = mouseY
                    break
                case 2: // top-right
                    newWidth = mouseX - startX
                    newHeight = startHeight + (startY - mouseY)
                    newTop = mouseY
                    break
                case 3: // left
                    newWidth = startWidth + (startX - mouseX)
                    newLeft = mouseX
                    break
                case 4: // right
                    newWidth = mouseX - startX
                    break
                case 5: // bottom-left
                    newWidth = startWidth + (startX - mouseX)
                    newLeft = mouseX
                    newHeight = mouseY - startY
                    break
                case 6: // bottom
                    newHeight = mouseY - startY
                    break
                case 7: // bottom-right
                    newWidth = mouseX - startX
                    newHeight = mouseY - startY
                    break
            }

            // Minimum size constraint
            newWidth = Math.max(20, newWidth)
            newHeight = Math.max(20, newHeight)

            // Limit area to imageWrapper bounds
            if (this.imageWrapper) {
                const wrapperRect = this.imageWrapper.getBoundingClientRect()
                const maxLeft = 0
                const maxTop = 0
                const maxWidth = wrapperRect.width
                const maxHeight = wrapperRect.height

                // Clamp left and top
                newLeft = Math.max(maxLeft, Math.min(newLeft, maxWidth - newWidth))
                newTop = Math.max(maxTop, Math.min(newTop, maxHeight - newHeight))

                // Clamp width and height so area stays inside wrapper
                if (newLeft + newWidth > maxWidth) {
                    newWidth = maxWidth - newLeft
                }
                if (newTop + newHeight > maxHeight) {
                    newHeight = maxHeight - newTop
                }
            }

            resizeArea({ x: newLeft, y: newTop, width: newWidth, height: newHeight })
        }

        const resizeArea = ({ x, y, width, height }) => {
            const data = { areaStartX: x, areaStartY: y, areaEndX: x + width, areaEndY: y + height }

            this.hotspotData = data

            updateArea({ x, y, width, height })
        }

        const updateArea = ({ x, y, width, height }) => {
            if (x) this.area.style.left = `${x}px`
            if (y) this.area.style.top = `${y}px`
            if (width) this.area.style.width = `${width}px`
            if (height) this.area.style.height = `${height}px`
        }

        const onMouseUp = () => {
            document.removeEventListener('mousemove', onMouseMove)
            document.removeEventListener('mouseup', onMouseUp)
        }

        // Add mousedown listener to each handle
        this.areaHandles.forEach((handle, idx) => {
            handle.addEventListener('mousedown', (e) => {
                e.preventDefault()
                e.stopPropagation()

                const areaRect = this.area.getBoundingClientRect()
                const parentRect = this.area.parentElement.getBoundingClientRect()
                startX = areaRect.left - parentRect.left
                startY = areaRect.top - parentRect.top
                startWidth = areaRect.width
                startHeight = areaRect.height
                handleIndex = idx

                document.addEventListener('mousemove', onMouseMove)
                document.addEventListener('mouseup', onMouseUp)
            })
        })
        this.hotspot = this.querySelector('.document-alignment-widget__hotspot')

        // this.hotspotFormInput = this.getFormInput('hotspot')
        // this.imageCropAlignmentFormInput = this.getFormInput('imageCropAlignment')

        // const formattedValue = formInput?.value && JSON.parse(formInput.value)

        // formInput?.addEventListener('change', this.onHotspotFormInputChangeCallback)

        // if (formattedValue) {
        //     this.updateHandle(formattedValue)
        // }

        // Area mouse down handler - handles different interaction types
        this.areaMouseDownHandler = (event) => {
            event.preventDefault()
            event.stopPropagation()

            const target = event.target

            this.mouseDownEvent = event

            // Check if clicking on a handle (resize area)
            if (this.areaHandles.includes(target)) {
                // Handle logic is already handled above in the forEach loop
                return
            }

            // Check if clicking on hotspot (move hotspot)
            if (target === this.hotspot) {
                this.startHotspotDrag(event)
                return
            }

            // Check if clicking on area but not on hotspot or handle (move entire area)
            if (target === this.area || this.area.contains(target)) {
                // For click events (not drag), move hotspot to clicked position
                if (event.type === 'click') {
                    this.moveHotspot(event.clientX, event.clientY)
                    return
                }

                // For mousedown events, start area drag
                this.startAreaDrag(event)
                return
            }
        }

        this.areaMouseClickHandler = (event) => {
            event.preventDefault()
            event.stopPropagation()

            if (this.mouseDownEvent.clientX === event.clientX && this.mouseDownEvent.clientY === event.clientY) {
                // If area was not dragged, move hotspot to clicked position
                this.moveHotspot(event.clientX, event.clientY)
            }

            this.mouseDownEvent = null
        }

        // Hotspot drag functionality
        this.startHotspotDrag = (event) => {
            console.log('startHotspotDrag', event)
            event.preventDefault()
            event.stopPropagation()

            this.hotspot?.classList.add('grabbing')

            this.hotspotMouseMoveHandler = (moveEvent) => {
                this.moveHotspot(moveEvent.clientX, moveEvent.clientY)
            }

            this.hotspotMouseUpHandler = () => {
                this.hotspot?.classList.remove('grabbing')
                document.removeEventListener('mousemove', this.hotspotMouseMoveHandler)
                document.removeEventListener('mouseup', this.hotspotMouseUpHandler)
            }

            document.addEventListener('mousemove', this.hotspotMouseMoveHandler)
            document.addEventListener('mouseup', this.hotspotMouseUpHandler)
        }

        // Area drag functionality
        this.startAreaDrag = (event) => {
            const areaRect = this.area.getBoundingClientRect()
            const parentRect = this.area.parentElement.getBoundingClientRect()
            const startAreaX = areaRect.left - parentRect.left
            const startAreaY = areaRect.top - parentRect.top
            const startMouseX = event.clientX
            const startMouseY = event.clientY

            this.area.classList.add('dragging')

            this.areaDragMoveHandler = (moveEvent) => {
                const deltaX = moveEvent.clientX - startMouseX
                const deltaY = moveEvent.clientY - startMouseY
                const newX = startAreaX + deltaX
                const newY = startAreaY + deltaY

                // Constrain area to stay within image wrapper bounds
                let constrainedX = newX
                let constrainedY = newY

                if (this.imageWrapper) {
                    const wrapperRect = this.imageWrapper.getBoundingClientRect()
                    const areaWidth = areaRect.width
                    const areaHeight = areaRect.height

                    constrainedX = Math.max(0, Math.min(newX, wrapperRect.width - areaWidth))
                    constrainedY = Math.max(0, Math.min(newY, wrapperRect.height - areaHeight))
                }

                // Update area position
                this.area.style.left = `${constrainedX}px`
                this.area.style.top = `${constrainedY}px`

                // Update hotspot data
                const data = {
                    areaStartX: constrainedX,
                    areaStartY: constrainedY,
                    areaEndX: constrainedX + areaRect.width,
                    areaEndY: constrainedY + areaRect.height,
                }
                this.hotspotData = data
            }

            this.areaDragUpHandler = () => {
                this.area.classList.remove('dragging')
                document.removeEventListener('mousemove', this.areaDragMoveHandler)
                document.removeEventListener('mouseup', this.areaDragUpHandler)
            }

            document.addEventListener('mousemove', this.areaDragMoveHandler)
            document.addEventListener('mouseup', this.areaDragUpHandler)
        }

        // Touch handling for hotspot
        this.hotspotTouchStartHandler = (event) => {
            event.preventDefault()
            event.stopPropagation()

            // Check if touching hotspot specifically
            if (event.target !== this.hotspot) return

            this.hotspotTouchMoveHandler = (moveEvent) => {
                if (!moveEvent.touches || moveEvent.touches.length === 0) return
                const touch = moveEvent.touches[0]
                this.moveHotspot(touch.clientX, touch.clientY)
            }

            this.hotspotTouchEndHandler = () => {
                document.removeEventListener('touchmove', this.hotspotTouchMoveHandler)
                document.removeEventListener('touchend', this.hotspotTouchEndHandler)
            }

            document.addEventListener('touchmove', this.hotspotTouchMoveHandler)
            document.addEventListener('touchend', this.hotspotTouchEndHandler)
        }

        // Add event listeners
        this.area?.addEventListener('mousedown', this.areaMouseDownHandler)
        this.area?.addEventListener('click', this.areaMouseClickHandler)
        this.hotspot?.addEventListener('mousedown', this.startHotspotDrag)
        this.area?.addEventListener('touchstart', this.hotspotTouchStartHandler, { passive: false })
    }

    dispose() {
        this.setFormInputValue('hotspot', 'null')
        const formInput = this.getFormInput('hotspot')

        if (formInput) {
            formInput.removeEventListener('change', this.onHotspotFormInputChangeCallback)
        }

        if (this.hotspotInputs) {
            this.hotspotInputs.forEach((input) => {
                input.removeEventListener('change', this.onHotspotTabInputChangeCallback)
            })
        }

        if (this.hotspotArea && this.hotspotAreaClickHandler) {
            this.hotspotArea.removeEventListener('click', this.hotspotAreaClickHandler)
        }

        if (this.area && this.areaMouseDownHandler) {
            this.area.removeEventListener('mousedown', this.areaMouseDownHandler)
            this.area.removeEventListener('click', this.areaMouseDownHandler)
        }

        if (this.hotspot && this.startHotspotDrag) {
            this.hotspot.removeEventListener('mousedown', this.startHotspotDrag)
        }

        if (this.area && this.hotspotTouchStartHandler) {
            this.area.removeEventListener('touchstart', this.hotspotTouchStartHandler)
        }

        // Remove any document listeners that might still be active
        if (this.hotspotMouseMoveHandler) {
            document.removeEventListener('mousemove', this.hotspotMouseMoveHandler)
        }

        if (this.hotspotMouseUpHandler) {
            document.removeEventListener('mouseup', this.hotspotMouseUpHandler)
        }

        if (this.areaDragMoveHandler) {
            document.removeEventListener('mousemove', this.areaDragMoveHandler)
        }

        if (this.areaDragUpHandler) {
            document.removeEventListener('mouseup', this.areaDragUpHandler)
        }

        if (this.hotspotTouchMoveHandler) {
            document.removeEventListener('touchmove', this.hotspotTouchMoveHandler)
        }

        if (this.hotspotTouchEndHandler) {
            document.removeEventListener('touchend', this.hotspotTouchEndHandler)
        }

        // Clean up handler references
        this.hotspotAreaClickHandler = null
        this.areaMouseDownHandler = null
        this.startHotspotDrag = null
        this.startAreaDrag = null
        this.hotspotMouseMoveHandler = null
        this.hotspotMouseUpHandler = null
        this.areaDragMoveHandler = null
        this.areaDragUpHandler = null
        this.hotspotTouchStartHandler = null
        this.hotspotTouchMoveHandler = null
        this.hotspotTouchEndHandler = null
    }

    updateImage() {
        const imagePath = this.getAttribute('image-path')

        this.querySelectorAll('.document-alignment-widget__image').forEach((img) => {
            if (imagePath && img.src !== imagePath) {
                img.src = imagePath
            }
        })
    }

    getFormInput(id) {
        const name = this.getAttribute('input-base-name')

        return document.querySelector(`input[name="${name}[${id}]"]`)
    }

    reset() {}

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
        const rect = this.area?.getBoundingClientRect()

        if (!rect) return

        let x = (clientX - rect.left) / rect.width
        let y = (clientY - rect.top) / rect.height

        // Clamp values between 0 and 1
        x = Math.max(0, Math.min(1, x))
        y = Math.max(0, Math.min(1, y))

        // Round to two decimal places
        // x = Math.round(x * 100) / 100
        // y = Math.round(y * 100) / 100

        this.hotspotData = { x, y, width: rect.width, height: rect.height }

        this.updateHandle({ x, y })
    }

    updateHandle(value) {
        if (!this.hotspot) return

        this.hotspot.style.top = `${(value.y || 0) * 100}%`
        this.hotspot.style.left = `${(value.x || 0) * 100}%`
    }

    getHotspotFormInputValue() {
        const value = this.getFormInput('hotspot')?.value

        return value ? JSON.parse(value) : {}
    }

    setHotspotFormInputValue(value) {
        const input = this.getFormInput('hotspot')
        const formattedValue = JSON.stringify(value)

        if (input && (!input.value || input.value !== formattedValue)) {
            input.value = formattedValue

            input.dispatchEvent(new Event('change', { bubbles: true, composed: true }))
        }
    }

    onHotspotFormInputChange(event) {
        const formattedValue = JSON.parse(event.target.value)

        this.updateHotspotTabInputs(formattedValue)
        this.updateHotspotHandle(formattedValue)
    }

    onHotspotTabInputChange(event) {
        const key = event.target.name.replace('hotspot-', '')
        const input = this.getFormInput('hotspot')

        if (!input) return

        const inputValue = this.getHotspotFormInputValue()

        this.setHotspotFormInputValue({
            ...inputValue,
            [key]: parseFloat(event.target.value),
        })
    }
}
