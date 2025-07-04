export default class DocumentAlignmentWidget extends HTMLElement {
    connectedCallback() {
        // Tabs
        this.tabs = this.querySelectorAll('.document-alignment-widget__tab')
        this.onTabClickCallback = this.onTabClick.bind(this)
        this.tabs?.forEach((tab) => {
            tab.addEventListener('click', this.onTabClickCallback)
        })

        // Tab panels
        this.tabPanels = this.querySelectorAll('.document-alignment-widget__tab-panel')
    }

    disconnectedCallback() {
        // Clean up event listeners
        this.tabs?.forEach((tab) => {
            tab.removeEventListener('click', this.onTabClickCallback)
        })
    }

    onTabClick(event) {
        console.log('Tab clicked:', event.target, this)
    }
}
