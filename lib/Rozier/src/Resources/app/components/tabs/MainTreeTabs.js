const STORAGE_KEY = 'roadiz.currentMainTreeTab'

export default class MainTreeTabs {
    constructor() {
        this.onTabChange = this.onTabChange.bind(this)
        const treeMenu = document.getElementById('tree-menu')
        const currentTabId = window.localStorage.getItem(STORAGE_KEY)

        if (treeMenu) {
            this.tabsMenu = window.UIkit.tab(treeMenu, {
                connect: '#tree-container',
                swiping: false,
                active: currentTabId ? Number.parseInt(currentTabId) : 0,
            })
            this.tabsMenu.on('change.uk.tab', this.onTabChange)
        }
    }

    unbind() {
        if (this.tabsMenu) {
            this.tabsMenu.off('change.uk.tab', this.onTabChange)
        }
    }

    onTabChange(event, activeItem, previousItem) {
        activeItem = activeItem[0]
        const index = activeItem.getAttribute('data-index')
        if (index) {
            window.localStorage.setItem(STORAGE_KEY, index)
        }
    }
}
