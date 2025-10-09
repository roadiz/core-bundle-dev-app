export default class NodeStatuses {
    constructor() {
        this.itemClick = this.itemClick.bind(this)
        this.containerEnter = this.containerEnter.bind(this)
        this.containerLeave = this.containerLeave.bind(this)
        this.onChange = this.onChange.bind(this)

        const containers = document.querySelectorAll(
            '.node-statuses, .node-actions',
        )
        this.icon = document.querySelector('.node-status header i')
        this.locked = false

        containers.forEach((container) => {
            const items = container.querySelectorAll('.node-statuses-item')
            const inputs = container.querySelectorAll(
                'input[type="checkbox"], input[type="radio"]',
            )
            this.init(container, items, inputs)
        })
    }

    /**
     * @param {HTMLElement} container
     * @param {NodeList<HTMLElement>} items
     * @param {NodeList<HTMLElement>} inputs
     */
    init(container, items, inputs) {
        items.forEach((item) => {
            item.removeEventListener('click', this.itemClick)
            item.addEventListener('click', this.itemClick)
        })

        container.addEventListener('mouseenter', this.containerEnter)
        container.addEventListener('mouseleave', this.containerLeave)

        inputs.forEach((input) => {
            input.removeEventListener('change', this.onChange)
            input.addEventListener('change', this.onChange)
        })

        container
            .querySelectorAll('.rz-boolean-checkbox')
            .forEach((checkbox) => {
                checkbox.addEventListener('change', this.onChange)
            })
    }

    containerEnter(event) {
        event.stopPropagation()

        const container = event.currentTarget
        const list = container.querySelector('ul, nav')
        const containerHeight = container.offsetHeight
        const listHeight = list ? list.offsetHeight : 0
        const containerOffsetTop =
            container.getBoundingClientRect().top + window.scrollY
        const windowHeight = window.innerHeight
        const fullHeight = containerOffsetTop + listHeight + containerHeight

        if (windowHeight < fullHeight) {
            container.classList.add('reverse')
        }
    }

    containerLeave(event) {
        event.stopPropagation()

        let container = event.currentTarget
        container.classList.remove('reverse')
    }

    itemClick(event) {
        event.stopPropagation()

        const input = event.currentTarget.querySelector('input[type="radio"]')

        if (input) {
            input.checked = true
            input.dispatchEvent(new Event('change', { bubbles: true }))
        }
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

        const statusName = input.getAttribute('name')
        let statusValue = null

        if (input instanceof HTMLInputElement && input.type === 'checkbox') {
            statusValue = Number(input.checked)
        } else if (
            input instanceof HTMLInputElement &&
            input.type === 'radio'
        ) {
            if (this.icon) {
                this.icon.className =
                    input.parentElement.querySelector('i').className
            }
            statusValue = input.value
        }

        window.dispatchEvent(new CustomEvent('requestLoaderShow'))
        const response = await fetch(
            window.RozierConfig.routes.nodesStatusesAjax,
            {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    // Required to prevent using this route as referer when login again
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new URLSearchParams({
                    _token: window.RozierConfig.ajaxToken,
                    _action: 'nodeChangeStatus',
                    nodeId: parseInt(input.getAttribute('data-node-id')),
                    statusName: statusName,
                    statusValue: statusValue,
                }),
            },
        )
        if (!response.ok) {
            const data = await response.json()
            window.dispatchEvent(
                new CustomEvent('pushToast', {
                    detail: {
                        message: data.error_message,
                        status: 'danger',
                    },
                }),
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
