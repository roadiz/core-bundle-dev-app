const STORAGE_KEY = 'roadiz.currentMainTreeTab'
export default class MainTrees extends HTMLElement {
    constructor() {
        super()

        this.activateTab = this.activateTab.bind(this)
        this.activateTabNav = this.activateTabNav.bind(this)

        this.currentTabId = window.localStorage.getItem(STORAGE_KEY)
    }

    connectedCallback() {
        const treeMenu = this.querySelector('#tree-menu');
        const links = treeMenu.querySelectorAll('.tab-link');

        links.forEach(link => {
            link.addEventListener('click', (event) => {
                event.preventDefault()

                this.activateTabNav(link)
                this.activateTab(this.querySelector('#'+link.getAttribute('aria-controls')))
            })
        })

        let currentTabNav = this.querySelector('.tab-link');
        if (this.currentTabId && typeof this.currentTabId === 'string' && this.currentTabId.startsWith('tree')) {
            currentTabNav = this.querySelector('#'+this.currentTabId)
        }
        this.activateTabNav(currentTabNav)
        this.activateTab(this.querySelector('#'+currentTabNav.getAttribute('aria-controls')))
    }

    disconnectedCallback() {

    }

    /**
     * @param {HTMLDivElement} element
     */
    activateTab(element) {
        this.querySelectorAll('.tree-tab-content').forEach(tab => {
            this.desactivateTab(tab)
        })

        element?.classList.add('active')
    }

    /**
     * @param {HTMLLIElement} element
     */
    activateTabNav(element) {
        this.querySelectorAll('.tab-link').forEach(tab => {
            this.desactivateTabNav(tab)
        })

        element?.classList.add('active')
        element?.setAttribute('aria-selected', true)

        const index = element.id
        if (index) {
            this.currentTabId = index
            window.localStorage.setItem(STORAGE_KEY, index)
        }
    }

    /**
     * @param {HTMLDivElement} element
     */
    desactivateTab(element) {
        element?.classList.remove('active')
    }

    /**
     * @param {HTMLLIElement} element
     */
    desactivateTabNav(element) {
        element?.classList.remove('active')
        element?.removeAttribute('aria-selected')
    }
}
