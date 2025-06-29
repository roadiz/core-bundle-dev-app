export default class NodesSourcesStatuses extends HTMLElement {
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

    async onChange(event) {
        event.stopPropagation()
        if (this.locked === true) {
            return false
        }

        this.locked = true
        const input = event.currentTarget

        if (!input) {
            return false
        }

        let statusValue = null

        if (input instanceof HTMLInputElement && input.type === 'checkbox') {
            statusValue = Number(input.checked)
        } else if (input instanceof HTMLInputElement && input.type === 'radio') {
            if (this.icon) {
                this.icon.className = input.parentElement.querySelector('i').className
            }
            statusValue = input.value
        }

        window.dispatchEvent(new CustomEvent('requestLoaderShow'))
        const response = await fetch(this.getAttribute('data-update-url'), {
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
            body: new URLSearchParams({
                _token: window.RozierConfig.ajaxToken,
                statusValue: statusValue,
            }),
        })
        if (!response.ok) {
            const data = await response.json()
            window.dispatchEvent(
                new CustomEvent('pushToast', {
                    detail: {
                        message: data.error_message,
                        status: 'danger',
                    },
                })
            )
        } else {
            window.Rozier.refreshMainNodeTree()
            window.Rozier.getMessages()
            window.dispatchEvent(new CustomEvent('requestAllNodeTreeRefresh'))
        }
        this.locked = false
        window.dispatchEvent(new CustomEvent('requestLoaderHide'))
    }
}
