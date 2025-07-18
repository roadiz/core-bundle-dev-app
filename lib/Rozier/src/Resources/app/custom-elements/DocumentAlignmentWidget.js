export default class DocumentAlignmentWidget extends HTMLElement {
    static observedAttributes = ['image-path', 'input-base-name', 'origin-button-id']

    constructor() {
        super()

        this.activeTabId = ''
        this.originEditButton = null
    }

    connectedCallback() {
        this.tabs = Array.from(this.querySelectorAll('.document-alignment-widget__tab'))
        this.tabPanels = Array.from(this.querySelectorAll('.document-alignment-widget__tab-panel'))

        // Tab inputs
        this.tabInputs = this.querySelectorAll('.document-alignment-widget__tab > input[type="radio"]')
        this.onTabInputChangeCallback = this.onTabInputChange.bind(this)
        this.tabInputs?.forEach((input) => {
            input.addEventListener('change', this.onTabInputChangeCallback)
        })

        // Image crop alignment
        this.onImageCropAlignmentChangeCallback = this.onImageCropAlignmentChange.bind(this)

        // Hotspot
        this.hotspotInputs = Array.from(this.querySelectorAll('.document-alignment-widget__hotspot-input input'))
        this.hotspotHandle = this.querySelector('.document-alignment-widget__hotspot-handle')
        this.hotspotArea = this.hotspotHandle?.parentElement
        this.onHotspotFormInputChangeCallback = this.onHotspotFormInputChange.bind(this)
        this.onHotspotTabInputChangeCallback = this.onHotspotTabInputChange.bind(this)

        this.setupPanelImages()

        this.setActiveTab(this.getAttribute('active-tab-id') || 'none')
        // this.setActiveTab('imageCropAlignment') // Default to 'imageCropAlignment' for testing
    }

    disconnectedCallback() {
        this.disposeTab(this.activeTabId)

        this.tabs = null
        this.tabPanels = null
        this.formInputs = null
        this.hotspotInputs = null

        // Dispose tab inputs
        this.tabInputs?.forEach((input) => {
            input.removeEventListener('change', this.onTabInputChangeCallback)
        })
        this.tabInputs = null
    }

    attributeChangedCallback(name) {
        if (name === 'image-path') {
            this.setupPanelImages()
        }
        if (name === 'origin-button-id') {
            const newButtonId = this.getAttribute('origin-button-id')
            this.originEditButton = document.getElementById(newButtonId)
        }
    }

    setupPanelImages() {
        const imagePath = this.getAttribute('image-path')

        this.querySelectorAll('.document-alignment-widget__tab-panel__content img').forEach((img) => {
            if (imagePath) {
                img.src = imagePath
            }
        })
    }

    getPanel(tabId) {
        return this.tabPanels?.find((panel) => panel.dataset.tabId === tabId)
    }

    activateOriginalEditButton() {
        if (this.originEditButton) {
            this.originEditButton.classList.add('document-link--alignment')
        }
    }

    desactivateOriginalEditButton() {
        if (this.originEditButton) {
            this.originEditButton.classList.remove('document-link--alignment')
        }
    }

    initTab(tabId) {
        if (tabId === 'none') {
            this.initNoAlignment()
            this.desactivateOriginalEditButton()
        } else if (tabId === 'imageCropAlignment') {
            this.initCropAlignment()
            this.activateOriginalEditButton()
        } else if (tabId === 'hotspot') {
            this.initHotspot()
            this.activateOriginalEditButton()
        }
    }

    disposeTab(tabId) {
        if (tabId === 'imageCropAlignment') {
            this.disposeCropAlignment()
        } else if (tabId === 'hotspot') {
            this.disposeHotspot()
        }
    }

    setActiveTab(tabId) {
        const previousActiveTabId = this.activeTabId

        this.activeTabId = tabId

        // Update tabs
        this.tabs?.forEach((tab) => {
            const isActive = tab.dataset.tabId === tabId

            tab.classList.toggle('document-alignment-widget__tab--active', isActive)
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false')
        })

        // Update tab inputs
        this.tabInputs?.forEach((input) => {
            input.checked = input.value === tabId
        })

        // Update panels
        this.tabPanels?.forEach((panel) => {
            panel.classList.toggle('document-alignment-widget__tab-panel--active', panel.dataset.tabId === tabId)
        })

        this.disposeTab(previousActiveTabId)
        this.initTab(tabId)
    }

    getFormInput(id) {
        const name = this.getAttribute('input-base-name')

        return document.querySelector(`input[name="${name}[${id}]"]`)
    }

    getTab(tabId) {
        return this.tabs?.find((tab) => tab.dataset.tabId === tabId)
    }

    setFormInputValue(id, value) {
        const input = this.getFormInput(id)

        if (!input) return

        input.value = value
    }

    initNoAlignment() {
        this.setFormInputValue('imageCropAlignment', '')
        this.setFormInputValue('hotspot', 'null')
    }

    // Image Crop Alignment
    initCropAlignment() {
        const panel = this.getPanel('imageCropAlignment')

        panel?.addEventListener('change', this.onImageCropAlignmentChangeCallback)

        const activeInput =
            panel?.querySelector('input[type="radio"]:checked') ||
            panel?.querySelector(`input[value="${this.getAttribute('active-tab-value')}"]`)

        if (activeInput) {
            if (!activeInput.checked) activeInput.checked = true

            this.setFormInputValue('imageCropAlignment', activeInput.value)
        }
    }

    disposeCropAlignment() {
        const panel = this.getPanel('imageCropAlignment')

        panel?.removeEventListener('change', this.onImageCropAlignmentChangeCallback)

        this.setFormInputValue('imageCropAlignment', '')
    }

    // Hotspot
    initHotspot() {
        const formInput = this.getFormInput('hotspot')
        const formattedValue = formInput?.value && JSON.parse(formInput.value)

        formInput?.addEventListener('change', this.onHotspotFormInputChangeCallback)

        this.hotspotInputs.forEach((input) => {
            input.addEventListener('change', this.onHotspotTabInputChangeCallback)
        })

        if (formattedValue) {
            this.updateHotspotHandle(formattedValue)
            this.updateHotspotTabInputs(formattedValue)
        } else {
            this.setHotspotFormInputValue({ x: 0.5, y: 0.5 })
        }

        // Allow clicking anywhere on the hotspot area to set the position
        if (this.hotspotArea) {
            this.hotspotAreaClickHandler = (event) => {
                // Ignore clicks on the handle itself (to avoid conflict with drag)
                if (event.target === this.hotspotHandle) return

                this.handleHotspotMove(event.clientX, event.clientY)
            }

            this.hotspotArea.addEventListener('click', this.hotspotAreaClickHandler)
        }

        // Store handler references for cleanup
        this.hotspotMouseDownHandler = (event) => {
            event.preventDefault()
            event.stopPropagation()

            this.hotspotHandle?.classList.add('grabbing')
            this.hotspotMouseMoveHandler = (moveEvent) => {
                this.handleHotspotMove(moveEvent.clientX, moveEvent.clientY)
            }

            this.hotspotMouseUpHandler = () => {
                this.hotspotHandle?.classList.remove('grabbing')
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

                this.handleHotspotMove(touch.clientX, touch.clientY)
            }

            this.hotspotTouchEndHandler = () => {
                document.removeEventListener('touchmove', this.hotspotTouchMoveHandler)
                document.removeEventListener('touchend', this.hotspotTouchEndHandler)
            }

            document.addEventListener('touchmove', this.hotspotTouchMoveHandler)
            document.addEventListener('touchend', this.hotspotTouchEndHandler)
        }

        this.hotspotHandle?.addEventListener('mousedown', this.hotspotMouseDownHandler)
        this.hotspotHandle?.addEventListener('touchstart', this.hotspotTouchStartHandler, { passive: false })
    }

    handleHotspotMove(clientX, clientY) {
        const parent = this.hotspotHandle.parentElement
        const rect = parent.getBoundingClientRect()
        let x = (clientX - rect.left) / rect.width
        let y = (clientY - rect.top) / rect.height

        // Clamp values between 0 and 1
        x = Math.max(0, Math.min(1, x))
        y = Math.max(0, Math.min(1, y))

        // Round to two decimal places
        x = Math.round(x * 100) / 100
        y = Math.round(y * 100) / 100

        this.setHotspotFormInputValue({ x, y })
    }

    disposeHotspot() {
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

        if (this.hotspotHandle && this.hotspotMouseDownHandler) {
            this.hotspotHandle.removeEventListener('mousedown', this.hotspotMouseDownHandler)
        }

        if (this.hotspotHandle && this.hotspotTouchStartHandler) {
            this.hotspotHandle.removeEventListener('touchstart', this.hotspotTouchStartHandler)
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

    updateHotspotTabInputs(value) {
        this.hotspotInputs.forEach((input) => {
            const key = input.name.replace('hotspot-', '')
            input.value = typeof value[key] === 'number' ? value[key] : 0
        })
    }

    updateHotspotHandle(value) {
        if (!this.hotspotHandle) return

        this.hotspotHandle.style.top = `${(value.y || 0) * 100}%`
        this.hotspotHandle.style.left = `${(value.x || 0) * 100}%`
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

    onTabInputChange(event) {
        this.setActiveTab(event.target.value)
    }

    onImageCropAlignmentChange(event) {
        this.setFormInputValue('imageCropAlignment', event.target.value)
    }
}
