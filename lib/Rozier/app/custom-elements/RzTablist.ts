export default class RzTablist extends HTMLElement {
    buttonClassName = 'rz-tablist__inner__tab'
    tabElements: HTMLElement[] = []

    constructor() {
        super()

        this.onTabClick = this.onTabClick.bind(this)
    }

    toggleTabVisibility(tab: HTMLElement, selected: boolean) {
        const panel = document.getElementById(tab.getAttribute('aria-controls'))

        if (selected) {
            tab.classList.add(`${this.buttonClassName}--selected`)
            tab.setAttribute('aria-selected', 'true')
            panel?.removeAttribute('hidden')
        } else {
            tab.classList.remove(`${this.buttonClassName}--selected`)
            tab.setAttribute('aria-selected', 'false')
            panel?.setAttribute('hidden', 'true')
        }
    }

    onTabClick(event: Event) {
        this.tabElements.forEach((tab) => {
            this.toggleTabVisibility(tab, tab === event.currentTarget)
        })
    }

    connectedCallback() {
        if (this.tabElements?.length) return

        this.tabElements = Array.from(
            this.querySelectorAll(`.${this.buttonClassName}`),
        ) as HTMLElement[]

        this.tabElements.forEach((tab) => {
            if (!tab.hasAttribute('aria-selected')) {
                tab.setAttribute('aria-selected', 'false')
            }

            if (!tab.hasAttribute('role')) {
                tab.setAttribute('role', 'tab')
            }

            if (!tab.hasAttribute('aria-controls')) {
                console.warn(
                    'Tab element is missing aria-controls attribute',
                    tab,
                )
            }

            const isSelected =
                tab.getAttribute('aria-selected') === 'true' ||
                tab.classList.contains(`${this.buttonClassName}--selected`)
            this.toggleTabVisibility(tab, isSelected)

            tab.addEventListener('click', this.onTabClick)
        })
    }

    disconnectedCallback() {
        this.tabElements.forEach((tab) => {
            tab.removeEventListener('click', this.onTabClick)
        })
        this.tabElements = []
    }
}
