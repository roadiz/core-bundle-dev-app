export default class AdminMenuNav extends HTMLElement {
    connectedCallback() {
        this.reverseSubNav = this.reverseSubNav.bind(this)
        window.addEventListener('resize', this.reverseSubNav)

        this.reverseSubNav()
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
