/**
 * Utility function to round a value to a specified precision
 * @param {number} value - The value to round
 * @param {number} precision - Number of decimal places
 * @returns {number} The rounded value
 */
function round(value, precision = 3) {
    const factor = Math.pow(10, precision)
    return Math.round(value * factor) / factor
}

export default class DocumentAlignmentWidget extends HTMLElement {
    static observedAttributes = ['image-path']

    // Constants
    static CSS_SELECTORS = {
        IMAGE_WRAPPER: '.document-alignment-widget__image-wrapper',
        AREA: '.document-alignment-widget__area',
        HANDLES: '.document-alignment-widget__handle',
        HOTSPOT: '.document-alignment-widget__hotspot',
        RESET_BUTTON: '.document-alignment-widget__reset',
        OVERRIDE_BUTTON: '.document-alignment-widget__override',
        IMAGE: '.document-alignment-widget__image',
    }

    static CSS_CLASSES = {
        OVERRIDE_VISIBLE: 'document-alignment-widget__override--visible',
        RESET_VISIBLE: 'document-alignment-widget__reset--visible',
        HOTSPOT_GRABBING: 'document-alignment-widget__hotspot--grabbing',
    }

    static CSS_PROPERTIES = {
        AREA_TOP: '--document-alignment-widget-area-top',
        AREA_RIGHT: '--document-alignment-widget-area-right',
        AREA_BOTTOM: '--document-alignment-widget-area-bottom',
        AREA_LEFT: '--document-alignment-widget-area-left',
    }

    static DEFAULT_VALUES = {
        HOTSPOT: { x: 0.5, y: 0.5 },
        AREA: { areaStartX: 0, areaStartY: 0, areaEndX: 1, areaEndY: 1 },
        MIN_SIZE: 20,
    }

    static CROP_ALIGNMENT_MAP = {
        'top-left': { x: 0, y: 0 },
        top: { x: 0.5, y: 0 },
        'top-right': { x: 1, y: 0 },
        left: { x: 0, y: 0.5 },
        center: { x: 0.5, y: 0.5 },
        right: { x: 1, y: 0.5 },
        'bottom-left': { x: 0, y: 1 },
        bottom: { x: 0.5, y: 1 },
        'bottom-right': { x: 1, y: 1 },
    }

    // Instance properties with default values (class fields)
    originalHotspot = null
    hotspotOverridable = false

    // Drag state
    isDraggingHandle = false
    isDraggingHotspot = false
    isDraggingArea = false

    // Cached properties for performance
    cachedRects = new Map()

    // DOM element references
    imageWrapper = null
    area = null
    handles = null
    hotspot = null
    resetButton = null
    overrideButton = null
    overrideButtonInput = null
    imageElement = null

    // Drag operation initial data
    handleDragProps = null
    areaDragProps = null
    pointerDownEvent = null

    // Lifecycle methods
    constructor() {
        super()

        // Event handlers (bound once for performance)
        this.boundHandlers = {
            areaPointerDown: this.onAreaPointerDown.bind(this),
            pointerMove: this.onPointerMove.bind(this),
            pointerUp: this.onPointerUp.bind(this),
            resetClick: this.onResetClick.bind(this),
            overrideChange: this.onOverrideChange.bind(this),
        }
    }

    connectedCallback() {
        this.initializeOriginalHotspot()
        this.initializeOverridableHotspot()
        this.initializeElements()
        this.updateImagePath()
        this.setupOverrideHandling()
        this.updateElements()
    }

    disconnectedCallback() {
        this.cleanup()
    }

    attributeChangedCallback(name) {
        if (name === 'image-path') {
            this.updateImagePath()
        }
    }

    /**
     * Parse and store original hotspot data from attributes
     */
    initializeOriginalHotspot() {
        const originalHotspot = this.getAttribute('original-hotspot')

        if (originalHotspot) {
            try {
                this.originalHotspot = JSON.parse(originalHotspot)
            } catch (error) {
                console.error('DocumentAlignmentWidget: Invalid original hotspot data:', error)
                this.originalHotspot = null
            }
        }
    }

    /**
     * Initialize overridable hotspot setting
     */
    initializeOverridableHotspot() {
        this.hotspotOverridable = Boolean(this.getAttribute('hotspot-overridable'))
    }

    /**
     * Setup override button handling if enabled
     */
    setupOverrideHandling() {
        if (!this.hotspotOverridable || !this.overrideButton) return

        const storedData = this.getStoredHotspotData()

        this.overrideButton.classList.add(DocumentAlignmentWidget.CSS_CLASSES.OVERRIDE_VISIBLE)
        this.overrideButtonInput.checked = !!storedData
        this.area.inert = !this.overrideButtonInput.checked

        this.overrideButtonInput.addEventListener('change', this.boundHandlers.overrideChange)
    }

    /**
     * Initialize DOM elements and event handlers
     */
    initializeElements() {
        // Query DOM elements using constants
        const { CSS_SELECTORS } = DocumentAlignmentWidget

        this.imageWrapper = this.querySelector(CSS_SELECTORS.IMAGE_WRAPPER)
        this.area = this.querySelector(CSS_SELECTORS.AREA)
        this.handles = Array.from(this.querySelectorAll(CSS_SELECTORS.HANDLES))
        this.hotspot = this.querySelector(CSS_SELECTORS.HOTSPOT)
        this.resetButton = this.querySelector(CSS_SELECTORS.RESET_BUTTON)
        this.overrideButton = this.querySelector(CSS_SELECTORS.OVERRIDE_BUTTON)
        this.overrideButtonInput = this.overrideButton?.querySelector('input')

        // Validate required elements
        if (!this.imageWrapper || !this.area || this.handles.length === 0 || !this.hotspot) {
            console.error('DocumentAlignmentWidget: Missing required elements')
            return
        }

        // Setup event listeners
        this.setupEventListeners()
    }

    /**
     * Setup all event listeners
     */
    setupEventListeners() {
        this.resetButton?.addEventListener('click', this.boundHandlers.resetClick)
        this.area.addEventListener('pointerdown', this.boundHandlers.areaPointerDown, { passive: false })
    }

    /**
     * Clean up resources and event listeners
     */
    cleanup() {
        // Remove event listeners
        this.area?.removeEventListener('pointerdown', this.boundHandlers.areaPointerDown)
        this.resetButton?.removeEventListener('click', this.boundHandlers.resetClick)
        this.overrideButtonInput?.removeEventListener('change', this.boundHandlers.overrideChange)

        // Remove global event listeners if they exist
        document.removeEventListener('pointermove', this.boundHandlers.pointerMove)
        document.removeEventListener('pointerup', this.boundHandlers.pointerUp)

        // Nullify DOM references to prevent memory leaks
        this.imageWrapper = null
        this.area = null
        this.handles = null
        this.hotspot = null
        this.resetButton = null
        this.overrideButton = null
        this.overrideButtonInput = null

        // Clear drag state
        this.handleDragProps = null
        this.areaDragProps = null
        this.pointerDownEvent = null
    }

    /**
     * Update the image source when the image-path attribute changes
     */
    updateImagePath() {
        if (!this.imageElement) {
            this.imageElement = this.querySelector(DocumentAlignmentWidget.CSS_SELECTORS.IMAGE)

            if (!this.imageElement) return
        }

        const imagePath = this.getAttribute('image-path')

        if (imagePath && this.imageElement.src !== imagePath) {
            this.imageElement.src = imagePath
        }
    }

    updateElements() {
        const storedData = this.getStoredHotspotData()
        let data = storedData
        let resetButtonVisible = !!storedData

        if (this.hotspotOverridable) {
            if (!this.overrideButtonInput?.checked) {
                data = this.originalHotspot
            }

            resetButtonVisible = this.overrideButtonInput?.checked && !!storedData
        }

        this.setAreaPosition(data)
        this.setHotspotPosition(data)
        this.toggleResetButtonVisibility(resetButtonVisible)
    }

    /**
     * Show or hide the reset button
     * @param {boolean} isVisible - Whether the button should be visible
     */
    toggleResetButtonVisibility(isVisible) {
        this.resetButton?.classList.toggle(DocumentAlignmentWidget.CSS_CLASSES.RESET_VISIBLE, isVisible)
    }

    getInput(id) {
        const name = this.getAttribute('input-base-name')

        return document.querySelector(`input[name="${name}[${id}]"]`)
    }

    /**
     * Move and resize the area while keeping it within bounds
     * @param {Object} bounds - Position and size
     * @param {number} bounds.x - X position
     * @param {number} bounds.y - Y position
     * @param {number} bounds.width - Width
     * @param {number} bounds.height - Height
     */
    moveArea({ x, y, width, height }) {
        // Get cached or fresh wrapper rect for performance
        let wrapperRect = this.cachedRects.get('wrapper')

        if (!wrapperRect) {
            wrapperRect = this.imageWrapper.getBoundingClientRect()
            this.cachedRects.set('wrapper', wrapperRect)
        }

        const maxWidth = wrapperRect.width
        const maxHeight = wrapperRect.height

        // Clamp values to keep area within bounds
        let clampedX = Math.max(0, Math.min(x, maxWidth - width))
        let clampedY = Math.max(0, Math.min(y, maxHeight - height))
        let clampedWidth = Math.max(DocumentAlignmentWidget.DEFAULT_VALUES.MIN_SIZE, width)
        let clampedHeight = Math.max(DocumentAlignmentWidget.DEFAULT_VALUES.MIN_SIZE, height)

        // Ensure area stays within wrapper bounds
        if (clampedX + clampedWidth > maxWidth) {
            clampedWidth = maxWidth - clampedX
        }

        if (clampedY + clampedHeight > maxHeight) {
            clampedHeight = maxHeight - clampedY
        }

        // Normalize values to 0-1 range
        const data = {
            areaStartX: round(clampedX / maxWidth),
            areaStartY: round(clampedY / maxHeight),
            areaEndX: round((clampedX + clampedWidth) / maxWidth),
            areaEndY: round((clampedY + clampedHeight) / maxHeight),
        }

        this.setHotspotData(data)
        this.toggleResetButtonVisibility(true)
        this.setAreaPosition(data)
    }

    /**
     * Set the area position using CSS custom properties
     * @param {Object|null} value - Position data or null to clear
     */
    setAreaPosition(value) {
        const { CSS_PROPERTIES } = DocumentAlignmentWidget

        if (value) {
            const { areaStartX, areaStartY, areaEndX, areaEndY } = value

            const top = (areaStartY || 0) * 100
            const right = (1 - (areaEndX || 1)) * 100
            const bottom = (1 - (areaEndY || 1)) * 100
            const left = (areaStartX || 0) * 100

            this.imageWrapper.style.setProperty(CSS_PROPERTIES.AREA_TOP, `${top}%`)
            this.imageWrapper.style.setProperty(CSS_PROPERTIES.AREA_RIGHT, `${right}%`)
            this.imageWrapper.style.setProperty(CSS_PROPERTIES.AREA_BOTTOM, `${bottom}%`)
            this.imageWrapper.style.setProperty(CSS_PROPERTIES.AREA_LEFT, `${left}%`)
        } else {
            this.imageWrapper.style.removeProperty(CSS_PROPERTIES.AREA_TOP)
            this.imageWrapper.style.removeProperty(CSS_PROPERTIES.AREA_RIGHT)
            this.imageWrapper.style.removeProperty(CSS_PROPERTIES.AREA_BOTTOM)
            this.imageWrapper.style.removeProperty(CSS_PROPERTIES.AREA_LEFT)
        }
    }

    /**
     * Get hotspot coordinates from image crop alignment value
     * @param {string} imageCropAlignment - Alignment value
     * @returns {Object|undefined} Hotspot coordinates or undefined
     */
    getHotspotFromImageCropAlignment(imageCropAlignment) {
        return DocumentAlignmentWidget.CROP_ALIGNMENT_MAP[imageCropAlignment]
    }

    /**
     * Move hotspot to specified client coordinates
     * @param {number} clientX - Client X coordinate
     * @param {number} clientY - Client Y coordinate
     */
    moveHotspot(clientX, clientY) {
        // Get cached or fresh area rect for performance
        let areaRect = this.cachedRects.get('area')

        if (!areaRect) {
            areaRect = this.area.getBoundingClientRect()

            this.cachedRects.set('area', areaRect)
        }

        // Calculate normalized coordinates
        let x = (clientX - areaRect.left) / areaRect.width
        let y = (clientY - areaRect.top) / areaRect.height

        // Clamp values between 0 and 1
        x = Math.max(0, Math.min(1, round(x)))
        y = Math.max(0, Math.min(1, round(y)))

        const data = { x, y }
        this.setHotspotData(data)
        this.toggleResetButtonVisibility(true)
        this.setHotspotPosition(data)
    }

    /**
     * Set hotspot visual position
     * @param {Object|null} value - Position data
     * @param {number} [value.x] - X coordinate (0-1)
     * @param {number} [value.y] - Y coordinate (0-1)
     */
    setHotspotPosition(value) {
        if (!this.hotspot) return

        this.hotspot.style.top = typeof value?.y === 'number' ? `${value.y * 100}%` : ''
        this.hotspot.style.left = typeof value?.x === 'number' ? `${value.x * 100}%` : ''
    }

    /**
     * Get stored hotspot data from form inputs or fallback to image crop alignment
     * @returns {Object|null} Hotspot data or null if none found
     */
    getStoredHotspotData() {
        const hotspot = this.getInput('hotspot')?.value

        if (hotspot && hotspot !== 'null') {
            try {
                const value = JSON.parse(hotspot)
                if (value && typeof value.x === 'number' && typeof value.y === 'number') {
                    return value
                }
            } catch (error) {
                console.error('DocumentAlignmentWidget: Invalid stored hotspot data:', error)
            }
        }

        const imageCropAlignment = this.getInput('imageCropAlignment')?.value
        if (imageCropAlignment) {
            return this.getHotspotFromImageCropAlignment(imageCropAlignment)
        }

        return null
    }

    /**
     * Set hotspot data in form inputs
     * @param {Object|null} value - Hotspot data to store
     * @param {number} [value.x] - X coordinate (0-1)
     * @param {number} [value.y] - Y coordinate (0-1)
     * @param {number} [value.areaStartX] - Area start X coordinate (0-1)
     * @param {number} [value.areaStartY] - Area start Y coordinate (0-1)
     * @param {number} [value.areaEndX] - Area end X coordinate (0-1)
     * @param {number} [value.areaEndY] - Area end Y coordinate (0-1)
     */
    setHotspotData(value) {
        const hotspotInput = this.getInput('hotspot')
        const imageCropAlignmentInput = this.getInput('imageCropAlignment')

        if (hotspotInput) {
            let oldValue = {}
            try {
                oldValue = JSON.parse(hotspotInput.value || '{}') || {}
            } catch (error) {
                console.error('DocumentAlignmentWidget: Invalid existing hotspot data:', error)
            }

            let newData = null
            if (value) {
                newData = {
                    ...DocumentAlignmentWidget.DEFAULT_VALUES.HOTSPOT,
                    ...oldValue,
                    ...value,
                }
            }

            hotspotInput.value = JSON.stringify(newData)
        }

        // Clear imageCropAlignment when hotspot is set
        if (imageCropAlignmentInput && imageCropAlignmentInput.value !== '') {
            imageCropAlignmentInput.value = ''
        }
    }

    /**
     * Start dragging an area handle
     * @param {PointerEvent} event - Pointer down event
     */
    startDragHandle(event) {
        const handle = event.target
        const areaRect = this.area.getBoundingClientRect()
        const parentRect = this.area.parentElement.getBoundingClientRect()

        this.isDraggingHandle = true

        this.handleDragProps = {
            startX: areaRect.left - parentRect.left,
            startY: areaRect.top - parentRect.top,
            startWidth: areaRect.width,
            startHeight: areaRect.height,
            handleIndex: this.handles.indexOf(handle),
            parentRect: this.imageWrapper.getBoundingClientRect(),
        }

        // Clear cached rects since we're starting a new operation
        this.cachedRects.clear()
    }

    /**
     * Handle area handle dragging
     * @param {PointerEvent} event - Pointer move event
     */
    dragHandle(event) {
        const { startX, startY, startWidth, startHeight, handleIndex, parentRect } = this.handleDragProps
        const mouseX = event.clientX - parentRect.left
        const mouseY = event.clientY - parentRect.top

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

        // Apply minimum size constraint
        width = Math.max(DocumentAlignmentWidget.DEFAULT_VALUES.MIN_SIZE, width)
        height = Math.max(DocumentAlignmentWidget.DEFAULT_VALUES.MIN_SIZE, height)

        this.moveArea({ x, y, width, height })
    }

    /**
     * Stop area handle dragging
     */
    stopDragHandle() {
        this.isDraggingHandle = false
    }

    /**
     * Start hotspot dragging
     */
    startDragHotspot() {
        this.isDraggingHotspot = true
        this.hotspot?.classList.add(DocumentAlignmentWidget.CSS_CLASSES.HOTSPOT_GRABBING)

        // Clear cached rects since we're starting a new operation
        this.cachedRects.clear()
    }

    /**
     * Handle hotspot dragging
     * @param {PointerEvent} event - Pointer move event
     */
    dragHotspot(event) {
        this.moveHotspot(event.clientX, event.clientY)
    }

    /**
     * Stop hotspot dragging
     */
    stopDragHotspot() {
        this.isDraggingHotspot = false
        this.hotspot?.classList.remove(DocumentAlignmentWidget.CSS_CLASSES.HOTSPOT_GRABBING)
    }

    /**
     * Start area dragging
     * @param {PointerEvent} event - Pointer down event
     */
    startDragArea(event) {
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

        // Clear cached rects since we're starting a new operation
        this.cachedRects.clear()
    }

    /**
     * Handle area dragging
     * @param {PointerEvent} event - Pointer move event
     */
    dragArea(event) {
        const { startX, startY, startMouseX, startMouseY, areaWidth, areaHeight } = this.areaDragProps
        const deltaX = event.clientX - startMouseX
        const deltaY = event.clientY - startMouseY
        const newX = startX + deltaX
        const newY = startY + deltaY

        this.moveArea({ x: newX, y: newY, width: areaWidth, height: areaHeight })
    }

    /**
     * Stop area dragging
     */
    stopDragArea() {
        this.isDraggingArea = false
    }

    /**
     * Handle pointer down events on the area
     * @param {PointerEvent} event - Pointer down event
     */
    onAreaPointerDown(event) {
        const target = event.target

        this.pointerDownEvent = event

        event.preventDefault()
        event.stopPropagation()

        if (this.handles.includes(target)) {
            this.startDragHandle(event)
        } else if (target === this.hotspot) {
            this.startDragHotspot(event)
        } else if (target === this.area || this.area.contains(target)) {
            this.startDragArea(event)
        }

        document.addEventListener('pointermove', this.boundHandlers.pointerMove, { passive: false })
        document.addEventListener('pointerup', this.boundHandlers.pointerUp)
    }

    /**
     * Handle pointer move events during dragging
     * @param {PointerEvent} event - Pointer move event
     */
    onPointerMove(event) {
        event.preventDefault() // Prevent scrolling during drag

        if (this.isDraggingHandle) {
            this.dragHandle(event)
        } else if (this.isDraggingHotspot) {
            this.dragHotspot(event)
        } else if (this.isDraggingArea) {
            this.dragArea(event)
        }
    }

    /**
     * Handle pointer up events
     * @param {PointerEvent} event - Pointer up event
     */
    onPointerUp(event) {
        // If pointer didn't move, treat as click to move hotspot
        if (this.pointerDownEvent.clientX === event.clientX && this.pointerDownEvent.clientY === event.clientY) {
            this.moveHotspot(event.clientX, event.clientY)
        }

        this.pointerDownEvent = null

        // Stop all drag operations
        if (this.isDraggingHandle) this.stopDragHandle()
        if (this.isDraggingArea) this.stopDragArea()
        if (this.isDraggingHotspot) this.stopDragHotspot()

        // Remove global event listeners
        document.removeEventListener('pointermove', this.boundHandlers.pointerMove)
        document.removeEventListener('pointerup', this.boundHandlers.pointerUp)

        // Clear cached rects since the operation is complete
        this.cachedRects.clear()
    }

    /**
     * Handle override button change events
     * @param {Event} event - Change event
     */
    onOverrideChange(event) {
        const isChecked = event.target.checked

        this.area.inert = !isChecked

        if (!isChecked) {
            this.setHotspotData(null)
        } else {
            const defaultData = {
                ...DocumentAlignmentWidget.DEFAULT_VALUES.HOTSPOT,
                ...DocumentAlignmentWidget.DEFAULT_VALUES.AREA,
            }

            this.setHotspotData(this.originalHotspot || defaultData)
        }

        this.updateElements()
    }

    /**
     * Handle reset button click events
     */
    onResetClick() {
        let newHotspotData = null

        if (this.hotspotOverridable) {
            newHotspotData = {
                ...DocumentAlignmentWidget.DEFAULT_VALUES.HOTSPOT,
                ...DocumentAlignmentWidget.DEFAULT_VALUES.AREA,
            }
        }

        this.setHotspotData(newHotspotData)
        this.updateElements()
    }
}
