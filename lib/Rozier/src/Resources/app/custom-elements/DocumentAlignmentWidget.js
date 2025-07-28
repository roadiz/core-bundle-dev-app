export default class DocumentAlignmentWidget extends HTMLElement {
    static observedAttributes = ['image-path', 'input-base-name']

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

        if (imageCropAlignmentInput) {
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
        this.area = this.querySelector('.document-alignment-widget__area')
        this.areaHandles = this.querySelectorAll('.document-alignment-widget__area__handles')
        this.hotspot = this.querySelector('.document-alignment-widget__hotspot')

        // this.hotspotFormInput = this.getFormInput('hotspot')
        // this.imageCropAlignmentFormInput = this.getFormInput('imageCropAlignment')

        // const formattedValue = formInput?.value && JSON.parse(formInput.value)

        // formInput?.addEventListener('change', this.onHotspotFormInputChangeCallback)

        // if (formattedValue) {
        //     this.updateHandle(formattedValue)
        // }

        // Allow clicking anywhere on the area to set the position
        if (this.area) {
            this.areaClickHandler = (event) => {
                // Ignore clicks on the handle itself (to avoid conflict with drag)
                if (event.target === this.hotspot) return

                this.moveHotspot(event.clientX, event.clientY)
            }

            this.area.addEventListener('click', this.areaClickHandler)
        }

        // Store handler references for cleanup
        this.hotspotMouseDownHandler = (event) => {
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

        this.hotspotTouchStartHandler = (event) => {
            event.preventDefault()
            event.stopPropagation()

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

        this.area?.addEventListener('mousedown', this.hotspotMouseDownHandler)
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

        if (this.hotspot && this.hotspotMouseDownHandler) {
            this.hotspot.removeEventListener('mousedown', this.hotspotMouseDownHandler)
        }

        if (this.hotspot && this.hotspotTouchStartHandler) {
            this.hotspot.removeEventListener('touchstart', this.hotspotTouchStartHandler)
        }

        // Remove any document listeners that might still be active
        if (this.hotspotMouseMoveHandler) {
            document.removeEventListener('mousemove', this.hotspotMouseMoveHandler)
        }

        if (this.hotspotMouseUpHandler) {
            document.removeEventListener('mouseup', this.hotspotMouseUpHandler)
        }

        if (this.hotspotTouchMoveHandler) {
            document.removeEventListener('touchmove', this.hotspotTouchMoveHandler)
        }

        if (this.hotspotTouchEndHandler) {
            document.removeEventListener('touchend', this.hotspotTouchEndHandler)
        }

        // Clean up handler references
        this.hotspotAreaClickHandler = null
        this.hotspotMouseDownHandler = null
        this.hotspotMouseMoveHandler = null
        this.hotspotMouseUpHandler = null
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
