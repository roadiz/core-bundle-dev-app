export default class EntriesPanel {
    constructor() {
        this.adminMenuNav = document.getElementById('admin-menu-nav')
        this.replaceSubNavs()
    }

    replaceSubNavs() {
        /** @var {HTMLElement} element */
        this.adminMenuNav.querySelectorAll('.uk-nav-sub').forEach((element) => {
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
