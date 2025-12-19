type TabElements = {
    tab: HTMLElement
    panel: HTMLElement | undefined
}

// type Attributes = {
//     'tab-controls'?: string
//     'tab-panel-active-class'?: string
//     'tab-active-class'?: string
// }

export class RzBulkActions extends HTMLElement {
    tabElements: TabElements[] | null = null

    constructor() {
        super()
        this.onTabClick = this.onTabClick.bind(this)
    }

    onTabClick(event: Event) {
        const clickedTab = event?.currentTarget as HTMLElement
        this.tabElements.forEach(({ tab, panel }) => {
            const activePanelClass = tab?.getAttribute('tab-panel-active-class')
            const activeTabClass = tab?.getAttribute('tab-active-class')

            if (tab === clickedTab && !tab.classList.contains(activeTabClass)) {
                tab.setAttribute('aria-selected', 'true')
                if (activeTabClass) tab?.classList.add(activeTabClass)
                if (activePanelClass) panel?.classList.add(activePanelClass)
            } else {
                tab.setAttribute('aria-selected', 'false')
                if (activeTabClass) tab?.classList.remove(activeTabClass)
                if (activePanelClass) panel?.classList.remove(activePanelClass)
            }
        })
    }

    connectedCallback() {
        this.tabElements = Array.from(
            this.querySelectorAll<HTMLElement>('[tab-controls]'),
        ).map((tabElement) => {
            tabElement.addEventListener('click', this.onTabClick)

            const panelId = tabElement.getAttribute('tab-controls')
            const panelElement = document.getElementById(panelId)!

            return {
                tab: tabElement,
                panel: panelElement,
            }
        })
    }

    disconnectedCallback() {
        this.tabElements.forEach(({ tab }) => {
            tab.removeEventListener('click', this.onTabClick)
        })
        this.tabElements = null
    }
}
