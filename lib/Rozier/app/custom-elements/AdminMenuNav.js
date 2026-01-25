export default class AdminMenuNav extends HTMLElement {
    connectedCallback() {
        this.reverseSubNav = this.reverseSubNav.bind(this)
        window.addEventListener('resize', this.reverseSubNav)

        this.querySelectorAll('ul[role="menu"]').forEach((element) => {
            const menuItem = element.closest('li')
            if (!menuItem) {
                return
            }
            menuItem.addEventListener('mouseenter', () =>
                this.enterMenuItem(menuItem),
            )
            menuItem.addEventListener('focusin', () =>
                this.enterMenuItem(menuItem),
            )
            menuItem.addEventListener('mouseleave', () =>
                this.leaveMenuItem(menuItem),
            )
            menuItem.addEventListener('focusout', () =>
                this.leaveMenuItem(menuItem),
            )
        })

        this.reverseSubNav()
    }

    /**
     * @param {HTMLLIElement} menuItem
     */
    enterMenuItem(menuItem) {
        const menuItemHeader = menuItem.querySelector('[role="menuitem"]')
        menuItemHeader?.setAttribute('aria-expanded', 'true')
    }

    /**
     * @param {HTMLLIElement} menuItem
     */
    leaveMenuItem(menuItem) {
        const menuItemHeader = menuItem.querySelector('[role="menuitem"]')
        menuItemHeader?.setAttribute('aria-expanded', 'false')
    }

    disconnectedCallback() {
        window.removeEventListener('resize', this.reverseSubNav)
    }

    reverseSubNav() {
        /** @var {HTMLElement} element */
        this.querySelectorAll('.uk-nav-sub').forEach((element) => {
            element.style.display = 'block'
            const top = element.getBoundingClientRect().top
            const height = element.getBoundingClientRect().height
            element.style.display = null

            if (top + height + 20 > window.innerHeight) {
                element.parentElement.classList.add('reversed-nav')
            }
        })
    }
}
