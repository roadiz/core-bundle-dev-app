export default class DocumentAlignmentWidget extends HTMLElement {
    connectedCallback() {
        // Tab inputs
        this.tabInputs = this.querySelectorAll('.document-alignment-widget__tab > input[type="radio"]')
        this.onTabInputChangeCallback = this.onTabInputChange.bind(this)
        this.tabInputs?.forEach((input) => {
            input.addEventListener('change', this.onTabInputChangeCallback)
        })
    }

    disconnectedCallback() {
        // Dispose tab inputs
        this.tabInputs?.forEach((input) => {
            input.removeEventListener('change', this.onTabInputChangeCallback)
        })
        this.tabInputs = null
    }

    setTab(tabId) {
        const tabs = Array.from(this.querySelectorAll('.document-alignment-widget__tab'))

        // Update tabs
        const currentTab = tabs?.find((tab) => tab.dataset.id === tabId)

        tabs?.forEach((tab) => {
            const isActive = tab.dataset.id === tabId

            tab.classList.toggle('document-alignment-widget__tab--active', isActive)
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false')
        })

        // Update panels
        const currentPanelId = currentTab?.getAttribute('aria-controls')

        this.querySelectorAll('.document-alignment-widget__tab-panel')?.forEach((panel) => {
            panel.classList.toggle('document-alignment-widget__tab-panel--active', panel.id === currentPanelId)
        })
    }

    onTabInputChange(event) {
        this.setTab(event.target.value)
    }
}
