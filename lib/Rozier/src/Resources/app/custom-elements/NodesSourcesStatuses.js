export default class NodesSourcesStatuses extends HTMLElement {
    connectedCallback() {
        this.onChange = this.onChange.bind(this)
        this.itemClick = this.itemClick.bind(this)

        this.icon = this.querySelector('.node-status header i')

        this.querySelectorAll('input').forEach((input) => {
            input.removeEventListener('change', this.onChange)
            input.addEventListener('change', this.onChange)
        })
        this.querySelectorAll('li.node-statuses-item').forEach((item) => {
            item.removeEventListener('click', this.itemClick)
            item.addEventListener('click', this.itemClick)
        })
    }

    disconnectedCallback() {}

    itemClick(event) {
        event.stopPropagation()

        const input = event.currentTarget.querySelector('input[type="radio"]')

        if (input) {
            input.checked = true
            input.dispatchEvent(new Event('change', { bubbles: true }))
        }
    }

    async onChange(event) {
        event.preventDefault()
        event.stopPropagation()

        if (this.locked === true) {
            return false
        }

        this.locked = true
        let input = event.currentTarget

        if (!input) {
            return false
        }

        let statusValue = input.value

        if (this.icon) {
            this.icon.className = input.parentElement.querySelector('i').className
        }

        window.dispatchEvent(new CustomEvent('requestLoaderShow'))
        console.log(this.getAttribute('data-update-url'), {
            _token: window.RozierConfig.ajaxToken,
            statusValue: statusValue,
        })
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
            window.dispatchEvent(new CustomEvent('requestAllNodeTreeRefresh'))
        }
        this.locked = false
        window.dispatchEvent(new CustomEvent('requestLoaderHide'))
        return false
    }
}
