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

        this.onImageCropAlignmentChangeCallback = this.onImageCropAlignmentChange.bind(this)

        this.setActiveTab(this.dataset.activeTabId || 'none')
        // this.setActiveTab('imageCropAlignment') // Default to 'imageCropAlignment' for testing
    }

    disconnectedCallback() {
        this.disposeTab(this.activeTabId)

        this.tabs = null
        this.tabPanels = null
        this.formInputs = null

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

    initHotspot() {
        const formInput = this.getFormInput('hotspot')

        if (formInput && formInput.value) {
            const formattedValue = JSON.parse(formInput.value)
            const tab = this.getTab('hotspot')

            for (const key in formattedValue) {
                const input = tab.querySelector(`input[name="hotspot-${key}"]`)

                if (input) {
                    input.value = formattedValue[key]
                }
            }
        }
    }

    disposeHotspot() {
        this.setFormInputValue('hotspot', '')
    }

    onTabInputChange(event) {
        this.setActiveTab(event.target.value)
    }

    onImageCropAlignmentChange(event) {
        this.setFormInputValue('imageCropAlignment', event.target.value)
    }
}
