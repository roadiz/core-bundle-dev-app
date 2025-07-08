export default class DocumentAlignmentWidget extends HTMLElement {
    constructor() {
        super()

        this.activeTabId = ''
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

        // Form inputs
        this.formInputs = Array.from(this.querySelectorAll('.document-alignment-widget__inputs input'))

        // Image crop alignment
        this.onImageCropAlignmentChangeCallback = this.onImageCropAlignmentChange.bind(this)

        // Hotspot
        this.hotspotInputs = Array.from(this.querySelectorAll('.document-alignment-widget__hotspot-input input'))
        this.hotspotHandle = this.querySelector('.document-alignment-widget__hotspot-handle')
        this.onHotspotFormInputChangeCallback = this.onHotspotFormInputChange.bind(this)
        this.onHotspotTabInputChangeCallback = this.onHotspotTabInputChange.bind(this)

        this.setActiveTab(this.dataset.activeTabId || 'none')
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

    getPanel(tabId) {
        return this.tabPanels?.find((panel) => panel.dataset.tabId === tabId)
    }

    initTab(tabId) {
        if (tabId === 'none') {
            this.initNoAlignment()
        } else if (tabId === 'imageCropAlignment') {
            this.initCropAlignment()
        } else if (tabId === 'hotspot') {
            this.initHotspot()
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
        return this.formInputs?.find((input) => input.name?.endsWith(`[${id}]`))
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
        this.setFormInputValue('hotspot', '')
    }

    // Image Crop Alignment
    initCropAlignment() {
        const panel = this.getPanel('imageCropAlignment')

        panel?.addEventListener('change', this.onImageCropAlignmentChangeCallback)

        const checkedInput = panel?.querySelector('input[type="radio"]:checked')

        if (checkedInput) {
            this.setFormInputValue('imageCropAlignment', checkedInput.value)
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
    }

    disposeHotspot() {
        this.setFormInputValue('hotspot', '')
        this.getFormInput('hotspot')?.removeEventListener('change', this.onHotspotChangeCallback)
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
