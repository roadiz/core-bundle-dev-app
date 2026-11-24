import { post } from '~/utils/http'
import { pushErrorToast } from '~/utils/toast'

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

        const input = event.currentTarget

        if (!input) {
            return false
        }

        this.locked = true

        const statusName = input.getAttribute('name')
        let statusValue = null

        if (input instanceof HTMLInputElement && input.type === 'checkbox') {
            statusValue = Number(input.checked)
        } else if (
            input instanceof HTMLInputElement &&
            input.type === 'radio'
        ) {
            statusValue = input.value
        }

        window.dispatchEvent(new CustomEvent('requestLoaderShow'))
        try {
            await post('nodesStatusesAjax', {
                body: {
                    nodeId: parseInt(input.getAttribute('data-node-id')),
                    statusName: statusName,
                    statusValue: statusValue,
                },
            })
            window.Rozier.getMessages()
            window.dispatchEvent(new CustomEvent('requestAllNodeTreeRefresh'))
        } catch (error) {
            pushErrorToast(error)
        } finally {
            this.locked = false
            window.dispatchEvent(new CustomEvent('requestLoaderHide'))
        }
    }
}
