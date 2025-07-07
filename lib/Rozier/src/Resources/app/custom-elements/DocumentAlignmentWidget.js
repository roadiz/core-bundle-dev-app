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

        this.setActiveTab(this.dataset.activeTabId || 'none')
    }

    disconnectedCallback() {
        this.tabs = null
        this.tabPanels = null

        // Dispose tab inputs
        this.tabInputs?.forEach((input) => {
            input.removeEventListener('change', this.onTabInputChangeCallback)
        })
        this.tabInputs = null
    }

    setActiveTab(tabId) {
        const previousActiveTabId = this.activeTabId

        // Update tabs
        const currentTab = this.tabs?.find((tab) => tab.dataset.id === tabId)

        this.tabs?.forEach((tab) => {
            const isActive = tab.dataset.id === tabId

            tab.classList.toggle('document-alignment-widget__tab--active', isActive)
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false')
        })

        // Update panels
        const currentPanelId = currentTab?.getAttribute('aria-controls')

        this.tabPanels?.forEach((panel) => {
            panel.classList.toggle('document-alignment-widget__tab-panel--active', panel.id === currentPanelId)
        })

        // Dispose previous tab / panel
        if (previousActiveTabId === 'cropAlignment') {
            this.disposeCropAlignment()
        } else if (previousActiveTabId === 'hotspot') {
            this.disposeHotspot()
        }

        // Initialize the selected tab / panel
        if (tabId === 'none') {
            this.initNoAlignment()
        } else if (tabId === 'cropAlignment') {
            this.initCropAlignment()
        } else if (tabId === 'hotspot') {
            this.initHotspot()
        }
    }

    initNoAlignment() {
        this.querySelectorAll('.document-alignment-widget__inputs input')?.forEach((input) => {
            input.value = ''
        })
    }

    initCropAlignment() { }

    disposeCropAlignment() { }

    initHotspot() { }

    disposeHotspot() { }

    onTabInputChange(event) {
        this.setActiveTab(event.target.value)
    }
}
